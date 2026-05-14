<?php
require 'lib/constants.php';
require 'lib/database.php';
require "lib/loginStatus.php";

error_reporting(-1); // reports all errors
ini_set("display_errors", "1"); // shows all errors
ini_set("log_errors", 1);

header('Access-Control-Allow-Origin: '.FRONTEND_SERVER);
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');

$student_id = getStudentId();

if (!isset($_POST) || !isset($_POST['new_name'])) {
  http_response_code(400);
  echo json_encode(array('error' => 'Bad request: Request only valid from within app'));
  exit();
} else {
  $new_name = $_POST['new_name'];
  mail(ADMIN_EMAIL, "Name Change Request from Student ID: $student_id", "Student with ID $student_id has requested a name change to $new_name. Please review and take appropriate action.");
  http_response_code(200);
  echo json_encode(array('message' => 'Name change request sent successfully.'));
  exit();
}
?>