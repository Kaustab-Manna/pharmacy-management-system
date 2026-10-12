<div class="page-header">
    <div class="page-title">
        <h1>Audit Trail & Activity Logs</h1>
        <p>Security and compliance log tracking user logins, price adjustments, stock corrections, and database actions.</p>
    </div>
    <div class="page-actions" style="display:flex;gap:8px;align-items:center;">
        <button class="btn btn-secondary" onclick="exportTableToCSV('auditTable', 'audit_compliance_trail.csv')">
            <span>📥 Export Audit Log</span>
        </button>
        <?php if (!empty($logs)): ?>
            <a href="<?= $baseURL ?>/audit/clear" 
               class="btn btn-subtle-danger" 
               onclick="return confirm('⚠️ WARNING: Are you sure you want to clear ALL audit logs? This action cannot be undone.');"
               style="text-decoration:none;display:inline-flex;align-items:center;gap:6px;">
                <span>🗑️</span>
                <span>Clear All Logs</span>
            </a>
        <?php endif; ?>
    </div>
</div>

<!-- Filters Bar -->
<div class="card" style="margin-bottom: 1.25rem; padding: 1rem 1.25rem;">
    <form method="GET" action="<?= $baseURL ?>/audit" style="display:flex; flex-wrap:wrap; gap:12px; align-items:center; justify-content:space-between;">
        <div style="display:flex; flex-wrap:wrap; gap:12px; align-items:center;">
            <div style="min-width: 180px;">
                <label style="font-size:11px; font-weight:600; color:var(--text-muted); display:block; margin-bottom:4px;">Filter by Module</label>
                <select name="module" class="form-control" style="padding:6px 10px; font-size:13px;" onchange="this.form.submit()">
                    <option value="">All Modules</option>
                    <?php 
                    $modules = ['auth', 'medicines', 'categories', 'batches', 'pos', 'sales', 'purchases', 'users', 'patients', 'suppliers', 'settings', 'security', 'audit'];
                    foreach ($modules as $m): 
                    ?>
                        <option value="<?= $m ?>" <?= ($moduleFilter ?? '') === $m ? 'selected' : '' ?>><?= ucfirst($m) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="min-width: 180px;">
                <label style="font-size:11px; font-weight:600; color:var(--text-muted); display:block; margin-bottom:4px;">Filter by User</label>
                <select name="user_id" class="form-control" style="padding:6px 10px; font-size:13px;" onchange="this.form.submit()">
                    <option value="">All Staff Users</option>
                    <?php if (!empty($users)): ?>
                        <?php foreach ($users as $u): ?>
                            <option value="<?= $u['id'] ?>" <?= ((string)($userFilter ?? '')) === ((string)$u['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($u['full_name']) ?> (<?= htmlspecialchars($u['username']) ?>)
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
        </div>

        <?php if (!empty($moduleFilter) || !empty($userFilter)): ?>
            <div>
                <a href="<?= $baseURL ?>/audit" class="btn btn-sm btn-secondary" style="text-decoration:none; display:inline-flex; align-items:center; gap:4px;">
                    <span>✕</span>
                    <span>Reset Filters</span>
                </a>
            </div>
        <?php endif; ?>
    </form>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table-custom" id="auditTable">
            <thead>
                <tr>
                    <th style="width: 140px;">Timestamp</th>
                    <th style="width: 130px;">User</th>
                    <th style="width: 110px;">Role</th>
                    <th style="width: 120px;">Action</th>
                    <th style="width: 110px;">Module</th>
                    <th style="width: 120px;">IP Address</th>
                    <th>Details & Description</th>
                    <th style="width: 90px; text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                    <tr><td colspan="8" style="text-align:center;padding:2.5rem;color:var(--text-muted);">No audit events recorded yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($logs as $l): ?>
                        <tr>
                            <td style="font-size:12px; color:var(--text-muted); white-space:nowrap;"><?= htmlspecialchars($l['created_at']) ?></td>
                            <td><strong><?= htmlspecialchars($l['username'] ?? 'System') ?></strong></td>
                            <td><span class="nav-badge badge-info" style="text-transform:capitalize;"><?= htmlspecialchars(str_replace('_', ' ', $l['user_role'] ?? 'user')) ?></span></td>
                            <td><code style="font-size:12px;"><?= htmlspecialchars($l['action']) ?></code></td>
                            <td><span class="nav-badge" style="background:#e0f2fe; color:#0369a1;"><?= htmlspecialchars($l['module']) ?></span></td>
                            <td><code style="font-size:11px; color:var(--text-muted);"><?= htmlspecialchars($l['ip_address'] ?? '127.0.0.1') ?></code></td>
                            <td style="font-size:12px;"><?= htmlspecialchars($l['details'] ?? '-') ?></td>
                            <td style="text-align: right; white-space: nowrap;">
                                <a href="<?= $baseURL ?>/audit/delete/<?= $l['id'] ?>" 
                                   class="btn btn-sm btn-subtle-danger" 
                                   style="padding: 4px 8px; font-size: 11px; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;"
                                   onclick="return confirm('Are you sure you want to delete audit log entry #<?= $l['id'] ?>?');"
                                   title="Delete this record">
                                    <span>🗑️</span>
                                    <span>Delete</span>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
