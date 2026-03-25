<?php
session_start();
include '../includes/db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int) $_GET['id'];

    // Check if class has enrollments before deleting
    $check = $conn->prepare("SELECT COUNT(*) as cnt FROM student_enrollment WHERE class_id = ?");
    $check->bind_param("i", $id);
    $check->execute();
    $cnt = $check->get_result()->fetch_assoc()['cnt'];

    if ($cnt > 0) {
        $_SESSION['toast'] = ['message' => "Cannot remove class: $cnt student(s) are still enrolled. Unenroll them first.", 'type' => 'error'];
    } else {
        $stmt = $conn->prepare("DELETE FROM classes WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $_SESSION['toast'] = ['message' => 'Class has been removed successfully.', 'type' => 'success'];
    }
}

header("Location: ../manage_classes.php");
exit();
?>
