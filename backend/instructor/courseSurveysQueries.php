<?php
//error logging
error_reporting(-1); // reports all errors
ini_set("display_errors", "1"); // shows all errors
ini_set("log_errors", 1);
ini_set("error_log", "~/php-error.log");

// start the session variable
session_start();

//bring in required code
require_once "../lib/database.php";
require_once "../lib/constants.php";
require_once "lib/courseQueries.php";

//query information about the requester
$con = connectToDatabase();

//try to get information about the instructor who made this request by checking the session token and redirecting if invalid
if (!isset($_SESSION['id'])) {
  http_response_code(403);
  echo "Forbidden: You must be logged in to access this page.";
  exit();
}
$instructor_id = $_SESSION['id'];

if($_SERVER['REQUEST_METHOD'] == 'POST') {

    // parameters will be $course_id
    if (!isset($_POST['course-id'])){
        http_response_code(400);
        echo "Bad Request: Missing parameters.";
        exit();
    }

    // Get the course id of the course that is being queried for surveys
    $course_id = intval($_POST['course-id']);

    $retVal = array("error" => "");
    $retVal["upcoming"] = array();
    $retVal["active"] = array();
    $retVal["expired"] = array();

    if (!isCourseInstructor($con, $course_id, $instructor_id)) {
        http_response_code(403);
        echo "403: Forbidden.";
        
        $error_msg = "Instructor [". $instructor_id . "] does not teach Course: [" . $course_id . "]";
        $retVal["error"] = $error_msg;
      
    } else {


        $courseSurveys = getSurveysFromSingleCourse($con, $course_id);
        
        $retVal["error"] = $courseSurveys["error"];
        $retVal["upcoming"] = $courseSurveys["upcoming"];
        $retVal["active"] = $courseSurveys["active"];
        $retVal["expired"] = $courseSurveys["expired"];
    }

  header("Content-Type: application/json; charset=UTF-8");

  // Now lets dump the data we found
  $myJSON = json_encode($retVal);

  echo $myJSON;
}
    
?>