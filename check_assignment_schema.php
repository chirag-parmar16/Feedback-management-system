<?php
include 'includes/db_connection.php';

echo "Table: assignments\n";
$result = $conn->query("DESCRIBE assignments");
while ($row = $result->fetch_assoc()) {
    print_r($row);
}

echo "\nTable: teacher_assignment\n";
$result = $conn->query("DESCRIBE teacher_assignment");
while ($row = $result->fetch_assoc()) {
    print_r($row);
}
?>
