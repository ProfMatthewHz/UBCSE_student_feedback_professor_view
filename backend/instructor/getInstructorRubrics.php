<?php

//error logging
error_reporting(-1); // reports all errors
ini_set("display_errors", "1"); // shows all errors
ini_set("log_errors", 1);
ini_set("error_log", "~/php-error.log");

//bring in required code
require_once "../lib/database.php";
require_once "../lib/constants.php";
require_once "lib/instructorQueries.php";
require_once "lib/rubricQueries.php";
require_once "lib/rubricFormat.php";
require_once "lib/loginStatus.php";

$instructor_id = getInstructorId();

//query information about the requester
$con = connectToDatabase();

$ret_val = array();

# get all rubrics and return json
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $rubrics = getRubrics($con, $instructor_id);
    if (count($rubrics) == 0) {
        $ret_val["error"] = "There are no rubrics! Please add one.";
    } else {
        $ret_val["rubrics"] = array();
        foreach ($rubrics as $id => $desc) {
          $single_rubric = array("id" => $id, "description" => $desc);
          $ret_val["rubrics"][] = $single_rubric;
        }
    }

    header("Content-Type: application/json; charset=UTF-8");
    $responseJSON = json_encode($ret_val);
    echo $responseJSON;
	exit();
}

# post the rubric-id
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ret_val['data'] = array();
    $ret_val['errors'] = array();


    if (!isset($_POST['rubric-id'])) {
		http_response_code(400);
		echo "Bad Request: Missing parameters to select rubric";
		exit();
	}

    $rubric_id = $_POST['rubric-id'];
    $rubrics = getRubrics($con, $instructor_id);
    if (!array_key_exists($rubric_id, $rubrics)) {
        $ret_val['errors']['rubric'] = "Please choose a valid rubric.";
    }

    if (empty($ret_val['errors'])){
		// no errors, grab data
        $rubric_name = getRubricName($con, $rubric_id);
        $rubric_scores = getRubricScores($con, $rubric_id);
        $rubric_topics = getRubricTopics($con, $rubric_id);
		$rubric_data = format_rubric_data($rubric_name, $rubric_scores, $rubric_topics);
        $ret_val['data'] = $rubric_data;
    
    }

    header("Content-Type: application/json; charset=UTF-8");
    $responseJSON = json_encode($ret_val, JSON_ENCODE_OPTIONS);
    echo $responseJSON;
	exit();
}
?>
