<?php
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
require_once "../lib/studentQueries.php";
require_once "lib/rubricQueries.php";
require_once "lib/courseQueries.php";
require_once "lib/surveyQueries.php";
require_once "lib/reviewQueries.php";
require_once "lib/enrollmentFunctions.php";
require_once "lib/fileParse.php";
require_once "lib/pairingFunctions.php";

//query information about the requester
$con = connectToDatabase();

//try to get information about the instructor who made this request by checking the session token and redirecting if invalid
if (!isset($_SESSION['id'])) {
  http_response_code(403);
  echo "Forbidden: You must be logged in to access this page.";
  exit();
}
$instructor_id = $_SESSION['id'];

// Verify we have already defined all the data required for this conformation.
if (!isset($_SESSION["survey_data"]) || !isset($_SESSION["survey_course_id"]) || !isset($_SESSION["survey_file"]) || !isset($_SESSION["survey_students"])) {
  http_response_code(302);
  header("Location: ".INSTRUCTOR_HOME."surveys.php");
  exit();
}
// Get the pairings we will be using in this survey
$file_data = $_SESSION["survey_file"];
// Get the survey data we will work with
$survey_data = $_SESSION["survey_data"];
// Get the course id of the course whose roster is being updated
$course_id = $_SESSION['survey_course_id'];
// Get information about the students in this course
$survey_students = $_SESSION["survey_students"];
// Break out the information about the survey
$survey_name = $survey_data['name'];
$survey_s = $survey_data['start'];
$survey_begin = $survey_s->format('M j').' at '. $survey_s->format('g:i A');
$survey_e = $survey_data['end'];
$survey_end = $survey_e->format('M j').' at '. $survey_e->format('g:i A');
$survey_type = $survey_data['pairing_mode'];
$pm_mult = $survey_data['multiplier'];
$rubric_id = $survey_data['rubric'];

if (!array_key_exists($survey_type, $_SESSION["surveyTypes"])) {
  http_response_code(400);
  echo "400: Request uses an incorrect survey type";
  exit();
}
$rubric_name = getRubricName($con, $rubric_id);
if (empty($rubric_name)) {
  http_response_code(400);
  echo "400: Request specifies an incorrect rubric";
  exit();
}

// make sure the survey is for a course the current instructor actually teaches
if (!isCourseInstructor($con, $course_id, $instructor_id)) {
  http_response_code(403);
  echo "403: Forbidden.";
  exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  if (isset($_POST["cancel-survey"]) || isset($_POST["return-survey"])) {
    // Cancel out of the changes and exit back to the instructor home page
    unset($_SESSION["survey_file"]);
    unset($_SESSION["survey_data"]);
    unset($_SESSION["survey_course_id"]);
    unset($_SESSION["survey_students"]);
    unset($_SESSION["pairings"]);

    http_response_code(302);
    header("Location: ".INSTRUCTOR_HOME."surveys.php");
    exit();
  } else {
    $survey_id = insertSurvey($con, $course_id, $survey_name, $survey_s, $survey_e, $rubric_id, $survey_type);
    addReviewsToSurvey($con, $survey_id, $_SESSION['pairings']);
    http_response_code(302);
    header("Location: ".INSTRUCTOR_HOME."surveys.php");
    exit();
  }
}
$course_info = getSingleCourseInfo($con, $course_id, $instructor_id);
$course_code = $course_info['code'];
$course_name = $course_info['name'];
$roster = getRoster($con, $course_id);
$non_roster = getNonRosterStudents($survey_students, $roster);
$file_results = processReviewRows($file_data, $survey_students, $pm_mult, $survey_type);
$_SESSION["pairings"] = $file_results["pairings"];
$roles = $file_results["roles"];
