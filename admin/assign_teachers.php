<?php
session_start();
include '../includes/db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

$page_title = 'Faculty Allocation';
$page_subtitle = 'Assign teaching staff to specific subjects and classrooms.';
$message = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['assign_teacher'])) {
    $teacher_id = (int) $_POST['teacher_id'];
    $subject_id = (int) $_POST['subject_id'];
    $class_id   = (int) $_POST['class_id'];
    
    // Check for duplicate assignment
    $chk = $conn->prepare("SELECT COUNT(*) as cnt FROM teacher_assignment WHERE teacher_id = ? AND subject_id = ? AND class_id = ?");
    $chk->bind_param("iii", $teacher_id, $subject_id, $class_id);
    $chk->execute();
    if ($chk->get_result()->fetch_assoc()['cnt'] > 0) {
        $error = "This teacher is already assigned to this subject and class combination.";
    } else {
        $stmt = $conn->prepare("INSERT INTO teacher_assignment (teacher_id, subject_id, class_id) VALUES (?, ?, ?)");
        $stmt->bind_param("iii", $teacher_id, $subject_id, $class_id);
        $stmt->execute();
        $message = "Academic mapping successfully established!";
    }
}

// Fixed dropdown queries using LEFT JOIN on profile_info
$teachers = $conn->query("SELECT u.id, u.username, p.first_name, p.last_name 
                          FROM users u 
                          LEFT JOIN profile_info p ON u.id = p.user_id 
                          WHERE u.role = 'teacher' 
                          ORDER BY u.username ASC");

$subjects = $conn->query("SELECT * FROM subjects ORDER BY name ASC");
$classes = $conn->query("SELECT * FROM classes ORDER BY name ASC");

$assignments = $conn->query("SELECT ta.*, u.username as teacher_user, p.first_name, p.last_name, s.name as subject_name, c.name as class_name, c.section 
                             FROM teacher_assignment ta 
                             JOIN users u ON ta.teacher_id = u.id 
                             LEFT JOIN profile_info p ON u.id = p.user_id
                             JOIN subjects s ON ta.subject_id = s.id 
                             JOIN classes c ON ta.class_id = c.id
                             ORDER BY ta.assigned_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Assign Teachers | Admin</title>
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
                <div class="flat-header">
                    <h5>New Educational Mapping</h5>
                    <p>Select the faculty member, subject, and target class.</p>
                </div>
                <form method="POST" class="row g-4 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label text-muted small fw-bold">FACULTY MEMBER</label>
                        <select name="teacher_id" class="form-select" required>
                            <option value="" disabled selected>Choose teacher...</option>
                            <?php while($t = $teachers->fetch_assoc()): ?>
                                <option value="<?php echo $t['id']; ?>">
                                    <?php echo $t['first_name'] ? htmlspecialchars($t['first_name'] . ' ' . $t['last_name']) : htmlspecialchars($t['username']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-muted small fw-bold">ACADEMIC SUBJECT</label>
                        <select name="subject_id" class="form-select" required>
                            <option value="" disabled selected>Choose subject...</option>
                            <?php if($subjects->num_rows > 0): ?>
                                <?php while($s = $subjects->fetch_assoc()): ?>
                                    <option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['name']); ?></option>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <option value="" disabled>No subjects available</option>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-muted small fw-bold">TARGET CLASS</label>
                        <select name="class_id" class="form-select" required>
                            <option value="" disabled selected>Choose class...</option>
                            <?php if($classes->num_rows > 0): ?>
                                <?php while($c = $classes->fetch_assoc()): ?>
                                    <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['name'] . ' — ' . $c['section']); ?></option>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <option value="" disabled>No classes defined</option>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" name="assign_teacher" class="btn btn-primary w-100 py-3 fw-bold rounded-pill">Assign Faculty</button>
                    </div>
                </form>
            </div>

            <div class="premium-panel mt-4">
                <h5 class="fw-bold mb-4">Active Academic Mappings</h5>
                <div class="table-responsive">
                    <table class="table">
                        <thead class="text-muted small">
                            <tr>
                                <th>FACULTY</th>
                                <th>SUBJECT</th>
                                <th>CLASS CONTEXT</th>
                                <th>DATE MAPPED</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($assignments->num_rows > 0): ?>
                                <?php while($row = $assignments->fetch_assoc()): ?>
                                    <tr>
                                        <td class="fw-bold"><?php echo $row['first_name'] ? htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) : htmlspecialchars($row['teacher_user']); ?></td>
                                        <td class="small"><?php echo htmlspecialchars($row['subject_name']); ?></td>
                                        <td><span class="badge bg-light text-dark px-3 py-1"><?php echo htmlspecialchars($row['class_name'] . ' — ' . $row['section']); ?></span></td>
                                        <td class="small text-muted"><?php echo date('M d, Y', strtotime($row['assigned_at'])); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-muted small">No faculty mappings have been established yet.</td>
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
