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


$con = connectToDatabase();

if (!isset($_SESSION['id'])) {
    http_response_code(403);
    echo "Forbidden: You must be logged in to access this page.";
    exit();
  }
  $instructor_id = $_SESSION['id'];


//echo "Before require";
//require_once "instructor/lib/courseQueries.php";
//echo "After require";
// issues here with the require 



error_reporting(E_ALL);
ini_set('display_errors', 1);

//$update_type = $_POST['update-type'];
// Check if the request method is POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Assuming you are sending parameters in the POST request
   
    $currentSemester = $_POST['currentSemester'];
    $currentYear = $_POST['currentYear'];
    
   
    //print_r("HELLO 2"); 
    // Call the function and get the results
    $result = getInstructorTerms($con, $instructor_id, $currentSemester, $currentYear);
    
    //print_r("HELLO 3"); 
    // Return the results as JSON
    header('Content-Type: application/json');
    echo json_encode($result);
   // print_r("HELLO 5"); 
} else {
    //print_r("HELLO 6"); 
    // Return an error message for unsupported request methods
    http_response_code(405); // Method Not Allowed
    echo "Only POST requests are allowed.";
}



?>