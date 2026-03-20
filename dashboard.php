<?php
session_start();
include './includes/db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

$page_title = 'Student Academic Hub';
$page_subtitle = 'Your personalized education portal and progress tracker.';
$student_id = $_SESSION['user_id'];

// Check for pending feedback
$pending_feedback = $conn->query("SELECT COUNT(*) as count FROM feedback_forms f 
                                  WHERE f.status = 'active' 
                                  AND f.id NOT IN (SELECT form_id FROM feedback_responses WHERE student_id = $student_id)")->fetch_assoc()['count'];

// Academic Metrics
$total_classes = $conn->query("SELECT COUNT(*) FROM student_enrollment WHERE student_id = $student_id")->fetch_row()[0];
$total_assignments = $conn->query("SELECT COUNT(*) FROM assignments a 
                                  JOIN teacher_assignment ta ON a.subject_id = ta.subject_id 
                                  JOIN student_enrollment e ON ta.class_id = e.class_id 
                                  WHERE e.student_id = $student_id")->fetch_row()[0];

// Basic attendance average calculation
$att_res = $conn->query("SELECT 
    (SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) / COUNT(*)) * 100 as avg_att 
    FROM attendance WHERE student_id = $student_id");
$avg_attendance = round($att_res->fetch_assoc()['avg_att'] ?? 0, 1);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Portal | SMS</title>
    <?php include './includes/links.php'; ?>
    <link rel="stylesheet" href="index.css">
</head>
<body>
    <?php include './includes/navbar.php'; ?>
    <?php include './includes/sidebar.php'; ?>

    <div class="content-wrapper">
        <div class="container">
            <div class="d-flex justify-content-between align-items-end mb-5">
                <?php if($pending_feedback > 0): ?>
                    <div class="alert alert-info border-0 shadow-sm d-flex align-items-center mb-0 px-4 py-3 rounded-4">
                        <i class="fas fa-bullhorn text-info me-3 h4 mb-0"></i>
                        <div>
                            <p class="small fw-bold mb-0">Pending Feedback Required</p>
                            <a href="feedback.php" class="text-info small fw-bold text-decoration-underline">Fill Feedback Now</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <!-- Academic Quick Stats -->
            <div class="row g-4 mb-4 pt-2">
                <div class="col-md-4">
                    <div class="premium-panel border-0 bg-primary text-white p-4 h-100 rounded-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h1 class="fw-bold mb-0"><?php echo $total_classes; ?></h1>
                                <p class="small opacity-75 mb-0 fw-bold uppercase">ACTIVE SUBJECTS</p>
                            </div>
                            <div class="bg-white bg-opacity-25 p-3 rounded-4"><i class="fas fa-book"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="premium-panel border-0 bg-secondary text-white p-4 h-100 rounded-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h1 class="fw-bold mb-0"><?php echo $total_assignments; ?></h1>
                                <p class="small opacity-75 mb-0 fw-bold uppercase">OPEN TASKS</p>
                            </div>
                            <div class="bg-white bg-opacity-25 p-3 rounded-4"><i class="fas fa-tasks"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="premium-panel border-0 bg-accent text-white p-4 h-100 rounded-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h1 class="fw-bold mb-0"><?php echo $avg_attendance; ?>%</h1>
                                <p class="small opacity-75 mb-0 fw-bold uppercase">ATTENDANCE</p>
                            </div>
                            <div class="bg-white bg-opacity-25 p-3 rounded-4"><i class="fas fa-check-circle"></i></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Student Platform Hub -->
            <div class="premium-panel mb-5">
                <div class="flat-header">
                    <h5>Student Terminal Access</h5>
                    <p>Academic tools and progress monitoring for your education portal.</p>
                </div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <a href="my_performance.php" class="btn btn-light w-100 py-3 border small fw-bold text-dark">
                            <i class="fas fa-chart-line me-2 text-primary"></i> Academic Insights
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="assignments.php" class="btn btn-light w-100 py-3 border small fw-bold text-dark">
                            <i class="fas fa-file-alt me-2 text-primary"></i> Tasks & Projects
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="feedback.php" class="btn btn-light w-100 py-3 border small fw-bold text-dark">
                            <i class="fas fa-comment-dots me-2 text-primary"></i> Faculty Feedback
                        </a>
                    </div>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-md-5">
                    <div class="premium-panel p-4 h-100 bg-dark text-white rounded-4">
                        <h6 class="fw-bold mb-4 d-flex align-items-center">
                            <i class="fas fa-bullhorn text-warning me-2"></i> Notice Board
                        </h6>
                        <div class="list-group list-group-flush bg-transparent">
                            <div class="list-group-item bg-transparent border-white-50 px-0 py-3">
                                <p class="small text-white-50 mb-1">ADMINISTRATION</p>
                                <h6 class="fw-bold mb-0">Term vacation starting from next Monday.</h6>
                            </div>
                            <div class="list-group-item bg-transparent border-white-50 px-0 py-2">
                                <p class="small text-white-50 mb-1">PRINCIPAL</p>
                                <h6 class="fw-bold mb-0">Annual cultural festival volunteers needed.</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include './includes/scripts.php'; ?>
</body>
</html>
