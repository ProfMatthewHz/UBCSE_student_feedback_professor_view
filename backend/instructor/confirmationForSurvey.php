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


// Get the pairings we will be using in this survey
if (!isset($_SESSION["survey_data"]) || !isset($_SESSION["survey_course_id"]) || 
    !isset($_SESSION["survey_file"]) || !isset($_SESSION["survey_students"])) 
{
    http_response_code(403);
    echo "Missing parameters, Survey Roster contains errors!";
    exit();
}


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

if($_SERVER['REQUEST_METHOD'] == 'GET') {
  // Verify we have already defined all the data required for this conformation.

  $response = array();
  $response['data'] = array();
  $response['errors'] = array();

  $errorMsg = array();

  if (!array_key_exists($survey_type, $_SESSION["surveyTypes"])) {
    // http_response_code(400);
    // echo "400: Request uses an incorrect survey type";
    // exit();
    $errorMsg['survey'] = "Request uses an incorrect survey type"; 

  }
  $rubric_name = getRubricName($con, $rubric_id);
  if (empty($rubric_name)) {
    // http_response_code(400);
    // echo "400: Request specifies an incorrect rubric";
    // exit();
    
    $errorMsg['rubric'] = "Request specifies an incorrect rubric";

  }

  // make sure the survey is for a course the current instructor actually teaches
  if (!isCourseInstructor($con, $course_id, $instructor_id)) {
    // http_response_code(403);
    // echo "403: Forbidden. You do not teach this course.";
    // exit();

    $errorMsg['course'] = "Forbidden. You do not teach this course.";
  }


  if (empty($errorMsg)){
    $course_info = getSingleCourseInfo($con, $course_id, $instructor_id);
    $course_code = $course_info['code'];
    $course_name = $course_info['name'];
    $roster = getRoster($con, $course_id);
    $non_roster = getNonRosterStudents($survey_students, $roster);
    $file_results = processReviewRows($file_data, $survey_students, $pm_mult, $survey_type);
    $_SESSION["pairings"] = $file_results["pairings"];
    $roles = $file_results["roles"];
  
    $rosterData = array();

    // {"student_email", "student_name", "reviewing", "reviewed"}
    $roster_students = array();
    $non_roster_students = array();

    # get roster students data
    foreach($roster as $email => $student_data){

      $student_name = $student_data[0];
      $student_id = $student_data[1];

      $isReviewer = (array_key_exists($student_id, $roles) && $roles[$student_id][0]);
      $isReviewed = (array_key_exists($student_id, $roles) && $roles[$student_id][1]);

      $student_data = array(
        'student_email' => $email,
        'student_name' => $student_name,
        'reviewing' => $isReviewer,
        'reviewed' => $isReviewed,
      );
      $roster_students[] = $student_data;
    }
    // good php practice to unset loop vars
    unset($email, $student_data);

    # get non-roster students data
    foreach($non_roster as $email => $student_data){

      $student_name = $student_data[0];
      $student_id = $student_data[1];

      $isReviewer = (array_key_exists($student_id, $roles) && $roles[$student_id][0]);
      $isReviewed = (array_key_exists($student_id, $roles) && $roles[$student_id][1]);

      $student_data = array(
        'student_email' => $email,
        'student_name' => $student_name,
        'reviewing' => $isReviewer,
        'reviewed' => $isReviewed,
      );
      $non_roster_students[] = $student_data;
    }
    // good php practice to unset loop vars
    unset($email, $student_data);


    $rosterData['roster-students'] = $roster_students;
    $rosterData['non-roster-students'] = $non_roster_students;
    $rosterData['pairings'] = $file_results['pairings'];

    $response['data'] = $rosterData;
  } else {
    $response['errors'] = $errorMsg;
  }


  header("Content-Type: application/json; charset=UTF-8");

  $responseJSON = json_encode($response);

  echo $responseJSON;

}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

  $response = array('data' => array(), 'errors' => array());

  if (isset($_POST["cancel-survey"]) || isset($_POST["return-survey"])) {
    // do nothing, frontend will redirect

    $response['data']['message'] = "Survey submission cancelled!";

    http_response_code(200);

  } elseif (isset($_POST['save-survey'])) {
    $survey_id = insertSurvey($con, $course_id, $survey_name, $survey_s, $survey_e, $rubric_id, $survey_type);
    
    $addSuccess = addReviewsToSurvey($con, $survey_id, $_SESSION['pairings']);

    if ($addSuccess){
      $response['data']['message'] = "Successfully created survey and added reviews to the survey";
    } else {
      $response["errors"]['message'] = "Unsuccessful in adding reviews to the survey :( ";
    }

    http_response_code(200);
  } 

  unset($_SESSION["survey_file"]);
  unset($_SESSION["survey_data"]);
  unset($_SESSION["survey_course_id"]);
  unset($_SESSION["survey_students"]);
  unset($_SESSION["pairings"]);

  
  header("Content-Type: application/json; charset=UTF-8");

  $responseJSON = json_encode($response);

  echo $responseJSON;



}

