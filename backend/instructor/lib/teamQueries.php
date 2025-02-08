<?php


function addTeamsToSurvey($con, $survey_id, &$teams) {
  // Optimistically assume everything works
  $retVal = true;
  $stmt_add = $con->prepare('INSERT INTO teams (survey_id, team_name) VALUES (?, ?)');

  // loop over each team and add it to the database
  foreach ($teams as &$team) {
    $stmt_add->bind_param('is', $survey_id, $team['name']);
    if ($stmt_add->execute()) {
      $team['id'] = $con->insert_id;
    } else {
      $retVal = false;
    }
  }
  // Release the reference to the array element (needed since it is being updated)
  unset($team);
  // Clean up our database work
  $stmt_add->close();
  // Return if we were successful
  return $retVal;
}
?>