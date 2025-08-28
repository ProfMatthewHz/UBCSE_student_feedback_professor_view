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
header('Content-Type: application/json');
echo json_encode($result);
?>