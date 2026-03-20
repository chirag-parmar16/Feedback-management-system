<div class="sidebar">
    <div class="sidebar-brand">
        SMS Portal
    </div>
    
    <?php if (isset($_SESSION['role'])): ?>
        <nav>
            <?php if ($_SESSION['role'] == 'admin'): ?>
                <a href="admin_dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin_dashboard.php' ? 'active' : ''; ?>">
                    <i class="fas fa-th-large"></i> Overview
                </a>
                <a href="manage_users.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'manage_users.php' ? 'active' : ''; ?>">
                    <i class="fas fa-users-cog"></i> Users
                </a>
                <a href="manage_classes.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'manage_classes.php' ? 'active' : ''; ?>">
                    <i class="fas fa-layer-group"></i> Classes
                </a>
                <a href="enroll_students.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'enroll_students.php' ? 'active' : ''; ?>">
                    <i class="fas fa-user-graduate"></i> Enrollment
                </a>
                <a href="assign_teachers.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'assign_teachers.php' ? 'active' : ''; ?>">
                    <i class="fas fa-chalkboard-teacher"></i> Assignments
                </a>
                <a href="admin_feedback.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin_feedback.php' ? 'active' : ''; ?>">
                    <i class="fas fa-comment-dots"></i> Feedback
                </a>
            <?php elseif ($_SESSION['role'] == 'teacher'): ?>
                <a href="teacher_dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'teacher_dashboard.php' ? 'active' : ''; ?>">
                    <i class="fas fa-th-large"></i> Dashboard
                </a>
                <a href="attendance.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'attendance.php' ? 'active' : ''; ?>">
                    <i class="fas fa-calendar-check"></i> Attendance
                </a>
                <a href="manage_marks.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'manage_marks.php' ? 'active' : ''; ?>">
                    <i class="fas fa-poll"></i> Marks
                </a>
                <a href="manage_assignments.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'manage_assignments.php' ? 'active' : ''; ?>">
                    <i class="fas fa-book"></i> Assignments
                </a>
            <?php else: ?>
                <a href="dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                    <i class="fas fa-th-large"></i> Dashboard
                </a>
                <a href="my_performance.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'my_performance.php' ? 'active' : ''; ?>">
                    <i class="fas fa-chart-line"></i> Performance
                </a>
                <a href="submit_assignment.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'submit_assignment.php' ? 'active' : ''; ?>">
                    <i class="fas fa-upload"></i> Submissions
                </a>
                <a href="feedback.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'feedback.php' ? 'active' : ''; ?>">
                    <i class="fas fa-comment-dots"></i> Feedback
                </a>
            <?php endif; ?>
        </nav>
        
        <div class="mt-auto">
             <a href="logout.php" class="bg-danger text-white border-0 py-2 mt-4 justify-content-center">
                 <i class="fas fa-sign-out-alt me-0"></i> <span>Logout</span>
             </a>
        </div>
    <?php endif; ?>
</div>

<style>
    /* CSS moved to index.css for cleaner loading, keeping refined local fallback if needed */
</style>
