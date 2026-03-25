<?php
session_start();
include '../includes/db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

$edit_mode     = false;
$page_title    = isset($_GET['edit_id']) ? 'Edit User' : 'Add New User';
$page_subtitle = isset($_GET['edit_id']) ? 'Update account details and permissions.' : 'Create an admin, teacher, or student account.';
$user_data = ['username' => '', 'email' => '', 'role' => '', 'first_name' => '', 'last_name' => '', 'Enroll_No' => '', 'phone' => ''];

if (isset($_GET['edit_id'])) {
    $edit_mode = true;
    $edit_id = $_GET['edit_id'];
    $stmt = $conn->prepare("SELECT u.*, p.first_name, p.last_name, p.Enroll_No, p.mobile_no as phone FROM users u LEFT JOIN profile_info p ON u.id = p.user_id WHERE u.id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $user_data = $result->fetch_assoc();
    }
}

// Fetch classes for dropdown
$classes_res = $conn->query("SELECT id, name, section FROM classes ORDER BY name ASC");
$all_classes = [];
while ($c = $classes_res->fetch_assoc()) {
    $all_classes[] = $c;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $edit_mode ? 'Edit User' : 'Add User'; ?> | SMS</title>
    <?php include '../includes/links.php'; ?>
    <link rel="stylesheet" href="../assets/index.css">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    <?php include '../includes/sidebar.php'; ?>

    <div class="content-wrapper">
        <div class="container py-4">
            <div class="flat-form-container mb-3">
            <a href="manage_users.php" class="btn btn-light border text-muted px-4 small fw-bold">Cancel</a>
                </div>

                <form action="backend/register_logic.php" method="POST">
                    <?php if($edit_mode): ?>
                        <input type="hidden" name="user_id" value="<?php echo $edit_id; ?>">
                    <?php endif; ?>

                    <!-- Institutional Account Section -->
                    <div class="premium-panel">
                        <div class="flat-header">
                            <h5>Institutional & Account Credentials</h5>
                            <p>Official system access and identification data managed by administration.</p>
                        </div>
                        <div class="row g-4 mb-5">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">SYSTEM USERNAME</label>
                                <input type="text" name="username" class="form-control py-3" value="<?php echo htmlspecialchars($user_data['username']); ?>" placeholder="e.g. j.doe@school" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">INSTITUTIONAL EMAIL</label>
                                <input type="email" name="email" class="form-control py-3" value="<?php echo htmlspecialchars($user_data['email']); ?>" placeholder="official.contact@school.edu" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">OFFICIAL ID / ENROLL NO</label>
                                <input type="text" name="Enroll_No" class="form-control py-3" value="<?php echo htmlspecialchars($user_data['Enroll_No']); ?>" placeholder="Unique institutional identifier (Optional for Faculty)">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">PORTAL ACCESS ROLE</label>
                                <select name="role" class="form-select py-3" required>
                                    <option value="student" <?php echo $user_data['role'] == 'student' ? 'selected' : ''; ?>>Student Portfolio Access</option>
                                    <option value="teacher" <?php echo $user_data['role'] == 'teacher' ? 'selected' : ''; ?>>Faculty / Teacher Access</option>
                                    <option value="admin" <?php echo $user_data['role'] == 'admin' ? 'selected' : ''; ?>>Administrative Clearance</option>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label small fw-bold text-muted"><?php echo $edit_mode ? 'UPDATE PASSWORD (LEAVE BLANK TO RETAIN CURRENT)' : 'INITIAL ACCESS PASSWORD'; ?></label>
                                <input type="password" name="password" class="form-control py-3" placeholder="••••••••••••" <?php echo $edit_mode ? '' : 'required'; ?>>
                            </div>
                        </div>

                        <!-- ── Student Enrollment Context (Dynamic) ──────────────── -->
                        <div id="student_context" style="display: <?php echo $user_data['role'] === 'student' ? 'block' : 'none'; ?>;">
                            <div class="flat-header mt-4">
                                <h5 class="text-primary"><i class="fas fa-graduation-cap me-2"></i>Enrollment Context</h5>
                                <p>Mandatory for students to generate official ID and link to class.</p>
                            </div>
                            <div class="row g-4 mb-5 p-4 rounded-4" style="background: var(--primary-soft);">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">ENROLLMENT YEAR</label>
                                    <input type="number" id="enroll_year" name="enroll_year" class="form-control py-3" value="<?php echo date('Y'); ?>" min="2020" max="2099">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">ASSIGNED CLASS & SECTION</label>
                                    <select id="class_id" name="class_id" class="form-select py-3">
                                        <option value="">-- Select Class --</option>
                                        <?php foreach($all_classes as $cls): ?>
                                            <option value="<?php echo $cls['id']; ?>" data-name="<?php echo htmlspecialchars($cls['name']); ?>" data-sec="<?php echo htmlspecialchars($cls['section']); ?>">
                                                <?php echo htmlspecialchars($cls['name'] . ' (' . $cls['section'] . ')'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-12">
                                    <div class="d-flex align-items-center justify-content-between p-3 bg-white rounded-3 border">
                                        <div>
                                            <div class="extra-small fw-bold text-muted mb-1">AUTO-GENERATED ENROLLMENT ID</div>
                                            <div id="enroll_preview" class="h5 mb-0 text-primary fw-bold font-monospace">
                                                <?php echo $user_data['Enroll_No'] ?: '---'; ?>
                                            </div>
                                        </div>
                                        <button type="button" id="refresh_id" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                            <i class="fas fa-sync-alt me-1"></i> Regenerate
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <!-- Personal Meta Section (Secondary) -->
                        <div class="flat-header mt-4">
                            <h5>Profile Metadata (User-Managed)</h5>
                            <p>Non-critical personal details that can be updated by the user later.</p>
                        </div>
                        <div class="row g-4 pb-4">
                            <div class="col-md-6">
                                <label class="form-label extra-small fw-bold">LEGAL GIVEN NAME</label>
                                <input type="text" name="first_name" class="form-control" value="<?php echo htmlspecialchars($user_data['first_name']); ?>" placeholder="Optional">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label extra-small fw-bold">LEGAL SURNAME</label>
                                <input type="text" name="last_name" class="form-control" value="<?php echo htmlspecialchars($user_data['last_name']); ?>" placeholder="Optional">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label extra-small fw-bold">PRIMARY CONTACT PHONE</label>
                                <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($user_data['phone']); ?>" placeholder="Optional mobile number">
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" name="submit_user" class="btn btn-primary px-5 py-3 fw-bold rounded-pill">
                            <?php echo $edit_mode ? 'Save Account Changes' : 'Finalize Registration'; ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Toggle Student Context
        const roleSelect = document.querySelector('select[name="role"]');
        const studentContext = document.getElementById('student_context');
        
        if (roleSelect && studentContext) {
            roleSelect.addEventListener('change', function() {
                studentContext.style.display = (this.value === 'student') ? 'block' : 'none';
                if (this.value === 'student') generateEnrollNo();
            });
        }

        // Auto-Generate Enrollment ID
        function generateEnrollNo() {
            const role = document.querySelector('select[name="role"]').value;
            if (role !== 'student') return;

            const year = document.getElementById('enroll_year').value;
            const classSelect = document.getElementById('class_id');
            const classId = classSelect.value;
            
            if (!classId) {
                document.getElementById('enroll_preview').innerText = '---';
                return;
            }

            const className = classSelect.options[classSelect.selectedIndex].getAttribute('data-name');
            const section = classSelect.options[classSelect.selectedIndex].getAttribute('data-sec');

            // Format: [YEAR][CLASS_PREFIX][SEC][SEQ]
            const prefix = className.replace(/[^a-zA-Z0-9]/g, '').substring(0, 3).toUpperCase();
            
            fetch(`backend/get_next_seq.php?class_id=${classId}&year=${year}`)
                .then(r => r.json())
                .then(data => {
                    const seq = (data.seq || 1).toString().padStart(3, '0');
                    const enrollId = `${year}${prefix}${section}${seq}`;
                    document.getElementById('enroll_preview').innerText = enrollId;
                    
                    const enrollInput = document.getElementsByName('Enroll_No')[0];
                    if (enrollInput) enrollInput.value = enrollId;
                })
                .catch(err => console.error("Error fetching sequence:", err));
        }

        const classIdEl = document.getElementById('class_id');
        const enrollYearEl = document.getElementById('enroll_year');
        const refreshIdBtn = document.getElementById('refresh_id');

        if (classIdEl) classIdEl.addEventListener('change', generateEnrollNo);
        if (enrollYearEl) enrollYearEl.addEventListener('input', generateEnrollNo);
        if (refreshIdBtn) refreshIdBtn.addEventListener('click', generateEnrollNo);
    </script>
</body>
</html>

