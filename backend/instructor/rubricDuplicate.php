<?php

error_reporting(-1); // reports all errors
ini_set("display_errors", "1"); // shows all errors
ini_set("log_errors", 1);
ini_set("error_log", "~/php-error.log");

//start the session variable
session_start();

//bring in required code
require_once "../lib/database.php";
require_once "../lib/constants.php";
require_once "lib/instructorQueries.php";
require_once "lib/rubricQueries.php";
require_once "lib/rubricFormat.php";

//query information about the requester
$con = connectToDatabase();

//try to get information about the instructor who made this request by checking the session token and redirecting if invalid
if (!isset($_SESSION['id'])) {
  http_response_code(403);
  echo "Forbidden: You must be logged in to access this page.";
  exit();
}
$instructor_id = $_SESSION['id'];

//stores error messages corresponding to form field
$errorMsg = array();


# post the rubric-id
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $response['data'] = array();
  $response['errors'] = array();


  if (!isset($_POST['rubric-id'])) {
    http_response_code(400);
    echo "Bad Request: Missing parameters to select rubric";
    exit();
  }

  $rubric_id = $_POST['rubric-id'];
  $rubrics = getRubrics($con);
  if (!array_key_exists($rubric_id, $rubrics)) {
    $response['errors']['rubric'] = "Please choose a valid rubric.";
  }

  if (empty($response['errors'])){
    // no errors, grab data

    $rubric_name = getRubricName($con, $rubric_id);
    $rubric_scores = getRubricScores($con, $rubric_id);
    $rubric_topics = getRubricTopics($con, $rubric_id);

    $rubric_name = "Duplicate of ".$rubric_name;

    $rubric_data = format_rubric_data($rubric_name, $rubric_scores, $rubric_topics);

    $response['data'] = $rubric_data;
  
  }

  header("Content-Type: application/json; charset=UTF-8");
  $responseJSON = json_encode($response, JSON_ENCODE_OPTIONS);
  echo $responseJSON;
  exit();
}

?>