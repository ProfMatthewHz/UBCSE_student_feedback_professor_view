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

// set timezone
date_default_timezone_set('America/New_York');

// //query information about the requester
$con = connectToDatabase();

//try to get information about the instructor who made this request by checking the session token and redirecting if invalid
if (!isset($_SESSION['id'])) {
  http_response_code(403);
  header('Content-Type: application/json');
  echo json_encode(array("error" => "Forbidden: You must be logged in to access this page."));
  exit();
}
$instructor_id = $_SESSION['id'];

//stores error messages corresponding to form fields

// set flags
$course_id = NULL;
$survey_id = NULL;
$rubric_id = NULL;
$start_data = NULL;
$end_data = NULL;
$start_date = NULL;
$end_date = NULL;
$start_time = NULL;
$end_time = NULL;

// check for the survey number we are working with
if (($_SERVER['REQUEST_METHOD'] == 'POST') && isset($_POST['survey-id'])) {
    $survey_id = intval($_POST['survey-id']);
} else {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(array("error" => "Bad Request: Must use app to access this function."));
    exit();
}

// Get rubric info
$rubrics = getRubrics($con, $instructor_id);

// try to look up info about the requested survey
$survey_info = getSurveyData($con, $survey_id);
if (empty($survey_info)) {
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(array("error" => "Forbidden: You must be logged in to access this page."));
    exit();
}

// Verify that the original survey is associated with the instructor
$has_access = isSurveyInstructor($con, $survey_id, $instructor_id);
if (!$has_access) {
  http_response_code(403);
  header('Content-Type: application/json');
  echo json_encode(array("error" => "Forbidden: You must be logged in to access this page."));
  exit();
}

$survey_type = $survey_info['survey_type_id'];
$course_id = getSurveyCourse($con, $survey_id);

// make sure values exist
if (!isset($_POST['start-date']) || !isset($_POST['start-time']) || 
    !isset($_POST['end-date']) || !isset($_POST['end-time']) || 
    !isset($_POST['survey-name']) || !isset($_POST['rubric-id']))  {
  http_response_code(400);
  header('Content-Type: application/json');
  echo json_encode(array("error" => "Bad Request: Must use app to access this function."));
  exit();
}

$response = array('data' => array(), 'errors' => array());
$errorMsg = array();

// The survey name and end date can always be updated, so we check them first
  
// Get the new name of this survey
$survey_name = trim($_POST['survey-name']);

// Get the new end date of the survey
$end_date = trim($_POST['end-date']);
if (empty($end_date)) {
  $errorMsg['end-date'] = "Please choose a end date.";
} else {
  $end = DateTime::createFromFormat('Y-m-d', $end_date);
  if (!$end) {
    $errorMsg['end-date'] = "Please choose a valid end date (YYYY-MM-DD)";
  } else if ($end->format('Y-m-d') != $end_date) {
    $errorMsg['end-date'] = "Please choose a valid end date (YYYY-MM-DD)";
  }
}

$end_time = trim($_POST['end-time']);
if (empty($end_time)) {
  $errorMsg['end-time'] = "Please choose a end time.";
} else {
  $end = DateTime::createFromFormat('H:i', $end_time);
  if (!$end) {
    $errorMsg['end-time'] = "Please choose a valid end time (HH:MM) (Ex: 15:00)";
  } else if ($end->format('H:i') != $end_time) {
    $errorMsg['end-time'] = "Please choose a valid end time (HH:MM) (Ex: 15:00)";
  }
}

$rubric_id = $_POST['rubric-id'];
$rubric_id = intval($rubric_id);
if (!array_key_exists($rubric_id, $rubrics)) {
  $errorMsg['rubric-id'] = "Please choose a valid rubric.";
}

$start_date = trim($_POST['start-date']);
if (empty($start_date)) {
  $errorMsg['start-date'] = "Please choose a start date.";
} else {
  $start = DateTime::createFromFormat('Y-m-d', $start_date);
  if (!$start) {
    $errorMsg['start-date'] = "Please choose a valid start date (YYYY-MM-DD)";
  } else if ($start->format('Y-m-d') != $start_date) {
    $errorMsg['start-date'] = "Please choose a valid start date (YYYY-MM-DD)";
  }
}
$start_time = trim($_POST['start-time']);
if (empty($start_time)) {
  $errorMsg['start-time'] = "Please choose a start time.";
} else {
  $start = DateTime::createFromFormat('H:i', $start_time);
  if (!$start) {
    $errorMsg['start-time'] = "Please choose a valid start time (HH:MM) (Ex: 15:00)";
  } else if ($start->format('H:i') != $start_time) {
    $errorMsg['start-time'] = "Please choose a valid start time (HH:MM) (Ex: 15:00)";
  }
}
$s = null;
$e = null;

// check dates and times
if (!isset($errorMsg['start-date']) && !isset($errorMsg['start-time']) && !isset($errorMsg['end-date']) && !isset($errorMsg['end-time'])) {
  $s = new DateTime($start_date . ' ' . $start_time);
  $e = new DateTime($end_date . ' ' . $end_time);
  $today = new DateTime();

  if ($e < $s) {
    $errorMsg['end-date'] = "End date and time cannot be before start date and time.";
    $errorMsg['end-time'] = "End date and time cannot be before start date and time.";
    $errorMsg['start-date'] = "End date and time cannot be before start date and time.";
    $errorMsg['start-time'] = "End date and time cannot be before start date and time.";
  } else if ($e < $today) {
    $errorMsg['end-date'] = "End date and time must occur in the future.";
    $errorMsg['end-time'] = "End date and time must occur in the future.";
  }
  

  // Update the survey details in the database
  if (empty($errorMsg)) {
    try {
      $pairings = getReviewsForSurvey($con, $survey_id);
      $survey_id = insertSurvey($con, $course_id, $survey_name, $s, $e, $rubric_id, $survey_type);
    
      $success = addReviewsToSurvey($con, $survey_id, $pairings);
      if ($success) {
        $response['data']['survey-update'] = "Success: Survey duplicated into: " . $survey_name;
      } else {
        $response['data']['survey-update'] = "Failed to duplicate survey: " . $survey_name;
      }  
    } catch (Exception $e) {
      $response['data']['survey-update'] = "Database failure duplicating survey: " . $survey_name;
      $errorMsg['survey-duplicate'] = $e->getMessage();
    }
  }
  $response['errors'] = $errorMsg;

  header("Content-Type: application/json; charset=UTF-8");
  $responseJSON = json_encode($response);
  echo $responseJSON;

}