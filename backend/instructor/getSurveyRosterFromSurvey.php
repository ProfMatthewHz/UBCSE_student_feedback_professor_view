<?php

//error logging
error_reporting(-1); // reports all errors
ini_set("display_errors", "1"); // shows all errors
ini_set("log_errors", 1);
ini_set("error_log", "~/php-error.log");

// //start the session variable
session_start();

// //bring in required code
require_once "../lib/database.php";
require_once "../lib/constants.php";
require_once '../lib/studentQueries.php';
require_once "lib/instructorQueries.php";
require_once 'lib/rubricQueries.php';
require_once 'lib/courseQueries.php';
require_once 'lib/reviewQueries.php';
require_once 'lib/surveyQueries.php';
require_once 'lib/teamQueries.php';
require_once 'lib/enrollmentFunctions.php';

// set timezone
date_default_timezone_set('America/New_York');

//try to get information about the instructor who made this request by checking the session token and redirecting if invalid
if (!isset($_SESSION['id'])) {
  http_response_code(403);
  header('Content-Type: application/json');
  echo json_encode(array("error" => "Forbidden: You must be logged in to access this page."));
  exit();
}
$instructor_id = $_SESSION['id'];

// Verify that this is a proper request 
if($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405); // Method Not Allowed
  echo "Only POST requests are allowed.";
  exit();
}

// make sure the required values exist
if (!isset($_POST['survey-id']) || !isset($_POST['course-id'])) {
  http_response_code(400);
  echo "Bad Request: Missing parameters.";
  exit();
}
// Get the id of the survey being duplicated
$survey_id = intval($_POST['survey-id']);
$course_id = intval($_POST['course-id']);

// query information about the requester
$con = connectToDatabase();

// try to look up info about the requested survey
$survey_info = getSurveyData($con, $survey_id);
if (empty($survey_info) || ($survey_info['course_id'] != $course_id) ) {
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(array("error" => "Forbidden: You must be logged in to access this page."));
    exit();
}

// Verify that the original survey is associated with the instructor
$has_access = isCourseInstructor($con, $course_id, $instructor_id);
if (!$has_access) {
  http_response_code(403);
  header('Content-Type: application/json');
  echo json_encode(array("error" => "Forbidden: You must be logged in to access this page."));
  exit();
}

$survey_type = $survey_info['survey_type_id'];

$errorMsg = array();

// Get the course's roster
$roster = getRoster($con, $course_id);

// Get the team information for this survey 
$individual_data = getSurveyStudents($con, $survey_id, $roster);
$team_info = getSurveyTeams($con, $survey_id);

$team_pairings = null;
if ($survey_type == 6) {
  // For aggregated surveys, we need to get the team pairings
  $team_pairings = getTeamPairings($con, $survey_id);
}

// Create the response
$survey_info = array( 'individuals' => $individual_data, 'teams' => $team_info, 'pairings' => $team_pairings );
$response = array( 'data' => $survey_info, 'errors' => $errorMsg );
header("Content-Type: application/json; charset=UTF-8");
$responseJSON = json_encode($response);
echo $responseJSON;
?>