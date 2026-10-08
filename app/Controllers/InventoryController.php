<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Config\App;

class InventoryController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $search = trim($this->request->get('search', ''));

        $query = "SELECT m.*, c.name as category_name,
                  COALESCE((SELECT SUM(b.quantity) FROM batches b WHERE b.medicine_id = m.id AND b.status = 'active'), 0) as current_stock,
                  COALESCE((SELECT SUM(b.quantity * b.purchase_price) FROM batches b WHERE b.medicine_id = m.id AND b.status = 'active'), 0) as stock_valuation_cost,
                  COALESCE((SELECT SUM(b.quantity * b.mrp) FROM batches b WHERE b.medicine_id = m.id AND b.status = 'active'), 0) as stock_valuation_mrp,
                  COALESCE((SELECT COUNT(*) FROM batches b WHERE b.medicine_id = m.id AND b.status = 'active'), 0) as batch_count,
                  COALESCE((SELECT MIN(b.expiry_date) FROM batches b WHERE b.medicine_id = m.id AND b.status = 'active' AND b.quantity > 0), NULL) as earliest_expiry
                  FROM medicines m
                  LEFT JOIN categories c ON m.category_id = c.id
                  WHERE m.is_active = 1";

        $params = [];
        if ($search !== '') {
            $query .= " AND (m.name LIKE ? OR m.brand_name LIKE ? OR m.code LIKE ?)";
            $wildcard = "%{$search}%";
            $params = [$wildcard, $wildcard, $wildcard];
        }

        $query .= " ORDER BY current_stock ASC";
        $inventory = Database::raw($query, $params);

        // Overall Valuation
        $totals = Database::raw("SELECT COALESCE(SUM(quantity * purchase_price), 0) as total_cost, COALESCE(SUM(quantity * mrp), 0) as total_mrp, COALESCE(SUM(quantity), 0) as total_units FROM batches WHERE status = 'active'")[0];

        // Stock movement ledger (last 20 entries)
        $movements = Database::raw("SELECT sm.*, m.brand_name, b.batch_number, u.full_name as user_name
                                   FROM stock_movements sm
                                   JOIN medicines m ON sm.medicine_id = m.id
                                   JOIN batches b ON sm.batch_id = b.id
                                   LEFT JOIN users u ON sm.user_id = u.id
                                   ORDER BY sm.created_at DESC LIMIT 20");

        $this->render('inventory.index', [
            'pageTitle'    => 'Real-Time Inventory & Valuation - INFOSOF',
            'activeModule' => 'inventory',
            'inventory'    => $inventory,
            'totals'       => $totals,
            'movements'    => $movements,
            'search'       => $search
        ]);
    }
}
