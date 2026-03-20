<?php
include 'includes/db_connection.php';

$users = [
    ['username' => 'admin', 'password' => 'admin123', 'email' => 'admin@sms.com', 'role' => 'admin'],
    ['username' => 'teacher', 'password' => 'teacher123', 'email' => 'teacher@sms.com', 'role' => 'teacher'],
    ['username' => 'student', 'password' => 'student123', 'email' => 'student@sms.com', 'role' => 'student'],
];

foreach ($users as $user) {
    $hashed_password = password_hash($user['password'], PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT IGNORE INTO users (username, password, email, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $user['username'], $hashed_password, $user['email'], $user['role']);
    if ($stmt->execute()) {
        echo "User '{$user['username']}' created successfully.\n";
    } else {
        echo "Error creating user '{$user['username']}': " . $conn->error . "\n";
    }
}
?>
