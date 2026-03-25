<?php
session_start();
include '../includes/db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php"); exit();
}

$page_title = 'Add Timetable Slot';
$page_subtitle = 'Assign a subject and teacher to a specific time slot';

$pre_class = isset($_GET['class_id']) ? (int)$_GET['class_id'] : 0;

$classes  = $conn->query("SELECT * FROM classes ORDER BY name, section");
$subjects = $conn->query("SELECT * FROM subjects ORDER BY name");
$teachers = $conn->query("SELECT id, username FROM users WHERE role = 'teacher' ORDER BY username");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $class_id   = (int)$_POST['class_id'];
    $subject_id = (int)$_POST['subject_id'];
    $teacher_id = (int)$_POST['teacher_id'];
    $day        = $_POST['day_of_week'];
    $start      = $_POST['start_time'];
    $end        = $_POST['end_time'];
    $room       = $_POST['room_no'];

    $stmt = $conn->prepare("INSERT INTO timetable (class_id, subject_id, teacher_id, day_of_week, start_time, end_time, room_no) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iiissss", $class_id, $subject_id, $teacher_id, $day, $start, $end, $room);
    
    if ($stmt->execute()) {
        $_SESSION['toast'] = ['message' => 'Timetable slot added successfully.', 'type' => 'success'];
        header("Location: manage_timetable.php?class_id=$class_id"); exit();
    } else {
        $error = "Error adding slot: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Slot | Admin</title>
    <?php include '../includes/links.php'; ?>
    <link rel="stylesheet" href="../assets/index.css">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    <?php include '../includes/sidebar.php'; ?>

    <div class="content-wrapper">
        <div class="container py-4">
            <div class="premium-panel max-width-700 mx-auto">
                <div class="flat-header">
                    <h5>Slot Configuration</h5>
                    <p>Enter the details for the new academic period.</p>
                </div>

                <?php if(isset($error)): ?>
                    <div class="alert bg-danger-soft text-danger fw-bold small"><?php echo $error; ?></div>
                <?php endif; ?>

                <form method="POST" class="row g-4">
                    <div class="col-md-12">
                        <label class="form-label">Target Class</label>
                        <select name="class_id" class="form-select" required>
                            <option value="">Select Class...</option>
                            <?php while($c = $classes->fetch_assoc()): ?>
                                <option value="<?php echo $c['id']; ?>" <?php echo $pre_class == $c['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($c['name'] . ' ' . $c['section']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Subject</label>
                        <select name="subject_id" class="form-select" required>
                            <option value="">Select Subject...</option>
                            <?php while($s = $subjects->fetch_assoc()): ?>
                                <option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['name']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Teacher</label>
                        <select name="teacher_id" class="form-select" required>
                            <option value="">Select Teacher...</option>
                            <?php while($t = $teachers->fetch_assoc()): ?>
                                <option value="<?php echo $t['id']; ?>"><?php echo htmlspecialchars($t['username']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Day of Week</label>
                        <select name="day_of_week" class="form-select" required>
                            <option value="Monday">Monday</option>
                            <option value="Tuesday">Tuesday</option>
                            <option value="Wednesday">Wednesday</option>
                            <option value="Thursday">Thursday</option>
                            <option value="Friday">Friday</option>
                            <option value="Saturday">Saturday</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Start Time</label>
                        <input type="time" name="start_time" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">End Time</label>
                        <input type="time" name="end_time" class="form-control" required>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Room / Laboratory</label>
                        <input type="text" name="room_no" class="form-control" placeholder="e.g. Room 102, Lab A">
                    </div>

                    <div class="col-12 mt-5">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary px-5 fw-bold">Create Slot</button>
                            <a href="manage_timetable.php" class="btn btn-light border px-4 fw-bold">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include '../includes/scripts.php'; ?>
</body>
</html>
