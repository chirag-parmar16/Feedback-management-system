<?php
session_start();
echo "<h2>Session Persistence Test</h2>";
if (isset($_SESSION['redirect_test'])) {
    echo "✅ SUCCESS: Session variable found: " . $_SESSION['redirect_test'];
} else {
    echo "❌ FAILED: Session variable NOT found. Sessions are not persisting across redirects.";
}
echo "<br><br><a href='login.php'>Back to Login</a>";
?>
