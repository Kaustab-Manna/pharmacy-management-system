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

        $medicines = Database::table('medicines')->where('is_active', 1)->get();

        $this->render('online_orders.index', [
            'pageTitle'    => 'Online / E-Pharmacy Orders (Module 31) - INFOSOF',
            'activeModule' => 'online_orders',
            'orders'       => $orders,
            'medicines'    => $medicines
        ]);
    }

    public function create(): void
    {
        if ($this->request->isPost()) {
            $name = trim($this->request->post('customer_name'));
            $phone = trim($this->request->post('customer_phone'));
            $email = trim($this->request->post('customer_email'));
            $address = trim($this->request->post('delivery_address'));
            $notes = trim($this->request->post('notes'));

            $orderNumber = 'WEB-' . date('Y') . '-' . str_pad((string)rand(100, 9999), 4, '0', STR_PAD_LEFT);

            $id = Database::table('online_orders')->insert([
                'order_number'     => $orderNumber,
                'customer_name'    => $name,
                'customer_phone'   => $phone,
                'customer_email'   => $email,
                'delivery_address' => $address,
                'status'           => 'pending',
                'total_amount'     => 450.00,
                'payment_status'   => 'cod',
                'notes'            => $notes,
                'created_at'       => date('Y-m-d H:i:s')
            ]);

            $this->redirect(App::baseURL() . '/online-orders', 'success', "Online order {$orderNumber} received.");
        }
    }

    public function updateStatus(int $id): void
    {
        $this->requireAuth();
        $status = $this->request->post('status', 'processing');
        Database::table('online_orders')->where('id', $id)->update(['status' => $status]);
        $this->redirect(App::baseURL() . '/online-orders', 'success', "Order status updated to {$status}.");
    }
}
