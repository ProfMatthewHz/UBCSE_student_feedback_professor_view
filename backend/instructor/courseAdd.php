<?php

//error logging
error_reporting(-1); // reports all errors
ini_set("display_errors", 1); // shows all errors
ini_set("log_errors", 1);
ini_set("error_log", "~/php-error.log");

//start the session variable
session_start();

//bring in required code
require_once "../lib/database.php";
require_once "../lib/constants.php";
require_once '../lib/studentQueries.php';
require_once "lib/instructorQueries.php";
require_once "lib/fileParse.php";
require_once "lib/enrollmentFunctions.php";
require_once "lib/courseQueries.php";




//query information about the requester
$con = connectToDatabase();

//try to get information about the instructor who made this request by checking the session token and redirecting if invalid
if (!isset($_SESSION['id'])) {
  http_response_code(403);
  echo "Forbidden: You must be logged in to access this page.";
  exit();
}

$query = "SELECT * FROM instructors";
$result = mysqli_query($con, $query);
$instructor_ids = array();
while ($row = mysqli_fetch_assoc($result)) {
  $instructor_ids[] = $row['id'];
}





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
    echo "Bad Request: Missing parmeters.";
    exit();
  }



  // check CSRF token
  // $csrf_token = getCSRFToken($con, $instructor_id);
  // if ((!hash_equals($csrf_token, $_POST['csrf-token'])) || !is_uploaded_file($_FILES['roster-file']['tmp_name'])) {
  //   http_response_code(403);
  //   echo "Forbidden: Incorrect parameters.";
  //   exit();
  // }

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

  // if($additional_instructors == NULL){
  //   $additional_instructors = array();
  // }else{
  //   $additional_instructors = $_POST['additional-instructors'];

  //   $instructorSplitString = explode(',', $additional_instructors);

  //   $additional_instructors = $instructorSplitString;
  // }

  $additional_instructors = $_POST['additional-instructors'];

  if(!empty($additional_instructors)) {
    $instructorSplit = explode(',', $additional_instructors);
    $additional_instructors = $instructorSplit;
  }else {
    $additional_instructors = [];
  }

 
   
   // have to take a comma seperated string and split them. 




 

  $currentYear = date('Y');
  //$currentMonthA = date('n');
  //$currentMonth = MONTH_MAP_SEMESTER[$currentMonth];
  //$currentSemesterMonth = SEMESTER_MAP[$currentMonth];

  //print_r($currentYear . " = Current Year.        ");
  $month = date('m');
  $ActualMonth = MONTH_MAP_SEMESTER[$month];
  $currentActualMonth = SEMESTER_MAP_REVERSE[$ActualMonth];
  
  // accounts for the current year and current semester 
// so a previous course can not be added. 
  if ($course_year < $currentYear) {
    $errorMsg['course-year'] = 'Course year cannot be in the past.';
    //print_r("Course year cannot be in the past");
  } else if ($course_year == $currentYear) {
    if ($semester != $ActualMonth) {
      //print_r($semester. " and " . $ActualMonth);
      $errorMsg['semester'] = 'incorrect semester';
      //print_r("Erorr Wrong semester");
    }
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

      
     
      

     
      if (!empty(($names_emails['error'])) || !(empty($differences))) {
        //$errorMsg['additional-instructors'] = "unkown intstructors found";
       $errorMsg['roster-file'] = $names_emails['error'];
      } else {
        // now add the roster to the database if no other errors were set after adding the course to the database
        if (empty($errorMsg)) {
          // Verify this course does not already exist
          if (!courseExists($con, $course_code, $course_name, $semester, $course_year, $_SESSION['id'])) {


            // Create the course in the database
            $course_id = addCourse($con, $course_code, $course_name, $semester, $course_year);


           
            //loop through additional instructors and add them to the course
            if (!empty($additional_instructors) && empty($differences)) {
              foreach ($additional_instructors as $instructor) {
                addInstructor($con, $course_id, $instructor);
              }
              //echo "Instructors added successfully\n";
            }
            
            addInstructor($con, $course_id, $instructor_id);


            // Upload the course roster for later use
            addStudents($con, $course_id, $names_emails['ids']);

            // redirect to course page with message
            //$_SESSION['course-add'] = "Successfully added course: " . htmlspecialchars($course_code) . ' - ' . htmlspecialchars($course_name) . ' - ' . SEMESTER_MAP_REVERSE[$semester] . ' ' . htmlspecialchars($course_year);
            //echo "Course added successfully";

            

            //http_response_code(302);
            //header("Location: ".INSTRUCTOR_HOME."surveys.php");
            exit();

          } else {
            $errorMsg['duplicate'] = 'Error: The entered course already exists.';
          }
        }
      }
    }
  }
  header("Content-Type: application/json; charset=UTF-8");

  // Now lets dump the data we found
  $myJSON = json_encode($errorMsg);

  echo $myJSON;

}

?>