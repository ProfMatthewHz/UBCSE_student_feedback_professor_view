<?php
require 'lib/constants.php';
require 'lib/database.php';
require "lib/loginRoutine.php";

error_reporting(-1); // reports all errors
ini_set("display_errors", "1"); // shows all errors
ini_set("log_errors", 1);

session_start();

// Set the session variables when running in production and it is needed
if (empty($_SESSION['redirect']) && !empty($_SERVER['uid'])) {
  $con = connectToDatabase();
  setSessionVariables($con, $_SERVER['uid']);
}

if (empty($_SESSION['redirect'])) {
  http_response_code(405);
  header('Content-Type: application/json');
  echo ('{"error": "No redirect set"}');
  exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $retArray = ['redirect' => $_SESSION['redirect']];
    header('Content-Type: application/json');
    echo json_encode($retArray);
    exit();
}
?>