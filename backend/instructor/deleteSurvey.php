<?php

//error logging
error_reporting(-1); // reports all errors
ini_set("display_errors", "1"); // shows all errors
ini_set("log_errors", 1);
ini_set("error_log", "~/php-error.log");


// bring in required code
require_once "../lib/database.php";
require_once "../lib/constants.php";
require_once "lib/instructorQueries.php";
require_once "lib/courseQueries.php";
require_once "lib/surveyQueries.php";
require_once "lib/loginStatus.php";

$instructor_id = getInstructorId();

// query information about the requester
$con = connectToDatabase();

// now perform the basic checks
if($_SERVER['REQUEST_METHOD'] == 'POST') {
  if (!isset($_POST['survey_id'])) {
      http_response_code(403);
      echo "Forbidden: You must be using the application to perform this action.";
      exit();
  }
  // Check that the survey is one that the current instructor has permission to delete
  $survey_id = intval($_POST['survey_id']);
  $survey_data = getSurveyData($con, $survey_id);
    
  if (empty($survey_data) || !isSurveyInstructor($con, $survey_id, $instructor_id)) {
    http_response_code(403);
    echo "Forbidden: You must be using the application to perform this action.";
    exit();
  } 

  // now delete the survey since all is working
  $response = null;

  if (deleteEvalsForSurvey($con, $survey_id) && deleteSurvey($con, $survey_id) ) {
    // redirect to next page and set message
    $response = array('success-message' => "Successfully deleted survey.");
  } else {
    $response = array('error' => "Server Error: Could not delete survey.");
  }

  header("Content-Type: application/json; charset=UTF-8");
  $responseJSON = json_encode($response);
  echo $responseJSON;
}
?>