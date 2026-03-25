<?php
session_start();
include '../includes/db_connection.php';

// Available to students
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../auth/login.php"); exit();
}

$student_id = (int) $_SESSION['user_id'];
$page_title    = 'My Attendance';
$page_subtitle = 'Monthly calendar view of your attendance record';

// Month/Year navigation
$month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('m');
$year  = isset($_GET['year'])  ? (int)$_GET['year']  : (int)date('Y');

// Clamp to reasonable range
if ($month < 1)  $month = 1;
if ($month > 12) $month = 12;
if ($year < 2020 || $year > 2030) $year = (int)date('Y');

$prev_month = $month - 1; $prev_year = $year;
if ($prev_month < 1) { $prev_month = 12; $prev_year--; }
$next_month = $month + 1; $next_year = $year;
if ($next_month > 12) { $next_month = 1; $next_year++; }

$month_name  = date('F', mktime(0, 0, 0, $month, 1, $year));
$days_in_month = (int)date('t', mktime(0, 0, 0, $month, 1, $year));
$first_day_of_week = (int)date('N', mktime(0, 0, 0, $month, 1, $year)); // 1=Mon,7=Sun

// Fetch attendance records for this student for the selected month
$from = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-01";
$to   = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-$days_in_month";

$stmt = $conn->prepare(
    "SELECT a.date, a.status, s.name as subject_name
     FROM attendance a
     JOIN subjects s ON a.subject_id = s.id
     WHERE a.student_id = ? AND a.date BETWEEN ? AND ?
     ORDER BY a.date ASC"
);
$stmt->bind_param("iss", $student_id, $from, $to);
$stmt->execute();
$rows = $stmt->get_result();

// Build lookup: date => [subject => status, ...]
$calendar_data = [];
while ($r = $rows->fetch_assoc()) {
    $day = (int)date('j', strtotime($r['date']));
    if (!isset($calendar_data[$day])) $calendar_data[$day] = [];
    $calendar_data[$day][] = ['subject' => $r['subject_name'], 'status' => $r['status']];
}

// Overall stats for this month
$total_records = 0; $present_count = 0;
foreach ($calendar_data as $day => $subjects) {
    foreach ($subjects as $rec) {
        $total_records++;
        if ($rec['status'] === 'Present') $present_count++;
    }
}
$absent_count   = $total_records - $present_count;
$percent        = $total_records > 0 ? round(($present_count / $total_records) * 100) : 0;

