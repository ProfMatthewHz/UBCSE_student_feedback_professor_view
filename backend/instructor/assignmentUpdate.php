<?php
// //bring in required code
require "../lib/database.php";
require "../lib/constants.php";
require '../lib/studentQueries.php';
require "lib/enrollmentFunctions.php";
require "lib/instructorQueries.php";
require "lib/fileParse.php";
require "lib/pairingFunctions.php";
require "lib/rubricQueries.php";
require "lib/surveyQueries.php";
require "lib/courseQueries.php";
require "lib/reviewQueries.php";
require "lib/teamQueries.php";
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
if (!isset($_POST['course-id']) || !isset($_POST['survey-id']) || !isset($_POST['team-data'])) {
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

$pairing_mode = $survey_info['survey_type_id'];
$pm_mult = $survey_info['pm_weight'];

//stores error messages corresponding to form fields
$errorMsg = array();

// Convert the team data from JSON
$teams = json_decode($_POST['team-data'], true);
$collective_pairings = null;
if (isset($_POST['collective-pairings'])) {
  $collective_pairings = json_decode($_POST['collective-pairings'], true);
}
if (empty($teams) || ($pairing_mode == 6 && (($collective_pairings == null) || empty($collective_pairings)))) {
  $errorMsg['team-data'] = "Please provide valid team and pairing data.";
} else {
  // Verify that the team data is valid
  $team_data = getIdsForAllRosters($con, $teams);
  $team_errors = validateTeams($pairing_mode, $team_data['teams']);
  if (!empty($team_errors)) {
    $team_data["error"] = array_merge($team_data["error"], $team_errors);
  }
  if (!empty($team_data["error"])) {
    $errorMsg['team-data'] = $team_data["error"];
  } else {
    // Prune any teams that have been removed from the survey
    $successTeams = updateTeamsAndPruneReviews($con, $survey_id, $team_data['teams']);
    // Now remove any members that have been removed from their teams
    $successMembers = updateTeamMembersAndPruneReviews($con, $survey_id, $team_data['teams'], $pairing_mode != 6);
    if ($pairing_mode != 6) {
      // Generate all the pairings that will be used in the survey and then add any missing reviews
      $pairings = generatePairingsFromTeams($team_data['teams'], $pm_mult, $pairing_mode);
      $successReviews = addReviewsToSurvey($con, $survey_id, $pairings);
    } else {
      // If this is a collective review, then we need to add the collective reviews
      $successReviews = addCollectiveReviewsToSurvey($con, $survey_id, $team_data['teams'], $collective_pairings);
    }
    // Report any errors that occurred when updating the reviews
    if (!$successReviews || !$successTeams || !$successMembers) {
       $errorMsg['db'] = "An error occured when updating the survey in the database. Please try again.";
    }
  }
}
// Create the response
$response = array('errors' => $errorMsg );
header('Access-Control-Allow-Origin: '.FRONTEND_SERVER);
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');
$responseJSON = json_encode($response);
echo $responseJSON;
?>