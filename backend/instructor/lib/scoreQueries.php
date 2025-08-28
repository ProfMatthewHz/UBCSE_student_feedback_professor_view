<?php
// This function is not used currently, but being kept for future use as it should be a huge improvement
function getEvalCriterionScores($con, $survey_id) {
  // Get the averages for each student in the course
  $retVal = array();
  // prepare SQL statements
  $stmt = $con->prepare('SELECT evals.id, topic_id, score
                         FROM evals
                         INNER JOIN scores ON scores.eval_id = evals.id
                         INNER JOIN rubric_scores ON rubric_scores.id = scores.rubric_score_id
                         WHERE evals.id in (SELECT eval_id FROM reviews WHERE reviews.survey_id=?)');
  $stmt->bind_param('i', $survey_id);
  $stmt->execute();
  $result = $stmt->get_result();
  while ($row = $result->fetch_array(MYSQLI_NUM)) {
    $eval_id = $row[0];
    $topic_id = $row[1];
    $score = $row[2];
    // Make certain we have an array in our results for this student
    if (!array_key_exists($eval_id, $retVal)) {
      $retVal[$eval_id] = array($topic_id => $score);
    } else {
      $retVal[$eval_id][$topic_id] = $score;
    }
  }
  $stmt->close();
  return $retVal;
}

function getCompletionResults($con, $survey_id) {
  $ret_val = array(array("Name", "Email", "Completed"));
  // This survey should roughly parallel the completion results in getReviewerPerTeamResults
  $stmt = $con->prepare('SELECT students.name, students.email, MIN(completed)
                         FROM reviews
                         LEFT JOIN evals ON evals.id=reviews.eval_id
                         LEFT JOIN students ON students.id=reviews.reviewer_id
                         WHERE survey_id=?
                         GROUP BY students.name, students.email');
  $stmt->bind_param('i', $survey_id);
  $stmt->execute();
  $result = $stmt->get_result();
  while ($row = $result->fetch_array(MYSQLI_NUM)) {
    $row[2] = ($row[2] === 1) ? "Completed" : "Not completed";
    $ret_val[] = $row;
  }
  $stmt->close();
  return $ret_val;
}

function getIndividualReviewersTotalPoints($con, $survey_id) {
  $ret_val = array();
  // This survey should roughly parallel the completion results in getReviewerPerTeamResults
  $stmt = $con->prepare('SELECT reviews.eval_id, total_reviewer_points / total_reviews review_factor
                         FROM reviews
                         INNER JOIN (SELECT survey_id, reviewer_id, team_id, SUM(score * evals.weight) total_reviewer_points, COUNT(DISTINCT reviews.eval_id) total_reviews
                                     FROM reviews
                                     INNER JOIN evals ON evals.id = reviews.eval_id
                                     INNER JOIN scores ON scores.eval_id = evals.id
                                     INNER JOIN rubric_scores ON rubric_scores.id = scores.rubric_score_id
                                     GROUP BY survey_id, reviewer_id, team_id) AS totals ON totals.survey_id=reviews.survey_id AND totals.reviewer_id = reviews.reviewer_id AND totals.team_id = reviews.team_id
                         WHERE totals.survey_id=?;');
  $stmt->bind_param('i', $survey_id);
  $stmt->execute();
  $result = $stmt->get_result();
  while ($row = $result->fetch_array(MYSQLI_NUM)) {
    $eval_id = $row[0];
    $review_factor = $row[1];
    $ret_val[$eval_id] = $review_factor;
  }
  $stmt->close();
  return $ret_val;
}

function getTeamReviewersTotalPoints($con, $survey_id) {
  $ret_val = array();
  // This survey should roughly parallel the completion results in getReviewerPerTeamResults
  $stmt = $con->prepare('SELECT collective_reviews.eval_id, total_reviewer_points / total_reviews review_factor
                         FROM collective_reviews
                         INNER JOIN (SELECT survey_id, reviewer_id, SUM(score * evals.weight) total_reviewer_points, COUNT(DISTINCT evals.id) total_reviews
                                     FROM collective_reviews
                                     INNER JOIN evals ON evals.id = collective_reviews.eval_id
                                     INNER JOIN scores ON scores.eval_id = evals.id
                                     INNER JOIN rubric_scores ON rubric_scores.id = scores.rubric_score_id
                                     GROUP BY survey_id, reviewer_id) AS totals ON totals.survey_id=collective_reviews.survey_id AND totals.reviewer_id = collective_reviews.reviewer_id
                         WHERE totals.survey_id=?;');
  $stmt->bind_param('i', $survey_id);
  $stmt->execute();
  $result = $stmt->get_result();
  while ($row = $result->fetch_array(MYSQLI_NUM)) {
    $eval_id = $row[0];
    $review_factor = $row[1];
    $ret_val[$eval_id] = $review_factor;
  }
  $stmt->close();
  return $ret_val;
}

function getReviewersTotalPoints($con, $survey_id, $use_team_reviews) {
  if ($use_team_reviews) {
    return getTeamReviewersTotalPoints($con, $survey_id);
  } else {
    return getIndividualReviewersTotalPoints($con, $survey_id);
  }
}

function getEvalsTotalPoints($con, $survey_id) {
  $ret_val = array();
  // This survey should roughly parallel the completion results in getReviewerPerTeamResults
  $stmt = $con->prepare('SELECT evals.id, SUM(score * evals.weight) total_points
                         FROM evals
                         INNER JOIN scores ON scores.eval_id = evals.id
                         INNER JOIN rubric_scores ON rubric_scores.id = scores.rubric_score_id
                         WHERE evals.id in (SELECT eval_id FROM reviews WHERE reviews.survey_id=?)
                         GROUP BY evals.id;');
  $stmt->bind_param('i', $survey_id);
  $stmt->execute();
  $result = $stmt->get_result();
  while ($row = $result->fetch_array(MYSQLI_NUM)) {
    $eval_id = $row[0];
    $total = $row[1];
    $ret_val[$eval_id] = $total;
  }
  $stmt->close();
  return $ret_val;
}
?>