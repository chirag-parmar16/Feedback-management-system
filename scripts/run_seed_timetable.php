<?php
$conn = new mysqli('localhost', 'root', '', 'userdb');
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$sql = file_get_contents('scripts/seed_timetable.sql');
if ($conn->multi_query($sql)) {
    do {
        if ($result = $conn->store_result()) {
            $result->free();
        }
    } while ($conn->next_result());
    echo "Seed Successful";
} else {
    echo "Seed Failed: " . $conn->error;
}
$conn->close();
?>
