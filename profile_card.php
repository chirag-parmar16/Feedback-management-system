<?php
session_start();
include './includes/db_connection.php'; 


session_regenerate_id(true);


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];


$query = $conn->prepare("SELECT Enroll_No, first_name, middle_name, last_name, branch, semester, division, mobile_no, email_id, 
parent_contact_no, gender, birth_date, religion, caste, nationality, blood_group, aadhar_card_no, mother_tongue, present_address, permanent_address, district, pin_code, state, country, profile_photo FROM profile_info WHERE user_id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$query->bind_result(
    $Enroll_No,
    $first_name,
    $middle_name,
    $last_name,
    $branch,
    $semester,
    $division,
    $mobile_no,
    $email_id,
    $parent_contact_no,
    $gender,
    $birth_date,
    $religion,
    $caste,
    $nationality,
    $blood_group,
    $aadhar_card_no,
    $mother_tongue,
    $present_address,
    $permanent_address,
    $district,
    $pin_code,
    $state,
    $country,
    $profile_photo
);
$query->fetch();
$query->close();


if (!$first_name) {
    $Enroll_No = $first_name = $middle_name = $last_name = $branch = $semester = $division = $mobile_no = $email_id =
        $parent_contact_no = $gender = $birth_date = $religion = $caste = $nationality = $blood_group = $aadhar_card_no =
        $mother_tongue = $present_address = $permanent_address = $district = $pin_code = $state = $country = $profile_photo = '';
}



?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f1f2f6;
            margin: 0;
            padding: 0;
            color: #333;
        }

       
        #navbar {
            position: sticky;
            top: 0;
            z-index: 1000;
            background-color: #343a40;
        }

        #sidebar {
            position: fixed;
            top: 80px;
            left: 0;
            width: 220px;
            height: 100%;
            background-color: #343a40;
            padding-top: 20px;
            z-index: 999;
        }

        .content-wrapper {
            margin-left: 240px;
            padding-top: 80px;
        }

        
        .profile-card {
            margin-top: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            overflow: hidden;
            position: sticky;
            top: 80px;
            
        }

        .profile-card .profile-header {
            text-align: center;
            background-color: #f8f9fa;
            padding: 20px;
        }

        .profile-photo img {
            border-radius: 50%;
            width: 180px;
            height: 180px;
            object-fit: cover;
            border: 4px solid #ddd;
            transition: all 0.3s ease;
        }

        .profile-photo img:hover {
            transform: scale(1.1);
        }

        .profile-details {
            padding: 20px;
            text-align: left;
        }

        .profile-info-table {
            margin-top: 20px;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .profile-info-table table {
            width: 100%;
            margin-top: 20px;
        }

        .profile-info-table th,
        .profile-info-table td {
            padding: 10px;
            text-align: left;
        }

        .profile-info-table th {
            background-color: #f1f1f1;
        }

        
        .edit-btn {
            display: inline-block;
            background-color: #4CAF50;
            color: #fff;
            padding: 12px 30px;
            font-size: 16px;
            text-decoration: none;
            border-radius: 30px;
            transition: all 0.3s ease;
            margin-top: 20px;
        }

        .edit-btn:hover {
            background-color: #45a049;
            box-shadow: 0 6px 15px rgba(72, 174, 92, 0.2);
            transform: translateY(-2px);
        }

        .edit-btn:active {
            transform: translateY(2px);
        }

        .logout-btn {
            display: inline-block;
            background-color: #d9534f;
            color: #fff;
            padding: 12px 30px;
            font-size: 16px;
            text-decoration: none;
            border-radius: 30px;
            text-align: center;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .logout-btn:hover {
            background-color: #c9302c;
            transform: translateY(-2px);
            box-shadow: 0 6px 10px rgba(0, 0, 0, 0.15);
        }

        .logout-btn:active {
            background-color: #c12e2a;
            transform: translateY(2px);
        }

        .logout-btn:focus {
            outline: none;
        }


        
    </style>
</head>

<body>

    
    <?php include './includes/navbar.php'; ?>
    <?php include './includes/sidebar.php'; ?>

    <div class="content-wrapper">
        <div class="container">
            
            <div class="row">
                <div class="col-md-5">
                    <div class="profile-card">
                        <div class="profile-header">
                            <div class="profile-photo">
                                <?php if ($profile_photo) { ?>
                                    <img src="<?php echo htmlspecialchars($profile_photo); ?>" alt="Profile Photo">
                                <?php } else { ?>
                                    <img src="default-profile.png" alt="Default Profile Photo">
                                <?php } ?>
                            </div>
                            <div class="profile-details">
                                <h4><strong>User Name: </strong><?php echo htmlspecialchars($user_name); ?></h4>
                                <h3><?php echo htmlspecialchars($last_name . ' ' . $first_name . ' ' . $middle_name); ?></h3>
                                <p><strong>Enroll No:</strong> <?php echo htmlspecialchars($user_id); ?></p>
                                <p><strong>Branch:</strong> <?php echo htmlspecialchars($branch); ?> | <strong>Semester:</strong> <?php echo htmlspecialchars($semester); ?> | <strong>Division:</strong> <?php echo htmlspecialchars($division); ?></p>
                                <a href="profile.php" class="edit-btn">Edit Profile</a>
                                <a href="logout.php" class="logout-btn">Logout</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-7">
                    <div class="profile-info-table">
                        <h3>Contact Information</h3>
                        <table class="table table-bordered">
                            <tr>
                                <th>Email</th>
                                <td><?php echo htmlspecialchars($email_id); ?></td>
                            </tr>
                            <tr>
                                <th>Mobile No</th>
                                <td><?php echo htmlspecialchars($mobile_no); ?></td>
                            </tr>
                            <tr>
                                <th>Parent's Contact No</th>
                                <td><?php echo htmlspecialchars($parent_contact_no); ?></td>
                            </tr>
                            <tr>
                                <th>Gender</th>
                                <td><?php echo htmlspecialchars($gender); ?></td>
                            </tr>
                            <tr>
                                <th>Birth Date</th>
                                <td><?php echo htmlspecialchars($birth_date); ?></td>
                            </tr>
                            <tr>
                                <th>Religion</th>
                                <td><?php echo htmlspecialchars($religion); ?></td>
                            </tr>
                            <tr>
                                <th>Caste</th>
                                <td><?php echo htmlspecialchars($caste); ?></td>
                            </tr>
                            <tr>
                                <th>Nationality</th>
                                <td><?php echo htmlspecialchars($nationality); ?></td>
                            </tr>
                            <tr>
                                <th>Blood Group</th>
                                <td><?php echo htmlspecialchars($blood_group); ?></td>
                            </tr>
                        </table>

                        <h3>Address Information</h3>
                        <table class="table table-bordered">
                            <tr>
                                <th>Present Address</th>
                                <td><?php echo htmlspecialchars($present_address); ?></td>
                            </tr>
                            <tr>
                                <th>Permanent Address</th>
                                <td><?php echo htmlspecialchars($permanent_address); ?></td>
                            </tr>
                            <tr>
                                <th>District</th>
                                <td><?php echo htmlspecialchars($district); ?></td>
                            </tr>
                            <tr>
                                <th>Pin Code</th>
                                <td><?php echo htmlspecialchars($pin_code); ?></td>
                            </tr>
                            <tr>
                                <th>State</th>
                                <td><?php echo htmlspecialchars($state); ?></td>
                            </tr>
                            <tr>
                                <th>Country</th>
                                <td><?php echo htmlspecialchars($country); ?></td>
                            </tr>
                            <tr>
                                <th>Aadhar Card No</th>
                                <td><?php echo htmlspecialchars($aadhar_card_no); ?></td>
                            </tr>
                            <tr>
                                <th>Mother Tongue</th>
                                <td><?php echo htmlspecialchars($mother_tongue); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>