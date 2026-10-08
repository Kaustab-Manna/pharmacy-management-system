<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Config\App;

class ExpiryController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $days = (int)$this->request->get('days', 30);
        $today = date('Y-m-d');
        $targetDate = date('Y-m-d', strtotime("+{$days} days"));

        // Expiry Statistics
        $expiredStats = Database::raw("SELECT COUNT(*) as count, COALESCE(SUM(quantity * purchase_price), 0) as val, COALESCE(SUM(quantity), 0) as units FROM batches WHERE (status = 'expired' OR (status = 'active' AND expiry_date < ?)) AND quantity > 0", [$today])[0];

        $within30Stats = Database::raw("SELECT COUNT(*) as count, COALESCE(SUM(quantity * purchase_price), 0) as val, COALESCE(SUM(quantity), 0) as units FROM batches WHERE status = 'active' AND expiry_date >= ? AND expiry_date <= ? AND quantity > 0", [$today, date('Y-m-d', strtotime('+30 days'))])[0];

        $within60Stats = Database::raw("SELECT COUNT(*) as count, COALESCE(SUM(quantity * purchase_price), 0) as val, COALESCE(SUM(quantity), 0) as units FROM batches WHERE status = 'active' AND expiry_date > ? AND expiry_date <= ? AND quantity > 0", [date('Y-m-d', strtotime('+30 days')), date('Y-m-d', strtotime('+60 days'))])[0];

        $within90Stats = Database::raw("SELECT COUNT(*) as count, COALESCE(SUM(quantity * purchase_price), 0) as val, COALESCE(SUM(quantity), 0) as units FROM batches WHERE status = 'active' AND expiry_date > ? AND expiry_date <= ? AND quantity > 0", [date('Y-m-d', strtotime('+60 days')), date('Y-m-d', strtotime('+90 days'))])[0];

        // Fetch batches according to selected filter
        if ($this->request->get('status') === 'expired') {
            $batches = Database::raw("SELECT b.*, m.name as medicine_name, m.brand_name, m.manufacturer, m.unit FROM batches b JOIN medicines m ON b.medicine_id = m.id WHERE (b.status = 'expired' OR (b.status = 'active' AND b.expiry_date < ?)) AND b.quantity > 0 ORDER BY b.expiry_date ASC", [$today]);
        } else {
            $batches = Database::raw("SELECT b.*, m.name as medicine_name, m.brand_name, m.manufacturer, m.unit FROM batches b JOIN medicines m ON b.medicine_id = m.id WHERE b.status = 'active' AND b.expiry_date <= ? AND b.quantity > 0 ORDER BY b.expiry_date ASC", [$targetDate]);
        }

        $suppliers = Database::table('suppliers')->where('is_active', 1)->get();

        $this->render('expiry.index', [
            'pageTitle'     => 'Expiry Management & Loss Mitigation - INFOSOF',
            'activeModule'  => 'expiry',
            'expiredStats'  => $expiredStats,
            'within30Stats' => $within30Stats,
            'within60Stats' => $within60Stats,
            'within90Stats' => $within90Stats,
            'batches'       => $batches,
            'days'          => $days,
            'suppliers'     => $suppliers,
            'statusFilter'  => $this->request->get('status')
        ]);
    }

    public function writeOff(int $batchId): void
    {
        $this->checkPermission('expiry', 'edit');

        $batch = Database::table('batches')->where('id', $batchId)->first();
        if ($batch) {
            $qty = (int)$batch['quantity'];
            Database::table('batches')->where('id', $batchId)->update([
                'quantity' => 0,
                'status'   => 'expired'
            ]);

            Database::table('stock_movements')->insert([
                'medicine_id'   => $batch['medicine_id'],
                'batch_id'      => $batchId,
                'movement_type' => 'expiry_writeoff',
                'quantity'      => -$qty,
                'previous_qty'  => $qty,
                'new_qty'       => 0,
                'user_id'       => $this->getUser()['id'] ?? null,
                'notes'         => 'Expired stock quarantine & write-off',
                'created_at'    => date('Y-m-d H:i:s')
            ]);

            $this->logAudit('writeoff_expiry', 'expiry', $batchId, "Written off {$qty} units of batch {$batch['batch_number']}");
            $this->redirect(App::baseURL() . '/expiry', 'success', "Batch {$batch['batch_number']} quarantined and written off.");
        }
        $this->redirect(App::baseURL() . '/expiry');
    }
}
