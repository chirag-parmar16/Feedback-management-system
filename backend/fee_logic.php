<?php
session_start();
include '../includes/db_connection.php';

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized: Please log in.");
}

// 1. Create Fee Structure
if (isset($_POST['create_structure_action'])) {
    if ($_SESSION['role'] !== 'admin') die("Forbidden: Admin only.");
    $class_id = $_POST['class_id'];
    $name = $_POST['fee_name'];
    $amount = $_POST['amount'];
    $desc = $_POST['description'];

    $stmt = $conn->prepare("INSERT INTO fee_structures (class_id, fee_name, amount, description) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isds", $class_id, $name, $amount, $desc);
    $stmt->execute();
    $_SESSION['message'] = "Fee structure created.";
    header("Location: ../admin/manage_fees.php");
    exit();
}

// 2. Process Payment
if (isset($_POST['collect_payment_action'])) {
    if ($_SESSION['role'] !== 'admin') die("Forbidden: Admin only.");
    $student_fee_id = $_POST['student_fee_id'];
    $amount_paid = $_POST['amount_paid'];
    $method = $_POST['payment_method'];
    $tx_id = $_POST['transaction_id'];
    $admin_id = $_SESSION['user_id'];

    $conn->begin_transaction();
    try {
        // Record payment
        $stmt = $conn->prepare("INSERT INTO payments (student_fee_id, amount_paid, payment_method, transaction_id, received_by) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("idssi", $student_fee_id, $amount_paid, $method, $tx_id, $admin_id);
        $stmt->execute();
        $payment_id = $conn->insert_id;

        // Generate receipt record
        $receipt_no = "RCP-" . time() . "-" . $payment_id;
        $stmt = $conn->prepare("INSERT INTO receipts (payment_id, receipt_no) VALUES (?, ?)");
        $stmt->bind_param("is", $payment_id, $receipt_no);
        $stmt->execute();

        // Update student_fee record
        $stmt = $conn->prepare("UPDATE student_fees SET paid_amount = paid_amount + ?, status = CASE WHEN (paid_amount + ?) >= total_amount THEN 'paid' ELSE 'partial' END WHERE id = ?");
        $stmt->bind_param("ddi", $amount_paid, $amount_paid, $student_fee_id);
        $stmt->execute();

        $conn->commit();
        $_SESSION['message'] = "Payment recorded. Receipt: $receipt_no";
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "Payment failed: " . $e->getMessage();
    }
    header("Location: ../admin/manage_fees.php");
    exit();
}

// 3. Student Self-Payment
if (isset($_POST['student_pay_action'])) {
    if ($_SESSION['role'] !== 'student') die("Forbidden");
    
    $student_fee_id = $_POST['student_fee_id'];
    $amount_paid = $_POST['amount'];
    $method = 'online';
    
    $conn->begin_transaction();
    try {
        // Record payment
        $stmt = $conn->prepare("INSERT INTO payments (student_fee_id, amount_paid, payment_method, transaction_id, received_by) VALUES (?, ?, ?, ?, NULL)");
        $tx_id = "TXN-" . time() . "-" . $_SESSION['user_id'];
        $stmt->bind_param("idss", $student_fee_id, $amount_paid, $method, $tx_id);
        $stmt->execute();
        $payment_id = $conn->insert_id;

        // Generate receipt
        $receipt_no = "RCP-" . time() . "-" . $payment_id;
        $stmt = $conn->prepare("INSERT INTO receipts (payment_id, receipt_no) VALUES (?, ?)");
        $stmt->bind_param("is", $payment_id, $receipt_no);
        $stmt->execute();

        // Update student_fee record
        $stmt_upd = $conn->prepare("UPDATE student_fees SET paid_amount = paid_amount + ?, status = CASE WHEN (paid_amount + ?) >= total_amount THEN 'paid' ELSE 'partial' END WHERE id = ?");
        $stmt_upd->bind_param("ddi", $amount_paid, $amount_paid, $student_fee_id);
        $stmt_upd->execute();

        $conn->commit();
        
        $_SESSION['message'] = "Payment of ₹$amount_paid successful! Receipt generated: $receipt_no";
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "Payment failed: " . $e->getMessage();
    }
    header("Location: ../student/my_fees.php");
    exit();
}
?>
