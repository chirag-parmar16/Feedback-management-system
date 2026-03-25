<?php
session_start();
include '../includes/db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php"); exit();
}

$student_id = $_SESSION['user_id'];
$results = $conn->query("SELECT r.*, e.exam_name, e.end_date as exam_date FROM results r JOIN exams e ON r.exam_id = e.id WHERE r.student_id = $student_id ORDER BY e.end_date DESC");
$has_results = ($results->num_rows > 0);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Report Card | Student</title>
    <?php include '../includes/links.php'; ?>
    <link rel="stylesheet" href="../assets/index.css">
    <style>
        .report-card { border: 2px solid #333; padding: 40px; background: #fff; max-width: 800px; margin: auto; }
        .grade-a { color: #2E7D32; font-weight: 900; }
        .grade-f { color: #C62828; font-weight: 900; }
    </style>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    <?php include '../includes/sidebar.php'; ?>
    <div class="content-wrapper">
        <div class="container py-5">
            <h3 class="fw-bold mb-4 text-center">Academic Report Cards</h3>

            <?php while($r = $results->fetch_assoc()): ?>
                <div class="report-card mb-5 shadow">
                    <div class="text-center mb-4">
                        <h4 class="fw-bold text-uppercase"><?php echo $r['exam_name']; ?> Report</h4>
                        <p class="text-muted">Issued on: <?php echo date('M d, Y'); ?></p>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-6"><strong>Student Name:</strong> <?php echo $_SESSION['username']; ?></div>
                        <div class="col-6 text-end"><strong>Result:</strong> <span class="text-uppercase fw-bold"><?php echo $r['result_status']; ?></span></div>
                    </div>

                    <table class="table table-bordered">
                        <thead class="bg-light">
                            <tr>
                                <th>Assessment Criteria</th>
                                <th class="text-center">Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Aggregated Marks Obtained</td>
                                <td class="text-center fw-bold"><?php echo $r['total_marks_obtained']; ?> / <?php echo $r['total_max_marks']; ?></td>
                            </tr>
                            <tr>
                                <td>Percentage Calculation</td>
                                <td class="text-center fw-bold text-primary"><?php echo $r['percentage']; ?>%</td>
                            </tr>
                            <tr>
                                <td>Final Letter Grade</td>
                                <td class="text-center fw-bold <?php echo ($r['grade'] == 'F') ? 'grade-f' : 'grade-a'; ?>" style="font-size: 1.5rem;">
                                    <?php echo $r['grade']; ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="mt-4 pt-4 border-top small text-muted text-center">
                        This is a computer-generated report card and does not require a physical signature for digital verification.
                    </div>
                </div>
            <?php endwhile; ?>

            <?php if (!$has_results): ?>
                <div class="premium-panel text-center py-5 border-dashed">
                    <div class="mb-4">
                        <i class="fas fa-file-invoice text-muted display-1 opacity-25"></i>
                    </div>
                    <h4 class="fw-bold text-dark">No Academic Reports Published</h4>
                    <p class="text-muted mb-4 mx-auto" style="max-width: 500px;">
                        It appears that your academic results for the current session haven't been compiled or published yet by the administration. 
                        <br><br>
                        Once your teachers finalize grading and the administrative review is complete, your digital report card will be automatically generated and appear here.
                    </p>
                    <div class="d-flex justify-content-center gap-3">
                        <a href="dashboard.php" class="btn btn-light px-4 border small fw-bold">Back to Dashboard</a>
                        <a href="my_performance.php" class="btn btn-primary px-4 small fw-bold">View Subject Marks</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
