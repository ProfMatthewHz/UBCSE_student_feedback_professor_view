<?php

// TODO: This is currently not being used as changes made by MHz make this moot
//       Revisit this decision if we ever need to add students in the system

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
require_once "lib/courseQueries.php";


// query information about the requester
$con = connectToDatabase();

// try to get information about the instructor who made this request by checking the session token and redirecting if invalid
$instructor = new InstructorInfo();
$instructor->check_session($con, 0);


// check for the query string parameter
// respond not found on no query string parameter
if (!isset($_GET['course'])) {
  http_response_code(404);
  echo "404: Not found.";
  exit();
}

// make sure the query string is an integer, reply 404 otherwise
$cid = intval($_GET['course']);

if ($cid === 0) {
  http_response_code(404);
  echo "404: Not found.";
  exit();
}

// Check that this is a survey for a course the current instructor actually teaches
if (!isCourseInstructor($con, $cid, $instructor->id)) {
  http_response_code(403);
  echo "403: Forbidden.";
  exit();
}

// finally, store information about course roster in a string
$roster = "student_name,student_email\n";

// get information about the pairings
$stmt = $con->prepare('SELECT roster.student_id, students.name, students.email FROM roster JOIN students ON roster.student_id=students.student_id WHERE roster.course_id=? ORDER BY roster.id');
$stmt->bind_param('i', $cid);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc())
{
  $roster .= $row['name'] . "," . $row['email'] . "\n";
}

// generate the correct headers for the file download
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="course-' . $cid . '-roster.csv"');

// ouput the data
echo $roster;
?>
