<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Config\App;

class SalesController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $search = trim($this->request->get('search', ''));
        $paymentStatus = $this->request->get('status');
        $dateFrom = $this->request->get('date_from', date('Y-m-d', strtotime('-30 days')));
        $dateTo = $this->request->get('date_to', date('Y-m-d'));

        $query = "SELECT s.*, p.name as patient_name, p.phone as patient_phone, u.full_name as cashier_name,
                         (SELECT COUNT(*) FROM sale_items si WHERE si.sale_id = s.id) as item_count
                  FROM sales s
                  LEFT JOIN patients p ON s.patient_id = p.id
                  LEFT JOIN users u ON s.cashier_id = u.id
                  WHERE DATE(s.sale_date) BETWEEN ? AND ?";

        $params = [$dateFrom, $dateTo];

        if ($search !== '') {
            $query .= " AND (s.invoice_number LIKE ? OR s.customer_name LIKE ? OR s.customer_phone LIKE ?)";
            $wildcard = "%{$search}%";
            $params = array_merge($params, [$wildcard, $wildcard, $wildcard]);
        }

        if (!empty($paymentStatus)) {
            $query .= " AND s.payment_status = ?";
            $params[] = $paymentStatus;
        }

        $query .= " ORDER BY s.sale_date DESC";
        $sales = Database::raw($query, $params);

        $summary = Database::raw("SELECT COUNT(*) as count, COALESCE(SUM(grand_total), 0) as total, COALESCE(SUM(tax_amount), 0) as tax, COALESCE(SUM(discount_amount), 0) as discount FROM sales WHERE DATE(sale_date) BETWEEN ? AND ?", [$dateFrom, $dateTo])[0];

        $this->render('sales.index', [
            'pageTitle'     => 'Medicine Sales Register & Invoices - INFOSOF',
            'activeModule'  => 'sales',
            'sales'         => $sales,
            'summary'       => $summary,
            'search'        => $search,
            'paymentStatus' => $paymentStatus,
            'dateFrom'      => $dateFrom,
            'dateTo'        => $dateTo
        ]);
    }

    public function invoice(int $id): void
    {
        $this->requireAuth();

        $sale = Database::raw("SELECT s.*, p.name as patient_name, p.phone as patient_phone, p.address as patient_address,
                               d.name as doctor_name, d.registration_number as doctor_reg, u.full_name as cashier_name
                               FROM sales s
                               LEFT JOIN patients p ON s.patient_id = p.id
                               LEFT JOIN doctors d ON s.doctor_id = d.id
                               LEFT JOIN users u ON s.cashier_id = u.id
                               WHERE s.id = ?", [$id])[0] ?? null;

        if (!$sale) {
            $this->redirect(App::baseURL() . '/sales', 'error', 'Invoice not found.');
        }

        $items = Database::raw("SELECT si.*, m.name as medicine_name, m.brand_name, m.hsn_code, m.dosage_form, m.strength, m.composition, m.pack_size, m.unit,
                                b.batch_number, b.expiry_date
                                FROM sale_items si
                                JOIN medicines m ON si.medicine_id = m.id
                                JOIN batches b ON si.batch_id = b.id
                                WHERE si.sale_id = ?", [$id]);

        $profile = Database::table('pharmacy_profile')->first();
        $format = $this->request->get('format', 'standard');

        $this->render('sales.invoice', [
            'pageTitle'    => 'Tax Invoice #' . $sale['invoice_number'],
            'activeModule' => 'sales',
            'sale'         => $sale,
            'items'        => $items,
            'pharmacy'     => $profile,
            'format'       => $format
        ], $format !== 'thermal' && $format !== 'standard'); // layout disabled on direct print format
    }
}
