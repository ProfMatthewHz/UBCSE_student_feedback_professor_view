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
$survey_id = NULL;
$course_id = NULL;
$end_date = NULL;
$end_time = NULL;

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

  echo "Success! This is the page to extend survey " . $survey_id . "<br>";
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {

  // make sure values exist
  if (!isset($_POST['course-id']) || !isset($_POST['survey-id'])
      || !isset($_POST['end-date']) || !isset($_POST['end-time'])
      // || !isset($_POST['csrf-token'])
      )
  {
    print_r($_POST);
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

  // get the name of this survey

  // check course is not empty
  $course_id = $_POST['course-id'];
  $course_id = intval($course_id);
  if (($course_id === 0)  || (!isCourseInstructor($con, $course_id, $instructor_id))){
    $errorMsg['course-id'] = "Please choose a valid course.";
  }

  $survey_id = $_POST['survey-id'];


  $end_date = trim($_POST['end-date']);

  // check survey
  if ((!isSurveyInstructor($con, $survey_id, $instructor_id)) ||
      (intval(getSurveyCourse($con,$survey_id) !== $course_id))
      )
  {
    $errorMsg["survey-id"] = "Please choose a valid survey";
  }

  $survey_data = getSurveyData($con, $survey_id);

  if ($survey_data == null){
    $errorMsg["survey-id"] = "Please choose a valid survey"; 
  }

  $current_start_date = $survey_data['start_date'];
  $current_end_date = $survey_data['end_date'];


  if (empty($end_date)) {
    $errorMsg['end-date'] = "Please choose a end date.";
  }

  // check the date's validity
  if (!isset($errorMsg['end-date'])){
  
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
  }

  if (!isset($errorMsg['end-time'])) {

    $end = DateTime::createFromFormat('H:i', $end_time);
    if (!$end) {
      $errorMsg['end-time'] = "Please choose a valid end time (HH:MM) (Ex: 15:00)";
    } else if ($end->format('H:i') != $end_time) {
      $errorMsg['end-time'] = "Please choose a valid end time (HH:MM) (Ex: 15:00)";
    }
  }

  // check dates and times
  if (!isset($errorMsg['end-date']) && !isset($errorMsg['end-time'])) {
    // $start = new Datetime($survey_data['start_date']);  
    $s = new DateTime($current_start_date);
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
    
  }

  if (empty($errorMsg)){

    $end_date = new DateTime($end_date);
    $end_time = new DateTime($end_time);

    $end = $end_date->format('Y-m-d') . ' ' . $end_time->format('H:i');


    $extend_success = extendSurvey($con, $survey_id, $end);
    if (!$extend_success) {
      $response['errors']['survey-update'] = "Database error. Could not update survey.";
    } else {
      $survey_data = getSurveyData($con, $survey_id);

      $survey_name = $survey_data['name'];
      $survey_end = $survey_data['end_date'];
      
      $survey_success_msg = $survey_name . " extended from " . $end . " to " . $survey_end;
      
      $response['data']['survey-update'] = $survey_success_msg;
      $response['data']['survey-data'] = $survey_data;
    }


  } 
  
  $response['errors'] = $errorMsg;

  header("Content-Type: application/json; charset=UTF-8");
  $responseJSON = json_encode($response);
  echo $responseJSON;
  
}
if ( (!isset($rubric_id)) && (count($rubrics) == 1)) {
  $rubric_id = array_key_first($rubrics);
}
$csrf_token = createCSRFToken($con, $instructor_id);
?>