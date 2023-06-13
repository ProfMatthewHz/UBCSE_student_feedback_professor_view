<?php
//error logging
error_reporting(-1); // reports all errors
ini_set("display_errors", "1"); // shows all errors
ini_set("log_errors", 1);
ini_set("error_log", "~/php-error.log");

//start the session variable
session_start();

//bring in required code
require_once "../../lib/database.php";
require_once "../../lib/constants.php";
require_once "rubricQueries.php";
require_once "rubricTable.php";

//query information about the requester
$con = connectToDatabase();

//try to get information about the instructor who made this request by checking the session token and redirecting if invalid
if (!isset($_SESSION['id'])) {
  http_response_code(403);
  echo "Forbidden: You must be logged in to access this page.";
  exit();
}
$instructor_id = $_SESSION['id'];

// In case of error, remove that we are reviewing a rubric
unset($_SESSION['rubric_reviewed']);

// Verify that we are handling a POST request
if($_SERVER['REQUEST_METHOD'] != 'POST') {
  http_response_code(504);
  echo "Bad Request: Wrong request type.";
  exit();
}
// Double-check the POST request included the proper data
if (!isset($_POST["rubric"]) || !ctype_digit($_POST["rubric"])) {
  http_response_code(400);
  echo "Bad request: parameters provided do not match what is required";
  exit();
}
$rubric_id = intval($_POST["rubric"]);
$data = getRubricData($con, $rubric_id);
$table_data = emitRubricTable($data["topics"], $data["scores"]);
$_SESSION['rubric_reviewed'] = $rubric_id;
echo json_encode($table_data);
?>
