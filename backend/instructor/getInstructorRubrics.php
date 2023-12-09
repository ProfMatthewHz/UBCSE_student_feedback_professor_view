<?php

function create_levels_array($rubric_scores) {
	$levels = array();
	// levels[i] = array("name" => [name], "score" => [score])

	foreach ($rubric_scores as $score_id => $level_data){
		// $level_data['level_id'] = $score_id;
		$levels[] = $level_data;
	}

	return $levels;
}

function create_topics_array($rubric_topics) {
	// akin to criterions

	$topics = array();
	foreach( $rubric_topics as $topic ){

		$single_criterion = array();

		$criterion_name = $topic['question'];
		$criterion_responses = $topic['responses'];
		$criterion_type = $topic['type'];

		$single_criterion['question'] = $criterion_name;
		$single_criterion['responses'] = array_values($criterion_responses);
		$single_criterion['type'] = $criterion_type;

		$topics[] = $single_criterion;
	}

	return $topics;
}

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
require_once "lib/instructorQueries.php";
require_once "lib/rubricQueries.php";

//query information about the requester
$con = connectToDatabase();

//try to get information about the instructor who made this request by checking the session token and redirecting if invalid
if (!isset($_SESSION['id'])) {
  http_response_code(403);
  echo "Forbidden: You must be logged in to access this page.";
  exit();
}
$instructor_id = $_SESSION['id'];

$response = array();
// will be the response to return, depending on HTTP request

# get all rubrics and return json
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $rubrics = getRubrics($con);

    $rubrics_array = array();
    foreach ($rubrics as $id => $desc) {
        $single_rubric = array("id" => $id, "description" => $desc);
        $rubrics_array[] = $single_rubric;
    }
    unset($id, $desc);

    header("Content-Type: application/json; charset=UTF-8");
    $response = $rubrics_array;
    $responseJSON = json_encode($response);
    echo $responseJSON;

	exit();

    
}

# post the rubric-id
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $response['data'] = array();
    $response['errors'] = array();


    if (!isset($_POST['rubric-id'])) {
		http_response_code(400);
		echo "Bad Request: Missing parameters to select rubric";
		exit();
	}

    $rubric_id = $_POST['rubric-id'];
    $rubrics = getRubrics($con);
    if (!array_key_exists($rubric_id, $rubrics)) {

        $response['errors']['rubric'] = "Please choose a valid rubric.";

    }

    if (empty($response['errors'])){

		$rubric_data = array();

        $rubric_name = getRubricName($con, $rubric_id);
        $rubric_scores = getRubricScores($con, $rubric_id);
        $rubric_topics = getRubricTopics($con, $rubric_id);
		
		$levels_data = create_levels_array($rubric_scores);
        $topics_data = create_topics_array($rubric_topics);
    
        $rubric_data = array();
        $rubric_data['name'] = $rubric_name;
        $rubric_data['levels'] = $levels_data;
        $rubric_data['topics'] = $topics_data;

        $response['data'] = $rubric_data;
    
    }

    header("Content-Type: application/json; charset=UTF-8");
    $responseJSON = json_encode($response, JSON_ENCODE_OPTIONS);
    echo $responseJSON;
	exit();

}




?>
