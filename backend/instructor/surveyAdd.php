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
require_once "lib/enrollmentFunctions.php";
require_once "lib/instructorQueries.php";
require_once "lib/fileParse.php";
require_once "lib/pairingFunctions.php";
require_once "lib/rubricQueries.php";
require_once "lib/surveyQueries.php";
require_once "lib/courseQueries.php";
require_once "lib/reviewQueries.php";
require_once "lib/teamQueries.php";

// set timezone
date_default_timezone_set('America/New_York');

//try to get information about the instructor who made this request by checking the session token and redirecting if invalid
if (!isset($_SESSION['id'])) {
  http_response_code(403);
  echo "Forbidden: You must be logged in to access this page.";
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
if (!isset($_POST['survey-name']) || !isset($_POST['pairing-mode']) || !isset($_POST['course-id']) || 
    !isset($_POST['start-date']) || !isset($_POST['start-time']) ||
    !isset($_POST['end-date']) || !isset($_POST['end-time']) || 
    !isset($_POST['pm-mult']) || !isset($_POST['rubric-id']) ||
    !isset($_POST['team-data'])) {
  http_response_code(400);
  echo "Bad Request: Missing parameters.";
  exit();
}

// query information about the requester
$con = connectToDatabase();

//stores error messages corresponding to form fields
$errorMsg = array();

// get the name of this survey
$survey_name = trim($_POST['survey-name']);

// check rubric is not empty and is a valid rubric
$rubrics = getRubrics($con, $instructor_id);
$rubric_id = intval($_POST['rubric-id']);
if (($rubric_id === 0) or (!array_key_exists($rubric_id, $rubrics))){
   $errorMsg['rubric-id'] = "Please choose a valid rubric.";
}

// check course is not empty
$course_id = intval($_POST['course-id']);
if ($course_id === 0) {
  $errorMsg['course-id'] = "Please choose a valid course.";
}

// Verify that this is a valid course for this instructor
if (!isCourseInstructor($con, $course_id, $instructor_id)) {
  $errorMsg['course-id'] = "Please choose a valid course.";
}

// Verify that the pairing mode is valid
$pairing_mode = intval($_POST['pairing-mode']);
$surveyTypes = getSurveyTypes($con);
if (!array_key_exists($pairing_mode, $surveyTypes)) {
  $errorMsg['pairing-mode'] = 'Please choose a valid mode for the pairing file.';
}

// Get the dates and verify that they are valid
$start_date = trim($_POST['start-date']);
$end_date = trim($_POST['end-date']);
if (empty($start_date)) {
  $errorMsg['start-date'] = "Please choose a start date.";
}
if (empty($end_date)) {
  $errorMsg['end-date'] = "Please choose a end date.";
}

// check the date's validity
if (!isset($errorMsg['start-date']) and !isset($errorMsg['end-date'])) {
  $start = DateTime::createFromFormat('Y-m-d', $start_date);
  if (!$start) {
    $errorMsg['start-date'] = "Please choose a valid start date (YYYY-MM-DD)";
  } else if ($start->format('Y-m-d') != $start_date) {
    $errorMsg['start-date'] = "Please choose a valid start date (YYYY-MM-DD)";
  }

  $end = DateTime::createFromFormat('Y-m-d', $end_date);
  if (!$end) {
    $errorMsg['end-date'] = "Please choose a valid end date (YYYY-MM-DD)";
  } else if ($end->format('Y-m-d') != $end_date) {
    $errorMsg['end-date'] = "Please choose a valid end date (YYYY-MM-DD)";
  }
}

// Get the starting and ending times and verify that they are valid
$start_time = trim($_POST['start-time']);
$end_time = trim($_POST['end-time']);
if (empty($start_time)) {
  $errorMsg['start-time'] = "Please choose a start time.";
}
if (empty($end_time)) {
  $errorMsg['end-time'] = "Please choose a end time.";
}

if (!isset($errorMsg['start-time']) && !isset($errorMsg['end-time'])) {
  $start = DateTime::createFromFormat('H:i', $start_time);
  if (!$start) {
    $errorMsg['start-time'] = "Please choose a valid start time (HH:MM) (Ex: 15:00)";
  } else if ($start->format('H:i') != $start_time) {
    $errorMsg['start-time'] = "Please choose a valid start time (HH:MM) (Ex: 15:00)";
  }

  $end = DateTime::createFromFormat('H:i', $end_time);
  if (!$end) {
    $errorMsg['end-time'] = "Please choose a valid end time (HH:MM) (Ex: 15:00)";
  } else if ($end->format('H:i') != $end_time) {
    $errorMsg['end-time'] = "Please choose a valid end time (HH:MM) (Ex: 15:00)";
  }
}

// sanity check that the start and end of the survey
if (!isset($errorMsg['start-date']) && !isset($errorMsg['start-time']) && !isset($errorMsg['end-date']) && !isset($errorMsg['end-time'])) {
  $survey_start = new DateTime($start_date . ' ' . $start_time);
  $survey_end = new DateTime($end_date . ' ' . $end_time);
  $today = new DateTime();

  if ($survey_end < $survey_start) {
    $errorMsg['end-date'] = "End date and time cannot be before start date and time.";
    $errorMsg['end-time'] = "End date and time cannot be before start date and time.";
    $errorMsg['start-date'] = "End date and time cannot be before start date and time.";
    $errorMsg['start-time'] = "End date and time cannot be before start date and time.";
  } else if ($survey_end < $today) {
    $errorMsg['end-date'] = "End date and time must occur in the future.";
    $errorMsg['end-time'] = "End date and time must occur in the future.";
  }
}

// Get the multiplier used for pm evaluations
$pm_mult = intval($_POST['pm-mult']);

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
  //$team_errors['error'] = json_encode($team_data['teams']);
  $team_errors = validateTeams($pairing_mode, $team_data['teams']);
  if (!empty($team_errors)) {
    $team_data["error"] = array_merge($team_data["error"], $team_errors);
  }
  if (!empty($team_data["error"])) {
    $errorMsg['team-data'] = $team_data["error"];
  } else {
    $survey_id = insertSurvey($con, $course_id, $survey_name, $survey_start, $survey_end, $rubric_id, $pairing_mode, $pm_mult);
    $success = insertTeams($con, $survey_id, $team_data['teams']);
    $success = insertMembers($con, $team_data['teams']);
    if ($success && $pairing_mode != 6) {
      $pairings = generatePairingsFromTeams($team_data['teams'], $pm_mult, $pairing_mode);
      $success = addReviewsToSurvey($con, $survey_id, $pairings);
    } else if ($success) {
      // If this is a collective review, then we need to add the collective reviews
      $success = addCollectiveReviewsToSurvey($con, $survey_id, $team_data['teams'], $collective_pairings);
    }
    if (!$success) {
      $errorMsg['db'] = "An error occured when adding the survey to the database. Please try again.";
    }
  }
}
// Create the response
$response = array('errors' => $errorMsg );
header("Content-Type: application/json; charset=UTF-8");
$responseJSON = json_encode($response);
echo $responseJSON;
?>