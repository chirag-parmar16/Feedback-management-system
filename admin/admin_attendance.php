<?php
session_start();
include '../includes/db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php"); exit();
}

$page_title    = 'Attendance Report';
$page_subtitle = 'View student and teacher attendance across all classes';

// Filters
$filter_class   = isset($_GET['class_id'])   && is_numeric($_GET['class_id'])   ? (int)$_GET['class_id']   : 0;
$filter_date    = isset($_GET['date'])        && preg_match('/^\d{4}-\d{2}$/',$_GET['date']) ? $_GET['date'] : date('Y-m');
$view           = ($_GET['view'] ?? 'student') === 'teacher' ? 'teacher' : 'student';

// All classes for filter dropdown
$classes = $conn->query("SELECT * FROM classes ORDER BY name ASC");

// ── STUDENT ATTENDANCE ───────────────────────────────────────────────────────
if ($view === 'student') {
    $from = $filter_date . '-01';
    $to   = date('Y-m-t', strtotime($from)); // last day of month

    $sql = "SELECT u.id, u.username, p.first_name, p.last_name, p.Enroll_No,
                   c.name as class_name, c.section,
                   COUNT(a.id) as total_sessions,
                   SUM(a.status = 'Present') as present_count,
                   SUM(a.status = 'Absent')  as absent_count
            FROM users u
            LEFT JOIN profile_info p ON u.id = p.user_id
            JOIN student_enrollment se ON u.id = se.student_id
            JOIN classes c ON se.class_id = c.id
            LEFT JOIN attendance a ON a.student_id = u.id AND a.date BETWEEN ? AND ?
            WHERE u.role = 'student'";
    $params = [$from, $to];
    $types  = "ss";

    if ($filter_class) {
        $sql .= " AND c.id = ?";
        $params[] = $filter_class;
        $types   .= "i";
    }
    $sql .= " GROUP BY u.id, se.class_id ORDER BY c.name, p.first_name ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $student_data = $stmt->get_result();
}

// ── TEACHER ATTENDANCE ───────────────────────────────────────────────────────
if ($view === 'teacher') {
    $from = $filter_date . '-01';
    $to   = date('Y-m-t', strtotime($from));

    $stmt = $conn->prepare(
        "SELECT u.id, u.username, p.first_name, p.last_name,
                COUNT(ta.id) as total_days,
                SUM(ta.status = 'Present') as present_count
         FROM users u
         LEFT JOIN profile_info p ON u.id = p.user_id
         LEFT JOIN teacher_attendance ta ON ta.teacher_id = u.id AND ta.date BETWEEN ? AND ?
         WHERE u.role = 'teacher'
         GROUP BY u.id
         ORDER BY p.first_name ASC"
    );
    $stmt->bind_param("ss", $from, $to);
    $stmt->execute();
    $teacher_data = $stmt->get_result();
}

