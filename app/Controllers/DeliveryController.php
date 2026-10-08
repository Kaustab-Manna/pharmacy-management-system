<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Config\App;

class DeliveryController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $deliveries = Database::table('deliveries')->orderBy('id', 'DESC')->get();

        $this->render('delivery.index', [
            'pageTitle'    => 'Home Delivery & Dispatch Desk (Module 32) - INFOSOF',
            'activeModule' => 'delivery',
            'deliveries'   => $deliveries
        ]);
    }

    public function create(): void
    {
        $this->checkPermission('pos', 'create');

        if ($this->request->isPost()) {
            $customerName = trim($this->request->post('customer_name'));
            $phone = trim($this->request->post('customer_phone'));
            $address = trim($this->request->post('delivery_address'));
            $rider = trim($this->request->post('delivery_executive_name', 'QuickRider Express'));
            $riderPhone = trim($this->request->post('delivery_executive_phone', '+91 98200 99881'));
            $fee = (float)$this->request->post('delivery_fee', 40.00);

            $track = 'TRK-' . date('Y') . '-' . str_pad((string)rand(100, 9999), 4, '0', STR_PAD_LEFT);

            $id = Database::table('deliveries')->insert([
                'tracking_number'          => $track,
                'customer_name'            => $customerName,
                'customer_phone'           => $phone,
                'delivery_address'         => $address,
                'delivery_executive_name'  => $rider,
                'delivery_executive_phone' => $riderPhone,
                'status'                   => 'assigned',
                'delivery_fee'             => $fee,
                'estimated_delivery'       => date('Y-m-d H:i:s', strtotime('+45 minutes')),
                'created_at'               => date('Y-m-d H:i:s')
            ]);

            $this->logAudit('create_delivery', 'delivery', $id, "Dispatched delivery {$track} to {$customerName}");
            $this->redirect(App::baseURL() . '/delivery', 'success', "Delivery order {$track} assigned to rider.");
        }
    }

    public function updateStatus(int $id): void
    {
        $this->requireAuth();
        $status = $this->request->post('status', 'delivered');
        Database::table('deliveries')->where('id', $id)->update([
            'status'       => $status,
            'delivered_at' => $status === 'delivered' ? date('Y-m-d H:i:s') : null
        ]);
        $this->redirect(App::baseURL() . '/delivery', 'success', "Delivery updated to {$status}.");
    }
}
