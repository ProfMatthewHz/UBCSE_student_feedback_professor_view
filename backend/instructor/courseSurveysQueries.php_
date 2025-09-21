<?php
error_reporting(-1);
ini_set("display_errors", "1");
ini_set("log_errors", 1);
ini_set("error_log", "~/php-error.log");

require "../lib/database.php";
require "../lib/constants.php";
require "lib/courseQueries.php";
require "lib/loginStatus.php";

$instructor_id = getInstructorId();

$con = connectToDatabase();

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