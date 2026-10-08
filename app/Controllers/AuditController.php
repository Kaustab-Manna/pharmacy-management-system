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
            'pageTitle'    => 'Audit Trail & Compliance Activity Logs (Module 35) - INFOSOF',
            'activeModule' => 'audit',
            'logs'         => $logs,
            'users'        => $users,
            'moduleFilter' => $module,
            'userFilter'   => $userFilter
        ]);
    }
}
