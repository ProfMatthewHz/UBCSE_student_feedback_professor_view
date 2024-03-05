<?php
error_reporting(-1); // reports all errors
ini_set("display_errors", "1"); // shows all errors
ini_set("log_errors", 1);

require "lib/constants.php";
require "lib/database.php";
require "lib/studentQueries.php";
require "lib/database.php";
require "lib/surveyQueries.php";

$con = connectToDatabase();
$month = idate('m');
$term = MONTH_MAP_SEMESTER[$month];
$year = idate('Y');

if(!empty($_SERVER['student_id'])) {
    header("Location: ".SITE_HOME);
    exit();
}

$student_id = $_SERVER['student_id'];

$past_surveys = getClosedSurveysForTerm($con, $term, $year, $student_id);
$current_surveys = getCurrentSurveys($con,   $student_id);
$upcoming_surveys = getUpcomingSurveys($con,   $student_id);





















?>