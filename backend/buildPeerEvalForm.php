<?php
error_reporting(-1); // reports all errors
ini_set("display_errors", "1"); // shows all errors
ini_set("log_errors", 1);
session_start();

require "lib/constants.php";
require "lib/database.php";
require "lib/reviewQueries.php";
require "lib/studentQueries.php";
require "lib/surveyQueries.php";
require "lib/scoreQueries.php";

header('Content-Type: application/json');

if(!isset($_SESSION['student_id']) || !isset($_SESSION['mc_answers'])) {
    http_response_code(400);
    echo json_encode(array('error' => 'Bad request: Request only valid from within app'));
    exit();
}

$responseArray = [];
$id = $_SESSION['student_id'];
$con = connectToDatabase();

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
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    http_response_code(403);
    echo json_encode(array('error' => 'Bad request: Missing required POST data'));
    exit();
}

if (empty($_POST['eval_id']) || empty($_POST['responses'])) {
    http_response_code(403);
    echo json_encode(array('error' => 'Bad request: Missing required POST data'));
    exit();
}

$eval_id = $_POST['eval_id'];
$eval_id = filter_var($eval_id, FILTER_SANITIZE_NUMBER_INT);

if (empty($eval_id) || !isStudentsEval($con, $eval_id, $id)) {
    http_response_code(403);
    echo json_encode(array('error' => 'Bad request: Missing or incorrect POST data'));
    exit();
}

// Get any existing scores
$student_scores = getEvalScores($con, $eval_id);
$mc_answers = $_SESSION['mc_answers'];

// response array
$response = json_decode($_POST['responses']);

// for each response, update or add score
foreach ($response as $topic_id => $score_id) {
    $topic_id = filter_var($topic_id, FILTER_SANITIZE_NUMBER_INT);
    $score_id = filter_var($score_id, FILTER_SANITIZE_NUMBER_INT);

    if ($score_id == 0) {
        http_response_code(400);
        echo json_encode(array('error' => 'Bad request: Request only valid from within app'));
        exit();
    }
    if (array_key_exists($topic_id, $student_scores) && array_key_exists($score_id, $mc_answers[$topic_id])) {
        // Update the existing score if it exists
        $result = updateExistingScore($con, $eval_id, $topic_id, $score_id);
        if (!$result) {
            http_response_code(500);
            echo json_encode('Oops. With eval '.$eval_id.' and topic '.$topic_id.' for score '.$score_id.' something went wrong. Please try again later.');
            exit();
        }
    } else {
        // Insert a new score if it had not existed
        insertNewScore($con, $eval_id, $topic_id, $score_id);
        $student_scores[$topic_id] = $score_id;
    }
}
// Update the evals table to mark it as completed
$result = markEvaluationCompleted($con, $eval_id);
echo json_encode(array("success" => $result));
?>
