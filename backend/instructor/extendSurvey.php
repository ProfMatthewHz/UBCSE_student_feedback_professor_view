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
require_once "lib/fileParse.php";
require_once "lib/pairingFunctions.php";
require_once "lib/rubricQueries.php";
require_once "lib/surveyQueries.php";
require_once "lib/courseQueries.php";
require_once "lib/reviewQueries.php";

// set timezone
date_default_timezone_set('America/New_York');

// //query information about the requester
$con = connectToDatabase();

//try to get information about the instructor who made this request by checking the session token and redirecting if invalid
if (!isset($_SESSION['id'])) {
  http_response_code(403);
  echo "Forbidden: You must be logged in to access this page.";
  exit();
}
$instructor_id = $_SESSION['id'];


// Find out the term that we are currently in
$month = idate('m');
$term = MONTH_MAP_SEMESTER[$month];
$year = idate('Y');

// store information about rubrics as array of array
$rubrics = getRubrics($con);

//stores error messages corresponding to form fields
$errorMsg = array();

# set up json response
$response = array();
$response['data'] = array();
$response['errors'] = array();


// set flags
$course_id = NULL;
$survey_id = NULL;
$rubric_id = NULL;
$current_start_date = NULL;
$current_start_time = NULL;
$current_end_date = NULL;
$current_end_date = NULL;

$new_start_date = NULL;
$new_start_time = NULL;
$new_end_date = NULL;
$new_end_time = NULL;


