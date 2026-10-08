<div class="page-header">
    <div class="page-title">
        <h1>Role-Based Access Control (RBAC) & Security (Module 36)</h1>
        <p>Configure granular permissions for every staff role across all 37 modules and download database disaster recovery backups.</p>
    </div>
    <div class="page-actions">
        <a href="<?= $baseURL ?>/security/backup" class="btn btn-secondary">
            <span>💾 1-Click SQL Backup</span>
        </a>
    </div>
</div>

<!-- Super Admin Notice -->
<div style="background: linear-gradient(135deg, rgba(13, 148, 136, 0.1) 0%, rgba(2, 132, 199, 0.1) 100%); border: 1px solid rgba(13, 148, 136, 0.3); border-radius: 8px; padding: 12px 18px; margin-bottom: 1.5rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
    <div style="display: flex; align-items: center; gap: 10px;">
        <span style="font-size: 20px;">🛡️</span>
        <div>
            <strong style="color: var(--primary); font-size: 14px;">Administrator Security Policy:</strong>
            <span style="font-size: 13px; color: var(--text-muted); margin-left: 6px;">
                Accounts with the <strong>Super Administrator</strong> or <strong>Pharmacy Admin</strong> role automatically retain unrestricted full access to all 37 modules.
            </span>
        </div>
    </div>
    <span class="nav-badge badge-success" style="font-size: 11px;">Policy Active</span>
</div>

