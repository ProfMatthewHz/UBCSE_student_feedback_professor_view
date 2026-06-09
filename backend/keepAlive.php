<?php
require 'lib/constants.php';
require 'lib/database.php';
require "lib/loginRoutine.php";

error_reporting(-1); // reports all errors
ini_set("display_errors", "1"); // shows all errors
ini_set("log_errors", 1);

session_start();
header('Access-Control-Allow-Origin: '.FRONTEND_SERVER);
header('Access-Control-Allow-Credentials: true');

if (empty($_SESSION['redirect'])) {
  http_response_code(405);
  header('Content-Type: application/json');
  echo ('{"error": "No redirect set"}');
  exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $retArray = ['success' => 1];
    header('Content-Type: application/json');
    echo json_encode($retArray);
    exit();
}
?>