<?
function deleteSurvey($con, $survey_id) {
  $stmt = $con->prepare('DELETE FROM surveys WHERE id=?');
  $stmt->bind_param('i', $survey_id);
  $retVal = $stmt->execute();
  $stmt->close();
  return $retVal;
}

function insertSurvey($con, $course_id, $name, $start, $end, $rubric_id) {
  $stmt = $con->prepare('INSERT INTO surveys (course_id, name, start_date, end_date, rubric_id) VALUES (?, ?, ?, ?, ?)');
  $stmt->bind_param('isssi', $course_id, $name, $start, $end, $rubric_id);
  $stmt->execute();
  $survey_id = $con->insert_id;
  $stmt->close();
  return $survey_id;
}

function updateSurvey($con, $survey_id, $name, $start, $end, $rubric_id) {
  $stmt = $con->prepare('UPDATE surveys SET name = ?, start_date = ?, end_date = ?, rubric_id = ? WHERE id = ?');
  $stmt->bind_param('sssii', $name, $start, $end, $rubric_id, $survey_id);
  $retVal = $stmt->execute();
  $stmt->close();
  return $retVal;
}

function isSurveyInstructor($con, $survey_id, $instructor_id) {
  $stmt = $con->prepare('SELECT surveys.course_id 
                         FROM surveys
                         INNER JOIN courses ON surveys.course_id=courses.id
                         INNER JOIN course_instructors ON courses.id=course_instructors.course_id
                         WHERE surveys.id=? AND course_instructors.instructor_id=?');
  $stmt->bind_param('ii', $survey_id, $instructor_id);
  $stmt->execute();
  $result = $stmt->get_result();
  $data = $result->fetch_all(MYSQLI_ASSOC);
  $retVal = $result->num_rows > 0;
  $stmt->close();
  return $retVal;
}

function getSurveyData($con, $survey_id) {
  // Pessimistically assume that this fails
  $retVal = null;
  $stmt = $con->prepare('SELECT course_id, start_date, end_date, name, rubric_id 
                         FROM surveys 
                         WHERE id=?');
  $stmt->bind_param('i', $survey_id);
  $stmt->execute();
  $result = $stmt->get_result();
  $row = $result->fetch_all(MYSQLI_ASSOC);
  // reply not found on no match
  if ($result->num_rows > 0) {
    $retVal = $row[0];
  }
  $stmt->close();
  return $retVal;
}


function getCompletionData($con, $survey_id) {
  $ret_val = array();
  $stmt = $con->prepare('SELECT reviewer.email, COUNT(reviews.id) expected, COUNT(evals.id) actual
                         FROM reviews
                         INNER JOIN students reviewer ON reviews.reviewer_id=students.id
                         LEFT JOIN evals ON evals.reviews_id=reviews.id
                         WHERE survey_id=?
                         GROUP BY reviewer.email');
  $stmt->bind_param('i', $survey_id);
  $stmt->execute();
  $result = $stmt->get_result();
  while ($row = $result->fetch_array(MYSQLI_NUM)) {
    $email_addr = $row[0];
    $completed = ($row[1] == $row[2]);
    $ret_val[$email_addr] = $completed;
  }
  $stmt->close();
  return $ret_val;
}

function getSurveyTotals($con, $survey_id, $teammates) {
  $ret_val = array();
  $stmt = $con->prepare('SELECT reviewer.email, SUM(score * eval_weight) 
                         FROM reviews
                         INNER JOIN students reviewer ON reviews.reviewer_id=students.id
                         LEFT JOIN evals on evals.reviews_id=reviews.id
                         LEFT JOIN scores2 ON evals.id=scores2.eval_id
                         LEFT JOIN rubric_scores ON rubric_scores.id=scores2.score_id
                         LEFT JOIN rubric_topics ON rubric_topics.id=scores2.topic_id
                         WHERE survey_id=? AND question_response <> "'.FREEFORM_QUESTION_TYPE.'"
                         GROUP BY reviewer.email');
  $stmt->bind_param('i',$survey_id);
  $stmt->execute();
  $result = $stmt->get_result();
  while ($row = $result->fetch_array(MYSQLI_NUM)) {
    $ret_val[$row[0]] = $row[1];
  }
  $stmt->close();
  return $ret_val;
}

function getReviewerData($con, $survey_id) {
  $ret_val = array();
  $stmt = $con->prepare('SELECT DISTINCT email, name
                         FROM reviews 
                         INNER JOIN students ON reviews.reviewer_id=students.id 
                         WHERE survey_id=?');
  $stmt->bind_param('i', $survey_id);
  $stmt->execute();
  $result = $stmt->get_result();
  while ($row = $result->fetch_array(MYSQLI_NUM)) {
    $email_addr = $row[0];
    $ret_val[$email_addr] = $row[1];
  }
  $stmt->close();
  return $ret_val;
}

function getReviewedData($con, $survey_id) {
  $ret_val = array();
  $stmt = $con->prepare('SELECT DISTINCT email, name
                         FROM reviews 
                         INNER JOIN students ON reviews.reviewed_id=students.id 
                         WHERE survey_id=?');
  $stmt->bind_param('i', $survey_id);
  $stmt->execute();
  $result = $stmt->get_result();
  while ($row = $result->fetch_array(MYSQLI_NUM)) {
    $email_addr = $row[0];
    $ret_val[$email_addr] = $row[1];
  }
  $stmt->close();
  return $ret_val;
}

function getSurveyWeights($con, $survey_id, $teammates) {
  $ret_val = array();
  $stmt = $con->prepare('SELECT reviewer.email, eval_weight
                         FROM reviews
                         INNER JOIN students reviewer ON reviews.reviewer_id=students.id
                         INNER JOIN students reviewed ON reviews.reviewed_id=students.id
                         WHERE survey_id=? AND reviewed.email=?');
  foreach (array_keys($teammates) as $email) {
    if (!isset($ret_val[$email])) {
      $ret_val[$email] = array("total" => 0);
    }
    $stmt->bind_param('is',$survey_id, $email);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_array(MYSQLI_NUM)) {
      $reviewer = $row[0];
      $weight = $row[1];
      if (!isset($ret_val[$reviewer])) {
        $ret_val[$reviewer] = array("total" => 0);
      }
      // Record how much this review was weighted
      $ret_val[$email][$reviewer] = $weight;
      $ret_val[$reviewer]["total"] = $ret_val[$reviewer]["total"] + $weight;
    }
  }
  $stmt->close();
  return $ret_val;
}

function getSurveyScores($con, $survey_id, $teammates) {
  $ret_val = array();
  $stmt = $con->prepare('SELECT reviewer.email, topic_id, score 
                         FROM reviews
                         INNER JOIN students reviewer ON reviews.reviewer_id=students.id
                         INNER JOIN students reviewed ON reviews.reviewed_id=students.id
                         LEFT JOIN evals on evals.reviews_id=reviews.id 
                         LEFT JOIN scores2 ON evals.id=scores2.eval_id
                         LEFT JOIN rubric_scores ON rubric_scores.id=scores2.score_id
                         WHERE survey_id=? AND reviewed.email=? AND topic_id IS NOT NULL');
  foreach (array_keys($teammates) as $email) {
    // Create the space for this teammate -- ASSUMES TEAMMATES ARE UNIQUE
    $ret_val[$email] = array();
    $stmt->bind_param('is',$survey_id, $email);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_array(MYSQLI_NUM)) {
      // Create space the first time we see the teammate reviewed by the reviewer
      if (!isset($ret_val[$email][$row[0]])) {
        $ret_val[$email][$row[0]] = array();
      }
      $ret_val[$email][$row[0]][$row[1]] = $row[2];
    }
  }
  $stmt->close();
  return $ret_val;
}
?>