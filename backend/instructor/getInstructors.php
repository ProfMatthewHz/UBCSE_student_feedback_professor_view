<?php
ini_set("display_errors", 1);
ini_set("log_errors", 1);
ini_set("error_log", "~/php-error.log");

require_once "../lib/constants.php";
require_once '../lib/studentQueries.php';
require_once "lib/instructorQueries.php";
require_once "lib/fileParse.php";
require_once "lib/enrollmentFunctions.php";
require_once "lib/courseQueries.php";
require_once "lib/instructorQueries.php";
require_once "../lib/database.php";
require_once "lib/loginStatus.php";

$instructor_id = getInstructorId();

$con = connectToDatabase();

$instructors = getAllOtherInstructorsFull($con, $instructor_id);

// Output the array of arrays for instructor details
header('Content-Type: application/json');
echo json_encode($instructors);
?>