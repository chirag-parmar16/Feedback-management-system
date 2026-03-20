<?php
session_start();
include './includes/db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$page_title = 'System Personnel';
$page_subtitle = 'Overview of all registered administrators, teachers, and students.';
$message = '';
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $conn->query("DELETE FROM users WHERE id = $delete_id");
    $message = "User account successfully removed from the system.";
}

$users = $conn->query("SELECT u.id, u.username, u.email, u.role, u.created_at, p.first_name, p.last_name 
                        FROM users u 
                        LEFT JOIN profile_info p ON u.id = p.user_id 
                        ORDER BY u.created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users | Admin</title>
    <?php include './includes/links.php'; ?>
    <link rel="stylesheet" href="index.css">
    <style>
        .table thead th { background: transparent; border-top: none; border-bottom: 1px solid #f1f5f9; padding-bottom: 15px; }
        .table tbody td { border-bottom: 1px solid #f8fafc; padding: 15px 0; }
    </style>
</head>

<body>
    <?php include './includes/navbar.php'; ?>
    <?php include './includes/sidebar.php'; ?>

    <div class="content-wrapper">
        <div class="container py-4">
            <div class="d-flex justify-content-end mb-5">
                <a href="add_user.php" class="btn btn-primary rounded-pill px-4 py-2 fw-bold">
                    <i class="fas fa-plus me-2"></i> Register New User
                </a>
            </div>

            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert bg-success-soft text-success border-0 small py-3 mb-4 fw-bold"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
            <?php endif; ?>

            <?php if ($message): ?>
                <div class="alert bg-danger-soft text-danger border-0 small py-3 mb-4 fw-bold"><?php echo $message; ?></div>
            <?php endif; ?>

            <div class="user-list-section">
                <div class="table-responsive">
                    <table class="table">
                        <thead class="text-muted small">
                            <tr>
                                <th class="ps-0">IDENTITY</th>
                                <th>PORTAL EMAIL</th>
                                <th>ACCESS ROLE</th>
                                <th>JOINED DATE</th>
                                <th class="text-end">ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($users->num_rows > 0): ?>
                                <?php while($u = $users->fetch_assoc()): ?>
                                    <tr>
                                        <td class="ps-0">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm bg-primary-soft text-primary me-3 rounded-circle d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                                                    <i class="fas fa-user small"></i>
                                                </div>
                                                <span class="fw-bold text-dark"><?php echo htmlspecialchars($u['username']); ?></span>
                                            </div>
                                        </td>
                                        <td class="small text-muted"><?php echo htmlspecialchars($u['email']); ?></td>
                                        <td>
                                            <?php 
                                                $badgeClass = 'border-primary-soft text-primary';
                                                if($u['role'] == 'teacher') $badgeClass = 'border-success-soft text-success';
                                                if($u['role'] == 'admin') $badgeClass = 'border-danger-soft text-danger';
                                            ?>
                                            <span class="badge border <?php echo $badgeClass; ?> px-3 py-2 rounded-pill small">
                                                <?php echo strtoupper($u['role']); ?>
                                            </span>
                                        </td>
                                        <td class="small text-muted"><?php echo date('M d, Y', strtotime($u['created_at'])); ?></td>
                                        <td class="text-end pe-4">
                                            <div class="d-flex justify-content-end gap-2">
                                                <a href="add_user.php?edit_id=<?php echo $u['id']; ?>" class="btn btn-light btn-sm border px-3">Edit</a>
                                                <a href="manage_users.php?delete_id=<?php echo $u['id']; ?>" class="btn btn-light-danger btn-sm border-0 px-3" onclick="return confirm('Archive this user?')">Archive</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted small">No registered personnel found in the system.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <style> .extra-small { font-size: 0.65rem; letter-spacing: 0.05em; text-transform: uppercase; } </style>
    <?php include './includes/scripts.php'; ?>
</body>
</html>
