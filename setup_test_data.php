<?php
include './includes/db_connection.php';

// This script ensures there is at least some data to test the Student Listing functionality.
echo "<h2>Initializing Test Environment...</h2>";

// 1. Ensure a class exists
$conn->query("INSERT IGNORE INTO classes (id, name, section) VALUES (1, 'Grade 10', 'A')");

// 2. Ensure a subject exists
$conn->query("INSERT IGNORE INTO subjects (id, name, code) VALUES (1, 'Mathematics', 'MATH101')");

// 3. Ensure a teacher is assigned
$conn->query("INSERT IGNORE INTO teacher_subjects (teacher_id, subject_id, class_id) VALUES (2, 1, 1)");

// 4. Ensure a student (id=3 usually in our setup) is enrolled in class 1
// We check if user with id 3 is a student
$check_student = $conn->query("SELECT id FROM users WHERE id = 3 AND role = 'student'");
if ($check_student->num_rows > 0) {
    $conn->query("INSERT IGNORE INTO student_enrollment (student_id, class_id) VALUES (3, 1)");
    echo "Student (ID: 3) enrolled in Grade 10-A.<br>";
} else {
    // Fallback search for any student
    $any_student = $conn->query("SELECT id FROM users WHERE role = 'student' LIMIT 1")->fetch_assoc();
    if ($any_student) {
        $sid = $any_student['id'];
        $conn->query("INSERT IGNORE INTO student_enrollment (student_id, class_id) VALUES ($sid, 1)");
        echo "Student (ID: $sid) enrolled in Grade 10-A.<br>";
    } else {
        echo "<b>Warning:</b> No student users found in database. Please create a student first.<br>";
    }
}

echo "<br><b>Database alignment complete.</b> Student lists should now appear in Attendance/Marks for Mathematics (Grade 10-A).";
?>
