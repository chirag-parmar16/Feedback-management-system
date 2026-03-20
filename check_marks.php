<?php
include 'includes/db_connection.php';
$result = $conn->query("DESCRIBE marks");
while ($row = $result->fetch_assoc()) {
    print_r($row);
}
$result = $conn->query("SHOW INDEX FROM marks");
while ($row = $result->fetch_assoc()) {
    print_r($row);
}
?>
