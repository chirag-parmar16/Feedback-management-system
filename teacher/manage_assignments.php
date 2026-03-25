<?php
session_start();
include '../includes/db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../auth/login.php");
    exit();
}

$teacher_id = $_SESSION['user_id'];
$page_title = 'Coursework Registry';
$page_subtitle = 'Create and distribute assignments to your student modules.';
$message = '';

// Create Assignment Logic
if (isset($_POST['create_assignment'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $subject_id = $_POST['subject_id'];
    $deadline = $_POST['deadline'];

    $stmt = $conn->prepare("INSERT INTO assignments (teacher_id, subject_id, title, description, deadline) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iisss", $teacher_id, $subject_id, $title, $description, $deadline);
    $stmt->execute();
    $message = "Educational assignment successfully posted to students!";
}

// Fixed dropdown queries using JOIN for assigned subjects
$subjects = $conn->query("SELECT s.id, s.name FROM subjects s JOIN teacher_assignment ta ON s.id = ta.subject_id WHERE ta.teacher_id = $teacher_id");
$assignments = $conn->query("SELECT a.*, s.name as subject_name FROM assignments a JOIN subjects s ON a.subject_id = s.id WHERE a.teacher_id = $teacher_id ORDER BY a.created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Assignments | SMS</title>
    <?php include '../includes/links.php'; ?>
    <link rel="stylesheet" href="../assets/index.css">
    <style>
        .assignment-item { border-left: 4px solid var(--primary); padding: 24px; margin-bottom: 24px; border-bottom: 1px solid #f1f5f9; }
        .class-form-panel { padding: 30px 0; border-bottom: 2px solid #f8fafc; }
    </style>
</head>

<body>
    <?php include '../includes/navbar.php'; ?>
    <?php include '../includes/sidebar.php'; ?>

    <div class="content-wrapper">
        <div class="container py-4">

            <?php if ($message): ?>
                <div class="alert bg-success-soft text-success border-0 small py-3 mb-5 fw-bold"><?php echo $message; ?></div>
            <?php endif; ?>

            <div class="assignment-form-panel mb-5">
                <div class="flat-header">
                    <h5>New Assignment Campaign</h5>
                    <p>Enter the scope and deadline for the new educational task.</p>
                </div>
                <form method="POST" class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label text-muted small fw-bold">PROJECT TITLE</label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. Modern History Essay" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-muted small fw-bold">SUBJECT CONTEXT</label>
                        <select name="subject_id" class="form-select" required>
                            <option value="" disabled selected>Select subject...</option>
                            <?php if($subjects->num_rows > 0): ?>
                                <?php while($s = $subjects->fetch_assoc()): ?>
                                    <option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['name']); ?></option>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <option value="" disabled>No assigned subjects</option>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-muted small fw-bold">SUBMISSION DEADLINE</label>
                        <input type="datetime-local" name="deadline" class="form-control" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label text-muted small fw-bold">INSTRUCTIONS & DESCRIPTION</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Provide detailed instructions for the students..."></textarea>
                    </div>
                    <div class="col-12 text-end">
                        <button type="submit" name="create_assignment" class="btn btn-primary px-5 py-3 fw-bold rounded-pill">Deploy Assignment</button>
                    </div>
                </form>
            </div>

            <div class="mt-5 pt-3">
                <h5 class="fw-bold mb-4">Active Assignment Log</h5>
                <?php if($assignments->num_rows > 0): ?>
                    <?php while($a = $assignments->fetch_assoc()): ?>
                        <div class="assignment-item">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <span class="badge bg-primary-soft text-primary px-3 mb-2"><?php echo htmlspecialchars($a['subject_name']); ?></span>
                                    <h5 class="fw-bold mb-0"><?php echo htmlspecialchars($a['title']); ?></h5>
                                </div>
                                <div class="text-end">
                                    <span class="small text-danger fw-bold d-block"><i class="far fa-clock me-1"></i> DEADLINE</span>
                                    <span class="small fw-bold"><?php echo date('M d, Y • H:i', strtotime($a['deadline'])); ?></span>
                                </div>
                            </div>
                            <p class="text-muted small mb-4" style="line-height: 1.6;"><?php echo nl2br(htmlspecialchars($a['description'])); ?></p>
                            <div class="d-flex justify-content-between align-items-center pt-3 border-top border-light">
                                <span class="extra-small text-muted fw-bold">POSTED ON <?php echo date('M d, Y', strtotime($a['created_at'])); ?></span>
                                <a href="#" class="btn btn-light border btn-sm rounded-pill px-4 fw-bold small">View Submissions</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="text-center py-5 text-muted small border rounded-4 bg-light">No educational assignments have been posted yet.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <style> .extra-small { font-size: 0.65rem; letter-spacing: 0.05em; text-transform: uppercase; } </style>
    <?php include '../includes/scripts.php'; ?>
</body>
</html>
