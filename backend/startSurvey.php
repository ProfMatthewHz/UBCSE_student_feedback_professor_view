<?php
  error_reporting(-1); // reports all errors
  ini_set("display_errors", "1"); // shows all errors
  ini_set("log_errors", 1);

  require "lib/constants.php";
  require "lib/database.php";
  require "lib/reviewQueries.php";
  require "lib/surveyQueries.php";
  require "lib/loginStatus.php";

  $student_id = getStudentId();

  $con = connectToDatabase();

  // Verify that the survey exists
  if (!empty($_POST) && isset($_POST['survey'])) {
    $survey = $_POST['survey'];
    // $survey = '47';
  } else {
    echo "Bad Request: Missing POST parameters";
    http_response_code(400);
    exit();
  }

  // Verify that the survey is a valid one for this student to be taking
  $survey_info = getActiveSurveyInfo($con, $survey, $student_id);
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

  $members = null;
  $prompt = null;
  if ($survey_info['survey_type'] != 6) {
    // Setup the names and ids for the student's to review
    $members = getIndividualEvaluationTargets($con, $survey, $student_id);
    $prompt = "Team Member";
  } else {
    // This is a team survey, so we need to get the team members
    $members = getTeamEvaluationTargets($con, $survey, $student_id);
    $prompt = "Team";
  }

  // Get the questions and responses for this survey. For now, this will be hard coded.
  $mcTopics = getSurveyMultipleChoiceTopics($con, $survey);
	$_SESSION['mc_answers'] = array();
  foreach ($mcTopics as $topic_id => $topic) {
    $_SESSION['mc_answers'][$topic_id] = getSurveyMultipleChoiceResponses($con, $topic_id, false);
  }

  // Get the freeform questions and responses for this survey.
  $ffTopics = getSurveyFreeformTopics($con, $survey);

  $topics = array();
  foreach ($mcTopics as $topic_id => $topic) {
    $topic_data = array(
      'topic_id' => $topic_id,
      'question' => $topic,
      'responses' => $_SESSION['mc_answers'][$topic_id]
    );
    $topics[] = $topic_data;
  }
  
  
  $data = array(
    'topics' => $topics,
    'freeform' => $ffTopics,
    'group_members' => $members,
    'prompt' => $prompt,
    'id' => $student_id 
  );
      
  header('Access-Control-Allow-Origin: '.FRONTEND_SERVER);
  header('Access-Control-Allow-Credentials: true');
  header('Content-Type: application/json');
  echo json_encode($data);
?>