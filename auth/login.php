<?php
ob_start(); // Buffer all output so header() redirects always work
session_start();
include '../includes/db_connection.php';

$error = '';

// Handle unauthorized redirect-back
if (isset($_GET['error']) && $_GET['error'] === 'unauthorized') {
    $error = "Your session expired or you don't have permission to access that page. Please sign in again.";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email    = trim($_POST['email'] ?? '');


    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role']     = $user['role'];
            $_SESSION['toast']    = ['message' => 'Welcome back, ' . $user['username'] . '!', 'type' => 'success'];

            session_write_close();

            if ($user['role'] === 'admin')   { header("Location: ../admin/admin_dashboard.php"); }
            elseif ($user['role'] === 'teacher') { header("Location: ../teacher/teacher_dashboard.php"); }
            else { header("Location: ../student/dashboard.php"); }
            exit(); 
 

        } else {
            $error = "Incorrect password. Please try again.";
        }
    } else {
        $error = "No account found with this email address.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — SMS Portal</title>
    <?php include '../includes/links.php'; ?>
    <link rel="stylesheet" href="../assets/index.css">
    <style>
        body {
            background: #0f172a;
            background-image:
                radial-gradient(at 20% 20%, rgba(79, 70, 229, 0.18) 0px, transparent 55%),
                radial-gradient(at 80% 80%, rgba(168, 85, 247, 0.12) 0px, transparent 55%);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }

        .login-wrap { width: 100%; max-width: 420px; }

        .login-card {
            background: #ffffff;
            border-radius: 20px;
            padding: 44px 40px;
            box-shadow: 0 32px 64px rgba(0,0,0,0.45), 0 0 0 1px rgba(255,255,255,0.05);
        }

        .brand-mark {
            width: 56px; height: 56px;
            background: var(--primary);
            border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.4rem; color: white;
            margin: 0 auto 20px;
            box-shadow: 0 12px 24px rgba(79, 70, 229, 0.4);
        }

        .login-card h2 { font-size: 1.5rem; font-weight: 800; letter-spacing: -0.03em; margin-bottom: 4px; }
        .login-card .sub { font-size: 0.8rem; color: var(--text-muted); font-weight: 500; margin-bottom: 28px; }

        .field-group { margin-bottom: 16px; }

        .input-pw-wrap { position: relative; }
        .input-pw-wrap .form-control { padding-right: 42px !important; }
        .pw-toggle { position: absolute; right: 13px; top: 50%; transform: translateY(-50%); color: var(--text-muted); }
        .pw-toggle:hover { color: var(--primary); }

        .login-btn {
            width: 100%; padding: 13px;
            background: var(--primary);
            color: white; border: none;
            border-radius: 10px;
            font-weight: 700; font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.15s;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
            margin-top: 4px;
        }
        .login-btn:hover { background: #3730a3; box-shadow: 0 8px 20px rgba(79, 70, 229, 0.35); transform: translateY(-1px); }
        .login-btn:active { transform: translateY(0); }

        .error-box {
            background: rgba(239, 68, 68, 0.08);
            border: 1px solid rgba(239, 68, 68, 0.2);
            border-radius: 10px;
            color: var(--danger);
            padding: 11px 14px;
            font-size: 0.85rem;
            font-weight: 500;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .footer-note { font-size: 0.7rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; color: #94a3b8; text-align: center; margin-top: 28px; }
    </style>
</head>
<body>
    <div class="login-wrap">
        <div class="login-card">
            <div class="text-center">
                <div class="brand-mark">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <h2>SMS Portal</h2>
                <p class="sub">Sign in to your account to continue</p>
            </div>

            <?php if ($error): ?>
                <div class="error-box">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" id="loginForm">
                <div class="field-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control"
                           placeholder="you@school.com"
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                           required autofocus>
                </div>

                <div class="field-group">
                    <label class="form-label">Password</label>
                    <div class="input-pw-wrap">
                        <input type="password" name="password" id="passwordInput"
                               class="form-control" placeholder="••••••••" required>
                        <span class="pw-toggle pw-toggle" data-target="#passwordInput" title="Show/hide password">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                </div>

                <div class="form-check mb-4 mt-2">
                    <input class="form-check-input" type="checkbox" id="remember" name="remember">
                    <label class="form-check-label" for="remember"
                           style="font-size:.825rem;font-weight:500;color:var(--text-muted);text-transform:none;letter-spacing:0">
                        Keep me signed in
                    </label>
                </div>

                <button type="submit" name="login" class="login-btn" id="loginBtn" data-no-loading="1">
                    Sign In
                </button>
            </form>

            <p class="footer-note">Restricted to authorized personnel only</p>
        </div>
    </div>
    <?php include '../includes/scripts.php'; ?>
    <script>
        // Loading state for login button
        document.getElementById('loginForm').addEventListener('submit', function() {
            const btn = document.getElementById('loginBtn');
            btn.innerHTML = '<i class="fas fa-circle-notch fa-spin me-2"></i>Signing in...';
            // btn.disabled = true; <-- Removing this to ensure the button name="login" is sent
        });

    </script>
</body>
</html>
