<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Config\App;

class ExpensesController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $expenses = Database::raw("SELECT e.*, u.full_name as created_by_name 
                                  FROM expenses e 
                                  LEFT JOIN users u ON e.created_by = u.id 
                                  ORDER BY e.expense_date DESC");

        // Expense categories breakdown
        $categoryBreakdown = Database::raw("SELECT category, COUNT(*) as count, SUM(amount) as total FROM expenses GROUP BY category");

        $totalExpenses = Database::raw("SELECT COALESCE(SUM(amount), 0) as total FROM expenses")[0]['total'];

        $this->render('expenses.index', [
            'pageTitle'         => 'Pharmacy Expense Tracker (Module 27) - INFOSOF',
            'activeModule'      => 'expenses',
            'expenses'          => $expenses,
            'categoryBreakdown' => $categoryBreakdown,
            'totalExpenses'     => $totalExpenses
        ]);
    }

    public function create(): void
    {
        $this->checkPermission('expenses', 'create');

        if ($this->request->isPost()) {
            $category = $this->request->post('category', 'Miscellaneous');
            $amount = (float)$this->request->post('amount');
            $expenseDate = $this->request->post('expense_date', date('Y-m-d'));
            $paymentMode = $this->request->post('payment_mode', 'cash');
            $payee = trim($this->request->post('payee', ''));
            $refNo = trim($this->request->post('reference_no', ''));
            $notes = trim($this->request->post('notes', ''));

            $receiptPath = null;
            $file = $this->request->getFile('receipt_file');
            if ($file && !empty($file['tmp_name'])) {
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $fileName = 'exp_' . time() . '.' . $ext;
                $targetDir = dirname(__DIR__, 2) . '/public/uploads/receipts';
                if (!is_dir($targetDir)) {
                    @mkdir($targetDir, 0777, true);
                }
                if (move_uploaded_file($file['tmp_name'], $targetDir . '/' . $fileName)) {
                    $receiptPath = 'uploads/receipts/' . $fileName;
                }
            }

            $id = Database::table('expenses')->insert([
                'expense_date' => $expenseDate,
                'category'     => $category,
                'amount'       => $amount,
                'payment_mode' => $paymentMode,
                'payee'        => $payee,
                'reference_no' => $refNo,
                'receipt_file' => $receiptPath,
                'notes'        => $notes,
                'created_by'   => $this->getUser()['id'] ?? null,
                'created_at'   => date('Y-m-d H:i:s')
            ]);

            $this->logAudit('create_expense', 'expenses', $id, "Added {$category} expense of {$amount} to {$payee}");
            $this->redirect(App::baseURL() . '/expenses', 'success', "Expense of ₹" . number_format($amount, 2) . " recorded.");
        }
    }
}
