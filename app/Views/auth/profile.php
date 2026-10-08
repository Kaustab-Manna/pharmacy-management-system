<div class="page-header">
    <div class="page-title">
        <h1>User Profile & Credentials</h1>
        <p>Manage your account settings, contact details, and security password.</p>
    </div>
</div>

<div style="max-width: 650px;">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">👤 Personal Details</h3>
            <span class="nav-badge badge-info"><?= strtoupper($user['role'] ?? 'USER') ?></span>
        </div>
        <form method="POST" action="<?= $baseURL ?>/profile">
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">Username</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($user['username'] ?? '') ?>" disabled style="background:var(--border-light);">
                </div>

                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" class="form-control" value="<?= htmlspecialchars($user['email'] ?? '') ?>" disabled style="background:var(--border-light);">
                </div>

                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($user['full_name'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Phone Number</label>
                    <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                </div>

                <hr style="margin: 1.5rem 0; border: none; border-top: 1px solid var(--border-color);">

                <div class="form-group">
                    <label class="form-label">New Password (leave blank to keep current password)</label>
                    <input type="password" name="password" class="form-control" placeholder="Enter new password if changing">
                </div>
            </div>
            <div class="card-footer" style="display:flex;justify-content:flex-end;">
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>
