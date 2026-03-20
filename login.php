<?php
session_start();
include './includes/db_connection.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] == 'admin') header("Location: admin_dashboard.php");
            elseif ($user['role'] == 'teacher') header("Location: teacher_dashboard.php");
            else header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid credentials. Please try again.";
        }
    } else {
        $error = "User account not recognized.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | SMS Portal</title>
    <?php include './includes/links.php'; ?>
    <link rel="stylesheet" href="index.css">
    <style>
        body {
            background-color: #0f172a;
            background-image: radial-gradient(at 0% 0%, rgba(79, 70, 229, 0.15) 0px, transparent 50%), 
                              radial-gradient(at 100% 100%, rgba(168, 85, 247, 0.1) 0px, transparent 50%);
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }
        .login-container {
            width: 100%;
            max-width: 440px;
            padding: 20px;
        }
        .login-card {
            background: #ffffff;
            border-radius: 24px;
            padding: 48px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }
        .brand-logo {
            width: 64px;
            height: 64px;
            background: var(--primary);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            margin: 0 auto 24px auto;
            box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.4);
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="text-center mb-5">
                <div class="brand-logo">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <h2 class="fw-bold mb-1">SMS PORTAL</h2>
                <p class="text-muted small fw-medium">ENTER YOUR CREDENTIALS TO CONTINUE</p>
            </div>

            <?php if ($error): ?>
                <div class="alert bg-danger-soft border-0 small py-3 mb-4 text-center fw-bold text-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-4">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" placeholder="name@school.com" required>
                </div>
                <div class="mb-4">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>
                
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="remember">
                        <label class="form-check-label small text-muted fw-medium" for="remember">Keep me signed in</label>
                    </div>
                </div>

                <button type="submit" name="login" class="btn btn-primary w-100 py-3 fw-bold">Sign In to Dashboard</button>
            </form>

            <div class="text-center mt-5">
                <p class="text-muted extra-small mb-0">System access restricted to authorized personnel only.</p>
            </div>
        </div>
    </div>
    <style> .extra-small { font-size: 0.65rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; } </style>
    <?php include './includes/scripts.php'; ?>
</body>
</html>
