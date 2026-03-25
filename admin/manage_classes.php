<?php
session_start();
include '../includes/db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
 exit();
}

$page_title    = 'Classes';
$page_subtitle = 'Set up and manage class sections';
$message = ''; $error = '';

if (isset($_SESSION['message'])) { $message = $_SESSION['message']; unset($_SESSION['message']); }
if (isset($_SESSION['error']))   { $error   = $_SESSION['error'];   unset($_SESSION['error']); }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_class'])) {
    $name    = htmlspecialchars(trim($_POST['name']));
    $section = htmlspecialchars(trim($_POST['section']));

    if (empty($name) || empty($section)) {
        $error = "Both Class Name and Section are required.";
    } else {
        $stmt = $conn->prepare("INSERT INTO classes (name, section) VALUES (?, ?)");
        $stmt->bind_param("ss", $name, $section);
        $stmt->execute();
        $_SESSION['message'] = "Class \"$name ($section)\" has been added.";
        header("Location: manage_classes.php"); exit();
    }
}

// Classes with enrollment count
$classes = $conn->query(
    "SELECT c.*, COUNT(se.student_id) as enrolled_count 
     FROM classes c 
     LEFT JOIN student_enrollment se ON c.id = se.class_id 
     GROUP BY c.id 
     ORDER BY c.name ASC"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Classes | Admin</title>
    <?php include '../includes/links.php'; ?>
    <link rel="stylesheet" href="../assets/index.css">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    <?php include '../includes/sidebar.php'; ?>

    <div class="content-wrapper">
        <div class="container-fluid px-0">

            <?php if ($message): ?>
                <div class="alert bg-success-soft text-success fw-semibold"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert bg-danger-soft text-danger fw-semibold"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <div class="row g-3 align-items-start">
                <!-- ── Add New Class ───────────────────────────────────── -->
                <div class="col-md-4">
                    <div class="premium-panel">
                        <div class="flat-header">
                            <h5>Add New Class</h5>
                            <p>Create a new class section</p>
                        </div>
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Class Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control"
                                       placeholder="e.g. Grade 10" required
                                       value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                            </div>
                            <div class="mb-4">
                                <label class="form-label">Section <span class="text-danger">*</span></label>
                                <input type="text" name="section" class="form-control"
                                       placeholder="e.g. A"
                                       maxlength="5" required
                                       value="<?php echo htmlspecialchars($_POST['section'] ?? ''); ?>">
                            </div>
                            <button type="submit" name="add_class" class="btn btn-primary w-100 fw-bold">
                                <i class="fas fa-plus me-2"></i>Add Class
                            </button>
                        </form>
                    </div>
                </div>

                <!-- ── All Classes ─────────────────────────────────────── -->
                <div class="col-md-8">
                    <div class="premium-panel">
                        <div class="flat-header d-flex align-items-center justify-content-between">
                            <div>
                                <h5>All Classes</h5>
                                <p>Active class sections and enrollment numbers</p>
                            </div>
                            <span class="badge bg-primary-soft"><?php echo $classes ? $classes->num_rows : 0; ?> total</span>
                        </div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Class Name</th>
                                        <th>Section</th>
                                        <th class="text-center">Students Enrolled</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if($classes && $classes->num_rows > 0): ?>
                                        <?php while($row = $classes->fetch_assoc()): ?>
                                            <tr>
                                                <td class="fw-semibold"><?php echo htmlspecialchars($row['name']); ?></td>
                                                <td>
                                                    <span class="badge bg-primary-soft px-3 py-2">
                                                        <?php echo htmlspecialchars($row['section']); ?>
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <?php $cnt = (int)$row['enrolled_count']; ?>
                                                    <span class="fw-bold <?php echo $cnt > 0 ? 'text-success' : 'text-muted'; ?>">
                                                        <?php echo $cnt; ?>
                                                    </span>
                                                    <span class="text-muted small"> student<?php echo $cnt !== 1 ? 's' : ''; ?></span>
                                                </td>
                                                <td class="text-end">
                                                    <?php if($row['enrolled_count'] > 0): ?>
                                                        <span class="small text-muted fst-italic" title="Remove all enrollments first">Has students</span>
                                                    <?php else: ?>
                                                        <a href="backend/delete_class.php?id=<?php echo $row['id']; ?>"
                                                           class="btn btn-sm border px-3 fw-semibold small"
                                                           style="color:var(--danger);border-color:rgba(239,68,68,0.3)!important"
                                                           onclick="return confirm('Remove class <?php echo htmlspecialchars(addslashes($row['name'] . ' ' . $row['section'])); ?>? This cannot be undone.')">
                                                            Remove
                                                        </a>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center py-5">
                                                <i class="fas fa-layer-group text-muted mb-3 d-block" style="font-size:2rem;opacity:.3"></i>
                                                <span class="text-muted small">No classes yet. Add your first class using the form.</span>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include '../includes/scripts.php'; ?>
</body>
</html>
