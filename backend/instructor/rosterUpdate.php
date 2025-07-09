<?php
//error logging
error_reporting(-1); // reports all errors
ini_set("display_errors", "1"); // shows all errors
ini_set("log_errors", 1);
ini_set("error_log", "~/php-error.log");

//bring in required code
require_once "../lib/database.php";
require_once "../lib/constants.php";
require_once '../lib/studentQueries.php';
require_once "lib/fileParse.php";
require_once "lib/courseQueries.php";
require_once "lib/enrollmentFunctions.php";
require_once "lib/instructorQueries.php";
require_once "lib/loginStatus.php";

$instructor_id = getInstructorId();

//query information about the requester
$con = connectToDatabase();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  if (!isset($_FILES['roster-file']) || !isset($_POST['course-id']) || !isset($_POST['update-type'])) {
    http_response_code(400);
    $json_out = json_encode(array("error" => "Forbidden: Access is only allowed through the application."));
    echo $json_out;
    exit();
  }

  if (!is_uploaded_file($_FILES['roster-file']['tmp_name'])) {
    http_response_code(403);
    $json_out = json_encode(array("error" => "Forbidden: Access is only allowed through the application."));
    echo $json_out;
    exit();
  }

  $update_type = $_POST['update-type'];
  if (($update_type != 'replace') && ($update_type != 'expand')) {
    http_response_code(400);
    $json_out = json_encode(array("error" => "Forbidden: Access is only allowed through the application."));
    echo $json_out;
    exit();
  }

  // Get the course id of the course whose roster is being updated
  $course_id = intval($_POST['course-id']);

  // make sure the survey is for a course the current instructor actually teaches
  if (!isCourseInstructor($con, $course_id, $instructor_id)) {
    http_response_code(403);
    $json_out = json_encode(array("error" => "Forbidden: Access is only allowed through logged in users of the application."));
    echo $json_out;
    exit();
  }
  //echo "error on line\n" ;
  $ret_val = array("error" => "");
  if ($_FILES['roster-file']['error'] == UPLOAD_ERR_INI_SIZE) {
    //echo "1 \n";
    $ret_val['error'] = 'The selected file is too large.';
  } else if ($_FILES['roster-file']['error'] == UPLOAD_ERR_PARTIAL) {
    //echo "2 \n";
    $ret_val['error'] = 'The selected file was only paritally uploaded. Please try again.';
  } else if ($_FILES['roster-file']['error'] == UPLOAD_ERR_NO_FILE) {
    //echo "3 \n";
    $ret_val['error'] = 'A roster file must be provided.';
  } else if ($_FILES['roster-file']['error'] != UPLOAD_ERR_OK) {
    //echo "4 \n";
    $ret_val['error'] = 'An error occured when uploading the file. Please try again.';
  } else {
    // Open the file
    $file_handle = @fopen($_FILES['roster-file']['tmp_name'], "r");
    // catch errors or continue parsing the file
    if (!$file_handle) {
      $ret_val['error'] = 'An error occured when uploading the file. Please try again.';
    } else {
      $names_emails = parse_roster_file($file_handle);
      // Clean up our file handling
      fclose($file_handle);

      // check for any errors
      if (!empty($names_emails['error'])) {
        $ret_val['error'] = $names_emails['error'];
      } else {
        if($update_type == "replace"){ // remove old roster and add new students
          //remove all students from the course
          $course_roster = getRoster($con, $course_id);
          $breakOutRos = breakoutRosters($course_roster, $names_emails["ids"]);
          $remove_students = $breakOutRos['remaining'];
          $new_students = $breakOutRos['new'];
          addStudents($con, $course_id, $new_students);
          removeFromRoster($con, $course_id, $remove_students);
          //echo "Students have been successfully replaced. \n";
        }
        if($update_type == "expand"){ // expand on original roster works correctly 
          $course_roster = getRoster($con, $course_id);
          $breakOutRos = breakoutRosters($course_roster, $names_emails["ids"]);
          $new_students = $breakOutRos['new'];
          addStudents($con, $course_id, $new_students);
         // echo "Students have been successfully added. \n" ;
        }
      }
    }
  }
  // We can open the file, so lets start setting up the header
  header("Content-Type: application/json; charset=UTF-8");
  // Now lets dump the data we found
  $myJSON = json_encode($ret_val);
  echo $myJSON; 
}
?>