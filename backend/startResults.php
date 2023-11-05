<?php
  error_reporting(-1); // reports all errors
  ini_set("display_errors", "1"); // shows all errors
  ini_set("log_errors", 1);
  session_start();

  require "lib/constants.php";
  require "lib/database.php";
  require "lib/surveyQueries.php";
  require "lib/reviewQueries.php";

  if(!isset($_SESSION['student_id'])) {
    header("Location: ".SITE_HOME."index.php");
    exit();
  }
  $id = $_SESSION['student_id'];
  $con = connectToDatabase();

  // Verify that the survey exists
  if (!empty($_GET) && isset($_GET) && isset($_GET['survey'])) {
    $survey = $_GET['survey'];
  } else {
    echo "Bad Request: Missing GET parameters";
    http_response_code(400);
    exit();
  }

  // Verify that the survey is a valid one for this student to view their results
  $survey_info = getSurveyResultsInfo($con, $survey, $id);
  if (isset($survey_info)) {
    foreach ($survey_info as $key => $value) {
      $_SESSION[$key] = $value;
    }
  } else {
    // This is not a valid survey for this student
    echo "Bad Request: Talk to your instructor about this error.";
    http_response_code(400);
    exit();
  }

  $_SESSION['reviewers'] = getReviewSources($con, $survey, $id);

  // Get the multiple choice questions and responses for this survey.
  $_SESSION['mc_topics'] = getSurveyMultipleChoiceTopics($con, $survey);
	$_SESSION['mc_answers'] = array();
  foreach ($_SESSION['mc_topics'] as $topic_id => $topic) {
    $_SESSION['mc_answers'][$topic_id] = getSurveyMultipleChoiceResponses($con, $topic_id, true);
  }

  // Get the freeform questions and responses for this survey.
  $_SESSION['ff_topics'] = getSurveyFreeformTopics($con, $survey);

  // Now redirect the user to the peer evaluation form
  header("Location: ".SITE_HOME."/showResults.php");
  exit();
?>