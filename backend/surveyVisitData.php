<?php
error_reporting(-1);
ini_set("display_errors", "1");
ini_set("log_errors", 1);
ini_set("error_log", "~/php-error.log");

require "lib/constants.php";
require "lib/database.php";
require "lib/surveyQueries.php";
require "lib/visitCountQueries.php";
require "lib/loginStatus.php";

$student_id = getStudentId();

// Ensure the database connection is established
$con = connectToDatabase();

if (!isset($_POST['survey-id'])) {
    http_response_code(400);
    echo json_encode(["error" => "Bad Request: Missing required data."]);
    exit();
}

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

header('Access-Control-Allow-Origin: '.FRONTEND_SERVER);
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');
echo json_encode($response);
?>