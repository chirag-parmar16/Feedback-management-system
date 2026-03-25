<?php
session_start();
include '../includes/db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Unauthorized.");
}

// Result Compilation Trigger
if (isset($_GET['process_id'])) {
    $exam_id = (int)$_GET['process_id'];

    // 1. Get all students linked to this exam's academic year (via enrollment)
    $stmt = $conn->prepare("SELECT DISTINCT e.student_id FROM student_enrollment e JOIN exams ex ON e.academic_year_id = ex.academic_year_id WHERE ex.id = ?");
    $stmt->bind_param("i", $exam_id);
    $stmt->execute();
    $students = $stmt->get_result();
    $processed_count = 0;

    while ($s = $students->fetch_assoc()) {
        $student_id = $s['student_id'];

        // 2. Sum up marks for this student for dates falling within the exam period
        $stmt_marks = $conn->prepare("SELECT SUM(marks_obtained) as obtained, SUM(total_marks) as total FROM marks m JOIN exams ex ON m.academic_year_id = ex.academic_year_id WHERE m.student_id = ? AND ex.id = ? AND m.exam_date BETWEEN ex.start_date AND ex.end_date");
        $stmt_marks->bind_param("ii", $student_id, $exam_id);
        $stmt_marks->execute();
        $result_data = $stmt_marks->get_result()->fetch_assoc();

        if ($result_data['total'] > 0) {
            $obtained = $result_data['obtained'];
            $total = $result_data['total'];
            $percentage = ($obtained / $total) * 100;

            // Simple grading logic
            $grade = 'F';
            if ($percentage >= 90) $grade = 'A+';
            elseif ($percentage >= 80) $grade = 'A';
            elseif ($percentage >= 70) $grade = 'B';
            elseif ($percentage >= 60) $grade = 'C';
            elseif ($percentage >= 33) $grade = 'D';

            $status = ($percentage >= 33) ? 'pass' : 'fail';

            $stmt_ins = $conn->prepare("INSERT INTO results (student_id, exam_id, total_marks_obtained, total_max_marks, percentage, grade, result_status) VALUES (?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE total_marks_obtained = VALUES(total_marks_obtained), total_max_marks = VALUES(total_max_marks), percentage = VALUES(percentage), grade = VALUES(grade), result_status = VALUES(result_status)");
            $stmt_ins->bind_param("iiddiss", $student_id, $exam_id, $obtained, $total, $percentage, $grade, $status);
            if ($stmt_ins->execute()) $processed_count++;
        }
    }

    $conn->query("UPDATE exams SET status = 'result_declared' WHERE id = $exam_id");
    $_SESSION['message'] = "Results processed successfully for $processed_count students.";
    header("Location: ../admin/manage_exams.php");
    exit();
}
?>
