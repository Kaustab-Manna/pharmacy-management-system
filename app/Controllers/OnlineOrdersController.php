<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Config\App;

class OnlineOrdersController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $orders = Database::raw("SELECT oo.*, 
                                (SELECT COUNT(*) FROM online_order_items WHERE online_order_id = oo.id) as item_count 
                                FROM online_orders oo 
                                ORDER BY oo.created_at DESC");

        // Fetch detailed items for each order
        $orderIds = array_column($orders, 'id');
        $itemsByOrder = [];
        if (!empty($orderIds)) {
            $idsList = implode(',', array_map('intval', $orderIds));
            $items = Database::raw("SELECT oi.*, m.name as medicine_name, m.brand_name, m.strength, m.dosage_form, m.unit, m.code 
                                    FROM online_order_items oi 
                                    JOIN medicines m ON oi.medicine_id = m.id 
                                    WHERE oi.online_order_id IN ($idsList) 
                                    ORDER BY oi.id ASC");
            foreach ($items as $item) {
                $itemsByOrder[$item['online_order_id']][] = $item;
            }
        }

        foreach ($orders as &$o) {
            $o['items'] = $itemsByOrder[$o['id']] ?? [];
        }
        unset($o);

        // Fetch active medicines with current selling prices for simulation modal
        $medicines = Database::raw("SELECT m.id, m.name, m.brand_name, m.strength, m.dosage_form, m.unit,
                                    COALESCE(b.selling_price, 50.00) as selling_price
                                    FROM medicines m
                                    LEFT JOIN (
                                        SELECT medicine_id, MAX(selling_price) as selling_price 
                                        FROM batches 
                                        WHERE quantity > 0 
                                        GROUP BY medicine_id
                                    ) b ON m.id = b.medicine_id
                                    WHERE m.is_active = 1
                                    ORDER BY m.name ASC");

        $this->render('online_orders.index', [
            'pageTitle'    => 'Online / E-Pharmacy Orders - INFOSOF',
            'activeModule' => 'online_orders',
            'orders'       => $orders,
            'medicines'    => $medicines
        ]);
    }

    public function create(): void
    {
        $this->requireAuth();

        if ($this->request->isPost()) {
            $name = trim($this->request->post('customer_name') ?? '');
            $phone = trim($this->request->post('customer_phone') ?? '');
            $email = trim($this->request->post('customer_email') ?? '');
            $address = trim($this->request->post('delivery_address') ?? '');
            $notes = trim($this->request->post('notes') ?? '');
            $paymentStatus = trim($this->request->post('payment_status') ?? 'cod');

            $orderNumber = 'WEB-' . date('Y') . '-' . str_pad((string)rand(100, 9999), 4, '0', STR_PAD_LEFT);

            $medicineIds = $this->request->post('medicine_ids');
            $quantities = $this->request->post('quantities');
            $prices = $this->request->post('prices');

            $itemsToInsert = [];
            $totalAmount = 0.0;

            if (is_array($medicineIds) && !empty($medicineIds)) {
                foreach ($medicineIds as $idx => $medId) {
                    $medId = (int)$medId;
                    if ($medId <= 0) continue;
                    $qty = max(1, (int)($quantities[$idx] ?? 1));
                    $price = max(0.0, (float)($prices[$idx] ?? 0.0));
                    $itemsToInsert[] = [
                        'medicine_id' => $medId,
                        'quantity'    => $qty,
                        'price'       => $price,
                    ];
                    $totalAmount += ($qty * $price);
                }
            }

            // Fallback if no medicine selected: pick first 2 active medicines
            if (empty($itemsToInsert)) {
                $sampleMeds = Database::raw("SELECT m.id, COALESCE(b.selling_price, 50.00) as selling_price 
                                            FROM medicines m 
                                            LEFT JOIN batches b ON m.id = b.medicine_id 
                                            WHERE m.is_active = 1 LIMIT 2");
                foreach ($sampleMeds as $sm) {
                    $qty = 1;
                    $pr = (float)$sm['selling_price'];
                    $itemsToInsert[] = [
                        'medicine_id' => (int)$sm['id'],
                        'quantity'    => $qty,
                        'price'       => $pr,
                    ];
                    $totalAmount += ($qty * $pr);
                }
            }

            $id = Database::table('online_orders')->insert([
                'order_number'     => $orderNumber,
                'customer_name'    => $name,
                'customer_phone'   => $phone,
                'customer_email'   => $email,
                'delivery_address' => $address,
                'status'           => 'pending',
                'total_amount'     => $totalAmount > 0 ? $totalAmount : 450.00,
                'payment_status'   => $paymentStatus ?: 'cod',
                'notes'            => $notes,
                'created_at'       => date('Y-m-d H:i:s')
            ]);

            foreach ($itemsToInsert as $item) {
                Database::table('online_order_items')->insert([
                    'online_order_id' => $id,
                    'medicine_id'     => $item['medicine_id'],
                    'quantity'        => $item['quantity'],
                    'price'           => $item['price'],
                ]);
            }

            $this->redirect(App::baseURL() . '/online-orders', 'success', "Online order {$orderNumber} received successfully.");
        }
    }

    public function updateStatus(int $id): void
    {
        $this->requireAuth();
        $status = $this->request->post('status', 'processing');
        Database::table('online_orders')->where('id', $id)->update(['status' => $status]);
        $this->redirect(App::baseURL() . '/online-orders', 'success', "Order status updated to {$status}.");
    }

    public function details(int $id): void
    {
        $this->requireAuth();

        $order = Database::table('online_orders')->where('id', $id)->first();
        if (!$order) {
            $this->json(['error' => 'Order not found'], 404);
            return;
        }

        $items = Database::raw("SELECT oi.*, m.name as medicine_name, m.brand_name, m.strength, m.dosage_form, m.unit, m.code 
                                FROM online_order_items oi 
                                JOIN medicines m ON oi.medicine_id = m.id 
                                WHERE oi.online_order_id = ? 
                                ORDER BY oi.id ASC", [$id]);

        $order['items'] = $items;
        $this->json(['success' => true, 'order' => $order]);
    }
}
