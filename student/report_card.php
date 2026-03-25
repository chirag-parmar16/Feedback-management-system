<?php
session_start();
include '../includes/db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php"); exit();
}

$student_id = $_SESSION['user_id'];

// Get Student Profile for Sidebar
$profile_res = $conn->query("SELECT p.*, u.username FROM profile_info p JOIN users u ON p.user_id = u.id WHERE p.user_id = $student_id");
$profile = $profile_res->fetch_assoc();

// Get all declared exams for the student to populate the dropdown
$all_exams_res = $conn->query("SELECT e.id, e.exam_name FROM results r JOIN exams e ON r.exam_id = e.id WHERE r.student_id = $student_id ORDER BY e.end_date DESC");
$all_exams = [];
while($ex = $all_exams_res->fetch_assoc()) $all_exams[] = $ex;

// Get selected exam ID from GET or default to the latest one
$selected_exam_id = isset($_GET['exam_id']) ? (int)$_GET['exam_id'] : ($all_exams[0]['id'] ?? 0);

// Get specific result data
$result_res = $conn->query("SELECT r.*, e.exam_name, e.end_date as exam_date, e.academic_year_id FROM results r JOIN exams e ON r.exam_id = e.id WHERE r.student_id = $student_id AND e.id = $selected_exam_id");
$current_result = $result_res->fetch_assoc();

