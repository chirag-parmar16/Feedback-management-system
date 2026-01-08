<?php
session_start();
include './includes/db_connection.php';


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}


$user_id = $_SESSION['user_id'];

$query = $conn->prepare("SELECT Enroll_No, first_name, middle_name, last_name, branch, semester, division, mobile_no, email_id, parent_contact_no, gender, birth_date, religion, caste, nationality, blood_group, aadhar_card_no, mother_tongue, present_address, permanent_address, district, pin_code, state, country, profile_photo FROM profile_info WHERE user_id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$query->bind_result($Enroll_No, $first_name, $middle_name, $last_name, $branch, $semester, $division, $mobile_no, $email_id, $parent_contact_no, $gender, $birth_date, $religion, $caste, $nationality, $blood_group, $aadhar_card_no, $mother_tongue, $present_address, $permanent_address, $district, $pin_code, $state, $country, $profile_photo);
$query->fetch();
$query->close();


if (!$first_name) {
    $Enroll_No = $first_name = $middle_name = $last_name = $branch = $semester = $division = $mobile_no = $email_id = $parent_contact_no = $gender = $birth_date = $religion = $caste = $nationality = $blood_group = $aadhar_card_no = $mother_tongue = $present_address = $permanent_address = $district = $pin_code = $state = $country = $profile_photo = '';
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $Enroll_No = $_POST['Enroll_No'];
    $first_name = $_POST['first_name'];
    $middle_name = $_POST['middle_name'];
    $last_name = $_POST['last_name'];
    $branch = $_POST['branch'];
    $semester = $_POST['semester'];
    $division = $_POST['division'];
    $mobile_no = $_POST['mobile_no'];
    $email_id = $_POST['email_id'];
    $parent_contact_no = $_POST['parent_contact_no'];
    $gender = $_POST['gender'];
    $birth_date = $_POST['birth_date'];
    $religion = $_POST['religion'];
    $caste = $_POST['caste'];
    $nationality = $_POST['nationality'];
    $blood_group = $_POST['blood_group'];
    $aadhar_card_no = $_POST['aadhar_card_no'];
    $mother_tongue = $_POST['mother_tongue'];
    $present_address = $_POST['present_address'];
    $permanent_address = $_POST['permanent_address'];
    $district = $_POST['district'];
    $pin_code = $_POST['pin_code'];
    $state = $_POST['state'];
    $country = $_POST['country'];


    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] == 0) {
        $upload_dir = 'uploads/profile_photos/';
        $photo_name = $_FILES['profile_photo']['name'];
        $photo_tmp_name = $_FILES['profile_photo']['tmp_name'];
        $photo_size = $_FILES['profile_photo']['size'];
        $photo_extension = pathinfo($photo_name, PATHINFO_EXTENSION);


        $new_photo_name = uniqid() . '.' . $photo_extension;


        if (move_uploaded_file($photo_tmp_name, $upload_dir . $new_photo_name)) {

            $profile_photo = $upload_dir . $new_photo_name;
        } else {
            $_SESSION['error_message'] = "Failed to upload the photo.";
        }
    }


    $check_query = $conn->prepare("SELECT id FROM profile_info WHERE user_id = ?");
    $check_query->bind_param("i", $user_id);
    $check_query->execute();
    $check_query->store_result();

    if ($check_query->num_rows > 0) {
        $update_query = "UPDATE profile_info SET 
Enroll_No = '$Enroll_No', 
first_name = '$first_name', 
middle_name = '$middle_name', 
last_name = '$last_name', 
branch = '$branch', 
semester = '$semester', 
division = '$division', 
mobile_no = '$mobile_no', 
email_id = '$email_id', 
parent_contact_no = '$parent_contact_no', 
gender = '$gender', 
birth_date = '$birth_date', 
religion = '$religion', 
caste = '$caste', 
nationality = '$nationality', 
blood_group = '$blood_group', 
aadhar_card_no = '$aadhar_card_no', 
mother_tongue = '$mother_tongue', 
present_address = '$present_address', 
permanent_address = '$permanent_address', 
district = '$district', 
pin_code = '$pin_code', 
state = '$state', 
country = '$country', 
profile_photo = '$profile_photo' 
WHERE user_id = $user_id";

        
        if ($conn->query($update_query)) {
            $_SESSION['success_message'] = "Profile updated successfully.";
        } else {
            $_SESSION['error_message'] = "Error updating profile: " . $conn->error;
        }
    } else {
        $insert_query = "INSERT INTO profile_info (user_id, Enroll_No, first_name, middle_name, last_name, branch, semester, division, mobile_no, email_id, parent_contact_no, gender, birth_date, religion, caste, nationality, blood_group, aadhar_card_no, mother_tongue, present_address, permanent_address, district, pin_code, state, country, profile_photo) 
        VALUES ('$user_id', '$Enroll_No', '$first_name', '$middle_name', '$last_name', '$branch', '$semester', '$division', '$mobile_no', '$email_id', '$parent_contact_no', '$gender', '$birth_date', '$religion', '$caste', '$nationality', '$blood_group', '$aadhar_card_no', '$mother_tongue', '$present_address', '$permanent_address', '$district', '$pin_code', '$state', '$country', '$profile_photo')";

       if ($conn->query($insert_query)) {
            $_SESSION['success_message'] = "Profile created successfully.";
        } else {
            $_SESSION['error_message'] = "Error creating profile: " . $conn->error;
        }
    }

    header("Location: profile_card.php");
    exit();
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Information</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding-top: 70px;
        }
    </style>
