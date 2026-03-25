<?php
session_start();
include '../includes/db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../auth/login.php?error=unauthorized");
    exit();
}

$teacher_id = (int) $_SESSION['user_id'];
$page_title    = 'Dashboard';
$page_subtitle = 'Your assignments, attendance, and quick actions.';

$stmt = $conn->prepare("SELECT COUNT(*) as count FROM teacher_assignment WHERE teacher_id = ?");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$total_subjects = $stmt->get_result()->fetch_assoc()['count'];

$stmt = $conn->prepare("SELECT COUNT(*) as count FROM assignments WHERE teacher_id = ?");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$total_assignments = $stmt->get_result()->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Teacher Dashboard | SMS</title>
    <?php include '../includes/links.php'; ?>
    <link rel="stylesheet" href="../assets/index.css">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    <?php include '../includes/sidebar.php'; ?>

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
                        <span class="text-muted small fw-bold">Assigned Subjects</span>
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
                        <span class="text-muted small fw-bold">Posted Assignments</span>
                        </div>
                        <h2 class="fw-bold mb-2"><?php echo $total_assignments; ?></h2>
                        <p class="text-muted small mb-0">Total assignments posted for your classes.</p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="premium-panel p-4 h-100 bg-white rounded-4 border">
                        <h6 class="fw-bold mb-4 d-flex align-items-center text-dark">
                            <i class="fas fa-calendar-day text-primary me-2"></i> Today's Schedule
                        </h6>
                        <?php
                        $day_today = date('l');
                        $stmt = $conn->prepare("SELECT t.*, s.name as subject_name, c.name as class_name, c.section as class_section 
                                                 FROM timetables t
                                                 JOIN subjects s ON t.subject_id = s.id
                                                 JOIN classes c ON t.class_id = c.id
                                                 WHERE t.teacher_id = ? AND t.day_of_week = ?
                                                 ORDER BY t.start_time");
                        $stmt->bind_param("is", $teacher_id, $day_today);
                        $stmt->execute();
                        $today_res = $stmt->get_result();
                        
                        if($today_res->num_rows > 0): ?>
                            <div class="d-flex flex-column gap-3">
                                <?php while($slot = $today_res->fetch_assoc()): ?>
                                    <div class="p-3 bg-light rounded-3 d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="fw-bold text-dark mb-0" style="font-size:0.9rem"><?php echo htmlspecialchars($slot['subject_name']); ?></div>
                                            <div class="extra-small text-muted"><?php echo htmlspecialchars($slot['class_name'] . ' ' . $slot['class_section']); ?> • <?php echo htmlspecialchars($slot['room_no'] ?: 'TBA'); ?></div>
                                        </div>
                                        <div class="text-end">
                                            <div class="badge bg-primary-soft text-primary rounded-pill px-3"><?php echo date('H:i', strtotime($slot['start_time'])); ?></div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                                <a href="view_timetable.php" class="text-center small text-primary fw-bold text-decoration-none mt-2">View Full Timetable</a>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4 opacity-50">
                                <p class="small mb-0">No classes scheduled for today.</p>
                                <a href="view_timetable.php" class="extra-small text-primary text-decoration-none">Check Weekly Schedule</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="premium-panel bg-light border-0 rounded-4">
                        <div class="p-4">
                            <h6 class="fw-bold mb-4 d-flex align-items-center">
                                <i class="fas fa-calendar-alt text-primary me-2"></i>
                                Quick Actions
                            </h6>
                            <div class="d-grid gap-2">
                                <a href="attendance.php" class="btn btn-primary py-2 fw-bold small rounded-3">
                                    <i class="fas fa-clipboard-check me-2"></i>Take Attendance
                                </a>
                                <a href="my_attendance.php" class="btn btn-outline-primary py-2 fw-bold small rounded-3">
                                    <i class="fas fa-calendar-check me-2"></i>Mark My Attendance
                                </a>
                                <a href="manage_marks.php" class="btn btn-light border py-2 fw-bold small rounded-3">
                                    <i class="fas fa-star me-2"></i>Enter Marks
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .icon-circle { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; }
    </style>
    <?php include '../includes/scripts.php'; ?>
</body>
</html>
