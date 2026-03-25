<?php
session_start();
include '../includes/db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    die("Unauthorized access.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['grade_submission_action'])) {
    $submission_id = $_POST['submission_id'];
    $marks = $_POST['marks'];
    $remarks = $_POST['remarks'];
    $teacher_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("UPDATE assignment_submissions SET marks = ?, remarks = ?, graded_at = NOW(), graded_by = ? WHERE id = ?");
    $stmt->bind_param("isii", $marks, $remarks, $teacher_id, $submission_id);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Submission graded successfully!";
    } else {
        $_SESSION['error'] = "Failed to grade submission.";
    }
    
    header("Location: ../teacher/view_submissions.php?assignment_id=" . $_POST['assignment_id']);
    exit();
}
?>
