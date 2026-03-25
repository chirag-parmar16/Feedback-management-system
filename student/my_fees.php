<?php
session_start();
include '../includes/db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php"); exit();
}

$student_id = $_SESSION['user_id'];

// Get all fees for this student
$fees_res = $conn->query("SELECT sf.*, fs.fee_name, fs.description 
                          FROM student_fees sf 
                          JOIN fee_structures fs ON sf.fee_structure_id = fs.id 
                          WHERE sf.student_id = $student_id
                          ORDER BY sf.status DESC, sf.due_date ASC");

// Get recent payments
$payments_res = $conn->query("SELECT p.*, fs.fee_name, r.receipt_no 
                               FROM payments p 
                               JOIN student_fees sf ON p.student_fee_id = sf.id 
                               JOIN fee_structures fs ON sf.fee_structure_id = fs.id 
                               LEFT JOIN receipts r ON p.id = r.payment_id
                               WHERE sf.student_id = $student_id
                               ORDER BY p.payment_date DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Fees | Student</title>
    <?php include '../includes/links.php'; ?>
    <link rel="stylesheet" href="../assets/index.css">
    <style>
        .fee-card { background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; padding: 20px; height: 100%; border-top: 4px solid var(--primary); }
    </style>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    <?php include '../includes/sidebar.php'; ?>
    <div class="content-wrapper">
        <div class="container py-4">
            <h3 class="fw-bold mb-4">Financial Dashboard</h3>
            
            <?php if(isset($_SESSION['message'])): ?>
                <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm border-0 mb-4" role="alert">
                    <i class="fas fa-check-circle me-2"></i> <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if(isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm border-0 mb-4" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <div class="row g-4 mb-5">
                <div class="col-md-7">
                    <h5 class="fw-bold text-muted mb-3">Pending & Active Fees</h5>
                    <div class="row g-3">
                        <?php while($f = $fees_res->fetch_assoc()): ?>
                            <div class="col-12">
                                <div class="fee-card">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="fw-bold mb-1"><?php echo $f['fee_name']; ?></h6>
                                            <p class="small text-muted mb-3"><?php echo $f['description']; ?></p>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge rounded-pill px-3 py-2 mb-2 d-inline-block <?php echo $f['status'] == 'paid' ? 'bg-success' : 'bg-danger'; ?>">
                                                <?php echo strtoupper($f['status']); ?>
                                            </span>
                                            <?php if($f['status'] !== 'paid'): ?>
                                                <br>
                                                <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 py-1 fw-bold pay-trigger" 
                                                        data-bs-toggle="modal" data-bs-target="#payModal" 
                                                        data-id="<?php echo $f['id']; ?>" 
                                                        data-name="<?php echo $f['fee_name']; ?>" 
                                                        data-balance="<?php echo ($f['total_amount'] - $f['paid_amount']); ?>"
                                                        style="font-size: 0.75rem;">Pay Now</button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-end">
                                        <div>
                                            <small class="text-muted d-block">Due Date</small>
                                            <span class="fw-bold"><?php echo date('M d, Y', strtotime($f['due_date'])); ?></span>
                                        </div>
                                        <div class="text-end">
                                            <small class="text-muted d-block">Outstanding Balance</small>
                                            <h4 class="fw-bold text-dark mb-0">₹<?php echo number_format($f['total_amount'] - $f['paid_amount'], 2); ?></h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>

                <div class="col-md-5">
                    <h5 class="fw-bold text-muted mb-3">Recent Payment History</h5>
                    <div class="premium-panel p-0">
                        <table class="table mb-0">
                            <thead class="small text-muted">
                                <tr>
                                    <th class="ps-4">Date</th>
                                    <th>Fee Head</th>
                                    <th class="text-end pe-4">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($p = $payments_res->fetch_assoc()): ?>
                                    <tr>
                                        <td class="ps-4 small"><?php echo date('d M', strtotime($p['payment_date'])); ?></td>
                                        <td class="small fw-bold"><?php echo $p['fee_name']; ?><br><small class="text-muted"><?php echo $p['receipt_no']; ?></small></td>
                                        <td class="text-end pe-4 fw-bold text-success">₹<?php echo number_format($p['amount_paid'], 2); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Modal -->
    <div class="modal fade" id="payModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Process Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="../backend/fee_logic.php" method="POST">
                    <div class="modal-body py-4">
                        <div class="text-center mb-4">
                            <h4 id="modalFeeName" class="fw-bold mb-1"></h4>
                            <p class="text-muted small">Online Secure Payment</p>
                        </div>
                        <div class="bg-light p-3 rounded-4 mb-4 text-center">
                            <small class="text-muted d-block mb-1">Total Balance Due</small>
                            <h3 class="fw-bold text-danger mb-0">₹<span id="modalBalance"></span></h3>
                        </div>
                        
                        <input type="hidden" name="student_fee_id" id="modalFeeId">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Amount to Pay</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">₹</span>
                                <input type="hidden" name="student_pay_action" value="1">
                                <input type="number" step="0.01" name="amount" id="modalAmount" class="form-control border-start-0" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="submit" class="btn btn-primary w-100 rounded-pill py-3 fw-bold">Proceed to Pay Now</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('.pay-trigger').forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('modalFeeName').innerText = this.dataset.name;
                document.getElementById('modalBalance').innerText = parseFloat(this.dataset.balance).toLocaleString();
                document.getElementById('modalAmount').value = this.dataset.balance;
                document.getElementById('modalAmount').max = this.dataset.balance;
                document.getElementById('modalFeeId').value = this.dataset.id;
            });
        });
    </script>
</body>
</html>
