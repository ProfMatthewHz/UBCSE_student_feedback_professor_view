<?php
//error logging
error_reporting(-1); // reports all errors
ini_set("display_errors", "1"); // shows all errors
ini_set("log_errors", 1);
ini_set("error_log", "~/php-error.log");

//bring in required code
require "../lib/database.php";
require "../lib/constants.php";
require "lib/rubricQueries.php";
require "lib/loginStatus.php";

$instructor_id = getInstructorId();

//query information about the requester
$con = connectToDatabase();

// Verify that we are handling a POST request
if($_SERVER['REQUEST_METHOD'] != 'POST') {
  http_response_code(504);
  echo json_encode(array("error" => "Bad Request: Wrong request type."));
  exit();
}
// Double-check the POST request included the proper data
if (!isset($_POST["rubric"]) || !ctype_digit($_POST["rubric"])) {
  http_response_code(400);
  echo json_encode(array("error" => "Bad Request: Incorrect parameters."));
  exit(); 
}
$rubric_id = intval($_POST["rubric"]);
$data = getRubricData($con, $rubric_id);
$json_data = json_encode($data);
header('Access-Control-Allow-Origin: '.FRONTEND_SERVER);
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');
echo $json_data;
?>
