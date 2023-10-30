<?php
//error logging
error_reporting(-1); // reports all errors
ini_set("display_errors", "1"); // shows all errors
ini_set("log_errors", 1);
ini_set("error_log", "~/php-error.log");

// start the session variable
session_start();

//bring in required code
require_once "../lib/database.php";
require_once "../lib/constants.php";
require_once "./lib/rubricQueries.php";

//query information about the requester
$con = connectToDatabase();

//try to get information about the instructor who made this request by checking the session token and redirecting if invalid
if (!isset($_SESSION['id'])) {
  http_response_code(403);
  echo "Forbidden: You must be logged in to access this page.";
  exit();
}

$instructor_id = $_SESSION['id'];

if($_SERVER['REQUEST_METHOD'] == 'GET') {


    // Get the course id of the course that is being queried for surveys

    $retVal = array("error" => "");
    $retVal["surveyTypes"] = array();

    
    $allRubrics = getRubrics($con);

    if (count($allRubrics) == 0) {
        $retVal["error"] = "There are no rubrics! Please add one.";
    } else {

        foreach ($allRubrics as $rubricId => $rubricDesc){

            $rubricData = array("rubricId" => $rubricId, "rubricDesc" => $rubricDesc);

            $retVal["rubrics"][] = $rubricData;


        }
        unset($rubric_id,$rubric_desc);

        }
    
  header("Content-Type: application/json; charset=UTF-8");

  // Now lets dump the data we found
  $myJSON = json_encode($retVal);

  echo $myJSON;
}
    
?>