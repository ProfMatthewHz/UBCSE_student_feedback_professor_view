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
$rubric_id = NULL;
$start_date = NULL;
$end_date = NULL;
$start_time = NULL;
$end_time = NULL;
$pairing_mode = NULL;
$survey_name = NULL;
$pm_mult = 1;

// check for the query string or post parameter
if($_SERVER['REQUEST_METHOD'] == 'GET') {
  // respond not found on no query string parameter
  if (isset($_GET['course'])) {

    $course_id = intval($_GET['course']);
    
    if (!isCourseInstructor($con, $course_id, $instructor_id)){
      http_response_code(400);
      echo "You do not teach this course!";
      exit();
    }


  } else {
    http_response_code(400);
    echo "Bad Request: Missing parameters.";
    exit();
  }

  echo "Success! This is the page to add a survey to course " . $course_id . "<br>";
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {

  // make sure values exist
  if (!isset($_POST['pairing-mode']) || !isset($_FILES['pairing-file']) || 
      !isset($_POST['start-date']) || !isset($_POST['start-time']) || 
      !isset($_POST['end-date']) || !isset($_POST['end-time']) || 
      // !isset($_POST['csrf-token']) || 
      !isset($_POST['course-id']) || 
      !isset($_POST['survey-name']) || !isset($_POST['rubric-id']))
  {
    http_response_code(400);
    echo "Bad Request: Missing parameters.";
    exit();
  }

  // check CSRF token
  // $csrf_token = getCSRFToken($con, $instructor_id);
  // if ((!hash_equals($csrf_token, $_POST['csrf-token'])) || !is_uploaded_file($_FILES['pairing-file']['tmp_name']))
  // {
  //   http_response_code(403);
  //   echo "Forbidden: Incorrect parameters.";
  //   exit();
  // }

  // get the name of this survey
  $survey_name = trim($_POST['survey-name']);

  // check course is not empty
  $course_id = $_POST['course-id'];
  $course_id = intval($course_id);
  if ($course_id === 0) {
    $errorMsg['course-id'] = "Please choose a valid course.";
  }

  // check rubric is not empty
  $rubric_id = $_POST['rubric-id'];
  $rubric_id = intval($rubric_id);
  if (($rubric_id === 0) or (!array_key_exists($rubric_id, $rubrics))){
    $errorMsg['rubric-id'] = "Please choose a valid rubric.";
  } 

  if (!isCourseInstructor($con, $course_id, $instructor_id)) {
    $errorMsg['course-id'] = "Please choose a valid course.";
  }

  $start_date = trim($_POST['start-date']);
  $end_date = trim($_POST['end-date']);
  if (empty($start_date)) {
    $errorMsg['start-date'] = "Please choose a start date.";
  }
  if (empty($end_date)) {
    $errorMsg['end-date'] = "Please choose a end date.";
  }

  // check the date's validity
  if (!isset($errorMsg['start-date']) and !isset($errorMsg['end-date']))
  {
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
  }

  // Get the multiplier used for pm evaluations
  $pm_mult = intval($_POST['pm-mult']);

  // check the pairing mode
  $pairing_mode = intval($_POST['pairing-mode']);
  if (!array_key_exists($pairing_mode, $_SESSION["surveyTypes"])) {
    $errorMsg['pairing-mode'] = 'Please choose a valid mode for the pairing file.';
  }
  
  // validate the uploaded file
  if ($_FILES['pairing-file']['error'] == UPLOAD_ERR_INI_SIZE) {
    $errorMsg['pairing-file'] = 'The selected file is too large.';
  } else if ($_FILES['pairing-file']['error'] == UPLOAD_ERR_PARTIAL) {
    $errorMsg['pairing-file'] = 'The selected file was only paritally uploaded. Please try again.';
  } else if ($_FILES['pairing-file']['error'] == UPLOAD_ERR_NO_FILE) {
    $errorMsg['pairing-file'] = 'A pairing file must be provided.';
  } else if ($_FILES['pairing-file']['error'] != UPLOAD_ERR_OK)  {
    $errorMsg['pairing-file'] = 'An error occured when uploading the file. Please try again.';
  } else {
    // start parsing the file
    $file_handle = @fopen($_FILES['pairing-file']['tmp_name'], "r");

    // catch errors or continue parsing the file


    if (!$file_handle) {
      $errorMsg['pairing-file'] = 'An error occured when uploading the file. Please try again.';
    } else {
      // Get the data from the review file 
      $file_data = processReviewFile($con, ($pairing_mode == 1),  $file_handle);
      
      // Clean up our file handling
      fclose($file_handle);

      // check for any errors
      if (!empty($file_data['error'])) { #
        $errorMsg['pairing-file'] = $file_data['error'];
      } 
      
      if (!empty($errorMsg)){
        
        $response['errors'] = $errorMsg;

      } else if (empty($errorMsg)) {

        $surveyInfo = array();

        $surveyInfo["survey_course_id"] = $course_id;
        $surveyInfo["survey_file"] = $file_data['rows'];
        $surveyInfo['survey_students'] = $file_data['individuals'];
        $surveyInfo["survey_data"] = array(
          'start' => $s, 
          'end' => $e, 
          'pairing_mode' => $pairing_mode, 
          'multiplier' => $pm_mult, 
          'rubric' => $rubric_id, 
          'name' => $survey_name
        );
        
        $response['data'] = $surveyInfo;

        // Save the data we will need for the confirmation page
        $_SESSION["survey_course_id"] = $course_id;
        $_SESSION["survey_file"] = $file_data['rows'];
        $_SESSION["survey_students"] = $file_data['individuals'];
        $_SESSION["survey_data"] = array('start' => $s, 'end' => $e, 'pairing_mode' => $pairing_mode, 'multiplier' => $pm_mult, 'rubric' => $rubric_id, 'name' => $survey_name);
        
        
        // $_SESSION["survey_course_id"] = $course_id;
        // $_SESSION["survey_file"] = $file_data['rows'];
        // $_SESSION["survey_students"] = $file_data['individuals'];
        // $_SESSION["survey_data"] = array('start' => $s, 'end' => $e, 'pairing_mode' => $pairing_mode, 'multiplier' => $pm_mult, 'rubric' => $rubric_id, 'name' => $survey_name);
        // http_response_code(302);
        // header("Location: ".INSTRUCTOR_HOME."surveyConfirm.php");
        // exit();
        // finally add the pairings to the database if no other error message were set so far
        // first add the survey details to the database
        // if (empty($errorMsg)) {
        //   $sdate = $start_date . ' ' . $start_time;
        //   $edate = $end_date . ' ' . $end_time;
        //   $survey_id = insertSurvey($con, $course_id, $survey_name, $sdate, $edate, $rubric_id, $pairing_mode);
        //   addReviewsToSurvey($con, $survey_id, $pairings['ids']);
        //   http_response_code(302);
        //   header("Location: ".INSTRUCTOR_HOME."surveys.php");
        //   exit();
        // }
      }
    }
  }

  header("Content-Type: application/json; charset=UTF-8");

  // $response['errors'] = $errorMsg;
  $responseJSON = json_encode($response);

  echo $responseJSON;
  
}
if ( (!isset($rubric_id)) && (count($rubrics) == 1)) {
  $rubric_id = array_key_first($rubrics);
}
$csrf_token = createCSRFToken($con, $instructor_id);
?>
