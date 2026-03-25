<?php
session_start();
include '../includes/db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$page_title = 'User Profile';
$page_subtitle = 'Personal information and account settings.';
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT u.*, p.* FROM users u LEFT JOIN profile_info p ON u.id = p.user_id WHERE u.id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Profile | SMS</title>
    <?php include '../includes/links.php'; ?>
    <link rel="stylesheet" href="../assets/index.css">
    <style>
        .profile-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            height: 160px;
            border-radius: 20px;
            margin-bottom: -60px;
        }
        .profile-avatar {
            width: 120px;
            height: 120px;
            background: white;
            border-radius: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            font-weight: 800;
            color: var(--primary);
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
            margin: 0 auto;
            border: 4px solid white;
        }
    </style>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    <?php include '../includes/sidebar.php'; ?>

    <div class="content-wrapper">
        <div class="container" style="max-width: 900px;">
            <div class="profile-header"></div>
            <div class="card p-5 border-0 shadow-sm text-center">
                <div class="profile-avatar mb-4">
                    <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                </div>
                
                <h2 class="fw-bold mb-1"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h2>
                <p class="text-muted mb-4"><?php echo strtoupper($user['role']); ?> • <?php echo htmlspecialchars($user['email']); ?></p>

                <div class="row g-4 text-start mt-2">
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded-4">
                            <label class="form-label d-block text-muted extra-small">Enrollment / ID</label>
                            <span class="fw-bold"><?php echo htmlspecialchars($user['Enroll_No'] ?: 'Not Set'); ?></span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded-4">
                            <label class="form-label d-block text-muted extra-small">Phone Number</label>
                            <span class="fw-bold"><?php echo htmlspecialchars($user['phone'] ?: 'No Phone'); ?></span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded-4">
                            <label class="form-label d-block text-muted extra-small">Member Since</label>
                            <span class="fw-bold"><?php echo date('M Y', strtotime($user['created_at'])); ?></span>
                        </div>
                    </div>
                </div>

                <div class="mt-5 pt-4 border-top">
                    <a href="dashboard.php" class="btn btn-primary px-5">Back to Dashboard</a>
                </div>
            </div>
        </div>
    </div>
    <style> .extra-small { font-size: 0.65rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em; color: var(--text-muted); margin-bottom: 4px !important; } </style>
    <?php include '../includes/scripts.php'; ?>
</body>
</html>
