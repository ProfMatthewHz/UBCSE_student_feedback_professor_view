<?php
//require_once __DIR__ . '/../vendor/autoload.php';
//use Firebase\JWT\JWT;
//use Firebase\JWT\Key;


error_reporting(-1); // reports all errors
ini_set("display_errors", "1"); // shows all errors
ini_set("log_errors", 1);
session_start();

require "lib/constants.php";
require "lib/studentQueries.php";
require "lib/database.php";
require "lib/surveyQueries.php";

if(!isset($_SESSION['student_id'])) {
    header("Location: ".SITE_HOME."fake_shibboleth.php");
    exit();
}

//// Validate CSRF token early in the script, this is for deployement
//if (!isset($_SESSION['csrf_token'])) {
//    http_response_code(403);
//    echo json_encode(["error" => "CSRF token validation failed."]);
//    exit();
//}
//print($_SESSION['csrf_token']);

header('Content-Type: application/json');

$con = connectToDatabase();
$month = idate('m');
$term = MONTH_MAP_SEMESTER[$month];
$year = idate('Y');

$response = [];


//$secretKey = "myAppJWTKey2024!#$";
//$jwt = $_COOKIE['student_id'];
//$alg = 'HS256';
//$decoded = JWT::decode($jwt, new Key($secretKey, $alg));
//$student_id = $decoded->data->student_id;


$student_id = $_SESSION['student_id'];
$ubit = trim($_SESSION['ubit']);
$past_surveys = getClosedSurveysForTerm($con, $term, $year, $_SESSION['student_id']);
$current_surveys = getCurrentSurveys($con, $_SESSION['student_id']);
$upcoming_surveys = getUpcomingSurveys($con, $_SESSION['student_id']);

// grabbing all the past surveys //
$pastSurveysResponse = [];

if(count($past_surveys) > 0) {
    foreach ($past_surveys as $key => $value) {
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
//else {
//    $pastSurveysResponse[] = 'No closed surveys for this term';
//}

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
            'completionRate' => getCompletionRate($con, $key)
        ];
        $currentSurveysResponse[] = $currentSurveyData;
    }
}
//else {
//    $currentSurveysResponse[] = 'No active surveys for this term';
//}

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
//else {
//    $upcomingSurveysResponse[] = 'Nothing planned yet. Check back later!';
//}


// get users surveys api endpoint //
if ($_SERVER['REQUEST_METHOD'] == 'GET') {

//    if (!isset($_GET['csrf_token']) || $_SESSION['csrf_token'] !== $_GET['csrf_token']) {
//        http_response_code(403);
//        echo "CSRF token validation failed.";
//        exit();
//    }

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