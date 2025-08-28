<?php
function getValidEvalsOfStudentByTeam($con, $survey_id) {
  // We calculate to related sets of results: 
  // One array maps student id to array of all eval ids to include in the normalized average calc
  $valid_evals = array();
  // The other maps an eval id to if it should be included in the normalized average
  $eval_normalized = array();
  // Select all of the evaluations  -- this is slower on postel? dropping the DISTINCT and eliminate the weight <> 0 does not help
  $stmt = $con->prepare('SELECT DISTINCT reviews.reviewed_id, evals.eval_id, evals.weight
                         FROM reviews
                         INNER JOIN evals ON reviews.eval_id = evals.id
                         LEFT JOIN (SELECT survey_id, reviewer_id, team_id
                                    FROM reviews
                                    INNER JOIN evals ON reviews.eval_id = evals.id
                                    WHERE survey_id=? AND completed = 0) validate ON survey_id=validate.survey_id AND reviewer_id=validate.reviewer_id AND team_id=validate.team_id
                         WHERE reviews.survey_id=? AND validate.reviewer_id is null AND evals.weight <> 0');
  $stmt->bind_param('ii', $survey_id, $survey_id);
  $stmt->execute();
  $result = $stmt->get_result();
  while ($row = $result->fetch_array(MYSQLI_NUM)) {
    $student_id = $row[0];
    $eval_id = $row[1];
    $eval_weight = $row[2];
    // Make certain we have an array in our results for this student
    if (!array_key_exists($student_id, $valid_evals)) {
      $valid_evals[$student_id] = array($eval_id => $eval_weight);
    } else {
      $valid_evals[$student_id][$eval_id] = $eval_weight;
    }
    // Now add that this evaluation will be inclued in the normalized average calculations
    $eval_normalized[$eval_id] = true;
  }
  $stmt->close();
  $retVal = array('valid_evals' => $valid_evals, 'eval_normalized' => $eval_normalized);
  return $retVal;
}

// This function is not used currently, but being kept for future use as it should be a huge improvement
function getEvalStudentInformation($con, $survey_id) {
  // Get the averages for each student in the course
  $retVal = array();
  // prepare SQL statements
  $stmt = $con->prepare('SELECT reviews.eval_id, reviewer.name, reviewer.email, reviewed.name, reviewed.email
                         FROM reviews
                         INNER JOIN students reviewer ON reviews.reviewer_id = reviewer.id
                         INNER JOIN students reviewed ON reviews.reviewed_id = reviewed.id
                         WHERE reviews.survey_id=?;');
  $stmt->bind_param('i', $survey_id);
  $stmt->execute();
  $result = $stmt->get_result();
  while ($row = $result->fetch_array(MYSQLI_NUM)) {
    $eval_id = $row[0];
    $retVal[$eval_id] = array($row[1].' ('.$row[2].')', $row[3].' ('.$row[4].')');
  }
  $stmt->close();
  return $retVal;
}

// This function is not used currently, but being kept for future use as it should be a huge improvement
function getEvalTeamInformation($con, $survey_id) {
  // Get the averages for each student in the course
  $retVal = array();
  // prepare SQL statements
  $stmt = $con->prepare('SELECT collective_reviews.eval_id, reviewer.team_name, reviewed.team_name
                         FROM collective_reviews
                         INNER JOIN teams reviewer ON collective_reviews.reviewer_id = reviewer.id
                         INNER JOIN teams reviewed ON collective_reviews.reviewed_id = reviewed.id
                         WHERE collective_reviews.survey_id=?;');
  $stmt->bind_param('i', $survey_id);
  $stmt->execute();
  $result = $stmt->get_result();
  while ($row = $result->fetch_array(MYSQLI_NUM)) {
    $eval_id = $row[0];
    $retVal[$eval_id] = array($row[1], $row[2]);
  }
  $stmt->close();
  return $retVal;
}

// This function is not used currently, but being kept for future use as it should be a huge improvement
function getEvalInformation($con, $survey_id, $use_team_evals) {
  if ($use_team_evals) {
    return getEvalTeamInformation($con, $survey_id);
  } else {
    return getEvalStudentInformation($con, $survey_id);
  }
}

function addEvaluation($con, $eval_weight) {
  // Add a new evaluation to the database
  $stmt = $con->prepare('INSERT INTO evals (weight) VALUES (?)');
  $stmt->bind_param('i', $eval_weight);
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
    $retVal = addEvaluation($con, 1); 
    // Add the collective review to the database
    $stmt_add_coll_review->bind_param('iiii', $survey_id, $reviewing_id, $reviewed_id, $retVal);
    $result = $stmt_add_coll_review->execute();
  }
  $stmt_check->close();
  $stmt_add_coll_review->close();
  return $retVal;
}

function ensureReviewExists($con, $survey_id, $eval_id, $team_id, $reviewer_id, $reviewed_id) {
  $retVal = false;
  $stmt_check = $con->prepare('SELECT id 
                               FROM reviews
                               WHERE survey_id=? AND team_id=? AND reviewer_id=? AND reviewed_id=?');
  $stmt_add = $con->prepare('INSERT INTO reviews (survey_id, reviewer_id, reviewed_id, team_id, eval_id) VALUES (?, ?, ?, ?, ?)');
  $stmt_check->bind_param('iiii', $survey_id, $team_id, $reviewer_id, $reviewed_id);
  $stmt_check->execute();
  $result = $stmt_check->get_result();
  $review_info = $result->fetch_all(MYSQLI_ASSOC);

  // Check that there is not already a review with this combination pairing does not already exist
  if ($result->num_rows == 0) {
    $stmt_add->bind_param('iiiii', $survey_id, $reviewer_id, $reviewed_id, $team_id, $eval_id);
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
        $result = ensureReviewExists($con, $survey_id, $eval_id, $reviewing_team['id'], $reviewer_id, $reviewed_id);
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
  $stmt_add_review = $con->prepare('INSERT INTO reviews (survey_id, reviewer_id, reviewed_id, team_id, eval_id) VALUES (?, ?, ?, ?, ?)');
  // loop over each pairing
  foreach ($pairings as $pairing) {
    // check if the pairing already exists
    $stmt_check->bind_param('iiii', $survey_id, $pairing[2], $pairing[0], $pairing[1]);
    $stmt_check->execute();
    $result = $stmt_check->get_result();
    $result->fetch_all(MYSQLI_ASSOC);

    // add the pairing if it does not exist
    if ($result->num_rows == 0) {
      $eval_id = addEvaluation($con, $pairing[3]);
      if ($eval_id != 0) {
        // Get the ID of the newly added review
        $stmt_add_review->bind_param('iiiii', $survey_id, $pairing[0], $pairing[1], $pairing[2], $eval_id);
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