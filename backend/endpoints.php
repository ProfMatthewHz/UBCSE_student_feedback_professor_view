<?php
require "lib/constants.php";
require "lib/studentQueries.php";
require "lib/database.php";
require "lib/surveyQueries.php";

error_reporting(-1); // reports all errors
ini_set("display_errors", "1"); // shows all errors
ini_set("log_errors", 1);
session_start();

if(!isset($_SESSION['student_id']) && !isset($_SESSION['id'])) {
    http_response_code(511);
    echo json_encode(array('error' => 'Bad request: Request only valid from within app'));
    exit();
}

header('Content-Type: application/json');

$con = connectToDatabase();
$month = idate('m');
$term = MONTH_MAP_SEMESTER[$month];
$year = idate('Y');

$response = [];

$student_id = $_SESSION['student_id'];
$ubit = trim($_SESSION['ubit']);
$past_surveys = getClosedSurveysForTerm($con, $term, $year, $student_id);
$current_surveys = getCurrentSurveys($con, $student_id);
$upcoming_surveys = getUpcomingSurveys($con, $student_id);

// grabbing all the past surveys //
$pastSurveysResponse = [];

if(count($past_surveys) > 0) {
    foreach ($past_surveys as $key => $value) {
        if ($past_surveys[$key][5]) {
            $pastSurveyData = [
                'surveyID' => $key,
                'courseName' => $value[0],
                'surveyName' => $value[1],
                'closingDate' => $value[2],
                'openingDate' => $value[7],
                'email' => $ubit."@buffalo.edu"
            ];
            $pastSurveysResponse[] = $pastSurveyData;
        }
    }
}

// grab all the current surveys //
$currentSurveysResponse = [];

if(count($current_surveys) > 0) {
    foreach ($current_surveys as $key => $value) {
        $currentSurveyData = [
            'surveyID' => $key,
            'courseName' => $value[0],
            'surveyName' => $value[1],
            'closingDate' => $value[2],
            'openingDate' => $value[7],
            'completionRate' => round(getCompletionRate($con, $key, $student_id), 2)
        ];
        $currentSurveysResponse[] = $currentSurveyData;
    }
}

// grab all the upcoming surveys //
$upcomingSurveysResponse = [];
if(count($upcoming_surveys) > 0) {
    foreach ($upcoming_surveys as $key => $value) {
        $upcomingSurveyData = [
            'surveyID' => $key,
            'courseName' => $value[0],
            'surveyName' => $value[1],
            'openingDate' => $value[2],
            'closingDate' => $value[7],
        ];
        $upcomingSurveysResponse[] = $upcomingSurveyData;
    }
}

// get users surveys api endpoint //
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $response = array("current" => $currentSurveysResponse, "future" => $upcomingSurveysResponse, "past" => $pastSurveysResponse);
    $json_encode = json_encode($response);
    echo $json_encode;
    exit();
}
?>