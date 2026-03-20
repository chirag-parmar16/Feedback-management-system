<?php
session_start();
include '../includes/db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_marks'])) {
    $subject_id = $_POST['subject_id'];
    $exam_date = $_POST['exam_date'];
    $total_marks = $_POST['total_marks'];
    $marks_data = $_POST['marks']; // student_id => marks_obtained

    foreach ($marks_data as $student_id => $obtained) {
        if ($obtained !== '') {
            $stmt = $conn->prepare("INSERT INTO marks (student_id, subject_id, marks_obtained, total_marks, exam_date) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE marks_obtained = VALUES(marks_obtained), total_marks = VALUES(total_marks)");
            $stmt->bind_param("iiiss", $student_id, $subject_id, $obtained, $total_marks, $exam_date);
            $stmt->execute();
        }
    }
    
    $_SESSION['message'] = "Marks uploaded successfully!";
    header("Location: ../manage_marks.php?assignment_id=" . $subject_id . "-" . $_POST['class_id']);
    exit();
}
?>
