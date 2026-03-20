<?php
include 'includes/db_connection.php';
$result = $conn->query("DESCRIBE attendance");
if ($result) {
    echo "Table: attendance\n";
    while ($row = $result->fetch_assoc()) {
        print_r($row);
    }
} else {
    echo "Error describing attendance: " . $conn->error . "\n";
}

$result = $conn->query("SHOW INDEX FROM attendance");
if ($result) {
    echo "\nIndexes for attendance:\n";
    while ($row = $result->fetch_assoc()) {
        print_r($row);
    }
} else {
    echo "Error showing indexes: " . $conn->error . "\n";
}

// Also check if the table exists
$result = $conn->query("SHOW TABLES LIKE 'attendance'");
if ($result->num_rows == 0) {
    echo "\nCRITICAL: Table 'attendance' DOES NOT EXIST!\n";
}
?>
