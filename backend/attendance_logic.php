<?php
session_start();
include '../includes/db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../login.php"); exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['attendance'])) {
    $subject_id      = (int) ($_POST['subject_id'] ?? 0);
    $class_id        = (int) ($_POST['class_id']   ?? 0);
    $date            = $_POST['date'] ?? date('Y-m-d');
    $attendance_data = $_POST['attendance']; // [student_id => status]

    // Validate date format
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        $date = date('Y-m-d');
    }

    // Ensure UNIQUE constraint exists (safe to run repeatedly)
    $conn->query("ALTER IGNORE TABLE attendance ADD UNIQUE KEY IF NOT EXISTS uq_attendance (student_id, subject_id, date)");

    $saved = 0;
    foreach ($attendance_data as $student_id => $status) {
        $student_id = (int) $student_id;
        $status     = ($status === 'Present') ? 'Present' : 'Absent';

        $stmt = $conn->prepare(
            "INSERT INTO attendance (student_id, subject_id, status, date) VALUES (?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE status = VALUES(status)"
        );
        $stmt->bind_param("iiss", $student_id, $subject_id, $status, $date);
        if ($stmt->execute()) $saved++;
    }

    $_SESSION['toast'] = [
        'message' => "Attendance for $date saved — $saved student(s) recorded.",
        'type'    => 'success'
    ];
    header("Location: ../attendance.php?assignment_id=" . $subject_id . "-" . $class_id);
    exit();
}

// If reached without POST — redirect back
header("Location: ../attendance.php"); exit();
