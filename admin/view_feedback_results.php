<?php
session_start();
include '../includes/db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php"); exit();
}

$page_title    = 'Feedback Analytics';
$page_subtitle = 'Analyze teacher performance and student sentiment';

// Filter logic
$teacher_filter = isset($_GET['teacher_id']) ? (int)$_GET['teacher_id'] : 0;
$form_filter = isset($_GET['form_id']) ? (int)$_GET['form_id'] : 0;

// Fetch teachers for filter
$teachers_list = $conn->query(
    "SELECT u.id, u.username, p.first_name, p.last_name 
     FROM users u LEFT JOIN profile_info p ON u.id = p.user_id 
     WHERE u.role = 'teacher' ORDER BY u.username ASC"
);

// Fetch feedback data with averages
$query = "
    SELECT f.id as form_id, f.title, f.created_at, 
           u.username as teacher_user, p.first_name, p.last_name, 
           s.name as subject_name,
           COUNT(r.id) as total_responses,
           AVG(r.rating) as avg_rating
    FROM feedback_forms f
    JOIN users u ON f.teacher_id = u.id
    LEFT JOIN profile_info p ON u.id = p.user_id
    JOIN subjects s ON f.subject_id = s.id
    LEFT JOIN feedback_responses r ON f.id = r.form_id
    WHERE 1=1
";

if ($teacher_filter > 0) $query .= " AND f.teacher_id = $teacher_filter";
if ($form_filter > 0) $query .= " AND f.id = $form_filter";

$query .= " GROUP BY f.id ORDER BY f.created_at DESC";
$results = $conn->query($query);

