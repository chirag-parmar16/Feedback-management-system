<?php
session_start();
include '../includes/db_connection.php';
include '../includes/session_context.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php"); exit();
}

$sessions = $conn->query("SELECT * FROM academic_years ORDER BY start_date DESC");

// Set active session logic
if (isset($_GET['activate_id'])) {
    $id = (int)$_GET['activate_id'];
    $conn->query("UPDATE academic_years SET is_active = 0"); // Deactivate all
    $conn->query("UPDATE academic_years SET is_active = 1 WHERE id = $id");
    unset($_SESSION['academic_year_id']); // Force refresh from helper
    header("Location: manage_sessions.php");
    exit();
}

// Add new session logic
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_session_action'])) {
    $name = $_POST['session_name'];
    $start = $_POST['start_date'];
    $end = $_POST['end_date'];
    $stmt = $conn->prepare("INSERT INTO academic_years (session_name, start_date, end_date) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $start, $end);
    $stmt->execute();
    header("Location: manage_sessions.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Academic Sessions | Admin</title>
    <?php include '../includes/links.php'; ?>
    <link rel="stylesheet" href="../assets/index.css">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    <?php include '../includes/sidebar.php'; ?>
    <div class="content-wrapper">
        <div class="container py-4">
            <h3 class="fw-bold mb-4">Manage Academic Sessions</h3>
            
            <div class="row g-4">
                <div class="col-md-12 text-end mb-4">
                    <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#newSessionModal">
                        <i class="fas fa-plus me-2"></i> Create New Session
                    </button>
                </div>
                <div class="col-md-12">
                    <div class="user-list-section">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Session</th>
                                    <th>Duration</th>
                                    <th>Status</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($s = $sessions->fetch_assoc()): ?>
                                    <tr>
                                        <td><strong><?php echo $s['session_name']; ?></strong></td>
                                        <td class="small text-muted">
                                            <?php echo date('M Y', strtotime($s['start_date'])); ?> - <?php echo date('M Y', strtotime($s['end_date'])); ?>
                                        </td>
                                        <td>
                                            <?php if($s['is_active']): ?>
                                                <span class="badge bg-success rounded-pill px-3">ACTIVE</span>
                                            <?php else: ?>
                                                <span class="badge bg-light text-muted border rounded-pill px-3">INACTIVE</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">
                                            <?php if(!$s['is_active']): ?>
                                                <a href="?activate_id=<?php echo $s['id']; ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3">Set Active</a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4 p-3 bg-light rounded text-muted small">
                        <i class="fas fa-info-circle me-2"></i> Only <b>one</b> session can be active at a time. All new attendance, marks, and fees will be automatically linked to the active session.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- New Session Modal -->
    <div class="modal fade" id="newSessionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content rounded-4 shadow border-0">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Academic Session Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body py-4">
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Session Name</label>
                            <input type="text" name="session_name" class="form-control" placeholder="e.g. 2026-27" required>
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
                        <input type="hidden" name="add_session_action" value="1">
                        <button type="submit" class="btn btn-primary w-100 rounded-pill py-2">Create Session</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
