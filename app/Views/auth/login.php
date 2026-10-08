<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - INFOSOF Technologies Pharmacy Management</title>
    <link rel="stylesheet" href="<?= $baseURL ?>/assets/css/style.css">
    <style>
        .login-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: radial-gradient(circle at 10% 20%, rgba(13, 148, 136, 0.15) 0%, rgba(15, 23, 42, 0.05) 90%), #0f172a;
            padding: 1.5rem;
            font-family: var(--font-main);
        }
        .login-card {
            background: #ffffff;
            border-radius: var(--radius-lg);
            width: 100%;
            max-width: 480px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            padding: 2.25rem;
            position: relative;
            overflow: hidden;
        }
        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, #0d9488, #06b6d4, #6366f1);
        }
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .login-logo {
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, #0d9488, #06b6d4);
            border-radius: 14px;
            margin: 0 auto 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            color: #fff;
            box-shadow: 0 8px 16px rgba(13, 148, 136, 0.35);
        }
        .login-title {
            font-size: 1.5rem;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.02em;
        }
        .login-subtitle {
            font-size: 0.85rem;
            color: #64748b;
            margin-top: 0.25rem;
        }
        .role-switch-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0.5rem;
            margin-top: 1.5rem;
            padding-top: 1.25rem;
            border-top: 1px dashed #e2e8f0;
        }
        .btn-role-quick {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 0.45rem 0.25rem;
            font-size: 0.72rem;
            font-weight: 600;
            color: #475569;
            cursor: pointer;
            text-align: center;
            transition: all 0.2s;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2px;
        }
        .btn-role-quick:hover {
            background: #f0fdfa;
            border-color: #0d9488;
            color: #0f766e;
            transform: translateY(-1px);
        }
    </style>
</head>
<body class="login-page">

<div class="login-card">
    <div class="login-header">
        <div class="login-logo">✚</div>
        <h1 class="login-title">INFOSOF Care</h1>
        <div class="login-subtitle">Enterprise Pharmacy Management System</div>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-error" style="margin-bottom: 1.25rem;">
            <span>⚠️</span>
            <div><?= htmlspecialchars($error) ?></div>
        </div>
    <?php endif; ?>

    <?php if ($flashMsg = \App\Core\Session::getFlash('success')): ?>
        <div class="alert alert-success" style="margin-bottom: 1.25rem;">
            <span>✅</span>
            <div><?= htmlspecialchars($flashMsg) ?></div>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?= $baseURL ?>/login">
        <div class="form-group">
            <label class="form-label" for="username">Username / Email</label>
            <input type="text" id="username" name="username" class="form-control" placeholder="Enter username or email" required value="<?= htmlspecialchars($_POST['username'] ?? 'admin') ?>">
        </div>

        <div class="form-group" style="margin-bottom: 1.5rem;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.4rem;">
                <label class="form-label" for="password" style="margin-bottom:0;">Password</label>
                <span style="font-size:0.75rem;color:#0d9488;">Default: admin123</span>
            </div>
            <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required value="<?= htmlspecialchars($_POST['password'] ?? 'admin123') ?>">
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.75rem; font-size: 0.95rem;">
            🔐 Secure Sign In
        </button>
    </form>

    <!-- 1-Click Role Switcher for Testing 9 Specified Roles -->
    <div style="margin-top: 1.5rem;">
        <div style="font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; text-align: center;">
            ⚡ 1-Click Instant Demo Login (PDF Roles)
        </div>
        <form method="POST" action="<?= $baseURL ?>/login" class="role-switch-grid">
            <button type="submit" name="quick_role" value="super_admin" class="btn-role-quick">
                <span>👑</span>
                <span>Super Admin</span>
            </button>
            <button type="submit" name="quick_role" value="pharmacy_admin" class="btn-role-quick">
                <span>🏢</span>
                <span>Admin</span>
            </button>
            <button type="submit" name="quick_role" value="pharmacist" class="btn-role-quick">
                <span>💊</span>
                <span>Pharmacist</span>
            </button>
            <button type="submit" name="quick_role" value="store_manager" class="btn-role-quick">
                <span>📦</span>
                <span>Store Mgr</span>
            </button>
            <button type="submit" name="quick_role" value="purchase_manager" class="btn-role-quick">
                <span>📋</span>
                <span>Purchase Mgr</span>
            </button>
            <button type="submit" name="quick_role" value="billing_executive" class="btn-role-quick">
                <span>🧾</span>
                <span>Billing Exec</span>
            </button>
            <button type="submit" name="quick_role" value="cashier" class="btn-role-quick">
                <span>💵</span>
                <span>Cashier</span>
            </button>
            <button type="submit" name="quick_role" value="accountant" class="btn-role-quick">
                <span>⚖️</span>
                <span>Accountant</span>
            </button>
            <button type="submit" name="quick_role" value="sales_staff" class="btn-role-quick">
                <span>👥</span>
                <span>Sales Staff</span>
            </button>
        </form>
    </div>
</div>

</body>
</html>
