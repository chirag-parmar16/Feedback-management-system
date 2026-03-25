<?php
session_start();
include '../includes/db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php"); exit();
}

$page_title = 'Timetable Management';
$page_subtitle = 'Organize and view weekly academic schedules per class';

$filter_class = isset($_GET['class_id']) ? (int)$_GET['class_id'] : 0;

// Fetch all classes for dropdown
$classes = $conn->query("SELECT * FROM classes ORDER BY name, section");

// Fetch timetable for selected class
$timetable = [];
if ($filter_class > 0) {
    $res = $conn->query("SELECT t.*, s.name as subject_name, u.username as teacher_name, p.first_name, p.last_name 
                         FROM timetables t
                         JOIN subjects s ON t.subject_id = s.id
                         JOIN users u ON t.teacher_id = u.id
                         LEFT JOIN profile_info p ON u.id = p.user_id
                         WHERE t.class_id = $filter_class
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
    <title>Timetable Management | Admin</title>
    <?php include '../includes/links.php'; ?>
    <link rel="stylesheet" href="../assets/index.css">
    <style>
        .timetable-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; height: 100%; transition: all 0.2s; }
        .timetable-card:hover { border-color: var(--primary); box-shadow: 0 4px 12px rgba(67, 56, 202, 0.08); }
        .slot-time { font-size: 0.75rem; font-weight: 700; color: var(--primary); background: var(--primary-soft); padding: 4px 8px; border-radius: 6px; display: inline-block; margin-bottom: 8px; }
        .slot-subject { font-size: 0.9rem; font-weight: 700; color: var(--text-main); margin-bottom: 2px; }
        .slot-teacher { font-size: 0.75rem; color: var(--text-muted); display: flex; align-items: center; gap: 4px; }
        .day-header { font-size: 0.8rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted); border-bottom: 2px solid #f1f5f9; padding-bottom: 12px; margin-bottom: 16px; }
    </style>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    <?php include '../includes/sidebar.php'; ?>

    <div class="content-wrapper">
        <div class="container-fluid px-0">
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <form method="GET" class="d-flex gap-3 align-items-center">
                    <select name="class_id" class="form-select py-2 px-4 rounded-pill border shadow-sm" style="min-width: 250px;" onchange="this.form.submit()">
                        <option value="0">Select Class to View...</option>
                        <?php while($c = $classes->fetch_assoc()): ?>
                            <option value="<?php echo $c['id']; ?>" <?php echo $filter_class == $c['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($c['name'] . ' ' . $c['section']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </form>
                <a href="add_timetable_slot.php<?php echo $filter_class ? '?class_id='.$filter_class : ''; ?>" class="btn btn-primary rounded-pill px-4 fw-bold">
                    <i class="fas fa-plus me-2"></i> Add Slot
                </a>
            </div>

            <?php if ($filter_class > 0): ?>
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
                                            <div class="timetable-card p-3 position-relative">
                                                <div class="slot-time">
                                                    <?php echo date('H:i', strtotime($slot['start_time'])); ?> - <?php echo date('H:i', strtotime($slot['end_time'])); ?>
                                                </div>
                                                <div class="slot-subject"><?php echo htmlspecialchars($slot['subject_name']); ?></div>
                                                <div class="slot-teacher">
                                                    <i class="fas fa-chalkboard-teacher small"></i> <?php echo htmlspecialchars($t_name); ?>
                                                </div>
                                                <div class="mt-2 pt-2 border-top d-flex gap-2">
                                                    <a href="edit_timetable_slot.php?id=<?php echo $slot['id']; ?>" class="extra-small text-primary text-decoration-none">Edit</a>
                                                    <a href="backend/delete_timetable_slot.php?id=<?php echo $slot['id']; ?>" class="extra-small text-danger text-decoration-none" onclick="return confirm('Delete this slot?')">Delete</a>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="text-center py-4 opacity-25">
                                            <i class="fas fa-calendar-alt d-block mb-1"></i>
                                            <span class="extra-small">No Classes</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="premium-panel text-center py-5">
                    <i class="fas fa-calendar-week display-1 text-muted opacity-25 mb-4"></i>
                    <h5 class="fw-bold">No Class Selected</h5>
                    <p class="text-muted">Please select a class from the dropdown above to view or manage its weekly timetable.</p>
                </div>
            <?php endif; ?>

        </div>
    </div>

    <?php include '../includes/scripts.php'; ?>
</body>
</html>
