<?php
include '../includes/db_connection.php';

// 1. Add Unique Constraint to prevent duplicates and allow ON DUPLICATE KEY UPDATE
$sql = "ALTER TABLE attendance ADD UNIQUE INDEX idx_attendance_unique (student_id, subject_id, date)";
if ($conn->query($sql)) {
    echo "Successfully added unique index idx_attendance_unique(student_id, subject_id, date)\n";
} else {
    echo "Error adding unique index: " . $conn->error . "\n";
}

// 2. Check if the table has 'class_id' - actually logic says we use subject_id and date.
// But some scripts might need class_id. Let's see.
// The logic uses subject_id which is correct for mapping to a teacher.

// 3. Verify column types
$result = $conn->query("DESCRIBE attendance");
while ($row = $result->fetch_assoc()) {
    echo "Field: " . $row['Field'] . " | Type: " . $row['Type'] . "\n";
}
?>
