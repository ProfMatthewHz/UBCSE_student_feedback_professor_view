<?php
error_reporting(-1); // reports all errors
ini_set("display_errors", "1"); // shows all errors
ini_set("log_errors", 1);
session_start();

header('Content-Type: application/json');

$retArray = [];

if (empty($_SESSION['redirect'])) {
    header("Location: "."http://localhost/StudentSurvey/backend/unified_fake_Shibboleth.php");
//    header("Location: ". "https://www-student.cse.buffalo.edu/CSE442-542/2023-Fall/cse-302a/StudentSurvey/backed/unified_fake_Shibboleth.php");
    exit();
}

if ($_SERVER('REQUEST METHOD') == 'GET') {
    $retArray['redirect'] = $_SESSION['redirect'];
    http_response_code(302);
    echo json_encode($retArray);
    exit();
}