// check for the query string or post parameter
if($_SERVER['REQUEST_METHOD'] == 'GET') {
  // respond not found on no query string parameter
  if (isset($_GET['survey'])) {

    $survey_id = intval($_GET['survey']);
    
    if (!isSurveyInstructor($con, $survey_id, $instructor_id)){
      http_response_code(400);
      echo "You cannot modify this survey!";
      exit();
    }

  } else {
    http_response_code(400);
    echo "Bad Request: Missing parameters.";
    exit();
  }

  // echo "Success! This is the page to extend survey " . $survey_id . "<br>";
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {

  // make sure values exist
  if (!isset($_POST['survey-id']) || !isset($_POST['end-date']) || !isset($_POST['end-time'])
      // || !isset($_POST['csrf-token'])
      )
  {
    http_response_code(400);
    echo "Bad Request: Missing parameters.";
    exit();
  }

  // check CSRF token
  // $csrf_token = getCSRFToken($con, $instructor_id);
  // if ((!hash_equals($csrf_token, $_POST['csrf-token'])))
  // {
  //   http_response_code(403);
  //   echo "Forbidden: Incorrect parameters.";
  //   exit();
  // }


  $survey_id = $_POST['survey-id'];
  $survey_info = getSurveyData($con, $survey_id);
  if (empty($survey_info)) {
    http_response_code(403);
    echo "403: Forbidden.";
    exit();
  }
  $course_id = $survey_info['course_id'];
  
  // Get the info for the course that this instructor teaches 
  $course_info = getSingleCourseInfo($con, $course_id, $instructor_id);
  if (empty($course_info)) {
    http_response_code(403);
    echo "403: Forbidden.";
    exit();
  }
  $survey_name = $survey_info['name'];
  $current_start_date = $survey_info['start_date'];
  $current_end_date = $survey_info['end_date'];
  $rubric_id = $survey_info['rubric_id'];
  $course_name = $course_info['name'];
  $course_code = $course_info['code'];
  $course_term = SEMESTER_MAP_REVERSE[$course_info['semester']];
  $course_year = $course_info['year'];
  
  
  $s = new DateTime($current_start_date);
  $e = new DateTime($current_end_date);
  $now = new DateTime();
  $current_start_date = $s->format('Y-m-d');
  $current_start_time = $s->format('H:i');
  $current_end_date = $e->format('Y-m-d');
  $current_end_time = $e->format('H:i');
  $full_perms = $now < $s;

  // assume start date is not modified
  $new_start_date = $current_start_date;
  $new_start_time = $current_start_time;


  // check survey
  if ((!isSurveyInstructor($con, $survey_id, $instructor_id)) ||
      (intval(getSurveyCourse($con,$survey_id) !== $course_id))
      )
  {
    $errorMsg["survey-id"] = "Please choose a valid survey";
  }


  $new_end_date = trim($_POST['end-date']);
  if (empty($new_end_date)) {
    $errorMsg['end-date'] = "Please choose a end date.";
  } else {
    $new_end = DateTime::createFromFormat('Y-m-d', $new_end_date);
    if (!$new_end) {
      $errorMsg['end-date'] = "Please choose a valid end date (YYYY-MM-DD)";
    } else if ($new_end->format('Y-m-d') != $new_end_date) {
      $errorMsg['end-date'] = "Please choose a valid end date (YYYY-MM-DD)";
    }
  }

  // check the date's validity
  $new_end_time = trim($_POST['end-time']);
  if (empty($new_end_time)) {
    $errorMsg['end-time'] = "Please choose a end time.";
  } else {
    $new_end = DateTime::createFromFormat('H:i', $new_end_time);
    if (!$new_end) {
      $errorMsg['end-time'] = "Please choose a valid end time (HH:MM) (Ex: 15:00)";
    } else if ($new_end->format('H:i') != $new_end_time) {
      $errorMsg['end-time'] = "Please choose a valid end time (HH:MM) (Ex: 15:00)";
    }
  }

  // check dates and times
  if (!$full_perms) {
    // This will be used when we are updating a survey that has already started
    if (!isset($errorMsg['end-date']) && !isset($errorMsg['end-time'])) {
      $orig_end = $e;
      $e = new DateTime($new_end_date . ' ' . $new_end_time);
      $today = new DateTime();
      if ($orig_end > $e) {
        $errorMsg['end-date'] = "Survey end date and time cannot be moved earlier.";
        $errorMsg['end-time'] = "Survey end date and time cannot be moved earlier.";
        $end_date = $orig_end->format('Y-m-d');
        $end_time = $orig_end->format('H:i');
      } else if (($e > $orig_end) && ($e < $today)) {
        $errorMsg['end-date'] = "End date and time must occur in the future.";
        $errorMsg['end-time'] = "End date and time must occur in the future.";
      }
    }
    # set data for survey that has not started

  } else { # not full_perms
    // Now check for the data that can only be updated when the survey has not started
    // check rubric is not empty
    
    if (!isset($_POST['rubric-id']) || !isset($_POST['start-date']) || !isset($_POST['start-time'])){
      http_response_code(400);
      echo "Bad Request: Missing parameters.";
      exit();
    }

    $rubric_id = $_POST['rubric-id'];
    $rubric_id = intval($rubric_id);
    if (!array_key_exists($rubric_id, $rubrics)) {
      $errorMsg['rubric-id'] = "Please choose a valid rubric.";
    }
    

    $new_start_date = trim($_POST['start-date']);
    if (empty($new_start_date)) {
      $errorMsg['start-date'] = "Please choose a start date.";
    } else {
      $start = DateTime::createFromFormat('Y-m-d', $new_start_date);
      if (!$start) {
        $errorMsg['start-date'] = "Please choose a valid start date (YYYY-MM-DD)";
      } else if ($start->format('Y-m-d') != $new_start_date) {
        $errorMsg['start-date'] = "Please choose a valid start date (YYYY-MM-DD)";
      }
    }
    
    $new_start_time = trim($_POST['start-time']);
    if (empty($new_start_time)) {
      $errorMsg['start-time'] = "Please choose a start time.";
    } else {
      $start = DateTime::createFromFormat('H:i', $new_start_time);
      if (!$start) {
        $errorMsg['start-time'] = "Please choose a valid start time (HH:MM) (Ex: 15:00)";
      } else if ($start->format('H:i') != $new_start_time) {
        $errorMsg['start-time'] = "Please choose a valid start time (HH:MM) (Ex: 15:00)";
      }
    }


    // check dates and times
    if (!isset($errorMsg['start-date']) && !isset($errorMsg['start-time']) && !isset($errorMsg['end-date']) && !isset($errorMsg['end-time'])) {
      $s = new DateTime($new_start_date . ' ' . $new_start_time);
      $e = new DateTime($new_end_date . ' ' . $new_end_time);
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
    }
  }
  if (empty($errorMsg)){
        
    $start_date = new DateTime($new_start_date);
    $start_time = new DateTime($new_start_time);
    $new_start = $start_date->format('Y-m-d') . ' ' . $start_time->format('H:i');

    $end_date = new DateTime($new_end_date);
    $end_time = new DateTime($new_end_time);
    $new_end = $end_date->format('Y-m-d') . ' ' . $end_time->format('H:i');


    $update_success = updateSurvey($con, $survey_id, $survey_name, $new_start, $new_end, $rubric_id);
    if (!$update_success) {
      $response['data']['survey-update'] = "Database error. Could not update survey.";
    } else {
      $survey_data = getSurveyData($con, $survey_id);

      $survey_name = $survey_data['name'];
      $survey_end = $survey_data['end_date'];

      $response['data']['survey-update'] = "Successfully updated survey";
      $response['data']['survey-data'] = $survey_data;
    }
  } else {
    $response['errors'] = $errorMsg;
  }

  header("Content-Type: application/json; charset=UTF-8");
  $responseJSON = json_encode($response);
  echo $responseJSON;
  
}
if ( (!isset($rubric_id)) && (count($rubrics) == 1)) {
  $rubric_id = array_key_first($rubrics);
}
$csrf_token = createCSRFToken($con, $instructor_id);
?>
