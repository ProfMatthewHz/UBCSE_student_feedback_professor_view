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
require_once '../lib/studentQueries.php';
require_once "lib/fileParse.php";
require_once "lib/courseQueries.php";
require_once "lib/enrollmentFunctions.php";
require_once "lib/instructorQueries.php";

//query information about the requester
$con = connectToDatabase();

//try to get information about the instructor who made this request by checking the session token and redirecting if invalid
if (!isset($_SESSION['id'])) {
  http_response_code(403);
  echo "Forbidden: You must be logged in to access this page.";
  exit();
}
$instructor_id = $_SESSION['id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  if (!isset($_FILES['roster-file']) || !isset($_POST['course-id']) || !isset($_POST['update-type'])) {
    http_response_code(400);
    echo "Bad Request: Missing parmeters.";
    exit();
  }

  if (!is_uploaded_file($_FILES['roster-file']['tmp_name'])) {
    http_response_code(403);
    echo "Forbidden: Incorrect parameters.";
    exit();
  }

  $update_type = $_POST['update-type'];
  if (($update_type != 'replace') && ($update_type != 'expand')) {
    http_response_code(400);
    echo "Bad Request: Incorrect parameters.";
    exit();
  }

  // Get the course id of the course whose roster is being updated
  $course_id = intval($_POST['course-id']);

  // make sure the survey is for a course the current instructor actually teaches
  if (!isCourseInstructor($con, $course_id, $instructor_id)) {
    http_response_code(403);
    echo "403: Forbidden.";
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
          foreach ($remove_students as $email => $student) {
           // var_dump($email); // This will output the email (key)
           // var_dump($name);  // This will output the name (value)
          }
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






      //   foreach ($new_students as $email => $name) {
      //     var_dump($email); // This will output the email (key)
      //     var_dump($name);  // This will output the name (value)
      //     addStudents($con, $course_id, [$emails => $name]);
      // }
        $_SESSION["roster_data"] = breakoutRosters($course_roster, $names_emails["ids"]);
        $_SESSION["roster_course_id"] = $course_id;
        $_SESSION["roster_update_type"] = $update_type;
      }
    }
  }
  // We can open the file, so lets start setting up the header
  header("Content-Type: application/json; charset=UTF-8");
  // Now lets dump the data we found
  if (isset($ret_val['error']) && $ret_val['error'] === "") {
    //echo "Success \n ";
    $myJSON = json_encode($ret_val);
  } else {
    $myJSON = json_encode($ret_val);
  echo $myJSON;
  }
 
}
?>