<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Config\App;

class GstController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $month = $this->request->get('month', date('Y-m'));
        $monthStart = $month . '-01 00:00:00';
        $monthEnd = date('Y-m-t 23:59:59', strtotime($monthStart));

        // Sales Tax (GSTR-1 Summary)
        $salesTaxSummary = Database::raw("SELECT si.gst_rate, 
                                          COUNT(DISTINCT si.sale_id) as invoice_count,
                                          COALESCE(SUM(si.quantity * si.unit_price), 0) as taxable_amount,
                                          COALESCE(SUM(si.cgst_amount), 0) as total_cgst,
                                          COALESCE(SUM(si.sgst_amount), 0) as total_sgst,
                                          COALESCE(SUM(si.igst_amount), 0) as total_igst,
                                          COALESCE(SUM(si.total_amount), 0) as total_amount
                                          FROM sale_items si
                                          JOIN sales s ON si.sale_id = s.id
                                          WHERE s.sale_date BETWEEN ? AND ?
                                          GROUP BY si.gst_rate
                                          ORDER BY si.gst_rate ASC", [$monthStart, $monthEnd]);

        // Purchase Tax (GSTR-2 Summary / Input Tax Credit - ITC)
        $purchaseTaxSummary = Database::raw("SELECT pi.gst_rate,
                                             COALESCE(SUM(pi.quantity * pi.purchase_rate), 0) as taxable_amount,
                                             COALESCE(SUM(pi.cgst_amount), 0) as itc_cgst,
                                             COALESCE(SUM(pi.sgst_amount), 0) as itc_sgst,
                                             COALESCE(SUM(pi.total_amount), 0) as total_amount
                                             FROM purchase_items pi
                                             JOIN purchases p ON pi.purchase_id = p.id
                                             WHERE p.invoice_date BETWEEN ? AND ?
                                             GROUP BY pi.gst_rate
                                             ORDER BY pi.gst_rate ASC", [substr($monthStart, 0, 10), substr($monthEnd, 0, 10)]);

        // HSN Code Summary
        $hsnSummary = Database::raw("SELECT m.hsn_code, si.gst_rate,
                                    COALESCE(SUM(si.quantity), 0) as total_qty,
                                    COALESCE(SUM(si.quantity * si.unit_price), 0) as taxable_val,
                                    COALESCE(SUM(si.cgst_amount + si.sgst_amount), 0) as total_tax
                                    FROM sale_items si
                                    JOIN medicines m ON si.medicine_id = m.id
                                    JOIN sales s ON si.sale_id = s.id
                                    WHERE s.sale_date BETWEEN ? AND ?
                                    GROUP BY m.hsn_code, si.gst_rate", [$monthStart, $monthEnd]);

        $this->render('gst.index', [
            'pageTitle'          => 'GST & Tax Compliance Center (Module 24) - INFOSOF',
            'activeModule'       => 'gst',
            'month'              => $month,
            'salesTaxSummary'    => $salesTaxSummary,
            'purchaseTaxSummary' => $purchaseTaxSummary,
            'hsnSummary'         => $hsnSummary
        ]);
    }
}
