<?php
  error_reporting(-1); // reports all errors
  ini_set("display_errors", "1"); // shows all errors
  ini_set("log_errors", 1);
  session_start();

  require "lib/constants.php";
  require "lib/database.php";
  require "lib/reviewQueries.php";
  require "lib/surveyQueries.php";
  
  if(!isset($_SESSION['student_id'])) {
    header("Location: ".SITE_HOME."index.php");
    exit();
  }
  $id = $_SESSION['student_id'];
  $con = connectToDatabase();

  // Verify that the survey exists
  if (!empty($_GET) && isset($_GET) && isset($_GET['survey'])) {
    $survey = $_GET['survey'];
    // $survey = '47';
  } else {
    echo "Bad Request: Missing GET parameters";
    http_response_code(400);
    exit();
  }

  // Verify that the survey is a valid one for this student to be taking
  $survey_info = getActiveSurveyInfo($con, $survey, $id);
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

  // Get the list of students being evaluated and initialize the variable count
  $_SESSION['group_member_number'] = 0;

  // Setup the names and ids for the student's to review
  $_SESSION['group_members'] = getReviewTargets($con, $survey, $id);

  // Get the questions and responses for this survey. For now, this will be hard coded.
  $_SESSION['mc_topics'] = getSurveyMultipleChoiceTopics($con, $survey);
	$_SESSION['mc_answers'] = array();
  $_SESSION['mc_topics_id'] = array();
  foreach ($_SESSION['mc_topics'] as $topic_id => $topic) {
    // $_SESSION['mc_answers'][$topic_id] = getSurveyMultipleChoiceResponses($con, $topic_id, false);
    $_SESSION['mc_answers'][$topic_id] = getSurveyMultipleChoiceResponses($con, $topic_id, false);
    $_SESSION['mc_topics_id'][$topic_id] = $topic_id;
  }

  // Get the freeform questions and responses for this survey.
  $_SESSION['ff_topics'] = getSurveyFreeformTopics($con, $survey);

  // Now redirect the user to the peer evaluation form
  $loc_string = "Location: ".SITE_HOME."/peerEvalForm.php";
  // header($loc_string);
  $topics = array();
  foreach ($_SESSION['mc_topics'] as $index => $topic) {
    $topic_data = array(
      'topic_id' => $_SESSION['mc_topics_id'][$index],
      'question' => $topic,
      'responses' => $_SESSION['mc_answers'][$index]
    );
    $topics[] = $topic_data;
  }
  
  $data = array(
    'topics' => $topics,
    'freeform' => $_SESSION['ff_topics'],
    'group_members' => $_SESSION['group_members']
  );
  echo json_encode($data);
  exit();
?>