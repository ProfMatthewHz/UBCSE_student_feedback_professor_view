<?php
function getEvalSources($db_connection, $survey_id, $id) {
  $ret_val = array();
  $stmt = $db_connection->prepare('SELECT evals.id, eval_weight
                                   FROM reviews
                                   INNER JOIN evals ON evals.id = reviews.eval_id AND evals.completed = 1
                                   WHERE reviews.survey_id =? AND reviews.reviewed_id=?');
  $stmt->bind_param('ii',$survey_id,$id);
  $stmt->execute();
  $result = $stmt->get_result();
  while ($row = $result->fetch_array(MYSQLI_NUM)) {
    $ret_val[] = array("id" => $row[0], "weight" => $row[1]);
  }
  $stmt->close();
  return $ret_val;
}

function getTeamEvaluationTargets($db_connection, $survey_id, $id) {
  $ret_val = array();
  $query_str = 'SELECT reviews.eval_id, teams.team_name 
                FROM reviews
                INNER JOIN collective_reviews ON reviews.eval_id = collective_reviews.eval_id
                INNER JOIN teams ON collective_reviews.reviewed_id = teams.id 
                WHERE reviews.survey_id =? AND reviews.reviewer_id=?';
  $stmt = $db_connection->prepare($query_str);
  $stmt->bind_param('ii',$survey_id, $id);
  $stmt->execute();
  $result = $stmt->get_result();
  while ($row = $result->fetch_array(MYSQLI_NUM)) {
    $ret_val[$row[0]] = $row[1];
  }
  $stmt->close();
  return $ret_val;
}

function getIndividualEvaluationTargets($db_connection, $survey_id, $id) {
  $ret_val = array();
  $query_str = 'SELECT reviews.eval_id, students.name 
                FROM reviews
                INNER JOIN students ON reviews.reviewed_id = students.id 
                WHERE reviews.survey_id =? AND reviews.reviewer_id=?';
  $stmt = $db_connection->prepare($query_str);
  $stmt->bind_param('ii',$survey_id, $id);
  $stmt->execute();
  $result = $stmt->get_result();
  while ($row = $result->fetch_array(MYSQLI_NUM)) {
    $ret_val[$row[0]] = $row[1];
  }
  $stmt->close();
  return $ret_val;
}

function markEvaluationCompleted($con, $eval_id) {
  $stmt = $con->prepare('UPDATE evals SET completed=1,last_update=current_timestamp WHERE id=?');
  $stmt->bind_param('i', $eval_id);
  $ret_val = $stmt->execute();
  $stmt->close();
  return $ret_val;
}
?>