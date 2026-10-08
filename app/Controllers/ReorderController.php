<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Config\App;

class ReorderController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $today = date('Y-m-d');
        $thirtyDaysAgo = date('Y-m-d', strtotime('-30 days'));

        $medicines = Database::raw("SELECT m.*, c.name as category_name,
                      COALESCE((SELECT SUM(b.quantity) FROM batches b WHERE b.medicine_id = m.id AND b.status = 'active'), 0) as current_stock,
                      COALESCE((SELECT SUM(si.quantity) FROM sale_items si JOIN sales s ON si.sale_id = s.id WHERE si.medicine_id = m.id AND s.sale_date >= ?), 0) as sold_last_30_days,
                      (SELECT b.purchase_price FROM batches b WHERE b.medicine_id = m.id AND b.status = 'active' ORDER BY b.id DESC LIMIT 1) as latest_purchase_rate
                      FROM medicines m
                      LEFT JOIN categories c ON m.category_id = c.id
                      WHERE m.is_active = 1
                      ORDER BY current_stock ASC", [$thirtyDaysAgo]);

        $reorderList = [];
        $leadTimeDays = 10; // Standard supplier fulfillment lead time

        foreach ($medicines as $med) {
            $currentStock = (int)$med['current_stock'];
            $minStock = (int)$med['min_stock_level'];
            $reorderLevel = (int)$med['reorder_level'];
            
            // Calculate sales velocity (avg daily sales)
            $totalSold = (int)$med['sold_last_30_days'];
            $dailySales = round($totalSold / 30, 1);
            if ($dailySales <= 0.1) {
                // Heuristic baseline if new item
                $dailySales = max(1.0, round($reorderLevel / 15, 1));
            }

            // Formula: Suggested Order = (Daily Sales * Lead Time) + Reorder Buffer - Current Stock
            $targetLevel = ceil($dailySales * $leadTimeDays) + $reorderLevel;
            $suggestedOrder = max(0, $targetLevel - $currentStock);

            // Determine urgency
            if ($currentStock <= 0) {
                $status = 'OUT_OF_STOCK';
                $urgency = 'danger';
            } elseif ($currentStock <= $minStock) {
                $status = 'CRITICAL';
                $urgency = 'danger';
            } elseif ($currentStock <= $reorderLevel) {
                $status = 'REORDER_NOW';
                $urgency = 'warning';
            } else {
                $status = 'ADEQUATE';
                $urgency = 'success';
            }

            $reorderList[] = array_merge($med, [
                'daily_sales'     => $dailySales,
                'suggested_order' => $suggestedOrder,
                'status_label'    => $status,
                'urgency'         => $urgency
            ]);
        }

        $suppliers = Database::table('suppliers')->where('is_active', 1)->get();

        $this->render('reorder.index', [
            'pageTitle'    => 'Smart Reorder Management (Module 21) - INFOSOF',
            'activeModule' => 'reorder',
            'reorderList'  => $reorderList,
            'suppliers'    => $suppliers
        ]);
    }
}
