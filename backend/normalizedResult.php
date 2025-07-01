<?php
error_reporting(-1); // reports all errors
ini_set("display_errors", "1"); // shows all errors
ini_set("log_errors", 1);
session_start();

require "lib/constants.php";
require "lib/database.php";
require "lib/surveyQueries.php";
require "lib/reviewQueries.php";
require "lib/scoreQueries.php";
require "instructor/lib/surveyQueries.php";
require "instructor/lib/resultsCalculations.php";

if(!isset($_SESSION['student_id'])) {
    header("Location: ".SITE_HOME."index.php"); // edit this header redirect to correct location //
    exit();
}

header('Content-Type: application/json');

$id = $_SESSION['student_id'];
$con = connectToDatabase();
$responseArray = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verify that the survey exists
    if (isset($_POST['survey'])) {
        $survey = $_POST['survey'];
    } else {
        http_response_code(400);
        echo json_encode($responseArray);
        exit();
    }
    // Verify that the survey is a valid one for this student to view their results
    $survey_info = getSurveyResultsInfo($con, $survey, $id);
    if (!isset($survey_info)) {
        // This is not a valid survey for this student
        http_response_code(400);
        echo json_encode($responseArray);
        exit();
    }

    // Retrieves the ids, names, & emails of everyone who was reviewed in this survey.
    $teammates = getReviewedData($con, $survey);

    // Get the survey results organized by the student being reviewed since this is how we actually do our calculations
    $scores = getSurveyScores($con, $survey, $teammates);

    // Averages only exist for multiple-choice topics, so that is all we get for now
    $topics = getSurveyMultipleChoiceTopics($con, $survey);

    // Retrieves the per-team records organized by reviewer
    $team_data = getReviewerPerTeamResults($con, $survey);

    // Finally, calculate the overall results for each student
    $overall = calculateFinalNormalizedScore(array_keys($teammates), $scores, $topics, $team_data);

    // Now output the results
    $ret_val = array("result" => "normalized", "data" => $overall[$id]);

    // $results now contains your criteria as keys and [AvgScore, Median] as values
    echo json_encode($ret_val);
}