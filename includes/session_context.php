<?php
// Function to get the current active academic year
function getCurrentAcademicYear($conn) {
    if (!isset($_SESSION['academic_year_id'])) {
        $query = "SELECT id, session_name FROM academic_years WHERE is_active = 1 LIMIT 1";
        $res = $conn->query($query);
        if ($res && $res->num_rows > 0) {
            $year = $res->fetch_assoc();
            $_SESSION['academic_year_id'] = $year['id'];
            $_SESSION['academic_year_name'] = $year['session_name'];
        }
    }
    return [
        'id' => $_SESSION['academic_year_id'] ?? 0,
        'name' => $_SESSION['academic_year_name'] ?? 'N/A'
    ];
}
?>
