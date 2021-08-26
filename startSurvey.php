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
  // TECHNICAL DEBT TODO: Make this into a separate function
  $stmt_request = $con->prepare('SELECT DISTINCT course.name
                                  FROM reviewers
                                  INNER JOIN surveys ON reviewers.survey_id = surveys.id 
                                  INNER JOIN course on course.id = surveys.course_id 
                                  WHERE surveys.id=? AND reviewers.reviewer_email=? AND surveys.start_date <= NOW() AND surveys.expiration_date > NOW()');
  $stmt_request->bind_param('is', $survey, $email);
  $stmt_request->execute();
  $result = $stmt_request->get_result();
  if ($row = $result->fetch_row()){
    $_SESSION['course'] = $row[0];
    $_SESSION['survey_id'] = $survey;
  } else {
    // This is not a valid survey for this student
    echo "Bad Request: Talk to your instructor about this error.";
    http_response_code(400);
    exit();
  }
  $stmt_request->close();

  // Get the list of students being evaluated and initialize the variable count
  $_SESSION['group_member_number'] = 0;

  $group_members=array();
  $group_ids=array();
  $stmt_group = $con->prepare('SELECT students.name, reviewers.id FROM reviewers
  INNER JOIN students ON reviewers.teammate_email = students.email WHERE reviewers.survey_id =? AND reviewers.reviewer_email=?');
  $stmt_group->bind_param('is',$survey,$email);
  $stmt_group->execute();
  $stmt_group->bind_result($group_member,$review_id);
  $stmt_group->store_result();
  while ($stmt_group->fetch()) {
    array_push($group_members,$group_member);
    array_push($group_ids,$review_id);
  }
  $stmt_group->close();
  $unfinished_evals = count($group_ids);
  foreach ($group_ids as $review_id) {
    $stmt_complete = $con->prepare('SELECT evals_id FROM scores INNER JOIN evals ON scores.evals_id=evals.id WHERE score1 <> -1 AND score2 <> -1 AND score3 <> -1 AND score4 <> -1 AND score5 <> -1 AND reviewers_id=?');
    $stmt_complete->bind_param('i',$review_id);
    $stmt_complete->execute();
    if ($stmt_complete->fetch()) {
      $unfinished_evals = $unfinished_evals - 1;
    }
    $stmt_complete->close();
  }
  $_SESSION['group_members'] = $group_members;
  $_SESSION['group_ids'] = $group_ids;

  // Get the questions and responses for this survey. For now, this will be hard coded.
  $_SESSION['topics'] = array('TEAMWORK', 'LEADERSHIP', 'PARTICIPATION', 'PROFESSIONALISM', 'QUALITY');
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
  $loc_string = "Location: ".SITE_HOME."/evalConfirm.php";
  header($loc_string);
  exit();
?>