<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Config\App;

class AccountsController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        // Receivables: Customers with balance
        $receivables = Database::raw("SELECT p.*,
                                     (SELECT COUNT(*) FROM sales WHERE patient_id = p.id AND payment_status != 'paid') as due_invoices_count
                                     FROM patients p 
                                     WHERE p.outstanding_balance > 0 
                                     ORDER BY p.outstanding_balance DESC");

        // Payables: Suppliers with balance
        $payables = Database::raw("SELECT s.*,
                                   (SELECT COUNT(*) FROM purchases WHERE supplier_id = s.id AND payment_status != 'paid') as due_purchases_count
                                   FROM suppliers s 
                                   WHERE s.current_balance > 0 
                                   ORDER BY s.current_balance DESC");

        $totalReceivables = Database::raw("SELECT COALESCE(SUM(outstanding_balance), 0) as total FROM patients")[0]['total'];
        $totalPayables = Database::raw("SELECT COALESCE(SUM(current_balance), 0) as total FROM suppliers")[0]['total'];

        $this->render('accounts.index', [
            'pageTitle'        => 'Accounts Receivable & Payable (Module 26) - INFOSOF',
            'activeModule'     => 'accounts',
            'receivables'      => $receivables,
            'payables'         => $payables,
            'totalReceivables' => $totalReceivables,
            'totalPayables'    => $totalPayables
        ]);
    }

    public function collectCustomerPayment(): void
    {
        $this->checkPermission('accounts', 'create');

        if ($this->request->isPost()) {
            $patientId = (int)$this->request->post('patient_id');
            $amount = (float)$this->request->post('amount');
            $paymentMode = $this->request->post('payment_mode', 'cash');
            $notes = trim($this->request->post('notes', ''));

            $patient = Database::table('patients')->where('id', $patientId)->first();
            if ($patient && $amount > 0) {
                $newBal = max(0, $patient['outstanding_balance'] - $amount);
                Database::table('patients')->where('id', $patientId)->update(['outstanding_balance' => $newBal]);

                // Record financial transaction
                Database::table('financial_transactions')->insert([
                    'trans_code'     => 'RCP-' . time(),
                    'trans_type'     => 'customer_collection',
                    'entity_type'    => 'customer',
                    'entity_id'      => $patientId,
                    'entity_name'    => $patient['name'],
                    'amount'         => $amount,
                    'payment_mode'   => $paymentMode,
                    'trans_date'     => date('Y-m-d'),
                    'description'    => "Collected from {$patient['name']}: {$notes}",
                    'created_by'     => $this->getUser()['id'] ?? null,
                    'created_at'     => date('Y-m-d H:i:s')
                ]);

                $this->logAudit('collect_payment', 'accounts', $patientId, "Collected {$amount} from patient {$patient['name']}");
                $this->redirect(App::baseURL() . '/accounts', 'success', "Recorded collection of ₹" . number_format($amount, 2) . " from {$patient['name']}.");
            }
        }
        $this->redirect(App::baseURL() . '/accounts');
    }

    public function paySupplier(): void
    {
        $this->checkPermission('accounts', 'create');

        if ($this->request->isPost()) {
            $supplierId = (int)$this->request->post('supplier_id');
            $amount = (float)$this->request->post('amount');
            $paymentMode = $this->request->post('payment_mode', 'bank_transfer');
            $refNo = trim($this->request->post('reference_no', ''));
            $notes = trim($this->request->post('notes', ''));

            $supplier = Database::table('suppliers')->where('id', $supplierId)->first();
            if ($supplier && $amount > 0) {
                $newBal = max(0, $supplier['current_balance'] - $amount);
                Database::table('suppliers')->where('id', $supplierId)->update(['current_balance' => $newBal]);

                Database::table('financial_transactions')->insert([
                    'trans_code'     => 'PMT-' . time(),
                    'trans_type'     => 'supplier_payment',
                    'entity_type'    => 'supplier',
                    'entity_id'      => $supplierId,
                    'entity_name'    => $supplier['company_name'],
                    'amount'         => $amount,
                    'payment_mode'   => $paymentMode,
                    'trans_date'     => date('Y-m-d'),
                    'description'    => "Paid to {$supplier['company_name']} (Ref: {$refNo}): {$notes}",
                    'created_by'     => $this->getUser()['id'] ?? null,
                    'created_at'     => date('Y-m-d H:i:s')
                ]);

                $this->logAudit('pay_supplier', 'accounts', $supplierId, "Paid {$amount} to supplier {$supplier['company_name']}");
                $this->redirect(App::baseURL() . '/accounts', 'success', "Recorded payment of ₹" . number_format($amount, 2) . " to {$supplier['company_name']}.");
            }
        }
        $this->redirect(App::baseURL() . '/accounts');
    }
}
