<?php
function getReviewSources($db_connection, $survey_id, $id) {
  $ret_val = array();
  $query_str = 'SELECT reviews_id
                FROM reviews
                INNER JOIN evals ON evals.reviews_id = reviews.id
                WHERE reviews.survey_id =? AND reviews.reviewed_id=? AND reviews.reviewed_id<>reviews.reviewer_id';
  $stmt = $db_connection->prepare($query_str);
  $stmt->bind_param('ii',$survey_id,$id);
  $stmt->execute();
  $result = $stmt->get_result();
  while ($row = $result->fetch_array(MYSQLI_NUM)) {
    $ret_val[] = $row[0];
  }
  $stmt->close();
  return $ret_val;
}

function getReviewTargets($db_connection, $survey_id, $id) {
  $ret_val = array();
  $query_str = 'SELECT reviews.id, students.name 
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

function addNewEvaluation($con, $reviews_id) {
  $stmt = $con->prepare('INSERT INTO evals (reviews_id) VALUES(?)');
  $stmt->bind_param('i', $reviews_id);
  $stmt->execute();
  $retVal = $stmt->insert_id;
  $stmt->close();
  return $retVal;
}

function getEvalForReview($con, $reviews_id) {
  $retVal = 0;
  $stmt = $con->prepare('SELECT id FROM evals WHERE review_id=?');
  $stmt->bind_param('i', $reviews_id);
  $stmt->execute();
  $stmt->bind_result($retVal);
  $stmt->store_result();
  if (!$stmt->fetch()) {
    $retVal = 0;
  }
  $stmt->close();
  return $retVal;
}
?>