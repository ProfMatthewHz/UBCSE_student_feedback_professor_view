<?php
error_reporting(-1); // reports all errors
ini_set("display_errors", "1"); // shows all errors
ini_set("log_errors", 1);
ini_set("error_log", "~/php-error.log");

//bring in required code
require "../lib/database.php";
require "../lib/constants.php";
require "lib/rubricQueries.php";
require "lib/rubricFormat.php";
require "lib/loginStatus.php";

$instructor_id = getInstructorId();

//query information about the requester
$con = connectToDatabase();

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
  $rubrics = getRubrics($con, $instructor_id);
  if (!array_key_exists($rubric_id, $rubrics)) {
    $response['errors']['rubric'] = "Please choose a valid rubric.";
  }

  if (empty($response['errors'])){
    // no errors, grab data

    $rubric_name = getRubricName($con, $rubric_id);
    $rubric_scores = getRubricScores($con, $rubric_id);
    $rubric_topics = getRubricTopics($con, $rubric_id);

    $rubric_name = $rubric_name." copy";

    $num_existing = intval(countRubricNames($con, $rubric_name));
    if ( $num_existing ){
      $duplicate_num = "(".$num_existing.")";
      $rubric_name = $rubric_name." ".$duplicate_num;
    }

    $rubric_data = format_rubric_data($rubric_name, $rubric_scores, $rubric_topics);

    $response['data'] = $rubric_data;

  }

  header("Content-Type: application/json; charset=UTF-8");
  $responseJSON = json_encode($response, JSON_ENCODE_OPTIONS);
  echo $responseJSON;
  exit();
}
?>