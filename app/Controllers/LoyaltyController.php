<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Config\App;

class LoyaltyController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $coupons = Database::table('loyalty_coupons')->orderBy('id', 'DESC')->get();
        $patients = Database::raw("SELECT * FROM patients WHERE loyalty_points > 0 ORDER BY loyalty_points DESC LIMIT 20");
        $logs = Database::raw("SELECT ll.*, p.name as patient_name FROM loyalty_logs ll JOIN patients p ON ll.patient_id = p.id ORDER BY ll.created_at DESC LIMIT 25");

        $this->render('loyalty.index', [
            'pageTitle'    => 'Pharmacy Loyalty & Coupon Management (Module 28) - INFOSOF',
            'activeModule' => 'loyalty',
            'coupons'      => $coupons,
            'patients'     => $patients,
            'logs'         => $logs
        ]);
    }

    public function createCoupon(): void
    {
        $this->checkPermission('settings', 'create');

        if ($this->request->isPost()) {
            $code = strtoupper(trim($this->request->post('code')));
            $desc = trim($this->request->post('description'));
            $discountType = $this->request->post('discount_type', 'percentage');
            $discountValue = (float)$this->request->post('discount_value');
            $minOrder = (float)$this->request->post('min_order_amount', 0);
            $expiry = $this->request->post('expiry_date', date('Y-m-d', strtotime('+3 months')));

            $id = Database::table('loyalty_coupons')->insert([
                'code'             => $code,
                'description'      => $desc,
                'discount_type'    => $discountType,
                'discount_value'   => $discountValue,
                'min_order_amount' => $minOrder,
                'expiry_date'      => $expiry,
                'is_active'        => 1,
                'created_at'       => date('Y-m-d H:i:s')
            ]);

            $this->logAudit('create_coupon', 'loyalty', $id, "Created coupon {$code}");
            $this->redirect(App::baseURL() . '/loyalty', 'success', "Coupon code '{$code}' created.");
        }
    }
}
