<?php
session_start();
include '../includes/db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../auth/login.php"); exit();
}

$student_id = $_SESSION['user_id'];
$page_title = 'My Class Timetable';
$page_subtitle = 'Your weekly academic schedule';

// Get student's class
$class_res = $conn->query("SELECT class_id, c.name, c.section 
                           FROM student_enrollment se 
                           JOIN classes c ON se.class_id = c.id 
                           WHERE student_id = $student_id");
$class_info = $class_res->fetch_assoc();
$class_id = $class_info['class_id'] ?? 0;

$timetable = [];
if ($class_id > 0) {
    $res = $conn->query("SELECT t.*, s.name as subject_name, u.username as teacher_name, p.first_name, p.last_name 
                         FROM timetables t
                         JOIN subjects s ON t.subject_id = s.id
                         JOIN users u ON t.teacher_id = u.id
                         LEFT JOIN profile_info p ON u.id = p.user_id
                         WHERE t.class_id = $class_id
                         ORDER BY t.start_time");
    while($row = $res->fetch_assoc()) {
        $timetable[$row['day_of_week']][] = $row;
    }
}

$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Timetable | SMS</title>
    <?php include '../includes/links.php'; ?>
    <link rel="stylesheet" href="../assets/index.css">
    <style>
        .timetable-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; height: 100%; transition: all 0.2s; }
        .slot-time { font-size: 0.75rem; font-weight: 700; color: var(--primary); background: var(--primary-soft); padding: 4px 8px; border-radius: 6px; display: inline-block; margin-bottom: 8px; }
        .day-header { font-size: 0.8rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted); border-bottom: 2px solid #f1f5f9; padding-bottom: 12px; margin-bottom: 16px; }
    </style>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    <?php include '../includes/sidebar.php'; ?>

    <div class="content-wrapper">
        <div class="container-fluid px-0">
            <div class="mb-4">
                <h4 class="fw-bold mb-1">Class: <?php echo htmlspecialchars($class_info['name'] . ' ' . $class_info['section']); ?></h4>
                <p class="text-muted small">Daily schedule and room assignments.</p>
            </div>

            <div class="row g-3">
                <?php foreach($days as $day): ?>
                    <div class="col-md-4 col-lg-2">
                        <div class="premium-panel h-100 p-3">
                            <div class="day-header"><?php echo $day; ?></div>
                            <div class="d-flex flex-column gap-3">
                                <?php if(isset($timetable[$day])): ?>
                                    <?php foreach($timetable[$day] as $slot): 
                                        $t_name = $slot['first_name'] ? ($slot['first_name'].' '.$slot['last_name']) : $slot['teacher_name'];
                                    ?>
                                        <div class="timetable-card p-3 shadow-sm border-0 bg-white">
                                            <div class="slot-time">
                                                <?php echo date('H:i', strtotime($slot['start_time'])); ?> - <?php echo date('H:i', strtotime($slot['end_time'])); ?>
                                            </div>
                                            <div class="fw-bold text-main" style="font-size:0.9rem"><?php echo htmlspecialchars($slot['subject_name']); ?></div>
                                            <div class="text-muted extra-small d-flex align-items-center gap-1">
                                                <i class="fas fa-chalkboard-teacher"></i> <?php echo htmlspecialchars($t_name); ?>
                                            </div>
                                            <?php if($slot['room_no']): ?>
                                                <div class="text-primary fw-bold mt-2" style="font-size:0.7rem">
                                                    <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($slot['room_no']); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-center py-4 opacity-25">
                                        <span class="extra-small">No Classes</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <?php include '../includes/scripts.php'; ?>
</body>
</html>
