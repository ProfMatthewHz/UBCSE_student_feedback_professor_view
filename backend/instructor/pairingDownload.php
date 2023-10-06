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
require_once "lib/courseQueries.php";
require_once "lib/reviewQueries.php";


// query information about the requester
$con = connectToDatabase();

//try to get information about the instructor who made this request by checking the session token and redirecting if invalid
if (!isset($_SESSION['id'])) {
  http_response_code(403);
  echo "Forbidden: You must be logged in to access this page.";
  exit();
}
$instructor_id = $_SESSION['id'];

// check for the query string parameter
// respond not found on no query string parameter
if (!isset($_GET['survey'])) {
  http_response_code(404);   
  echo "404: Not found.";
  exit();
}

// make sure the query string is an integer, reply 404 otherwise
$sid = intval($_GET['survey']);
if ($sid === 0) {
  http_response_code(404);   
  echo "404: Not found.";
  exit();
}

// Look up info about the requested survey
if (!isSurveyInstructor($con, $sid, $instructor_id)) {
  http_response_code(403);   
  echo "403: Forbidden.";
  exit();
}

// Finally, get the pairings for review
$pairings = "reviewer_email,reviewee_email\n";

// get information about the pairings
$reviews = getReviewPairingsData($con, $sid);

foreach ($reviews as $pair) {
  $pairings .= $pair['reviewer_email'] . "," . $pair['reviewed_email'] . "\n";
}

// generate the correct headers for the file download
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="survey-' . $sid . '-pairings.csv"');

// ouput the data
echo $pairings;
?>