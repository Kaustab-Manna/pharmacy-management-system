<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Config\App;

class ReportsController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $reportType = $this->request->get('type', 'daily_sales');
        $dateFrom = $this->request->get('date_from', date('Y-m-01'));
        $dateTo = $this->request->get('date_to', date('Y-m-d'));

        $reportData = [];

        switch ($reportType) {
            case 'daily_sales':
                $reportData = Database::raw("SELECT DATE(sale_date) as report_date, COUNT(*) as invoice_count,
                                             SUM(subtotal) as subtotal, SUM(tax_amount) as tax_amount,
                                             SUM(discount_amount) as discount, SUM(grand_total) as grand_total
                                             FROM sales
                                             WHERE DATE(sale_date) BETWEEN ? AND ?
                                             GROUP BY DATE(sale_date)
                                             ORDER BY report_date DESC", [$dateFrom, $dateTo]);
                break;

            case 'monthly_sales':
                $reportData = Database::raw("SELECT strftime('%Y-%m', sale_date) as report_month,
                                             COUNT(*) as invoice_count, SUM(grand_total) as grand_total,
                                             SUM(tax_amount) as tax_total
                                             FROM sales
                                             GROUP BY report_month
                                             ORDER BY report_month DESC");
                break;

            case 'medicine_sales':
                $reportData = Database::raw("SELECT m.name as medicine_name, m.brand_name, m.manufacturer,
                                             SUM(si.quantity) as total_units_sold,
                                             SUM(si.total_amount) as total_revenue
                                             FROM sale_items si
                                             JOIN medicines m ON si.medicine_id = m.id
                                             JOIN sales s ON si.sale_id = s.id
                                             WHERE DATE(s.sale_date) BETWEEN ? AND ?
                                             GROUP BY m.id
                                             ORDER BY total_revenue DESC LIMIT 25", [$dateFrom, $dateTo]);
                break;

            case 'manufacturer_sales':
                $reportData = Database::raw("SELECT m.manufacturer, COUNT(DISTINCT si.sale_id) as sales_count,
                                             SUM(si.quantity) as units_sold,
                                             SUM(si.total_amount) as total_revenue
                                             FROM sale_items si
                                             JOIN medicines m ON si.medicine_id = m.id
                                             JOIN sales s ON si.sale_id = s.id
                                             WHERE DATE(s.sale_date) BETWEEN ? AND ?
                                             GROUP BY m.manufacturer
                                             ORDER BY total_revenue DESC", [$dateFrom, $dateTo]);
                break;

            case 'doctor_prescriptions':
                $reportData = Database::raw("SELECT d.name as doctor_name, d.specialization, d.hospital_clinic,
                                             COUNT(s.id) as prescription_sales_count,
                                             COALESCE(SUM(s.grand_total), 0) as total_generated_sales
                                             FROM doctors d
                                             LEFT JOIN sales s ON d.id = s.doctor_id AND DATE(s.sale_date) BETWEEN ? AND ?
                                             GROUP BY d.id
                                             ORDER BY total_generated_sales DESC", [$dateFrom, $dateTo]);
                break;

            case 'profit_report':
                $salesWithCogs = Database::raw("SELECT s.invoice_number, s.sale_date, s.grand_total as revenue,
                                                COALESCE(SUM(si.quantity * b.purchase_price), 0) as cogs,
                                                s.grand_total - COALESCE(SUM(si.quantity * b.purchase_price), 0) as gross_profit
                                                FROM sales s
                                                JOIN sale_items si ON s.id = si.sale_id
                                                JOIN batches b ON si.batch_id = b.id
                                                WHERE DATE(s.sale_date) BETWEEN ? AND ?
                                                GROUP BY s.id
                                                ORDER BY s.sale_date DESC", [$dateFrom, $dateTo]);
                $reportData = $salesWithCogs;
                break;

            case 'supplier_purchases':
                $reportData = Database::raw("SELECT s.company_name, COUNT(p.id) as invoice_count,
                                             COALESCE(SUM(p.grand_total), 0) as total_procured,
                                             s.current_balance as current_due
                                             FROM suppliers s
                                             LEFT JOIN purchases p ON s.id = p.supplier_id AND p.invoice_date BETWEEN ? AND ?
                                             GROUP BY s.id
                                             ORDER BY total_procured DESC", [$dateFrom, $dateTo]);
                break;
        }

        $this->render('reports.index', [
            'pageTitle'    => 'Reports & Business Analytics (Module 33) - INFOSOF',
            'activeModule' => 'reports',
            'reportType'   => $reportType,
            'dateFrom'     => $dateFrom,
            'dateTo'       => $dateTo,
            'reportData'   => $reportData
        ]);
    }
}
