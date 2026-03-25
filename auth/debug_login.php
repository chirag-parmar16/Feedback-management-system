<?php
// TEMPORARY DEBUG FILE - DELETE AFTER FIXING
include '../includes/db_connection.php';

echo "<h2>Login Debug Report</h2>";
echo "<pre>";

// 1. Check DB connection
if ($conn->connect_error) {
    echo "❌ DB CONNECTION FAILED: " . $conn->connect_error . "\n";
    exit;
} else {
    echo "✅ DB connected to '$conn->host_info'\n\n";
}

// 2. Check users table exists
$r = $conn->query("SHOW TABLES LIKE 'users'");
if ($r->num_rows === 0) {
    echo "❌ CRITICAL: 'users' table does NOT exist!\n";
    exit;
} else {
    echo "✅ 'users' table exists\n\n";
}

// 3. Show all users (email, role, password hash snippet)
$r = $conn->query("SELECT id, username, email, role, LEFT(password, 20) as pw_hint, created_at FROM users LIMIT 20");
echo "--- Users in database ---\n";
if ($r->num_rows === 0) {
    echo "⚠️  NO USERS in database! Run setup_default_users.php first.\n";
} else {
    while($u = $r->fetch_assoc()) {
        echo "ID:{$u['id']}  Role:{$u['role']}  Email:{$u['email']}  Username:{$u['username']}  PW_starts:{$u['pw_hint']}...\n";
    }
}

// 4. Test password_verify for known credentials
echo "\n--- Password Verification Test ---\n";
$test_cases = [
    ['email' => 'admin@sms.com',   'pass' => 'admin123'],
    ['email' => 'teacher@sms.com', 'pass' => 'teacher123'],
    ['email' => 'student@sms.com', 'pass' => 'student123'],
];
foreach ($test_cases as $t) {
    $s = $conn->prepare("SELECT password FROM users WHERE email = ?");
    $s->bind_param("s", $t['email']);
    $s->execute();
    $row = $s->get_result()->fetch_assoc();
    if (!$row) {
        echo "❌ email '{$t['email']}' NOT FOUND in users table\n";
    } else {
        $ok = password_verify($t['pass'], $row['password']);
        echo ($ok ? "✅" : "❌") . " {$t['email']} / {$t['pass']} → " . ($ok ? "VALID" : "WRONG HASH") . "\n";
    }
}

// 5. Check PHP session working
session_start();
$_SESSION['test'] = 'ok';
echo "\n--- PHP Session ---\n";
echo "✅ session ok, id: " . session_id() . "\n";

echo "\n--- PHP version ---\n";
echo phpversion() . "\n";

echo "</pre>";
echo "<hr><p><strong>After reviewing, visit: <a href='setup_default_users.php'>run setup_default_users.php</a> to reset passwords</strong></p>";
echo "<p><a href='login.php'>Back to Login</a></p>";
?>
