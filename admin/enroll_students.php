<?php
session_start();
include '../includes/db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

$page_title = 'Student Enrollment';
$page_subtitle = 'Assign students to their respective academic classes.';
$message = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['enroll_student'])) {
    $student_id = (int) $_POST['student_id'];
    $class_id   = (int) $_POST['class_id'];
    
    // Verify target user is actually a student
    $role_chk = $conn->prepare("SELECT role FROM users WHERE id = ?");
    $role_chk->bind_param("i", $student_id);
    $role_chk->execute();
    $user_role = $role_chk->get_result()->fetch_assoc()['role'] ?? '';

    if ($user_role !== 'student') {
        $error = "Critical Error: Only accounts with 'student' role can be enrolled in classes.";
    } else {
        // Check for duplicate enrollment
        $chk = $conn->prepare("SELECT COUNT(*) as cnt FROM student_enrollment WHERE student_id = ? AND class_id = ?");
        $chk->bind_param("ii", $student_id, $class_id);
        $chk->execute();
        if ($chk->get_result()->fetch_assoc()['cnt'] > 0) {
            $error = "This student is already enrolled in the selected class.";
        } else {
            $stmt = $conn->prepare("INSERT INTO student_enrollment (student_id, class_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $student_id, $class_id);
            $stmt->execute();
            $message = "Student successfully enrolled in the selected class!";
        }
    }
}

// Fixed dropdown queries using LEFT JOIN to ensure all students appear
$students = $conn->query("SELECT u.id, u.username, p.Enroll_No 
                          FROM users u 
                          LEFT JOIN profile_info p ON u.id = p.user_id 
                          WHERE u.role = 'student' 
                          ORDER BY u.username ASC");

$classes = $conn->query("SELECT * FROM classes ORDER BY name ASC");

$enrollments = $conn->query("SELECT se.*, u.username, p.Enroll_No, c.name as class_name, c.section 
                              FROM student_enrollment se 
                              JOIN users u ON se.student_id = u.id 
                              LEFT JOIN profile_info p ON u.id = p.user_id
                              JOIN classes c ON se.class_id = c.id
                              ORDER BY se.enrolled_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Enroll Students | SMS</title>
    <?php include '../includes/links.php'; ?>
    <link rel="stylesheet" href="../assets/index.css">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    <?php include '../includes/sidebar.php'; ?>

    <div class="content-wrapper">
        <div class="container py-4">
            <?php if ($message): ?>
                <div class="alert bg-success-soft text-success border-0 small py-3 mb-4 fw-bold"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert bg-danger-soft text-danger border-0 small py-3 mb-4 fw-bold"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <div class="premium-panel">
                <div class="flat-header">
                    <h5>Academic Placement Protocol</h5>
                    <p>Initialize student enrollment by mapping system identifiers to institutional class streams.</p>
                </div>
                <form method="POST" class="row g-4 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label text-muted small fw-bold">PROSPECTIVE STUDENT</label>
                        <select name="student_id" class="form-select py-3" required>
                            <option value="" disabled selected>Select from student registry...</option>
                            <?php while($s = $students->fetch_assoc()): ?>
                                <option value="<?php echo $s['id']; ?>">
                                    <?php echo htmlspecialchars($s['username']); ?> 
                                    <?php echo $s['Enroll_No'] ? ' — ID: ' . htmlspecialchars($s['Enroll_No']) : '(Profile Incomplete)'; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label text-muted small fw-bold">TARGET CLASS CONFIGURATION</label>
                        <select name="class_id" class="form-select py-3" required>
                            <option value="" disabled selected>Assign to class stream...</option>
                            <?php if($classes->num_rows > 0): ?>
                                <?php while($c = $classes->fetch_assoc()): ?>
                                    <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['name'] . ' (Section ' . $c['section'] . ')'); ?></option>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <option value="" disabled>No class streams available</option>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" name="enroll_student" class="btn btn-primary w-100 py-3 fw-bold rounded-pill shadow-sm">Enroll Student</button>
                    </div>
                </form>
            </div>

            <div class="premium-panel mt-4">
                <div class="flat-header d-flex justify-content-between align-items-center mb-4">
                    <div class="mb-0 pb-0 border-0">
                        <h5>Institutional Enrollment Registry</h5>
                        <p>Live monitoring of active student-class mappings.</p>
                    </div>
                    <span class="badge bg-primary px-3 py-2 rounded-pill"><?php echo $enrollments->num_rows; ?> Total Active</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">OFFICIAL ID</th>
                                <th>STUDENT PORTFOLIO</th>
                                <th>CLASS STREAM</th>
                                <th class="text-end pe-4">ENROLLMENT STATUS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($enrollments->num_rows > 0): ?>
                                <?php while($row = $enrollments->fetch_assoc()): ?>
                                    <tr>
                                        <td class="ps-4"><code class="text-primary fw-bold small"><?php echo htmlspecialchars($row['Enroll_No'] ?: 'PENDING'); ?></code></td>
                                        <td class="fw-bold"><?php echo htmlspecialchars($row['username']); ?></td>
                                        <td><span class="badge bg-primary-soft text-primary px-3 py-2"><?php echo htmlspecialchars($row['class_name'] . ' — ' . $row['section']); ?></span></td>
                                        <td class="text-end pe-4 small text-muted"><?php echo date('M d, Y', strtotime($row['enrolled_at'])); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center py-5">
                                        <div class="mb-3"><i class="fas fa-inbox display-4 opacity-25"></i></div>
                                        <p class="text-muted small fw-bold">No active enrollments located in the registry.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php include '../includes/scripts.php'; ?>
</body>
</html>
