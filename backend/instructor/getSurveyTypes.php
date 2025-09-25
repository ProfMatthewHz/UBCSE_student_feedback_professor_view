<?php
//error logging
error_reporting(-1); // reports all errors
ini_set("display_errors", "1"); // shows all errors
ini_set("log_errors", 1);
ini_set("error_log", "~/php-error.log");

//bring in required code
require "../lib/database.php";
require "../lib/constants.php";
require "./lib/pairingFunctions.php";
require "lib/loginStatus.php";

header('Access-Control-Allow-Origin: '.FRONTEND_SERVER);
header('Access-Control-Allow-Credentials: true');

$instructor_id = getInstructorId();

//query information about the requester
$con = connectToDatabase();


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
            $review_class = $surveyTypeInfo[4];
            $retVal["survey_types"][] = array("id" => $surveyTypeId, "text" => $text ,"description" => $desc, "file_organization" => $file_org, "usesMultiplier" => ($display_mult===1), "review_class" => $review_class);
        }
    }
    header("Content-Type: application/json; charset=UTF-8");

    // Now lets dump the data we found
    $myJSON = json_encode($retVal);

    echo $myJSON;
}
