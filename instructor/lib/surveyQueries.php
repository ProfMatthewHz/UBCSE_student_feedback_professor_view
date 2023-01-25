<?
function getSurveyRubric($con, $survey_id) {
  $stmt = $con->prepare('SELECT course_id, start_date, expiration_date, name, rubric_id FROM surveys WHERE id=?');
  $stmt->bind_param('i', $survey_id);
  $stmt->execute();
  $result = $stmt->get_result();
  $ret_val = $result->fetch_all(MYSQLI_ASSOC);
  // reply not found on no match
  if ($result->num_rows == 0) {
    http_response_code(404);
    echo "404: Not found.";
    exit();
  }
  $stmt->close();
  return $ret_val[0];
}



function getCompletionData($con, $survey_id) {
  $ret_val = array();
  $stmt = $con->prepare('SELECT reviewer_email, COUNT(reviewers.id) expected, COUNT(evals.id) actual
                         FROM reviewers
                         LEFT JOIN evals ON evals.reviewers_id=reviewers.id
                         WHERE survey_id=?
                         GROUP BY reviewer_email');
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
  $stmt = $con->prepare('SELECT reviewer_email, SUM(score * eval_weight) 
                         FROM reviewers
                         LEFT JOIN evals on evals.reviewers_id=reviewers.id
                         LEFT JOIN scores2 ON evals.id=scores2.eval_id
                         LEFT JOIN rubric_scores ON rubric_scores.id=scores2.score_id
                         LEFT JOIN rubric_topics ON rubric_topics.id=scores2.topic_id
                         WHERE survey_id=? AND question_response <> "'.FREEFORM_QUESTION_TYPE.'"
                         GROUP BY reviewer_email');
  foreach (array_keys($teammates) as $email) {
    $stmt->bind_param('is',$survey_id, $email);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_array(MYSQLI_NUM)) {
      $ret_val[$row[0]] = $row[1];
    }
  }
  $stmt->close();
  return $ret_val;
}

function getReviewerData($con, $survey_id) {
  $ret_val = array();
  $stmt = $con->prepare('SELECT DISTINCT reviewer_email, students.name
                         FROM reviewers 
                         INNER JOIN students ON reviewers.reviewer_email=students.email 
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

function getRevieweeData($con, $survey_id) {
  $ret_val = array();
  $stmt = $con->prepare('SELECT DISTINCT teammate_email, students.name
                         FROM reviewers 
                         INNER JOIN students ON reviewers.teammate_email=students.email 
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
  $stmt = $con->prepare('SELECT reviewer_email, eval_weight 
                         FROM reviewers
                         WHERE survey_id=? AND teammate_email=?');
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
  $stmt = $con->prepare('SELECT reviewer_email, topic_id, score 
                         FROM reviewers
                         LEFT JOIN evals on evals.reviewers_id=reviewers.id 
                         LEFT JOIN scores2 ON evals.id=scores2.eval_id
                         LEFT JOIN rubric_scores ON rubric_scores.id=scores2.score_id
                         WHERE survey_id=? AND teammate_email=? AND topic_id IS NOT NULL');
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