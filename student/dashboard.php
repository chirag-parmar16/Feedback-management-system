<?php
session_start();
include '../includes/db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../auth/login.php?error=unauthorized");
    exit();
}

$page_title = 'Student Academic Hub';
$page_subtitle = 'Your personalized education portal and progress tracker.';
$student_id = (int) $_SESSION['user_id'];

// Check for pending feedback — join with enrollment to only count forms for student's class
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM feedback_forms f 
                          JOIN student_enrollment e ON f.class_id = e.class_id
                          WHERE f.status = 'active' 
                          AND e.student_id = ?
                          AND f.id NOT IN (SELECT form_id FROM feedback_responses WHERE student_id = ?)");
$stmt->bind_param("ii", $student_id, $student_id);
$stmt->execute();
$pending_feedback = $stmt->get_result()->fetch_assoc()['count'];

// Academic Metrics — prepared statements
$stmt = $conn->prepare("SELECT COUNT(*) FROM student_enrollment WHERE student_id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$total_classes = $stmt->get_result()->fetch_row()[0];

$stmt = $conn->prepare("SELECT COUNT(*) FROM assignments a 
                         JOIN teacher_assignment ta ON a.subject_id = ta.subject_id 
                         JOIN student_enrollment e ON ta.class_id = e.class_id 
                         WHERE e.student_id = ? AND a.deadline > NOW()
                         AND a.id NOT IN (SELECT assignment_id FROM assignment_submissions WHERE student_id = ?)");
$stmt->bind_param("ii", $student_id, $student_id);
$stmt->execute();
$total_assignments = $stmt->get_result()->fetch_row()[0];

// Attendance Average
$stmt = $conn->prepare("SELECT 
    (SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) / COUNT(*)) * 100 as avg_att 
    FROM attendance WHERE student_id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$avg_attendance = round($stmt->get_result()->fetch_assoc()['avg_att'] ?? 0, 1);

// Published Results Check
$stmt_res = $conn->prepare("SELECT COUNT(*) FROM results WHERE student_id = ?");
$stmt_res->bind_param("i", $student_id);
$stmt_res->execute();
$has_published_results = $stmt_res->get_result()->fetch_row()[0] > 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Portal | SMS</title>
    <?php include '../includes/links.php'; ?>
    <link rel="stylesheet" href="../assets/index.css">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    <?php include '../includes/sidebar.php'; ?>

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
                <div class="col-md-6">
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
                <div class="col-md-6">
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

            <?php if($has_published_results): ?>
                <div class="alert alert-success border-0 shadow-sm d-flex align-items-center mb-4 px-4 py-3 rounded-4">
                    <i class="fas fa-medal text-success me-3 h4 mb-0"></i>
                    <div class="flex-grow-1">
                        <p class="small fw-bold mb-0">New Academic Results Published!</p>
                        <p class="extra-small mb-0 opacity-75">Your official report card is now available for download.</p>
                    </div>
                    <a href="report_card.php" class="btn btn-success btn-sm rounded-pill px-4 fw-bold">View Report Card</a>
                </div>
            <?php endif; ?>

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
                        <a href="submit_assignment.php" class="btn btn-light w-100 py-3 border small fw-bold text-dark">
                            <i class="fas fa-file-alt me-2 text-primary"></i> Tasks &amp; Projects
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
                <div class="col-md-7">
                    <div class="premium-panel p-4 h-100 bg-white rounded-4 border">
                        <h6 class="fw-bold mb-4 d-flex align-items-center text-dark">
                            <i class="fas fa-calendar-day text-primary me-2"></i> Today's Schedule
                        </h6>
                        <?php
                        $day_today = date('l');
                        $stmt = $conn->prepare("SELECT t.*, s.name as subject_name, u.username as teacher_name, p.first_name, p.last_name 
                                                 FROM timetables t
                                                 JOIN subjects s ON t.subject_id = s.id
                                                 JOIN users u ON t.teacher_id = u.id
                                                 LEFT JOIN profile_info p ON u.id = p.user_id
                                                 JOIN student_enrollment se ON t.class_id = se.class_id
                                                 WHERE se.student_id = ? AND t.day_of_week = ?
                                                 ORDER BY t.start_time");
                        $stmt->bind_param("is", $student_id, $day_today);
                        $stmt->execute();
                        $today_res = $stmt->get_result();
                        
                        if($today_res->num_rows > 0): ?>
                            <div class="d-flex flex-column gap-3">
                                <?php while($slot = $today_res->fetch_assoc()): ?>
                                    <div class="p-3 bg-light rounded-3 d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="fw-bold text-dark mb-0" style="font-size:0.9rem"><?php echo htmlspecialchars($slot['subject_name']); ?></div>
                                            <div class="extra-small text-muted"><?php echo htmlspecialchars($slot['first_name'] ?: $slot['teacher_name']); ?> • <?php echo htmlspecialchars($slot['room_no'] ?: 'TBA'); ?></div>
                                        </div>
                                        <div class="text-end">
                                            <div class="badge bg-primary-soft text-primary rounded-pill px-3"><?php echo date('H:i', strtotime($slot['start_time'])); ?></div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4 opacity-50">
                                <p class="small mb-0">No classes scheduled for today.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="premium-panel p-4 h-100 bg-white border rounded-4 border-warning border-3 border-top-0 border-bottom-0 border-end-0 shadow-sm">
                        <h6 class="fw-bold mb-4 d-flex align-items-center text-warning">
                            <i class="fas fa-bullhorn me-3"></i> Notice Board
                        </h6>
                        <div class="list-group list-group-flush bg-transparent">
                            <div class="list-group-item bg-transparent border-light-subtle px-0 py-3">
                                <p class="extra-small text-muted mb-1 fw-bold">ADMINISTRATION</p>
                                <h6 class="fw-bold mb-0 text-dark" style="font-size:0.9rem">Term vacation starting from next Monday.</h6>
                            </div>
                            <div class="list-group-item bg-transparent border-light-subtle px-0 py-2">
                                <p class="extra-small text-muted mb-1 fw-bold">PRINCIPAL</p>
                                <h6 class="fw-bold mb-0 text-dark" style="font-size:0.9rem">Annual cultural festival volunteers needed.</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/scripts.php'; ?>
</body>
</html>
