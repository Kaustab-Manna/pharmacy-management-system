<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Config\App;

class AuditController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $module = $this->request->get('module');
        $userFilter = $this->request->get('user_id');

        $query = "SELECT a.*, u.role as user_role FROM audit_logs a LEFT JOIN users u ON a.user_id = u.id WHERE 1=1";
        $params = [];

        if (!empty($module)) {
            $query .= " AND a.module = ?";
            $params[] = $module;
        }

        if (!empty($userFilter)) {
            $query .= " AND a.user_id = ?";
            $params[] = (int)$userFilter;
        }

        $query .= " ORDER BY a.created_at DESC LIMIT 100";
        $logs = Database::raw($query, $params);
        $users = Database::table('users')->get();

        $this->render('audit.index', [
            'pageTitle'    => 'Audit Trail & Compliance Activity Logs - INFOSOF',
            'activeModule' => 'audit',
            'logs'         => $logs,
            'users'        => $users,
            'moduleFilter' => $module,
            'userFilter'   => $userFilter
        ]);
    }

    public function delete(int|string $id): void
    {
        $this->requireAuth();
        $this->checkPermission('audit', 'delete');

        $id = (int)$id;
        $exists = Database::raw("SELECT id FROM audit_logs WHERE id = ?", [$id]);
        if (!empty($exists)) {
            Database::execute("DELETE FROM audit_logs WHERE id = ?", [$id]);
            $this->redirect(App::baseURL() . '/audit', 'success', "Audit log entry #{$id} was successfully deleted.");
        } else {
            $this->redirect(App::baseURL() . '/audit', 'error', "Audit log entry #{$id} not found.");
        }
    }

    public function clearAll(): void
    {
        $this->requireAuth();
        $this->checkPermission('audit', 'delete');

        $count = (int)Database::table('audit_logs')->count();
        Database::raw("DELETE FROM audit_logs");
        $this->redirect(App::baseURL() . '/audit', 'success', "Successfully deleted all {$count} audit log records.");
    }
}
