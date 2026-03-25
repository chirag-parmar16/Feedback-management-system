<?php
session_start();
include '../includes/db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../auth/login.php");
    exit();
}

$page_title = 'Academic Analytics';
$page_subtitle = 'Comprehensive overview of your attendance and examination performance.';
$student_id = (int) $_SESSION['user_id'];

// Attendance Stats — prepared statement
$stmt = $conn->prepare("SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) as present
    FROM attendance WHERE student_id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$attendance = $stmt->get_result()->fetch_assoc() ?? ['total' => 0, 'present' => 0];
$percent = ($attendance['total'] > 0) ? round(($attendance['present'] / $attendance['total']) * 100, 1) : 0;

// Recent Marks — prepared statement
$stmt = $conn->prepare("SELECT m.*, s.name as subject_name 
                       FROM marks m 
                       JOIN subjects s ON m.subject_id = s.id 
                       WHERE m.student_id = ? 
                       ORDER BY m.exam_date DESC");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$marks = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Performance | SMS</title>
    <?php include '../includes/links.php'; ?>
    <link rel="stylesheet" href="../assets/index.css">
    <style>
        .stats-strip { background: #ffffff; padding: 40px 0; border-bottom: 2px solid #f8fafc; }
        .progress-circle { width: 120px; height: 120px; border-radius: 50%; background: conic-gradient(var(--primary) calc(var(--percent) * 1%), #f1f5f9 0); display: flex; align-items: center; justify-content: center; position: relative; }
        .progress-circle::after { content: ''; position: absolute; width: 100px; height: 100px; background: #fff; border-radius: 50%; }
        .progress-circle span { position: relative; z-index: 1; font-size: 1.5rem; font-weight: 800; color: #0f172a; }
        .performance-table-section { background: #ffffff; padding: 40px 0; }
    </style>
</head>

<body>
    <?php include '../includes/navbar.php'; ?>
    <?php include '../includes/sidebar.php'; ?>

    <div class="content-wrapper">
        <div class="container py-4">
            
            <div class="stats-strip mb-5">
                <div class="row g-5 align-items-center">
                    <div class="col-md-6 border-end">
                        <div class="flat-header">
                            <h5>Attendance Standing</h5>
                            <p>Daily presence ratio for the current term.</p>
                        </div>
                        <div class="d-flex align-items-center mt-4">
                            <div class="progress-circle me-5" style="--percent: <?php echo $percent; ?>;">
                                <span><?php echo $percent; ?>%</span>
                            </div>
                            <div>
                                <h3 class="fw-bold mb-1 <?php echo ($percent >= 75) ? 'text-primary' : 'text-danger'; ?>">
                                    <?php echo ($percent >= 75) ? 'Academic Compliance' : 'Review Required'; ?>
                                </h3>
                                <p class="mb-0 text-muted small fw-bold text-uppercase tracking-wider">Target: 75.0% Minimum</p>
                                <div class="mt-3">
                                    <span class="small text-muted me-3">Present: <strong><?php echo (int)$attendance['present']; ?></strong></span>
                                    <span class="small text-muted">Total Days: <strong><?php echo (int)$attendance['total']; ?></strong></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="flat-header">
                            <h5>Cumulative Assessment</h5>
                            <p>Recent grading and performance markers.</p>
                        </div>
                        <div class="mt-4 p-4 bg-primary-soft rounded-4 d-flex align-items-center justify-content-between">
                            <div>
                                <p class="extra-small text-primary fw-bold mb-1">CURRENT STANDING</p>
                                <h1 class="display-3 fw-bold text-primary mb-0">
                                    <?php
                                    // Calculate average grade from marks
                                    $stmt2 = $conn->prepare("SELECT AVG((marks_obtained / total_marks) * 100) as avg_pct FROM marks WHERE student_id = ?");
                                    $stmt2->bind_param("i", $student_id);
                                    $stmt2->execute();
                                    $avg_pct = $stmt2->get_result()->fetch_assoc()['avg_pct'] ?? 0;
                                    if ($avg_pct >= 90) echo 'A+';
                                    elseif ($avg_pct >= 80) echo 'A';
                                    elseif ($avg_pct >= 70) echo 'B+';
                                    elseif ($avg_pct >= 60) echo 'B';
                                    elseif ($avg_pct >= 50) echo 'C';
                                    elseif ($avg_pct >= 40) echo 'D';
                                    else echo ($avg_pct > 0 ? 'F' : 'N/A');
                                    ?>
                                </h1>
                            </div>
                            <div class="text-end">
                                <p class="small text-muted mb-0 fw-bold">Based on <?php echo $marks->num_rows; ?> exam(s)</p>
                                <p class="small text-primary fw-bold mb-0">Avg: <?php echo round($avg_pct, 1); ?>%</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="performance-table-section">
                <div class="flat-header">
                    <h5>Examination History</h5>
                    <p>Detailed breakdown of subject-wise results.</p>
                </div>
                <div class="table-responsive mt-4">
                    <table class="table">
                        <thead class="text-muted small">
                            <tr>
                                <th>DATE</th>
                                <th>ACADEMIC SUBJECT</th>
                                <th>SCORE DATA</th>
                                <th>RATIO</th>
                                <th class="text-end">STATUS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($marks && $marks->num_rows > 0): ?>
                                <?php while($m = $marks->fetch_assoc()): 
                                    $p = round(($m['marks_obtained'] / $m['total_marks']) * 100, 1);
                                ?>
                                    <tr>
                                        <td class="small text-muted"><?php echo date('M d, Y', strtotime($m['exam_date'])); ?></td>
                                        <td class="fw-bold text-dark"><?php echo htmlspecialchars($m['subject_name']); ?></td>
                                        <td class="fw-bold text-primary"><?php echo $m['marks_obtained'] . ' / ' . $m['total_marks']; ?></td>
                                        <td class="small fw-bold"><?php echo $p; ?>%</td>
                                        <td class="text-end">
                                            <span class="badge rounded-pill <?php echo ($p >= 40) ? 'bg-success-soft text-success' : 'bg-danger-soft text-danger'; ?> px-3 small border-0">
                                                <?php echo ($p >= 40) ? 'CREDIT PASSED' : 'RETAKE REQUIRED'; ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="5" class="text-center py-5 text-muted small">No validated examination results are available in your profile yet.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <style> .extra-small { font-size: 0.65rem; letter-spacing: 0.05em; text-transform: uppercase; } </style>
    <?php include '../includes/scripts.php'; ?>
</body>
</html>
