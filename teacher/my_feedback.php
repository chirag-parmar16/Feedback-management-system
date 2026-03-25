<?php
session_start();
include '../includes/db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../auth/login.php"); exit();
}

$teacher_id = (int) $_SESSION['user_id'];
$page_title    = 'My Feedback';
$page_subtitle = 'View your academic ratings and constructive student feedback';

// Aggregate stats for the teacher
$stats_query = "
    SELECT COUNT(r.id) as total_responses, AVG(r.rating) as avg_rating
    FROM feedback_responses r
    JOIN feedback_forms f ON r.form_id = f.id
    WHERE f.teacher_id = $teacher_id AND f.visibility_to_teacher = 1
";
$stats_res = $conn->query($stats_query);
$stats = $stats_res->fetch_assoc();

// List of visible campaigns and comments
$query = "
    SELECT f.title, f.created_at, s.name as subject_name,
           r.rating, r.comments, r.created_at as response_date
    FROM feedback_responses r
    JOIN feedback_forms f ON r.form_id = f.id
    JOIN subjects s ON f.subject_id = s.id
    WHERE f.teacher_id = $teacher_id AND f.visibility_to_teacher = 1
    ORDER BY r.created_at DESC
";
$results = $conn->query($query);

function getRatingClass($rating) {
    if ($rating >= 4.5) return 'bg-success';
    if ($rating >= 3.5) return 'bg-info';
    if ($rating >= 2.5) return 'bg-warning text-dark';
    return 'bg-danger';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Feedback | Teacher</title>
    <?php include '../includes/links.php'; ?>
    <link rel="stylesheet" href="../assets/index.css">
    <style>
        .stats-hero { background: var(--primary); color: white; border-radius: 24px; padding: 40px; margin-bottom: 30px; }
        .stats-hero h1 { font-size: 3.5rem; font-weight: 800; letter-spacing: -0.05em; margin: 10px 0; }
        .comment-card { border-radius: 16px; padding: 24px; border: 1px solid #f1f5f9; background: white; margin-bottom: 20px; transition: 0.2s; }
        .comment-card:hover { border-color: var(--primary-soft); transform: translateY(-2px); }
    </style>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    <?php include '../includes/sidebar.php'; ?>

    <div class="content-wrapper">
        <div class="container py-4">

            <!-- Hero Stats Section -->
            <div class="stats-hero d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-uppercase small fw-bold opacity-75">Your Career Rating</span>
                    <h1><?php echo number_format($stats['avg_rating'] ?? 0, 1); ?> ★</h1>
                    <p class="mb-0 opacity-75">Based on <?php echo $stats['total_responses']; ?> student reviews</p>
                </div>
                <div class="d-none d-md-block">
                    <i class="fas fa-star-half-alt fa-5x opacity-25"></i>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <h5 class="fw-bold mb-0">Recent Student Feedback</h5>
                        <span class="small text-muted fw-bold"><?php echo $results->num_rows; ?> TOTAL REVIEWS</span>
                    </div>

                    <?php if ($results && $results->num_rows > 0): ?>
                        <div class="row">
                            <?php while($r = $results->fetch_assoc()): ?>
                                <div class="col-md-6 col-lg-4">
                                    <div class="comment-card h-100 d-flex flex-column">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <span class="badge bg-primary-soft text-primary px-3 small"><?php echo htmlspecialchars($r['subject_name']); ?></span>
                                            <div class="badge <?php echo getRatingClass($r['rating']); ?> rounded-pill px-3 py-2" style="font-size:0.7rem">
                                                <?php echo $r['rating']; ?> / 5
                                            </div>
                                        </div>
                                        <h6 class="fw-bold mb-3" style="line-height:1.4"><?php echo htmlspecialchars($r['title']); ?></h6>
                                        <div class="flex-grow-1 border-top pt-3 mt-auto">
                                            <p class="small text-muted mb-0 italic" style="line-height:1.6">
                                                <?php echo !empty($r['comments']) ? '"'.htmlspecialchars($r['comments']).'"' : '<span class="opacity-50">No written comment provided.</span>'; ?>
                                            </p>
                                        </div>
                                        <div class="text-end mt-3">
                                            <span class="extra-small text-muted"><?php echo date('M d, Y', strtotime($r['response_date'])); ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5 border rounded-4 bg-light">
                            <i class="fas fa-comments text-muted opacity-25 d-block mb-3" style="font-size:3rem"></i>
                            <h6 class="text-muted fw-bold">No Visible Feedback</h6>
                            <p class="text-muted small">Once students submit reviews and the admin enables visibility, you'll see them here.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
    <style> .extra-small { font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.05em; } </style>
    <?php include '../includes/scripts.php'; ?>
</body>
</html>
