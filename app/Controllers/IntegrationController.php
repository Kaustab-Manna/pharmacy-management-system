<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Config\App;

class IntegrationController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $recentSales = Database::table('sales')->whereNotNull('customer_phone')->orderBy('sale_date', 'DESC')->limit(15)->get();
        $duePatients = Database::raw("SELECT * FROM patients WHERE outstanding_balance > 0 ORDER BY outstanding_balance DESC LIMIT 15");

        $this->render('integration.index', [
            'pageTitle'    => 'WhatsApp / SMS / Email Gateway (Module 30) - INFOSOF',
            'activeModule' => 'integration',
            'recentSales'  => $recentSales,
            'duePatients'  => $duePatients
        ]);
    }
}
