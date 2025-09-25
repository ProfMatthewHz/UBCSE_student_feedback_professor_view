<?php
// //bring in required code
require "../lib/database.php";
require "../lib/constants.php";
require '../lib/studentQueries.php';
require "lib/instructorQueries.php";
require 'lib/rubricQueries.php';
require 'lib/courseQueries.php';
require 'lib/reviewQueries.php';
require 'lib/surveyQueries.php';
require 'lib/teamQueries.php';
require 'lib/enrollmentFunctions.php';
require "lib/loginStatus.php";

//error logging
error_reporting(-1); // reports all errors
ini_set("display_errors", "1"); // shows all errors
ini_set("log_errors", 1);
ini_set("error_log", "~/php-error.log");

$instructor_id = getInstructorId();

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
header('Access-Control-Allow-Origin: '.FRONTEND_SERVER);
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');
$responseJSON = json_encode($response);
echo $responseJSON;
?>