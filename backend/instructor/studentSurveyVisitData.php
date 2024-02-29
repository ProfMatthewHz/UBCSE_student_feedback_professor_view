<?php

require "lib/database.php";
require "lib/constants.php";
require "lib/constants.php";


session_start();
ini_set("display_errors", 1);
ini_set("log_errors", 1);
ini_set("error_log", "~/php-error.log");
$con = connectToDatabase();

if (!isset($_SESSION['id'])) {
    http_response_code(403);
    echo "Forbidden: You must be logged in to access this page.";
    exit();
  }
$instructor_id = $_SESSION['id'];

$survey_data = array();

// Query to fetch data from student_visit_data table
$query = "SELECT survey_id, student_id, timestamp, counter FROM student_visit_data WHERE instructor_id = ?";
$stmt = $con->prepare($query);
$stmt->bind_param("i", $instructor_id);
$stmt->execute();
$result = $stmt->get_result();

// Iterate over the result set and populate the dictionary
while ($row = $result->fetch_assoc()) {
    $survey_id = $row['survey_id'];
    $student_id = $row['student_id'];
    $timestamp = $row['timestamp'];
    $counter = $row['counter'];

    // Store data in the dictionary
    if (!isset($survey_data[$survey_id])) {
        $survey_data[$survey_id] = array();
    }
    $survey_data[$survey_id][] = array(
        'student_id' => $student_id,
        'timestamp' => $timestamp,
        'counter' => $counter
    );
}

// Output the survey data dictionary
echo json_encode($survey_data);

?>