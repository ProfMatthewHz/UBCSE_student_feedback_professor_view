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

// Verify that this is a proper request 
if($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405); // Method Not Allowed
  echo json_encode(array("error" => "Only POST requests are allowed."));
  exit();
}

// make sure the required values exist
if (!isset($_POST['semester']) || !isset($_POST['year'])) {
    http_response_code(400);
    echo json_encode(array("error" => "Bad Request: Missing parameters."));
    exit();
}
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Assuming you are sending parameters in the POST request
   
    $semester = $_POST['semester'];
    $year = $_POST['year'];
    
    // Call the function and get the results
    $result = getInstructorTermCourses($con, $instructor_id, $semester, $year);
   
    // Return the results as JSON
    header('Content-Type: application/json');
    echo json_encode($result);
} else {
    // Return an error message for unsupported request methods
    http_response_code(405); // Method Not Allowed
    echo "Only POST requests are allowed.";
}



?>