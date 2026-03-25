<?php
session_start();
include '../includes/db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include '../backend/profile_logic.php';

// Fetch current profile data for the form
$user_id = $_SESSION['user_id'];
$p = $conn->query("SELECT * FROM profile_info WHERE user_id = $user_id")->fetch_assoc();

if (!$p) {
    $p = [
        'first_name' => '', 'middle_name' => '', 'last_name' => '',
        'branch' => '', 'semester' => '', 'division' => '',
        'mobile_no' => '', 'email_id' => '', 'parent_contact_no' => '',
        'gender' => '', 'birth_date' => '', 'religion' => '',
        'caste' => '', 'nationality' => '', 'blood_group' => '',
        'aadhar_card_no' => '', 'mother_tongue' => '',
        'present_address' => '', 'permanent_address' => '',
        'district' => '', 'pin_code' => '', 'state' => '', 'country' => '',
        'profile_photo' => ''
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Profile | SMS</title>
    <?php include '../includes/links.php'; ?>
    <link rel="stylesheet" href="../assets/index.css">
    <style> .content-wrapper { margin-left: 240px; padding-top: 80px; } </style>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    <?php include '../includes/sidebar.php'; ?>

    <div class="content-wrapper">
        <div class="container py-4">
            <div class="card border-0 p-4 shadow-sm">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="fw-bold mb-0">Update Profile</h2>
                    <a href="profile_card.php" class="btn btn-light rounded-pill px-4">View Profile</a>
                </div>

                <form method="POST" action="profile.php" enctype="multipart/form-data" class="row g-4">
                    <div class="col-12">
                        <h6 class="text-primary fw-bold small text-uppercase border-bottom pb-2">Basic Information</h6>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label text-muted small fw-bold">PROFILE PHOTO</label>
                        <div class="mb-3">
                            <?php if ($p['profile_photo']): ?>
                                <img src="<?php echo htmlspecialchars($p['profile_photo']); ?>" alt="Profile" class="rounded-circle mb-2" width="80" height="80" style="object-fit: cover;">
                            <?php endif; ?>
                            <input type="file" name="profile_photo" class="form-control border-0 bg-light">
                        </div>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label text-muted small fw-bold">FIRST NAME</label>
                        <input type="text" name="first_name" class="form-control border-0 bg-light" value="<?php echo htmlspecialchars($p['first_name']); ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-muted small fw-bold">MIDDLE NAME</label>
                        <input type="text" name="middle_name" class="form-control border-0 bg-light" value="<?php echo htmlspecialchars($p['middle_name']); ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-muted small fw-bold">LAST NAME</label>
                        <input type="text" name="last_name" class="form-control border-0 bg-light" value="<?php echo htmlspecialchars($p['last_name']); ?>" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label text-muted small fw-bold">EMAIL ADDRESS</label>
                        <input type="email" name="email_id" class="form-control border-0 bg-light" value="<?php echo htmlspecialchars($p['email_id']); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small fw-bold">MOBILE NO</label>
                        <input type="text" name="mobile_no" class="form-control border-0 bg-light" value="<?php echo htmlspecialchars($p['mobile_no']); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small fw-bold">PARENT CONTACT</label>
                        <input type="text" name="parent_contact_no" class="form-control border-0 bg-light" value="<?php echo htmlspecialchars($p['parent_contact_no']); ?>">
                    </div>

                    <div class="col-12 mt-5">
                        <h6 class="text-primary fw-bold small text-uppercase border-bottom pb-2">Academic & Other Info</h6>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label text-muted small fw-bold">BRANCH</label>
                        <select name="branch" class="form-select border-0 bg-light">
                            <option value="BCA" <?php echo ($p['branch'] == 'BCA') ? 'selected' : ''; ?>>BCA</option>
                            <option value="BTech" <?php echo ($p['branch'] == 'BTech') ? 'selected' : ''; ?>>BTech</option>
                            <option value="Diploma" <?php echo ($p['branch'] == 'Diploma') ? 'selected' : ''; ?>>Diploma</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small fw-bold">SEMESTER</label>
                        <select name="semester" class="form-select border-0 bg-light">
                            <?php for($i=1; $i<=8; $i++): ?>
                                <option value="<?php echo $i; ?>" <?php echo ($p['semester'] == $i) ? 'selected' : ''; ?>><?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small fw-bold">GENDER</label>
                        <select name="gender" class="form-select border-0 bg-light">
                            <option value="Male" <?php echo ($p['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?php echo ($p['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                        </select>
                    </div>

                    <div class="col-12 mt-5">
                        <button type="submit" name="update_profile" class="btn btn-primary px-5 py-3 fw-bold rounded-pill">Save All Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include '../includes/scripts.php'; ?>
</body>
</html>
