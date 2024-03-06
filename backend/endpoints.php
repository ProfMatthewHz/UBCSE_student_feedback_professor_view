<?php
require_once __DIR__ . '/../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;


error_reporting(-1); // reports all errors
ini_set("display_errors", "1"); // shows all errors
ini_set("log_errors", 1);

require "lib/constants.php";
require "lib/studentQueries.php";
require "lib/database.php";
require "lib/surveyQueries.php";

header('Content-Type: application/json');

$con = connectToDatabase();
$month = idate('m');
$term = MONTH_MAP_SEMESTER[$month];
$year = idate('Y');

$response = [];


//if(empty($_SERVER['student_id'])) {
//    http_response_code(400);
//    $response['error'] = 'missing student_id';
//    echo json_encode($response);
//    exit();
//}

$secretKey = "myAppJWTKey2024!#$";
$jwt = $_COOKIE['student_id'];
$alg = 'HS256';
$decoded = JWT::decode($jwt, new Key($secretKey, $alg));
$student_id = $decoded->data->student_id;

//$student_id = $_SERVER['student_id'];

$past_surveys = getClosedSurveysForTerm($con, $term, $year, $student_id);
$current_surveys = getCurrentSurveys($con,   $student_id);
$upcoming_surveys = getUpcomingSurveys($con,   $student_id);

// grabbing all the past surveys //
$pastSurveysResponse = [];

if(count($past_surveys) > 0) {
    foreach ($past_surveys as $key => $value) {
        $pastSurveyData = [
            'surveyID' => $key,
            'courseName' => $value[0],
            'surveyName' => $value[1],
            'closedDate' => $value[2]
        ];
        $pastSurveysResponse[] = $pastSurveyData;
    }
} else {
    $pastSurveysResponse[] = 'No closed surveys for this term';
}

// grab all the current surveys //
$currentSurveysResponse = [];

if(count($current_surveys) > 0) {
    foreach ($current_surveys as $key => $value) {
        $currentSurveyData = [
            'surveyID' => $key,
            'courseName' => $value[0],
            'surveyName' => $value[1],
            'deadlineDate' => $value[2]
        ];
        $currentSurveysResponse[] = $currentSurveyData;
    }
} else {
    $currentSurveysResponse[] = 'No active surveys for this term';
}

// grab all the upcoming surveys //
$upcomingSurveysResponse = [];

if(count($upcoming_surveys) > 0) {
    foreach ($upcoming_surveys as $key => $value) {
        $upcomingSurveyData = [
            'surveyID' => $key,
            'courseName' => $value[0],
            'surveyName' => $value[1],
            'openingDate' => $value[2]
        ];
        $upcomingSurveysResponse[] = $upcomingSurveyData;
    }
} else {
    $upcomingSurveysResponse[] = 'Nothing planned yet. Check back later!';
}


// get users surveys api endpoint //
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (isset($_GET['type'])) {
        $type = $_GET['type'];
        switch ($type) {
            case 'upcoming':
                $response = $upcomingSurveysResponse;
                break;
            case 'current':
                $response = $currentSurveysResponse;
                break;
            case 'past':
                $response = $pastSurveysResponse;
                break;
            default:
                http_response_code(400); // Bad request
                $response = ['error' => 'Invalid survey type specified'];
                break;

        }
    } else {
        http_response_code(400); // Bad request
        $response = ['error' => 'No survey type specified'];
    }

    echo json_encode($response);
}






















?>