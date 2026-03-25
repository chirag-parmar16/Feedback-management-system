<?php
session_start();
include '../includes/db_connection.php';

function sanitize_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_user_action'])) {
    $username  = sanitize_input($_POST['username']);
    $email     = sanitize_input($_POST['email']);
    $role      = sanitize_input($_POST['role']);
    $user_id   = isset($_POST['user_id']) && is_numeric($_POST['user_id']) ? (int)$_POST['user_id'] : null;
    $first_name = sanitize_input($_POST['first_name']);
    $last_name  = sanitize_input($_POST['last_name']);
    $enroll_no  = sanitize_input($_POST['Enroll_No'] ?? '');
    $mobile_no  = sanitize_input($_POST['phone'] ?? '');
    $class_id   = isset($_POST['class_id']) ? (int)$_POST['class_id'] : null;


    // Validate role
    $allowed_roles = ['admin', 'teacher', 'student'];
    if (!in_array($role, $allowed_roles)) {
        $_SESSION['error'] = "Invalid role selected.";
        header("Location: ../admin/add_user.php" . ($user_id ? "?edit_id=$user_id" : ""));
        exit();
    }

    $conn->begin_transaction();

    try {
        if ($user_id) {
            // Update existing user
            if (!empty($_POST['password'])) {
                $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET username = ?, password = ?, email = ?, role = ? WHERE id = ?");
                $stmt->bind_param("ssssi", $username, $password, $email, $role, $user_id);
            } else {
                $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, role = ? WHERE id = ?");
                $stmt->bind_param("sssi", $username, $email, $role, $user_id);
            }
            $stmt->execute();

            // Update profile — check first if profile exists
            $chk = $conn->prepare("SELECT id FROM profile_info WHERE user_id = ?");
            $chk->bind_param("i", $user_id);
            $chk->execute();
            $chk->store_result();

            if ($chk->num_rows > 0) {
                $stmt = $conn->prepare("UPDATE profile_info SET first_name = ?, last_name = ?, Enroll_No = ?, mobile_no = ? WHERE user_id = ?");
                $stmt->bind_param("ssssi", $first_name, $last_name, $enroll_no, $mobile_no, $user_id);
            } else {
                $stmt = $conn->prepare("INSERT INTO profile_info (user_id, first_name, last_name, Enroll_No, mobile_no) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("issss", $user_id, $first_name, $last_name, $enroll_no, $mobile_no);
            }
            $stmt->execute();

            $success_msg = "User account successfully updated!";
        } else {
            // Insert new user — password required
            if (empty($_POST['password'])) {
                throw new Exception("Password is required for new users.");
            }
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $username, $password, $email, $role);
            $stmt->execute();
            $new_user_id = $conn->insert_id;

            // Insert profile
            $stmt = $conn->prepare("INSERT INTO profile_info (user_id, first_name, last_name, Enroll_No, mobile_no) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("issss", $new_user_id, $first_name, $last_name, $enroll_no, $mobile_no);
            $stmt->execute();

            // Link to class if student
            if ($role === 'student' && $class_id) {
                $stmt = $conn->prepare("INSERT INTO student_enrollment (student_id, class_id) VALUES (?, ?)");
                $stmt->bind_param("ii", $new_user_id, $class_id);
                $stmt->execute();
            }

            $success_msg = "New user account successfully created!";

        }

        $conn->commit();
        $_SESSION['toast'] = ['message' => $success_msg, 'type' => 'success'];
        header("Location: ../admin/manage_users.php");
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['toast'] = ['message' => 'Error: ' . $e->getMessage(), 'type' => 'error'];
        header("Location: ../admin/add_user.php" . ($user_id ? "?edit_id=$user_id" : ""));
        exit();
    }
}
?>