<!-- Role Selector Tabs -->
<div style="margin-bottom: 1.25rem;">
    <label style="font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-muted); display: block; margin-bottom: 8px;">
        Select Staff Role to Configure:
    </label>
    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
        <?php foreach ($roles as $rKey => $rLabel): ?>
            <?php $isActive = ($selectedRole === $rKey); ?>
            <a href="<?= $baseURL ?>/security?role=<?= urlencode($rKey) ?>" 
               class="btn <?= $isActive ? 'btn-primary' : 'btn-secondary' ?>" 
               style="font-size: 13px; padding: 8px 14px; border-radius: 6px; display: inline-flex; align-items: center; gap: 6px;">
                <span><?= $isActive ? '👉' : '👤' ?></span>
                <span><strong><?= htmlspecialchars($rLabel) ?></strong></span>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<form method="POST" action="<?= $baseURL ?>/security/update-permissions" id="permissionsForm">
    <input type="hidden" name="role" value="<?= htmlspecialchars($selectedRole) ?>">

    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; padding: 14px 20px;">
            <div>
                <h3 class="card-title" style="margin: 0; font-size: 16px;">
                    Permissions for: <span style="color: var(--primary);"><?= htmlspecialchars($roles[$selectedRole] ?? $selectedRole) ?></span>
                </h3>
                <span style="font-size: 12px; color: var(--text-muted);">
                    <?php if ($hasCustomConfig): ?>
                        <span style="color: #059669; font-weight: 600;">● Custom Database Configuration Active</span> (Overrides system defaults)
                    <?php else: ?>
                        <span style="color: #d97706; font-weight: 600;">● Default Recommended Matrix</span> (Click Save to establish custom rules)
                    <?php endif; ?>
                </span>
            </div>

            <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                <button type="button" class="btn btn-sm btn-secondary" onclick="toggleAllView(true)">
                    <span>👁️ Check All View</span>
                </button>
                <button type="button" class="btn btn-sm btn-secondary" onclick="toggleAll(true)">
                    <span>✅ Grant All</span>
                </button>
                <button type="button" class="btn btn-sm btn-secondary" onclick="toggleAll(false)">
                    <span>❌ Revoke All</span>
                </button>

                <?php if ($hasCustomConfig): ?>
                    <button type="button" class="btn btn-sm btn-secondary" style="color: #dc2626;" onclick="if(confirm('Reset permissions for this role back to default standard settings?')) document.getElementById('resetForm').submit();">
                        <span>🔄 Reset Defaults</span>
                    </button>
                <?php endif; ?>

                <button type="submit" class="btn btn-sm btn-primary" style="padding: 6px 18px; font-weight: 700; box-shadow: var(--shadow-md);">
                    <span>💾 Save Role Permissions</span>
                </button>
            </div>
        </div>

        <div class="card-body" style="padding: 1.25rem 1.5rem;">
            <?php foreach ($moduleGroups as $groupTitle => $modules): ?>
                <div style="margin-bottom: 2rem;">
                    <h4 style="margin: 0 0 12px 0; font-size: 14px; font-weight: 700; color: var(--text-main); border-bottom: 2px solid var(--border-color); padding-bottom: 6px; display: flex; justify-content: space-between; align-items: center;">
                        <span><?= htmlspecialchars($groupTitle) ?></span>
                        <span style="font-size: 11px; font-weight: normal; color: var(--text-muted);">
                            <a href="javascript:void(0)" onclick="toggleGroup('<?= md5($groupTitle) ?>', true)" style="color: var(--primary); text-decoration: none;">Select Section</a> | 
                            <a href="javascript:void(0)" onclick="toggleGroup('<?= md5($groupTitle) ?>', false)" style="color: var(--text-muted); text-decoration: none;">Clear</a>
                        </span>
                    </h4>

                    <div class="table-responsive">
                        <table class="table-custom" style="font-size: 13px;" data-group="<?= md5($groupTitle) ?>">
                            <thead>
                                <tr>
                                    <th style="width: 32%;">Module Name & Scope</th>
                                    <th style="width: 13%; text-align: center;">View</th>
                                    <th style="width: 13%; text-align: center;">Create</th>
                                    <th style="width: 13%; text-align: center;">Edit</th>
                                    <th style="width: 13%; text-align: center;">Delete</th>
                                    <th style="width: 13%; text-align: center;">Export</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($modules as $modKey => $info): ?>
                                    <?php 
                                        // Determine initial state: DB config or fallback
                                        if ($hasCustomConfig) {
                                            $p = $permMap[$modKey] ?? null;
                                            $canView   = !empty($p['can_view']);
                                            $canCreate = !empty($p['can_create']);
                                            $canEdit   = !empty($p['can_edit']);
                                            $canDelete = !empty($p['can_delete']);
                                            $canExport = !empty($p['can_export']);
                                        } else {
                                            // Standard default role mappings
                                            $canView = $this->hasPermission($modKey, 'view');
                                            $canCreate = in_array($selectedRole, ['pharmacist', 'store_manager', 'purchase_manager', 'accountant', 'billing_executive']) && $canView;
                                            $canEdit = $canCreate;
                                            $canDelete = in_array($selectedRole, ['pharmacist', 'purchase_manager', 'store_manager']) && $canView;
                                            $canExport = $canView;
                                        }
                                    ?>
                                    <tr style="border-bottom: 1px solid var(--border-color);">
                                        <td>
                                            <div style="font-weight: 600; color: var(--text-main); font-size: 13px;">
                                                <?= htmlspecialchars($info['title']) ?>
                                            </div>
                                            <div style="font-size: 11px; color: var(--text-muted);">
                                                <?= htmlspecialchars($info['desc']) ?>
                                            </div>
                                        </td>
                                        <td style="text-align: center;">
                                            <label style="cursor: pointer; display: inline-flex; align-items: center; justify-content: center; width: 100%;">
                                                <input type="checkbox" 
                                                       name="perms[<?= $modKey ?>][can_view]" 
                                                       value="1" 
                                                       class="perm-checkbox perm-view" 
                                                       <?= $canView ? 'checked' : '' ?>
                                                       onchange="if(!this.checked) uncheckChildren('<?= $modKey ?>')">
                                            </label>
                                        </td>
                                        <td style="text-align: center;">
                                            <label style="cursor: pointer; display: inline-flex; align-items: center; justify-content: center; width: 100%;">
                                                <input type="checkbox" 
                                                       name="perms[<?= $modKey ?>][can_create]" 
                                                       value="1" 
                                                       id="create_<?= $modKey ?>"
                                                       class="perm-checkbox perm-create" 
                                                       <?= $canCreate ? 'checked' : '' ?>
                                                       onchange="if(this.checked) checkView('<?= $modKey ?>')">
                                            </label>
                                        </td>
                                        <td style="text-align: center;">
                                            <label style="cursor: pointer; display: inline-flex; align-items: center; justify-content: center; width: 100%;">
                                                <input type="checkbox" 
                                                       name="perms[<?= $modKey ?>][can_edit]" 
                                                       value="1" 
                                                       id="edit_<?= $modKey ?>"
                                                       class="perm-checkbox perm-edit" 
                                                       <?= $canEdit ? 'checked' : '' ?>
                                                       onchange="if(this.checked) checkView('<?= $modKey ?>')">
                                            </label>
                                        </td>
                                        <td style="text-align: center;">
                                            <label style="cursor: pointer; display: inline-flex; align-items: center; justify-content: center; width: 100%;">
                                                <input type="checkbox" 
                                                       name="perms[<?= $modKey ?>][can_delete]" 
                                                       value="1" 
                                                       id="delete_<?= $modKey ?>"
                                                       class="perm-checkbox perm-delete" 
                                                       <?= $canDelete ? 'checked' : '' ?>
                                                       onchange="if(this.checked) checkView('<?= $modKey ?>')">
                                            </label>
                                        </td>
                                        <td style="text-align: center;">
                                            <label style="cursor: pointer; display: inline-flex; align-items: center; justify-content: center; width: 100%;">
                                                <input type="checkbox" 
                                                       name="perms[<?= $modKey ?>][can_export]" 
                                                       value="1" 
                                                       id="export_<?= $modKey ?>"
                                                       class="perm-checkbox perm-export" 
                                                       <?= $canExport ? 'checked' : '' ?>
                                                       onchange="if(this.checked) checkView('<?= $modKey ?>')">
                                            </label>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="card-footer" style="padding: 14px 20px; display: flex; justify-content: space-between; align-items: center; background: var(--bg-body); border-top: 1px solid var(--border-color);">
            <div style="font-size: 12px; color: var(--text-muted);">
                💡 Once saved, staff accounts logged in as <strong><?= htmlspecialchars($roles[$selectedRole] ?? $selectedRole) ?></strong> will instantly have these access rules applied.
            </div>
            <button type="submit" class="btn btn-primary" style="padding: 8px 24px; font-weight: 700; box-shadow: var(--shadow-md);">
                <span>💾 Save Permissions</span>
            </button>
        </div>
    </div>
