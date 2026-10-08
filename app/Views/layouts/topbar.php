<main class="app-main">
    <header class="app-topbar">
        <div class="topbar-left">
            <button id="sidebarToggleBtn" class="btn-icon" title="Toggle Sidebar">☰</button>
            
            <div class="search-bar-global">
                <span class="search-icon">🔍</span>
                <input type="text" id="globalSearch" placeholder="Search medicines, batches, customers (F2)..." autocomplete="off" onkeyup="if(event.key==='Enter') window.location.href='<?= $baseURL ?>/medicines?search=' + encodeURIComponent(this.value)">
            </div>

            <?php if (($nearExpiryCount ?? 0) > 0): ?>
                <a href="<?= $baseURL ?>/expiry" class="fefo-pill" style="background:#fee2e2;color:#991b1b;border-color:#fca5a5;display:flex;align-items:center;gap:4px;" title="Near-Expiry batches detected">
                    <span>⚠️</span>
                    <span><strong><?= $nearExpiryCount ?></strong> Near Expiry</span>
                </a>
            <?php endif; ?>

            <?php if (($lowStockCount ?? 0) > 0): ?>
                <a href="<?= $baseURL ?>/reorder" class="fefo-pill" style="background:#fef3c7;color:#92400e;border-color:#fcd34d;display:flex;align-items:center;gap:4px;" title="Low stock items requiring reorder">
                    <span>📉</span>
                    <span><strong><?= $lowStockCount ?></strong> Low Stock</span>
                </a>
            <?php endif; ?>
        </div>

        <div class="topbar-right">
            <!-- Quick POS Button -->
            <a href="<?= $baseURL ?>/pos" class="btn btn-sm btn-primary" style="display:flex;align-items:center;gap:6px;">
                <span>⚡ POS Counter</span>
            </a>

            <!-- Notifications Bell -->
            <a href="<?= $baseURL ?>/notifications" class="btn-icon" title="Notifications & System Alerts">
                <span>🔔</span>
                <?php if (($unreadAlertsCount ?? 0) > 0): ?>
                    <span class="indicator"></span>
                <?php endif; ?>
            </a>

            <!-- Keyboard Shortcuts Trigger -->
            <button class="btn-icon" onclick="openModal('shortcutsModal')" title="Keyboard Shortcuts (F1)">⌨️</button>

            <!-- Dark / Light Theme Toggle -->
            <button id="themeToggleBtn" class="btn-icon" title="Toggle Dark/Light Mode">🌙</button>

            <!-- Current User Profile & Role -->
            <div class="user-profile-badge" onclick="document.getElementById('userMenuDropdown').classList.toggle('active')">
                <div class="user-avatar">
                    <?= strtoupper(substr($currentUser['full_name'] ?? 'A', 0, 1)) ?>
                </div>
                <div class="user-info">
                    <div class="name"><?= htmlspecialchars($currentUser['full_name'] ?? 'Super Admin') ?></div>
                    <div class="role"><?= htmlspecialchars(str_replace('_', ' ', $currentUser['role'] ?? 'super_admin')) ?></div>
                </div>
                <span style="font-size:10px;color:var(--text-muted);margin-left:4px;">▼</span>
            </div>

            <!-- Quick Profile & Role Switch Dropdown -->
            <div id="userMenuDropdown" class="modal-overlay" style="background:transparent;" onclick="this.classList.remove('active')">
                <div style="position:absolute;top:65px;right:25px;background:var(--bg-card);border:1px solid var(--border-color);border-radius:var(--radius-md);box-shadow:var(--shadow-xl);width:240px;padding:0.75rem;z-index:999;" onclick="event.stopPropagation()">
                    <div style="padding:0.5rem;border-bottom:1px solid var(--border-color);margin-bottom:0.5rem;">
                        <strong><?= htmlspecialchars($currentUser['full_name'] ?? 'Administrator') ?></strong>
                        <div style="font-size:11px;color:var(--text-muted);"><?= htmlspecialchars($currentUser['email'] ?? '') ?></div>
                    </div>
                    <a href="<?= $baseURL ?>/profile" class="nav-item" style="color:var(--text-main);padding:6px 10px;">👤 My Profile</a>
                    <a href="<?= $baseURL ?>/settings" class="nav-item" style="color:var(--text-main);padding:6px 10px;">⚙️ Store Settings</a>
                    <a href="<?= $baseURL ?>/audit" class="nav-item" style="color:var(--text-main);padding:6px 10px;">📜 My Activity Log</a>
                    <hr style="border:none;border-top:1px solid var(--border-color);margin:6px 0;">
                    <a href="<?= $baseURL ?>/logout" class="nav-item" style="color:#ef4444;padding:6px 10px;font-weight:600;">🚪 Log Out</a>
                </div>
            </div>
        </div>
    </header>

    <div class="app-content">
        <!-- System Alerts / Flash Messages -->
        <?php if (!empty($flashSuccess)): ?>
            <div class="alert alert-success">
                <span>✅</span>
                <div><?= htmlspecialchars($flashSuccess) ?></div>
            </div>
        <?php endif; ?>

        <?php if (!empty($flashError)): ?>
            <div class="alert alert-error">
                <span>⚠️</span>
                <div><?= htmlspecialchars($flashError) ?></div>
            </div>
        <?php endif; ?>

        <?php if (!empty($flashWarning)): ?>
            <div class="alert alert-warning">
                <span>🔔</span>
                <div><?= htmlspecialchars($flashWarning) ?></div>
            </div>
        <?php endif; ?>
