<?php
session_start();
include '../includes/db_connection.php';

$class_id = $_GET['class_id'] ?? 0;
$year     = $_GET['year'] ?? date('Y');

// Count existing students in this class for this year
// Pattern: Enrollment ID starts with the year
$pattern = $year . "%";

// Alternative: Count records in student_enrollment for this class
// But to be safe for ID generation, let's look at counts in profile_info or enrollment
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM student_enrollment WHERE class_id = ? AND YEAR(enrolled_at) = ?");
$stmt->bind_param("ii", $class_id, $year);
$stmt->execute();
$count = $stmt->get_result()->fetch_assoc()['count'];

$next_seq = $count + 1;

header('Content-Type: application/json');
echo json_encode(['seq' => $next_seq]);
?>
