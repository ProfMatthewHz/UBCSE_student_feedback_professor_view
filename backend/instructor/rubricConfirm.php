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
require_once "lib/rubricQueries.php";
require_once "lib/rubricTable.php";

//query information about the requester
$con = connectToDatabase();

//try to get information about the instructor who made this request by checking the session token and redirecting if invalid
if (!isset($_SESSION['id'])) {
  http_response_code(403);
  echo "Forbidden: You must be logged in to access this page.";
  exit();
}
$instructor_id = $_SESSION['id'];

// Verify we have already defined the rubric in total
if (!isset($_SESSION["rubric-preview"])) { 
  http_response_code(400);
  $json_out = json_encode(array("errors" => "Bad Request: Missing information to save rubric"));
  echo $json_out;
  exit();
}

$rubric_name = $_SESSION['rubric-preview']['name'];
$rubric_levels = $_SESSION['rubric-preview']['levels'];
$rubric_criteria = $_SESSION['rubric-preview']['criteria'];


if ($_SERVER['REQUEST_METHOD'] == 'POST') {

  if (!isset($_POST['save-rubric'])){
    http_response_code(400);
    $json_out = json_encode(array("errors" => "Bad Request: Missing parameters to save rubric"));
    echo $json_out;
    exit();
  }

  $save_rubric = $_POST['save-rubric'];

  // for some reason, we should check that this was intended to be saved
  if ($save_rubric) {
    // Add the rubric to the database and keep track of the id it was assigned. 
    $rubric_id = insertRubric($con, $rubric_name);

    // add levels data into db and keep track of their score ids
    $levels_ids = array();
    foreach ($rubric_levels as $level_id => $level_data){

      $level_name = $level_data['name'];
      $level_score = $level_data['score'];

      $score_id = insertRubricScore($con, $rubric_id, $level_name, $level_score);

      $levels_id[$level_id] = $score_id;
    }

    // add levels data into db and keep track of their topic ids
    // Then, add responses to database
    foreach ($rubric_criteria as $criterion_id => $criterion_data){
      $criterion_name = $criterion_data['name'];
      $criterion_responses = $criterion_data['responses'];
      $criterion_type = $criterion_data['type'];

      $topic_id = insertRubricTopic($con, $rubric_id, $criterion_name, $criterion_type);

      if ($criterion_type == MC_QUESTION_TYPE){
        // responses field should be sorted from level-0 to the last level
        foreach ($criterion_responses as $response_id => $response_description){
            
          $curr_level = "level-".$response_id;
          $level_id = $levels_id[$curr_level];

          insertRubricResponse($con, $topic_id, $level_id, $response_description);

        }
      }
    }
    unset($_SESSION['rubric-format'], $_SESSION['rubric-preview']);
  }
}
?>
