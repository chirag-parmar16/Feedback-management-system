<?php
session_start();
include './includes/db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$edit_mode = false;
$page_title = isset($_GET['edit_id']) ? 'Refine Profile' : 'Personnel Registration';
$page_subtitle = isset($_GET['edit_id']) ? 'Update existing user credentials and access levels.' : 'Onboard new administrators, faculty, or students to the platform.';
$message = '';
$user_data = ['username' => '', 'email' => '', 'role' => '', 'first_name' => '', 'last_name' => '', 'Enroll_No' => '', 'phone' => ''];

if (isset($_GET['edit_id'])) {
    $edit_mode = true;
    $edit_id = $_GET['edit_id'];
    $stmt = $conn->prepare("SELECT u.*, p.first_name, p.last_name, p.Enroll_No, p.phone FROM users u LEFT JOIN profile_info p ON u.id = p.user_id WHERE u.id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $user_data = $result->fetch_assoc();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $edit_mode ? 'Refine Profile' : 'New User'; ?> | SMS</title>
    <?php include './includes/links.php'; ?>
    <link rel="stylesheet" href="index.css">
</head>
<body>
    <?php include './includes/navbar.php'; ?>
    <?php include './includes/sidebar.php'; ?>

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

    <?php include './includes/scripts.php'; ?>
</body>
</html>
