<?php
session_start();
include '../includes/db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php?error=unauthorized"); exit();
}


$page_title    = 'Dashboard';
$page_subtitle = 'System overview and recent activity';

// ── Stats (all safe — check query result before accessing) ──────────────────
function safe_count($conn, $sql) {
    $r = $conn->query($sql);
    if (!$r) return 0;
    $row = $r->fetch_assoc();
    return (int)($row['c'] ?? 0);
}

$total_students = safe_count($conn, "SELECT COUNT(*) as c FROM users WHERE role='student'");
$total_teachers = safe_count($conn, "SELECT COUNT(*) as c FROM users WHERE role='teacher'");
$total_classes  = safe_count($conn, "SELECT COUNT(*) as c FROM classes");

// ── Advanced Stats ───────────────────────────────────────────
$total_fees_collected = safe_count($conn, "SELECT SUM(amount_paid) as c FROM payments");
$total_outstanding = safe_count($conn, "SELECT SUM(total_amount - paid_amount) as c FROM student_fees");

// Attendance Rate (Last 30 days)
$attendance_rate = 0;
$att_res = $conn->query("SELECT (COUNT(CASE WHEN status='Present' THEN 1 END) / COUNT(*)) * 100 as rate FROM attendance WHERE date > DATE_SUB(NOW(), INTERVAL 30 DAY)");
if ($att_res) $attendance_rate = round($att_res->fetch_assoc()['rate'] ?? 0, 1);

