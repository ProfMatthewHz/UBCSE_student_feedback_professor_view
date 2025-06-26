<?php
function getReviewsForSurvey($con, $survey_id) {
  // Get the pairings used in the original survey 
  $retVal = array();
  // prepare SQL statements
  $stmt = $con->prepare('SELECT reviewer_id, reviewed_id, team_id, eval_weight 
                         FROM reviews
                         WHERE survey_id=?');
  $stmt->bind_param('i', $survey_id);
  $stmt->execute();
  $result = $stmt->get_result();
  while ($row = $result->fetch_array(MYSQLI_NUM)) {
    $retVal[] = $row;
  }
  $stmt->close();
  return $retVal;
}

function addReviewsToSurvey($con, $survey_id, $pairings) {
  // Optimistically assume everything works
  $retVal = true;
  // prepare SQL statements
  $stmt_check = $con->prepare('SELECT id 
                               FROM reviews
                               WHERE survey_id=? AND reviewer_id=? AND reviewed_id=?');
  $stmt_add_review = $con->prepare('INSERT INTO reviews (survey_id, reviewer_id, reviewed_id, team_id, eval_weight) VALUES (?, ?, ?, ?, ?)');
  $stmt_add_evaluation = $con->prepare('INSERT INTO evals (review_id) VALUES (?)');
  // loop over each pairing
  foreach ($pairings as $pairing) {
    // check if the pairing already exists
    $stmt_check->bind_param('iii', $survey_id, $pairing[0], $pairing[1]);
    $stmt_check->execute();
    $result = $stmt_check->get_result();
    $data = $result->fetch_all(MYSQLI_ASSOC);

    // add the pairing if it does not exist
    if ($result->num_rows == 0) {
      $stmt_add_review->bind_param('iiiii', $survey_id, $pairing[0], $pairing[1], $pairing[2], $pairing[3]);
      $result = $stmt_add_review->execute();
      if ($result) {
        // Get the ID of the newly added review
        $review_id = $stmt_add_review->insert_id;
        // Add an evaluation for the review
        $stmt_add_evaluation->bind_param('i', $review_id);
        $result = $stmt_add_evaluation->execute();
      }
      $retVal = $retVal && $result;
    }
  }
  $stmt_add_review->close();
  $stmt_add_evaluation->close();
  $stmt_check->close();
  return $retVal;
}
?>