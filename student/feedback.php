<?php
session_start();
include '../includes/db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../auth/login.php");
    exit();
}

$student_id = (int) $_SESSION['user_id'];
$page_title = 'Academic Feedback Terminal';
$message = '';

if (isset($_POST['submit_feedback_action'])) {
    $form_id  = (int) $_POST['form_id'];
    $rating   = (int) $_POST['rating'];
    $comments = trim($_POST['comments']);

    // Validate rating range
    if ($rating < 1 || $rating > 5) {
        $message = "Invalid rating value.";
    } else {
        // Check this student hasn't already submitted for this form
        $chk = $conn->prepare("SELECT COUNT(*) as cnt FROM feedback_responses WHERE form_id = ? AND student_id = ?");
        $chk->bind_param("ii", $form_id, $student_id);
        $chk->execute();
        if ($chk->get_result()->fetch_assoc()['cnt'] > 0) {
            $message = "You have already submitted feedback for this form.";
        } else {
            $stmt = $conn->prepare("INSERT INTO feedback_responses (form_id, student_id, rating, comments) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiis", $form_id, $student_id, $rating, $comments);
            if ($stmt->execute()) {
                $_SESSION['message'] = "Your contribution has been successfully recorded. Thank you for your feedback!";
                header("Location: feedback.php");
                exit();
            } else {
                $message = "Database Error: " . $stmt->error;
            }
        }
    }
}

$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']);