</head>

<body>
    <?php include './includes/navbar.php'; ?>

    <div class="container-fluid">
        <div class="row">
            
            <?php include './includes/sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Profile Information</h1>
                </div>

                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success">
                        <?php
                        echo $_SESSION['success_message'];
                        unset($_SESSION['success_message']);
                        ?>
                    </div>
                <?php elseif (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger">
                        <?php
                        echo $_SESSION['error_message'];
                        unset($_SESSION['error_message']);
                        ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="profile.php" class="row g-3" enctype="multipart/form-data">
                    <div class="col-md-4">
                        <label for="profile_photo" class="form-label">Profile Photo</label>
                        <input type="file" class="form-control" id="profile_photo" name="profile_photo">
                        <?php if ($profile_photo): ?>
                            <img src="<?php echo $profile_photo; ?>" alt="Profile Photo" class="img-fluid mt-2" width="100">
                        <?php endif; ?>
                    </div>

                    
                    <div class="col-md-4">
                        <label for="Enroll_No" class="form-label">Enroll No</label>
                        <input type="text" class="form-control" id="Enroll_No" name="Enroll_No" value="<?php echo htmlspecialchars($user_id); ?>" disabled>
                    </div>
                    <div class="col-md-4">
                        <label for="first_name" class="form-label">First Name</label>
                        <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo htmlspecialchars($first_name); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label for="middle_name" class="form-label">Middle Name</label>
                        <input type="text" class="form-control" id="middle_name" name="middle_name" value="<?php echo htmlspecialchars($middle_name); ?>">
                    </div>
                    <div class="col-md-4">
                        <label for="last_name" class="form-label">Last Name</label>
                        <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo htmlspecialchars($last_name); ?>" required>
                    </div>
                    
                    <div class="col-md-4">
                        <label for="branch" class="form-label">Branch</label>
                        <select class="form-select" id="branch" name="branch" required>
                            <option value="BCA" <?php echo ($branch == 'BCA') ? 'selected' : ''; ?>>BCA</option>
                            <option value="BTech (IT)" <?php echo ($branch == 'BTech (IT)') ? 'selected' : ''; ?>>BTech (IT)</option>
                            <option value="BTech (Cyber Security)" <?php echo ($branch == 'BTech (Cyber Security))') ? 'selected' : ''; ?>>BTech (Cyber Security)</option>
                            <option value="Diploma" <?php echo ($branch == 'Diploma') ? 'selected' : ''; ?>>Diploma</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="semester" class="form-label">Semester</label>
                        <select class="form-select" id="semester" name="semester" required>
                            <option value="1" <?php echo ($semester == '1') ? 'selected' : ''; ?>>1</option>
                            <option value="2" <?php echo ($semester == '2') ? 'selected' : ''; ?>>2</option>
                            <option value="3" <?php echo ($semester == '3') ? 'selected' : ''; ?>>3</option>
                            <option value="4" <?php echo ($semester == '4') ? 'selected' : ''; ?>>4</option>
                            <option value="5" <?php echo ($semester == '5') ? 'selected' : ''; ?>>5</option>
                            <option value="6" <?php echo ($semester == '6') ? 'selected' : ''; ?>>6</option>
                        </select>
                    </div> 
                    <div class="col-md-4">
                        <label for="division" class="form-label">Division</label>
                        <select class="form-select" id="division" name="division" required>
                            <option value="A" <?php echo ($division == 'A') ? 'selected' : ''; ?>>A</option>
                            <option value="B" <?php echo ($division == 'B') ? 'selected' : ''; ?>>B</option>
                            <option value="C" <?php echo ($division == 'C') ? 'selected' : ''; ?>>C</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="mobile_no" class="form-label">Mobile No</label>
                        <input type="text" class="form-control" id="mobile_no" name="mobile_no" value="<?php echo htmlspecialchars($mobile_no); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label for="email_id" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email_id" name="email_id" value="<?php echo htmlspecialchars($email_id); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label for="parent_contact_no" class="form-label">Parent Contact No</label>
                        <input type="text" class="form-control" id="parent_contact_no" name="parent_contact_no" value="<?php echo htmlspecialchars($parent_contact_no); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label for="gender" class="form-label">Gender</label>
                        <select class="form-select" id="gender" name="gender" required>
                            <option value="Male" <?php echo ($gender == 'Male') ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?php echo ($gender == 'Female') ? 'selected' : ''; ?>>Female</option>
                            <option value="Other" <?php echo ($gender == 'Other') ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="birth_date" class="form-label">Birth Date</label>
                        <input type="date" class="form-control" id="birth_date" name="birth_date" value="<?php echo htmlspecialchars($birth_date); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label for="religion" class="form-label">Religion</label>
                        <select class="form-select" id="religion" name="religion" required>
                            <option value="Hindu" <?php echo ($religion == 'Hindu') ? 'selected' : ''; ?>>Hindu</option>
                            <option value="Muslim" <?php echo ($religion == 'Muslim') ? 'selected' : ''; ?>>Muslim</option>
                            <option value="Christian" <?php echo ($religion == 'Christian') ? 'selected' : ''; ?>>Christian</option>
                            <option value="Other" <?php echo ($religion == 'Other') ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label for="caste" class="form-label">Caste</label>
                        <select class="form-select" id="caste" name="caste" required>
                            <option value="General" <?php echo ($caste == 'General') ? 'selected' : ''; ?>>General</option>
                            <option value="OBC" <?php echo ($caste == 'OBC') ? 'selected' : ''; ?>>OBC</option>
                            <option value="SC" <?php echo ($caste == 'SC') ? 'selected' : ''; ?>>SC</option>
                            <option value="ST" <?php echo ($caste == 'ST') ? 'selected' : ''; ?>>ST</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="nationality" class="form-label">Nationality</label>
                        <input type="text" class="form-control" id="nationality" name="nationality" value="<?php echo htmlspecialchars($nationality); ?>" required>
                    </div>
                    
                    <div class="col-md-4">
                        <label for="blood_group" class="form-label">Blood Group</label>
                        <select class="form-select" id="blood_group" name="blood_group" required>
                            <option value="A+" <?php echo ($blood_group == 'A+') ? 'selected' : ''; ?>>A+</option>
                            <option value="A-" <?php echo ($blood_group == 'A-') ? 'selected' : ''; ?>>A-</option>
                            <option value="B+" <?php echo ($blood_group == 'B+') ? 'selected' : ''; ?>>B+</option>
                            <option value="B-" <?php echo ($blood_group == 'B-') ? 'selected' : ''; ?>>B-</option>
                            <option value="O+" <?php echo ($blood_group == 'O+') ? 'selected' : ''; ?>>O+</option>
                            <option value="O-" <?php echo ($blood_group == 'O-') ? 'selected' : ''; ?>>O-</option>
                            <option value="AB+" <?php echo ($blood_group == 'AB+') ? 'selected' : ''; ?>>AB+</option>
                            <option value="AB-" <?php echo ($blood_group == 'AB-') ? 'selected' : ''; ?>>AB-</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label for="aadhar_card_no" class="form-label">Aadhar Card No</label>
                        <input type="text" class="form-control" id="aadhar_card_no" name="aadhar_card_no" value="<?php echo htmlspecialchars($aadhar_card_no); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label for="mother_tongue" class="form-label">Mother Tongue</label>
                        <input type="text" class="form-control" id="mother_tongue" name="mother_tongue" value="<?php echo htmlspecialchars($mother_tongue); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label for="present_address" class="form-label">Present Address</label>
                        <input type="text" class="form-control" id="present_address" name="present_address" value="<?php echo htmlspecialchars($present_address); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label for="permanent_address" class="form-label">Permanent Address</label>
                        <input type="text" class="form-control" id="permanent_address" name="permanent_address" value="<?php echo htmlspecialchars($permanent_address); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label for="district" class="form-label">District</label>
                        <input type="text" class="form-control" id="district" name="district" value="<?php echo htmlspecialchars($district); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label for="pin_code" class="form-label">Pin Code</label>
                        <input type="text" class="form-control" id="pin_code" name="pin_code" value="<?php echo htmlspecialchars($pin_code); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label for="state" class="form-label">State</label>
                        <input type="text" class="form-control" id="state" name="state" value="<?php echo htmlspecialchars($state); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label for="country" class="form-label">Country</label>
                        <input type="text" class="form-control" id="country" name="country" value="<?php echo htmlspecialchars($country); ?>" required>
                    </div>

                    
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Save Profile</button>
                    </div>
                </form>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>