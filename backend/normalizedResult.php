<?php
error_reporting(-1); // reports all errors
ini_set("display_errors", "1"); // shows all errors
ini_set("log_errors", 1);

require "lib/constants.php";
require "lib/database.php";
require "lib/surveyQueries.php";
require "lib/reviewQueries.php";
require "lib/scoreQueries.php";
require "instructor/lib/scoreQueries.php";
require "instructor/lib/surveyQueries.php";
require "instructor/lib/reviewQueries.php";
require "instructor/lib/resultsCalculations.php";
require "lib/loginRoutine.php";

$student_id = getStudentId();

header('Content-Type: application/json');

$con = connectToDatabase();
$responseArray = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verify that the survey exists
    if (isset($_POST['survey'])) {
        $survey = $_POST['survey'];
    } else {
        http_response_code(400);
        echo ('{"error": "Should be using the frontend to access the endpoint."}');
        exit();
    }
    // Verify that the survey is a valid one for this student to view their results
    $survey_info = getSurveyResultsInfo($con, $survey, $student_id);
    if (!isset($survey_info)) {
        // This is not a valid survey for this student
        http_response_code(400);
        echo json_encode($responseArray);
        exit();
    }

    $use_team_scores = ($survey_info['survey_type_id'] === 6);

    // Get the scores from all of the evaluations that were completed in this survey
    $eval_totals = getEvalsTotalPoints($con, $survey_id);
    // Get the list of evaluations that should be included in the calculations
    $eval_info = getValidEvalsOfStudentByTeam($con, $survey_id);
    // Get reviewers' total points for each team
    $reviewer_totals = getReviewersTotalPoints($con, $survey_id, $use_team_scores);
    // Do the work needed to get the normalized score of each evaluation
    $normalized_averages = calculateAllNormalizedAverages($eval_info['valid_evals'], $eval_totals, $reviewer_totals);

    // Now output the results
    $ret_val = array("result" => "normalized", "data" => $normalized_averages[$id]);

    // $results now contains your criteria as keys and [AvgScore, Median] as values
    echo json_encode($ret_val);
    exit();
}
?>
