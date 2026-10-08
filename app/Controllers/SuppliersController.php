<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Config\App;

class SuppliersController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $suppliers = Database::raw("SELECT s.*, 
                                   (SELECT COUNT(*) FROM purchases WHERE supplier_id = s.id) as total_invoices,
                                   (SELECT COALESCE(SUM(grand_total), 0) FROM purchases WHERE supplier_id = s.id) as total_purchase_volume
                                   FROM suppliers s
                                   ORDER BY s.company_name ASC");

        $this->render('suppliers.index', [
            'pageTitle'    => 'Suppliers & Distributors Management (Module 9) - INFOSOF',
            'activeModule' => 'suppliers',
            'suppliers'    => $suppliers
        ]);
    }

    public function create(): void
    {
        $this->checkPermission('suppliers', 'create');

        if ($this->request->isPost()) {
            $companyName = trim($this->request->post('company_name'));
            $contactPerson = trim($this->request->post('contact_person'));
            $phone = trim($this->request->post('phone'));
            $email = trim($this->request->post('email'));
            $address = trim($this->request->post('address'));
            $city = trim($this->request->post('city', 'Mumbai'));
            $state = trim($this->request->post('state', 'Maharashtra'));
            $gstin = trim($this->request->post('gstin'));
            $drugLicense = trim($this->request->post('drug_license_no'));
            $terms = (int)$this->request->post('payment_terms_days', 30);

            $id = Database::table('suppliers')->insert([
                'name'               => $contactPerson ?: $companyName,
                'company_name'       => $companyName,
                'contact_person'     => $contactPerson,
                'phone'              => $phone,
                'email'              => $email,
                'address'            => $address,
                'city'               => $city,
                'state'              => $state,
                'gstin'              => $gstin,
                'drug_license_no'    => $drugLicense,
                'payment_terms_days' => $terms,
                'current_balance'    => 0,
                'is_active'          => 1,
                'created_at'         => date('Y-m-d H:i:s')
            ]);

            $this->logAudit('create_supplier', 'suppliers', $id, "Added supplier {$companyName}");
            $this->redirect(App::baseURL() . '/suppliers', 'success', "Supplier '{$companyName}' added.");
        }
    }
}
