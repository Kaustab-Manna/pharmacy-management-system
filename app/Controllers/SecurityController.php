<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Config\App;

class SecurityController extends Controller
{
    private array $roles = [
        'pharmacist'        => 'Pharmacist',
        'store_manager'     => 'Store / Warehouse Manager',
        'purchase_manager'  => 'Purchase Manager',
        'billing_executive' => 'Billing Executive',
        'cashier'           => 'Cashier',
        'accountant'        => 'Accountant',
        'sales_staff'       => 'Sales / Counter Staff'
    ];

    private array $moduleGroups = [
        '💊 Medicines & Catalog' => [
            'medicines'   => ['title' => 'Medicine Master', 'desc' => 'Drug catalog, compositions, dosage forms & HSN codes'],
            'categories'  => ['title' => 'Categories & Classification', 'desc' => 'Therapeutic groups and classifications'],
            'batches'     => ['title' => 'Batch Management', 'desc' => 'Batch numbering, physical stock count & reconciliation'],
            'expiry'      => ['title' => 'Expiry Tracking', 'desc' => '30/60/90 days near-expiry alerts & write-offs'],
            'barcode'     => ['title' => 'Barcode & Scanning', 'desc' => 'Hardware scanner integration and label printing'],
            'fefo'        => ['title' => 'FEFO / FIFO System', 'desc' => 'First-Expiry First-Out dispensing enforcement'],
            'pricing'     => ['title' => 'Pricing & Margins', 'desc' => 'Price tiers, markup formulas and discount limits']
        ],
        '⚡ Sales & POS Billing' => [
            'pos'                  => ['title' => 'POS Counter (F2)', 'desc' => 'Fast billing counter, shortcut keys & cash drawer'],
            'sales'                => ['title' => 'Sales Invoices', 'desc' => 'Tax invoice history, reprints & duplicate copies'],
            'sales_returns'        => ['title' => 'Sales Returns', 'desc' => 'Customer returns, credit notes & refund processing'],
            'prescription_billing' => ['title' => 'Rx Dispensing Billing', 'desc' => 'Direct dispensing from prescription records'],
            'payments'             => ['title' => 'Payments & Cash Inflow', 'desc' => 'UPI, card, credit & split-tender collection']
        ],
        '📦 Purchases & Inventory' => [
            'purchases'        => ['title' => 'Purchase Invoices (GRN)', 'desc' => 'Goods Receipt Note, batch entry & MRP verification'],
            'purchase_orders'  => ['title' => 'Purchase Orders (PO)', 'desc' => 'Vendor purchase ordering and procurement approval'],
            'purchase_returns' => ['title' => 'Purchase Returns', 'desc' => 'Supplier debit notes & expired batch returns'],
            'suppliers'        => ['title' => 'Suppliers Directory', 'desc' => 'Vendor records, drug licenses & GSTIN details'],
            'inventory'        => ['title' => 'Inventory Valuation', 'desc' => 'Stock valuation, slow-moving items & reorder flags'],
            'reorder'          => ['title' => 'Auto-Reorder Engine', 'desc' => 'Safety threshold monitoring & PO auto-generation']
        ],
        '🩺 Clinical & Customers' => [
            'prescriptions' => ['title' => 'Prescriptions', 'desc' => 'Doctor prescriptions, digital scans & dosage directions'],
            'patients'      => ['title' => 'Patient Registry', 'desc' => 'Patient profiles, medical history & contact directory'],
            'doctors'       => ['title' => 'Doctors Directory', 'desc' => 'Medical council registration & specialty indexing'],
            'loyalty'       => ['title' => 'Loyalty & Rewards', 'desc' => 'Reward points calculation, coupons & promotional schemes']
        ],
        '💰 Accounts, Ledgers & Tax' => [
            'accounts'  => ['title' => 'Financial Ledgers', 'desc' => 'Cash book, bank accounts, journal & supplier ledger'],
            'expenses'  => ['title' => 'Operating Expenses', 'desc' => 'Daily petty cash, utilities & operational expense vouchers'],
            'gst'       => ['title' => 'GST & Tax Reports', 'desc' => 'GSTR-1, GSTR-2, B2B, B2C tax breakdowns & JSON exports']
        ],
        '⚙️ Operations & System Control' => [
            'online_orders' => ['title' => 'Online / E-Pharmacy', 'desc' => 'Web portal orders, customer prescription uploads & queue'],
            'delivery'      => ['title' => 'Delivery & Dispatch', 'desc' => 'Rider dispatch, home delivery tracking & COD status'],
            'documents'     => ['title' => 'Digital Archival', 'desc' => 'Uploaded prescription scans, drug licenses & tax certificates'],
            'reports'       => ['title' => 'BI Reports & Analytics', 'desc' => 'Revenue, profit, fast-moving items & ABC inventory reports'],
            'notifications' => ['title' => 'System Notifications', 'desc' => 'Automated alerts for stockout, expiry & approval requests'],
            'integration'   => ['title' => 'WhatsApp / SMS Integration', 'desc' => 'Automated SMS invoices, refill reminders & OTP messages'],
            'audit'         => ['title' => 'Audit Trail & Logs', 'desc' => 'Immutable audit history of user actions and IP logs'],
            'users'         => ['title' => 'User & Staff Management', 'desc' => 'Staff accounts, passwords & role assignments'],
            'settings'      => ['title' => 'Pharmacy Settings', 'desc' => 'Store details, GSTIN, invoice headers & printer setup'],
            'security'      => ['title' => 'Security & RBAC Matrix', 'desc' => 'Access control permissions and SQL backup dumps']
        ]
    ];

