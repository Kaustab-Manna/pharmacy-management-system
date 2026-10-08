<div class="page-header">
    <div class="page-title">
        <h1>Audit Trail & Activity Logs (Module 35)</h1>
        <p>Immutable regulatory security log tracking user logins, price adjustments, stock corrections, and invoice actions.</p>
    </div>
    <div class="page-actions">
        <button class="btn btn-secondary" onclick="exportTableToCSV('auditTable', 'audit_compliance_trail.csv')">
            <span>📥 Export Audit Log</span>
        </button>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table-custom" id="auditTable">
            <thead>
                <tr>
                    <th>Timestamp</th>
                    <th>User</th>
                    <th>Role</th>
                    <th>Action</th>
                    <th>Module</th>
                    <th>IP Address</th>
                    <th>Details & Description</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                    <tr><td colspan="7" style="text-align:center;padding:2rem;color:var(--text-muted);">No audit events recorded yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($logs as $l): ?>
                        <tr>
                            <td style="font-size:12px;"><?= htmlspecialchars($l['created_at']) ?></td>
                            <td><strong><?= htmlspecialchars($l['username'] ?? 'System') ?></strong></td>
                            <td><span class="nav-badge badge-info"><?= htmlspecialchars($l['user_role'] ?? 'user') ?></span></td>
                            <td><code><?= htmlspecialchars($l['action']) ?></code></td>
                            <td><?= htmlspecialchars($l['module']) ?></td>
                            <td><code style="font-size:11px;"><?= htmlspecialchars($l['ip_address'] ?? '127.0.0.1') ?></code></td>
                            <td style="font-size:12px;"><?= htmlspecialchars($l['details'] ?? '') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