// Fetch details for a specific form if selected
$details = null;
if ($form_filter > 0) {
    $details = $conn->query(
        "SELECT r.*, u.username as student_user 
         FROM feedback_responses r 
         JOIN users u ON r.student_id = u.id 
         WHERE r.form_id = $form_filter 
         ORDER BY r.created_at DESC"
    );
}

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
    <title>Feedback Analytics | Admin</title>
    <?php include '../includes/links.php'; ?>
    <link rel="stylesheet" href="../assets/index.css">
    <style>
        .analytics-card { transition: all 0.2s; border: 1px solid #eef2f6; }
        .analytics-card:hover { transform: translateY(-3px); box-shadow: 0 10px 25px rgba(0,0,0,0.05); }
        .rating-pill { font-size: 0.75rem; font-weight: 700; padding: 4px 12px; border-radius: 20px; }
        .comment-item { padding: 15px; border-bottom: 1px solid #f1f5f9; }
        .comment-item:last-child { border-bottom: none; }
    </style>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    <?php include '../includes/sidebar.php'; ?>

    <div class="content-wrapper">
        <div class="container-fluid px-0">
            
            <!-- Filter Bar -->
            <div class="premium-panel mb-4 py-3">
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-muted">FILTER BY TEACHER</label>
                        <select name="teacher_id" class="form-select border-0 bg-light" onchange="this.form.submit()">
                            <option value="0">All Teachers</option>
                            <?php while($t = $teachers_list->fetch_assoc()): ?>
                                <option value="<?php echo $t['id']; ?>" <?php echo $teacher_filter == $t['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($t['first_name'] ? $t['first_name'].' '.$t['last_name'] : $t['username']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <?php if ($teacher_filter > 0): ?>
                    <div class="col-md-2">
                         <a href="view_feedback_results.php" class="btn btn-light w-100 fw-bold border-0">Clear</a>
                    </div>
                    <?php endif; ?>
                </form>
            </div>

            <div class="row g-4">
                <div class="<?php echo $form_filter > 0 ? 'col-md-5' : 'col-12'; ?>">
                    <h5 class="fw-bold mb-3">Feedback Campaigns</h5>
                    <div class="row g-3">
                        <?php if ($results && $results->num_rows > 0): ?>
                            <?php while($r = $results->fetch_assoc()): ?>
                                <div class="<?php echo $form_filter > 0 ? 'col-12' : 'col-md-6 col-lg-4'; ?>">
                                    <div class="premium-panel analytics-card <?php echo $form_filter == $r['form_id'] ? 'border-primary' : ''; ?>">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <h6 class="fw-bold mb-1"><?php echo htmlspecialchars($r['title']); ?></h6>
                                                <div class="small text-muted">
                                                    <i class="fas fa-chalkboard-teacher me-1"></i> 
                                                    <?php echo htmlspecialchars($r['first_name'] ? $r['first_name'].' '.$r['last_name'] : $r['teacher_user']); ?>
                                                    <span class="mx-1">•</span>
                                                    <i class="fas fa-book me-1"></i> <?php echo htmlspecialchars($r['subject_name']); ?>
                                                </div>
                                            </div>
                                            <div class="text-end">
                                                <?php if ($r['total_responses'] > 0): ?>
                                                    <div class="badge <?php echo getRatingClass($r['avg_rating']); ?> rating-pill">
                                                        <?php echo number_format($r['avg_rating'], 1); ?> ★
                                                    </div>
                                                <?php else: ?>
                                                    <span class="badge bg-light text-muted border rating-pill text-uppercase" style="font-size:0.6rem">No Data</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                                            <span class="small text-muted fw-bold"><?php echo $r['total_responses']; ?> Responses</span>
                                            <a href="view_feedback_results.php?form_id=<?php echo $r['form_id']; ?>&teacher_id=<?php echo $teacher_filter; ?>" 
                                               class="btn btn-link btn-sm p-0 text-decoration-none fw-bold small">
                                                View Details <i class="fas fa-chevron-right ms-1"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="col-12 text-center py-5">
                                <img src="../assets/no-data.svg" alt="No data" style="width:120px;opacity:0.5;filter:grayscale(1)" class="mb-3">
                                <p class="text-muted small">No feedback campaigns found matching your criteria.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Detail View Panel -->
                <?php if ($details): ?>
                <div class="col-md-7">
                    <div class="premium-panel sticky-top" style="top: 100px;">
                        <div class="flat-header d-flex justify-content-between align-items-center">
                            <div>
                                <h5>Student Responses</h5>
                                <p>Individual ratings and qualitative feedback</p>
                            </div>
                            <a href="view_feedback_results.php?teacher_id=<?php echo $teacher_filter; ?>" class="btn-close"></a>
                        </div>
                        
                        <div class="response-list mt-4">
                            <?php if ($details && $details->num_rows > 0): ?>
                                <?php while($d = $details->fetch_assoc()): ?>
                                    <div class="comment-item">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="bg-light rounded-circle p-2 text-primary" style="width:32px;height:32px;display:flex;align-items:center;justify-content:center;font-size:0.8rem">
                                                    <i class="fas fa-user"></i>
                                                </div>
                                                <span class="small fw-bold">Student #<?php echo $d['student_id']; ?></span>
                                            </div>
                                            <div class="badge <?php echo getRatingClass($d['rating']); ?> rating-pill">
                                                <?php echo $d['rating']; ?> ★
                                            </div>
                                        </div>
                                        <p class="small text-muted mb-0 italic">
                                            <?php echo !empty($d['comments']) ? '"'.htmlspecialchars($d['comments']).'"' : '<span class="opacity-50">No written comment provided.</span>'; ?>
                                        </p>
                                        <div class="text-end mt-2">
                                            <span class="extra-small text-muted"><?php echo date('M d, H:i', strtotime($d['created_at'])); ?></span>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <div class="text-center py-5 text-muted">
                                    <i class="fas fa-inbox d-block mb-3 opacity-25" style="font-size:2rem"></i>
                                    No responses collected yet for this campaign.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

        </div>
    </div>
    <style> .extra-small { font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.05em; } </style>
    <?php include '../includes/scripts.php'; ?>
</body>
</html>
