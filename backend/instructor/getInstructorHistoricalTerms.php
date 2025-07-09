<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

//bring in required code 
require_once "../lib/database.php";
require_once "../lib/constants.php";
require_once '../lib/studentQueries.php';
require_once "lib/fileParse.php";
require_once "lib/courseQueries.php";
require_once "lib/enrollmentFunctions.php";
require_once "lib/instructorQueries.php";
require_once "lib/loginStatus.php";

$instructor_id = getInstructorId();

$con = connectToDatabase();

// Call the function and get the results
$result = getInstructorHistoricalTerms($con, $instructor_id);
    
// Return the results as JSON
header('Content-Type: application/json');
echo json_encode($result);
?>