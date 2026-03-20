<?php
session_start();
include './includes/db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: login.php");
    exit();
}

$teacher_id = $_SESSION['user_id'];
$message = '';

if (isset($_POST['mark_own'])) {
    $date = date('Y-m-d');
    $status = 'Present';
    $stmt = $conn->prepare("INSERT INTO teacher_attendance (teacher_id, date, status) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE status = VALUES(status)");
    $stmt->bind_param("iss", $teacher_id, $date, $status);
    $stmt->execute();
    $message = "Your attendance for today has been marked!";
}

$history = $conn->query("SELECT * FROM teacher_attendance WHERE teacher_id = $teacher_id ORDER BY date DESC LIMIT 30");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Attendance | SMS</title>
    <?php include './includes/links.php'; ?>
    <link rel="stylesheet" href="index.css">
</head>

<body>
    <?php include './includes/navbar.php'; ?>
    <?php include './includes/sidebar.php'; ?>

    <div class="content-wrapper">
        <div class="container py-4">
            <h2 class="mb-4 fw-bold">My Attendance</h2>

            <?php if ($message): ?>
                <div class="alert alert-success border-0 shadow-sm p-3 rounded-3 mb-4 text-center fw-bold"><?php echo $message; ?></div>
            <?php endif; ?>

            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card border-0 p-4 text-center">
                        <i class="fas fa-user-check text-primary display-4 mb-3"></i>
                        <h4 class="fw-bold">Mark Presence</h4>
                        <p class="text-muted small mb-4">Click the button below to mark your attendance for today (<?php echo date('d M, Y'); ?>).</p>
                        <form method="POST">
                            <button type="submit" name="mark_own" class="btn btn-primary w-100 py-3 fw-bold rounded-pill">I am Present Today</button>
                        </form>
                    </div>
                </div>
                
                <div class="col-md-8">
                    <div class="card border-0 p-4">
                        <h5 class="fw-bold mb-4">My Attendance History (Last 30 Days)</h5>
                        <div class="table-responsive">
                            <table class="table">
                                <thead class="text-muted small">
                                    <tr>
                                        <th>DATE</th>
                                        <th>STATUS</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($history->num_rows > 0): ?>
                                        <?php while($h = $history->fetch_assoc()): ?>
                                            <tr>
                                                <td class="fw-semibold"><?php echo date('M d, Y', strtotime($h['date'])); ?></td>
                                                <td><span class="badge bg-success-light text-success px-3">Present</span></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr><td colspan="2" class="text-center py-4 text-muted">No attendance logs found.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style> .bg-success-light { background: rgba(25, 135, 84, 0.1); } </style>
    <?php include './includes/scripts.php'; ?>
</body>
</html>
