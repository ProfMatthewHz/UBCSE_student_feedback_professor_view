<?php
// Error handling and session initialization
error_reporting(-1);
ini_set("display_errors", "1");
ini_set("log_errors", 1);
ini_set("error_log", "/path/to/your/php-error.log");
session_start();

require_once "../lib/database.php";
require_once "../lib/constants.php";
require_once "lib/surveyQueries.php";

$conn = connectToDatabase();

if (!isset($_SESSION['id'])) {
    http_response_code(403);
    echo json_encode(["error" => "Forbidden: You must be logged in to access this page."]);
    exit();
}

// respond not found on no query string parameters
if (!isset($_POST['survey'])) {
  http_response_code(400);
  echo "400: Improper request made.";
  exit();
}

// make sure the query string is an integer, reply 404 otherwise
$survey_id = intval($_POST['survey']);

if ($survey_id === 0) {
  http_response_code(404);
  echo json_encode(["error" => "Page not found"]);
  exit();
}

// Look up info about the requested survey
$survey_info = getSurveyData($con, $survey_id);
if (empty($survey_info)) {
  http_response_code(404);
  echo json_encode(["error" => "Page not found"]);
  exit();
}

// make sure the survey is for a course the current instructor actually teaches
if (!isCourseInstructor($con, $survey_info['course_id'], $instructor_id)) {
  http_response_code(403);
  echo json_encode(["error" => "Permission not granted"]);
  exit();
}

$results = getReviewerCompletionResults($conn, $survey_id);
$json_encode = json_encode($output);
echo $json_encode;
?>