</form>

<!-- Hidden form for reset action -->
<form id="resetForm" method="POST" action="<?= $baseURL ?>/security/reset-permissions" style="display:none;">
    <input type="hidden" name="role" value="<?= htmlspecialchars($selectedRole) ?>">
</form>

<!-- Staff User Accounts Reference -->
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h3 class="card-title" style="margin: 0; font-size: 15px;">👥 Active Staff Members with Role: <span style="color: var(--primary);"><?= htmlspecialchars($roles[$selectedRole] ?? $selectedRole) ?></span></h3>
        <a href="<?= $baseURL ?>/users" class="btn btn-sm btn-secondary">Manage Staff Users &rarr;</a>
    </div>
    <div class="table-responsive">
        <table class="table-custom" style="font-size: 12px;">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Assigned Role</th>
                    <th>Account Status</th>
                    <th>Last Login</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                    $roleUsers = array_filter($users, fn($u) => $u['role'] === $selectedRole);
                ?>
                <?php if (empty($roleUsers)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 1.5rem;">
                            No staff accounts currently assigned to role <strong><?= htmlspecialchars($roles[$selectedRole] ?? $selectedRole) ?></strong>.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($roleUsers as $u): ?>
                        <tr>
                            <td><code><?= htmlspecialchars($u['username']) ?></code></td>
                            <td><strong><?= htmlspecialchars($u['full_name']) ?></strong></td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td><span class="nav-badge badge-info"><?= strtoupper(str_replace('_', ' ', $u['role'])) ?></span></td>
                            <td>
                                <span class="nav-badge <?= $u['is_active'] ? 'badge-success' : 'badge-danger' ?>">
                                    <?= $u['is_active'] ? 'Active' : 'Deactivated' ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($u['last_login'] ?? 'Never') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function checkView(modKey) {
    const viewCb = document.querySelector('input[name="perms[' + modKey + '][can_view]"]');
    if (viewCb) {
        viewCb.checked = true;
    }
}

function uncheckChildren(modKey) {
    ['create', 'edit', 'delete', 'export'].forEach(action => {
        const cb = document.querySelector('input[name="perms[' + modKey + '][can_' + action + ']"]');
        if (cb) {
            cb.checked = false;
        }
    });
}

function toggleAll(checked) {
    document.querySelectorAll('#permissionsForm .perm-checkbox').forEach(cb => {
        cb.checked = checked;
    });
}

function toggleAllView(checked) {
    document.querySelectorAll('#permissionsForm .perm-view').forEach(cb => {
        cb.checked = checked;
    });
}

function toggleGroup(groupHash, checked) {
    const table = document.querySelector('table[data-group="' + groupHash + '"]');
    if (table) {
        table.querySelectorAll('.perm-checkbox').forEach(cb => {
            cb.checked = checked;
        });
    }
}
</script>
