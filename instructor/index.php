<?php

//error logging
error_reporting(-1); // reports all errors
ini_set("display_errors", "1"); // shows all errors
ini_set("log_errors", 1);
ini_set("error_log", "~/php-error.log");

// start the session variable
session_start();

// bring in required code
require_once "../lib/random.php";
require_once "../lib/database.php";
require_once "../lib/constants.php";
require_once "lib/pairingFunctions.php";
require_once "lib/instructorQueries.php";

// query information about the requester
$con = connectToDatabase();

// handle data from shibboleth
if (!empty($_SERVER['uid'])) {
  // make sure the email is not just whitespace
  $email = $_SERVER['uid']."@buffalo.edu";

  // Now check if this is a recognized instructor
  $id = getInstructorId($con, $email);
  if (empty($id)) {
    // This user is not an instructor -- optimistically redirect them to the student-side of the system
    $loc_string = "Location: ".SITE_HOME."/index.php";
    header($loc_string);
    exit();
  }
  $_SESSION['id'] = $id;
  $_SESSION["surveyTypes"] = getSurveyTypes($con);
  // redirect the instructor to the next page
  http_response_code(302);
  header("Location: ".INSTRUCTOR_HOME."surveys.php");
  exit();
} else {
  http_response_code(400);
  echo "Could not connect: Error connecting to shibboleth. Talk to Matthew to get this fixed.";
  exit();
}
?>
