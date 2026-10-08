<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Config\App;

class SettingsController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $pharmacy = Database::table('pharmacy_profile')->first();
        $settingsList = Database::table('settings')->get();
        $settings = [];
        foreach ($settingsList as $s) {
            $settings[$s['setting_key']] = $s['setting_value'];
        }

        $this->render('settings.index', [
            'pageTitle'    => 'Store Profile, Settings & Print Center (Modules 3 & 37) - INFOSOF',
            'activeModule' => 'settings',
            'pharmacy'     => $pharmacy,
            'settings'     => $settings
        ]);
    }

    public function update(): void
    {
        $this->checkPermission('settings', 'edit');

        if ($this->request->isPost()) {
            $data = [
                'pharmacy_name'      => trim($this->request->post('pharmacy_name')),
                'legal_name'         => trim($this->request->post('legal_name')),
                'gstin'              => trim($this->request->post('gstin')),
                'drug_license_no'    => trim($this->request->post('drug_license_no')),
                'pharmacy_reg_no'    => trim($this->request->post('pharmacy_reg_no')),
                'pharmacist_name'    => trim($this->request->post('pharmacist_name')),
                'pharmacist_reg_no'  => trim($this->request->post('pharmacist_reg_no')),
                'address'            => trim($this->request->post('address')),
                'city'               => trim($this->request->post('city')),
                'state'              => trim($this->request->post('state')),
                'pincode'            => trim($this->request->post('pincode')),
                'phone'              => trim($this->request->post('phone')),
                'mobile'             => trim($this->request->post('mobile')),
                'email'              => trim($this->request->post('email')),
                'website'            => trim($this->request->post('website')),
                'invoice_prefix'     => trim($this->request->post('invoice_prefix', 'INV-')),
                'po_prefix'          => trim($this->request->post('po_prefix', 'PO-')),
                'currency_symbol'    => trim($this->request->post('currency_symbol', '₹')),
                'thermal_header'     => trim($this->request->post('thermal_header', '')),
                'thermal_footer'     => trim($this->request->post('thermal_footer', '')),
                'terms_conditions'   => trim($this->request->post('terms_conditions', '')),
                'updated_at'         => date('Y-m-d H:i:s')
            ];

            Database::table('pharmacy_profile')->where('id', 1)->update($data);

            // Update settings key values
            $thermalSize = $this->request->post('thermal_paper_size', '80mm');
            Database::table('settings')->where('setting_key', 'thermal_paper_size')->update(['setting_value' => $thermalSize]);

            $this->logAudit('update_settings', 'settings', 1, "Updated pharmacy company profile & invoice settings");
            $this->redirect(App::baseURL() . '/settings', 'success', 'Pharmacy settings & Print configurations updated successfully.');
        }
    }
}
