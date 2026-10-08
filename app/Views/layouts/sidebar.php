<?php
$can = function(string $mod): bool {
    return $this->hasPermission($mod, 'view');
};

$cur = $activeModule ?? '';

// Check which dropdown is currently active to open it by default
$medModules   = ['medicines', 'categories', 'batches', 'expiry', 'barcode', 'fefo', 'pricing'];
$stockModules = ['inventory', 'reorder', 'purchase_orders', 'purchases', 'purchase_returns', 'suppliers'];
$clinModules  = ['prescriptions', 'prescription_billing', 'patients', 'doctors'];
$salesModules = ['sales', 'sales_returns', 'loyalty', 'online_orders', 'delivery'];
$finModules   = ['gst', 'payments', 'accounts', 'expenses', 'reports'];
$docModules   = ['integration', 'documents', 'notifications'];
$adminModules = ['users', 'audit', 'security', 'settings'];

$isMedOpen   = in_array($cur, $medModules);
$isStockOpen = in_array($cur, $stockModules);
$isClinOpen  = in_array($cur, $clinModules);
$isSalesOpen = in_array($cur, $salesModules);
$isFinOpen   = in_array($cur, $finModules);
$isDocOpen   = in_array($cur, $docModules);
$isAdminOpen = in_array($cur, $adminModules);
?>
<aside class="app-sidebar">
    <div class="sidebar-header">
        <div class="brand-icon">✚</div>
        <div class="brand-text">
            <h2>INFOSOF</h2>
            <span>Pharmacy Suite</span>
        </div>
    </div>

    <div class="sidebar-menu">
        <!-- Standalone: Dashboard -->
        <a href="<?= $baseURL ?>/dashboard" class="nav-item <?= $cur === 'dashboard' ? 'active' : '' ?>">
            <span class="nav-icon">📊</span>
            <span>Dashboard</span>
        </a>

        <!-- Standalone: Fast Counter POS -->
        <?php if ($can('pos')): ?>
            <a href="<?= $baseURL ?>/pos" class="nav-item <?= $cur === 'pos' ? 'active' : '' ?>" style="background: rgba(13, 148, 136, 0.2); border: 1px solid rgba(13, 148, 136, 0.4); margin-bottom: 8px;">
                <span class="nav-icon">⚡</span>
                <span style="font-weight: 700; color: #5eead4;">POS Counter (F2)</span>
                <span class="nav-badge badge-success">FAST</span>
            </a>
        <?php endif; ?>

        <!-- 1. Dropdown: Medicines & Catalog -->
        <?php if ($can('medicines') || $can('categories') || $can('batches') || $can('expiry') || $can('barcode') || $can('fefo') || $can('pricing')): ?>
            <div class="sidebar-dropdown <?= $isMedOpen ? 'open has-active' : '' ?>">
                <button type="button" class="sidebar-dropdown-header">
                    <div class="dropdown-title-group">
                        <span class="dropdown-icon">💊</span>
                        <span class="dropdown-title">Medicines & Catalog</span>
                    </div>
                    <div class="dropdown-meta">
                        <?php if (($nearExpiryCount ?? 0) > 0): ?>
                            <span class="nav-badge badge-warning"><?= $nearExpiryCount ?></span>
                        <?php endif; ?>
                        <span class="dropdown-arrow"></span>
                    </div>
                </button>
                <div class="sidebar-dropdown-content">
                    <?php if ($can('medicines')): ?>
                        <a href="<?= $baseURL ?>/medicines" class="nav-subitem <?= $cur === 'medicines' ? 'active' : '' ?>">
                            <span>Medicine Master</span>
                        </a>
                    <?php endif; ?>
                    <?php if ($can('categories')): ?>
                        <a href="<?= $baseURL ?>/categories" class="nav-subitem <?= $cur === 'categories' ? 'active' : '' ?>">
                            <span>Categories & Classification</span>
                        </a>
                    <?php endif; ?>
                    <?php if ($can('batches')): ?>
                        <a href="<?= $baseURL ?>/batches" class="nav-subitem <?= $cur === 'batches' ? 'active' : '' ?>">
                            <span>Batch Management</span>
                        </a>
                    <?php endif; ?>
                    <?php if ($can('expiry')): ?>
                        <a href="<?= $baseURL ?>/expiry" class="nav-subitem <?= $cur === 'expiry' ? 'active' : '' ?>">
                            <span>Expiry Management</span>
                            <?php if (($nearExpiryCount ?? 0) > 0): ?>
                                <span class="nav-badge badge-warning"><?= $nearExpiryCount ?></span>
                            <?php endif; ?>
                        </a>
                    <?php endif; ?>
                    <?php if ($can('barcode')): ?>
                        <a href="<?= $baseURL ?>/barcode" class="nav-subitem <?= $cur === 'barcode' ? 'active' : '' ?>">
                            <span>Barcode & Scanning</span>
                        </a>
                    <?php endif; ?>
                    <?php if ($can('fefo')): ?>
                        <a href="<?= $baseURL ?>/fefo" class="nav-subitem <?= $cur === 'fefo' ? 'active' : '' ?>">
                            <span>FEFO / FIFO Engine</span>
                        </a>
                    <?php endif; ?>
                    <?php if ($can('pricing')): ?>
                        <a href="<?= $baseURL ?>/pricing" class="nav-subitem <?= $cur === 'pricing' ? 'active' : '' ?>">
                            <span>Medicine Pricing</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- 2. Dropdown: Stock & Procurement -->
        <?php if ($can('inventory') || $can('reorder') || $can('purchase_orders') || $can('purchases') || $can('purchase_returns') || $can('suppliers')): ?>
            <div class="sidebar-dropdown <?= $isStockOpen ? 'open has-active' : '' ?>">
                <button type="button" class="sidebar-dropdown-header">
                    <div class="dropdown-title-group">
                        <span class="dropdown-icon">📦</span>
                        <span class="dropdown-title">Stock & Procurement</span>
                    </div>
                    <div class="dropdown-meta">
                        <?php if (($lowStockCount ?? 0) > 0): ?>
                            <span class="nav-badge badge-danger"><?= $lowStockCount ?></span>
                        <?php endif; ?>
                        <span class="dropdown-arrow"></span>
                    </div>
                </button>
                <div class="sidebar-dropdown-content">
                    <?php if ($can('inventory')): ?>
                        <a href="<?= $baseURL ?>/inventory" class="nav-subitem <?= $cur === 'inventory' ? 'active' : '' ?>">
                            <span>Stock Management</span>
                        </a>
                    <?php endif; ?>
                    <?php if ($can('reorder')): ?>
                        <a href="<?= $baseURL ?>/reorder" class="nav-subitem <?= $cur === 'reorder' ? 'active' : '' ?>">
                            <span>Smart Reorder</span>
                            <?php if (($lowStockCount ?? 0) > 0): ?>
                                <span class="nav-badge badge-danger"><?= $lowStockCount ?></span>
                            <?php endif; ?>
                        </a>
                    <?php endif; ?>
                    <?php if ($can('purchase_orders')): ?>
                        <a href="<?= $baseURL ?>/purchase-orders" class="nav-subitem <?= $cur === 'purchase_orders' ? 'active' : '' ?>">
                            <span>Purchase Orders (PO)</span>
                        </a>
                    <?php endif; ?>
                    <?php if ($can('purchases')): ?>
                        <a href="<?= $baseURL ?>/purchases" class="nav-subitem <?= $cur === 'purchases' ? 'active' : '' ?>">
                            <span>Purchase Invoices</span>
                        </a>
                    <?php endif; ?>
                    <?php if ($can('purchase_returns')): ?>
                        <a href="<?= $baseURL ?>/purchase-returns" class="nav-subitem <?= $cur === 'purchase_returns' ? 'active' : '' ?>">
                            <span>Purchase Returns</span>
                        </a>
                    <?php endif; ?>
                    <?php if ($can('suppliers')): ?>
                        <a href="<?= $baseURL ?>/suppliers" class="nav-subitem <?= $cur === 'suppliers' ? 'active' : '' ?>">
                            <span>Suppliers & Distributors</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- 3. Dropdown: Clinical & Prescriptions -->
        <?php if ($can('prescriptions') || $can('prescription_billing') || $can('patients') || $can('doctors')): ?>
            <div class="sidebar-dropdown <?= $isClinOpen ? 'open has-active' : '' ?>">
                <button type="button" class="sidebar-dropdown-header">
                    <div class="dropdown-title-group">
                        <span class="dropdown-icon">🩺</span>
                        <span class="dropdown-title">Clinical & Prescriptions</span>
                    </div>
                    <div class="dropdown-meta">
                        <span class="dropdown-arrow"></span>
                    </div>
                </button>
                <div class="sidebar-dropdown-content">
                    <?php if ($can('prescriptions')): ?>
                        <a href="<?= $baseURL ?>/prescriptions" class="nav-subitem <?= $cur === 'prescriptions' ? 'active' : '' ?>">
                            <span>Prescription Archive</span>
                        </a>
                    <?php endif; ?>
                    <?php if ($can('prescription_billing')): ?>
                        <a href="<?= $baseURL ?>/prescription-billing" class="nav-subitem <?= $cur === 'prescription_billing' ? 'active' : '' ?>">
                            <span>Prescription Billing</span>
                        </a>
                    <?php endif; ?>
                    <?php if ($can('patients')): ?>
                        <a href="<?= $baseURL ?>/patients" class="nav-subitem <?= $cur === 'patients' ? 'active' : '' ?>">
                            <span>Patients & Customers</span>
                        </a>
                    <?php endif; ?>
                    <?php if ($can('doctors')): ?>
                        <a href="<?= $baseURL ?>/doctors" class="nav-subitem <?= $cur === 'doctors' ? 'active' : '' ?>">
                            <span>Doctor Profiles</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- 4. Dropdown: Sales & Orders -->
        <?php if ($can('sales') || $can('sales_returns') || $can('loyalty') || $can('online_orders') || $can('delivery')): ?>
            <div class="sidebar-dropdown <?= $isSalesOpen ? 'open has-active' : '' ?>">
                <button type="button" class="sidebar-dropdown-header">
                    <div class="dropdown-title-group">
                        <span class="dropdown-icon">🧾</span>
                        <span class="dropdown-title">Sales & Orders</span>
                    </div>
                    <div class="dropdown-meta">
                        <span class="dropdown-arrow"></span>
                    </div>
                </button>
                <div class="sidebar-dropdown-content">
                    <?php if ($can('sales')): ?>
                        <a href="<?= $baseURL ?>/sales" class="nav-subitem <?= $cur === 'sales' ? 'active' : '' ?>">
                            <span>Sales History & Invoices</span>
                        </a>
                    <?php endif; ?>
                    <?php if ($can('sales_returns')): ?>
                        <a href="<?= $baseURL ?>/sales-returns" class="nav-subitem <?= $cur === 'sales_returns' ? 'active' : '' ?>">
                            <span>Sales Returns (Credit Notes)</span>
                        </a>
                    <?php endif; ?>
                    <?php if ($can('loyalty')): ?>
                        <a href="<?= $baseURL ?>/loyalty" class="nav-subitem <?= $cur === 'loyalty' ? 'active' : '' ?>">
                            <span>Loyalty & Coupons</span>
                        </a>
                    <?php endif; ?>
                    <?php if ($can('online_orders')): ?>
                        <a href="<?= $baseURL ?>/online-orders" class="nav-subitem <?= $cur === 'online_orders' ? 'active' : '' ?>">
                            <span>Online Orders</span>
                        </a>
                    <?php endif; ?>
                    <?php if ($can('delivery')): ?>
                        <a href="<?= $baseURL ?>/delivery" class="nav-subitem <?= $cur === 'delivery' ? 'active' : '' ?>">
                            <span>Delivery Dispatch</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- 5. Dropdown: Accounting & Tax -->
        <?php if ($can('gst') || $can('payments') || $can('accounts') || $can('expenses') || $can('reports')): ?>
            <div class="sidebar-dropdown <?= $isFinOpen ? 'open has-active' : '' ?>">
                <button type="button" class="sidebar-dropdown-header">
                    <div class="dropdown-title-group">
                        <span class="dropdown-icon">💰</span>
                        <span class="dropdown-title">Accounting & Tax</span>
                    </div>
                    <div class="dropdown-meta">
                        <span class="dropdown-arrow"></span>
                    </div>
                </button>
                <div class="sidebar-dropdown-content">
                    <?php if ($can('gst')): ?>
                        <a href="<?= $baseURL ?>/gst" class="nav-subitem <?= $cur === 'gst' ? 'active' : '' ?>">
                            <span>GST & Tax Engine</span>
                        </a>
                    <?php endif; ?>
                    <?php if ($can('payments')): ?>
                        <a href="<?= $baseURL ?>/payments" class="nav-subitem <?= $cur === 'payments' ? 'active' : '' ?>">
                            <span>Payment & Cash Register</span>
                        </a>
                    <?php endif; ?>
                    <?php if ($can('accounts')): ?>
                        <a href="<?= $baseURL ?>/accounts" class="nav-subitem <?= $cur === 'accounts' ? 'active' : '' ?>">
                            <span>Receivables & Payables</span>
                        </a>
                    <?php endif; ?>
                    <?php if ($can('expenses')): ?>
                        <a href="<?= $baseURL ?>/expenses" class="nav-subitem <?= $cur === 'expenses' ? 'active' : '' ?>">
                            <span>Expense Tracker</span>
                        </a>
                    <?php endif; ?>
                    <?php if ($can('reports')): ?>
                        <a href="<?= $baseURL ?>/reports" class="nav-subitem <?= $cur === 'reports' ? 'active' : '' ?>">
                            <span>Reports & Analytics</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- 6. Dropdown: Documents & Communications -->
        <?php if ($can('integration') || $can('documents') || $can('notifications')): ?>
            <div class="sidebar-dropdown <?= $isDocOpen ? 'open has-active' : '' ?>">
                <button type="button" class="sidebar-dropdown-header">
                    <div class="dropdown-title-group">
                        <span class="dropdown-icon">📁</span>
                        <span class="dropdown-title">Documents & Alerts</span>
                    </div>
                    <div class="dropdown-meta">
                        <?php if (($unreadAlertsCount ?? 0) > 0): ?>
                            <span class="nav-badge badge-info"><?= $unreadAlertsCount ?></span>
                        <?php endif; ?>
                        <span class="dropdown-arrow"></span>
                    </div>
                </button>
                <div class="sidebar-dropdown-content">
                    <?php if ($can('integration')): ?>
                        <a href="<?= $baseURL ?>/integration" class="nav-subitem <?= $cur === 'integration' ? 'active' : '' ?>">
                            <span>WhatsApp & SMS Gateway</span>
                        </a>
                    <?php endif; ?>
                    <?php if ($can('documents')): ?>
                        <a href="<?= $baseURL ?>/documents" class="nav-subitem <?= $cur === 'documents' ? 'active' : '' ?>">
                            <span>Document Storage</span>
                        </a>
                    <?php endif; ?>
                    <?php if ($can('notifications')): ?>
                        <a href="<?= $baseURL ?>/notifications" class="nav-subitem <?= $cur === 'notifications' ? 'active' : '' ?>">
                            <span>System Notifications</span>
                            <?php if (($unreadAlertsCount ?? 0) > 0): ?>
                                <span class="nav-badge badge-info"><?= $unreadAlertsCount ?></span>
                            <?php endif; ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- 7. Dropdown: Administration & Control -->
        <?php if ($can('users') || $can('audit') || $can('security') || $can('settings')): ?>
            <div class="sidebar-dropdown <?= $isAdminOpen ? 'open has-active' : '' ?>">
                <button type="button" class="sidebar-dropdown-header">
                    <div class="dropdown-title-group">
                        <span class="dropdown-icon">⚙️</span>
                        <span class="dropdown-title">Administration & Control</span>
                    </div>
                    <div class="dropdown-meta">
                        <span class="dropdown-arrow"></span>
                    </div>
                </button>
                <div class="sidebar-dropdown-content">
                    <?php if ($can('users')): ?>
                        <a href="<?= $baseURL ?>/users" class="nav-subitem <?= $cur === 'users' ? 'active' : '' ?>">
                            <span>Users & Role Matrix</span>
                        </a>
                    <?php endif; ?>
                    <?php if ($can('audit')): ?>
                        <a href="<?= $baseURL ?>/audit" class="nav-subitem <?= $cur === 'audit' ? 'active' : '' ?>">
                            <span>Audit Trail & Activity</span>
                        </a>
                    <?php endif; ?>
                    <?php if ($can('security')): ?>
                        <a href="<?= $baseURL ?>/security" class="nav-subitem <?= $cur === 'security' ? 'active' : '' ?>">
                            <span>Security & Backups</span>
                        </a>
                    <?php endif; ?>
                    <?php if ($can('settings')): ?>
                        <a href="<?= $baseURL ?>/settings" class="nav-subitem <?= $cur === 'settings' ? 'active' : '' ?>">
                            <span>Settings & Print Center</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</aside>
