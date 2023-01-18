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

function getReviewerData($con, $survey_id, &$reviewers, &$totals) {
  $stmt = $con->prepare('SELECT reviewer_email, students.name, SUM(rubric_scores.score) total_score, COUNT(DISTINCT teammate_email) expected, COUNT(DISTINCT evals.id) actual
                         FROM reviewers
                         INNER JOIN students ON reviewers.reviewer_email=students.email 
                         LEFT JOIN evals ON evals.reviewers_id=reviewers.id 
                         LEFT JOIN scores2 ON scores2.eval_id=evals.id 
                         LEFT JOIN rubric_scores ON rubric_scores.id=scores2.score_id 
                         WHERE survey_id=? 
                         GROUP BY reviewer_email, students.name');
  $stmt->bind_param('i', $survey_id);
  $stmt->execute();
  $result = $stmt->get_result();
  while ($row = $result->fetch_array(MYSQLI_NUM)) {
    $email_addr = $row[0];
    $reviewers[$email_addr] = $row[1];
    // If the reviewer completed this survey
    if ($row[3] == $row[4]) {
      // Initialize the total number of points
      $totals[$email_addr] = $row[2] / $row[3];
    }
  }
  $stmt->close();
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

function getSurveyScores($con, $survey_id, $teammates) {
  $ret_val = array();
  $stmt = $con->prepare('SELECT reviewer_email, teammate_email, topic_id, score 
                                FROM reviewers
                                LEFT JOIN evals on evals.reviewers_id=reviewers.id 
                                LEFT JOIN scores2 ON evals.id=scores2.eval_id
                                LEFT JOIN rubric_scores ON rubric_scores.id=scores2.score_id
                                WHERE survey_id=? AND teammate_email=?');
  foreach (array_keys($teammates) as $email) {
    $stmt->bind_param('is',$survey_id, $email);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_array(MYSQLI_NUM)) {
      if (isset($row[2])) {
        if (!isset($ret_val[$email])) {
          $ret_val[$email] = array();
        }
        if (!isset($ret_val[$email][$row[0]])) {
          $ret_val[$email][$row[0]] = array();
        }
        if (isset($row[2])) {
          $ret_val[$email][$row[0]][$row[2]] = $row[3];
        }
      }
    }
  }
  $stmt->close();
  return $ret_val;
}
?>