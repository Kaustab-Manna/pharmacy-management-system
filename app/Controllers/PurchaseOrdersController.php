<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Config\App;

class PurchaseOrdersController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $orders = Database::raw("SELECT po.*, s.company_name as supplier_name, s.phone as supplier_phone,
                                 u.full_name as created_by_name, app.full_name as approved_by_name,
                                 (SELECT COUNT(*) FROM purchase_order_items WHERE po_id = po.id) as item_count,
                                 (SELECT invoice_number FROM purchases WHERE po_id = po.id LIMIT 1) as purchase_invoice_number,
                                 (SELECT id FROM purchases WHERE po_id = po.id LIMIT 1) as purchase_id
                                 FROM purchase_orders po
                                 JOIN suppliers s ON po.supplier_id = s.id
                                 LEFT JOIN users u ON po.created_by = u.id
                                 LEFT JOIN users app ON po.approved_by = app.id
                                 ORDER BY po.order_date DESC, po.id DESC");

        $suppliers = Database::table('suppliers')->where('is_active', 1)->get();
        $medicines = Database::table('medicines')->where('is_active', 1)->orderBy('name', 'ASC')->get();

        $this->render('purchase_orders.index', [
            'pageTitle'    => 'Purchase Orders (PO) - INFOSOF',
            'activeModule' => 'purchase_orders',
            'orders'       => $orders,
            'suppliers'    => $suppliers,
            'medicines'    => $medicines
        ]);
    }

    public function create(): void
    {
        $this->checkPermission('purchases', 'create');

        if ($this->request->isPost()) {
            $supplierId = (int)$this->request->post('supplier_id');
            $expectedDate = $this->request->post('expected_date', date('Y-m-d', strtotime('+7 days')));
            $notes = trim($this->request->post('notes', ''));

            $profile = Database::table('pharmacy_profile')->first();
            $poPrefix = $profile['po_prefix'] ?? 'PO-';
            $poNumber = $poPrefix . date('Y') . '-' . str_pad((string)rand(100, 9999), 4, '0', STR_PAD_LEFT);

            $medIds = $this->request->post('medicine_id') ?? [];
            $quantities = $this->request->post('quantity') ?? [];
            $rates = $this->request->post('expected_rate') ?? [];

            $totalAmount = 0;
            $itemsData = [];

            for ($i = 0; $i < count($medIds); $i++) {
                if (!empty($medIds[$i]) && !empty($quantities[$i])) {
                    $qty = (int)$quantities[$i];
                    $rate = (float)($rates[$i] ?? 0);
                    if ($rate <= 0) {
                        $batchPrev = Database::raw("SELECT purchase_price, mrp FROM batches WHERE medicine_id = ? AND (purchase_price > 0 OR mrp > 0) ORDER BY id DESC LIMIT 1", [(int)$medIds[$i]]);
                        if (!empty($batchPrev) && (float)$batchPrev[0]['purchase_price'] > 0) {
                            $rate = (float)$batchPrev[0]['purchase_price'];
                        } elseif (!empty($batchPrev) && (float)$batchPrev[0]['mrp'] > 0) {
                            $rate = round((float)$batchPrev[0]['mrp'] * 0.70, 2);
                        } else {
                            $rate = 60.00;
                        }
                    }
                    $lineTotal = $qty * $rate;
                    $totalAmount += $lineTotal;

                    $itemsData[] = [
                        'medicine_id'   => (int)$medIds[$i],
                        'quantity'      => $qty,
                        'expected_rate' => $rate,
                        'total_amount'  => $lineTotal
                    ];
                }
            }

            $poId = Database::table('purchase_orders')->insert([
                'po_number'     => $poNumber,
                'supplier_id'   => $supplierId,
                'order_date'    => date('Y-m-d'),
                'expected_date' => $expectedDate,
                'status'        => 'pending_approval',
                'total_amount'  => $totalAmount,
                'notes'         => $notes,
                'created_by'    => $this->getUser()['id'] ?? null,
                'created_at'    => date('Y-m-d H:i:s')
            ]);

            foreach ($itemsData as $item) {
                Database::table('purchase_order_items')->insert([
                    'po_id'         => $poId,
                    'medicine_id'   => $item['medicine_id'],
                    'quantity'      => $item['quantity'],
                    'expected_rate' => $item['expected_rate'],
                    'total_amount'  => $item['total_amount']
                ]);
            }

            $this->logAudit('create_po', 'purchase_orders', $poId, "Created PO {$poNumber}");
            $this->redirect(App::baseURL() . '/purchase-orders', 'success', "Purchase Order {$poNumber} submitted.");
            return;
        }

        $medId = (int)$this->request->get('medicine_id');
        $this->redirect(App::baseURL() . '/purchase-orders?medicine_id=' . $medId . '&open=create');
    }

    public function approve(int $id): void
    {
        $this->checkPermission('purchases', 'approve');

        Database::table('purchase_orders')->where('id', $id)->update([
            'status'      => 'approved',
            'approved_by' => $this->getUser()['id'] ?? null
        ]);

        $action = $this->request->post('action');
        if ($action === 'only_approve') {
            $this->redirect(App::baseURL() . '/purchase-orders', 'success', 'Purchase Order approved.');
            return;
        }

        // By default, approving a PO immediately converts to a Purchase Invoice and updates stock
        $this->convertToInvoice($id);
    }

    public function convertToInvoice(int $id): void
    {
        $this->checkPermission('purchases', 'create');

        $po = Database::table('purchase_orders')->where('id', $id)->first();
        if (!$po) {
            $this->redirect(App::baseURL() . '/purchase-orders', 'error', 'Purchase Order not found.');
            return;
        }

        // Check if an invoice was already generated for this PO
        $existingInvoice = Database::table('purchases')->where('po_id', $id)->first();
        if ($existingInvoice) {
            Database::table('purchase_orders')->where('id', $id)->update(['status' => 'converted_to_invoice']);
            $this->redirect(App::baseURL() . '/purchases', 'info', "PO {$po['po_number']} already has Purchase Invoice {$existingInvoice['invoice_number']}.");
            return;
        }

        $poItems = Database::raw("SELECT poi.*, m.name as medicine_name, m.brand_name, m.gst_rate 
                                  FROM purchase_order_items poi 
                                  JOIN medicines m ON poi.medicine_id = m.id 
                                  WHERE poi.po_id = ?", [$id]);

        if (empty($poItems)) {
            $this->redirect(App::baseURL() . '/purchase-orders', 'error', 'This Purchase Order has no items to convert.');
            return;
        }

        $subtotal = 0;
        $totalTax = 0;
        $itemsData = [];

        foreach ($poItems as $idx => $poi) {
            $qty = (int)$poi['quantity'];
            $rate = (float)$poi['expected_rate'];
            if ($rate <= 0) {
                $batchPrev = Database::raw("SELECT purchase_price, mrp FROM batches WHERE medicine_id = ? AND (purchase_price > 0 OR mrp > 0) ORDER BY id DESC LIMIT 1", [(int)$poi['medicine_id']]);
                if (!empty($batchPrev) && (float)$batchPrev[0]['purchase_price'] > 0) {
                    $rate = (float)$batchPrev[0]['purchase_price'];
                } elseif (!empty($batchPrev) && (float)$batchPrev[0]['mrp'] > 0) {
                    $rate = round((float)$batchPrev[0]['mrp'] * 0.70, 2);
                } else {
                    $rate = 60.00;
                }
            }
            $gst = (float)($poi['gst_rate'] ?? 12.0);

            $lineBase = $qty * $rate;
            $lineTax = ($lineBase * $gst) / 100;
            $lineTotal = $lineBase + $lineTax;

            $subtotal += $lineBase;
            $totalTax += $lineTax;

            // Generate clean batch number for received stock
            $cleanPoNum = preg_replace('/[^a-zA-Z0-9]/', '', $po['po_number']);
            $batchNo = 'B-' . strtoupper(substr($cleanPoNum, -4)) . '-' . str_pad((string)($idx + 1), 2, '0', STR_PAD_LEFT);
            $mfgDate = date('Y-m-d');
            $expDate = date('Y-m-d', strtotime('+2 years'));
            $mrp = $rate > 0 ? round($rate * 1.35, 2) : 100.00;
            $sellingPrice = $rate > 0 ? round($rate * 1.20, 2) : 90.00;

            $itemsData[] = [
                'medicine_id'   => (int)$poi['medicine_id'],
                'batch_number'  => $batchNo,
                'mfg_date'      => $mfgDate,
                'expiry_date'   => $expDate,
                'quantity'      => $qty,
                'purchase_rate' => $rate,
                'mrp'           => $mrp,
                'selling_price' => $sellingPrice,
                'gst_rate'      => $gst,
                'cgst_amount'   => $lineTax / 2,
                'sgst_amount'   => $lineTax / 2,
                'igst_amount'   => 0,
                'total_amount'  => $lineTotal
            ];
        }

        $grandTotal = $subtotal + $totalTax;
        $cleanPoSuffix = substr(preg_replace('/[^0-9]/', '', $po['po_number']), -4) ?: rand(1000, 9999);
        $invNumber = 'PINV-' . date('Ymd') . '-' . $cleanPoSuffix;

        // 1. Insert Purchase Invoice Record
        $purchaseId = Database::table('purchases')->insert([
            'invoice_number'  => $invNumber,
            'po_id'           => $id,
            'supplier_id'     => (int)$po['supplier_id'],
            'invoice_date'    => date('Y-m-d'),
            'subtotal'        => $subtotal,
            'tax_amount'      => $totalTax,
            'discount_amount' => 0.00,
            'grand_total'     => $grandTotal,
            'paid_amount'     => 0.00,
            'payment_status'  => 'unpaid',
            'payment_mode'    => 'credit',
            'notes'           => "Received & converted from Purchase Order {$po['po_number']}",
            'created_by'      => $this->getUser()['id'] ?? null,
            'created_at'      => date('Y-m-d H:i:s')
        ]);

        // 2. Insert items, create/update stock batches, and record stock movements
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
                'igst_amount'      => 0,
                'total_amount'     => $item['total_amount']
            ]);

            // Track stock movement
            try {
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
                    'notes'          => "Inwarded via PO {$po['po_number']}",
                    'created_at'     => date('Y-m-d H:i:s')
                ]);
            } catch (\Throwable $e) {}
        }

        // Update supplier dues balance
        try {
            Database::raw("UPDATE suppliers SET current_balance = current_balance + ? WHERE id = ?", [$grandTotal, (int)$po['supplier_id']]);
        } catch (\Throwable $e) {}

        // 3. Mark PO as converted_to_invoice
        Database::table('purchase_orders')->where('id', $id)->update(['status' => 'converted_to_invoice']);

        $this->logAudit('convert_po', 'purchase_orders', $id, "Converted PO {$po['po_number']} to Purchase Invoice {$invNumber}");

        $this->redirect(App::baseURL() . '/purchases', 'success', "PO {$po['po_number']} successfully approved and converted to Purchase Invoice {$invNumber}! Stock added to inventory.");
    }
}
