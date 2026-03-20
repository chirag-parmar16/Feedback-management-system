<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$user_name = isset($_SESSION['username']) ? $_SESSION['username'] : 'User';

// Fallback titles if not set in the page
if (!isset($page_title)) {
    $current_page = basename($_SERVER['PHP_SELF']);
    if($current_page == 'admin_dashboard.php') {
        $page_title = 'Admin Command Center';
        $page_subtitle = 'System-wide oversight and management.';
    } elseif($current_page == 'teacher_dashboard.php') {
        $page_title = 'Teacher Control Panel';
        $page_subtitle = 'Manage your classes and academic tasks.';
    } elseif($current_page == 'dashboard.php') {
        $page_title = 'Student Hub';
        $page_subtitle = 'Track your learning and performance.';
    } else {
        $page_title = 'School Management System';
        $page_subtitle = 'Educational Platform';
    }
}
?>

<nav class="navbar navbar-expand-lg fixed-top">
    <div class="container-fluid">
        <div class="navbar-title-container">
            <span class="navbar-page-title"><?php echo htmlspecialchars($page_title); ?></span>
            <?php if (isset($page_subtitle) && $page_subtitle): ?>
                <span class="navbar-page-subtitle"><?php echo htmlspecialchars($page_subtitle); ?></span>
            <?php endif; ?>
        </div>
        
        <div class="ms-auto d-flex align-items-center">
            <div class="user-pill d-flex align-items-center bg-light px-3 py-1 rounded-pill">
                <div class="avatar-xs bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 24px; height: 24px; font-size: 10px;">
                    <?php echo strtoupper(substr($user_name, 0, 1)); ?>
                </div>
                <span class="small fw-bold text-dark"><?php echo htmlspecialchars($user_name); ?></span>
            </div>
        </div>
    </div>
</nav>

<style>
    .navbar { box-shadow: none !important; }
    .user-pill { border: 1px solid #e2e8f0; }
</style>
