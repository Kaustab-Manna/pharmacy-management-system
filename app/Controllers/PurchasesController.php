<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Config\App;

class PurchasesController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $purchases = Database::raw("SELECT p.*, s.company_name as supplier_name, s.phone as supplier_phone, u.full_name as created_by_name,
                                           po.po_number
                                   FROM purchases p
                                   JOIN suppliers s ON p.supplier_id = s.id
                                   LEFT JOIN users u ON p.created_by = u.id
                                   LEFT JOIN purchase_orders po ON p.po_id = po.id
                                   ORDER BY p.invoice_date DESC, p.id DESC");

        $suppliers = Database::table('suppliers')->where('is_active', 1)->get();
        $medicines = Database::table('medicines')->where('is_active', 1)->orderBy('name', 'ASC')->get();

        $this->render('purchases.index', [
            'pageTitle'    => 'Purchase Invoices & Procurement - INFOSOF',
            'activeModule' => 'purchases',
            'purchases'    => $purchases,
            'suppliers'    => $suppliers,
            'medicines'    => $medicines
        ]);
    }

    public function create(): void
    {
        $this->checkPermission('purchases', 'create');

        if ($this->request->isPost()) {
            $supplierId = (int)$this->request->post('supplier_id');
            $invNumber = trim($this->request->post('invoice_number'));
            if (empty($invNumber)) {
                $invNumber = 'PINV-' . date('Ymd') . '-' . rand(1000, 9999);
            }
            $poId = (int)$this->request->post('po_id');
            $invDate = $this->request->post('invoice_date', date('Y-m-d'));
            $paymentMode = $this->request->post('payment_mode', 'credit');
            $notes = trim($this->request->post('notes', ''));

            $medIds = $this->request->post('medicine_id') ?? [];
            $batchNos = $this->request->post('batch_number') ?? [];
            $mfgDates = $this->request->post('mfg_date') ?? [];
            $expDates = $this->request->post('expiry_date') ?? [];
            $quantities = $this->request->post('quantity') ?? [];
            $purchaseRates = $this->request->post('purchase_rate') ?? [];
            $mrps = $this->request->post('mrp') ?? [];
            $gstRates = $this->request->post('gst_rate') ?? [];

            if ($supplierId <= 0) {
                $this->redirect(App::baseURL() . '/purchases/create', 'error', 'Please select a valid supplier/distributor.');
                return;
            }

            $subtotal = 0;
            $totalTax = 0;
            $itemsData = [];

            for ($i = 0; $i < count($medIds); $i++) {
                if (!empty($medIds[$i]) && !empty($batchNos[$i]) && !empty($quantities[$i])) {
                    $qty = (int)$quantities[$i];
                    $rate = (float)($purchaseRates[$i] ?? 0);
                    $mrp = (float)($mrps[$i] ?? 0);
                    $gst = (float)($gstRates[$i] ?? 12);

                    $lineBase = $qty * $rate;
                    $lineTax = ($lineBase * $gst) / 100;
                    $lineTotal = $lineBase + $lineTax;

                    $subtotal += $lineBase;
                    $totalTax += $lineTax;

                    $itemsData[] = [
                        'medicine_id'     => (int)$medIds[$i],
                        'batch_number'    => trim($batchNos[$i]),
                        'mfg_date'        => $mfgDates[$i] ?: date('Y-m-d'),
                        'expiry_date'     => $expDates[$i] ?: date('Y-m-d', strtotime('+2 years')),
                        'quantity'        => $qty,
                        'purchase_rate'   => $rate,
                        'mrp'             => $mrp,
                        'selling_price'   => $mrp > 0 ? ($mrp * 0.95) : ($rate * 1.25),
                        'gst_rate'        => $gst,
                        'cgst_amount'     => $lineTax / 2,
                        'sgst_amount'     => $lineTax / 2,
                        'igst_amount'     => 0,
                        'total_amount'    => $lineTotal
                    ];
                }
            }

            if (empty($itemsData)) {
                $this->redirect(App::baseURL() . '/purchases/create', 'error', 'Please add at least one valid medicine item with batch and quantity.');
                return;
            }

            $grandTotal = $subtotal + $totalTax;
            $userPaid = $this->request->post('paid_amount');
            if ($userPaid !== null && is_numeric($userPaid)) {
                $paidAmount = (float)$userPaid;
            } else {
                $paidAmount = ($paymentMode === 'credit') ? 0 : $grandTotal;
            }
            $paymentStatus = ($paidAmount >= $grandTotal) ? 'paid' : ($paidAmount > 0 ? 'partial' : 'unpaid');

            try {
                $purchaseId = Database::table('purchases')->insert([
                    'invoice_number'  => $invNumber,
                    'po_id'           => $poId > 0 ? $poId : null,
                    'supplier_id'     => $supplierId,
                    'invoice_date'    => $invDate,
                    'subtotal'        => $subtotal,
                    'tax_amount'      => $totalTax,
                    'discount_amount' => 0,
                    'grand_total'     => $grandTotal,
                    'paid_amount'     => $paidAmount,
                    'payment_status'  => $paymentStatus,
                    'payment_mode'    => $paymentMode,
                    'notes'           => $notes,
                    'created_by'      => $this->getUser()['id'] ?? null,
                    'created_at'      => date('Y-m-d H:i:s')
                ]);

                // Save items & create/update batches
                foreach ($itemsData as $item) {
                    $existingBatch = Database::table('batches')
                        ->where('medicine_id', $item['medicine_id'])
                        ->where('batch_number', $item['batch_number'])
                        ->first();

                    if ($existingBatch) {
                        $batchId = $existingBatch['id'];
                        $newQty = $existingBatch['quantity'] + $item['quantity'];
                        Database::table('batches')->where('id', $batchId)->update([
                            'quantity'       => $newQty,
                            'purchase_price' => $item['purchase_rate'],
                            'mrp'            => $item['mrp'],
                            'selling_price'  => $item['selling_price'],
                            'status'         => 'active',
                            'updated_at'     => date('Y-m-d H:i:s')
                        ]);
                    } else {
                        $batchId = Database::table('batches')->insert([
                            'medicine_id'      => $item['medicine_id'],
                            'batch_number'     => $item['batch_number'],
                            'mfg_date'         => $item['mfg_date'],
                            'expiry_date'      => $item['expiry_date'],
                            'quantity'         => $item['quantity'],
                            'initial_quantity' => $item['quantity'],
                            'purchase_price'   => $item['purchase_rate'],
                            'selling_price'    => $item['selling_price'],
                            'mrp'              => $item['mrp'],
                            'barcode'          => '890' . rand(100000000, 999999999),
                            'status'           => 'active',
                            'created_at'       => date('Y-m-d H:i:s')
                        ]);
                    }

                    Database::table('purchase_items')->insert([
                        'purchase_id'      => $purchaseId,
                        'medicine_id'      => $item['medicine_id'],
                        'batch_id'         => $batchId,
                        'batch_number'     => $item['batch_number'],
                        'mfg_date'         => $item['mfg_date'],
                        'expiry_date'      => $item['expiry_date'],
                        'quantity'         => $item['quantity'],
                        'purchase_rate'    => $item['purchase_rate'],
                        'mrp'              => $item['mrp'],
                        'selling_price'    => $item['selling_price'],
                        'gst_rate'         => $item['gst_rate'],
                        'cgst_amount'      => $item['cgst_amount'],
                        'sgst_amount'      => $item['sgst_amount'],
                        'igst_amount'      => $item['igst_amount'],
                        'total_amount'     => $item['total_amount']
                    ]);

                    // Record stock movement
                    Database::table('stock_movements')->insert([
                        'medicine_id'    => $item['medicine_id'],
                        'batch_id'       => $batchId,
                        'movement_type'  => 'purchase',
                        'quantity'       => $item['quantity'],
                        'previous_qty'   => $existingBatch ? $existingBatch['quantity'] : 0,
                        'new_qty'        => ($existingBatch ? $existingBatch['quantity'] : 0) + $item['quantity'],
                        'reference_type' => 'purchase_invoice',
                        'reference_id'   => $purchaseId,
                        'user_id'        => $this->getUser()['id'] ?? null,
                        'notes'          => "Purchased from Supplier #{$supplierId}",
                        'created_at'     => date('Y-m-d H:i:s')
                    ]);
                }

                // Update PO status if converting
                if ($poId > 0) {
                    try {
                        Database::table('purchase_orders')->where('id', $poId)->update([
                            'status'     => 'converted_to_invoice',
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);
                    } catch (\Throwable $e) {}
                }

                // Update supplier balance if credit / dues
                if ($paymentStatus !== 'paid') {
                    $due = $grandTotal - $paidAmount;
                    Database::raw("UPDATE suppliers SET current_balance = current_balance + ? WHERE id = ?", [$due, $supplierId]);
                }

                $this->logAudit('create_purchase', 'purchases', $purchaseId, "Created purchase invoice {$invNumber} for {$grandTotal}");
                $this->redirect(App::baseURL() . '/purchases', 'success', "Purchase invoice {$invNumber} successfully recorded! Inventory & batches updated.");
                return;
            } catch (\Throwable $e) {
                error_log("Purchase error: " . $e->getMessage());
                $this->redirect(App::baseURL() . '/purchases/create', 'error', "Failed to record purchase invoice: " . $e->getMessage());
                return;
            }
        }

        // GET request: Render the Purchase Creation page
        $poId = (int)$this->request->get('po_id');
        $po = null;
        $poItems = [];
        if ($poId > 0) {
            $poList = Database::raw("SELECT po.*, s.company_name as supplier_name FROM purchase_orders po JOIN suppliers s ON po.supplier_id = s.id WHERE po.id = ?", [$poId]);
            $po = $poList[0] ?? null;
            if ($po) {
                $poItems = Database::raw("SELECT poi.*, m.name, m.brand_name FROM purchase_order_items poi JOIN medicines m ON poi.medicine_id = m.id WHERE poi.po_id = ?", [$poId]);
            }
        }

        $suppliers = Database::table('suppliers')->where('is_active', 1)->orderBy('company_name', 'ASC')->get();
        $medicines = Database::table('medicines')->where('is_active', 1)->orderBy('name', 'ASC')->get();

        $this->render('purchases.create', [
            'pageTitle'    => 'New Purchase Entry & Inwarding - INFOSOF',
            'activeModule' => 'purchases',
            'suppliers'    => $suppliers,
            'medicines'    => $medicines,
            'po'           => $po,
            'poItems'      => $poItems
        ]);
    }

    public function recordPayment(int $id): void
    {
        $this->checkPermission('purchases', 'create');

        $purchase = Database::table('purchases')->where('id', $id)->first();
        if (!$purchase) {
            $this->redirect(App::baseURL() . '/purchases', 'error', 'Purchase invoice not found.');
            return;
        }

        $amount = (float)$this->request->post('amount');
        $paymentMode = $this->request->post('payment_mode', 'cash');
        $refNo = trim($this->request->post('reference_no', ''));
        $notes = trim($this->request->post('notes', ''));

        if ($amount <= 0) {
            $this->redirect(App::baseURL() . '/purchases', 'error', 'Please enter a valid payment amount greater than zero.');
            return;
        }

        $currentPaid = (float)($purchase['paid_amount'] ?? 0);
        $grandTotal = (float)($purchase['grand_total'] ?? 0);
        if ($grandTotal > 0) {
            $due = max(0, $grandTotal - $currentPaid);
            $payAmount = min($amount, $due);
        } else {
            $payAmount = $amount;
            $grandTotal = $amount;
            Database::table('purchases')->where('id', $id)->update(['grand_total' => $grandTotal, 'subtotal' => $grandTotal]);
        }

        if ($payAmount <= 0) {
            $this->redirect(App::baseURL() . '/purchases', 'info', "Invoice #{$purchase['invoice_number']} is already fully settled.");
            return;
        }

        $newPaid = $currentPaid + $payAmount;
        $newStatus = ($newPaid >= ($grandTotal - 0.01)) ? 'paid' : 'partial';

        // Update purchase record
        Database::table('purchases')->where('id', $id)->update([
            'paid_amount'    => $newPaid,
            'payment_status' => $newStatus,
            'payment_mode'   => $paymentMode
        ]);

        // Reduce supplier dues balance
        Database::raw("UPDATE suppliers SET current_balance = MAX(0, current_balance - ?) WHERE id = ?", [$payAmount, (int)$purchase['supplier_id']]);

        // Record financial transaction
        $supplier = Database::table('suppliers')->where('id', $purchase['supplier_id'])->first();
        $supplierName = $supplier['company_name'] ?? ('Supplier #' . $purchase['supplier_id']);

        Database::table('financial_transactions')->insert([
            'trans_code'     => 'PMT-' . time(),
            'trans_type'     => 'supplier_payment',
            'entity_type'    => 'supplier',
            'entity_id'      => (int)$purchase['supplier_id'],
            'entity_name'    => $supplierName,
            'amount'         => $payAmount,
            'payment_mode'   => $paymentMode,
            'trans_date'     => date('Y-m-d'),
            'description'    => "Payment for Invoice #{$purchase['invoice_number']}" . ($refNo ? " (Ref: {$refNo})" : "") . ($notes ? " - {$notes}" : ""),
            'created_by'     => $this->getUser()['id'] ?? null,
            'created_at'     => date('Y-m-d H:i:s')
        ]);

        $this->logAudit('pay_purchase_invoice', 'purchases', $id, "Recorded payment of ₹{$payAmount} for invoice {$purchase['invoice_number']}");
        $this->redirect(App::baseURL() . '/purchases', 'success', "Payment of ₹" . number_format($payAmount, 2) . " successfully recorded for Invoice #{$purchase['invoice_number']}! Supplier balance updated.");
    }
}
