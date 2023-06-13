<?php
  function getSurveyResultsInfo($db_connection, $survey_id, $student_id) {
    $query_str = 'surveys.end_date <= NOW()';
    $student_id_field = 'reviewed_id';
    return handleSurveyQuery($db_connection, $survey_id, $student_id, $student_id_field, $query_str);
  }

  function getCompletedSurveyInfo($db_connection, $survey_id, $student_id) {
    $query_str = 'surveys.end_date <= NOW()';
    $student_id_field = 'reviewer_id';
    return handleSurveyQuery($db_connection, $survey_id, $student_id, $student_id_field, $query_str);
  }

  function getActiveSurveyInfo($db_connection, $survey_id, $student_id) {
    $query_str = 'surveys.start_date <= NOW() AND surveys.end_date > NOW()';
    $student_id_field = 'reviewer_id';
    return handleSurveyQuery($db_connection, $survey_id, $student_id, $student_id_field, $query_str);
  }

  function handleSurveyQuery($db_connection, $survey_id, $student_id, $student_id_field, $addl_query) {
    // Pessimistically assume this fails
    $ret_val = null;
    $query = 'SELECT DISTINCT courses.name course_name, surveys.name survey_name 
              FROM surveys
              INNER JOIN reviews ON reviews.survey_id = surveys.id 
              INNER JOIN courses on courses.id = surveys.course_id 
              WHERE surveys.id=? AND reviews.'.$student_id_field.'=? AND '.$addl_query;
    $stmt = $db_connection->prepare($query);
    $stmt->bind_param('ii', $survey_id, $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_row()) {
      $ret_val = array("survey_id" => $survey_id, "course_name" => $row[0], "survey_name" => $row[1]);
    }
    $stmt->close();
    return $ret_val;
  }


  function getSurveyMultipleChoiceTopics($db_connection, $survey_id) {
    $ret_val = array();
    $query_str = 'SELECT rubric_topics.id, question
                  FROM surveys 
                  INNER JOIN rubric_topics ON surveys.rubric_id = rubric_topics.rubric_id
                  WHERE surveys.id = ? AND rubric_topics.question_response = "'.MC_QUESTION_TYPE.'"
                  ORDER BY rubric_topics.id';
    $stmt_topics = $db_connection->prepare($query_str);
    $stmt_topics->bind_param('i', $survey_id);
    $stmt_topics->execute();
    $result = $stmt_topics->get_result();
    while ($row = $result->fetch_array(MYSQLI_NUM)) {
      $ret_val[$row[0]] = strtoupper($row[1]);
    }
    $stmt_topics->close();
    return $ret_val;
  }

  function getSurveyFreeformTopics($db_connection, $survey_id) {
    $ret_val = array();
    $query_str = 'SELECT rubric_topics.id, question
                  FROM surveys 
                  INNER JOIN rubric_topics ON surveys.rubric_id = rubric_topics.rubric_id
                  WHERE surveys.id = ? AND rubric_topics.question_response = "'.FREEFORM_QUESTION_TYPE.'"
                  ORDER BY rubric_topics.id';
    $stmt_topics = $db_connection->prepare($query_str);
    $stmt_topics->bind_param('i', $survey_id);
    $stmt_topics->execute();
    $result = $stmt_topics->get_result();
    while ($row = $result->fetch_array(MYSQLI_NUM)) {
      $ret_val[$row[0]] = strtoupper($row[1]);
    }
    $stmt_topics->close();
    return $ret_val;
  }

  function getSurveyMultipleChoiceResponses($db_connection, $topic_id, $include_score) {
    $ret_val = array();
    $query_str = 'SELECT rubric_responses.rubric_score_id, response, score
                  FROM rubric_responses
                  INNER JOIN rubric_scores ON rubric_scores.id = rubric_responses.rubric_score_id
                  WHERE topic_id = ? 
                  ORDER BY rubric_responses.rubric_score_id';
    $stmt_responses = $db_connection->prepare($query_str);
    $stmt_responses->bind_param('i', $topic_id);
    $stmt_responses->execute();
    $result = $stmt_responses->get_result();
    while ($row = $result->fetch_array(MYSQLI_NUM)) {
      if ($include_score) {
        $ret_val[$row[0]] = array($row[1], $row[2]);
      } else {
        $ret_val[$row[0]] = $row[1];
      }
    }
    $stmt_responses->close();
    return $ret_val;
  }

  function createQueryReviewer($con, $date_clause) {
    $sql = 'SELECT courses.name, surveys.name, surveys.id, surveys.start_date, surveys.end_date, COUNT(reviews.id), COUNT(evals.id)
            FROM surveys
            INNER JOIN courses on courses.id = surveys.course_id 
            INNER JOIN reviews on reviews.survey_id=surveys.id
            LEFT JOIN evals on evals.review_id=reviews.id
            WHERE reviews.reviewer_id=? AND courses.semester=? AND courses.year=?';
    if (!empty($date_clause)) {
      $sql = $sql . ' AND ' . $date_clause;
    }
    $sql = $sql.' GROUP BY surveys.id';
    $retVal = $con->prepare($sql);
    return $retVal;
  }

  function createQueryReviewed($con, $date_clause) {
    $sql = 'SELECT courses.name, surveys.name, surveys.id, surveys.start_date, surveys.end_date, COUNT(evals.id)
            FROM surveys
            INNER JOIN courses on courses.id = surveys.course_id 
            INNER JOIN reviews on reviews.survey_id=surveys.id
            LEFT JOIN evals on evals.review_id=reviews.id
            WHERE reviews.reviewed_id=? AND reviews.reviewer_id<>? AND courses.semester=? AND courses.year=?';
    if (!empty($date_clause)) {
      $sql = $sql . ' AND ' . $date_clause;
    }
    $sql = $sql.' GROUP BY surveys.id';
    $retVal = $con->prepare($sql);
    return $retVal;
  }

  function chronologicalComparator($a, $b) {
    $a_datetime = $a[1];
    $b_datetime = $b[1];
    if ($a_datetime < $b_datetime) {
      return 1;
    } else if ($a_datetime > $b_datetime) {
      return -1;
    } else {
      return 0;
    }
  }

  function getClosedSurveysForTerm($con, $term, $year, $id) {
    $retVal = array();
    $stmt = createQueryReviewer($con, 'surveys.end_date < NOW()');
    $stmt->bind_param('iii', $id, $term, $year);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_array(MYSQLI_NUM)) {
      $e = new DateTime($row[4]);
      $fully_submitted = ($row[5] == $row[6]);
      $retVal[$row[2]] = array($row[0], $row[1], $e, true, $fully_submitted, false, false);
    }
    $stmt->close();

    $stmt = createQueryReviewed($con, 'surveys.end_date < NOW()');
    $stmt->bind_param('iiii', $id, $id, $term, $year);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_array(MYSQLI_NUM)) {
      if (array_key_exists($row[2], $retVal)) {
        $survey = $retVal[$row[2]];
        // Update that they could be reviewed on this survey
        $survey[5] = true;
        // Update that they were reviewed on this survey
        $survey[6] = ($row[5] > 0);
        $retVal[$row[2]] = $survey;
      } else {
        $e = new DateTime($row[4]);
        $evaluated = ($row[5] > 0);
        $retVal[$row[2]] = array($row[0], $row[1], $e, false, false, true, $evaluated);
      }
    }
    $stmt->close();
    // Sort the array to be in chronological order
    uasort($retVal, 'chronologicalComparator');
    return $retVal;
  }

  function getCurrentSurveysForTerm($con, $term, $year, $id) {
    $retVal = array();
    $stmt = createQueryReviewer($con, 'surveys.start_date <= NOW() AND surveys.end_date > NOW()');
    $stmt->bind_param('iii', $id, $term, $year);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_array(MYSQLI_NUM)) {
      $e = new DateTime($row[4]);
      $fully_submitted = ($row[5] == $row[6]);
      $retVal[$row[2]] = array($row[0], $row[1], $e, true, $fully_submitted, false, false);
    }
    $stmt->close();
    // Sort the array to be in chronological order
    uasort($retVal, 'chronologicalComparator');
    return $retVal;
  }

  function getUpcomingSurveysForTerm($con, $term, $year, $id) {
    $retVal = array();
    $stmt = createQueryReviewer($con, 'surveys.start_date > NOW()');
    $stmt->bind_param('iii', $id, $term, $year);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_array(MYSQLI_NUM)) {
      $s = new DateTime($row[3]);
      $retVal[$row[2]] = array($row[0], $row[1], $s, true, false, false, false);
    }
    $stmt->close();
    // Sort the array to be in chronological order
    uasort($retVal, 'chronologicalComparator');
    return $retVal;
  }
?>