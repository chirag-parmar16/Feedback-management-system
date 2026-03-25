<?php
include '../includes/db_connection.php';
$sql = "ALTER TABLE marks ADD UNIQUE INDEX idx_marks_unique (student_id, subject_id, exam_date)";
if ($conn->query($sql)) {
    echo "Successfully added unique index idx_marks_unique(student_id, subject_id, exam_date)\n";
} else {
    echo "Error adding unique index: " . $conn->error . "\n";
}
?>
