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
require_once "lib/courseQueries.php";
require_once "lib/enrollmentFunctions.php";

//query information about the requester
$con = connectToDatabase();

//try to get information about the instructor who made this request by checking the session token and redirecting if invalid
if (!isset($_SESSION['id'])) {
  http_response_code(403);
  echo "Forbidden: You must be logged in to access this page.";
  exit();
}
$instructor_id = $_SESSION['id'];

// Verify we have already defined the rubric in total
if (!isset($_SESSION["roster_data"]) || !isset($_SESSION["roster_course_id"]) || !isset($_SESSION["roster_update_type"])) {
  http_response_code(302);
  header("Location: ".INSTRUCTOR_HOME."surveys.php");
  exit();
}

$update_type = $_SESSION['roster_update_type'];
if (($update_type != 'replace') && ($update_type != 'expand')) {
  http_response_code(400);
  echo "Bad Request: Incorrect parameters.";
  exit();
}

// Get the course id of the course whose roster is being updated
$course_id = $_SESSION['roster_course_id'];

// make sure the survey is for a course the current instructor actually teaches
if (!isCourseInstructor($con, $course_id, $instructor_id)) {
  http_response_code(403);
  echo "403: Forbidden.";
  exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  if (isset($_POST["cancel-changes"]) || isset($_POST["return-cancel"])) {
    // Cancel out of the changes and exit back to the instructor home page
    unset($_SESSION["roster_data"]);
    unset($_SESSION["roster_course_id"]);
    unset($_SESSION["roster_update_type"]);
    http_response_code(302);
    header("Location: ".INSTRUCTOR_HOME."surveys.php");
    exit();
  } else {
    // Remove any students that remained on the roster without being listed in the file
    if ($update_type == 'replace') {
      removeFromRoster($con, $course_id, $_SESSION["roster_data"]["remaining"]);
    }
    // Now add any students that need to be added
    addStudents($con, $course_id, $_SESSION["roster_data"]["new"]);
    // And go back to the main page.
    unset($_SESSION["roster_data"]);
    unset($_SESSION["roster_course_id"]);
    unset($_SESSION["roster_update_type"]);
    http_response_code(302);
    header("Location: ".INSTRUCTOR_HOME."surveys.php");
    exit();
  }
}
$course_info = getSingleCourseInfo($con, $course_id, $instructor_id);
$course_code = $course_info['code'];
$course_name = $course_info['name'];
$update_text = "Changes";
$remaining_text = "Removed from";
$remain_no_student_text = "to remove";
if ($update_type == 'expand') {
  $update_text = "Expansion";
  $remaining_text = "Remaining on";
  $remain_no_student_text = " were enrolled and not in the update file";
}
?>
<!DOCTYPE HTML>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous"></script>
  <title>CSE Evaluation Survey System - Confirm Roster Changes</title>
