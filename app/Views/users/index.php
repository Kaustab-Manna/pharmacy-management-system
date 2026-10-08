<div class="page-header">
    <div class="page-title">
        <h1>User & Role Management (Module 1)</h1>
        <p>Manage staff accounts, assign granular role permissions, and monitor last activity logins.</p>
    </div>
    <div class="page-actions">
        <button class="btn btn-primary" onclick="openModal('addUserModal')">
            <span>➕ Create Staff User</span>
        </button>
    </div>
</div>

<div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
        <div>
            <h3 style="margin:0;font-size:16px;">Active Staff Accounts</h3>
            <span style="font-size:12px;color:var(--text-muted);"><?= count($users) ?> total users registered in the system</span>
        </div>
        <div style="font-size:12px;background:var(--bg-body);padding:6px 12px;border-radius:6px;border:1px solid var(--border-color);color:var(--text-muted);">
            💡 Default system logins: <code>admin</code>, <code>pharmacist</code>, <code>cashier</code>, <code>store</code>, <code>accountant</code> (Password: <code>admin123</code>)
        </div>
    </div>

    <div class="table-responsive">
        <table class="table-custom">
            <thead>
                <tr>
                    <th>Full Name</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Assigned Role</th>
                    <th>Status</th>
                    <th>Last Login</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($u['full_name']) ?></strong></td>
                        <td><code><?= htmlspecialchars($u['username']) ?></code></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td><?= htmlspecialchars($u['phone'] ?? '-') ?></td>
                        <td>
                            <span class="nav-badge badge-info" style="text-transform:uppercase;">
                                <?= htmlspecialchars(str_replace('_', ' ', $u['role'])) ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($u['is_active']): ?>
                                <span class="nav-badge badge-success">Active</span>
                            <?php else: ?>
                                <span class="nav-badge badge-danger">Deactivated</span>
                            <?php endif; ?>
                        </td>
                        <td style="font-size:12px;color:var(--text-muted);"><?= htmlspecialchars($u['last_login'] ?? 'Never') ?></td>
                        <td style="text-align:right;">
                            <div style="display:inline-flex;gap:4px;align-items:center;">
                                <button type="button" class="btn btn-sm btn-secondary" onclick='openEditModal(<?= json_encode($u) ?>)' title="Edit User">
                                    ✏️ Edit
                                </button>

                                <form method="POST" action="<?= $baseURL ?>/users/toggle-status/<?= $u['id'] ?>" style="display:inline;">
                                    <button type="submit" class="btn btn-sm <?= $u['is_active'] ? 'btn-secondary' : 'btn-success' ?>" title="<?= $u['is_active'] ? 'Deactivate account' : 'Activate account' ?>">
                                        <?= $u['is_active'] ? 'Deactivate' : 'Activate' ?>
                                    </button>
                                </form>

                                <?php if (!in_array($u['username'], ['admin', 'superadmin'])): ?>
                                    <form method="POST" action="<?= $baseURL ?>/users/delete/<?= $u['id'] ?>" style="display:inline;" onsubmit="return confirm('Are you sure you want to permanently delete user <?= htmlspecialchars(addslashes($u['username'])) ?>?');">
                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete User">
                                            🗑️
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Add User -->
<div id="addUserModal" class="modal-overlay">
    <div class="modal-dialog" style="max-width: 560px;">
        <div class="modal-header">
            <h3 class="modal-title">➕ Create Staff Account</h3>
            <button class="modal-close" onclick="closeModal('addUserModal')">&times;</button>
        </div>
        <form method="POST" action="<?= $baseURL ?>/users/create" id="createUserForm">
            <div class="modal-body">
                <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:6px;padding:10px 14px;margin-bottom:14px;font-size:12px;color:#1e40af;">
                    ℹ️ <strong>Unique Requirement:</strong> Each user must have a unique <strong>Username</strong> and <strong>Email</strong>. Existing usernames (<code><?= implode('</code>, <code>', array_slice(array_column($users, 'username'), 0, 5)) ?></code>...) cannot be reused.
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <label class="form-label">Username *</label>
                        <input type="text" name="username" id="createUsernameInput" class="form-control" required placeholder="e.g. rahul_rph" autocomplete="off" oninput="validateNewUser()">
                        <div id="usernameFeedback" style="font-size:11px;margin-top:4px;display:none;"></div>
                    </div>
                    <div class="form-col">
                        <label class="form-label">Full Name *</label>
                        <input type="text" name="full_name" class="form-control" required placeholder="e.g. Dr. Rajesh Kumar">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" id="createEmailInput" class="form-control" required placeholder="user@pharmacy.com" autocomplete="off" oninput="validateNewUser()">
                        <div id="emailFeedback" style="font-size:11px;margin-top:4px;display:none;"></div>
                    </div>
                    <div class="form-col">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control" placeholder="+91 98765 43210">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Role Assignment (From PDF Specification) *</label>
                    <select name="role" class="form-control" required>
                        <option value="super_admin">Super Admin</option>
                        <option value="pharmacy_admin">Pharmacy Admin</option>
                        <option value="pharmacist">Pharmacist</option>
                        <option value="store_manager">Store / Warehouse Manager</option>
                        <option value="purchase_manager">Purchase Manager</option>
                        <option value="billing_executive">Billing Executive</option>
                        <option value="cashier" selected>Cashier</option>
                        <option value="accountant">Accountant</option>
                        <option value="sales_staff">Sales / Counter Staff</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Initial Password *</label>
                    <input type="password" name="password" class="form-control" value="admin123" required placeholder="Min 4 characters">
                    <small style="font-size:11px;color:var(--text-muted);">Default password is <code>admin123</code> (can be changed later by user).</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addUserModal')">Cancel</button>
                <button type="submit" id="createUserSubmitBtn" class="btn btn-primary">Create User</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Edit User -->
