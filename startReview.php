<?php
  error_reporting(-1); // reports all errors
  ini_set("display_errors", "1"); // shows all errors
  ini_set("log_errors", 1);
  session_start();
  require "lib/constants.php";

  if(!isset($_SESSION['email'])) {
    header("Location: ".SITE_HOME."index.php");
    exit();
  }
  $email = $_SESSION['email'];
  require "lib/database.php";
  require "lib/surveyQueries.php";
  $con = connectToDatabase();

  // Verify that the survey exists
  if (!empty($_GET) && isset($_GET) && isset($_GET['survey'])) {
    $survey = $_GET['survey'];
  } else {
    echo "Bad Request: Missing GET parameters";
    http_response_code(400);
    exit();
  }

  // Verify that the survey is a valid one for this student.
  if (validCompletedSurvey($con, $survey, $email)) {
    $_SESSION['survey_id'] = $survey;
  } else {
    // This is not a valid survey for this student
    echo "Bad Request: Talk to your instructor about this error.";
    http_response_code(400);
    exit();
  }

  initializeRevieweeData($con, $survey, $email);

  // Get the questions and responses for this survey. For now, this will be hard coded.
  $_SESSION['topics'] = getSurveyTopics($con, $survey);
	$_SESSION['answers'] = array();
	$_SESSION['answers']['TEAMWORK'] = array('Does not willingly assume team roles, rarely completes assigned work',
																					 'Usually accepts assigned team roles, occasionally completes assigned work',
																					 'Accepts assigned team roles, mostly completes assigned work',
																					 'Accepts all assigned team roles, always completes assigned work');
	$_SESSION['answers']['LEADERSHIP'] = array('Rarely takes leadership role, does not collaborate, sometimes willing to assist teammates',
																						'Occasionally shows leadership, mostly collaborates, generally willing to assist teammates',
																						'Shows an ability to lead when necessary, willing to collaborate, willing to assist teammates',
																						'Takes leadership role, is a good collaborator, always willing to assist teammates.');
	$_SESSION['answers']['PARTICIPATION'] = array('Often misses meetings, routinely unprepared for meetings, rarely participates in meetings and doesn\'t share ideas',
																								'Occasionally misses/doesn\'t participate in meetings, somewhat unprepared for meetings, offers unclear/unhelpful ideas',
																								'Attends and participates in most meetings, comes prepared, offers useful ideas',
																								'Attends and participates in all meetings, comes prepared, and clearly expresses well-developed ideas');
	$_SESSION['answers']['PROFESSIONALISM'] = array('Often discourteous and/or openly critical of teammates, doesn\'t listen to alternative perspectives',
																									'Often courteous to teammates, usually appreciates teammates perspectives but often unwilling to consider them',
																									'Mostly courteous to teammates, values teammates\' perspectives and often willing to consider them',
																									'Always courteous to teammates, values teammates\' perspectives, knowledge, and experience, and always willing to consider them');
	$_SESSION['answers']['QUALITY'] = array('Rarely commits to shared documents, others often required to revise, debug, or fix their work',
																					'Occasionally commits to shared documents, others sometimes needed to revise, debug, or fix their work',
																					'Often commits to shared documents, others occasionally needed to revise, debug, or fix their work',
																					'Frequently commits to shared documents, others rarely need to revise, debug, or fix their work');
	$_SESSION['scores'] = array('Unsatisfactory', 'Developing', 'Satisfactory', 'Exemplary');

  // Now redirect the user to the peer evaluation form
  header("Location: ".SITE_HOME."/evalReview.php");
  exit();
?>