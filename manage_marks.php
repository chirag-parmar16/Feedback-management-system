<?php
session_start();
include './includes/db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: login.php");
    exit();
}

$teacher_id = $_SESSION['user_id'];
$page_title = 'Assessment Matrix';
$page_subtitle = 'Enter and manage academic marks for subject evaluations.';
$message = '';
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

// Fetch assigned classes and subjects
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
        
        $students = $conn->query("SELECT u.id, p.first_name, p.last_name, p.Enroll_No 
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
    <title>Academic Marks | SMS</title>
    <?php include './includes/links.php'; ?>
    <link rel="stylesheet" href="index.css">
</head>

<body>
    <?php include './includes/navbar.php'; ?>
    <?php include './includes/sidebar.php'; ?>

    <div class="content-wrapper">
        <div class="container py-4">
            
            <!-- Contextual Selection -->
            <div class="premium-panel">
                <div class="flat-header border-0 mb-0 pb-0">
                    <h5>Academic Assessment Context</h5>
                    <p>Define the specific class and subject stream for the current marking period.</p>
                </div>
                <form method="GET" class="row g-3">
                    <div class="col-md-12 mt-4">
                        <label class="form-label text-muted small fw-bold">ACTIVE CLASS & SUBJECT STREAM</label>
                        <select name="assignment_id" class="form-select py-3" onchange="this.form.submit()">
                            <option value="">Choose academic context...</option>
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
                    <form action="backend/marks_logic.php" method="POST">
                        <input type="hidden" name="subject_id" value="<?php echo $selected_assignment['subject_id']; ?>">
                        <input type="hidden" name="class_id" value="<?php echo $selected_assignment['class_id']; ?>">

                        <div class="flat-header">
                            <h5>Examination Parameters</h5>
                            <p>Provision the core testing parameters for this assessment.</p>
                        </div>
                        <div class="row g-4 mb-5">
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-bold">ASSESSMENT DATE</label>
                                <input type="date" name="exam_date" class="form-control py-3" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-bold">MAXIMUM ATTAINABLE SCORE</label>
                                <input type="number" name="total_marks" class="form-control py-3 font-monospace" value="100" required>
                            </div>
                        </div>
                        
                        <div class="flat-header">
                            <h5>Academic Result Matrix</h5>
                            <p>Capture individual student performance data for permanent record.</p>
                        </div>
                        <div class="table-responsive">
                            <table class="table align-middle table-hover">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4 py-3">OFFICIAL ID</th>
                                        <th class="py-3">STUDENT IDENTITY</th>
                                        <th width="220" class="text-center py-3">MARKS ACHIEVED</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($s = $students->fetch_assoc()): ?>
                                        <tr>
                                            <td class="ps-4"><code class="text-primary small fw-bold"><?php echo htmlspecialchars($s['Enroll_No'] ?: 'PENDING'); ?></code></td>
                                            <td class="fw-bold text-dark"><?php echo htmlspecialchars(($s['first_name'] ? ($s['first_name'] . ' ' . $s['last_name']) : 'Incomplete Profile')); ?></td>
                                            <td>
                                                <input type="number" name="marks[<?php echo $s['id']; ?>]" class="form-control text-center py-2 fw-bold text-primary border-primary-soft bg-light shadow-none" placeholder="0.00" min="0">
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-5 pt-4 border-top">
                            <button type="submit" name="save_marks" class="btn btn-primary px-5 py-3 fw-bold rounded-pill shadow-sm">Finalize & Log Assessment Data</button>
                        </div>
                    </form>
                </div>
            <?php if ($message): ?>
                <div class="alert bg-success-soft text-success border-0 small py-3 mb-4 fw-bold"><?php echo $message; ?></div>
            <?php endif; ?>
            <?php else: ?>
                <div class="premium-panel text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-layer-group text-muted display-4 opacity-25"></i>
                    </div>
                    <h5 class="fw-bold">No Student Context Available</h5>
                    <p class="text-muted">Direct enrollment is required before marks can be provisioned for this class.</p>
                    <a href="dashboard.php" class="btn btn-light border px-4 small fw-bold">Return to Dashboard</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php include './includes/scripts.php'; ?>
</body>
</html>
