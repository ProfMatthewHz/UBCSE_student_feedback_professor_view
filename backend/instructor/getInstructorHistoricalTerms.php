<?php

session_start();

//bring in required code 
require_once "../lib/database.php";
require_once "../lib/constants.php";
require_once '../lib/studentQueries.php';
require_once "lib/fileParse.php";
require_once "lib/courseQueries.php";
require_once "lib/enrollmentFunctions.php";
require_once "lib/instructorQueries.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);

$con = connectToDatabase();

if (!isset($_SESSION['id'])) {
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(array("error" => "Forbidden: You must be logged in to access this page."));
    exit();
}
$instructor_id = $_SESSION['id'];

// Call the function and get the results
$result = getInstructorHistoricalTerms($con, $instructor_id);
    
// Return the results as JSON
header('Content-Type: application/json');
echo json_encode($result);
?>