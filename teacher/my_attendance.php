<?php
session_start();
include '../includes/db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../auth/login.php"); exit();
}

$teacher_id = (int) $_SESSION['user_id'];
$page_title = 'My Attendance';
$page_subtitle = 'Mark your daily presence and view your history';

// ── POST: Mark today's attendance ──────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_own'])) {
    // Ensure UNIQUE key exists on teacher_attendance
    $conn->query("ALTER IGNORE TABLE teacher_attendance ADD UNIQUE KEY IF NOT EXISTS uq_teacher_att (teacher_id, date)");

    $date   = date('Y-m-d');
    $status = 'Present';
    $stmt = $conn->prepare(
        "INSERT INTO teacher_attendance (teacher_id, date, status) VALUES (?, ?, ?)
         ON DUPLICATE KEY UPDATE status = VALUES(status)"
    );
    $stmt->bind_param("iss", $teacher_id, $date, $status);
    $stmt->execute();

    $_SESSION['toast'] = ['message' => 'Attendance marked for today — ' . date('d M Y') . '.', 'type' => 'success'];
    // POST-Redirect-GET — prevents duplicate on browser refresh
    header("Location: my_attendance.php"); exit();
}

// ── GET: Pull last 60 days + summary stats ─────────────────────────────────
$stmt = $conn->prepare(
    "SELECT * FROM teacher_attendance WHERE teacher_id = ? ORDER BY date DESC LIMIT 60"
);
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$history = $stmt->get_result();

// Stats
$stmt2 = $conn->prepare(
    "SELECT COUNT(*) as total, SUM(status='Present') as present
     FROM teacher_attendance WHERE teacher_id = ?"
);
$stmt2->bind_param("i", $teacher_id);
$stmt2->execute();
$stats = $stmt2->get_result()->fetch_assoc();
$total   = (int)($stats['total']   ?? 0);
$present = (int)($stats['present'] ?? 0);
$absent  = $total - $present;
$pct     = $total > 0 ? round(($present / $total) * 100) : 0;

// Has today already been marked?
$today_marked = false;
$chk = $conn->prepare("SELECT id FROM teacher_attendance WHERE teacher_id = ? AND date = CURDATE()");
$chk->bind_param("i", $teacher_id);
$chk->execute();
$today_marked = $chk->get_result()->num_rows > 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Attendance | SMS</title>
    <?php include '../includes/links.php'; ?>
    <link rel="stylesheet" href="../assets/index.css">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    <?php include '../includes/sidebar.php'; ?>

    <div class="content-wrapper">
        <div class="container-fluid px-0">

            <div class="row g-3">
                <!-- ── Mark Attendance Card ────────────────────────────── -->
                <div class="col-md-4">
                    <div class="premium-panel text-center">
                        <?php if ($today_marked): ?>
                            <div style="width:60px;height:60px;background:rgba(16,185,129,.12);border-radius:16px;display:flex;align-items:center;justify-content:center;margin:0 auto 16px">
                                <i class="fas fa-check-circle text-success" style="font-size:1.8rem"></i>
                            </div>
                            <h5 class="fw-bold mb-1">You're Marked!</h5>
                            <p class="text-muted small mb-3">Attendance recorded for today<br>
                                <strong><?php echo date('l, d M Y'); ?></strong>
                            </p>
                            <span class="badge bg-success-soft px-4 py-2 fw-bold" style="font-size:.85rem">Present Today ✓</span>
                        <?php else: ?>
                            <div style="width:60px;height:60px;background:var(--primary-soft);border-radius:16px;display:flex;align-items:center;justify-content:center;margin:0 auto 16px">
                                <i class="fas fa-user-check" style="font-size:1.8rem;color:var(--primary)"></i>
                            </div>
                            <h5 class="fw-bold mb-1">Mark Today's Attendance</h5>
                            <p class="text-muted small mb-3"><?php echo date('l, d M Y'); ?></p>
                            <form method="POST">
                                <button type="submit" name="mark_own"
                                        class="btn btn-primary w-100 fw-bold"
                                        data-no-loading="1">
                                    <i class="fas fa-check me-2"></i>I Am Present Today
                                </button>
                            </form>
                        <?php endif; ?>

                        <!-- Stats Row -->
                        <div class="row g-2 mt-4 pt-3 border-top">
                            <div class="col-4">
                                <div class="fw-bold" style="font-size:1.3rem;color:var(--primary)"><?php echo $pct; ?>%</div>
                                <div class="extra-small text-muted">Rate</div>
                            </div>
                            <div class="col-4">
                                <div class="fw-bold text-success" style="font-size:1.3rem"><?php echo $present; ?></div>
                                <div class="extra-small text-muted">Present</div>
                            </div>
                            <div class="col-4">
                                <div class="fw-bold text-danger" style="font-size:1.3rem"><?php echo $absent; ?></div>
                                <div class="extra-small text-muted">Absent</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── History Table ───────────────────────────────────── -->
                <div class="col-md-8">
                    <div class="premium-panel">
                        <div class="flat-header">
                            <h5>Attendance History</h5>
                            <p>Last 60 days of logged attendance</p>
                        </div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Day</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($history->num_rows > 0): ?>
                                        <?php while($h = $history->fetch_assoc()): ?>
                                            <tr>
                                                <td class="fw-semibold"><?php echo date('d M Y', strtotime($h['date'])); ?></td>
                                                <td class="small text-muted"><?php echo date('l', strtotime($h['date'])); ?></td>
                                                <td>
                                                    <?php if($h['status'] === 'Present'): ?>
                                                        <span class="badge bg-success-soft px-3 py-2">✓ Present</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger-soft px-3 py-2">✗ Absent</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="text-center py-5">
                                                <i class="fas fa-calendar-times d-block mb-3 text-muted" style="font-size:2rem;opacity:.3"></i>
                                                <span class="text-muted small">No attendance records yet. Mark today's attendance to get started.</span>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include '../includes/scripts.php'; ?>
</body>
</html>
