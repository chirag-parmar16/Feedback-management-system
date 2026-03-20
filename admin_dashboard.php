<?php
session_start();
include './includes/db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$page_title = 'Administrative Oversight';
$page_subtitle = 'System-wide monitoring and institutional statistics.';
// Fetch stats - Ensured feedback_responses exists now
$total_students = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'student'")->fetch_assoc()['count'];
$total_teachers = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'teacher'")->fetch_assoc()['count'];
$total_classes = $conn->query("SELECT COUNT(*) as count FROM classes")->fetch_assoc()['count'];

// Handle potential missing table gracefully just in case, though we created it
$feedback_query = $conn->query("SHOW TABLES LIKE 'feedback_responses'");
if ($feedback_query->num_rows > 0) {
    $total_feedback = $conn->query("SELECT COUNT(*) as count FROM feedback_responses")->fetch_assoc()['count'];
} else {
    $total_feedback = 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard | SMS</title>
    <?php include './includes/links.php'; ?>
    <link rel="stylesheet" href="index.css">
</head>
<body>
    <?php include './includes/navbar.php'; ?>
    <?php include './includes/sidebar.php'; ?>

    <div class="content-wrapper">
        <div class="container py-4">

            <!-- Stats Grid -->
            <div class="row g-4 mb-4 pt-2">
                <div class="col-md-3">
                    <div class="premium-panel border-0 bg-primary text-white p-4 h-100 rounded-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h1 class="fw-bold mb-0"><?php echo $total_students; ?></h1>
                                <p class="small opacity-75 mb-0 fw-bold">ENROLLED STUDENTS</p>
                            </div>
                            <div class="bg-white bg-opacity-25 p-3 rounded-4">
                                <i class="fas fa-user-graduate fa-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- ... other stats optimized similarly ... -->
                <div class="col-md-3">
                    <div class="premium-panel border-0 bg-secondary text-white p-4 h-100">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h2 class="fw-bold mb-0"><?php echo $total_teachers; ?></h2>
                                <p class="small opacity-75 mb-0">Total Faculty</p>
                            </div>
                            <div class="bg-white bg-opacity-25 p-3 rounded-4">
                                <i class="fas fa-chalkboard-teacher"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="premium-panel border-0 bg-accent text-white p-4 h-100">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h2 class="fw-bold mb-0"><?php echo $total_classes; ?></h2>
                                <p class="small opacity-75 mb-0">Academic Classes</p>
                            </div>
                            <div class="bg-white bg-opacity-25 p-3 rounded-4">
                                <i class="fas fa-school"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="premium-panel border-0 bg-info text-white p-4 h-100">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h2 class="fw-bold mb-0"><?php echo $total_feedback; ?></h2>
                                <p class="small opacity-75 mb-0">Feedbacks Logged</p>
                            </div>
                            <div class="bg-white bg-opacity-25 p-3 rounded-4">
                                <i class="fas fa-comments"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions Panel -->
            <div class="premium-panel mb-5">
                <div class="flat-header">
                    <h5>Institutional Management Controls</h5>
                    <p>Core administrative actions to maintain system integrity.</p>
                </div>
                <div class="row g-3">
                    <div class="col-md-3">
                        <a href="manage_users.php" class="btn btn-light w-100 py-3 border small fw-bold text-dark">
                            <i class="fas fa-users-cog me-2 text-primary"></i> User Registry
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="manage_classes.php" class="btn btn-light w-100 py-3 border small fw-bold text-dark">
                            <i class="fas fa-layer-group me-2 text-primary"></i> Class Streams
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="enroll_students.php" class="btn btn-light w-100 py-3 border small fw-bold text-dark">
                            <i class="fas fa-user-plus me-2 text-primary"></i> Student Placement
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="admin_feedback.php" class="btn btn-light w-100 py-3 border small fw-bold text-dark">
                            <i class="fas fa-comment-medical me-2 text-primary"></i> Feedback Config
                        </a>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-md-8">
                    <div class="premium-panel bg-light p-4 rounded-4 mb-4">
                        <h6 class="fw-bold mb-1">System Insights</h6>
                        <p class="text-muted mb-4 small">Key performance and engagement metrics.</p>
                        <div class="d-flex align-items-center justify-content-center py-5">
                            <div class="text-center opacity-50">
                                <i class="fas fa-chart-line display-1 mb-3"></i>
                                <p class="text-muted small">Analytical data visualization will appear here as the database matures.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="premium-panel p-4 bg-white border border-light rounded-4">
                        <h6 class="fw-bold mb-4 small text-muted text-uppercase tracking-widest">Administrative Actions</h6>
                        <div class="d-grid gap-2">
                            <a href="add_user.php" class="btn btn-primary py-3 fw-bold rounded-3 text-start px-4">
                                <i class="fas fa-plus-circle me-2"></i> Register New Faculty/Student
                            </a>
                            <a href="manage_classes.php" class="btn btn-outline-primary border-0 py-3 fw-medium text-start px-4 bg-light">
                                <i class="fas fa-layer-group me-2"></i> Initialize Academic Stream
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include './includes/scripts.php'; ?>
</body>
</html>
