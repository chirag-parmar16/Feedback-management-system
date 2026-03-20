<?php
session_start();
include './includes/db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$page_title = 'Classroom Architecture';
$page_subtitle = 'Define and manage the physical and logical divisions of the institution.';
$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_class'])) {
    $name = $_POST['name'];
    $section = $_POST['section'];
    $stmt = $conn->prepare("INSERT INTO classes (name, section) VALUES (?, ?)");
    $stmt->bind_param("ss", $name, $section);
    $stmt->execute();
    $message = "New academic class successfully initialized!";
}

$classes = $conn->query("SELECT * FROM classes ORDER BY name ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Classes | Admin</title>
    <?php include './includes/links.php'; ?>
    <link rel="stylesheet" href="index.css">
    <style>
        .class-form-panel { background: #ffffff; padding: 30px 0; border-bottom: 2px solid #f8fafc; }
        .form-control { background: #ffffff !important; border: 1px solid #e2e8f0 !important; }
    </style>
</head>
<body>
    <?php include './includes/navbar.php'; ?>
    <?php include './includes/sidebar.php'; ?>

    <div class="content-wrapper">
        <div class="container py-4">
            <?php if ($message): ?>
                <div class="alert bg-success-soft text-success border-0 small py-3 mb-4 fw-bold"><?php echo $message; ?></div>
            <?php endif; ?>
            <div class="row g-4">
                <!-- Creation Terminal -->
                <div class="col-md-4">
                    <div class="premium-panel">
                        <div class="flat-header">
                            <h5>Provision Class</h5>
                            <p>Initialize a new academic class stream.</p>
                        </div>
                        <form method="POST">
                            <div class="mb-4">
                                <label class="form-label text-muted small fw-bold">CLASS NOMENCLATURE</label>
                                <input type="text" name="name" class="form-control py-3" placeholder="e.g., Grade 10" required>
                            </div>
                            <div class="mb-4">
                                <label class="form-label text-muted small fw-bold">SECTION IDENTIFIER</label>
                                <input type="text" name="section" class="form-control py-3" placeholder="e.g., A" required>
                            </div>
                            <button type="submit" name="add_class" class="btn btn-primary w-100 py-3 fw-bold rounded-pill shadow-sm">Initialize Stream</button>
                        </form>
                    </div>
                </div>

                <!-- Active Registry -->
                <div class="col-md-8">
                    <div class="premium-panel">
                        <div class="flat-header">
                            <h5>Academic Stream Registry</h5>
                            <p>Active class configurations and institutional structures.</p>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4">CLASS NAME</th>
                                        <th>SECTION</th>
                                        <th class="text-end pe-4">ACTION</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if($classes->num_rows > 0): ?>
                                        <?php while($row = $classes->fetch_assoc()): ?>
                                            <tr>
                                                <td class="ps-4 fw-bold text-dark"><?php echo htmlspecialchars($row['name']); ?></td>
                                                <td><span class="badge border border-primary-soft text-primary px-3 py-2 rounded-pill"><?php echo htmlspecialchars($row['section']); ?></span></td>
                                                <td class="text-end pe-4">
                                                    <a href="backend/delete_class.php?id=<?php echo $row['id']; ?>" class="btn btn-light-danger btn-sm border-0 px-3" onclick="return confirm('Archive this class?')">Archive</a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="text-center py-5 text-muted small">No class divisions have been initialized.</td>
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

    <?php include './includes/scripts.php'; ?>
</body>
</html>
