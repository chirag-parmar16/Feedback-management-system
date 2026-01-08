<?php
include './includes/db_connection.php'; 
include './includes/sidebar.php'; 

$faculty = '';
$subject = '';
$question1 = '';
$question2 = '';
$question3 = '';
$question4 = '';
$comments = '';
$id = '';
$user_id = ''; 

session_start();
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
        echo "<script>showFeedbackForm();</script>"; 
    } else {
        echo "<script>alert('No feedback found!');</script>";
    }
}

if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM feedback WHERE id=? AND user_id=?");
    $stmt->bind_param("ii", $delete_id, $user_id);
    $stmt->execute();
}

$stmt = $conn->prepare("SELECT * FROM feedback WHERE user_id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback Management</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .content-wrapper {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            height: auto;
            margin-left: 220px;
            padding-top: 60px;
        }

        .card {
            border: none;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }

        h2,
        h5 {
            color: #343a40;
        }

        .form-label {
            font-weight: bold;
        }

        .btn-primary {
            background-color: #007bff;
            border: none;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }

        .table th,
        .table td {
            vertical-align: middle;
        }
    </style>
</head>

<body>
    <?php include './includes/navbar.php'; ?>

    <div class="content-wrapper">
        <div class="container">
            <div class="card mb-4">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Feedback Management</h4>
                    <button class="btn btn-primary" onclick="showFeedbackForm()">Create Feedback</button>
                </div>
            </div>

            <div id="feedbackForm" style="display: none;">
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Submit Feedback</h5>
                        <form id="formFeedback" action="feedback.php" method="POST">
                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="faculty" class="form-label">Faculty</label>
                                    <select class="form-select" name="faculty" required>
                                        <option value="">Select Faculty</option>
                                        <?php
                                        $faculties = ['Twinkle Modi', 'Aiysha Siddiqui', 'Isha Prajapati', 'Hasti Patel', 'Satyendra Sharma', 'Enakshi Chakraborty', 'Pratima Patil', 'Akil Surti'];
                                        foreach ($faculties as $fac) {
                                            echo '<option value="' . $fac . '" ' . ($faculty == $fac ? 'selected' : '') . '>' . $fac . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="subject" class="form-label">Subject</label>
                                    <select class="form-select" name="subject" required>
                                        <option value="">Select Subject</option>
                                        <?php
                                        $subjects = ['PHP', 'Java', 'Basic Linux', 'DSA', 'MIS & ERP', 'English & Communication', 'Basic Stat with R', 'Tkinter'];
                                        foreach ($subjects as $sub) {
                                            echo '<option value="' . $sub . '" ' . ($subject == $sub ? 'selected' : '') . '>' . $sub . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="question1" class="form-label">Rate the clarity of the instructor's explanations</label>
                                    <select class="form-select" name="question1" required>
                                        <option value="Excellent" <?php if ($question1 == 'Excellent') echo 'selected'; ?>>Excellent</option>
                                        <option value="Good" <?php if ($question1 == 'Good') echo 'selected'; ?>>Good</option>
                                        <option value="Average" <?php if ($question1 == 'Average') echo 'selected'; ?>>Average</option>
                                        <option value="Poor" <?php if ($question1 == 'Poor') echo 'selected'; ?>>Poor</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="question2" class="form-label">Did the instructor encourage participation and questions?</label>
                                    <select class="form-select" name="question2" required>
                                        <option value="Yes" <?php if ($question2 == 'Yes') echo 'selected'; ?>>Yes</option>
                                        <option value="No" <?php if ($question2 == 'No') echo 'selected'; ?>>No</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="question3" class="form-label">How satisfied are you with the course content?</label>
                                    <select class="form-select" name="question3" required>
                                        <option value="Very Satisfied" <?php if ($question3 == 'Very Satisfied') echo 'selected'; ?>>Very Satisfied</option>
                                        <option value="Satisfied" <?php if ($question3 == 'Satisfied') echo 'selected'; ?>>Satisfied</option>
                                        <option value="Dissatisfied" <?php if ($question3 == 'Dissatisfied') echo 'selected'; ?>>Dissatisfied</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="question4" class="form-label">Do you think the course was engaging and interesting?</label>
                                    <select class="form-select" name="question4" required>
                                        <option value="Yes" <?php if ($question4 == 'Yes') echo 'selected'; ?>>Yes</option>
                                        <option value="No" <?php if ($question4 == 'No') echo 'selected'; ?>>No</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="comments" class="form-label">Any Additional Comments</label>
                                <textarea class="form-control" name="comments" rows="4" required><?php echo htmlspecialchars($comments); ?></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Submit Feedback</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Feedback List</h5>
                    <?php if ($result->num_rows > 0) { ?>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Faculty</th>
                                <th>Subject</th>
                                <th>Rating</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()) { ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['faculty']); ?></td>
                                <td><?php echo htmlspecialchars($row['subject']); ?></td>
                                <td><?php echo htmlspecialchars($row['question1']); ?></td>
                                <td>
                                    <a href="feedback.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                    <a href="feedback.php?delete=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger"
                                        onclick="return confirm('Are you sure you want to delete this feedback?')">Delete</a>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                    <?php } else { ?>
                    <p>No feedback available.</p>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
    <script>
        function showFeedbackForm() {
            document.getElementById('feedbackForm').style.display = 'block';
        }

        function hideFeedbackForm() {
            document.getElementById('feedbackForm').style.display = 'none';
        }

        
        window.onload = function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('id')) {
                showFeedbackForm();
            }
        };
    </script>

</body>
</html>
