<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user_name = isset($_SESSION['username']) ? $_SESSION['username'] : 'User';
?>

<div class="navbar navbar-expand-lg navbar-light fixed-top" style="background-color: #343a40; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
    <div class="container-fluid">
        <!-- Brand Section -->
        <a class="navbar-brand text-light" href="#">Student Feedback Management System</a>
        
        <!-- Toggler Button for Mobile -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navbar Links -->
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <!-- Welcome Section -->
                <li class="nav-item">
                    <a class="nav-link text-light" href="#">Welcome, <?php echo htmlspecialchars($user_name); ?></a>
                </li>
            </ul>
        </div>
    </div>
</div>
