<?php

//error logging
error_reporting(-1); // reports all errors
ini_set("display_errors", 1); // shows all errors
ini_set("log_errors", 1);
ini_set("error_log", "~/php-error.log");

//bring in required code
require "../lib/database.php";
require "../lib/constants.php";
require '../lib/studentQueries.php';
require "lib/instructorQueries.php";
require "lib/fileParse.php";
require "lib/enrollmentFunctions.php";
require "lib/courseQueries.php";
require "lib/loginStatus.php";

$instructor_id = getInstructorId();

//query information about the requester
$con = connectToDatabase();
header("Content-Type: application/json; charset=UTF-8");


$instructor_ids = getAllInstructorIds($con);

$instructor_id = $_SESSION['id'];
//stores error messages corresponding to form fields
$errorMsg = array();

// set flags
$course_code = NULL;
$course_name = NULL;
$semester = NULL;
$course_year = NULL;
$roster_file = NULL;
$additional_instructors = NULL;


if ($_SERVER['REQUEST_METHOD'] == 'POST') {

  // make sure values exist
  if (
    !isset($_POST['course-code']) || !isset($_POST['course-name']) || !isset($_POST['course-year']) || !isset($_FILES['roster-file']) ||
    !isset($_POST['semester']) && !empty($_POST['additional-instructors'])
  ) {
    http_response_code(400);
    echo json_encode(array("errors" => "Request must be made from within app."));
    exit();
  }

  //check valid formatting
  $course_code = trim($_POST['course-code']);
  if (empty($course_code)) {
    $errorMsg['course-code'] = 'Course code cannot be blank.';
  } else if (!ctype_print($course_code)) {
    $errorMsg["course-code"] = "Course code cannot contain unprintable characters.";
  }

  $course_name = trim($_POST['course-name']);
  if (empty($course_name)) {
    $errorMsg['course-name'] = 'Course name cannot be blank.';
  } else if (!ctype_print($course_name)) {
    $errorMsg["course-name"] = "Course name cannot contain unprintable characters.";
  }

  $semester = trim($_POST['semester']);
  if (empty($semester)) {
    $errorMsg['semester'] = 'Please choose a semester.';
  } else if ($semester != "fall" and $semester != "winter" and $semester != "spring" and $semester != "summer") {
    //Prevent injections into 'semester' field
    $errorMsg["semester"] = "Please select a valid semester.";
  }

  $semester = SEMESTER_MAP[$semester];

  $course_year = trim($_POST['course-year']);
  if (empty($course_year)) {
    $errorMsg['course-year'] = 'Course year cannot be blank.';
  } else if (!ctype_digit($course_year) || strlen($course_year) != 4) {
    $errorMsg["course-year"] = "Please enter a valid 4-digit year.";
  }

  if(!empty($_POST['additional-instructors'])) {
    $additional_instructors = explode(',', $_POST['additional-instructors']);
  } else {
    $additional_instructors = [];
  }

  $currentYear = idate('Y');
  $month = idate('m');
  $currentTerm = MONTH_MAP_SEMESTER[$month];
  
  // accounts for the current year and current semester 
  // so courses cannot be created for past terms
  if ($course_year < $currentYear) {
    $errorMsg['course-year'] = 'Course year cannot be in the past.';
  } else if  (($course_year == $currentYear) && ($semester < $currentTerm) ) {
    $errorMsg['semester'] = 'Course term cannot be in the past.';
  }

  if (courseExists($con, $course_code, $course_name, $semester, $course_year, $_SESSION['id'])) {
    $errorMsg['duplicate'] = 'Error: The entered course already exists.';
  }

  // now validate the roster file
  if ($_FILES['roster-file']['error'] == UPLOAD_ERR_INI_SIZE) {
    $errorMsg['roster-file'] = 'The selected file is too large.';
  } else if ($_FILES['roster-file']['error'] == UPLOAD_ERR_PARTIAL) {
    $errorMsg['roster-file'] = 'The selected file was only paritally uploaded. Please try again.';
  } else if ($_FILES['roster-file']['error'] == UPLOAD_ERR_NO_FILE) {
    $errorMsg['roster-file'] = 'A roster file must be provided.';
  } else if ($_FILES['roster-file']['error'] != UPLOAD_ERR_OK) {
    $errorMsg['roster-file'] = 'An error occured when uploading the file. Please try again.';
  }
  // start parsing the file
  else {
    $file_handle = @fopen($_FILES['roster-file']['tmp_name'], "r");

    // catch errors or continue parsing the file
    if (!$file_handle) {
      $errorMsg['roster-file'] = 'An error occured when uploading the file. Please try again.';
    } else {
      $names_emails = parse_roster_file($file_handle);

      // Clean up our file handling
      fclose($file_handle);

      if (!empty($names_emails['error'])) {
       $errorMsg['roster-file'] = $names_emails['error'];
      } else if (empty($errorMsg)) {
        // now add the roster to the database if no other errors were set after adding the course to the database
        $course_id = addCourse($con, $course_code, $course_name, $semester, $course_year);
        
        //loop through additional instructors and add them to the course
        if (!empty($additional_instructors)) {
          foreach ($additional_instructors as $instructor) {
            addInstructor($con, $course_id, $instructor);
          }
        }            
        addInstructor($con, $course_id, $instructor_id);
        // Upload the course roster for later use
        addStudents($con, $course_id, $names_emails['ids']);
      }
    }
  }
  // Now lets dump the data we found
  $myJSON = json_encode($errorMsg);
  echo $myJSON;
}
?>