<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Config\App;

class PurchaseReturnsController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $returns = Database::raw("SELECT pr.*, s.company_name as supplier_name, u.full_name as created_by_name
                                  FROM purchase_returns pr
                                  JOIN suppliers s ON pr.supplier_id = s.id
                                  LEFT JOIN users u ON pr.created_by = u.id
                                  ORDER BY pr.return_date DESC");

        $suppliers = Database::table('suppliers')->where('is_active', 1)->get();
        $batches = Database::raw("SELECT b.*, m.name as medicine_name, m.brand_name 
                                  FROM batches b JOIN medicines m ON b.medicine_id = m.id 
                                  WHERE b.quantity > 0 ORDER BY b.expiry_date ASC");

        $this->render('purchase_returns.index', [
            'pageTitle'    => 'Purchase Return Management (Module 16) - INFOSOF',
            'activeModule' => 'purchase_returns',
            'returns'      => $returns,
            'suppliers'    => $suppliers,
            'batches'      => $batches
        ]);
    }

    public function create(): void
    {
        $this->requireAuth();
        if (!$this->hasPermission('purchases', 'create') && 
            !$this->hasPermission('purchase_returns', 'create') && 
            !$this->hasPermission('expiry', 'edit') && 
            !$this->hasPermission('expiry', 'view')) {
            Session::setFlash('error', "Access Denied: You do not have permission to return stock to suppliers.");
            $this->redirect(App::baseURL() . '/dashboard');
            return;
        }

        if ($this->request->isPost()) {
            $supplierId = (int)$this->request->post('supplier_id');
            $batchId = (int)$this->request->post('batch_id');
            $returnQty = (int)$this->request->post('quantity');
            $returnReason = $this->request->post('return_reason', 'expired');
            $notes = trim($this->request->post('notes', ''));

            if ($supplierId <= 0) {
                $this->redirect(App::baseURL() . '/purchase-returns/create' . ($batchId ? "?batch_id={$batchId}" : ''), 'error', 'Please select a valid supplier/distributor.');
                return;
            }

            $batch = Database::table('batches')->where('id', $batchId)->first();
            if (!$batch || $returnQty <= 0 || $batch['quantity'] < $returnQty) {
                $this->redirect(App::baseURL() . '/purchase-returns/create' . ($batchId ? "?batch_id={$batchId}" : ''), 'error', 'Invalid return quantity. Quantity must be between 1 and available stock (' . ($batch['quantity'] ?? 0) . ').');
                return;
            }

            $returnNumber = 'PR-' . date('Y') . '-' . str_pad((string)rand(100, 9999), 4, '0', STR_PAD_LEFT);
            $rate = (float)$batch['purchase_price'];
            $totalAmount = $rate * $returnQty;

            try {
                $returnId = Database::table('purchase_returns')->insert([
                    'return_number' => $returnNumber,
                    'supplier_id'   => $supplierId,
                    'return_date'   => date('Y-m-d'),
                    'total_amount'  => $totalAmount,
                    'return_reason' => $returnReason,
                    'refund_type'   => 'supplier_credit',
                    'status'        => 'completed',
                    'notes'         => $notes,
                    'created_by'    => $this->getUser()['id'] ?? null,
                    'created_at'    => date('Y-m-d H:i:s')
                ]);

                Database::table('purchase_return_items')->insert([
                    'return_id'    => $returnId,
                    'medicine_id'  => $batch['medicine_id'],
                    'batch_id'     => $batchId,
                    'quantity'     => $returnQty,
                    'rate'         => $rate,
                    'total_amount' => $totalAmount,
                    'reason'       => $returnReason
                ]);

                // Deduct stock from batch
                $newQty = $batch['quantity'] - $returnQty;
                Database::table('batches')->where('id', $batchId)->update([
                    'quantity' => $newQty,
                    'status'   => $newQty <= 0 ? 'depleted' : $batch['status'],
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

                // Adjust supplier ledger balance (Debit Note against supplier)
                Database::raw("UPDATE suppliers SET current_balance = current_balance - ? WHERE id = ?", [$totalAmount, $supplierId]);

                // Record stock movement ledger
                Database::table('stock_movements')->insert([
                    'medicine_id'    => $batch['medicine_id'],
                    'batch_id'       => $batchId,
                    'movement_type'  => 'purchase_return',
                    'quantity'       => -$returnQty,
                    'previous_qty'   => $batch['quantity'],
                    'new_qty'        => $newQty,
                    'reference_type' => 'purchase_return',
                    'reference_id'   => $returnId,
                    'user_id'        => $this->getUser()['id'] ?? null,
                    'notes'          => "Returned to Supplier: {$returnReason}",
                    'created_at'     => date('Y-m-d H:i:s')
                ]);

                $this->logAudit('create_purchase_return', 'purchase_returns', $returnId, "Created purchase return {$returnNumber} for {$totalAmount}");
                $this->redirect(App::baseURL() . '/purchase-returns', 'success', "Debit Note {$returnNumber} generated. Supplier balance adjusted by ₹" . number_format($totalAmount, 2) . ". Batch stock updated.");
                return;
            } catch (\Throwable $e) {
                error_log("Purchase return error: " . $e->getMessage());
                $this->redirect(App::baseURL() . '/purchase-returns/create' . ($batchId ? "?batch_id={$batchId}" : ''), 'error', "Failed to process supplier return: " . $e->getMessage());
                return;
            }
        }

        // GET request: Render the Process Return to Supplier page
        $batchId = (int)$this->request->get('batch_id');
        $batch = null;
        $suggestedSupplierId = null;

        if ($batchId > 0) {
            $batchRows = Database::raw("SELECT b.*, m.name as medicine_name, m.brand_name, m.dosage_form, m.unit, m.strength
                                        FROM batches b
                                        JOIN medicines m ON b.medicine_id = m.id
                                        WHERE b.id = ?", [$batchId]);
            $batch = $batchRows[0] ?? null;

            if ($batch) {
                $supRow = Database::raw("SELECT p.supplier_id 
                                         FROM purchase_items pi 
                                         JOIN purchases p ON pi.purchase_id = p.id 
                                         WHERE pi.batch_id = ? 
                                         ORDER BY p.invoice_date DESC LIMIT 1", [$batchId]);
                $suggestedSupplierId = $supRow[0]['supplier_id'] ?? null;
            }
        }

        $suppliers = Database::table('suppliers')->where('is_active', 1)->orderBy('company_name', 'ASC')->get();
        $batches = Database::raw("SELECT b.*, m.name as medicine_name, m.brand_name, m.unit 
                                  FROM batches b 
                                  JOIN medicines m ON b.medicine_id = m.id 
                                  WHERE b.quantity > 0 
                                  ORDER BY b.expiry_date ASC");

        $this->render('purchase_returns.create', [
            'pageTitle'           => 'Return Stock to Supplier (Debit Note) - INFOSOF',
            'activeModule'        => 'expiry',
            'batch'               => $batch,
            'suggestedSupplierId' => $suggestedSupplierId,
            'suppliers'           => $suppliers,
            'batches'             => $batches,
            'prefillQty'          => $this->request->get('qty')
        ]);
    }
}
