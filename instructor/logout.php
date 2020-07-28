<?php

//error logging
error_reporting(-1); // reports all errors
ini_set("display_errors", "1"); // shows all errors
ini_set("log_errors", 1);
ini_set("error_log", "~/php-error.log");

// start the session variable
session_start();

// bring in required code
require_once "../lib/database.php";
require_once "../lib/constants.php";
require_once "../lib/infoClasses.php";


// query information about the requester
$con = connectToDatabase();

// try to get information about the instructor who made this request by checking the session token and redirecting if invalid
$instructor = new InstructorInfo();
$instructor->check_session($con, 0);

// if not post request redirect back to main page
if ($_SERVER['REQUEST_METHOD'] != 'POST')
{
  http_response_code(302);   
  header("Location: surveys.php");
  exit();
}

// check CSRF token and then log the user out
if (!isset($_POST['csrf-token']))
{
  http_response_code(400);
  echo "Bad Request: Missing parmeters.";
  exit();
}

if (!hash_equals($instructor->csrf_token, $_POST['csrf-token']))
{
  http_response_code(403);
  echo "Forbidden: Incorrect parameters.";
  exit();
}

$stmt = $con->prepare('UPDATE instructors SET session_token=NULL WHERE id=?');
$stmt->bind_param('i', $instructor->id);
$stmt->execute();

// redirect back to login page
http_response_code(302);   
header("Location: instructorLogin.php");
exit();

?>