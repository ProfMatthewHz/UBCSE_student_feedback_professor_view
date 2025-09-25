<?php
ini_set("display_errors", 1);
ini_set("log_errors", 1);
ini_set("error_log", "~/php-error.log");

require "../lib/constants.php";
require "../lib/database.php";
require "lib/loginStatus.php";
require "lib/instructorQueries.php";


$instructor_id = getInstructorId();

$con = connectToDatabase();

$instructors = getAllOtherInstructorsFull($con, $instructor_id);

// Output the array of arrays for instructor details
header('Access-Control-Allow-Origin: '.FRONTEND_SERVER);
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');
echo json_encode($instructors);
?>