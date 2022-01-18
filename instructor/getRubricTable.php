<?php
//error logging
error_reporting(-1); // reports all errors
ini_set("display_errors", "1"); // shows all errors
ini_set("log_errors", 1);
ini_set("error_log", "~/php-error.log");

//start the session variable
session_start();

//bring in required code
require_once "../lib/database.php";
require_once "../lib/constants.php";
require_once "../lib/infoClasses.php";
require_once "../lib/fileParse.php";
require_once "lib/rubricQueries.php";
require_once "lib/rubricTable.php";

//query information about the requester
$con = connectToDatabase();

//try to get information about the instructor who made this request by checking the session token and redirecting if invalid
$instructor = new InstructorInfo();
$instructor->check_session($con, 0);

if($_SERVER['REQUEST_METHOD'] != 'POST') {
  http_response_code(400);
  echo "Bad Request: Missing parmeters.";
  exit();
}
if (!isset($_POST["rubric"]) || !ctype_digit($_POST["rubric"])) {
  http_response_code(400);
  echo "Bad request: parameters provided do not match what is required";
  exit();
}
$rubric_id = intval($_POST["rubric"]);
$data = getRubricData($con, $rubric_id);
$table_data = emitRubricTable($data["topics"], $data["scores"]);
echo json_encode($table_data);
?>
