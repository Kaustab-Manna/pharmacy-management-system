<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Config\App;

class PricingController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $pricingList = Database::raw("SELECT b.*, m.name as medicine_name, m.brand_name, m.pack_size, m.unit, m.gst_rate
                                     FROM batches b
                                     JOIN medicines m ON b.medicine_id = m.id
                                     WHERE b.status = 'active'
                                     ORDER BY m.brand_name ASC");

        $coupons = Database::table('loyalty_coupons')->orderBy('is_active', 'DESC')->get();

        $this->render('pricing.index', [
            'pageTitle'    => 'Medicine Pricing Management (Module 23) - INFOSOF',
            'activeModule' => 'pricing',
            'pricingList'  => $pricingList,
            'coupons'      => $coupons
        ]);
    }

    public function updatePrice(int $batchId): void
    {
        $this->checkPermission('medicines', 'edit');

        if ($this->request->isPost()) {
            $sellingPrice = (float)$this->request->post('selling_price');
            $wholesalePrice = (float)$this->request->post('wholesale_price');
            $mrp = (float)$this->request->post('mrp');

            Database::table('batches')->where('id', $batchId)->update([
                'selling_price'   => $sellingPrice,
                'wholesale_price' => $wholesalePrice,
                'mrp'             => $mrp,
                'updated_at'      => date('Y-m-d H:i:s')
            ]);

            $this->logAudit('update_price', 'pricing', $batchId, "Updated prices: MRP {$mrp}, Retail {$sellingPrice}, Wholesale {$wholesalePrice}");
            $redirect = $this->request->post('redirect_to') ?: (App::baseURL() . '/pricing');
            $this->redirect($redirect, 'success', 'Prices and MRP updated successfully.');
        }
    }
}
