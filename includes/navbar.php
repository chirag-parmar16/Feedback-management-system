<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$user_name = $_SESSION['username'] ?? 'User';

if (!isset($page_title)) {
    $cur = basename($_SERVER['PHP_SELF']);
    $titles = [
        'admin_dashboard.php'   => ['Dashboard',          'System overview and stats'],
        'teacher_dashboard.php' => ['Dashboard',          'Your academic overview'],
        'dashboard.php'         => ['Dashboard',          'Your student portal'],
        'manage_users.php'      => ['Users',              'Manage all user accounts'],
        'add_user.php'          => ['Add User',           'Register a new user account'],
        'manage_classes.php'    => ['Classes',            'Set up and manage class sections'],
        'enroll_students.php'   => ['Enroll Students',    'Assign students to classes'],
        'assign_teachers.php'   => ['Assign Teachers',    'Link teachers to class subjects'],
        'attendance.php'        => ['Attendance',         'Record student attendance'],
        'manage_marks.php'      => ['Marks',              'Enter and manage exam marks'],
        'manage_assignments.php'=> ['Assignments',        'Post and review assignments'],
        'my_attendance.php'     => ['My Attendance',      'Log and view your attendance'],
        'my_performance.php'    => ['My Performance',     'Your grades and attendance summary'],
        'feedback.php'          => ['Feedback',           'Submit teacher feedback'],
        'admin_feedback.php'    => ['Feedback Forms',     'Create and monitor feedback campaigns'],
        'admin_attendance.php'   => ['Attendance Report',  'Student and teacher attendance by month'],
        'student_attendance_calendar.php' => ['My Attendance','Monthly attendance calendar'],
    ];
    $page_title    = $titles[$cur][0] ?? 'School Management';
    $page_subtitle = $titles[$cur][1] ?? '';
}

// Toast message from session
$_toast = null;
if (!empty($_SESSION['toast'])) {
    $_toast = $_SESSION['toast'];
    unset($_SESSION['toast']);
}
?>

<nav class="navbar navbar-expand-lg fixed-top">
    <div class="container-fluid">
        <div class="navbar-title-container">
            <span class="navbar-page-title"><?php echo htmlspecialchars($page_title); ?></span>
            <?php if (!empty($page_subtitle)): ?>
                <span class="navbar-page-subtitle"><?php echo htmlspecialchars($page_subtitle); ?></span>
            <?php endif; ?>
        </div>

        <div class="ms-auto d-flex align-items-center gap-3">
            <div class="user-pill d-flex align-items-center bg-white px-3 py-2 rounded-pill border"
                 style="border-color:#e2e8f0!important;cursor:default">
                <div style="width:24px;height:24px;background:var(--primary);border-radius:8px;display:flex;align-items:center;justify-content:center;color:white;font-size:10px;font-weight:700;margin-right:8px">
                    <?php echo strtoupper(substr($user_name, 0, 1)); ?>
                </div>
                <span class="small fw-semibold text-dark"><?php echo htmlspecialchars($user_name); ?></span>
            </div>
        </div>
    </div>
</nav>

<?php if (!empty($_toast)): ?>
<!-- Toast stack rendered by JS below -->
<div id="server-toast-data"
     data-message="<?php echo htmlspecialchars($_toast['message']); ?>"
     data-type="<?php echo htmlspecialchars($_toast['type'] ?? 'success'); ?>"
     style="display:none"></div>
<?php endif; ?>
