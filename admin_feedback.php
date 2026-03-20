<?php
session_start();
include './includes/db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$page_title = 'Feedback Campaigns';
$page_subtitle = 'Generate and monitor departmental feedback forms for students.';
$message = '';
if (isset($_POST['generate_feedback'])) {
    $title = $_POST['title'];
    $teacher_id = $_POST['teacher_id'];
    $subject_id = $_POST['subject_id'];
    $status = 'active';

    $stmt = $conn->prepare("INSERT INTO feedback_forms (title, teacher_id, subject_id, status) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("siis", $title, $teacher_id, $subject_id, $status);
    $stmt->execute();
    $message = "Feedback campaign successfully initialized and broadcasted!";
}

// Fixed dropdown queries using LEFT JOIN on profile_info
$teachers = $conn->query("SELECT u.id, u.username, p.first_name, p.last_name 
                          FROM users u 
                          LEFT JOIN profile_info p ON u.id = p.user_id 
                          WHERE u.role = 'teacher' 
                          ORDER BY u.username ASC");

$subjects = $conn->query("SELECT * FROM subjects ORDER BY name ASC");

$active_forms = $conn->query("SELECT f.*, u.username as teacher_user, p.first_name, p.last_name, s.name as subject_name 
                               FROM feedback_forms f 
                               JOIN users u ON f.teacher_id = u.id
                               LEFT JOIN profile_info p ON u.id = p.user_id 
                               JOIN subjects s ON f.subject_id = s.id 
                               ORDER BY f.created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Feedback Hub | Admin</title>
    <?php include './includes/links.php'; ?>
    <link rel="stylesheet" href="index.css">
    <style>
        .flat-config-panel {
            background: #ffffff;
            padding: 30px 0;
            border-bottom: 2px solid #f8fafc;
        }
        .form-control, .form-select {
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

            <?php if ($message): ?>
                <div class="alert bg-primary-soft text-primary border-0 small py-3 mb-5 fw-bold"><?php echo $message; ?></div>
            <?php endif; ?>

            <div class="flat-config-panel">
                <div class="mb-4">
                    <h5 class="fw-bold text-primary small text-uppercase tracking-wider">Campaign Configuration</h5>
                </div>
                <form method="POST" class="row g-4 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label text-muted small fw-bold">CAMPAIGN TITLE</label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. Q4 Teacher Performance" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-muted small fw-bold">FACULTY MEMBER</label>
                        <select name="teacher_id" class="form-select" required>
                            <option value="" disabled selected>Choose teacher...</option>
                            <?php while($t = $teachers->fetch_assoc()): ?>
                                <option value="<?php echo $t['id']; ?>">
                                    <?php echo $t['first_name'] ? htmlspecialchars($t['first_name'] . ' ' . $t['last_name']) : htmlspecialchars($t['username'] . ' (Profile Pending)'); ?>
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
                                <option value="" disabled>No subjects defined yet</option>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" name="generate_feedback" class="btn btn-primary w-100 py-3 fw-bold rounded-pill">Deploy Form</button>
                    </div>
                </form>
            </div>

            <div class="mt-5 pt-4">
                <h5 class="fw-bold mb-4">Historical Campaigns</h5>
                <div class="table-responsive">
                    <table class="table">
                        <thead class="text-muted small">
                            <tr>
                                <th>TITLE</th>
                                <th>TEACHER</th>
                                <th>SUBJECT</th>
                                <th>DATE DEPLOYED</th>
                                <th>STATUS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($active_forms->num_rows > 0): ?>
                                <?php while($f = $active_forms->fetch_assoc()): ?>
                                    <tr>
                                        <td class="fw-bold"><?php echo htmlspecialchars($f['title']); ?></td>
                                        <td class="small"><?php echo $f['first_name'] ? ($f['first_name'] . ' ' . $f['last_name']) : $f['teacher_user']; ?></td>
                                        <td class="small opacity-75"><?php echo htmlspecialchars($f['subject_name']); ?></td>
                                        <td class="small text-muted"><?php echo date('M d, Y', strtotime($f['created_at'])); ?></td>
                                        <td><span class="badge bg-success-soft text-success px-3 py-1">Active</span></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted small">No feedback campaigns found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php include './includes/scripts.php'; ?>
</body>
</html>
