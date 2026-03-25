<?php
session_start();
include '../includes/db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php"); exit();
}

$classes = $conn->query("SELECT * FROM classes ORDER BY name");
$subjects = $conn->query("SELECT * FROM subjects ORDER BY name");
$teachers = $conn->query("SELECT id, username FROM users WHERE role = 'teacher' AND is_active = 1");

$class_id = $_GET['class_id'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Timetable Slot</title>
    <?php include '../includes/links.php'; ?>
    <link rel="stylesheet" href="../assets/index.css">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    <div class="container py-5">
        <div class="premium-panel mx-auto" style="max-width: 600px;">
            <h3 class="fw-bold mb-4">Add Schedule Slot</h3>
            <form action="../backend/timetable_logic.php" method="POST">
                <div class="mb-3">
                    <label class="form-label">Class</label>
                    <select name="class_id" class="form-select" required>
                        <?php while($c = $classes->fetch_assoc()): ?>
                            <option value="<?php echo $c['id']; ?>" <?php echo $class_id == $c['id'] ? 'selected' : ''; ?>>
                                <?php echo $c['name']; ?> <?php echo $c['section']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Subject</label>
                        <select name="subject_id" class="form-select" required>
                            <?php while($s = $subjects->fetch_assoc()): ?>
                                <option value="<?php echo $s['id']; ?>"><?php echo $s['name']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Teacher</label>
                        <select name="teacher_id" class="form-select" required>
                            <?php while($t = $teachers->fetch_assoc()): ?>
                                <option value="<?php echo $t['id']; ?>"><?php echo $t['username']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Day of Week</label>
                    <select name="day_of_week" class="form-select" required>
                        <option>Monday</option>
                        <option>Tuesday</option>
                        <option>Wednesday</option>
                        <option>Thursday</option>
                        <option>Friday</option>
                        <option>Saturday</option>
                    </select>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Start Time</label>
                        <input type="time" name="start_time" class="form-select" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">End Time</label>
                        <input type="time" name="end_time" class="form-select" required>
                    </div>
                </div>
                <input type="hidden" name="add_slot_action" value="1">
                <button type="submit" class="btn btn-primary w-100 rounded-pill py-2 fw-bold">Add to Timetable</button>
            </form>
        </div>
    </div>
</body>
</html>
