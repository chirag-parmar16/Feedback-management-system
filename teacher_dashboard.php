<?php
session_start();
include './includes/db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: login.php");
    exit();
}

$page_title = 'Faculty Command Center';
$page_subtitle = 'Manage your academic portfolio and student interactions.';
$teacher_id = $_SESSION['user_id'];
$total_subjects = $conn->query("SELECT COUNT(*) as count FROM teacher_assignment WHERE teacher_id = $teacher_id")->fetch_assoc()['count'];
$total_assignments = $conn->query("SELECT COUNT(*) as count FROM assignments WHERE teacher_id = $teacher_id")->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Teacher Dashboard | SMS</title>
    <?php include './includes/links.php'; ?>
    <link rel="stylesheet" href="index.css">
</head>
<body>
    <?php include './includes/navbar.php'; ?>
    <?php include './includes/sidebar.php'; ?>

    <div class="content-wrapper">
        <div class="container">

            <!-- Dashboard Highlights -->
            <div class="row g-4 pt-3">
                <div class="col-md-4">
                    <div class="premium-panel">
                        <div class="d-flex align-items-center mb-3">
                            <div class="icon-circle bg-primary-soft me-3">
                                <i class="fas fa-book-reader"></i>
                            </div>
                            <span class="text-muted small fw-bold">ACADEMIC SUBJECTS</span>
                        </div>
                        <h2 class="fw-bold mb-2"><?php echo $total_subjects; ?></h2>
                        <p class="text-muted small mb-0">Total active subject assignments for the current term.</p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="premium-panel">
                        <div class="d-flex align-items-center mb-3">
                            <div class="icon-circle bg-success-soft me-3">
                                <i class="fas fa-tasks"></i>
                            </div>
                            <span class="text-muted small fw-bold">PENDING ASSESSMENTS</span>
                        </div>
                        <h2 class="fw-bold mb-2"><?php echo $total_assignments; ?></h2>
                        <p class="text-muted small mb-0">Active assignment campaigns requiring review or grading.</p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="premium-panel bg-light border-0 rounded-4">
                        <div class="p-4">
                            <h6 class="fw-bold mb-3 d-flex align-items-center">
                                <i class="fas fa-calendar-alt text-primary me-2"></i>
                                Next Session
                            </h6>
                            <div class="small text-muted">
                                <p class="mb-1">Mathematics | Grade 10-A</p>
                                <p class="mb-0 fw-bold text-dark">Today, 10:30 AM</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .icon-circle { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; }
        .bg-primary-light { background: #eef2ff; color: #4f46e5; }
        .bg-success-light { background: #ecfdf5; color: #10b981; }
        .btn-light:hover { background: #f8fafc !important; }
    </style>
    <?php include './includes/scripts.php'; ?>
</body>
</html>
