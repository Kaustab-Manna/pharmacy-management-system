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
            font-family: var(--font-main, system-ui, -apple-system, sans-serif);
        }
        .login-card {
            background: #ffffff;
            border-radius: var(--radius-lg, 12px);
            width: 100%;
            max-width: 440px;
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
            margin: 0;
        }
        .login-subtitle {
            font-size: 0.85rem;
            color: #64748b;
            margin-top: 0.25rem;
        }
        .password-input-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }
        .password-input-wrap input {
            padding-right: 42px;
            width: 100%;
        }
        .toggle-password-btn {
            position: absolute;
            right: 10px;
            background: none;
            border: none;
            padding: 4px;
            cursor: pointer;
            color: #64748b;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            transition: color 0.15s ease;
        }
        .toggle-password-btn:hover {
            color: #0d9488;
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

    <!-- Secure Login Form -->
    <form method="POST" action="<?= $baseURL ?>/login" id="loginForm">
        <div class="form-group">
            <label class="form-label" for="username">Username / Email</label>
            <input type="text" id="username" name="username" class="form-control" placeholder="Enter username or email" required autofocus value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
        </div>

        <div class="form-group" style="margin-bottom: 1.5rem;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.4rem;">
                <label class="form-label" for="password" style="margin-bottom:0;">Password</label>
            </div>
            <div class="password-input-wrap">
                <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required value="">
                <button type="button" class="toggle-password-btn" id="togglePasswordBtn" title="Toggle password visibility">
                    <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                    <svg id="eyeSlashIcon" style="display:none;" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                        <line x1="1" y1="1" x2="23" y2="23"></line>
                    </svg>
                </button>
            </div>
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.75rem; font-size: 0.95rem;">
            🔐 Secure Sign In
        </button>
    </form>
</div>

<script>
    // Password visibility toggle
    const toggleBtn = document.getElementById('togglePasswordBtn');
    const pwdInput = document.getElementById('password');
    const eyeIcon = document.getElementById('eyeIcon');
    const eyeSlashIcon = document.getElementById('eyeSlashIcon');

    if (toggleBtn && pwdInput) {
        toggleBtn.addEventListener('click', function () {
            const isPassword = pwdInput.type === 'password';
            pwdInput.type = isPassword ? 'text' : 'password';
            eyeIcon.style.display = isPassword ? 'none' : 'block';
            eyeSlashIcon.style.display = isPassword ? 'block' : 'none';
        });
    }
</script>

</body>
</html>
