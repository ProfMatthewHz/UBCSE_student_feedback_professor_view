<?php
include_once 'lib/constants.php';
include_once "lib/loginRoutine.php";

error_reporting(-1); // reports all errors
ini_set("display_errors", "1"); // shows all errors
ini_set("log_errors", 1);

session_start();
#header("Access-Control-Allow-Origin: http://localhost:3000");
#header('Access-Control-Allow-Headers: Content-Type');
#header("Access-Control-Allow-Credentials: true");

// Set the session variables when running in production 
if (!empty($_SERVER['uid'])) {
  setSessionVariables($_SERVER['uid']);
}

if (empty($_SESSION['redirect'])) {
  http_response_code(405);
  header('Content-Type: application/json');
  echo ('{"error": "No redirect set"}');
  exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $retArray = ['redirect' => $_SESSION['redirect']];
    http_response_code(302);
    header('Content-Type: application/json');
    echo json_encode($retArray);
    exit();
}
