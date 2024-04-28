<?php
require "lib/constants.php";
//error logging
error_reporting(-1); // reports all errors
ini_set("display_errors", "1"); // shows all errors
ini_set("log_errors", 1);
session_start();

header('Content-Type: application/json');


    require "lib/database.php";
    require "lib/scoreQueries.php";
    require "lib/resultsTable.php";
    $con = connectToDatabase();
    $course = $_SESSION['course_name'];
    $survey_name = $_SESSION['survey_name'];
    $survey_id = $_SESSION['survey_id'];
    $num_of_group_members = count($_SESSION['group_members']);
    $mc_topics = $_SESSION['mc_topics'];
    $mc_answers = $_SESSION['mc_answers'];
    $ff_topics = $_SESSION['ff_topics'];
    $members = $_SESSION['group_members'];
    $reviewer_id = $_SESSION['testReview_id'];

    // Store the multiple choice scores submitted for each teammate
    $scores = array();
    $scores[$reviewer_id] = getReviewScores($con, $reviewer_id, $mc_topics);
    echo json_encode($scores);


?>
