<?php
session_start();
include './includes/db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: login.php");
    exit();
}

$teacher_id = $_SESSION['user_id'];
$page_title = 'Attendance Terminal';
$page_subtitle = 'Record student presence for the current academic session.';
$message = '';
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

// Step 1: Fetch assigned classes and subjects
$assignments = $conn->query("SELECT ta.*, s.name as subject_name, c.name as class_name, c.section 
                             FROM teacher_assignment ta 
                             JOIN subjects s ON ta.subject_id = s.id 
                             JOIN classes c ON ta.class_id = c.id 
                             WHERE ta.teacher_id = $teacher_id");

$selected_assignment = null;
$students = null;

if (isset($_GET['assignment_id'])) {
    $parts = explode('-', $_GET['assignment_id']);
    if (count($parts) == 2) {
        $subject_id = $parts[0];
        $class_id = $parts[1];
        
        // Fetch students using LEFT JOIN for safety
        $students = $conn->query("SELECT u.id, u.username, p.first_name, p.last_name, p.Enroll_No 
                                  FROM users u 
                                  LEFT JOIN profile_info p ON u.id = p.user_id 
                                  JOIN student_enrollment se ON u.id = se.student_id 
                                  WHERE se.class_id = $class_id AND u.role = 'student'
                                  ORDER BY p.first_name ASC");
        
        $selected_assignment = ['subject_id' => $subject_id, 'class_id' => $class_id];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Take Attendance | SMS</title>
    <?php include './includes/links.php'; ?>
    <link rel="stylesheet" href="index.css">
    <style>
        .form-select, .form-control {
            background: #ffffff !important;
            border: 1px solid #e2e8f0 !important;
        }
    </style>
</head>

<body>
    <?php include './includes/navbar.php'; ?>
    <?php include './includes/sidebar.php'; ?>

    <div class="content-wrapper">
        <div class="container py-4">

            <!-- Session Selection Context -->
            <div class="premium-panel">
                <div class="flat-header">
                    <h5>Institutional Session Selection</h5>
                    <p>Select the academic class and corresponding subject for the current assessment period.</p>
                </div>
                <form method="GET" class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label text-muted small fw-bold">ACTIVE ASSIGNMENT STREAM</label>
                        <select name="assignment_id" class="form-select py-3" onchange="this.form.submit()">
                            <option value="">Choose Class & Subject Path...</option>
                            <?php while($a = $assignments->fetch_assoc()): ?>
                                <?php $val = $a['subject_id'] . '-' . $a['class_id']; ?>
                                <option value="<?php echo $val; ?>" <?php echo (isset($_GET['assignment_id']) && $_GET['assignment_id'] == $val) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($a['subject_name'] . ' — ' . $a['class_name'] . ' (' . $a['section'] . ')'); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </form>
            </div>

            <?php if ($students): ?>
                <div class="premium-panel">
                    <form action="backend/attendance_logic.php" method="POST">
                        <input type="hidden" name="subject_id" value="<?php echo $selected_assignment['subject_id']; ?>">
                        <input type="hidden" name="class_id" value="<?php echo $selected_assignment['class_id']; ?>">

                        <div class="d-flex justify-content-between align-items-end mb-5">
                            <div class="flat-header border-0 mb-0 pb-0">
                                <h5>Student Roll Call Registry</h5>
                                <p>Authenticate student presence for the selected academic stream.</p>
                            </div>
                            <div class="text-end">
                                <label class="form-label small fw-bold text-muted d-block">SESSION DATE</label>
                                <input type="date" name="date" class="form-control border-0 bg-light fw-bold" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4 py-3">OFFICIAL ID</th>
                                        <th class="py-3">STUDENT IDENTITY</th>
                                        <th class="text-center py-3">ATTENDANCE STATUS</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($s = $students->fetch_assoc()): ?>
                                        <tr>
                                            <td class="ps-4"><code class="text-primary fw-bold small"><?php echo htmlspecialchars($s['Enroll_No'] ?: 'PENDING'); ?></code></td>
                                            <td class="fw-bold text-dark"><?php echo htmlspecialchars(($s['first_name'] ? ($s['first_name'] . ' ' . $s['last_name']) : 'Incomplete Profile (' . $s['username'] . ')')); ?></td>
                                            <td class="text-center">
                                                <div class="d-flex justify-content-center gap-2">
                                                    <input type="radio" class="btn-check" name="attendance[<?php echo $s['id']; ?>]" value="Present" id="p<?php echo $s['id']; ?>" checked autocomplete="off">
                                                    <label class="btn btn-outline-success btn-sm px-4 rounded-pill" for="p<?php echo $s['id']; ?>">Present</label>

                                                    <input type="radio" class="btn-check" name="attendance[<?php echo $s['id']; ?>]" value="Absent" id="a<?php echo $s['id']; ?>" autocomplete="off">
                                                    <label class="btn btn-outline-danger btn-sm px-4 rounded-pill" for="a<?php echo $s['id']; ?>">Absent</label>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-5 pt-4 border-top">
                            <button type="submit" class="btn btn-primary px-5 py-3 fw-bold rounded-pill shadow-sm">Submit Attendance Protocol</button>
                        </div>
                    </form>
                </div>
                <?php if ($message): ?>
                    <div class="alert bg-success-soft text-success border-0 small py-3 mb-4 fw-bold"><?php echo $message; ?></div>
                <?php endif; ?>
<?php elseif (isset($_GET['assignment_id'])): ?>
                <div class="premium-panel text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-user-slash text-muted display-4 opacity-25"></i>
                    </div>
                    <h5 class="fw-bold">No Active Enrollments Discovered</h5>
                    <p class="text-muted mb-4">It appears no students are currently assigned to this class configuration. <br> Please coordinate with administration to facilitate enrollment.</p>
                    <a href="dashboard.php" class="btn btn-light border px-4 small fw-bold">Return to Overview</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include './includes/scripts.php'; ?>
</body>
</html>
