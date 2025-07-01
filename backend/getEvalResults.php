<?php
require "lib/constants.php";
require "lib/database.php";
require "lib/scoreQueries.php";
require "lib/studentQueries.php";

//error logging
error_reporting(-1); // reports all errors
ini_set("display_errors", "1"); // shows all errors
ini_set("log_errors", 1);
session_start();

if (!isset($_SESSION['student_id']) || !isset($_POST) || !isset($_POST['reviewed'])) {
    http_response_code(400);
    echo json_encode(array('error' => 'Bad request: Request only valid from within app'));
    exit();
} else {
    $reviewed = $_POST['reviewed'];
    
    $con = connectToDatabase();
    if (!isStudentsEval($con, $reviewed, $_SESSION['student_id'])) {
        http_response_code(403);
        echo json_encode(array('error' => 'Forbidden: You are not allowed to view this review.'));
        exit();
    }
    $obj = getReviewScores($con, $reviewed);
    echo json_encode($obj);
}
exit();