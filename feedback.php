<?php
session_start();
include './includes/db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['user_id'];
$page_title = 'Academic Feedback Terminal';
$page_subtitle = 'Your anonymous insights help us improve teaching quality.';
$message = '';

if (isset($_POST['submit_feedback'])) {
    $form_id = $_POST['form_id'];
    $rating = $_POST['rating'];
    $comments = $_POST['comments'];

    $stmt = $conn->prepare("INSERT INTO feedback_responses (form_id, student_id, rating, comments) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiis", $form_id, $student_id, $rating, $comments);
    $stmt->execute();
    $message = "Your contribution has been successfully recorded. Thank you for your feedback!";
}

// Fetch active forms for the student - Fixed JOIN on profile_info
$active_forms = $conn->query("SELECT f.*, p.first_name, p.last_name, s.name as subject_name 
                               FROM feedback_forms f 
                               LEFT JOIN profile_info p ON f.teacher_id = p.user_id 
                               JOIN subjects s ON f.subject_id = s.id 
                               WHERE f.status = 'active' 
                               AND f.id NOT IN (SELECT form_id FROM feedback_responses WHERE student_id = $student_id)
                               ORDER BY f.created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Voice of Student | SMS</title>
    <?php include './includes/links.php'; ?>
    <link rel="stylesheet" href="index.css">
    <style>
        .feedback-item { background: #ffffff; padding: 32px 0; border-bottom: 2px solid #f8fafc; }
        .rating-chip { cursor: pointer; transition: all 0.2s; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px 20px; font-weight: 700; color: #64748b; }
        .form-check-input:checked + .rating-chip { background: var(--primary); color: #ffffff; border-color: var(--primary); box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3); }
        .form-check-input { display: none; }
        .form-control { background: #ffffff !important; border: 1px solid #e2e8f0 !important; }
    </style>
</head>

<body>
    <?php include './includes/navbar.php'; ?>
    <?php include './includes/sidebar.php'; ?>

    <div class="content-wrapper">
        <div class="container py-4">

            <?php if ($message): ?>
                <div class="alert bg-success-soft text-success border-0 small py-3 mb-5 fw-bold text-center"><?php echo $message; ?></div>
            <?php endif; ?>

            <div class="feedback-roll mt-4">
                <?php if($active_forms->num_rows > 0): ?>
                    <?php while($f = $active_forms->fetch_assoc()): ?>
                        <div class="feedback-item">
                            <div class="row align-items-start gx-5">
                                <div class="col-md-4">
                                    <div class="flat-header">
                                        <h5><?php echo htmlspecialchars($f['title']); ?></h5>
                                        <p class="fw-bold text-dark mt-2"><?php echo htmlspecialchars($f['first_name'] . ' ' . $f['last_name']); ?></p>
                                        <p class="extra-small text-primary fw-bold"><?php echo htmlspecialchars($f['subject_name']); ?></p>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <form method="POST">
                                        <input type="hidden" name="form_id" value="<?php echo $f['id']; ?>">
                                        
                                        <label class="form-label text-muted extra-small fw-bold mb-3">PERFORMANCE RATING</label>
                                        <div class="d-flex gap-3 mb-4">
                                            <?php for($i=1; $i<=5; $i++): ?>
                                                <div class="form-check p-0 m-0">
                                                    <input type="radio" name="rating" value="<?php echo $i; ?>" id="r<?php echo $f['id'].$i; ?>" class="form-check-input" required>
                                                    <label class="rating-chip" for="r<?php echo $f['id'].$i; ?>"><?php echo $i; ?></label>
                                                </div>
                                            <?php endfor; ?>
                                        </div>

                                        <div class="mb-4">
                                            <label class="form-label text-muted extra-small fw-bold">QUALITATIVE FEEDBACK</label>
                                            <textarea name="comments" class="form-control" rows="3" placeholder="Describe your learning experience..."></textarea>
                                        </div>
                                        
                                        <div class="text-end">
                                            <button type="submit" name="submit_feedback" class="btn btn-primary px-5 py-3 fw-bold rounded-pill">Transmit Feedback</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="text-center py-5 border rounded-4 bg-light">
                        <i class="fas fa-check-circle text-success mb-3 fs-1 opacity-25"></i>
                        <h5 class="fw-bold mb-1">Queue Clear</h5>
                        <p class="text-muted small mb-0">There are no pending feedback requests for your profile.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <style> .extra-small { font-size: 0.65rem; letter-spacing: 0.05em; text-transform: uppercase; } </style>
    <?php include './includes/scripts.php'; ?>
</body>
</html>
