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
                                 (SELECT COUNT(*) FROM purchase_order_items WHERE po_id = po.id) as item_count
                                 FROM purchase_orders po
                                 JOIN suppliers s ON po.supplier_id = s.id
                                 LEFT JOIN users u ON po.created_by = u.id
                                 LEFT JOIN users app ON po.approved_by = app.id
                                 ORDER BY po.order_date DESC");

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

        $this->redirect(App::baseURL() . '/purchase-orders', 'success', 'Purchase Order approved.');
    }

    public function convertToInvoice(int $id): void
    {
        $this->checkPermission('purchases', 'create');

        $po = Database::table('purchase_orders')->where('id', $id)->first();
        if ($po) {
            Database::table('purchase_orders')->where('id', $id)->update(['status' => 'converted_to_invoice']);
            $this->redirect(App::baseURL() . '/purchases?po_id=' . $id, 'success', "PO {$po['po_number']} converted to Invoice.");
        }
        $this->redirect(App::baseURL() . '/purchase-orders');
    }
}
