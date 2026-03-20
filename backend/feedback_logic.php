<?php
$faculty = '';
$subject = '';
$question1 = '';
$question2 = '';
$question3 = '';
$question4 = '';
$comments = '';
$id = '';
$user_id = $_SESSION['user_id']; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $faculty = $_POST['faculty'];
    $subject = $_POST['subject'];
    $question1 = $_POST['question1'];
    $question2 = $_POST['question2'];
    $question3 = $_POST['question3'];
    $question4 = $_POST['question4'];
    $comments = $_POST['comments'];

    if (!empty($_POST['id'])) {
        $id = $_POST['id'];
        $stmt = $conn->prepare(
            "UPDATE feedback SET faculty=?, subject=?, question1=?, question2=?, question3=?, question4=?, comments=? WHERE id=? AND user_id=?"
        );
        $stmt->bind_param("ssssssssi", $faculty, $subject, $question1, $question2, $question3, $question4, $comments, $id, $user_id);
    } else {
        $stmt = $conn->prepare(
            "INSERT INTO feedback (user_id, faculty, subject, question1, question2, question3, question4, comments) VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("isssssss", $user_id, $faculty, $subject, $question1, $question2, $question3, $question4, $comments);
    }

    $stmt->execute();
    header("Location: feedback.php");
    exit();
}

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM feedback WHERE id=? AND user_id=?");
    $stmt->bind_param("ii", $id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $faculty = $row['faculty'];
        $subject = $row['subject'];
        $question1 = $row['question1'];
        $question2 = $row['question2'];
        $question3 = $row['question3'];
        $question4 = $row['question4'];
        $comments = $row['comments'];
    }
}

if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM feedback WHERE id=? AND user_id=?");
    $stmt->bind_param("ii", $delete_id, $user_id);
    $stmt->execute();
    header("Location: feedback.php");
    exit();
}

$stmt = $conn->prepare("SELECT * FROM feedback WHERE user_id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$feedback_result = $stmt->get_result();
?>
