<?php
error_reporting(-1);
ini_set("display_errors", "1");
ini_set("log_errors", 1);
ini_set("error_log", "~/php-error.log");

session_start();

require_once "../lib/database.php";
require_once "../lib/constants.php";
require_once "lib/courseQueries.php";

$con = connectToDatabase();

if (!isset($_SESSION['id'])) {
    http_response_code(403);
    echo "Forbidden: You must be logged in to access this page.";
    exit();
}
$instructor_id = $_SESSION['id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['course-id'])) {
        http_response_code(400);
        echo "Bad Request: Missing parameters.";
        exit();
    }

    $course_id = intval($_POST['course-id']);

    $retVal = array("error" => "");
    $retVal["upcoming"] = array();
    $retVal["active"] = array();
    $retVal["expired"] = array();

    if (!isCourseInstructor($con, $course_id, $instructor_id)) {
        http_response_code(403);
        $error_msg = 
        $retVal["error"] = "Invalid course id provided.";
    } else {
        $courseSurveys = getSurveysFromSingleCourse($con, $course_id);
        $retVal["error"] = $courseSurveys["error"];
        $retVal["upcoming"] = $courseSurveys["upcoming"];
        $retVal["active"] = $courseSurveys["active"];
        $retVal["expired"] = $courseSurveys["expired"];
    }

    header("Content-Type: application/json; charset=UTF-8");
    $myJSON = json_encode($retVal);
    echo $myJSON;
}
?>