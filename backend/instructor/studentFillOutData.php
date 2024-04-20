<?php
// Error handling and session initialization
error_reporting(-1);
ini_set("display_errors", "1");
ini_set("log_errors", 1);
ini_set("error_log", "/path/to/your/php-error.log");
session_start();

require_once "../lib/database.php";
require_once "../lib/constants.php";

if (!isset($_SESSION['id'])) {
    http_response_code(403);
    echo json_encode(["error" => "Forbidden: You must be logged in to access this page."]);
    exit();
}

$currentMonth = date('n');
$currentSemesterNumber = MONTH_MAP_SEMESTER[$currentMonth];
$mysqli = connectToDatabase();

// Using SQL CASE to determine the semester based on the month
$sql = "SELECT s.id AS student_id, s.email, su.start_date, COUNT(r.reviewer_id) AS reviews_count
        FROM students s
        LEFT JOIN reviews r ON s.id = r.reviewer_id
        LEFT JOIN surveys su ON r.survey_id = su.id
        GROUP BY s.id
        HAVING COALESCE(
          CASE
            WHEN MONTH(su.start_date) IN (1, 12) THEN 1
            WHEN MONTH(su.start_date) IN (2, 3, 4) THEN 2
            WHEN MONTH(su.start_date) IN (5, 6, 7) THEN 3
            WHEN MONTH(su.start_date) IN (8, 9, 10, 11) THEN 4
            ELSE NULL
          END, 0) = ?";

if ($stmt = $mysqli->prepare($sql)) {
    $stmt->bind_param("i", $currentSemesterNumber);
    $stmt->execute();
    $result = $stmt->get_result();
    $output = [];

    while ($row = $result->fetch_assoc()) {
        $completed = ($row['reviews_count'] > 0) ? 1 : 0;  // Simplified completed logic

        $output[] = [
            "student_id" => $row['student_id'],
            "email" => $row['email'],
            "completed" => $completed
        ];
    }

    echo json_encode($output);
    $stmt->close();
} else {
    echo json_encode(["error" => "Failed to prepare the SQL statement"]);
}

$mysqli->close();
?>