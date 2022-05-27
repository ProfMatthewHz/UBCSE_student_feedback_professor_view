<?php
  function handleSurveyQuery($db_connection, $survey_id, $email, $addl_query) {
    $base_query = 'SELECT DISTINCT course.name course_name, surveys.name survey_name FROM reviewers
                   INNER JOIN surveys ON reviewers.survey_id = surveys.id 
                   INNER JOIN course on course.id = surveys.course_id 
                   WHERE surveys.id=? AND reviewers.reviewer_email=? AND '.$addl_query;
    $stmt_request = $db_connection->prepare($base_query);
    $stmt_request->bind_param('is', $survey_id, $email);
    $stmt_request->execute();
    $result = $stmt_request->get_result();
    if ($row = $result->fetch_row()){
      $_SESSION['course_name'] = $row[0];
      $_SESSION['survey_name'] = $row[1];
      $stmt_request->close();
      return true;
    }
    $stmt_request->close();
    return false;
  }

  function validCompletedSurvey($db_connection, $survey_id, $email) {
    $query_str = 'surveys.expiration_date < NOW()';
    return handleSurveyQuery($db_connection, $survey_id, $email, $query_str);
  }

  function validActiveSurvey($db_connection, $survey_id, $email) {
    $query_str = 'surveys.start_date <= NOW() AND surveys.expiration_date > NOW()';
    return handleSurveyQuery($db_connection, $survey_id, $email, $query_str);
  }

  function initializeReviewerData($db_connection, $survey_id, $email) {
    $retVal = array();
    $query_str = 'SELECT reviewers_id
                  FROM reviewers
                  INNER JOIN evals ON evals.reviewers_id = reviewers.id
                  WHERE reviewers.survey_id =? AND reviewers.teammate_email=?';
    $stmt_group = $db_connection->prepare($query_str);
    $stmt_group->bind_param('is',$survey_id,$email);
    $stmt_group->execute();
    $result = $stmt_group->get_result();
    while ($row = $result->fetch_array(MYSQLI_NUM)) {
      $retVal[] = $row[0];
    }
    $stmt_group->close();
    return $retVal;
  }

  function initializeRevieweeData($db_connection, $survey_id, $email) {
    $retVal = array();
    $query_str = 'SELECT reviewers.id, students.name FROM reviewers
                  INNER JOIN students ON reviewers.teammate_email = students.email 
                  WHERE reviewers.survey_id =? AND reviewers.reviewer_email=?';
    $stmt_group = $db_connection->prepare($query_str);
    $stmt_group->bind_param('is',$survey_id,$email);
    $stmt_group->execute();
    $result = $stmt_group->get_result();
    while ($row = $result->fetch_array(MYSQLI_NUM)) {
      $retVal[$row[0]] = $row[1];
    }
    $stmt_group->close();
    return $retVal;
  }

  function getSurveyTopics($db_connection, $survey_id) {
    $retVal = array();
    $query_str = 'SELECT rubric_topics.id, question 
                  FROM rubric_topics 
                  INNER JOIN surveys ON surveys.rubric_id = rubric_topics.rubric_id
                  WHERE surveys.id = ?
                  ORDER BY rubric_topics.id';
    $stmt_topics = $db_connection->prepare($query_str);
    $stmt_topics->bind_param('i', $survey_id);
    $stmt_topics->execute();
    $result = $stmt_topics->get_result();
    while ($row = $result->fetch_array(MYSQLI_NUM)) {
      $retVal[$row[0]] = strtoupper($row[1]);
    }
    $stmt_topics->close();
    return $retVal;
  }

  function getSurveyResponses($db_connection, $topic_id) {
    $retVal = array();
    $query_str = 'SELECT score_id, response 
                  FROM rubric_responses 
                  WHERE topic_id = ? 
                  ORDER BY score_id';
    $stmt_responses = $db_connection->prepare($query_str);
    $stmt_responses->bind_param('i', $topic_id);
    $stmt_responses->execute();
    $result = $stmt_responses->get_result();
    while ($row = $result->fetch_array(MYSQLI_NUM)) {
      $retVal[$row[0]] = $row[1];
    }
    $stmt_responses->close();
    return $retVal;
  }

  function getSurveyScores($db_connection, $survey_id) {
    $retVal = array();
    $query_str = 'SELECT rubric_scores.id, score
                  FROM rubric_scores
                  INNER JOIN surveys ON surveys.rubric_id = rubric_scores.rubric_i
                  WHERE surveys.id = ?
                  ORDER BY rubric_scores.score';
    $stmt_scores = $db_connection->prepare($query_str);
    $stmt_scores->bind_param('i', $survey_id);
    $stmt_scores->execute();
    $result = $stmt_scores->get_result();
    while ($row = $result->fetch_array(MYSQLI_NUM)) {
      $retVal[$row[0]] = $row[1];
    }
    return $retVal;
  }
?>