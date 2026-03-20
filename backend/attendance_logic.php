<?php
session_start();
include '../includes/db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['attendance'])) {
    $subject_id = $_POST['subject_id'];
    $date = $_POST['date'];
    $attendance_data = $_POST['attendance']; // student_id => status

    foreach ($attendance_data as $student_id => $status) {
        $stmt = $conn->prepare("INSERT INTO attendance (student_id, subject_id, status, date) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE status = VALUES(status)");
        $stmt->bind_param("iiss", $student_id, $subject_id, $status, $date);
        $stmt->execute();
    }
    
    $_SESSION['message'] = "Attendance for " . $date . " recorded successfully!";
    header("Location: ../attendance.php?assignment_id=" . $subject_id . "-" . $_POST['class_id']);
    exit();
}
?>