</head>
<body class="text-center">
<!-- Header -->
<main>
  <div class="container-fluid">
    <div class="row justify-content-md-center bg-primary mt-1 mx-1 rounded-pill">
      <div class="col-sm-auto">
        <h4 class="text-white display-1">UB CSE Evalution System<br>Confirm Roster Changes</h4>
      </div>
    </div>
    <div class="row justify-content-md-center mt-5 mx-1">
      <div class="col-sm-auto text-center">
        <h4>Roster <?php echo $update_text;?> for <br><?php echo $course_code . " - " . $course_name; ?></h4>
      </div>
    </div>
    <div class="row justify-content-md-center mt-5 mx-4">
      <div class="accordion" id="roster_changes">
        <div class="accordion-item shadow">
          <h2 class="accordion-header" id="headerAdded">
            <button class="accordion-button fs-4" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAdded" aria-expanded="true" aria-controls="collapseAdded">Students Added to Roster</button>
          </h2>
          <div id="collapseAdded" class="accordion-collapse collapse show" aria-labelledby="headerAdded">
            <div class="accordion-body">
              <?php
              if (!empty($_SESSION["roster_data"]["new"])) {
                // Add a header row
                echo '<div class="row pb-2 justify-content-evenly">
                      <div class="col"><b>Name</b></div>
                      <div class="col"><b>Email</b></div>
                      </div>';
                // Add a row for each student we will add to the roster
                foreach ($_SESSION["roster_data"]["new"] as $email=>$name) {
                  echo '<div class="row pb-2 justify-content-evenly">
                          <div class="col text-success">'.$name.'</div>
                          <div class="col text-success">'.$email.'</div>
                        </div>';
                }
              } else {
                echo '<div class="row justify-content-center"><p><i>No new students to add</i></p></div>';
              }
              ?>
            </div>
          </div>
        </div>
        <div class="accordion-item shadow">
          <h2 class="accordion-header" id="headerContinue">
            <button class="accordion-button fs-4" type="button" data-bs-toggle="collapse" data-bs-target="#collapseContinue" aria-expanded="true" aria-controls="collapseContinue">Students Continuing on Roster</button>
          </h2>
          <div id="collapseContinue" class="accordion-collapse collapse show" aria-labelledby="headerContinue">
            <div class="accordion-body">
              <?php
              if (!empty($_SESSION["roster_data"]["continuing"])) {
                // Add a header row
                echo '<div class="row pb-2 justify-content-evenly">
                      <div class="col"><b>Name</b></div>
                      <div class="col"><b>Email</b></div>
                      </div>';
                // Add a row for each student we will add to the roster
                foreach ($_SESSION["roster_data"]["continuing"] as $email=>$name) {
                  echo '<div class="row pb-2 justify-content-evenly">
                          <div class="col">'.$name.'</div>
                          <div class="col">'.$email.'</div>
                        </div>';
                }
              } else {
                echo '<div class="row justify-content-center"><p><i>No students continuing</i></p></div>';
              }
              ?>
            </div>
          </div>
        </div>
        <div class="accordion-item shadow">
          <h2 class="accordion-header" id="headerRemoved">
            <button class="accordion-button fs-4" type="button" data-bs-toggle="collapse" data-bs-target="#collapseRemoved" aria-expanded="true" aria-controls="collapseRemoved">Students <?php echo $remaining_text; ?> Roster</button>
          </h2>
          <div id="collapseRemoved" class="accordion-collapse collapse show" aria-labelledby="headerRemoved">
            <div class="accordion-body">
              <?php
              if (!empty($_SESSION["roster_data"]["remaining"])) {
                // Add a header row
                echo '<div class="row pb-2 justify-content-evenly">
                      <div class="col"><b>Name</b></div>
                      <div class="col"><b>Email</b></div>
                      </div>';
                // Add a row for each student we will add to the roster
                foreach ($_SESSION["roster_data"]["remaining"] as $email=>$name_and_id) {
                  echo '<div class="row pb-2 justify-content-evenly">
                          <div class="col text-danger">'.$name_and_id[0].'</div>
                          <div class="col text-danger">'.$email[0].'</div>
                        </div>';
                }
              } else {
                echo '<div class="row justify-content-center"><p><i>No students ' . $remain_no_student_text . '</i></p></div>';
              }
              ?>
            </div>
          </div>
      </div>
    </div>
    </div>
    <form id="confirm-rubric" method="post">
      <div class="row mt-2 mx-1">
        <div class="col">
          <input class="btn btn-outline-danger" name="cancel-changes" type="submit" value="Cancel Changes"></input>
        </div>
        <div class="col ms-auto">
          <input class="btn btn-success" name="save-rubric" type="submit" value="Accept Changes"></input>
        </div>
      </div>
      <hr>
      <div class="row mx-1 mt-2 justify-content-center">
        <div class="col-auto">
          <input class="btn btn-outline-info" name="return-cancel" type="submit" value="Return to Instructor Home"></input>
        </div>
      </div>
    </form>
  </div>
</main>
</body>
</html>