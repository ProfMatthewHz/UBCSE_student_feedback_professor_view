<?php
error_reporting(-1);
ini_set("display_errors", "1");
ini_set("log_errors", 1);
ini_set("error_log", "~/php-error.log");

session_start();
require_once "../lib/database.php";
require_once "../lib/surveyQueries.php";
require_once "../lib/visitCountQueries.php";

// Ensure the database connection is established
$con = connectToDatabase();
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Ensure the user is logged in
if (!isset($_SESSION['student_id'])) {
    http_response_code(403);
    echo json_encode(["error" => "Bad Request: Only requests from within the app are valid."]);
    exit();
}

if (!isset($_POST['survey-id'])) {
    http_response_code(400);
    echo json_encode(["error" => "Bad Request: Missing required data."]);
    exit();
}

$student_id = $_SESSION['student_id'];
$survey_id = intval($_POST['survey-id']);

if (!wasReviewedInSurvey($con, $survey_id, $student_id)) {
    http_response_code(403);
    echo json_encode(["error" => "Bad Request: Improper survey requested."]);
    exit();
}

$visit_count = getVisitCount($con, $survey_id, $student_id); 
if ($visit_count == 0) {
    createFirstVisit($con, $survey_id, $student_id);
} else {
    updateVisitCount($con, $survey_id, $student_id, $visit_count + 1);
}

$response = [
    "count" => $visit_count + 1,
    "message" => "Student visit data updated successfully."
];

header('Content-Type: application/json');
echo json_encode($response);
?>