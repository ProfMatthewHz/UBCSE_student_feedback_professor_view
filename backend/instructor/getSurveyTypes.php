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
require_once "./lib/pairingFunctions.php";

//query information about the requester
$con = connectToDatabase();

//try to get information about the instructor who made this request by checking the session token and redirecting if invalid
if (!isset($_SESSION['id'])) {
    http_response_code(403);
    $json_out = json_encode(array("error" => "Forbidden: Access is only allowed through the application."));
    echo $json_out;
    exit();
}

$instructor_id = $_SESSION['id'];

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    // Get the course id of the course that is being queried for surveys
    $retVal = array("error" => "", "survey_types" => array());
    $allSurveyTypes = getSurveyTypes($con);

    if (count($allSurveyTypes) == 0) {
        http_response_code(500);
        $json_out = json_encode(array("error" => "Oops: No known surveys exist."));
        echo $json_out;
        exit();
    } else {
        foreach ($allSurveyTypes as $surveyTypeId => $surveyTypeInfo) {
            $desc = $surveyTypeInfo[0];
            $file_org = $surveyTypeInfo[1];
            $display_mult = $surveyTypeInfo[2];
            $text = $surveyTypeInfo[3];
            $retVal["survey_types"][] = array("id" => $surveyTypeId, "text" => $text ,"description" => $desc, "file_organization" => $file_org, "usesMultiplier" => ($display_mult===1));
        }
    }
    header("Content-Type: application/json; charset=UTF-8");

    // Now lets dump the data we found
    $myJSON = json_encode($retVal);

    echo $myJSON;
}
