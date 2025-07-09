<?php
//error logging
error_reporting(-1); // reports all errors
ini_set("display_errors", ""); // shows all errors
ini_set("log_errors", 1);
ini_set("error_log", "~/php-error.log");

// bring in required code
require_once "../lib/database.php";
require_once "../lib/constants.php";
require_once "../lib/surveyQueries.php";
require_once "lib/courseQueries.php";
require_once "lib/surveyQueries.php";
require_once "lib/scoreQueries.php";
require_once "lib/resultsCalculations.php";
require_once "lib/resultsFunctions.php";
require_once "lib/reviewQueries.php";
require_once "lib/loginStatus.php";

$instructor_id = getInstructorId();

// query information about the requester
$con = connectToDatabase();

header("Content-Type: application/json; charset=UTF-8");

// respond not found on no query string parameters
$survey_id = NULL;
if ((!isset($_POST['survey'])) || (!isset($_POST['type']))) {
  http_response_code(400);
  $json_out = json_encode(array("error" => "Forbidden: Access is only allowed through the application."));
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
if (!isSurveyInstructor($con, $survey_id, $instructor_id)) {
  http_response_code(403);
  $json_out = json_encode(array("error" => "Forbidden: Access is only allowed through the application."));
  echo $json_out;
  exit();
}
$use_team_scores = ($survey_info['survey_type_id'] === 6);

$results_wanted = $_POST['type'];

// Check if we are just getting survey completion data
if ($results_wanted === 'completion') {
  $results = getCompletionResults($con, $survey_id);
  $json_encode = json_encode($results);
  echo $json_encode;
  exit();
} else if ($results_wanted === 'raw-full') {
  // Get the scores from all of the evaluations that were completed in this survey
  $scores = getEvalCriterionScores($con, $survey_id);
  // Do the work needed to get the normalized score of each evaluation
  $normalized_total = getEvalNormalizedScores($con, $survey_id, $use_team_scores);
  // Now get the student information for each eval
  $eval_data = getEvalInformation($con, $survey_id, $use_team_scores);
  // Get the names of the topics that were evaluated in this survey
  $topics = getSurveyMultipleChoiceTopics($con, $survey_id);
  // Zip all this data together as a single array ready for CSV output
  $results = createRawDataResult($eval_data, $scores, $normalized_total, $topics);
  // Now output the results
  $json_results = json_encode($results);
  echo $json_results;
  exit();
} else if ($results_wanted === 'individual') {
  // Get the topics that were evaluated in this survey
  $topics = getSurveyMultipleChoiceTopics($con, $survey_id);
  // Get the scores for each evaluation in the study
  $scores = getEvalCriterionScores($con, $survey_id);
  // Get the list of evaluations that should be included in the calculations
  $eval_info = getValidEvalsOfStudentByTeam($con, $survey_id);
  // Calculate the averages for each student in the course
  $averages = calculateAllCriterionAverages($eval_info['valid_evals'], $scores, $topics);
  // Retrieves the ids, names, & emails of everyone who was reviewed in this survey.
  $teammates = getReviewedData($con, $survey_id);
  // Now generate the array of results to output
  $results = createIndividualAverageResult($teammates, $averages, $topics);
  // And output the results
  $json_results = json_encode($results);
  echo $json_results;
  exit();
} else if ($results_wanted === 'average') {
  // Get the scores from all of the evaluations that were completed in this survey
  $eval_totals = getEvalsTotalPoints($con, $survey_id);
  // Get the list of evaluations that should be included in the calculations
  $eval_info = getValidEvalsOfStudentByTeam($con, $survey_id);
  // Get reviewers' total points for each team
  $reviewer_totals = getReviewersTotalPoints($con, $survey_id, $use_team_scores);
  // Do the work needed to get the normalized score of each evaluation
  $normalized_averages = calculateAllNormalizedAverages($eval_info['valid_evals'], $eval_totals, $reviewer_totals);
  // Now get the student information for each eval
  $teammates = getReviewedData($con, $survey_id);
  // Get the names of the topics that were evaluated in this survey
  $result_views = getReviewerResultViewsCount($con, $survey_id);
  // Get the names of the topics that were evaluated in this survey
  // Zip all this data together as a single array ready for CSV output
  $results = createNormalizedAveragesResult($teammates, $normalized_averages, $result_views);
  // Now output the results
  $json_results = json_encode($results);
  echo $json_results;
  exit();
} else {
  http_response_code(404);
  $json_out = json_encode(array("error" => "Unknown request: Request is for unknown results format."));
  echo $json_out;
  exit();
}
?>