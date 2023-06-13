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
require_once "../lib/infoClasses.php";
require_once '../lib/studentQueries.php';
require_once "lib/fileParse.php";
require_once "lib/enrollmentFunctions.php";
require_once "lib/instructorQueries.php";

//query information about the requester
$con = connectToDatabase();

//try to get information about the instructor who made this request by checking the session token and redirecting if invalid
$instructor = new InstructorInfo();
$instructor->check_session($con, 0);

if($_SERVER['REQUEST_METHOD'] == 'POST') {
  if (!isset($_FILES['roster-file']) || !isset($_POST['course_id'])) {
    http_response_code(400);
    echo "Bad Request: Missing parmeters.";
    exit();
  }

  if (!is_uploaded_file($_FILES['roster-file']['tmp_name'])) {
    http_response_code(403);
    echo "Forbidden: Incorrect parameters.";
    exit();
  }

  // Get the course id of the course whose roster is being updated
  $course_id = intval($_POST['course_id']);

  // make sure the survey is for a course the current instructor actually teaches
  if (!isCourseInstructor($con, $course_id, $instructor->id)) {
    http_response_code(403);
    echo "403: Forbidden.";
    exit();
  }

  $results = array();
  $results['success'] = true;
  if ($_FILES['roster-file']['error'] == UPLOAD_ERR_INI_SIZE) {
    $results['success'] = false;
    $results['error'] = 'The selected file is too large.';
  } else if ($_FILES['roster-file']['error'] == UPLOAD_ERR_PARTIAL) {
    $results['success'] = false;
    $results['error'] = 'The selected file was only paritally uploaded. Please try again.';
  } else if ($_FILES['roster-file']['error'] == UPLOAD_ERR_NO_FILE) {
    $results['success'] = false;
    $results['error'] ='A roster file must be provided.';
  } else if ($_FILES['roster-file']['error'] != UPLOAD_ERR_OK) {
    $results['success'] = false;
    $results['error'] = 'An error occured when uploading the file. Please try again.';
  } else {    
    $file_handle = @fopen($_FILES['roster-file']['tmp_name'], "r");
    // catch errors or continue parsing the file
    if (!$file_handle) {
      $results['success'] = false;
      $results['error'] = 'An error occured when uploading the file. Please try again.';
    } else {
      $names_emails = parse_roster_file($file_handle);
      // Clean up our file handling
      fclose($file_handle);

      // check for any errors
      if (!empty($names_emails['error'])) {
        $results['success'] = false;
        $results['error'] = $names_emails['error'];
      } else {
        clearRoster($con, $course_id);
        addToCourse($con, $course_id, $names_emails);
      }
    }
  }
  // We can open the file, so lets start setting up the header
  header("Content-Type: application/json; charset=UTF-8");
  // Now lets dump the data we found
  $myJSON = json_encode($results);
  echo $myJSON;
}
?>