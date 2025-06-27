<?php
require "lib/constants.php";
require "lib/database.php";
require "lib/scoreQueries.php";

//error logging
error_reporting(-1); // reports all errors
ini_set("display_errors", "1"); // shows all errors
ini_set("log_errors", 1);
session_start();

if (!isset($_SESSION['student_id']) || !isset($_SESSION['survey_id']) || !isset($_SESSION['course_name']) || 
    !isset($_SESSION['survey_name']) || !isset($_SESSION['group_members']) ||
    !isset($_SESSION['mc_topics']) || !isset($_SESSION['mc_answers']) || !isset($_SESSION['ff_topics'])) {
    http_response_code(400);
    echo json_encode(array('error' => 'Bad request: Request only valid from within app'));
    exit();
} else {
    if (!empty($_POST) && isset($_POST['reviewed'])) {
        $reviewed = $_POST['reviewed'];
        
        $con = connectToDatabase();
        $course = $_SESSION['course_name'];
        $survey_name = $_SESSION['survey_name'];
        $survey_id = $_SESSION['survey_id'];
        $num_of_group_members = count($_SESSION['group_members']);
        $mc_topics = $_SESSION['mc_topics'];
        $mc_answers = $_SESSION['mc_answers'];
        $ff_topics = $_SESSION['ff_topics'];
        $members = $_SESSION['group_members'];

        // Store the scores submitted for each teammate
        $scores = array();
        $texts = array();
        foreach ($members as $reviewer_id => $name) {
            $scores[$reviewer_id] = getReviewScores($con, $reviewer_id, $mc_topics);
            $texts[$reviewer_id] = getReviewText($con, $reviewer_id, $ff_topics);
        }
        $data = array();
        if(!empty($scores)) {
            $obj = $scores[$reviewed];
            foreach ($obj as $top_id => $answer) {
                $data[$top_id] = $mc_answers[$top_id][$answer];
            }
        }
        echo json_encode($data);
    }
}
exit();