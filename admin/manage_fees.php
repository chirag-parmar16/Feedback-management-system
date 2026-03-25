<?php
session_start();
include '../includes/db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php"); exit();
}

$classes = $conn->query("SELECT * FROM classes");
$fee_structures = $conn->query("SELECT fs.*, c.name as class_name, c.section FROM fee_structures fs JOIN classes c ON fs.class_id = c.id");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Fees | Admin</title>
    <?php include '../includes/links.php'; ?>
    <link rel="stylesheet" href="../assets/index.css">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    <?php include '../includes/sidebar.php'; ?>
    <div class="content-wrapper">
        <div class="container py-4">
            <div class="row">
                <div class="col-md-12 text-end mb-4">
                    <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#newFeeModal">
                        <i class="fas fa-plus me-2"></i> Define New Fee
                    </button>
                </div>
                <div class="col-md-12">
                    <div class="user-list-section">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Fee Name</th>
                                    <th>Class</th>
                                    <th>Amount</th>
                                    <th>Created</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($fs = $fee_structures->fetch_assoc()): ?>
                                    <tr>
                                        <td><strong><?php echo $fs['fee_name']; ?></strong></td>
                                        <td><?php echo $fs['class_name']; ?></td>
                                        <td class="text-primary fw-bold">₹<?php echo number_format($fs['amount'], 2); ?></td>
                                        <td class="small text-muted"><?php echo date('M d, Y', strtotime($fs['created_at'])); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- New Fee Modal -->
    <div class="modal fade" id="newFeeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content rounded-4 shadow border-0">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold">Define New Fee Structure</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="../backend/fee_logic.php" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Target Class</label>
                            <select name="class_id" class="form-select" required>
                                <?php 
                                $classes->data_seek(0);
                                while($c = $classes->fetch_assoc()): ?>
                                    <option value="<?php echo $c['id']; ?>"><?php echo $c['name']; ?> <?php echo $c['section']; ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Fee Name</label>
                            <input type="text" name="fee_name" class="form-control" placeholder="e.g. Tuition Fee Q1" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Amount (₹)</label>
                            <input type="number" step="0.01" name="amount" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Description (Optional)</label>
                            <textarea name="description" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <input type="hidden" name="create_structure_action" value="1">
                        <button type="submit" class="btn btn-primary w-100 rounded-pill py-2">Create Fee Head</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
