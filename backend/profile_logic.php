<?php
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
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        $photo_name = $_FILES['profile_photo']['name'];
        $photo_tmp_name = $_FILES['profile_photo']['tmp_name'];
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
        $update_query = $conn->prepare("UPDATE profile_info SET Enroll_No=?, first_name=?, middle_name=?, last_name=?, branch=?, semester=?, division=?, mobile_no=?, email_id=?, parent_contact_no=?, gender=?, birth_date=?, religion=?, caste=?, nationality=?, blood_group=?, aadhar_card_no=?, mother_tongue=?, present_address=?, permanent_address=?, district=?, pin_code=?, state=?, country=?, profile_photo=? WHERE user_id=?");
        $update_query->bind_param("sssssssssssssssssssssssssi", $Enroll_No, $first_name, $middle_name, $last_name, $branch, $semester, $division, $mobile_no, $email_id, $parent_contact_no, $gender, $birth_date, $religion, $caste, $nationality, $blood_group, $aadhar_card_no, $mother_tongue, $present_address, $permanent_address, $district, $pin_code, $state, $country, $profile_photo, $user_id);
        
        if ($update_query->execute()) {
            $_SESSION['success_message'] = "Profile updated successfully.";
        } else {
            $_SESSION['error_message'] = "Error updating profile: " . $conn->error;
        }
    } else {
        $insert_query = $conn->prepare("INSERT INTO profile_info (user_id, Enroll_No, first_name, middle_name, last_name, branch, semester, division, mobile_no, email_id, parent_contact_no, gender, birth_date, religion, caste, nationality, blood_group, aadhar_card_no, mother_tongue, present_address, permanent_address, district, pin_code, state, country, profile_photo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $insert_query->bind_param("isssssssssssssssssssssssss", $user_id, $Enroll_No, $first_name, $middle_name, $last_name, $branch, $semester, $division, $mobile_no, $email_id, $parent_contact_no, $gender, $birth_date, $religion, $caste, $nationality, $blood_group, $aadhar_card_no, $mother_tongue, $present_address, $permanent_address, $district, $pin_code, $state, $country, $profile_photo);

        if ($insert_query->execute()) {
            $_SESSION['success_message'] = "Profile created successfully.";
        } else {
            $_SESSION['error_message'] = "Error creating profile: " . $conn->error;
        }
    }

    header("Location: profile.php");
    exit();
}
?>
