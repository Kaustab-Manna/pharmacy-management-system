<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Config\App;

class PaymentsController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $transactions = Database::raw("SELECT * FROM financial_transactions ORDER BY trans_date DESC, id DESC LIMIT 50");

        // Daily Cash Drawer / Register Calculations
        $today = date('Y-m-d');
        $cashSales = Database::raw("SELECT COALESCE(SUM(paid_amount), 0) as total FROM sales WHERE payment_mode = 'cash' AND DATE(sale_date) = ?", [$today])[0]['total'];
        $upiSales = Database::raw("SELECT COALESCE(SUM(paid_amount), 0) as total FROM sales WHERE payment_mode = 'upi' AND DATE(sale_date) = ?", [$today])[0]['total'];
        $cardSales = Database::raw("SELECT COALESCE(SUM(paid_amount), 0) as total FROM sales WHERE payment_mode = 'card' AND DATE(sale_date) = ?", [$today])[0]['total'];

        $todayExpenses = Database::raw("SELECT COALESCE(SUM(amount), 0) as total FROM expenses WHERE payment_mode = 'cash' AND expense_date = ?", [$today])[0]['total'];

        $netCashInDrawer = (float)$cashSales - (float)$todayExpenses;

        $this->render('accounts.payments', [
            'pageTitle'       => 'Payment Register & Cash Drawer (Module 25) - INFOSOF',
            'activeModule'    => 'payments',
            'transactions'    => $transactions,
            'cashSales'       => $cashSales,
            'upiSales'        => $upiSales,
            'cardSales'       => $cardSales,
            'todayExpenses'   => $todayExpenses,
            'netCashInDrawer' => $netCashInDrawer
        ]);
    }
}
