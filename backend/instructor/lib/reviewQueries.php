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
  $stmt_add = $con->prepare('INSERT INTO reviews (survey_id, reviewer_id, reviewed_id, team_id, eval_weight) VALUES (?, ?, ?, ?, ?)');

  // loop over each pairing
  foreach ($pairings as $pairing) {
    // check if the pairing already exists
    $stmt_check->bind_param('iii', $survey_id, $pairing[0], $pairing[1]);
    $stmt_check->execute();
    $result = $stmt_check->get_result();
    $data = $result->fetch_all(MYSQLI_ASSOC);

    // add the pairing if it does not exist
    if ($result->num_rows == 0) {
      $stmt_add->bind_param('iiiii', $survey_id, $pairing[0], $pairing[1], $pairing[2], $pairing[3]);
      $retVal = $retVal && $stmt_add->execute();
    }
  }
  $stmt_add->close();
  $stmt_check->close();
  return $retVal;
}
?>