// Also fetch overall lifetime stats
$overall = $conn->prepare(
    "SELECT COUNT(*) as total, SUM(status='Present') as present_total FROM attendance WHERE student_id = ?"
);
$overall->bind_param("i", $student_id);
$overall->execute();
$ov = $overall->get_result()->fetch_assoc();
$ov_total   = (int)$ov['total'];
$ov_present = (int)$ov['present_total'];
$ov_percent = $ov_total > 0 ? round(($ov_present / $ov_total) * 100) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Attendance | SMS</title>
    <?php include '../includes/links.php'; ?>
    <link rel="stylesheet" href="../assets/index.css">
    <style>
        .cal-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 6px;
        }
        .cal-head {
            text-align: center;
            font-size: .7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: var(--text-muted);
            padding: 8px 0;
        }
        .cal-day {
            min-height: 80px;
            border-radius: 10px;
            padding: 8px;
            font-size: .8rem;
            border: 1.5px solid #f1f5f9;
            background: #fff;
            position: relative;
        }
        .cal-day.empty { background: transparent; border-color: transparent; }
        .cal-day.today { border-color: var(--primary); }
        .cal-day.all-present { background: rgba(16,185,129,.08); border-color: rgba(16,185,129,.3); }
        .cal-day.has-absent  { background: rgba(239,68,68,.07); border-color: rgba(239,68,68,.25); }
        .cal-day.no-data     { background: #f8fafc; border-color: #f1f5f9; }

        .cal-day-num {
            font-weight: 700;
            font-size: .85rem;
            margin-bottom: 4px;
        }
        .cal-day.today .cal-day-num { color: var(--primary); }

        .cal-subject-tag {
            display: inline-block;
            font-size: .6rem;
            font-weight: 700;
            border-radius: 4px;
            padding: 1px 5px;
            margin: 1px 1px 0 0;
            white-space: nowrap;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .tag-present { background: rgba(16,185,129,.15); color: #059669; }
        .tag-absent  { background: rgba(239,68,68,.13);  color: #dc2626; }

        .stat-mini {
            background: #fff;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 16px 20px;
            text-align: center;
        }
        .stat-mini .num { font-size: 1.75rem; font-weight: 800; line-height: 1; }
        .stat-mini .lbl { font-size: .7rem; font-weight: 600; text-transform: uppercase; letter-spacing: .06em; color: var(--text-muted); margin-top: 4px; }

        .pct-ring {
            width: 80px; height: 80px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.2rem; font-weight: 800;
        }
    </style>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    <?php include '../includes/sidebar.php'; ?>

    <div class="content-wrapper">
        <div class="container-fluid px-0">

            <!-- Overall Stats -->
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3">
                    <div class="stat-mini">
                        <div class="num text-primary"><?php echo $ov_percent; ?>%</div>
                        <div class="lbl">Overall Attendance</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-mini">
                        <div class="num text-success"><?php echo $ov_present; ?></div>
                        <div class="lbl">Total Present Records</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-mini">
                        <div class="num text-danger"><?php echo $ov_total - $ov_present; ?></div>
                        <div class="lbl">Total Absent Records</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-mini">
                        <div class="num <?php echo $ov_percent >= 75 ? 'text-success' : 'text-danger'; ?>">
                            <?php echo $ov_percent >= 75 ? '✓ Good' : '✗ Low'; ?>
                        </div>
                        <div class="lbl">Attendance Status</div>
                    </div>
                </div>
            </div>

            <!-- Calendar Panel -->
            <div class="premium-panel">
                <!-- Month Nav Header -->
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <a href="?month=<?php echo $prev_month; ?>&year=<?php echo $prev_year; ?>"
                       class="btn btn-light border px-3 py-2 fw-bold small">
                        <i class="fas fa-chevron-left me-1"></i> <?php echo date('M', mktime(0,0,0,$prev_month,1,$prev_year)); ?>
                    </a>

                    <div class="text-center">
                        <h5 class="fw-bold mb-0"><?php echo $month_name . ' ' . $year; ?></h5>
                        <div class="d-flex gap-3 justify-content-center mt-2">
                            <span class="small text-success fw-bold"><i class="fas fa-circle me-1" style="font-size:.5rem"></i><?php echo $present_count; ?> Present</span>
                            <span class="small text-danger fw-bold"><i class="fas fa-circle me-1" style="font-size:.5rem"></i><?php echo $absent_count; ?> Absent</span>
                            <span class="small text-muted fw-bold"><?php echo $percent; ?>% rate</span>
                        </div>
                    </div>

                    <a href="?month=<?php echo $next_month; ?>&year=<?php echo $next_year; ?>"
                       class="btn btn-light border px-3 py-2 fw-bold small">
                        <?php echo date('M', mktime(0,0,0,$next_month,1,$next_year)); ?> <i class="fas fa-chevron-right ms-1"></i>
                    </a>
                </div>

                <!-- Calendar Grid -->
                <div class="cal-grid mb-2">
                    <?php foreach(['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $d): ?>
                        <div class="cal-head"><?php echo $d; ?></div>
                    <?php endforeach; ?>

                    <?php
                    // Empty cells before first day (ISO: Mon=1)
                    for($i = 1; $i < $first_day_of_week; $i++) {
                        echo '<div class="cal-day empty"></div>';
                    }

                    $today_day  = (int)date('j');
                    $today_mo   = (int)date('m');
                    $today_yr   = (int)date('Y');

                    for($d = 1; $d <= $days_in_month; $d++) {
                        $is_today = ($d === $today_day && $month === $today_mo && $year === $today_yr);
                        $has_data = isset($calendar_data[$d]);

                        $has_absent  = false;
                        $all_present = false;
                        if ($has_data) {
                            $has_absent  = array_filter($calendar_data[$d], fn($x) => $x['status'] === 'Absent');
                            $all_present = !$has_absent;
                        }

                        $cls = 'cal-day';
                        if ($is_today) $cls .= ' today';
                        if (!$has_data) $cls .= ' no-data';
                        elseif ($has_absent) $cls .= ' has-absent';
                        else $cls .= ' all-present';

                        echo "<div class=\"$cls\">";
                        echo "<div class=\"cal-day-num\">$d</div>";
                        if ($has_data) {
                            foreach($calendar_data[$d] as $rec) {
                                $tag_cls = $rec['status'] === 'Present' ? 'tag-present' : 'tag-absent';
                                $subj = htmlspecialchars(substr($rec['subject'], 0, 8));
                                echo "<span class=\"cal-subject-tag $tag_cls\" title=\"{$rec['subject']}: {$rec['status']}\">$subj</span>";
                            }
                        }
                        echo "</div>";
                    }
                    ?>
                </div>

                <!-- Legend -->
                <div class="d-flex gap-4 pt-3 border-top mt-2">
                    <span class="small text-muted d-flex align-items-center gap-1">
                        <span style="width:12px;height:12px;border-radius:3px;background:rgba(16,185,129,.15);display:inline-block"></span> All Present
                    </span>
                    <span class="small text-muted d-flex align-items-center gap-1">
                        <span style="width:12px;height:12px;border-radius:3px;background:rgba(239,68,68,.13);display:inline-block"></span> Has Absent
                    </span>
                    <span class="small text-muted d-flex align-items-center gap-1">
                        <span style="width:12px;height:12px;border-radius:3px;background:#f8fafc;border:1px solid #f1f5f9;display:inline-block"></span> Not Recorded
                    </span>
                    <span class="small text-muted d-flex align-items-center gap-1">
                        <span style="width:12px;height:12px;border-radius:3px;border:1.5px solid var(--primary);display:inline-block"></span> Today
                    </span>
                </div>
            </div>
        </div>
    </div>
    <?php include '../includes/scripts.php'; ?>
</body>
</html>
