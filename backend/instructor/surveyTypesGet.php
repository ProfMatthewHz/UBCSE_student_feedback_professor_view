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
  echo "Forbidden: You must be logged in to access this page.";
  exit();
}

$instructor_id = $_SESSION['id'];

if($_SERVER['REQUEST_METHOD'] == 'GET') {

    // Get the course id of the course that is being queried for surveys

    $retVal = array("error" => "");
    $retVal["survey_types"] = array();
    $retVal["survey_types"]["mult"] = array();
    $retVal["survey_types"]["no_mult"] = array();

    
    $allSurveyTypes = getSurveyTypes($con);

    if (count($allSurveyTypes) == 0) {
        $retVal["error"] = "There are no survey types available! :(";
    } else {
 

        foreach ($allSurveyTypes as $surveyTypeId => $surveyTypeInfo){

            $desc = $surveyTypeInfo[0];
            $file_org = $surveyTypeInfo[1];
            $display_mult = $surveyTypeInfo[2];

            $survey_type = array();
            $survey_type["id"] = $surveyTypeId;
            $survey_type["description"] = $desc;
            $survey_type["file_organization"] = $file_org;
            $survey_type["display_mult"] = $display_mult;

            if (intval($display_mult) == 1) {
                $retVal["survey_types"]["mult"][] = $survey_type;
            } else{
                $retVal["survey_types"]["no_mult"][] = $survey_type;
            }

        }

        unset($surveyTypeId, $surveyTypeInfo);

    }
    
  header("Content-Type: application/json; charset=UTF-8");

  // Now lets dump the data we found
  $myJSON = json_encode($retVal);

  echo $myJSON;
}
    
?>