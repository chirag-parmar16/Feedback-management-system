<?php
session_start();
include '../../includes/db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/login.php"); exit();
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Get class_id for redirect before deleting
    $res = $conn->query("SELECT class_id FROM timetable WHERE id = $id");
    $row = $res->fetch_assoc();
    $class_id = $row['class_id'] ?? 0;

    $stmt = $conn->prepare("DELETE FROM timetable WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $_SESSION['toast'] = ['message' => 'Slot removed.', 'type' => 'success'];
    } else {
        $_SESSION['toast'] = ['message' => 'Error deleting slot.', 'type' => 'error'];
    }
    
    header("Location: ../manage_timetable.php?class_id=$class_id");
} else {
    header("Location: ../manage_timetable.php");
}
exit();
?>
