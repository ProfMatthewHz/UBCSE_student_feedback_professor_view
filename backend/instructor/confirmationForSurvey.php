<?php
//error logging
error_reporting(-1); // reports all errors
ini_set("display_errors", "1"); // shows all errors
ini_set("log_errors", 1);
ini_set("error_log", "~/php-error.log");

//start the session variable
session_start();

//bring in required code
require_once "../lib/database.php";
require_once "../lib/constants.php";
require_once "../lib/studentQueries.php";
require_once "lib/rubricQueries.php";
require_once "lib/courseQueries.php";
require_once "lib/surveyQueries.php";
require_once "lib/reviewQueries.php";
require_once "lib/enrollmentFunctions.php";
require_once "lib/fileParse.php";
require_once "lib/pairingFunctions.php";

//query information about the requester
$con = connectToDatabase();

//try to get information about the instructor who made this request by checking the session token and redirecting if invalid
if (!isset($_SESSION['id'])) {
  http_response_code(403);
  echo json_encode(array("error" => "Forbidden: You must be logged in to access this page."));
  exit();
}
$instructor_id = $_SESSION['id'];


// Get the pairings we will be using in this survey
if (!isset($_SESSION["survey_data"]) || !isset($_SESSION["survey_course_id"]) || 
    !isset($_SESSION["survey_file"]) || !isset($_SESSION["survey_students"]))  {
    http_response_code(403);
    echo json_encode(array("error" => "Incorrect usage: This file must be used within app."));
    exit();
}
// Get the data we previously created
$file_data = $_SESSION["survey_file"];
// Get the survey data we will work with
$survey_data = $_SESSION["survey_data"];
// Get the course id of the course whose roster is being updated
$course_id = $_SESSION['survey_course_id'];
// Get information about the students in this course
$survey_students = $_SESSION["survey_students"];
// Break out the information about the survey
$survey_name = $survey_data['name'];
$survey_s = $survey_data['start'];
$survey_begin = $survey_s->format('M j').' at '. $survey_s->format('g:i A');
$survey_e = $survey_data['end'];
$survey_end = $survey_e->format('M j').' at '. $survey_e->format('g:i A');
$survey_type = $survey_data['pairing_mode'];
$pm_mult = $survey_data['multiplier'];
$rubric_id = $survey_data['rubric'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $response = array('data' => array(), 'errors' => array());

  if (isset($_POST["cancel-survey"]) || isset($_POST["return-survey"])) {
    $response['data']['message'] = "Survey submission cancelled!";
    http_response_code(200);

  } elseif (isset($_POST['save-survey'])) {
    $survey_id = insertSurvey($con, $course_id, $survey_name, $survey_s, $survey_e, $rubric_id, $survey_type);
    
    $addSuccess = addReviewsToSurvey($con, $survey_id, $_SESSION['pairings']);

    if ($addSuccess){
      $response['data']['message'] = "Successfully created survey and added reviews to the survey";
    } else {
      $response["errors"]['message'] = "Unsuccessful in adding reviews to the survey :( ";
    }

    http_response_code(200);
  } 

  unset($_SESSION["survey_file"]);
  unset($_SESSION["survey_data"]);
  unset($_SESSION["survey_course_id"]);
  unset($_SESSION["survey_students"]);
  unset($_SESSION["pairings"]);
  header("Content-Type: application/json; charset=UTF-8");
  $responseJSON = json_encode($response);
  echo $responseJSON;
}

