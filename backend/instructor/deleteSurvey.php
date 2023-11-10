<?php

//error logging
error_reporting(-1); // reports all errors
ini_set("display_errors", "1"); // shows all errors
ini_set("log_errors", 1);
ini_set("error_log", "~/php-error.log");

// start the session variable
session_start();

// bring in required code
require_once "../lib/database.php";
require_once "../lib/constants.php";
require_once "lib/instructorQueries.php";
require_once "lib/courseQueries.php";
require_once "lib/surveyQueries.php";


// query information about the requester
$con = connectToDatabase();

//try to get information about the instructor who made this request by checking the session token and redirecting if invalid
if (!isset($_SESSION['id'])) {
  http_response_code(403);
  echo "Forbidden: You must be logged in to access this page.";
  exit();
}
$instructor_id = $_SESSION['id'];

// check for the query string or post parameter
$sid = NULL;
if($_SERVER['REQUEST_METHOD'] == 'GET') {
  // respond not found on no query string parameter
  if (!isset($_GET['survey'])) {
    http_response_code(404);
    echo "404: Not found.";
    exit();
  }

  $response = array('data' => array(), 'errors' => array());

  // make sure the query string is an integer, reply 404 otherwise
  $sid = intval($_GET['survey']);

  if ($sid === 0) {
    http_response_code(404);   
    echo "404: Not found. Survey must be an integer";
    exit();
  }

  $survey_id = $sid;

  $survey_data = getSurveyData($con, $survey_id);

  $errorMsg = array();

  if (empty($survey_data)) {
    $errorMsg['survey'] = "Please choose a valid survey";
  } 
  if (!isSurveyInstructor($con, $survey_id, $instructor_id)){
    $errorMsg['survey'] = "Please choose a valid survey";
  }

  $survey_course_id = $survey_data['course_id'];
  $course_data = getSingleCourseInfo($con, $survey_course_id, $instructor_id);

  if (empty($course_data)){
    $errorMsg['course'] = "Please choose a valid course";
  }

  if (empty($errorMsg)){

    # return data for frontend to present

    $course_code = $course_data['code'];
    $course_name = $course_data['name'];
    $course_term = SEMESTER_MAP_REVERSE[$course_data['semester']];
    $course_year = $course_data['year'];

    $courseData = array();
    $courseData['code'] = $course_code;
    $courseData['name'] = $course_name;
    $courseData['term'] = $course_term;
    $courseData['year'] = $course_year;

    $surveyData = array();
    $survey_name = $survey_data['name'];
    $surveyData['name'] = $survey_name;
    $surveyData['id'] = $survey_id;
    
    $response['data']['survey'] = $surveyData;
    $response['data']['course'] = $courseData;

    $_SESSION['survey-id'] = $survey_id;

  } else {
    $response['errors'] = $errorMsg;
  }

  header("Content-Type: application/json; charset=UTF-8");
  $responseJSON = json_encode($response);
  echo $responseJSON;

}

// now perform the basic checks
if($_SERVER['REQUEST_METHOD'] == 'POST') {

  if (!isset($_SESSION['survey-id'])){
    http_response_code(403);
    echo "403 Forbidden.";
    exit();
  }

  $survey_id = $_SESSION['survey-id'];

  $response = array('data' => array(), 'errors' => array());

  $errorMsg = array();

  // now check for the agreement checkbox
  if (!isset($_POST['agreement'])) {
    $errorMsg['agreement'] = 'Please read the statement next to the checkbox and check it if you agree.';
  } else if ($_POST['agreement'] != "1") {
    $errorMsg['agreement'] = 'Please read the statement next to the checkbox and check it if you agree.';
  }
  
  // now delete the survey if agreement
  if (empty($errorMsg)) {
    if (deleteSurvey($con, $survey_id)) { 
      // redirect to next page and set message
      $response['data']['delete-message'] = "Successfully deleted survey.";
    } else {
      $response['data']['delete-message'] = "Error: Could not delete survey.";
    }
    unset($_SESSION['survey-id']);

  } else {
    $response['errors'] = $errorMsg;
  }

  header("Content-Type: application/json; charset=UTF-8");
  $responseJSON = json_encode($response);
  echo $responseJSON;
  
}
// $csrf_token = createCSRFToken($con, $instructor_id);
?>