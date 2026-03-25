<?php
session_start();
include '../includes/db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../auth/login.php");
    exit();
}

$assignment_id = (int) ($_GET['assignment_id'] ?? 0);
if (!$assignment_id) {
    header("Location: manage_assignments.php");
    exit();
}

// Fetch assignment details
$stmt = $conn->prepare("SELECT a.title, s.name as subject_name FROM assignments a JOIN subjects s ON a.subject_id = s.id WHERE a.id = ?");
$stmt->bind_param("i", $assignment_id);
$stmt->execute();
$assignment = $stmt->get_result()->fetch_assoc();

if (!$assignment) {
    header("Location: manage_assignments.php");
    exit();
}

$message = $_SESSION['message'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['message'], $_SESSION['error']);

// Fetch submissions
$stmt = $conn->prepare("SELECT asub.*, u.username as student_name, p.first_name, p.last_name 
                       FROM assignment_submissions asub 
                       JOIN users u ON asub.student_id = u.id 
                       LEFT JOIN profile_info p ON u.id = p.user_id
                       WHERE asub.assignment_id = ? 
                       ORDER BY asub.submitted_at DESC");
$stmt->bind_param("i", $assignment_id);
$stmt->execute();
$submissions = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Grade Submissions | SMS</title>
    <?php include '../includes/links.php'; ?>
    <link rel="stylesheet" href="../assets/index.css">
    <style>
        .submission-card { 
            background: #ffffff; 
            border: 1px solid #f1f5f9; 
            border-radius: 16px; 
            padding: 24px; 
            margin-bottom: 24px; 
            transition: all 0.3s ease;
        }
        .submission-card:hover { transform: translateY(-3px); box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05); }
        .status-badge { font-size: 0.65rem; letter-spacing: 0.05em; text-transform: uppercase; font-weight: 800; padding: 6px 12px; border-radius: 50px; }
        .status-pending { background: #fffbeb; color: #92400e; border: 1px solid #fde68a; }
        .status-graded { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
    </style>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    <?php include '../includes/sidebar.php'; ?>

    <div class="content-wrapper">
        <div class="container py-4">
            <div class="d-flex justify-content-between align-items-center mb-5">
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2">
                            <li class="breadcrumb-item small fw-bold text-uppercase"><a href="manage_assignments.php">Assignments</a></li>
                            <li class="breadcrumb-item small fw-bold text-uppercase active">Grading</li>
                        </ol>
                    </nav>
                    <h3 class="fw-bold mb-0"><?php echo htmlspecialchars($assignment['title']); ?></h3>
                    <span class="badge bg-primary-soft text-primary small px-3 mt-2"><?php echo htmlspecialchars($assignment['subject_name']); ?></span>
                </div>
            </div>

            <?php if ($message): ?>
                <div class="alert bg-success-soft text-success border-0 small py-3 mb-4 fw-bold shadow-sm d-flex align-items-center">
                    <i class="fas fa-check-circle me-2"></i> <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-lg-12">
                    <?php if($submissions->num_rows > 0): ?>
                        <div class="row g-4">
                            <?php while ($row = $submissions->fetch_assoc()): ?>
                                <div class="col-md-6">
                                    <div class="submission-card">
                                        <div class="d-flex justify-content-between align-items-start mb-4">
                                            <div>
                                                <h6 class="fw-bold mb-1 text-dark">
                                                    <?php echo htmlspecialchars($row['first_name'] ? ($row['first_name'] . ' ' . $row['last_name']) : $row['student_name']); ?>
                                                </h6>
                                                <span class="text-muted extra-small fw-bold">SUBMITTED ON <?php echo date('M d, Y • H:i', strtotime($row['submitted_at'])); ?></span>
                                            </div>
                                            <span class="status-badge <?php echo $row['marks'] !== null ? 'status-graded' : 'status-pending'; ?>">
                                                <?php echo $row['marks'] !== null ? 'Graded' : 'Pending'; ?>
                                            </span>
                                        </div>

                                        <div class="bg-light p-3 rounded-3 mb-4 border">
                                            <p class="small text-muted mb-2 fw-bold text-uppercase extra-small">Submission Content</p>
                                            <p class="small mb-2"><?php echo nl2br(htmlspecialchars($row['submission_text'] ?: 'No text commentary provided.')); ?></p>
                                            <?php if($row['file_path']): ?>
                                                <a href="../<?php echo $row['file_path']; ?>" target="_blank" class="btn btn-white btn-sm border fw-bold small rounded-3 px-3 shadow-sm">
                                                    <i class="far fa-file-pdf me-2 text-danger"></i> View Attachment
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted extra-small">No files attached.</span>
                                            <?php endif; ?>
                                        </div>

                                        <form action="../backend/assignment_logic.php" method="POST" class="row g-3">
                                            <input type="hidden" name="submission_id" value="<?php echo $row['id']; ?>">
                                            <input type="hidden" name="assignment_id" value="<?php echo $assignment_id; ?>">
                                            <input type="hidden" name="grade_submission_action" value="1">
                                            
                                            <div class="col-4">
                                                <label class="form-label text-muted extra-small fw-bold">MARKS</label>
                                                <input type="number" name="marks" class="form-control form-control-sm py-2 fw-bold text-primary" value="<?php echo $row['marks']; ?>" placeholder="0" required>
                                            </div>
                                            <div class="col-8">
                                                <label class="form-label text-muted extra-small fw-bold">REMARKS</label>
                                                <input type="text" name="remarks" class="form-control form-control-sm py-2" value="<?php echo htmlspecialchars($row['remarks']); ?>" placeholder="Constructive feedback...">
                                            </div>
                                            <div class="col-12 mt-3">
                                                <button type="submit" class="btn btn-primary w-100 btn-sm py-2 fw-bold rounded-pill">Save Evaluation</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5 bg-white border rounded-4 shadow-sm">
                            <i class="fas fa-inbox display-4 text-muted opacity-25 mb-3"></i>
                            <h5 class="fw-bold">No Submissions Yet</h5>
                            <p class="text-muted small">Once students start submitting their educational tasks, they will appear here for evaluation.</p>
                            <a href="manage_assignments.php" class="btn btn-light border btn-sm fw-bold px-4 rounded-pill">Return to Overview</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <style> .extra-small { font-size: 0.65rem; } </style>
    <?php include '../includes/scripts.php'; ?>
</body>
</html>
