<?php
session_start();
include '../includes/db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php"); exit();
}

$page_title    = 'Feedback Forms';
$page_subtitle = 'Create and monitor teacher feedback campaigns';
$message = ''; $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_feedback'])) {
    $title      = htmlspecialchars(trim($_POST['title'] ?? ''));
    $teacher_id = (int) ($_POST['teacher_id'] ?? 0);
    $subject_id = (int) ($_POST['subject_id'] ?? 0);

    if (empty($title) || $teacher_id <= 0 || $subject_id <= 0) {
        $error = "All fields are required.";
    } else {
        $status = 'active';
        $stmt = $conn->prepare("INSERT INTO feedback_forms (title, teacher_id, subject_id, status) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("siis", $title, $teacher_id, $subject_id, $status);
        $stmt->execute();
        $_SESSION['toast'] = ['message' => 'Feedback form "' . $title . '" created and activated.', 'type' => 'success'];
        header("Location: admin_feedback.php"); exit();
    }
}

// Toggle status (activate/close)
if (isset($_GET['toggle_id']) && is_numeric($_GET['toggle_id'])) {
    $tid = (int) $_GET['toggle_id'];
    $cur_status = $conn->prepare("SELECT status FROM feedback_forms WHERE id = ?");
    $cur_status->bind_param("i", $tid); $cur_status->execute();
    $row = $cur_status->get_result()->fetch_assoc();
    if ($row) {
        $new_status = ($row['status'] === 'active') ? 'inactive' : 'active';
        $upd = $conn->prepare("UPDATE feedback_forms SET status = ? WHERE id = ?");
        $upd->bind_param("si", $new_status, $tid); $upd->execute();
        $_SESSION['toast'] = ['message' => 'Form status updated to ' . $new_status . '.', 'type' => 'success'];
    }
    header("Location: admin_feedback.php"); exit();
}

// Toggle teacher visibility
if (isset($_GET['toggle_visibility']) && is_numeric($_GET['toggle_visibility'])) {
    $tid = (int) $_GET['toggle_visibility'];
    $cur_vis = $conn->prepare("SELECT visibility_to_teacher FROM feedback_forms WHERE id = ?");
    $cur_vis->bind_param("i", $tid); $cur_vis->execute();
    $row = $cur_vis->get_result()->fetch_assoc();
    if ($row) {
        $new_vis = $row['visibility_to_teacher'] ? 0 : 1;
        $upd = $conn->prepare("UPDATE feedback_forms SET visibility_to_teacher = ? WHERE id = ?");
        $upd->bind_param("ii", $new_vis, $tid); $upd->execute();
        $_SESSION['toast'] = ['message' => 'Teacher visibility updated.', 'type' => 'success'];
    }
    header("Location: admin_feedback.php"); exit();
}

$teachers = $conn->query(
    "SELECT u.id, u.username, p.first_name, p.last_name
     FROM users u LEFT JOIN profile_info p ON u.id = p.user_id
     WHERE u.role = 'teacher' ORDER BY u.username ASC"
);
$subjects = $conn->query("SELECT * FROM subjects ORDER BY name ASC");