if ($current_result) {
    // Get Subject-wise Marks for the selected result
    $marks_res = $conn->query("SELECT m.*, s.name as subject_name 
                           FROM marks m 
                           JOIN subjects s ON m.subject_id = s.id 
                           WHERE m.student_id = $student_id 
                           AND m.exam_id = $selected_exam_id");
    $subject_marks = [];
    while($row = $marks_res->fetch_assoc()) $subject_marks[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Academic Report Cards | SMS</title>
    <?php include '../includes/links.php'; ?>
    <link rel="stylesheet" href="../assets/index.css">
    <style>
        :root { --sms-teal: #008080; --sms-bg: #f5f7fb; }
        body { background: var(--sms-bg); }
        .result-container { width: 98%; margin: auto; padding-left: 15px; padding-right: 15px; }
        .search-panel { background: #fff; border-radius: 8px; padding: 20px; border-bottom: 4px solid #eee; }
        .result-header { border-left: 5px solid #28a745; background: #fff; padding: 15px 25px; display: flex; align-items: center; gap: 10px; margin-bottom: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .result-table { background: #fff; border-radius: 0; }
        .result-table th { background: #f8f9fa; color: #555; text-transform: uppercase; font-size: 0.8rem; border-color: #eee; }
        .result-table td { vertical-align: middle; border-color: #eee; font-size: 0.9rem; }
        .sidebar-card { background: #fff; border-radius: 8px; padding: 0; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .sidebar-item { display: flex; justify-content: space-between; padding: 10px 20px; border-bottom: 1px solid #f1f1f1; }
        .sidebar-item:last-child { border-bottom: none; }
        .sidebar-label { color: #666; font-size: 0.85rem; }
        .sidebar-value { font-weight: 600; color: #0d6efd; text-transform: uppercase; }
        .btn-download { background: #008a73; color: #fff; border: none; padding: 10px 20px; border-radius: 4px; font-weight: 600; }
        .btn-download:hover { background: #006b5a; color: #fff; }
        .pass-banner { background: #e8f5e9; color: #2e7d32; padding: 12px 20px; border-radius: 4px; display: flex; align-items: center; gap: 10px; font-weight: 500; }
        .grade-pill { padding: 4px 10px; border-radius: 4px; font-weight: bold; font-size: 0.85rem; }
        .grade-A { background: #E8F5E9; color: #2E7D32; }
        .grade-B { background: #E3F2FD; color: #1565C0; }
        .grade-C { background: #FFF3E0; color: #EF6C00; }
        .grade-D { background: #FBE9E7; color: #D84315; }
        .grade-F { background: #FFEBEE; color: #C62828; }
        @media print {
            .navbar, .sidebar, .search-panel, .btn-download, .content-wrapper::before { display: none !important; }
            .content-wrapper { margin: 0 !important; padding: 0 !important; }
            .result-container { width: 100% !important; max-width: 100% !important; }
        }
    </style>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    <?php include '../includes/sidebar.php'; ?>

    <div class="content-wrapper">
        <div class="container-fluid py-4 result-container">
            
            <!-- Search Panel -->
            <div class="search-panel mb-4 shadow-sm border-0 bg-white" style="border-radius: 12px;">
                <div class="row align-items-center p-2">
                    <div class="col-auto">
                        <span class="text-primary fw-bold"><i class="fas fa-search me-2"></i>SELECT EXAM</span>
                    </div>
                    <div class="col-md-6">
                        <div class="input-group">
                            <select class="form-select border-0 bg-light" onchange="location.href='report_card.php?exam_id=' + this.value" style="border-radius: 8px;">
                                <?php foreach ($all_exams as $ex): ?>
                                    <option value="<?php echo $ex['id']; ?>" <?php echo ($ex['id'] == $selected_exam_id) ? 'selected' : ''; ?>>
                                        <?php echo $ex['exam_name']; ?> (2025-26)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($current_result): ?>
                <div class="report-card-main-card bg-white shadow-sm p-4" style="border-radius: 15px; border-top: 5px solid #4e73df;">
                    <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
                        <div>
                            <h4 class="mb-0 fw-bold text-dark text-uppercase"><?php echo $current_result['exam_name']; ?></h4>
                            <p class="text-muted small mb-0">Academic Session: 2025-2026</p>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-<?php echo ($current_result['result_status'] == 'pass' ? 'success' : 'danger'); ?> fs-6 p-2 px-3">
                                <?php echo strtoupper($current_result['result_status']); ?>
                            </span>
                        </div>
                    </div>

                    <div class="row g-4">
                        <!-- Marks Table -->
                        <div class="col-lg-9">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle border-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="py-3 px-4 border-0">SUBJECT</th>
                                            <th class="py-3 text-center border-0">MAX MARKS</th>
                                            <th class="py-3 text-center border-0">OBTAINED</th>
                                            <th class="py-3 text-center border-0">PERCENTAGE</th>
                                            <th class="py-3 text-center border-0">GRADE</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $total_max = 0;
                                        $total_obtained = 0;
                                        foreach ($subject_marks as $m): 
                                            $total_max += $m['total_marks'];
                                            $total_obtained += $m['marks_obtained'];
                                            $perc = ($m['marks_obtained'] / $m['total_marks']) * 100;
                                            $g = $m['grade'] ?? 'F';
                                        ?>
                                            <tr>
                                                <td class="py-3 px-4 fw-bold text-dark"><?php echo $m['subject_name']; ?></td>
                                                <td class="py-3 text-center text-secondary"><?php echo number_format($m['total_marks'], 0); ?></td>
                                                <td class="py-3 text-center fw-bold"><?php echo number_format($m['marks_obtained'], 0); ?></td>
                                                <td class="py-3 text-center">
                                                    <div class="d-flex align-items-center justify-content-center">
                                                        <span class="me-2"><?php echo number_format($perc, 1); ?>%</span>
                                                        <div class="progress w-25" style="height: 6px;">
                                                            <div class="progress-bar <?php echo $perc >= 40 ? 'bg-success' : 'bg-danger'; ?>" role="progressbar" style="width: <?php echo $perc; ?>%"></div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="py-3 text-center">
                                                    <span class="badge rounded-pill bg-opacity-10 bg-<?php 
                                                        if($g == 'A+' || $g == 'A') echo 'success text-success';
                                                        elseif($g == 'B' || $g == 'C') echo 'primary text-primary';
                                                        elseif($g == 'D') echo 'warning text-warning';
                                                        else echo 'danger text-danger';
                                                    ?> fw-bold p-2 px-3" style="min-width: 45px;">
                                                        <?php echo $g; ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot class="bg-light-subtle">
                                        <tr class="fw-bold">
                                            <td class="py-3 px-4 text-primary">GRAND TOTAL</td>
                                            <td class="py-3 text-center text-primary"><?php echo number_format($total_max, 0); ?></td>
                                            <td class="py-3 text-center text-primary"><?php echo number_format($total_obtained, 0); ?></td>
                                            <td class="py-3 text-center text-primary"><?php echo number_format(($total_obtained/$total_max)*100, 2); ?>%</td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <!-- Sidebar Summary -->
                        <div class="col-lg-3">
                            <div class="student-info-card p-4 border shadow-sm mb-4" style="border-radius: 12px; background: #f8f9fc;">
                                <h6 class="text-uppercase text-secondary fw-bold mb-4 small ls-1">Student Profile</h6>
                                <div class="d-flex align-items-center mb-4">
                                    <div class="avatar-circle me-3 bg-primary text-white d-flex align-items-center justify-content-center fw-bold fs-4" style="width: 50px; height: 50px; border-radius: 50%;">
                                        <?php echo strtoupper($profile['first_name'][0]); ?>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark"><?php echo strtoupper($profile['first_name'] . ' ' . $profile['last_name']); ?></div>
                                        <div class="text-muted small">Roll No: <?php echo $profile['Enroll_No'] ?? 'N/A'; ?></div>
                                    </div>
                                </div>
                                <hr>
                                <div class="mb-3">
                                    <div class="text-muted small">Result Declaration</div>
                                    <div class="fw-bold text-dark"><?php echo date('F d, Y', strtotime($current_result['declared_at'])); ?></div>
                                </div>
                                <div class="mb-3">
                                    <div class="text-muted small">Overall Attendance</div>
                                    <div class="fw-bold text-dark">92.5%</div>
                                </div>
                                <div class="p-3 bg-white border border-dashed rounded-3 mt-4">
                                    <div class="row g-0">
                                        <div class="col-6 border-end text-center">
                                            <div class="text-muted small">Marks</div>
                                            <div class="h5 mb-0 fw-bold text-primary"><?php echo number_format($total_obtained, 0); ?></div>
                                        </div>
                                        <div class="col-6 text-center">
                                            <div class="text-muted small">Grade</div>
                                            <div class="h5 mb-0 fw-bold text-primary">
                                                <?php 
                                                    $overall_percentage = ($total_max > 0) ? ($total_obtained / $total_max) * 100 : 0;
                                                    if ($overall_percentage >= 90) echo 'A+';
                                                    elseif ($overall_percentage >= 80) echo 'A';
                                                    elseif ($overall_percentage >= 70) echo 'B';
                                                    elseif ($overall_percentage >= 60) echo 'C';
                                                    elseif ($overall_percentage >= 40) echo 'D';
                                                    else echo 'F';
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button class="btn btn-outline-secondary p-2 border-0 small">
                                    <i class="fas fa-share-alt me-2"></i> Share with Parents
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-info border-0 shadow-sm p-4" style="border-radius: 12px;">
                    <i class="fas fa-info-circle me-2"></i> No academic results found for the selected examination.
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
