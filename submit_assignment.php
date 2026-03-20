<?php
session_start();
include './includes/db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['user_id'];
$message = '';

if (isset($_POST['submit_assignment'])) {
    $assignment_id = $_POST['assignment_id'];
    $submission_text = $_POST['submission_text'];
    
    // Handle File Upload
    $file_path = '';
    if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
        $file_path = 'uploads/' . time() . '_' . $_FILES['file']['name'];
        move_uploaded_file($_FILES['file']['tmp_name'], $file_path);
    }

    $stmt = $conn->prepare("INSERT INTO submissions (student_id, assignment_id, submission_text, file_path) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE submission_text = VALUES(submission_text), file_path = VALUES(file_path)");
    $stmt->bind_param("iiss", $student_id, $assignment_id, $submission_text, $file_path);
    $stmt->execute();
    $message = "Assignment submitted successfully!";
}

// Fetch and filter assignments by student's class
$assignments = $conn->query("SELECT a.*, s.name as subject_name 
                            FROM assignments a 
                            JOIN subjects s ON a.subject_id = s.id 
                            JOIN teacher_assignment ta ON s.id = ta.subject_id 
                            JOIN student_enrollment se ON ta.class_id = se.class_id 
                            WHERE se.student_id = $student_id AND a.deadline > NOW()");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Submit Assignment | SMS</title>
    <?php include './includes/links.php'; ?>
    <link rel="stylesheet" href="index.css">
</head>

<body>
    <?php include './includes/navbar.php'; ?>
    <?php include './includes/sidebar.php'; ?>

    <div class="content-wrapper">
        <div class="container py-4">
            <h2 class="mb-4 fw-bold">Assignment Submission</h2>

            <?php if ($message): ?>
                <div class="alert alert-success border-0 shadow-sm mb-4 text-center fw-bold"><?php echo $message; ?></div>
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
                                    <div class="mb-3">
                                        <textarea name="submission_text" class="form-control border-0 bg-light" rows="3" placeholder="Write your comments here..."></textarea>
                                    </div>
                                    <div class="mb-4">
                                        <input type="file" name="file" class="form-control border-0 bg-light">
                                    </div>
                                    <button type="submit" name="submit_assignment" class="btn btn-primary w-100 rounded-pill py-2 fw-bold">Submit Assignment</button>
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
        </div>
    </div>

    <style> .bg-primary-light { background: rgba(99, 102, 241, 0.1); } </style>
    <?php include './includes/scripts.php'; ?>
</body>
</html>
