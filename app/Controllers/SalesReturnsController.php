<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Config\App;

class SalesReturnsController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $returns = Database::raw("SELECT sr.*, s.invoice_number, p.name as patient_name, u.full_name as created_by_name
                                  FROM sale_returns sr
                                  JOIN sales s ON sr.sale_id = s.id
                                  LEFT JOIN patients p ON sr.patient_id = p.id
                                  LEFT JOIN users u ON sr.created_by = u.id
                                  ORDER BY sr.return_date DESC");

        $recentSales = Database::table('sales')->orderBy('sale_date', 'DESC')->limit(30)->get();

        $this->render('sales_returns.index', [
            'pageTitle'    => 'Sales Returns & Credit Notes (Module 19) - INFOSOF',
            'activeModule' => 'sales_returns',
            'returns'      => $returns,
            'recentSales'  => $recentSales
        ]);
    }

    public function create(): void
    {
        $this->checkPermission('sales', 'create');

        $saleId = (int)$this->request->get('sale_id');
        $sale = Database::table('sales')->where('id', $saleId)->first();
        $items = [];

        if ($sale) {
            $items = Database::raw("SELECT si.*, m.name as medicine_name, m.brand_name, b.batch_number 
                                    FROM sale_items si 
                                    JOIN medicines m ON si.medicine_id = m.id 
                                    JOIN batches b ON si.batch_id = b.id 
                                    WHERE si.sale_id = ?", [$saleId]);
        }

        if ($this->request->isPost()) {
            $saleId = (int)$this->request->post('sale_id');
            $saleItemId = (int)$this->request->post('sale_item_id');
            $returnQty = (int)$this->request->post('quantity');
            $refundType = $this->request->post('refund_type', 'cash_refund');
            $reason = trim($this->request->post('reason', ''));

            $saleItem = Database::table('sale_items')->where('id', $saleItemId)->first();
            $sale = Database::table('sales')->where('id', $saleId)->first();

            if ($saleItem && $returnQty > 0 && $returnQty <= $saleItem['quantity']) {
                $returnNumber = 'SR-' . date('Y') . '-' . str_pad((string)rand(100, 9999), 4, '0', STR_PAD_LEFT);
                $unitPrice = (float)$saleItem['unit_price'];
                $totalRefund = $unitPrice * $returnQty;

                $retId = Database::table('sale_returns')->insert([
                    'return_number' => $returnNumber,
                    'sale_id'       => $saleId,
                    'patient_id'    => $sale['patient_id'],
                    'return_date'   => date('Y-m-d'),
                    'total_amount'  => $totalRefund,
                    'refund_type'   => $refundType,
                    'refund_status' => 'completed',
                    'created_by'    => $this->getUser()['id'] ?? null,
                    'notes'         => $reason,
                    'created_at'    => date('Y-m-d H:i:s')
                ]);

                Database::table('sale_return_items')->insert([
                    'return_id'     => $retId,
                    'sale_item_id'  => $saleItemId,
                    'medicine_id'   => $saleItem['medicine_id'],
                    'batch_id'      => $saleItem['batch_id'],
                    'quantity'      => $returnQty,
                    'unit_price'    => $unitPrice,
                    'total_amount'  => $totalRefund,
                    'return_reason' => $reason
                ]);

                // Automatic Stock Restock to Batch
                $batch = Database::table('batches')->where('id', $saleItem['batch_id'])->first();
                if ($batch) {
                    $newQty = $batch['quantity'] + $returnQty;
                    Database::table('batches')->where('id', $saleItem['batch_id'])->update([
                        'quantity' => $newQty,
                        'status'   => 'active'
                    ]);

                    Database::table('stock_movements')->insert([
                        'medicine_id'    => $batch['medicine_id'],
                        'batch_id'       => $saleItem['batch_id'],
                        'movement_type'  => 'sale_return',
                        'quantity'       => $returnQty,
                        'previous_qty'   => $batch['quantity'],
                        'new_qty'        => $newQty,
                        'reference_type' => 'sale_return',
                        'reference_id'   => $retId,
                        'user_id'        => $this->getUser()['id'] ?? null,
                        'notes'          => "Customer Return: {$reason}",
                        'created_at'     => date('Y-m-d H:i:s')
                    ]);
                }

                $this->logAudit('create_sales_return', 'sales_returns', $retId, "Sales Return {$returnNumber} for {$totalRefund}");
                $this->redirect(App::baseURL() . '/sales-returns', 'success', "Credit Note / Refund {$returnNumber} processed. Batch restocked.");
            }
        }

        $this->render('sales_returns.create', [
            'pageTitle'    => 'Process Sales Return - INFOSOF',
            'activeModule' => 'sales_returns',
            'sale'         => $sale,
            'items'        => $items
        ]);
    }
}