// Recent registrations — last 6 users (safe query)
$recent_users = $conn->query(
    "SELECT u.id, u.username, u.email, u.role, u.created_at, p.first_name, p.last_name
     FROM users u LEFT JOIN profile_info p ON u.id = p.user_id
     ORDER BY u.created_at DESC LIMIT 6"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard | SMS</title>
    <?php include '../includes/links.php'; ?>
    <link rel="stylesheet" href="../assets/index.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    <?php include '../includes/sidebar.php'; ?>

    <div class="content-wrapper">
        <div class="container-fluid px-0">

            <!-- ── Stat Cards ──────────────────────────────────────────── -->
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3">
                    <a href="manage_users.php?filter=student" class="stat-card stat-primary text-decoration-none">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="stat-icon"><i class="fas fa-user-graduate"></i></div>
                            <i class="fas fa-arrow-right" style="color:rgba(255,255,255,0.4);font-size:.75rem"></i>
                        </div>
                        <div class="stat-number"><?php echo $total_students; ?></div>
                        <div class="stat-label">Students</div>
                    </a>
                </div>
                <div class="col-6 col-md-3">
                    <a href="manage_users.php?filter=teacher" class="stat-card stat-indigo text-decoration-none">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="stat-icon"><i class="fas fa-chalkboard-teacher"></i></div>
                            <i class="fas fa-arrow-right" style="color:rgba(255,255,255,0.4);font-size:.75rem"></i>
                        </div>
                        <div class="stat-number"><?php echo $total_teachers; ?></div>
                        <div class="stat-label">Teachers</div>
                    </a>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-card stat-success">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="stat-icon"><i class="fas fa-wallet"></i></div>
                            <span class="small text-white-50">Collected</span>
                        </div>
                        <div class="stat-number">₹<?php echo number_format($total_fees_collected / 1000, 1); ?>k</div>
                        <div class="stat-label">Fee Collections</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-card stat-sky">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="stat-icon"><i class="fas fa-percentage"></i></div>
                            <span class="small text-white-50">30d Avg</span>
                        </div>
                        <div class="stat-number"><?php echo $attendance_rate; ?>%</div>
                        <div class="stat-label">Attendance Rate</div>
                    </div>
                </div>
            </div>

            <!-- ── Analytics Charts ────────────────────────────────────── -->
            <div class="row g-3 mb-4">
                <div class="col-md-7">
                    <div class="premium-panel h-100">
                        <h6 class="fw-bold mb-4">Weekly Attendance Trend</h6>
                        <canvas id="attendanceChart" height="200"></canvas>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="premium-panel h-100">
                        <h6 class="fw-bold mb-4">Revenue vs Outstanding</h6>
                        <canvas id="feeChart" height="200"></canvas>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <!-- ── Recent Users ────────────────────────────────────── -->
                <div class="col-md-8">
                    <div class="premium-panel">
                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <div class="flat-header mb-0 pb-0 border-0">
                                <h5>Recently Registered</h5>
                                <p>Newest user accounts in the system</p>
                            </div>
                            <a href="add_user.php" class="btn btn-primary btn-sm px-3 py-2 rounded-3 fw-bold" style="font-size:.8rem">
                                <i class="fas fa-plus me-1"></i> Add User
                            </a>
                        </div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Joined</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($recent_users && $recent_users->num_rows > 0): ?>
                                        <?php while($u = $recent_users->fetch_assoc()): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div style="width:32px;height:32px;background:var(--primary-soft);border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:.8rem;font-weight:700;color:var(--primary);flex-shrink:0">
                                                            <?php echo strtoupper(substr($u['username'], 0, 1)); ?>
                                                        </div>
                                                        <div>
                                                            <div class="fw-semibold small"><?php echo htmlspecialchars($u['username']); ?></div>
                                                            <?php if($u['first_name']): ?>
                                                                <div class="extra-small text-muted"><?php echo htmlspecialchars($u['first_name'] . ' ' . $u['last_name']); ?></div>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="small text-muted"><?php echo htmlspecialchars($u['email']); ?></td>
                                                <td>
                                                    <?php
                                                        $rc = ['admin'=>'bg-danger-soft','teacher'=>'bg-warning-soft','student'=>'bg-primary-soft'];
                                                        $cls = $rc[$u['role']] ?? 'bg-primary-soft';
                                                    ?>
                                                    <span class="badge <?php echo $cls; ?> px-2"><?php echo ucfirst($u['role']); ?></span>
                                                </td>
                                                <td class="small text-muted"><?php echo date('d M Y', strtotime($u['created_at'])); ?></td>
                                                <td>
                                                    <a href="add_user.php?edit_id=<?php echo $u['id']; ?>" class="btn btn-light border btn-sm px-3 small fw-bold">Edit</a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr><td colspan="5" class="text-center py-5 text-muted small">No users registered yet.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="pt-3 border-top">
                            <a href="manage_users.php" class="small fw-bold text-primary text-decoration-none">
                                View all users <i class="fas fa-arrow-right ms-1" style="font-size:.7rem"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- ── Quick Actions ───────────────────────────────────── -->
                <div class="col-md-4">
                    <div class="premium-panel">
                        <div class="flat-header">
                            <h5>Quick Actions</h5>
                            <p>Common admin tasks</p>
                        </div>
                        <div class="d-grid gap-2">
                            <a href="add_user.php" class="btn btn-primary py-3 fw-bold text-start px-4" style="font-size:.875rem">
                                <i class="fas fa-user-plus me-2"></i> Register New User
                            </a>
                            <a href="manage_classes.php" class="btn py-3 fw-bold text-start px-4 bg-light border" style="font-size:.875rem;color:var(--text-main)">
                                <i class="fas fa-layer-group me-2 text-primary"></i> Manage Classes
                            </a>
                            <a href="manage_timetable.php" class="btn py-3 fw-bold text-start px-4 bg-light border" style="font-size:.875rem;color:var(--text-main)">
                                <i class="fas fa-calendar-alt me-2 text-primary"></i> Manage Timetable
                            </a>
                            <a href="enroll_students.php" class="btn py-3 fw-bold text-start px-4 bg-light border" style="font-size:.875rem;color:var(--text-main)">
                                <i class="fas fa-user-graduate me-2 text-primary"></i> Enroll Students
                            </a>
                            <a href="assign_teachers.php" class="btn py-3 fw-bold text-start px-4 bg-light border" style="font-size:.875rem;color:var(--text-main)">
                                <i class="fas fa-chalkboard-teacher me-2 text-primary"></i> Assign Teachers
                            </a>
                            <a href="admin_attendance.php?view=teacher" class="btn py-3 fw-bold text-start px-4 bg-light border" style="font-size:.875rem;color:var(--text-main)">
                                <i class="fas fa-calendar-alt me-2 text-primary"></i> Teacher Attendance Report
                            </a>
                            <a href="admin_feedback.php" class="btn py-3 fw-bold text-start px-4 bg-light border" style="font-size:.875rem;color:var(--text-main)">
                                <i class="fas fa-comment-dots me-2 text-primary"></i> Create Feedback Form
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/scripts.php'; ?>
    <script>
        // Attendance Chart
        const ctxAtt = document.getElementById('attendanceChart').getContext('2d');
        new Chart(ctxAtt, {
            type: 'line',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
                datasets: [{
                    label: 'Attendance %',
                    data: [85, 92, 88, 95, 90, 82], // Static for now, can be dynamically fetched
                    borderColor: '#2563eb',
                    tension: 0.4,
                    fill: true,
                    backgroundColor: 'rgba(37, 99, 235, 0.1)'
                }]
            },
            options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, max: 100 } } }
        });

        // Fee Chart
        const ctxFee = document.getElementById('feeChart').getContext('2d');
        new Chart(ctxFee, {
            type: 'doughnut',
            data: {
                labels: ['Collected', 'Outstanding'],
                datasets: [{
                    data: [<?php echo $total_fees_collected; ?>, <?php echo $total_outstanding; ?>],
                    backgroundColor: ['#10b981', '#ef4444']
                }]
            },
            options: { cutout: '70%', plugins: { legend: { position: 'bottom' } } }
        });
    </script>
</body>
</html>
