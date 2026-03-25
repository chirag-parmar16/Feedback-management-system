<?php
session_start();
$_SESSION['redirect_test'] = "Session verified at " . date('H:i:s');
header("Location: test_session_get.php");
exit();
?>
