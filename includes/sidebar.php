<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>

<?php
$user_name = $_SESSION['username'] ?? 'User';
$role      = $_SESSION['role'] ?? '';
$cur = basename($_SERVER['PHP_SELF']);

function nav_link(string $href, string $icon, string $label, string $cur): string {
    $active = (basename($href) === $cur) ? 'active' : '';
    return "<a href=\"$href\" class=\"$active\"><i class=\"fas fa-$icon\"></i> $label</a>";
}
?>

<div class="sidebar">
    <div class="sidebar-brand">
        <div class="sidebar-brand-mark">
            <i class="fas fa-graduation-cap" style="color:white;font-size:.85rem"></i>
        </div>
        SMS Portal
    </div>

    <?php if (!empty($role)): ?>
        <nav>
            <?php if ($role === 'admin'): ?>
                <p class="sidebar-section-label">Overview</p>
                <?php echo nav_link('../admin/admin_dashboard.php', 'th-large', 'Dashboard', $cur); ?>

                <p class="sidebar-section-label">Student Management</p>
                <?php echo nav_link('../admin/enroll_students.php','user-graduate',      'Enroll Students',  $cur); ?>
                <?php echo nav_link('../admin/manage_classes.php', 'layer-group',        'Classes',          $cur); ?>
                <?php echo nav_link('../admin/manage_timetable.php', 'calendar-alt',     'Class Timetable',  $cur); ?>

                <p class="sidebar-section-label">Faculty Management</p>
                <?php echo nav_link('../admin/assign_teachers.php','chalkboard-teacher', 'Assign Teachers',  $cur); ?>
                <?php echo nav_link('../admin/manage_users.php',   'users-cog',          'System Users',     $cur); ?>

                <p class="sidebar-section-label">Reports</p>
                <?php echo nav_link('../admin/admin_feedback.php',   'comment-dots',    'Feedback Forms',    $cur); ?>
                <?php echo nav_link('../admin/view_feedback_results.php', 'chart-line', 'Feedback Analytics', $cur); ?>
                <?php echo nav_link('../admin/admin_attendance.php', 'calendar-check',  'Attendance Report', $cur); ?>

            <?php elseif ($role === 'teacher'): ?>
                <p class="sidebar-section-label">Overview</p>
                <?php echo nav_link('../teacher/teacher_dashboard.php', 'th-large',      'Dashboard',  $cur); ?>

                <p class="sidebar-section-label">Classroom</p>
                <?php echo nav_link('../teacher/attendance.php',    'calendar-check', 'Attendance',   $cur); ?>
                <?php echo nav_link('../teacher/view_timetable.php', 'calendar-alt',   'Timetable',    $cur); ?>
                <?php echo nav_link('../teacher/manage_marks.php',  'poll',           'Marks',        $cur); ?>
                <?php echo nav_link('../teacher/manage_assignments.php', 'book',      'Assignments',  $cur); ?>

                <p class="sidebar-section-label">Personal</p>
                <?php echo nav_link('../teacher/my_attendance.php', 'user-clock',    'My Attendance', $cur); ?>
                <?php echo nav_link('../teacher/my_feedback.php',    'comment-alt',   'My Feedback',   $cur); ?>

            <?php else: /* student */ ?>
                <p class="sidebar-section-label">Overview</p>
                <?php echo nav_link('../student/dashboard.php',        'th-large',        'Dashboard',   $cur); ?>

                <p class="sidebar-section-label">Academics</p>
                <?php echo nav_link('../student/view_timetable.php',            'calendar-alt', 'Timetable',            $cur); ?>
                <?php echo nav_link('../student/my_performance.php',            'chart-line',   'Performance',          $cur); ?>
                <?php echo nav_link('../student/student_attendance_calendar.php','calendar-alt', 'My Attendance',        $cur); ?>
                <?php echo nav_link('../student/submit_assignment.php',         'upload',       'Submissions',          $cur); ?>

                <p class="sidebar-section-label">Feedback</p>
                <?php echo nav_link('../student/feedback.php',         'comment-dots',    'Feedback',    $cur); ?>
            <?php endif; ?>
        </nav>

        <div class="sidebar-footer">
            <div class="sidebar-user-block">
                <div class="sidebar-user-avatar">
                    <?php echo strtoupper(substr($user_name, 0, 1)); ?>
                </div>
                <div class="sidebar-user-info">
                    <div class="sidebar-user-name"><?php echo htmlspecialchars($user_name); ?></div>
                    <div class="sidebar-user-role"><?php echo ucfirst($role); ?></div>
                </div>
            </div>
            <a href="../auth/logout.php" class="sidebar-logout">
                <i class="fas fa-sign-out-alt"></i> Sign Out
            </a>
        </div>
    <?php endif; ?>
</div>
