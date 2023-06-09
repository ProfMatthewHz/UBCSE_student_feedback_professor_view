<?php
function getReviewPairingsData($con, $survey_id) {
  $stmt = $con->prepare('SELECT reviewer.email reviewer_email, reviewer.name reviewer_name, reviewed.email reviewed_email, reviewed.name reviewed_name
                         FROM reviews
                         INNER JOIN students reviewer ON reviews.reviewer_id=reviewer.id
                         INNER JOIN students reviewed ON reviews.reviewed_id=reviewed.id
                         WHERE survey_id=?
                         ORDER BY id');
  $stmt->bind_param('i', $survey_id);
  $stmt->execute();
  $result = $stmt->get_result();
  $retVal = $result->fetch_all(MYSQLI_ASSOC);
  $stmt->close();
  return $retVal;
}

function removeExistingPairings($con, $survey_id) {
  $stmt = $con->prepare('DELETE FROM reviews WHERE survey_id=?');
  $stmt->bind_param('i', $survey_id);
  $retVal = $stmt->execute();
  $stmt->close();
  return $retVal;
}

function getIdFromEmail($con, $email_addr) {
  // Create the SQL statement which we can use to verify the email address exists
  $stmt = $con->prepare('SELECT students.id FROM students WHERE students.email=?');
  $stmt->bind_param('s', $email_addr);
  $stmt->execute();
  $result = $stmt->get_result();
  $count = mysqli_num_rows($result);
  $stmt->close();
  // Return if that email address already exists
  return $count != 0;
}

function addPairings($con, $survey_id, $emails) {
  // Optimistically assume everything works
  $retVal = true;
  // prepare SQL statements
  $stmt_check = $con->prepare('SELECT id 
                               FROM reviews
                               WHERE survey_id=? AND reviewer_id=? AND reviewed_id=?');
  $stmt_add = $con->prepare('INSERT INTO reviews (survey_id, reviewer_id, reviewed_id, team_id, eval_weight) VALUES (?, ?, ?, ?, ?)');

  // loop over each pairing
  foreach ($emails as $pairing) {
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