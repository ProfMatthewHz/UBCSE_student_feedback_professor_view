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

function addEvaluation($con) {
  // Add a new evaluation to the database
  $stmt = $con->prepare('INSERT INTO evals (id) VALUES (NULL)');
  $stmt->execute();
  // Get the ID of the newly added evaluation
  $retVal = $stmt->insert_id;
  // Clean up the statement
  $stmt->close();
  return $retVal;
}

function createOrRetrieveCollectiveEvaluation($con, $survey_id, $reviewing_id, $reviewed_id) {
  $retVal = 0;
  // prepare SQL statements
  $stmt_check = $con->prepare('SELECT eval_id 
                               FROM collective_reviews
                               WHERE survey_id=? AND reviewer_id=? AND reviewed_id=?');
  $stmt_add_coll_review = $con->prepare('INSERT INTO collective_reviews (survey_id, reviewer_id, reviewed_id, eval_id) VALUES (?, ?, ?, ?)');
  // Check to see if there is an existing evaluation for this pairing
  $stmt_check->bind_param('iii', $survey_id, $reviewing_id, $reviewed_id);
  $stmt_check->execute();
  $result = $stmt_check->get_result();
  $eval_info = $result->fetch_all(MYSQLI_ASSOC);
  if ($result->num_rows > 0) {
    $retVal = $eval_info[0]['eval_id'];
  } else {
    // Create the evaluation
    $retVal = addEvaluation($con); 
    // Add the collective review to the database
    $stmt_add_coll_review->bind_param('iiii', $survey_id, $reviewing_id, $reviewed_id, $retVal);
    $result = $stmt_add_coll_review->execute();
  }
  $stmt_check->close();
  $stmt_add_coll_review->close();
  return $retVal;
}

function ensureReviewExists($con, $survey_id, $eval_id, $team_id, $reviewer_id, $reviewed_id, $eval_weight) {
  $retVal = false;
  $stmt_check = $con->prepare('SELECT id 
                               FROM reviews
                               WHERE survey_id=? AND team_id=? AND reviewer_id=? AND reviewed_id=?');
  $stmt_add = $con->prepare('INSERT INTO reviews (survey_id, reviewer_id, reviewed_id, team_id, eval_weight, eval_id) VALUES (?, ?, ?, ?, ?, ?)');
  $stmt_check->bind_param('iiii', $survey_id, $team_id, $reviewer_id, $reviewed_id);
  $stmt_check->execute();
  $result = $stmt_check->get_result();
  $review_info = $result->fetch_all(MYSQLI_ASSOC);

  // Check that there is not already a review with this combination pairing does not already exist
  if ($result->num_rows == 0) {
    $stmt_add->bind_param('iiiiii', $survey_id, $reviewer_id, $reviewed_id, $team_id, $eval_weight, $eval_id);
    $stmt_add->execute();
    $retVal = $stmt_add->insert_id;
  } else {
    $retVal = $review_info[0]['id']; // The review already exists, so we don't need to do anything
  }
  $stmt_check->close();
  $stmt_add->close();
  return $retVal;
}


function addCollectiveReviewsToSurvey($con, $survey_id, $teams, $pairings) {
  // Optimistically assume everything works
  $retVal =true;
  
  // loop over each pairing
  foreach ($pairings as $pairing) {
    $reviewing_team = $teams[$pairing['reviewing']];
    $reviewing_team_id = $reviewing_team['id'];
    $reviewed_team = $teams[$pairing['reviewed']];
    // Assume that we need to create a new evaluation
    $eval_id = createOrRetrieveCollectiveEvaluation($con, $survey_id, $reviewing_team_id, $reviewed_team['id']);
    if ($eval_id == 0) {
      return false; // If we could not create or retrieve an evaluation, a bug has occured
    }
    // Now create all of the reviews to make this work
    foreach ($reviewing_team['roster'] as $reviewer) {
      // Get the reviewers ID
      $reviewer_id = $reviewer['id'];
      // Loop through each member of the reviewed team
      foreach ($reviewed_team['roster'] as $reviewed) {
        // Get the reviewed ID
        $reviewed_id = $reviewed['id'];
        // Make the review as needed
        $result = ensureReviewExists($con, $survey_id, $eval_id, $reviewing_team['id'], $reviewer_id, $reviewed_id, 1);
        $retVal = $retVal && ($result != 0);
      }
    }
  }
  return $retVal;
}



function addReviewsToSurvey($con, $survey_id, $pairings) {
  // Optimistically assume everything works
  $retVal = true;
  // prepare SQL statements
  $stmt_check = $con->prepare('SELECT id 
                               FROM reviews
                               WHERE survey_id=? AND team_id=? AND reviewer_id=? AND reviewed_id=?');
  $stmt_add_review = $con->prepare('INSERT INTO reviews (survey_id, reviewer_id, reviewed_id, team_id, eval_weight, eval_id) VALUES (?, ?, ?, ?, ?, ?)');
  // loop over each pairing
  foreach ($pairings as $pairing) {
    // check if the pairing already exists
    $stmt_check->bind_param('iiii', $survey_id, $pairing[2], $pairing[0], $pairing[1]);
    $stmt_check->execute();
    $result = $stmt_check->get_result();
    $result->fetch_all(MYSQLI_ASSOC);

    // add the pairing if it does not exist
    if ($result->num_rows == 0) {
      $eval_id = addEvaluation($con);
      if ($eval_id != 0) {
        // Get the ID of the newly added review
        $stmt_add_review->bind_param('iiiiii', $survey_id, $pairing[0], $pairing[1], $pairing[2], $pairing[3], $eval_id);
        $result = $stmt_add_review->execute();
      }
      $retVal = $retVal && $result;
    }
  }
  $stmt_add_review->close();
  $stmt_check->close();
  return $retVal;
}
?>