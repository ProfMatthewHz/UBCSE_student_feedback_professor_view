<?php
error_reporting(-1); // reports all errors
ini_set("display_errors", "1"); // shows all errors
ini_set("log_errors", 1);
session_start();

require "lib/constants.php";
require "lib/database.php";
require "lib/reviewQueries.php";
require "lib/surveyQueries.php";
require "lib/scoreQueries.php";

header('Content-Type: application/json');
$responseArray = [];
$id = $_SESSION['student_id'];
$con = connectToDatabase();

if(!isset($_SESSION['student_id'])) {
    header("Location: ". "https://www-student.cse.buffalo.edu/CSE442-542/2023-Fall/cse-302a/StudentSurvey/backend/unified_fake_Shibboleth.php");
    exit();
}

if(!isset($_SESSION['mc_answers'])) {
    header("Location: ". "https://www-student.cse.buffalo.edu/CSE442-542/2023-Fall/cse-302a/StudentSurvey/backend/unified_fake_Shibboleth.php");
    exit();
}

/* expected response :
review_id -> x,

    responses -> [
    TopicId 1-> score is going to be text, have a lookup table that correctly matches text with score,
    TopicId 2-> score,
    TopicId 3-> score,
    TopicId 4-> score,
    TopicId 5-> score
    ]
*/

//When submit button is pressed
$postData = json_decode(file_get_contents('php://input'), true);

if (!empty($postData)) {

    if (!isset($postData['review_id'], $postData['responses'])) {
        http_response_code(400);
        $responseArray[] = "Bad Request: Missing POST parameters";
        exit();
    }

    $review_id = $postData['review_id'];
    $review_id = filter_var($review_id, FILTER_SANITIZE_NUMBER_INT);
    $eval_id = getEvalForReview($con, $review_id);

    if (empty($eval_id)) {
        $student_scores=array();
        $eval_id = addNewEvaluation($con, $review_id);
    } else {
        // Get any existing scores
        $student_scores=getEvalScores($con, $eval_id);
    }

    $mc_answers = $_SESSION['mc_answers'];

    // response array //
    $response = $postData['responses'];
    // for each response, update or add score //
    foreach ($response as $topic_id => $score) {

        $topic_id = filter_var($topic_id, FILTER_SANITIZE_NUMBER_INT);
        $score_id = -1;
        // find score id //
        //  "mc answers":{"1":{"1":"Does not willingly assume team roles, rarely completes assigned work","2":"Usually accepts assigned
        //  team roles, occasionally completes assigned work","3":"Accepts assigned team roles, mostly completes assigned
        //  work","4":"Accepts all assigned team roles, always completes assigned work"},..
        $searchArr = $mc_answers[$topic_id];
        foreach ($searchArr as $current_id => $textVal) {
            if ($textVal == $score) {
                $score_id = $current_id;
                break;
            }
        }

        if ($score_id == -1) {
            http_response_code(400);
            $responseArray[] = "Bad Request: score text doesnt correspond to a valid score_id";
            exit();
        }

        if (array_key_exists($topic_id, $student_scores)) {
            // Update the existing score if it exists
            updateExistingScore($con, $eval_id, $topic_id, $score_id);
        } else {
            // Insert a new score if it had not existed
            insertNewScore($con, $eval_id, $topic_id, $score_id);
        }

    }


}



