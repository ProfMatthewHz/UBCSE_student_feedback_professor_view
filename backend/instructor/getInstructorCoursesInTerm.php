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

// Check if the request method is POST
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