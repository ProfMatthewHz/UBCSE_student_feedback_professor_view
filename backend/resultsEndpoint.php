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

if(!isset($_SESSION['student_id'])) {
    header("Location: ".SITE_HOME."index.php"); // edit this header redirect to correct location //
    exit();
}

// Validate CSRF token early in the script, this is for deployement
// if (!isset($_SESSION['csrf_token'])) {
//     http_response_code(403);
//     echo json_encode(["error" => "CSRF token validation failed."]);
//     exit();
// }
// print($_SESSION['csrf_token']);

header('Content-Type: application/json');

$id = $_SESSION['student_id'];
$con = connectToDatabase();
$responseArray = [];

if ($_SERVER['REQUEST_METHOD'] == 'GET') {

//    if (!isset($_GET['csrf_token']) || $_SESSION['csrf_token'] !== $_GET['csrf_token']) {
//        http_response_code(403);
//        echo "CSRF token validation failed.";
//        exit();
//    }

// Verify that the survey exists
    if (isset($_GET['survey'])) {
        $survey = $_GET['survey'];
    } else {
        http_response_code(400);
        echo json_encode($responseArray);
        exit();
    }
// Verify that the survey is a valid one for this student to view their results
    $survey_info = getSurveyResultsInfo($con, $survey, $id);
    if (isset($survey_info)) {
        foreach ($survey_info as $key => $value) {
            $_SESSION[$key] = $value;
        }
    } else {
        // This is not a valid survey for this student
        http_response_code(400);
        echo json_encode($responseArray);
        exit();
    }

    $_SESSION['reviewers'] = getReviewSources($con, $survey, $id);

    // Get the multiple choice questions and responses for this survey.
    $_SESSION['mc_topics'] = getSurveyMultipleChoiceTopics($con, $survey);
    $_SESSION['mc_answers'] = array();
    foreach ($_SESSION['mc_topics'] as $topic_id => $topic) {
        $_SESSION['mc_answers'][$topic_id] = getSurveyMultipleChoiceResponses($con, $topic_id, true);
    }

    // Get the freeform questions and responses for this survey.
    $_SESSION['ff_topics'] = getSurveyFreeformTopics($con, $survey);

    $course = $_SESSION['course_name'];
    $survey_name = $_SESSION['survey_name'];
    $survey_id = $_SESSION['survey_id'];
    $mc_topics = $_SESSION['mc_topics'];
    $mc_answers = $_SESSION['mc_answers'];
    $ff_topics = $_SESSION['ff_topics'];
    $reviewers = $_SESSION['reviewers'];

    // Store the scores submitted by each teammate
    $scores = array();
    $texts = array();
    foreach ($reviewers as $reviewer_id) {
        $scores[] = getReviewPoints($con, $reviewer_id, $mc_topics);
        $texts[] = getReviewText($con, $reviewer_id, $ff_topics);
    }

    // fill out the response array, criterion -> [AvgScore, Median]
    $results = []; // Initialize the array to hold our results

    foreach ($mc_topics as $topic_id => $topic) {
        $sum = 0;
        $count = 0;
        $med_score = array();

        // Collect all scores for the current topic.
        foreach ($scores as $submit) {
            if (isset($submit[$topic_id])) {
                $sum += $submit[$topic_id];
                $count++;
                $med_score[] = $submit[$topic_id];
            }
        }

        if ($count > 0) {
            // Calculate the average score.
            $average = round($sum / $count, 2);

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
            $results[$topic] = ['average' => $average, 'median' => $median_text];
        } else {
            // If there are no scores
            $results[$topic] = ['average' => null, 'median' => null];
        }
    }

// $results now contains your criteria as keys and [AvgScore, Median] as values
    echo json_encode($results);
}