    public function index(): void
    {
        $this->checkPermission('security', 'view');

        $selectedRole = $this->request->get('role', 'pharmacist');
        if (!array_key_exists($selectedRole, $this->roles)) {
            $selectedRole = 'pharmacist';
        }

        $allUsers = Database::table('users')->orderBy('role', 'ASC')->get();
        $dbPerms = Database::table('role_permissions')->where('role', $selectedRole)->get();

        $permMap = [];
        foreach ($dbPerms as $p) {
            $permMap[$p['module']] = $p;
        }

        $hasCustomConfig = !empty($dbPerms);

        $this->render('security.index', [
            'pageTitle'       => 'Security & Role-Based Access Control (Module 36) - INFOSOF',
            'activeModule'    => 'security',
            'roles'           => $this->roles,
            'selectedRole'    => $selectedRole,
            'moduleGroups'    => $this->moduleGroups,
            'permMap'         => $permMap,
            'hasCustomConfig' => $hasCustomConfig,
            'users'           => $allUsers
        ]);
    }

    public function updatePermissions(): void
    {
        $this->checkPermission('security', 'edit');

        if ($this->request->isPost()) {
            $role = trim($this->request->post('role') ?? '');
            if (!array_key_exists($role, $this->roles)) {
                $this->redirect(App::baseURL() . '/security', 'error', 'Invalid role selected.');
                return;
            }

            $perms = $this->request->post('perms') ?? [];

            try {
                // Clear existing configured permissions for this role
                Database::raw("DELETE FROM role_permissions WHERE role = ?", [$role]);

                // Insert newly selected permissions
                foreach ($perms as $module => $actions) {
                    Database::table('role_permissions')->insert([
                        'role'        => $role,
                        'module'      => $module,
                        'can_view'    => !empty($actions['can_view']) ? 1 : 0,
                        'can_create'  => !empty($actions['can_create']) ? 1 : 0,
                        'can_edit'    => !empty($actions['can_edit']) ? 1 : 0,
                        'can_delete'  => !empty($actions['can_delete']) ? 1 : 0,
                        'can_approve' => !empty($actions['can_approve']) ? 1 : 0,
                        'can_export'  => !empty($actions['can_export']) ? 1 : 0,
                    ]);
                }

                $roleTitle = $this->roles[$role] ?? $role;
                $this->logAudit('update_permissions', 'security', null, "Configured module permissions for role: {$role}");
                $this->redirect(App::baseURL() . '/security?role=' . urlencode($role), 'success', "Access permissions for '{$roleTitle}' were saved successfully!");
                return;
            } catch (\Throwable $e) {
                $this->redirect(App::baseURL() . '/security?role=' . urlencode($role), 'error', "Error saving permissions: " . $e->getMessage());
                return;
            }
        }

        $this->redirect(App::baseURL() . '/security');
    }

    public function resetPermissions(): void
    {
        $this->checkPermission('security', 'edit');

        if ($this->request->isPost()) {
            $role = trim($this->request->post('role') ?? '');
            if (!array_key_exists($role, $this->roles)) {
                $this->redirect(App::baseURL() . '/security', 'error', 'Invalid role selected.');
                return;
            }

            try {
                Database::raw("DELETE FROM role_permissions WHERE role = ?", [$role]);
                $roleTitle = $this->roles[$role] ?? $role;
                $this->logAudit('reset_permissions', 'security', null, "Reset permissions to defaults for role: {$role}");
                $this->redirect(App::baseURL() . '/security?role=' . urlencode($role), 'success', "Permissions for '{$roleTitle}' reset to system default standards.");
                return;
            } catch (\Throwable $e) {
                $this->redirect(App::baseURL() . '/security?role=' . urlencode($role), 'error', "Error resetting permissions: " . $e->getMessage());
                return;
            }
        }

        $this->redirect(App::baseURL() . '/security');
    }

    public function downloadBackup(): void
    {
        $this->checkPermission('security', 'export');

        $driver = Database::getActiveDriver();
        $filename = 'infosof_pharmacy_backup_' . date('Y-m-d_H-i-s') . '.sql';

        header('Content-Type: text/plain; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        echo "-- ==========================================================\n";
        echo "-- INFOSOF TECHNOLOGIES - Pharmacy Management System SQL Dump\n";
        echo "-- Generated on: " . date('Y-m-d H:i:s') . "\n";
        echo "-- Database Driver: " . $driver . "\n";
        echo "-- ==========================================================\n\n";

        // Read schema and seed tables
        $tables = ['users', 'role_permissions', 'pharmacy_profile', 'categories', 'medicines', 'batches', 'suppliers', 'doctors', 'patients', 'prescriptions', 'prescription_items', 'purchase_orders', 'purchase_order_items', 'purchases', 'purchase_items', 'purchase_returns', 'purchase_return_items', 'sales', 'sale_items', 'sale_returns', 'sale_return_items', 'stock_movements', 'financial_transactions', 'expenses', 'loyalty_coupons', 'loyalty_logs', 'notifications', 'online_orders', 'deliveries', 'documents', 'audit_logs', 'settings'];

        foreach ($tables as $tbl) {
            try {
                $rows = Database::table($tbl)->get();
                if (!empty($rows)) {
                    echo "-- Dumping data for table `{$tbl}`\n";
                    foreach ($rows as $row) {
                        $cols = array_keys($row);
                        $escapedValues = array_map(function ($val) {
                            if ($val === null) return 'NULL';
                            return "'" . addslashes((string)$val) . "'";
                        }, array_values($row));
                        echo "INSERT INTO `{$tbl}` (`" . implode('`, `', $cols) . "`) VALUES (" . implode(', ', $escapedValues) . ");\n";
                    }
                    echo "\n";
                }
            } catch (\Exception $e) {
                // Table might not exist in some minimal environments
            }
        }
        exit;
    }
}
