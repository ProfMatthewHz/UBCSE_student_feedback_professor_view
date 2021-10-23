<?php
  function handleSurveyQuery($db_connection, $survey, $email, $query) {
    $stmt_request = $db_connection->prepare($query);
    $stmt_request->bind_param('is', $survey, $email);
    $stmt_request->execute();
    $result = $stmt_request->get_result();
    if ($row = $result->fetch_row()){
      $_SESSION['course'] = $row[0];
      $stmt_request->close();
      return true;
    }
    $stmt_request->close();
    return false;
  }

  function validCompletedSurvey($db_connection, $survey, $email) {
    $query_str = 'SELECT DISTINCT course.name FROM reviewers
                  INNER JOIN surveys ON reviewers.survey_id = surveys.id 
                  INNER JOIN course on course.id = surveys.course_id 
                  WHERE surveys.id=? AND reviewers.reviewer_email=? AND surveys.expiration_date < NOW()';
    return handleSurveyQuery($db_connection, $survey, $email, $query_str);
  }

  function validActiveSurvey($db_connection, $survey, $email) {
    $query_str = 'SELECT DISTINCT course.name FROM reviewers
                  INNER JOIN surveys ON reviewers.survey_id = surveys.id 
                  INNER JOIN course on course.id = surveys.course_id 
                  WHERE surveys.id=? AND reviewers.reviewer_email=? AND surveys.start_date <= NOW() AND surveys.expiration_date > NOW()';
    return handleSurveyQuery($db_connection, $survey, $email, $query_str);
  }

  function initializeRevieweeData($db_connection, $survey, $email) {
    $_SESSION['group_members']=array();
    $_SESSION['group_ids']=array();
    $query_str = 'SELECT students.name, reviewers.id FROM reviewers
                  INNER JOIN students ON reviewers.teammate_email = students.email WHERE reviewers.survey_id =? AND reviewers.reviewer_email=?';
    $stmt_group = $db_connection->prepare($query_str);
    $stmt_group->bind_param('is',$survey,$email);
    $stmt_group->execute();
    $result = $stmt_group->get_result();
    while ($row = $result->fetch_array(MYSQLI_NUM)) {
      array_push($_SESSION['group_members'], $row[0]);
      array_push($_SESSION['group_ids'], $row[1]);
    }
    $stmt_group->close();
  }

  function getSurveyTopics($db_connection, $survey) {
    $retVal = array();
    $query_str = 'SELECT question FROM rubric_topics INNER JOIN surveys ON surveys.rubric_id = rubric_topics.rubric_id WHERE surveys.id = ? ORDER BY rubric_topics.id';
    $stmt_topics = $db_connection->prepare($query_str);
    $stmt_topics->bind_param('i', $survey);
    $stmt_topics->execute();
    $result = $stmt_topics->get_result();
    while ($row = $result->fetch_array(MYSQLI_NUM)) {
      array_push($retVal, $row[0]);
    }
    return $retVal;
  }
?>