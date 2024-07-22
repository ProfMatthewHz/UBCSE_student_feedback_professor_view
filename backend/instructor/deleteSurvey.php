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

// now perform the basic checks
if($_SERVER['REQUEST_METHOD'] == 'POST') {
  $errorMsg = array();

  
  // now check for the agreement checkbox
  if (!isset($_POST['agreement'])) {
    $errorMsg['agreement'] = 'Please read the statement next to the checkbox and check it if you agree.';
  } else if ($_POST['agreement'] != "1") {
    $errorMsg['agreement'] = 'Please read the statement next to the checkbox and check it if you agree.';
  }
  if (!isset($_POST['survey_id'])) {
    $errorMsg['survey'] = "Please choose a valid survey";
  } else {
    // Check that the survey is one that the current instructor has permission to delete
    $survey_id = intval($_POST['survey_id']);
    $survey_data = getSurveyData($con, $survey_id);
    
    if (empty($survey_data) || !isSurveyInstructor($con, $survey_id, $instructor_id)) {
      $errorMsg['survey'] = "Please choose a valid survey";
    }
  }
  // now delete the survey if agreement
  if (empty($errorMsg)) {
    if (deleteSurvey($con, $survey_id)) { 
      // redirect to next page and set message
      $response['success-message'] = "Successfully deleted survey.";
    } else {
      $response['errors'] = "Server Error: Could not delete survey.";
    }
  } else {
    $response['errors'] = $errorMsg;
  }

  header("Content-Type: application/json; charset=UTF-8");
  $responseJSON = json_encode($response);
  echo $responseJSON;
}
?>