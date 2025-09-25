<?php
error_reporting(-1); // reports all errors
ini_set("display_errors", "1"); // shows all errors
ini_set("log_errors", 1);

require "lib/constants.php";
require "lib/database.php";
require "lib/surveyQueries.php";
require "lib/reviewQueries.php";
require "lib/scoreQueries.php";
require "lib/loginStatus.php";

$student_id = getStudentId();

$con = connectToDatabase();
$responseArray = [];

header('Access-Control-Allow-Origin: '.FRONTEND_SERVER);
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');

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
    $survey_info = getSurveyResultsInfo($con, $survey, $student_id);
    if (!isset($survey_info)) {
        // This is not a valid survey for this student
        http_response_code(400);
        echo ('{"error": "Improper access to the endpoint."}');
        exit();
    }

    $evals = getEvalSources($con, $survey, $student_id);

    // Get the multiple choice questions and responses for this survey.
    $mc_topics = getSurveyMultipleChoiceTopics($con, $survey);
    $mc_answers = array();
    foreach ($mc_topics as $topic_id => $topic) {
        $mc_answers[$topic_id] = getSurveyMultipleChoiceResponses($con, $topic_id, true);
    }

    // Get the freeform questions and responses for this survey.
    $ff_topics = getSurveyFreeformTopics($con, $survey);

    // Store the scores submitted by each teammate
    $scores = array();
    $texts = array();
    foreach ($evals as $eval_info) {
        $eval_id = $eval_info['id'];
        $multiplier = $eval_info['weight'];
        $answers = getEvalPoints($con, $eval_id, $mc_topics);
        $scores[] = array("answers" => $answers, "multiplier" => $multiplier);
        $texts[] = getReviewText($con, $eval_id, $ff_topics);
    }

    // fill out the response array, criterion -> [AvgScore, Median]
    $results = []; // Initialize the array to hold our results

    foreach ($mc_topics as $topic_id => $topic) {
        $sum = 0;
        $weight = 0;
        $med_score = array();
        $max_possible = $mc_answers[$topic_id][array_key_first($mc_answers[$topic_id])][1];
        foreach ($mc_answers[$topic_id] as $response) {
            if ($response[1] > $max_possible) {
                $max_possible = $response[1];
            }
        }

        // Collect all scores for the current topic.
        foreach ($scores as $submit) {
            $response = $submit['answers'];
            $multiplier = $submit['multiplier'];
            if (isset($response[$topic_id])) {
                $sum += $response[$topic_id] * $multiplier;
                $weight = $weight + $multiplier;
                $med_score[] = $response[$topic_id];
            }
        }

        if ($weight > 0) {
            // Calculate the average score.
            $average = round($sum / $weight, 2);

            // Calculate the median score.
            sort($med_score);
            $mid_point = intdiv(count($med_score), 2);
            $median_value = $med_score[$mid_point];
            if (count($med_score) % 2 == 0) {
                // For an even count, median is the average of the two middle numbers.
                $median_value = ($med_score[$mid_point - 1] + $med_score[$mid_point]) / 2;
            }

            $median_text = "";
            foreach ($mc_answers[$topic_id] as $response) {
                if (count($med_score) % 2 == 0) {
                    if ($response[1] == $median_value || $response[1] == $med_score[$mid_point - 1]) {
                        $median_text = $response[0];
                        break;
                    }
                } else {
                    if ($response[1] == $median_value) {
                        $median_text = $response[0];
                        break;
                    }
                }
            }

            // Add the average and median text to the results array.
            $results[$topic] = ['average' => $average, 'median' => $median_text, 'maximum' => $max_possible];
        } else {
            // If there are no scores
            $results[$topic] = ['average' => null, 'median' => null, 'maximum' => null];
        }
    }
    // $results now contains your criteria as keys and [AvgScore, Median] as values
    echo json_encode($results);
}
?>