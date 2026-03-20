<?php
session_start();
include '../includes/db_connection.php';

function sanitize_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_user'])) {
    $username = sanitize_input($_POST['username']);
    $email = sanitize_input($_POST['email']);
    $role = sanitize_input($_POST['role']);
    $user_id = isset($_POST['user_id']) ? $_POST['user_id'] : null;
    $first_name = sanitize_input($_POST['first_name']);
    $last_name = sanitize_input($_POST['last_name']);
    $enroll_no = sanitize_input($_POST['Enroll_No']);
    $phone = sanitize_input($_POST['phone']);

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

            // Update profile
            $stmt = $conn->prepare("UPDATE profile_info SET first_name = ?, last_name = ?, Enroll_No = ?, phone = ? WHERE user_id = ?");
            $stmt->bind_param("ssssi", $first_name, $last_name, $enroll_no, $phone, $user_id);
            $stmt->execute();
            
            // If no profile exists yet, insert it (upsert style)
            if ($stmt->affected_rows == 0) {
                $stmt = $conn->prepare("INSERT INTO profile_info (user_id, first_name, last_name, Enroll_No, phone) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("issss", $user_id, $first_name, $last_name, $enroll_no, $phone);
                $stmt->execute();
            }

            $success_msg = "User account successfully refined!";
        } else {
            // Insert new user
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $username, $password, $email, $role);
            $stmt->execute();
            $new_user_id = $conn->insert_id;

            // Insert profile
            $stmt = $conn->prepare("INSERT INTO profile_info (user_id, first_name, last_name, Enroll_No, phone) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("issss", $new_user_id, $first_name, $last_name, $enroll_no, $phone);
            $stmt->execute();

            $success_msg = "New user account successfully created!";
        }

        $conn->commit();
        $_SESSION['message'] = $success_msg;
        header("Location: ../manage_users.php");
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "Transaction failed: " . $e->getMessage();
        header("Location: ../add_user.php" . ($user_id ? "?edit_id=$user_id" : ""));
        exit();
    }
}
?>
