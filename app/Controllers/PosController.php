<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Config\App;

class PosController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        // Fetch active medicines with batches for counter
        $today = date('Y-m-d');
        $medicines = Database::raw("SELECT m.*, c.name as category_name,
                      COALESCE((SELECT SUM(b.quantity) FROM batches b WHERE b.medicine_id = m.id AND b.status = 'active' AND b.expiry_date >= ?), 0) as total_stock
                      FROM medicines m
                      LEFT JOIN categories c ON m.category_id = c.id
                      WHERE m.is_active = 1
                        AND m.id IN (SELECT DISTINCT medicine_id FROM batches WHERE status = 'active' AND expiry_date >= ? AND quantity > 0)
                      ORDER BY m.name ASC", [$today, $today]);

        $patients = Database::table('patients')->where('is_active', 1)->orderBy('name', 'ASC')->get();
        $doctors = Database::table('doctors')->orderBy('name', 'ASC')->get();
        $coupons = Database::table('loyalty_coupons')->where('is_active', 1)->where('expiry_date', '>=', date('Y-m-d'))->get();

        // Active verified prescriptions for fast loading
        $prescriptions = Database::raw("SELECT p.*, pt.name as patient_name, pt.phone as patient_phone, d.name as doctor_name 
                                       FROM prescriptions p 
                                       JOIN patients pt ON p.patient_id = pt.id 
                                       LEFT JOIN doctors d ON p.doctor_id = d.id 
                                       WHERE p.status IN ('verified', 'partially_dispensed') 
                                       ORDER BY p.prescription_date DESC LIMIT 20");

        $this->render('pos.index', [
            'pageTitle'     => 'Fast POS Counter Billing (F2) - INFOSOF',
            'activeModule'  => 'pos',
            'medicines'     => $medicines,
            'patients'      => $patients,
            'doctors'       => $doctors,
            'coupons'       => $coupons,
            'prescriptions' => $prescriptions,
        ]);
    }

    public function getBatches(int $medicineId): void
    {
        $today = date('Y-m-d');
        // FEFO order: earliest expiry first
        $batches = Database::raw("SELECT b.*, m.name as medicine_name, m.brand_name, m.unit, m.gst_rate
                                  FROM batches b
                                  JOIN medicines m ON b.medicine_id = m.id
                                  WHERE b.medicine_id = ? AND b.status = 'active' AND b.quantity > 0 AND b.expiry_date >= ?
                                  ORDER BY b.expiry_date ASC", [$medicineId, $today]);

        $this->json(['success' => true, 'batches' => $batches]);
    }

    public function checkout(): void
    {
        $this->checkPermission('pos', 'create');

        $input = $this->request->all();
        if (empty($input['items'])) {
            $input = array_merge((array)$this->request->post(), (array)$this->request->json());
        }

        $items = $input['items'] ?? [];
        if (empty($items)) {
            $this->json(['success' => false, 'message' => 'Cart is empty. Please add items.'], 400);
            return;
        }

        $patientId = !empty($input['patient_id']) ? (int)$input['patient_id'] : null;
        $customerName = trim($input['customer_name'] ?? 'Walk-in Cash Customer');
        $customerPhone = trim($input['customer_phone'] ?? '');
        $doctorId = !empty($input['doctor_id']) ? (int)$input['doctor_id'] : null;
        $prescriptionId = !empty($input['prescription_id']) ? (int)$input['prescription_id'] : null;
        $paymentMode = trim($input['payment_mode'] ?? 'cash');
        $paidAmount = (float)($input['paid_amount'] ?? 0);
        $couponCode = trim($input['coupon_code'] ?? '');
        $useLoyaltyPoints = (int)($input['use_loyalty_points'] ?? 0);

        // Generate Invoice Number (e.g. INV-2026-0004)
        $profile = Database::table('pharmacy_profile')->first();
        $prefix = $profile['invoice_prefix'] ?? 'INV-';
        $invoiceNumber = $prefix . date('Y') . '-' . str_pad((string)rand(1000, 99999), 5, '0', STR_PAD_LEFT);

        $subtotal = 0;
        $totalTax = 0;
        $totalCgst = 0;
        $totalSgst = 0;
        $totalIgst = 0;
        $totalDiscount = 0;
        $processedItems = [];

        // Validate batches and reduce stock
        foreach ($items as $item) {
            $batchId = (int)$item['batch_id'];
            $reqQty = (int)$item['quantity'];

            $batch = Database::raw("SELECT b.*, m.name as medicine_name, m.brand_name, m.gst_rate 
                                    FROM batches b JOIN medicines m ON b.medicine_id = m.id 
                                    WHERE b.id = ?", [$batchId])[0] ?? null;

            if (!$batch || $batch['quantity'] < $reqQty) {
                $medName = $batch['brand_name'] ?? 'Item';
                $avail = $batch['quantity'] ?? 0;
                $this->json(['success' => false, 'message' => "Insufficient stock for {$medName}. Requested {$reqQty}, available {$avail}."], 400);
                return;
            }

            $unitPrice = (float)($item['unit_price'] ?? $batch['selling_price']);
            $mrp = (float)$batch['mrp'];
            $itemDiscountPercent = (float)($item['discount_percent'] ?? 0);
            $gstRate = (float)$batch['gst_rate'];

            $lineBase = $unitPrice * $reqQty;
            $lineDiscount = ($lineBase * $itemDiscountPercent) / 100;
            $lineNet = $lineBase - $lineDiscount;

            // Split GST 50/50 CGST & SGST
            $lineTax = ($lineNet * $gstRate) / 100;
            $lineCgst = $lineTax / 2;
            $lineSgst = $lineTax / 2;
            $lineIgst = 0;
            $lineTotal = $lineNet + $lineTax;

            $subtotal += $lineBase;
            $totalDiscount += $lineDiscount;
            $totalTax += $lineTax;
            $totalCgst += $lineCgst;
            $totalSgst += $lineSgst;

            $processedItems[] = [
                'medicine_id'      => $batch['medicine_id'],
                'batch_id'         => $batchId,
                'quantity'         => $reqQty,
                'unit_price'       => $unitPrice,
                'mrp'              => $mrp,
                'discount_percent' => $itemDiscountPercent,
                'gst_rate'         => $gstRate,
                'cgst_amount'      => $lineCgst,
                'sgst_amount'      => $lineSgst,
                'igst_amount'      => $lineIgst,
                'total_amount'     => $lineTotal,
                'batch_data'       => $batch
            ];
        }

        // Apply Loyalty / Coupon Discounts
        $loyaltyDiscount = 0;
        if ($useLoyaltyPoints > 0 && $patientId) {
            $patient = Database::table('patients')->where('id', $patientId)->first();
            if ($patient && $patient['loyalty_points'] >= $useLoyaltyPoints) {
                $loyaltyDiscount = min($useLoyaltyPoints * 1.0, $subtotal * 0.5); // max 50% discount
                // Deduct patient points
                Database::table('patients')->where('id', $patientId)->update([
                    'loyalty_points' => $patient['loyalty_points'] - $useLoyaltyPoints
                ]);
            }
        }

        $grossTotal = $subtotal - $totalDiscount - $loyaltyDiscount + $totalTax;
        $grandTotal = round($grossTotal);
        $roundOff = $grandTotal - $grossTotal;

        if ($paidAmount <= 0) {
            $paidAmount = $grandTotal;
        }

        $changeAmount = max(0, $paidAmount - $grandTotal);
        $paymentStatus = ($paidAmount >= $grandTotal) ? 'paid' : ($paidAmount > 0 ? 'partial' : 'unpaid');
        $isCreditSale = ($paymentMode === 'credit' || $paymentStatus !== 'paid');

        // Insert Sale Record
        $saleId = Database::table('sales')->insert([
            'invoice_number'      => $invoiceNumber,
            'prescription_id'     => $prescriptionId,
            'patient_id'          => $patientId,
            'customer_name'       => $customerName,
            'customer_phone'      => $customerPhone,
            'doctor_id'           => $doctorId,
            'sale_date'           => date('Y-m-d H:i:s'),
            'subtotal'            => $subtotal,
            'tax_amount'          => $totalTax,
            'cgst_amount'         => $totalCgst,
            'sgst_amount'         => $totalSgst,
            'igst_amount'         => $totalIgst,
            'discount_amount'     => $totalDiscount,
            'loyalty_points_used' => $useLoyaltyPoints,
            'loyalty_discount'    => $loyaltyDiscount,
            'round_off'           => $roundOff,
            'grand_total'         => $grandTotal,
            'paid_amount'         => $paidAmount,
            'change_amount'       => $changeAmount,
            'payment_mode'        => $paymentMode,
            'payment_status'      => $paymentStatus,
            'is_credit_sale'      => $isCreditSale ? 1 : 0,
            'cashier_id'          => $this->getUser()['id'] ?? null,
            'created_at'          => date('Y-m-d H:i:s')
        ]);

        // Insert Sale Items & Deduct Inventory Batches
        foreach ($processedItems as $pItem) {
            Database::table('sale_items')->insert([
                'sale_id'          => $saleId,
                'medicine_id'      => $pItem['medicine_id'],
                'batch_id'         => $pItem['batch_id'],
                'quantity'         => $pItem['quantity'],
                'unit_price'       => $pItem['unit_price'],
                'mrp'              => $pItem['mrp'],
                'discount_percent' => $pItem['discount_percent'],
                'gst_rate'         => $pItem['gst_rate'],
                'cgst_amount'      => $pItem['cgst_amount'],
                'sgst_amount'      => $pItem['sgst_amount'],
                'igst_amount'      => $pItem['igst_amount'],
                'total_amount'     => $pItem['total_amount']
            ]);

            // Deduct stock from batch
            $currentBatchQty = (int)$pItem['batch_data']['quantity'];
            $newBatchQty = $currentBatchQty - $pItem['quantity'];

            Database::table('batches')->where('id', $pItem['batch_id'])->update([
                'quantity' => $newBatchQty,
                'status'   => $newBatchQty <= 0 ? 'depleted' : 'active'
            ]);

            // Record stock movement ledger
            Database::table('stock_movements')->insert([
                'medicine_id'    => $pItem['medicine_id'],
                'batch_id'       => $pItem['batch_id'],
                'movement_type'  => 'sale',
                'quantity'       => -$pItem['quantity'],
                'previous_qty'   => $currentBatchQty,
                'new_qty'        => $newBatchQty,
                'reference_type' => 'sales_invoice',
                'reference_id'   => $saleId,
                'user_id'        => $this->getUser()['id'] ?? null,
                'notes'          => "POS Sale #{$invoiceNumber}",
                'created_at'     => date('Y-m-d H:i:s')
            ]);
        }

        // Award Loyalty Points to Patient (1 pt per ₹100)
        if ($patientId) {
            $earnedPoints = floor($grandTotal / 100);
            if ($earnedPoints > 0) {
                Database::raw("UPDATE patients SET loyalty_points = loyalty_points + ? WHERE id = ?", [$earnedPoints, $patientId]);
                Database::table('loyalty_logs')->insert([
                    'patient_id'    => $patientId,
                    'sale_id'       => $saleId,
                    'points_change' => $earnedPoints,
                    'reason'        => "Earned from Invoice #{$invoiceNumber}",
                    'created_at'    => date('Y-m-d H:i:s')
                ]);
            }

            // If credit sale, update patient outstanding balance
            if ($isCreditSale && $paidAmount < $grandTotal) {
                $due = $grandTotal - $paidAmount;
                Database::raw("UPDATE patients SET outstanding_balance = outstanding_balance + ? WHERE id = ?", [$due, $patientId]);
            }
        }

        // If prescription used, mark as completed or update fulfillment
        if ($prescriptionId) {
            Database::table('prescriptions')->where('id', $prescriptionId)->update(['status' => 'completed']);
        }

        $this->logAudit('create_sale', 'pos', $saleId, "Created POS Invoice {$invoiceNumber} for {$grandTotal}");

        $this->json([
            'success'        => true,
            'invoice_id'     => $saleId,
            'invoice_number' => $invoiceNumber,
            'grand_total'    => $grandTotal,
            'change_amount'  => $changeAmount,
            'message'        => 'Sale completed successfully!'
        ]);
    }
}