$month_name = date('F Y', strtotime($filter_date . '-01'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Attendance Report | Admin</title>
    <?php include '../includes/links.php'; ?>
    <link rel="stylesheet" href="../assets/index.css">
    <style>
        .pct-bar { height: 6px; border-radius: 10px; background: #f1f5f9; overflow: hidden; }
        .pct-bar-fill { height: 100%; border-radius: 10px; transition: width .3s; }
        .tab-btn { border: none; background: transparent; padding: 10px 20px; font-weight: 600; font-size: .875rem; color: var(--text-muted); border-bottom: 2px solid transparent; cursor: pointer; }
        .tab-btn.active { color: var(--primary); border-bottom-color: var(--primary); }
    </style>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    <?php include '../includes/sidebar.php'; ?>

    <div class="content-wrapper">
        <div class="container-fluid px-0">

            <!-- Filter Bar -->
            <div class="premium-panel mb-4">
                <form method="GET" class="row g-3 align-items-end">
                    <input type="hidden" name="view" value="<?php echo $view; ?>">

                    <div class="col-auto">
                        <label class="form-label">View</label>
                        <div class="d-flex border rounded-3 overflow-hidden" style="border-color:#e2e8f0!important">
                            <a href="?view=student&date=<?php echo $filter_date; ?>&class_id=<?php echo $filter_class; ?>"
                               class="px-4 py-2 fw-semibold small text-decoration-none <?php echo $view==='student'?'bg-primary text-white':'text-muted bg-white'; ?>">
                                Students
                            </a>
                            <a href="?view=teacher&date=<?php echo $filter_date; ?>"
                               class="px-4 py-2 fw-semibold small text-decoration-none <?php echo $view==='teacher'?'bg-primary text-white':'text-muted bg-white'; ?>">
                                Teachers
                            </a>
                        </div>
                    </div>

                    <div class="col-auto">
                        <label class="form-label">Month</label>
                        <input type="month" name="date" class="form-control" value="<?php echo $filter_date; ?>">
                    </div>

                    <?php if ($view === 'student'): ?>
                    <div class="col-auto">
                        <label class="form-label">Class</label>
                        <select name="class_id" class="form-select">
                            <option value="0">All Classes</option>
                            <?php
                            $classes->data_seek(0);
                            while($c = $classes->fetch_assoc()):
                            ?>
                            <option value="<?php echo $c['id']; ?>" <?php echo $filter_class==$c['id']?'selected':''; ?>>
                                <?php echo htmlspecialchars($c['name'] . ' ' . $c['section']); ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <?php endif; ?>

                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary fw-bold px-4">
                            <i class="fas fa-filter me-2"></i>Apply Filter
                        </button>
                    </div>
                </form>
            </div>

            <!-- Results -->
            <div class="premium-panel">
                <div class="flat-header d-flex align-items-center justify-content-between">
                    <div>
                        <h5><?php echo $view === 'student' ? 'Student Attendance' : 'Teacher Attendance'; ?> — <?php echo $month_name; ?></h5>
                        <p>Attendance percentage based on recorded sessions this month</p>
                    </div>
                </div>

                <?php if ($view === 'student'): ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Enroll No</th>
                                <th>Class</th>
                                <th class="text-center">Present</th>
                                <th class="text-center">Absent</th>
                                <th style="min-width:150px">Attendance %</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $rows_found = false;
                            while($s = $student_data->fetch_assoc()):
                                $rows_found = true;
                                $pct = $s['total_sessions'] > 0
                                    ? round(($s['present_count'] / $s['total_sessions']) * 100)
                                    : 0;
                                $bar_color = $pct >= 75 ? '#10b981' : ($pct >= 50 ? '#f59e0b' : '#ef4444');
                                $name = $s['first_name'] ? htmlspecialchars($s['first_name'].' '.$s['last_name']) : htmlspecialchars($s['username']);
                            ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold small"><?php echo $name; ?></div>
                                </td>
                                <td class="small text-muted"><?php echo htmlspecialchars($s['Enroll_No'] ?: '—'); ?></td>
                                <td class="small"><?php echo htmlspecialchars($s['class_name'].' '.$s['section']); ?></td>
                                <td class="text-center">
                                    <span class="fw-bold text-success"><?php echo (int)$s['present_count']; ?></span>
                                </td>
                                <td class="text-center">
                                    <span class="fw-bold text-danger"><?php echo (int)$s['absent_count']; ?></span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="pct-bar flex-grow-1">
                                            <div class="pct-bar-fill" style="width:<?php echo $pct; ?>%;background:<?php echo $bar_color; ?>"></div>
                                        </div>
                                        <span class="fw-bold small" style="min-width:36px;color:<?php echo $bar_color; ?>"><?php echo $pct; ?>%</span>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                            <?php if(!$rows_found): ?>
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <i class="fas fa-user-slash d-block mb-3 text-muted" style="font-size:2rem;opacity:.3"></i>
                                    <span class="text-muted small">No student attendance data for <?php echo $month_name; ?></span>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php else: /* teacher view */ ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Teacher</th>
                                <th class="text-center">Days Marked Present</th>
                                <th class="text-center">Total Logged Days</th>
                                <th style="min-width:150px">Attendance %</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $rows_found = false;
                            while($t = $teacher_data->fetch_assoc()):
                                $rows_found = true;
                                $pct = $t['total_days'] > 0 ? round(($t['present_count'] / $t['total_days']) * 100) : 0;
                                $bar_color = $pct >= 75 ? '#10b981' : ($pct >= 50 ? '#f59e0b' : '#ef4444');
                                $name = $t['first_name'] ? htmlspecialchars($t['first_name'].' '.$t['last_name']) : htmlspecialchars($t['username']);
                            ?>
                            <tr>
                                <td class="fw-semibold small"><?php echo $name; ?></td>
                                <td class="text-center fw-bold text-success"><?php echo (int)$t['present_count']; ?></td>
                                <td class="text-center text-muted small"><?php echo (int)$t['total_days']; ?></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="pct-bar flex-grow-1">
                                            <div class="pct-bar-fill" style="width:<?php echo $pct; ?>%;background:<?php echo $bar_color; ?>"></div>
                                        </div>
                                        <span class="fw-bold small" style="min-width:36px;color:<?php echo $bar_color; ?>"><?php echo $pct; ?>%</span>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                            <?php if(!$rows_found): ?>
                            <tr>
                                <td colspan="4" class="text-center py-5">
                                    <i class="fas fa-chalkboard-teacher d-block mb-3 text-muted" style="font-size:2rem;opacity:.3"></i>
                                    <span class="text-muted small">No teacher attendance data for <?php echo $month_name; ?></span>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php include '../includes/scripts.php'; ?>
</body>
</html>