<div id="editUserModal" class="modal-overlay">
    <div class="modal-dialog" style="max-width: 560px;">
        <div class="modal-header">
            <h3 class="modal-title">✏️ Edit Staff Account</h3>
            <button class="modal-close" onclick="closeModal('editUserModal')">&times;</button>
        </div>
        <form method="POST" id="editUserForm" action="">
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-col">
                        <label class="form-label">Username (Immutable)</label>
                        <input type="text" id="editUsernameInput" class="form-control" disabled style="background:var(--bg-body);cursor:not-allowed;">
                    </div>
                    <div class="form-col">
                        <label class="form-label">Full Name *</label>
                        <input type="text" name="full_name" id="editFullNameInput" class="form-control" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" id="editEmailInput" class="form-control" required>
                    </div>
                    <div class="form-col">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" id="editPhoneInput" class="form-control">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Role Assignment *</label>
                    <select name="role" id="editRoleSelect" class="form-control" required>
                        <option value="super_admin">Super Admin</option>
                        <option value="pharmacy_admin">Pharmacy Admin</option>
                        <option value="pharmacist">Pharmacist</option>
                        <option value="store_manager">Store / Warehouse Manager</option>
                        <option value="purchase_manager">Purchase Manager</option>
                        <option value="billing_executive">Billing Executive</option>
                        <option value="cashier">Cashier</option>
                        <option value="accountant">Accountant</option>
                        <option value="sales_staff">Sales / Counter Staff</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Reset Password <small style="color:var(--text-muted);">(Leave blank to keep current password)</small></label>
                    <input type="password" name="password" class="form-control" placeholder="Enter new password to reset">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editUserModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
const takenUsernames = <?= json_encode(array_map('strtolower', array_column($users, 'username'))) ?>;
const takenEmails = <?= json_encode(array_map('strtolower', array_column($users, 'email'))) ?>;

function validateNewUser() {
    const usernameInput = document.getElementById('createUsernameInput');
    const emailInput = document.getElementById('createEmailInput');
    const uFeedback = document.getElementById('usernameFeedback');
    const eFeedback = document.getElementById('emailFeedback');
    const submitBtn = document.getElementById('createUserSubmitBtn');

    const uVal = (usernameInput.value || '').trim().toLowerCase();
    const eVal = (emailInput.value || '').trim().toLowerCase();

    let uError = false;
    let eError = false;

    if (uVal && takenUsernames.includes(uVal)) {
        uFeedback.style.display = 'block';
        uFeedback.style.color = '#ef4444';
        uFeedback.innerHTML = '⚠️ Username "<strong>' + uVal + '</strong>" is already in use. Please choose a different username.';
        usernameInput.style.borderColor = '#ef4444';
        uError = true;
    } else {
        uFeedback.style.display = 'none';
        usernameInput.style.borderColor = '';
    }

    if (eVal && takenEmails.includes(eVal)) {
        eFeedback.style.display = 'block';
        eFeedback.style.color = '#ef4444';
        eFeedback.innerHTML = '⚠️ Email "<strong>' + eVal + '</strong>" is already registered to another user.';
        emailInput.style.borderColor = '#ef4444';
        eError = true;
    } else {
        eFeedback.style.display = 'none';
        emailInput.style.borderColor = '';
    }

    submitBtn.disabled = uError || eError;
}

function openEditModal(user) {
    document.getElementById('editUserForm').action = '<?= $baseURL ?>/users/update/' + user.id;
    document.getElementById('editUsernameInput').value = user.username;
    document.getElementById('editFullNameInput').value = user.full_name;
    document.getElementById('editEmailInput').value = user.email;
    document.getElementById('editPhoneInput').value = user.phone || '';
    document.getElementById('editRoleSelect').value = user.role;
    openModal('editUserModal');
}
</script>
