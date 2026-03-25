<?php
session_start();
include '../includes/db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php"); exit();
}

$search = $_GET['search'] ?? '';
$query = "SELECT p.*, sf.total_amount, fs.fee_name, u.username, prof.first_name, prof.last_name, r.receipt_no 
          FROM payments p
          JOIN student_fees sf ON p.student_fee_id = sf.id
          JOIN fee_structures fs ON sf.fee_structure_id = fs.id
          JOIN users u ON sf.student_id = u.id
          LEFT JOIN profile_info prof ON u.id = prof.user_id
          LEFT JOIN receipts r ON p.id = r.payment_id";

if ($search) {
    $search = $conn->real_escape_string($search);
    $query .= " WHERE u.username LIKE '%$search%' OR r.receipt_no LIKE '%$search%' OR prof.first_name LIKE '%$search%'";
}

$query .= " ORDER BY p.payment_date DESC";
$payments = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment History | Admin</title>
    <?php include '../includes/links.php'; ?>
    <link rel="stylesheet" href="../assets/index.css">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    <?php include '../includes/sidebar.php'; ?>
    <div class="content-wrapper">
        <div class="container py-4">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h3 class="fw-bold mb-0">Payment Received Log</h3>
                    <p class="text-muted small mb-0">Track all incoming fee transactions</p>
                </div>
                <form action="" method="GET" class="d-flex gap-2">
                    <div class="input-group input-group-sm border rounded-pill px-2 bg-white shadow-sm">
                        <span class="input-group-text bg-transparent border-0 text-muted"><i class="fas fa-search"></i></span>
                        <input type="text" name="search" class="form-control border-0 bg-transparent" placeholder="Student or Receipt#" value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                </form>
            </div>

            <div class="user-list-section shadow-sm border-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Student</th>
                                <th>Fee Details</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Date & Time</th>
                                <th class="text-end">Receipt #</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($payments && $payments->num_rows > 0): ?>
                                <?php while($p = $payments->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="bg-primary-soft rounded-circle d-flex align-items-center justify-content-center fw-bold text-primary" style="width:32px;height:32px;font-size:12px">
                                                    <?php echo strtoupper(substr($p['username'], 0, 1)); ?>
                                                </div>
                                                <div>
                                                    <div class="fw-bold small"><?php echo htmlspecialchars($p['username']); ?></div>
                                                    <div class="extra-small text-muted"><?php echo htmlspecialchars($p['first_name'] . ' ' . $p['last_name']); ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="small fw-bold"><?php echo htmlspecialchars($p['fee_name']); ?></div>
                                            <div class="extra-small text-muted">Total: ₹<?php echo number_format($p['total_amount'], 2); ?></div>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-success">₹<?php echo number_format($p['amount_paid'], 2); ?></div>
                                        </td>
                                        <td>
                                            <span class="badge <?php echo $p['payment_method'] == 'online' ? 'bg-info-soft text-info' : 'bg-warning-soft text-warning'; ?> rounded-pill px-2 small">
                                                <?php echo strtoupper($p['payment_method']); ?>
                                            </span>
                                        </td>
                                        <td class="small text-muted">
                                            <?php echo date('M d, Y', strtotime($p['payment_date'])); ?><br>
                                            <span class="extra-small opacity-75"><?php echo date('h:i A', strtotime($p['payment_date'])); ?></span>
                                        </td>
                                        <td class="text-end">
                                            <span class="badge bg-light text-dark border rounded-pill px-3 font-monospace small">
                                                <?php echo $p['receipt_no'] ?: 'N/A'; ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="fas fa-receipt display-4 opacity-25 mb-3 d-block"></i>
                                        No payment records found.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
