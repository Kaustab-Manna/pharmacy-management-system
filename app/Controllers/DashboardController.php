<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Config\App;

class DashboardController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $today = date('Y-m-d');
        $curDateStart = $today . ' 00:00:00';
        $curDateEnd = $today . ' 23:59:59';
        $nearExpiryThreshold = date('Y-m-d', strtotime('+30 days'));

        // 1. Today's sales
        $todaySalesRow = Database::raw("SELECT COUNT(*) as count, COALESCE(SUM(grand_total), 0) as total FROM sales WHERE sale_date BETWEEN ? AND ?", [$curDateStart, $curDateEnd])[0] ?? ['count' => 0, 'total' => 0];

        // 2. Today's purchases
        $todayPurchasesRow = Database::raw("SELECT COUNT(*) as count, COALESCE(SUM(grand_total), 0) as total FROM purchases WHERE invoice_date = ?", [$today])[0] ?? ['count' => 0, 'total' => 0];

        // 3. Total stock value (at purchase price & at MRP)
        $stockValuation = Database::raw("SELECT COALESCE(SUM(quantity * purchase_price), 0) as cost_val, COALESCE(SUM(quantity * mrp), 0) as mrp_val, COALESCE(SUM(quantity), 0) as total_units FROM batches WHERE status = 'active'")[0] ?? ['cost_val' => 0, 'mrp_val' => 0, 'total_units' => 0];

        // 4. Low-stock medicines count
        $lowStockCount = Database::raw("SELECT COUNT(*) as cnt FROM medicines m WHERE is_active = 1 AND (SELECT COALESCE(SUM(quantity), 0) FROM batches b WHERE b.medicine_id = m.id AND b.status = 'active') <= m.min_stock_level")[0]['cnt'] ?? 0;

        // 5. Near-expiry medicines count (<= 30 days)
        $nearExpiryCount = Database::raw("SELECT COUNT(*) as cnt FROM batches WHERE status = 'active' AND expiry_date <= ? AND expiry_date >= ?", [$nearExpiryThreshold, $today])[0]['cnt'] ?? 0;

        // 6. Expired medicines count
        $expiredCount = Database::raw("SELECT COUNT(*) as cnt FROM batches WHERE status = 'expired' OR (status = 'active' AND expiry_date < ?)", [$today])[0]['cnt'] ?? 0;

        // 7. Pending purchase orders count
        $pendingPoCount = Database::table('purchase_orders')->where('status', 'draft')->orWhere('status', 'pending_approval')->count();

        // 8. Outstanding supplier payments (Accounts Payable)
        $supplierOutstanding = Database::raw("SELECT COALESCE(SUM(current_balance), 0) as total FROM suppliers")[0]['total'] ?? 0;

        // 9. Customer receivables (Accounts Receivable)
        $customerReceivables = Database::raw("SELECT COALESCE(SUM(outstanding_balance), 0) as total FROM patients")[0]['total'] ?? 0;

        // 10. Daily profit indicator (Today's Sales Revenue - Cost of Goods Sold)
        $todayCogs = Database::raw("SELECT COALESCE(SUM(si.quantity * b.purchase_price), 0) as cogs FROM sale_items si JOIN batches b ON si.batch_id = b.id JOIN sales s ON si.sale_id = s.id WHERE s.sale_date BETWEEN ? AND ?", [$curDateStart, $curDateEnd])[0]['cogs'] ?? 0;
        $todayProfit = max(0, (float)$todaySalesRow['total'] - (float)$todayCogs);
        $profitMargin = $todaySalesRow['total'] > 0 ? round(($todayProfit / $todaySalesRow['total']) * 100, 1) : 0;

        // 11. Prescription sales count
        $prescriptionSalesCount = Database::raw("SELECT COUNT(*) as cnt FROM sales WHERE prescription_id IS NOT NULL AND sale_date BETWEEN ? AND ?", [$curDateStart, $curDateEnd])[0]['cnt'] ?? 0;

        // 12. Recent transactions (Last 10 sales)
        $recentTransactions = Database::raw("SELECT s.*, p.name as patient_name FROM sales s LEFT JOIN patients p ON s.patient_id = p.id ORDER BY s.sale_date DESC LIMIT 8");

        // Fast reorder alerts list
        $criticalReorders = Database::raw("SELECT m.name, m.code, m.pack_size, m.min_stock_level, COALESCE(SUM(b.quantity), 0) as current_stock FROM medicines m LEFT JOIN batches b ON m.id = b.medicine_id AND b.status = 'active' WHERE m.is_active = 1 GROUP BY m.id HAVING current_stock <= m.min_stock_level LIMIT 5");

        // Expiring batches list
        $urgentExpiringBatches = Database::raw("SELECT b.*, m.name as medicine_name FROM batches b JOIN medicines m ON b.medicine_id = m.id WHERE b.status = 'active' AND b.expiry_date <= ? ORDER BY b.expiry_date ASC LIMIT 5", [$nearExpiryThreshold]);

        $this->render('dashboard.index', [
            'pageTitle'              => 'Pharmacy Central Dashboard - INFOSOF',
            'activeModule'           => 'dashboard',
            'todaySales'             => $todaySalesRow,
            'todayPurchases'         => $todayPurchasesRow,
            'stockValuation'         => $stockValuation,
            'lowStockCount'          => $lowStockCount,
            'nearExpiryCount'        => $nearExpiryCount,
            'expiredCount'           => $expiredCount,
            'pendingPoCount'         => $pendingPoCount,
            'supplierOutstanding'    => $supplierOutstanding,
            'customerReceivables'    => $customerReceivables,
            'todayProfit'            => $todayProfit,
            'profitMargin'           => $profitMargin,
            'prescriptionSalesCount' => $prescriptionSalesCount,
            'recentTransactions'     => $recentTransactions,
            'criticalReorders'       => $criticalReorders,
            'urgentExpiringBatches'  => $urgentExpiringBatches,
        ]);
    }
}
