<?php

//error logging
error_reporting(-1); // reports all errors
ini_set("display_errors", ""); // shows all errors
ini_set("log_errors", 1);
ini_set("error_log", "~/php-error.log");

// start the session variable
session_start();

// bring in required code
require_once "../lib/database.php";
require_once "../lib/constants.php";
require_once "../lib/surveyQueries.php";
require_once "lib/courseQueries.php";
require_once "lib/surveyQueries.php";
require_once "lib/resultsCalculations.php";
require_once "lib/resultsFunctions.php";

// query information about the requester
$con = connectToDatabase();

//try to get information about the instructor who made this request by checking the session token and redirecting if invalid
if (!isset($_SESSION['id'])) {
  http_response_code(403);
  $json_out = json_encode(array("error" => "Forbidden: Access is only allowed through the application."));
  echo $json_out;
  exit();
}
$instructor_id = $_SESSION['id'];

// respond not found on no query string parameters
$survey_id = NULL;
if ((!isset($_POST['survey'])) || (!isset($_POST['type']))) {
  http_response_code(400);
  $json_out = json_encode(array("error" => "Forbidden: Access is only allowed through the application."));
  echo $json_out;
  exit();
}

// make sure the type query is one of the valid types. if not, respond not found
if ($_POST['type'] !== 'raw-full' && $_POST['type'] !== 'individual' && $_POST['type'] !== 'average' && $_POST['type'] !== 'completion') {
  http_response_code(404);
  $json_out = json_encode(array("error" => "Unknown request: Request is for unknown results format."));
  echo $json_out;
  exit();
}

// make sure the query string is an integer, reply 404 otherwise
$survey_id = intval($_POST['survey']);

if ($survey_id === 0) {
  http_response_code(404);
  $json_out = json_encode(array("error" => "Forbidden: Access is only allowed through the application."));
  echo $json_out;
  exit();
}

// Look up info about the requested survey
$survey_info = getSurveyData($con, $survey_id);
if (empty($survey_info)) {
  http_response_code(404);
  $json_out = json_encode(array("error" => "Forbidden: Access is only allowed through the application."));
  echo $json_out;
  exit();
}

// make sure the survey is for a course the current instructor actually teaches
if (!isCourseInstructor($con, $survey_info['course_id'], $instructor_id)) {
  http_response_code(403);
  $json_out = json_encode(array("error" => "Forbidden: Access is only allowed through the application."));
  echo $json_out;
  exit();
}

// Check if we are just getting survey completion data
if ($_POST['type'] === 'completion') {
  $results = getReviewerCompletionResults($con, $survey_id);
  $json_encode = json_encode($results);
  echo $json_encode;
  exit();
} else {
  // Retrieves he ids, names, & emails of everyone who was reviewed in this survey.
  $teammates = getReviewedData($con, $survey_id);

  // Get the survey results organized by the student being reviewed since this is how we actually do our calculations
  $scores = getSurveyScores($con, $survey_id, $teammates);

  // Averages only exist for multiple-choice topics, so that is all we get for now
  $topics = getSurveyMultipleChoiceTopics($con, $survey_id);

  // Retrieves the ids, names, & emails of everyone who was a reviewer in this survey.
  $reviewers = getReviewerData($con, $survey_id);

  // Retrieves the per-team records organized by reviewer
  $team_data = getReviewerPerTeamResults($con, $survey_id);

  $results = NULL;
  // now generate the raw scores output
  if ($_POST['type'] === 'individual') {
    $results = getIndividualsAverages($teammates, $scores, $topics);
  } else if ($_POST['type'] === 'raw-full') {
    $results = getRawResults($teammates, $scores, $topics, $reviewers, $team_data);
  } else {
    $views = getReviewerResultReviewsCount($con, $survey_id);
    $results = getNormalizedResults($teammates, $scores, $topics, $team_data, $views);
  }
  // Now output the results
  header("Content-Type: application/json; charset=UTF-8");
  $json_results = json_encode($results);
  echo $json_results;
}
?>