<?php


echo "Before require";
require_once "courseQueries.php";
echo "After require";
// issues here with the require 



error_reporting(E_ALL);
ini_set('display_errors', 1);

//$update_type = $_POST['update-type'];
// Check if the request method is POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Assuming you are sending parameters in the POST request
    $instructor_id = $_POST['instructor_id'];
    $currentSemester = $_POST['currentSemester'];
    $currentYear = $_POST['currentYear'];
    
   
    print_r("HELLO 2"); 
    // Call the function and get the results
    $result = getInstructorTerms($con, $instructor_id, $currentSemester, $currentYear);

    print_r("HELLO 3"); 
    // Return the results as JSON
    header('Content-Type: application/json');
    echo json_encode($result);
} else {
    print_r("HELLO 4"); 
    // Return an error message for unsupported request methods
    http_response_code(405); // Method Not Allowed
    echo "Only POST requests are allowed.";
}



?>




