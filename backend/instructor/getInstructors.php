<?php

require_once "../lib/constants.php";
require_once '../lib/studentQueries.php';
require_once "lib/instructorQueries.php";
require_once "lib/fileParse.php";
require_once "lib/enrollmentFunctions.php";
require_once "lib/courseQueries.php";
require_once "lib/instructorQueries.php";
require_once "../lib/database.php";

session_start();
ini_set("display_errors", 1);
ini_set("log_errors", 1);
ini_set("error_log", "~/php-error.log");
$con = connectToDatabase();

//try to get information about the instructor who made this request by checking the session token and redirecting if invalid
if (!isset($_SESSION['id'])) {
    http_response_code(403);
    echo "Forbidden: You must be logged in to access this page.";
    exit();
  }
$instructor_id = $_SESSION['id'];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $instructors = array();

    // Fetch all instructors
    $query = "SELECT * FROM instructors";
    $result = mysqli_query($con, $query);

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            if($row['id'] == $instructor_id){
                continue;
            }else {
                $instructor = array(
                    $row['id'],
                    $row['name'],
                    $row['email']
                    
                );
                $instructors[] = $instructor;
            }
        }
        // Output the array of arrays for instructor details
        header('Content-Type: application/json');
        echo json_encode($instructors);
    } else {
        echo "Error retrieving instructors: " . mysqli_error($con);
    }
    exit;
}
?>