$forms = $conn->query(
    "SELECT f.*, u.username as teacher_user, p.first_name, p.last_name, s.name as subject_name,
            (SELECT COUNT(*) FROM feedback_responses fr WHERE fr.form_id = f.id) as response_count
     FROM feedback_forms f
     JOIN users u ON f.teacher_id = u.id
     LEFT JOIN profile_info p ON u.id = p.user_id
     JOIN subjects s ON f.subject_id = s.id
     ORDER BY f.created_at DESC"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Feedback Forms | Admin</title>
    <?php include '../includes/links.php'; ?>
    <link rel="stylesheet" href="../assets/index.css">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    <?php include '../includes/sidebar.php'; ?>

    <div class="content-wrapper">
        <div class="container-fluid px-0">

            <?php if ($error): ?>
                <div class="alert bg-danger-soft text-danger fw-semibold"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <!-- Create Form Panel -->
            <div class="premium-panel mb-4">
                <div class="flat-header">
                    <h5>Create Feedback Form</h5>
                    <p>A new form will be immediately visible to enrolled students</p>
                </div>

                <form method="POST" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Form Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control"
                               placeholder="e.g. Mid-Term Teacher Evaluation" required
                               value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Teacher <span class="text-danger">*</span></label>
                        <select name="teacher_id" class="form-select" required>
                            <option value="">Select teacher...</option>
                            <?php while($t = $teachers->fetch_assoc()): ?>
                                <option value="<?php echo $t['id']; ?>">
                                    <?php echo htmlspecialchars($t['first_name']
                                        ? $t['first_name'] . ' ' . $t['last_name']
                                        : $t['username']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Subject <span class="text-danger">*</span></label>
                        <select name="subject_id" class="form-select" required>
                            <option value="">Select subject...</option>
                            <?php if($subjects && $subjects->num_rows > 0): ?>
                                <?php while($s = $subjects->fetch_assoc()): ?>
                                    <option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['name']); ?></option>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <option value="" disabled>No subjects found</option>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" name="generate_feedback" class="btn btn-primary w-100 fw-bold">
                            <i class="fas fa-plus me-1"></i> Create Form
                        </button>
                    </div>
                </form>
            </div>

            <!-- All Forms -->
            <div class="premium-panel">
                <div class="flat-header">
                    <h5>All Feedback Forms</h5>
                    <p>Click the status badge to toggle between active and inactive</p>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Form Title</th>
                                <th>Teacher</th>
                                <th>Subject</th>
                                <th class="text-center">Responses</th>
                                <th>Created</th>
                                <th class="text-center">Teacher View</th>
                                <th class="text-center">Form Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($forms && $forms->num_rows > 0): ?>
                                <?php while($f = $forms->fetch_assoc()): ?>
                                    <tr>
                                        <td class="fw-semibold"><?php echo htmlspecialchars($f['title']); ?></td>
                                        <td class="small">
                                            <?php echo htmlspecialchars($f['first_name']
                                                ? $f['first_name'] . ' ' . $f['last_name']
                                                : $f['teacher_user']); ?>
                                        </td>
                                        <td class="small text-muted"><?php echo htmlspecialchars($f['subject_name']); ?></td>
                                        <td class="text-center">
                                            <span class="fw-bold <?php echo $f['response_count'] > 0 ? 'text-success' : 'text-muted'; ?>">
                                                <?php echo $f['response_count']; ?>
                                            </span>
                                        </td>
                                        <td class="small text-muted"><?php echo date('d M Y', strtotime($f['created_at'])); ?></td>
                                        <td class="text-center">
                                            <a href="admin_feedback.php?toggle_visibility=<?php echo $f['id']; ?>" 
                                               class="badge text-decoration-none <?php echo $f['visibility_to_teacher'] ? 'bg-primary-soft text-primary' : 'bg-secondary text-white'; ?> px-3 py-2">
                                                <i class="fas <?php echo $f['visibility_to_teacher'] ? 'fa-eye' : 'fa-eye-slash'; ?> me-1"></i>
                                                <?php echo $f['visibility_to_teacher'] ? 'Visible' : 'Hidden'; ?>
                                            </a>
                                        </td>
                                        <td class="text-center">
                                            <a href="admin_feedback.php?toggle_id=<?php echo $f['id']; ?>"
                                               class="badge text-decoration-none <?php echo $f['status'] === 'active' ? 'bg-success-soft' : 'bg-danger-soft'; ?> px-3 py-2"
                                               title="Click to toggle status"
                                               onclick="return confirm('Toggle this form\'s status?')">
                                                <?php echo ucfirst($f['status']); ?>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <i class="fas fa-comment-dots d-block mb-3 text-muted" style="font-size:2rem;opacity:.3"></i>
                                        <span class="text-muted small">No feedback forms yet. Create one above.</span>
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
