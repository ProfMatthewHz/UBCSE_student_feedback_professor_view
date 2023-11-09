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
  echo "Forbidden: You must be logged in to access this page.";
  exit();
}
$instructor_id = $_SESSION['id'];

// respond not found on no query string parameters
$survey_id = NULL;
if ((!isset($_POST['survey'])) || (!isset($_POST['type']))) {
  http_response_code(400);
  echo "400: Improper request made.";
  exit();
}

// make sure the type query is one of the valid types. if not, respond not found
if ($_POST['type'] !== 'raw-full' && $_POST['type'] !== 'individual' && $_POST['type'] !== 'average') {
  http_response_code(404);
  echo "404: Not found.";
  exit();
}

// make sure the query string is an integer, reply 404 otherwise
$survey_id = intval($_POST['survey']);

if ($survey_id === 0) {
  http_response_code(404);
  echo "404: Not found.";
  exit();
}

// Look up info about the requested survey
$survey_info = getSurveyData($con, $survey_id);
if (empty($survey_info)) {
  http_response_code(404);
  echo "404: Not found.";
  exit();
}

// make sure the survey is for a course the current instructor actually teaches
if (!isCourseInstructor($con, $survey_info['course_id'], $instructor_id)) {
  http_response_code(403);
  echo "403: Forbidden.";
  exit();
}

// Retrieves the ids, names, & emails of everyone who was reviewed in this survey.
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
$filename = NULL;
// now generate the raw scores output
if ($_POST['type'] === 'individual') {
  $results = getIndividualsAverages($teammates, $scores, $topics);
  $filename = 'survey-' . $survey_id . '-individual-averages.csv';
} else if ($_POST['type'] === 'raw-full') {
  $results = getRawResults($teammates, $scores, $topics, $reviewers, $team_data);
  $filename = 'survey-' . $survey_id . '-raw-results.csv';
} else {
  $results = getFinalResults($teammates, $scores, $topics, $team_data);
  $filename = 'survey-' . $survey_id . '-normalized-averages.csv';
}
// Now output the results
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
$out = fopen('php://output', 'w');
foreach ($results as $line) {
  fputcsv($out, $line);
}
fclose($out);
?>
