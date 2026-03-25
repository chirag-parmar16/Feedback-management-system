<?php
session_start();
include '../includes/db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] == 'student') {
    die("Access denied.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_slot_action'])) {
    $class_id = $_POST['class_id'];
    $subject_id = $_POST['subject_id'];
    $teacher_id = $_POST['teacher_id'];
    $day = $_POST['day_of_week'];
    $start = $_POST['start_time'];
    $end = $_POST['end_time'];

    // 1. Check for Teacher Conflict (Same teacher, same day, overlapping time)
    $stmt = $conn->prepare("SELECT id FROM timetables WHERE teacher_id = ? AND day_of_week = ? AND ((start_time < ? AND end_time > ?) OR (start_time < ? AND end_time > ?) OR (start_time >= ? AND end_time <= ?))");
    $stmt->bind_param("isssssss", $teacher_id, $day, $end, $start, $start, $start, $start, $end);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $_SESSION['error'] = "Teacher is already busy at this time.";
        header("Location: ../admin/manage_timetable.php");
        exit();
    }

    // 2. Check for Class Conflict (Same class, same day, overlapping time)
    $stmt = $conn->prepare("SELECT id FROM timetables WHERE class_id = ? AND day_of_week = ? AND ((start_time < ? AND end_time > ?) OR (start_time < ? AND end_time > ?) OR (start_time >= ? AND end_time <= ?))");
    $stmt->bind_param("isssssss", $class_id, $day, $end, $start, $start, $start, $start, $end);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $_SESSION['error'] = "Class is already scheduled for another subject at this time.";
        header("Location: ../admin/manage_timetable.php");
        exit();
    }

    // Insert Slot
    $stmt = $conn->prepare("INSERT INTO timetables (class_id, subject_id, teacher_id, day_of_week, start_time, end_time) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iiisss", $class_id, $subject_id, $teacher_id, $day, $start, $end);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Timetable slot added successfully!";
    } else {
        $_SESSION['error'] = "Failed to add timetable slot.";
    }

    header("Location: ../admin/manage_timetable.php");
    exit();
}
?>
