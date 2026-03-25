<?php
session_start();
include '../includes/db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

$student_id = $_SESSION['user_id'];

// Fetch graded assignments
$query = "SELECT a.title, s.name as subject_name, asub.marks, asub.remarks, asub.graded_at 
          FROM assignment_submissions asub 
          JOIN assignments a ON asub.assignment_id = a.id 
          JOIN subjects s ON a.subject_id = s.id 
          WHERE asub.student_id = ? AND asub.marks IS NOT NULL 
          ORDER BY asub.graded_at DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$results = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Assignment Grades</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .grade-row { border: 1px solid #eee; padding: 15px; margin-bottom: 10px; border-radius: 8px; display: flex; justify-content: space-between; align-items: center; }
        .marks-badge { background: #E8F5E9; color: #2E7D32; padding: 5px 15px; border-radius: 20px; font-weight: bold; }
        .remarks { font-style: italic; color: #666; font-size: 0.9em; }
    </style>
</head>
<body>
    <div class="container">
        <h2>My Grades & Feedback</h2>
        <a href="dashboard.php">← Back to Dashboard</a>
        <hr>

        <?php if ($results->num_rows > 0): ?>
            <?php while ($row = $results->fetch_assoc()): ?>
                <div class="grade-row">
                    <div>
                        <strong><?php echo $row['title']; ?></strong> (<?php echo $row['subject_name']; ?>)
                        <p class="remarks"><?php echo $row['remarks'] ? 'Teacher\'s Remarks: "' . $row['remarks'] . '"' : 'No remarks provided.'; ?></p>
                        <small>Graded on: <?php echo date('M d, Y', strtotime($row['graded_at'])); ?></small>
                    </div>
                    <div class="marks-badge">
                        Marks: <?php echo $row['marks']; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No graded assignments yet.</p>
        <?php endif; ?>
    </div>
</body>
</html>
