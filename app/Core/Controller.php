<?php
namespace App\Core;

use App\Config\App;

abstract class Controller
{
    protected Request $request;
    protected Response $response;

    public function __construct()
    {
        $this->request = new Request();
        $this->response = new Response();
        Session::start();
    }

    protected function isLoggedIn(): bool
    {
        return Session::has('user_id');
    }

    protected function getUser(): ?array
    {
        if (!$this->isLoggedIn()) {
            return null;
        }
        return [
            'id'        => Session::get('user_id'),
            'username'  => Session::get('username'),
            'full_name' => Session::get('full_name'),
            'role'      => Session::get('role'),
            'email'     => Session::get('email'),
        ];
    }

    protected function requireAuth(): void
    {
        if (!$this->isLoggedIn()) {
            Session::setFlash('error', 'Please log in to access the system.');
            $this->redirect(App::baseURL() . '/login');
        }
    }

    public function hasPermission(string $module, string $action = 'view'): bool
    {
        $user = $this->getUser();
        if (!$user) return false;

        $role = $user['role'];
        if ($role === 'super_admin' || $role === 'superadmin' || $role === 'pharmacy_admin') {
            return true;
        }

        // Dashboard and profile are accessible to all authenticated staff
        if (in_array($module, ['dashboard', 'profile'])) {
            return true;
        }

        // 1. Check if specific permissions are configured in DB for this role
        try {
            $hasDbConfig = Database::table('role_permissions')->where('role', $role)->first();
            if ($hasDbConfig) {
                $perm = Database::table('role_permissions')
                    ->where('role', $role)
                    ->where('module', $module)
                    ->first();

                if ($perm) {
                    $col = 'can_' . $action;
                    return !empty($perm[$col]);
                }
                return false; // Role has custom DB matrix configured, but this module is not granted
            }
        } catch (\Exception $e) {
            // fallback to code rules
        }

        // 2. Default standard access rules (fallback when no custom DB permissions have been saved)
        $roleRules = [
            'pharmacist' => [
                'medicines', 'categories', 'batches', 'expiry', 'barcode',
                'prescriptions', 'prescription_billing', 'pos', 'sales', 'sales_returns',
                'inventory', 'fefo', 'reorder', 'documents', 'patients'
            ],
            'billing_executive' => [
                'pos', 'sales', 'sales_returns', 'patients', 'prescriptions',
                'prescription_billing', 'payments', 'barcode'
            ],
            'cashier' => [
                'pos', 'sales', 'sales_returns', 'patients', 'payments'
            ],
            'purchase_manager' => [
                'purchases', 'purchase_orders', 'purchase_returns', 'suppliers',
                'inventory', 'reorder', 'pricing', 'medicines'
            ],
            'store_manager' => [
                'inventory', 'batches', 'expiry', 'medicines', 'categories',
                'reorder', 'fefo', 'barcode', 'delivery'
            ],
            'accountant' => [
                'accounts', 'expenses', 'reports', 'gst', 'sales', 'purchases',
                'payments'
            ],
            'sales_staff' => [
                'pos', 'sales', 'patients', 'loyalty', 'online_orders'
            ]
        ];

        return in_array($module, $roleRules[$role] ?? []);
    }

    protected function checkPermission(string $module, string $action = 'view'): void
    {
        $this->requireAuth();
        if (!$this->hasPermission($module, $action)) {
            Session::setFlash('error', "Access Denied: You do not have permission to {$action} in {$module}.");
            $this->redirect(App::baseURL() . '/dashboard');
        }
    }

    protected function logAudit(string $action, string $module, ?int $recordId = null, string $details = ''): void
    {
        try {
            $user = $this->getUser();
            Database::table('audit_logs')->insert([
                'user_id'    => $user['id'] ?? null,
                'username'   => $user['username'] ?? 'guest',
                'action'     => $action,
                'module'     => $module,
                'record_id'  => $recordId,
                'ip_address' => $this->request->getIp(),
                'user_agent' => substr($this->request->getUserAgent(), 0, 250),
                'details'    => $details,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            error_log("Audit log failed: " . $e->getMessage());
        }
    }

    protected function render(string $viewPath, array $data = [], bool $withLayout = true): void
    {
        // Extract data variables
        extract($data);

        // System-wide standard variables
        $currentUser = $this->getUser();
        $appName = App::$appName;
        $baseURL = App::baseURL();

        // Get pharmacy profile
        $pharmacy = Database::table('pharmacy_profile')->first() ?? [
            'pharmacy_name'   => 'INFOSOF Care Pharmacy',
            'currency_symbol' => '₹',
            'gstin'           => '27AAACI1234F1Z9',
            'phone'           => '+91 98200 12345'
        ];

        // Get quick notification counts
        $unreadAlertsCount = 0;
        $lowStockCount = 0;
        $nearExpiryCount = 0;

        try {
            $unreadAlertsCount = Database::table('notifications')->where('is_read', 0)->count();
            $nearExpiryCount = Database::table('batches')
                ->where('status', 'active')
                ->where('expiry_date', '<=', date('Y-m-d', strtotime('+30 days')))
                ->count();
            $lowStockCount = Database::table('medicines')
                ->where('is_active', 1)
                ->whereRaw("id IN (SELECT medicine_id FROM batches GROUP BY medicine_id HAVING SUM(quantity) <= medicines.min_stock_level)")
                ->count();
        } catch (\Exception $e) {
            // Non-critical
        }

        $flashSuccess = Session::getFlash('success');
        $flashError = Session::getFlash('error');
        $flashWarning = Session::getFlash('warning');

        $viewFile = dirname(__DIR__) . '/Views/' . str_replace('.', '/', $viewPath) . '.php';

        if (!file_exists($viewFile)) {
            echo "<h1>View file not found: {$viewPath}</h1>";
            return;
        }

        if ($withLayout) {
            require dirname(__DIR__) . '/Views/layouts/header.php';
            require dirname(__DIR__) . '/Views/layouts/sidebar.php';
            require dirname(__DIR__) . '/Views/layouts/topbar.php';
            require $viewFile;
            require dirname(__DIR__) . '/Views/layouts/footer.php';
        } else {
            require $viewFile;
        }
    }

    protected function json(mixed $data, int $statusCode = 200): void
    {
        $this->response->json($data, $statusCode);
    }

    protected function redirect(string $url, ?string $flashType = null, ?string $flashMsg = null): void
    {
        if ($flashType && $flashMsg) {
            Session::setFlash($flashType, $flashMsg);
        }
        $this->response->redirect($url);
    }
}
