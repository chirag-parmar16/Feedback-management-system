<?php
session_start();
include '../includes/db_connection.php';
include '../includes/session_context.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php"); exit();
}

$ay = getCurrentAcademicYear($conn);
$exams = $conn->query("SELECT * FROM exams WHERE academic_year_id = " . $ay['id']);
$subjects = $conn->query("SELECT * FROM subjects");

// Create Exam
if (isset($_POST['create_exam_action'])) {
    $name = $_POST['exam_name'];
    $start = $_POST['start_date'];
    $end = $_POST['end_date'];
    $stmt = $conn->prepare("INSERT INTO exams (academic_year_id, exam_name, start_date, end_date) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $ay['id'], $name, $start, $end);
    $stmt->execute();
    header("Location: manage_exams.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Exams & Results | Admin</title>
    <?php include '../includes/links.php'; ?>
    <link rel="stylesheet" href="../assets/index.css">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    <?php include '../includes/sidebar.php'; ?>
    <div class="content-wrapper">
        <div class="container py-4">
            <h3 class="fw-bold mb-4">Academic Examinations (<?php echo $ay['name']; ?>)</h3>
            
            <div class="row g-4">
                <div class="col-md-12 text-end mb-4">
                    <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#newExamModal">
                        <i class="fas fa-calendar-plus me-2"></i> Schedule New Exam
                    </button>
                </div>
                <div class="col-md-12">
                    <div class="user-list-section">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Exam Name</th>
                                    <th>Dates</th>
                                    <th>Status</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($e = $exams->fetch_assoc()): ?>
                                    <tr>
                                        <td><strong><?php echo $e['exam_name']; ?></strong></td>
                                        <td class="small text-muted"><?php echo $e['start_date']; ?> to <?php echo $e['end_date']; ?></td>
                                        <td>
                                            <span class="badge bg-light text-primary border rounded-pill px-3"><?php echo strtoupper($e['status']); ?></span>
                                        </td>
                                        <td class="text-end">
                                            <?php if ($e['status'] === 'result_declared'): ?>
                                                <a href="../backend/exam_logic.php?process_id=<?php echo $e['id']; ?>" class="btn btn-sm btn-outline-info rounded-pill px-3" onclick="return confirm('Re-calculate results for this exam? This will update existing records.')">Re-process Results</a>
                                            <?php else: ?>
                                                <a href="../backend/exam_logic.php?process_id=<?php echo $e['id']; ?>" class="btn btn-sm btn-outline-success rounded-pill px-3" onclick="return confirm('Calculate results for this exam based on current marks?')">Process Results</a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- New Exam Modal -->
    <div class="modal fade" id="newExamModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content rounded-4 shadow border-0">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Examination Schedule</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body py-4">
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Exam Title</label>
                            <input type="text" name="exam_name" class="form-control" placeholder="e.g. Mid-Term 2025" required>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Start Date</label>
                                <input type="date" name="start_date" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">End Date</label>
                                <input type="date" name="end_date" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <input type="hidden" name="create_exam_action" value="1">
                        <button type="submit" class="btn btn-primary w-100 rounded-pill py-2">Schedule Exam</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
