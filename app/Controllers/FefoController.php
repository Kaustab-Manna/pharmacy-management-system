<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Config\App;

class FefoController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        // Get medicines with multiple active batches to verify FEFO prioritization
        $fefoAudit = Database::raw("SELECT m.id, m.name, m.brand_name, m.dosage_form,
                                   COUNT(b.id) as batch_count,
                                   SUM(b.quantity) as total_qty,
                                   MIN(b.expiry_date) as earliest_expiry,
                                   MAX(b.expiry_date) as latest_expiry
                                   FROM medicines m
                                   JOIN batches b ON m.id = b.medicine_id
                                   WHERE b.status = 'active' AND b.quantity > 0
                                   GROUP BY m.id
                                   ORDER BY earliest_expiry ASC");

        $fefoBatches = [];
        foreach ($fefoAudit as $row) {
            $fefoBatches[$row['id']] = Database::raw("SELECT * FROM batches WHERE medicine_id = ? AND status = 'active' AND quantity > 0 ORDER BY expiry_date ASC", [$row['id']]);
        }

        // FEFO Compliance Sales Movements: verify if sold batches adhered to earliest expiry
        $recentSalesItems = Database::raw("SELECT si.*, s.invoice_number, s.sale_date, m.brand_name, b.batch_number, b.expiry_date
                                          FROM sale_items si
                                          JOIN sales s ON si.sale_id = s.id
                                          JOIN medicines m ON si.medicine_id = m.id
                                          JOIN batches b ON si.batch_id = b.id
                                          ORDER BY s.sale_date DESC LIMIT 15");

        $this->render('inventory.fefo', [
            'pageTitle'        => 'FEFO / FIFO Engine & Compliance (Module 22) - INFOSOF',
            'activeModule'     => 'fefo',
            'fefoAudit'        => $fefoAudit,
            'fefoBatches'      => $fefoBatches,
            'recentSalesItems' => $recentSalesItems
        ]);
    }
}
