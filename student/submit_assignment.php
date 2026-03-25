<?php
session_start();
include '../includes/db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../auth/login.php");
    exit();
}

$student_id = $_SESSION['user_id'];
$message = '';

if (isset($_POST['submit_assignment_action'])) {
    $assignment_id = $_POST['assignment_id'];
    $submission_text = $_POST['submission_text'];
    
    // Handle File Upload
    $file_path = '';
    if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
        $upload_dir = '../uploads/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        $file_path = 'uploads/' . time() . '_' . $_FILES['file']['name'];
        move_uploaded_file($_FILES['file']['tmp_name'], '../' . $file_path);
    }

    $stmt = $conn->prepare("INSERT INTO assignment_submissions (student_id, assignment_id, submission_text, file_path) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE submission_text = VALUES(submission_text), file_path = VALUES(file_path)");
    $stmt->bind_param("iiss", $student_id, $assignment_id, $submission_text, $file_path);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Assignment submitted successfully!";
    } else {
        $_SESSION['error'] = "Submission failed: " . $stmt->error;
    }
    header("Location: submit_assignment.php");
    exit();
}

$message = $_SESSION['message'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['message'], $_SESSION['error']);

// Fetch pending assignments (not submitted yet and not past deadline)
$assignments = $conn->query("SELECT a.*, s.name as subject_name 
                            FROM assignments a 
                            JOIN subjects s ON a.subject_id = s.id 
                            JOIN teacher_assignment ta ON s.id = ta.subject_id 
                            JOIN student_enrollment se ON ta.class_id = se.class_id 
                            WHERE se.student_id = $student_id 
                            AND a.deadline > NOW()
                            AND a.id NOT IN (SELECT assignment_id FROM assignment_submissions WHERE student_id = $student_id)");

// Fetch submission history
$history = $conn->query("SELECT asub.*, a.title, s.name as subject_name 
                         FROM assignment_submissions asub
                         JOIN assignments a ON asub.assignment_id = a.id
                         JOIN subjects s ON a.subject_id = s.id
                         WHERE asub.student_id = $student_id
                         ORDER BY asub.submitted_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Submit Assignment | SMS</title>
    <?php include '../includes/links.php'; ?>
    <link rel="stylesheet" href="../assets/index.css">
</head>

<body>
    <?php include '../includes/navbar.php'; ?>
    <?php include '../includes/sidebar.php'; ?>

    <div class="content-wrapper">
        <div class="container py-4">
            <h2 class="mb-4 fw-bold">Assignment Submission</h2>

            <?php if ($message): ?>
                <div class="alert alert-success border-0 shadow-sm mb-4 text-center fw-bold"><?php echo $message; ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger border-0 shadow-sm mb-4 text-center fw-bold"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="row g-4">
                <?php if($assignments->num_rows > 0): ?>
                    <?php while($a = $assignments->fetch_assoc()): ?>
                        <div class="col-md-6">
                            <div class="card border-0 p-4 h-100 position-relative">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <span class="badge bg-primary-light text-primary px-3"><?php echo htmlspecialchars($a['subject_name']); ?></span>
                                    <span class="small text-danger fw-bold"><i class="far fa-clock me-1"></i> Due: <?php echo date('M d, H:i', strtotime($a['deadline'])); ?></span>
                                </div>
                                <h5 class="fw-bold mb-2"><?php echo htmlspecialchars($a['title']); ?></h5>
                                <p class="text-muted small mb-4"><?php echo htmlspecialchars($a['description']); ?></p>
                                
                                <form method="POST" enctype="multipart/form-data">
                                    <input type="hidden" name="assignment_id" value="<?php echo $a['id']; ?>">
                                    <input type="hidden" name="submit_assignment_action" value="1">
                                    <div class="mb-3">
                                        <textarea name="submission_text" class="form-control border-0 bg-light" rows="3" placeholder="Write your comments here..."></textarea>
                                    </div>
                                    <div class="mb-4">
                                        <input type="file" name="file" class="form-control border-0 bg-light">
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100 rounded-pill py-2 fw-bold">Submit Assignment</button>
                                </form>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="card border-0 p-5 text-center">
                            <i class="fas fa-check-circle text-success display-4 mb-3"></i>
                            <h4 class="fw-bold">All Caught Up!</h4>
                            <p class="text-muted">You have no pending assignments at the moment.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Submission History -->
            <div class="mt-5 pt-4">
                <h4 class="fw-bold mb-4">Submission History</h4>
                <div class="card border-0 shadow-sm overflow-hidden">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Assignment</th>
                                    <th>Subject</th>
                                    <th>Status</th>
                                    <th>Submitted On</th>
                                    <th class="text-end pe-4">File</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($history && $history->num_rows > 0): ?>
                                    <?php while($h = $history->fetch_assoc()): ?>
                                        <tr>
                                            <td class="ps-4 fw-bold text-dark"><?php echo htmlspecialchars($h['title']); ?></td>
                                            <td><span class="badge bg-primary-soft text-primary px-3"><?php echo htmlspecialchars($h['subject_name']); ?></span></td>
                                            <td>
                                                <?php if($h['marks'] !== null): ?>
                                                    <span class="badge bg-success-soft text-success">Graded: <?php echo $h['marks']; ?></span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning-soft text-warning">Pending Review</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-muted small"><?php echo date('M d, Y H:i', strtotime($h['submitted_at'])); ?></td>
                                            <td class="text-end pe-4">
                                                <?php if($h['file_path']): ?>
                                                    <a href="../<?php echo $h['file_path']; ?>" target="_blank" class="btn btn-sm btn-light border small px-3">View File</a>
                                                <?php else: ?>
                                                    <span class="text-muted small">No File</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted small">No previous submissions found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style> .bg-primary-light { background: rgba(99, 102, 241, 0.1); } </style>
    <?php include '../includes/scripts.php'; ?>
</body>
</html>
