<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

//bring in required code 
require "../lib/database.php";
require "../lib/constants.php";
require "lib/courseQueries.php";
require "lib/loginStatus.php";

$instructor_id = getInstructorId();

$con = connectToDatabase();

// Call the function and get the results
$result = getInstructorHistoricalTerms($con, $instructor_id, MONTH_MAP_SEMESTER, SEMESTER_MAP_REVERSE);
    
// Return the results as JSON
header('Access-Control-Allow-Origin: '.FRONTEND_SERVER);
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');
echo json_encode($result);
?>