// Fetch active forms for the student — prepared statement
$stmt = $conn->prepare("SELECT f.*, p.first_name, p.last_name, s.name as subject_name 
                               FROM feedback_forms f 
                               JOIN subjects s ON f.subject_id = s.id 
                               JOIN student_enrollment e ON e.student_id = ?
                               JOIN timetables t ON t.class_id = e.class_id AND t.subject_id = f.subject_id
                               LEFT JOIN profile_info p ON f.teacher_id = p.user_id 
                               WHERE f.status = 'active' 
                               AND f.id NOT IN (SELECT form_id FROM feedback_responses WHERE student_id = ?)
                               GROUP BY f.id
                               ORDER BY f.created_at DESC");
$stmt->bind_param("ii", $student_id, $student_id);
$stmt->execute();
$active_forms = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Voice of Student | SMS</title>
    <?php include '../includes/links.php'; ?>
    <link rel="stylesheet" href="../assets/index.css">
    <style>
        .feedback-item { background: #ffffff; padding: 32px 0; border-bottom: 2px solid #f8fafc; }
        .rating-chip { cursor: pointer; transition: all 0.2s; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px 20px; font-weight: 700; color: #64748b; }
        .form-check-input:checked + .rating-chip { background: var(--primary); color: #ffffff; border-color: var(--primary); box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3); }
        .form-check-input { display: none; }
        .form-control { background: #ffffff !important; border: 1px solid #e2e8f0 !important; }
    </style>
</head>

<body>
    <?php include '../includes/navbar.php'; ?>
    <?php include '../includes/sidebar.php'; ?>

    <div class="content-wrapper">
        <div class="container py-4">
            <?php if ($message): ?>
                <div class="toast-stack">
                    <div class="toast-item toast-success shadow-lg">
                        <i class="fas fa-check-circle text-accent h5 mb-0"></i>
                        <div class="toast-text fw-bold"><?php echo htmlspecialchars($message); ?></div>
                        <button type="button" class="btn-close ms-2" data-bs-dismiss="alert"></button>
                    </div>
                </div>
                <script>
                    setTimeout(() => {
                        document.querySelector('.toast-item').classList.add('removing');
                        setTimeout(() => document.querySelector('.toast-stack').remove(), 300);
                    }, 4000);
                </script>
            <?php endif; ?>

            <div class="row g-4 mt-2">
                <div class="col-md-7">
                    <h5 class="fw-bold text-dark mb-4">Pending Requests</h5>
                    <?php if($active_forms->num_rows > 0): ?>
                        <div class="row g-3">
                            <?php while($f = $active_forms->fetch_assoc()): ?>
                                <div class="col-12">
                                    <div class="premium-panel border p-3 d-flex justify-content-between align-items-center rounded-4">
                                        <div>
                                            <h6 class="fw-bold mb-1"><?php echo htmlspecialchars($f['title']); ?></h6>
                                            <p class="extra-small text-muted mb-0"><?php echo htmlspecialchars($f['first_name'] . ' ' . $f['last_name']); ?> • <?php echo htmlspecialchars($f['subject_name']); ?></p>
                                        </div>
                                        <button class="btn btn-primary btn-sm rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#feedbackModal<?php echo $f['id']; ?>">
                                            Provide Feedback
                                        </button>
                                    </div>
                                </div>

                                <!-- Feedback Modal -->
                                <div class="modal fade" id="feedbackModal<?php echo $f['id']; ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content rounded-4 shadow border-0">
                                            <div class="modal-header border-0 pb-0">
                                                <h5 class="modal-title fw-bold">Performance Appraisal</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form method="POST">
                                                <div class="modal-body py-4">
                                                    <input type="hidden" name="form_id" value="<?php echo $f['id']; ?>">
                                                    <p class="small text-muted mb-4">Rating for <b><?php echo htmlspecialchars($f['subject_name']); ?></b> by <b><?php echo htmlspecialchars($f['first_name'] . ' ' . $f['last_name']); ?></b></p>
                                                    
                                                    <label class="form-label small fw-bold text-muted">STAR RATING</label>
                                                    <div class="d-flex gap-3 mb-4">
                                                        <?php for($i=1; $i<=5; $i++): ?>
                                                            <div class="form-check p-0 m-0">
                                                                <input type="radio" name="rating" value="<?php echo $i; ?>" id="r<?php echo $f['id'].$i; ?>" class="form-check-input" required>
                                                                <label class="rating-chip" for="r<?php echo $f['id'].$i; ?>"><?php echo $i; ?></label>
                                                            </div>
                                                        <?php endfor; ?>
                                                    </div>

                                                    <label class="form-label small fw-bold text-muted">COMMENTS</label>
                                                    <textarea name="comments" class="form-control" rows="3" placeholder="Any specific suggestions?"></textarea>
                                                </div>
                                                <div class="modal-footer border-0 pt-0">
                                                    <input type="hidden" name="submit_feedback_action" value="1">
                                                    <button type="submit" class="btn btn-primary w-100 rounded-pill py-2">Submit Feedback</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5 border rounded-4 bg-light shadow-sm">
                            <i class="fas fa-check-circle text-success mb-3 fs-1 opacity-25"></i>
                            <h6 class="fw-bold">All caught up!</h6>
                            <p class="text-muted extra-small mb-0">No pending feedback requests found.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="col-md-5">
                    <h5 class="fw-bold text-dark mb-4">Submission History</h5>
                    <div class="user-list-section border rounded-4 bg-white p-3 shadow-sm">
                        <?php
                        $hist = $conn->prepare("SELECT r.*, f.title, p.first_name, p.last_name 
                                               FROM feedback_responses r 
                                               JOIN feedback_forms f ON r.form_id = f.id 
                                               LEFT JOIN profile_info p ON f.teacher_id = p.user_id 
                                               WHERE r.student_id = ? 
                                               ORDER BY r.created_at DESC LIMIT 10");
                        $hist->bind_param("i", $student_id);
                        $hist->execute();
                        $history = $hist->get_result();

                        if($history->num_rows > 0): ?>
                            <table class="table table-sm extra-small">
                                <thead>
                                    <tr>
                                        <th>Form</th>
                                        <th>Rating</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($h = $history->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($h['title']); ?></td>
                                            <td><span class="badge bg-warning text-dark"><?php echo $h['rating']; ?> ★</span></td>
                                            <td class="text-muted"><?php echo date('d M', strtotime($h['created_at'])); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p class="text-muted small text-center py-4">No history available yet.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <style> .extra-small { font-size: 0.65rem; letter-spacing: 0.05em; text-transform: uppercase; } </style>
    <?php include '../includes/scripts.php'; ?>
</body>
</html>
