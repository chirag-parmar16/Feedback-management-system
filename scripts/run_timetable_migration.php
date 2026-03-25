<?php
$conn = new mysqli('localhost', 'root', '', 'userdb');
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$sql = file_get_contents('scripts/timetable.sql');
if ($conn->multi_query($sql)) {
    do {
        if ($result = $conn->store_result()) {
            $result->free();
        }
    } while ($conn->next_result());
    echo "Migration Successful";
} else {
    echo "Migration Failed: " . $conn->error;
}
$conn->close();
?>
