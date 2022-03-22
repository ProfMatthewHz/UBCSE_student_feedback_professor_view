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


// check for the query string parameter
// respond not found on no query string parameter
if (!isset($_GET['survey']))
{
  http_response_code(404);   
  echo "404: Not found.";
  exit();
}

// make sure the query string is an integer, reply 404 otherwise
$sid = intval($_GET['survey']);

if ($sid === 0)
{
  http_response_code(404);   
  echo "404: Not found.";
  exit();
}

// try to look up info about the requested survey
$survey_info = array();

$stmt = $con->prepare('SELECT course_id, start_date, expiration_date, rubric_id FROM surveys WHERE id=?');
$stmt->bind_param('i', $sid);
$stmt->execute();
$result = $stmt->get_result();

$survey_info = $result->fetch_all(MYSQLI_ASSOC);

// reply not found on no match
if ($result->num_rows == 0) {
  http_response_code(404);   
  echo "404: Not found.";
  exit();
}

// make sure the survey is for a course the current instructor actually teaches
$stmt = $con->prepare('SELECT year FROM course WHERE id=? AND instructor_id=?');
$stmt->bind_param('ii', $survey_info[0]['course_id'], $instructor->id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_all(MYSQLI_ASSOC);

// reply forbidden if instructor did not create survey
if ($result->num_rows == 0) {
  http_response_code(403);   
  echo "403: Forbidden.";
  exit();
}

// Finally, get the pairings for review
$pairings = "reviewer_email,reviewee_email\n";

// get information about the pairings
$stmt = $con->prepare('SELECT reviewer_email, teammate_email FROM reviewers WHERE survey_id=? ORDER BY id');
$stmt->bind_param('i', $sid);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
  $pairings .= $row['reviewer_email'] . "," . $row['teammate_email'] . "\n";
}

// generate the correct headers for the file download
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="survey-' . $sid . '-pairings.csv"');

// ouput the data
echo $pairings;

?>