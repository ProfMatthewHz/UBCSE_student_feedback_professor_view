<?php
// This function is not used currently, but being kept for future use as it should be a huge improvement
function getTeamForEvals($con, $survey_id) {
  // Get the averages for each student in the course
  $retVal = array();
  // prepare SQL statements
  $stmt = $con->prepare('SELECT evals.id, team_name
                         FROM reviews
                         INNER JOIN evals ON evals.id = reviews.eval_id
                         LEFT JOIN teams ON teams.id = reviews.team_id
                         WHERE reviews.survey_id=?');
  $stmt->bind_param('i', $survey_id);
  $stmt->execute();
  $result = $stmt->get_result();
  while ($row = $result->fetch_array(MYSQLI_NUM)) {
    $eval_id = $row[0];
    $team_name = $row[1];
    $retVal[$eval_id] = $team_name;
  }
  $stmt->close();
  return $retVal;
}
function getTeamPairings($con, $survey_id) {
  $retVal = array();
  // Get the team pairings for the survey
  $stmt = $con->prepare('SELECT team1.team_name reviewing, team2.team_name reviewed 
                         FROM collective_reviews
                         INNER JOIN teams AS team1 ON collective_reviews.reviewer_id = team1.id AND collective_reviews.survey_id=team1.survey_id
                         INNER JOIN teams AS team2 ON collective_reviews.reviewed_id = team2.id AND collective_reviews.survey_id=team2.survey_id
                         WHERE collective_reviews.survey_id = ?');
  $stmt->bind_param('i', $survey_id);
  $stmt->execute();
  $result = $stmt->get_result();
  $rows = $result->fetch_all(MYSQLI_ASSOC);
  $stmt->close();
  return $rows;
}

function getIdsForRoster($con, $line_num, $roster) {
  // Otherwise we have a valid team and can try getting the ids
  $retVal = array('error' => array(), 'roster' => array());
  // Make sure the current line's data are valid
  foreach ($roster as $column => $member) {
    $id_and_name = getStudentInfoFromEmail($con, $member['email']);
    if (empty($id_and_name)) {
      $retVal['error'][] = 'Line '. $line_num . '  column ' . $column . ' includes an unknown email (' . $member['email'] . ')<br>';
    } else {
      $id = $id_and_name[0];
      $retVal['roster'][] = array('email' => $member['email'], 'role' => $member['role'], 'id' => $id);
    }
  }
  return $retVal;
}

function getTeamMemberIds($con, $team_id) {
  $retVal = array();
  // Get the ids of the members of the team
  $stmt = $con->prepare('SELECT student_id FROM team_members WHERE team_id = ?');
  $stmt->bind_param('i', $team_id);
  $stmt->execute();
  $result = $stmt->get_result();
  $rows = $result->fetch_all(MYSQLI_ASSOC);
  foreach ($rows as $row) {
    $retVal[] = $row['student_id'];
  }
  // Clean up after our query
  $stmt->close();
  return $retVal;
}

function getSurveyStudents($con, $survey_id, $roster) {
  $retVal = array();
  // Add all the students in the course roster to the return value
  foreach ($roster as $email => $student_info) {
    $name = $student_info['name'];
    $retVal[$email] = array(
      'name' => $name,
      'email' => $email,
      'rostered' => true
    );
  }
  // Now we need to get all the students who are in the survey, but may not be in the roster
  $stmt = $con->prepare('SELECT DISTINCT students.email, students.name
                         FROM teams
                         INNER JOIN team_members ON teams.id = team_members.team_id
                         INNER JOIN students ON team_members.student_id = students.id
                         WHERE survey_id = ?');
  $stmt->bind_param('i', $survey_id);
  $stmt->execute();
  $result = $stmt->get_result();
  $rows = $result->fetch_all(MYSQLI_ASSOC);
  foreach ($rows as $row) {
    $email = $row['email'];
    $name = $row['name'];
    if (!array_key_exists($email, $retVal)) {
      // If the student is not in the roster, add them to the return value
      $retVal[$email] = array(
        'name' => $name,
        'email' => $email,
        'rostered' => false
      );
    }
  }
  // Clean up after our query
  $stmt->close();
  return $retVal;
}

function getSurveyTeams($con, $survey_id) {
  $retVal = array();
  $stmt_team = $con->prepare('SELECT id, team_name FROM teams WHERE survey_id = ?');
  $stmt_team->bind_param('i', $survey_id);
  $stmt_team->execute();
  $result = $stmt_team->get_result();
  $rows = $result->fetch_all(MYSQLI_ASSOC);
  foreach ($rows as $row) {
    $retVal[$row['team_name']] = array('id' => $row['id'], 'name' => $row['team_name'], 'roster' => array());
  }
  $stmt_team->close();
  // Get the members of each team
  $stmt_members = $con->prepare('SELECT email, role
                                 FROM team_members
                                 INNER JOIN students ON team_members.student_id = students.id
                                 WHERE team_id = ?');
  foreach ($retVal as $team_name => $team_data) {
    $team_id = $team_data['id'];
    $stmt_members->bind_param('i', $team_id);
    $stmt_members->execute();
    $result_members = $stmt_members->get_result();
    $members = $result_members->fetch_all(MYSQLI_ASSOC);
    // Add the members to the team
    foreach ($members as $member) {
      $retVal[$team_name]['roster'][] = array(
        'email' => $member['email'],
        'role' => $member['role']
      );
    }
  }
  // Clean up after our query
  $stmt_members->close();
  return $retVal;
}

function getSurveyTeamInfo($con, $survey_id) {
  $retVal = array();
  $stmt_team = $con->prepare('SELECT id, team_name FROM teams WHERE survey_id = ?');
  $stmt_team->bind_param('i', $survey_id);
  $stmt_team->execute();
  $result = $stmt_team->get_result();
  $rows = $result->fetch_all(MYSQLI_ASSOC);
  foreach ($rows as $row) {
    $team_id = $row['id'];
    $team_name = $row['team_name'];
    $retVal[$team_id] = $team_name;
  }
  // Clean up after out query
  $stmt_team->close();
  return $retVal;
}

function checkArrayForKey($array, string $key, $value) {
  foreach ($array as $item) {
    if ($item[$key] == $value) {
      return true;
    }
  }
  return false;
}

function checkForTeam($array, $team_id, $team_name) {
  $retval = array('id' => false, 'name' => false);
  foreach ($array as $name => $team) {
    if ($team['id'] == $team_id) {
      if ($name == $team_name) {
        return array('id' => true, 'name' => true);
      } else {
        return array('id' => true, 'name' => false);
      }
    }
  }
  return array('id' => false, 'name' => false);
}

function deleteTeamAndEvals($stmt_del_team, $stmt_del_evals, $survey_id, $team_id) {
  // Optimistically assume everything works
  $retVal = true;
  // Delete the team (which automatically deletes membership records)
  $stmt_del_team->bind_param('ii', $survey_id, $team_id);
  if (!$stmt_del_team->execute()) {
    $retVal = false;
  }
  // Delete the evals for the team (which automatically deletes the reviews & scores)
  $stmt_del_evals->bind_param('ii', $survey_id, $team_id);
  if (!$stmt_del_evals->execute()) {
    $retVal = false;
  }
  return $retVal;
}

function deleteMembershipAndEvals($stmt_del_member, $stmt_del_responses, $survey_id, $team_id, $student_id) {
  // Optimistically assume everything works
  $retVal = true;
  // Delete the member from the team
  $stmt_del_member->bind_param('ii', $team_id, $student_id);
  if (!$stmt_del_member->execute()) {
    $retVal = false;
  }
  // Delete the responses that are specifically about that student
  $stmt_del_responses->bind_param('iis', $survey_id, $team_id, $student_id);
  if (!$stmt_del_responses->execute()) {
    $retVal = false;
  }
  return $retVal;
}

function updateTeamName($stmt_update_team, $team_id, $team_name) {
  // Optimistically assume everything works
  $retVal = true;
  // Update the team name
  $stmt_update_team->bind_param('si', $team_name, $team_id);
  if (!$stmt_update_team->execute()) {
    $retVal = false;
  }
  return $retVal;
}

function updateTeamMembersAndPruneReviews($con, $survey_id, $teams, $prune_evals) {
  // Optimistically assume everything works
  $retVal = true;
  $stmt_del_member = $con->prepare('DELETE FROM team_members WHERE team_id = ? AND student_id = ?');
  $stmt_del_responses = null;
  // Prepare the delete statement which will delete either reviews or evals and reviews based on need
  if ($prune_evals) {
    // If we are pruning individual reviews, we need to delete the evals from this team
    //  that include this student as either a reviewer or reviewed. This cannot be done
    //  for collective reviews, however.
    $stmt_del_responses = $con->prepare('DELETE evals FROM evals INNER JOIN reviews ON reviews.eval_id=evals.id WHERE survey_id = ? AND team_id = ? AND (? in (reviewer_id, reviewed_id))');
  } else {
    // If we are not pruning individual evaluations, we just delete the reviews but keep that evals could still be possible
    $stmt_del_responses = $con->prepare('DELETE FROM reviews WHERE survey_id = ? AND team_id = ? AND (? in (reviewer_id, reviewed_id))');
  }
  // Loop over each team and delete it from the database
  foreach ($teams as $team) {
    $team_id = $team['id'];
    $roster = $team['roster'];
    $existing_ids = getTeamMemberIds($con, $team_id);
    insertMissingMembersToTeam($con, $team_id, $roster, $existing_ids);
    // Check if the team is in the remaining teams
    foreach ($existing_ids as $student_id) {
      $found = checkArrayForKey($roster, 'id', $student_id);
 
      // If the member was not found, prune the member and their evaluations/reviews as appropriate
      if (!$found) {
        if (!deleteMembershipAndEvals($stmt_del_member, $stmt_del_responses, $survey_id, $team_id, $student_id)) {
          $retVal = false;
        }
      }
    }
  }
  // Clean up our database work
  $stmt_del_member->close();
  $stmt_del_responses->close();
  // Return if we were successful
  return $retVal;
}

function updateTeamsAndPruneReviews($con, $survey_id, &$teams) {
  // Optimistically assume everything works
  $retVal = true;

  // This call will make certain that all of the teams we have listed have been added to the database
  insertTeams($con, $survey_id, $teams);

  $existing_teams = getSurveyTeamInfo($con, $survey_id);
  $stmt_del_team = $con->prepare('DELETE FROM teams WHERE survey_id = ? AND id = ?');
  $stmt_update_team = $con->prepare('UPDATE teams SET team_name = ? WHERE id = ?');
  $stmt_del_evals = $con->prepare('DELETE evals FROM evals INNER JOIN reviews ON reviews.eval_id=evals.id WHERE survey_id = ? AND team_id = ?');
  
  // Loop over each team and delete it from the database
  foreach ($existing_teams as $team_id => $team_name) {
    $teamExists = checkForTeam($teams, $team_id, $team_name);

    // If the team was not found, we need to prune the team, its membership, and any survey responses
    if (!$teamExists['id']) {
      if (!deleteTeamAndEvals($stmt_del_team, $stmt_del_evals, $survey_id, $team_id)) {
        $retVal = false;
      }
    } else if (!$teamExists['name']) {
      if (!updateTeamName($stmt_update_team, $team_id, $team_name)) {
        $retVal = false;
      }
    }
  }
  // Clean up our database work
  $stmt_del_team->close();
  $stmt_update_team->close();
  $stmt_del_evals->close();
  // Return if we were successful
  return $retVal;
}

function insertTeams($con, $survey_id, &$teams) {
  // Optimistically assume everything works
  $retVal = true;
  $stmt_add = $con->prepare('INSERT INTO teams (survey_id, team_name) VALUES (?, ?)');

  // loop over each team and add any missing teams to the database
  foreach ($teams as $name => &$team) {
    // Only add the team if it is not already in the database (i.e., does not have an id key)
    if (!array_key_exists('id', $team)) {
      $stmt_add->bind_param('is', $survey_id, $name);
      if ($stmt_add->execute()) {
        $team['id'] = $con->insert_id;
      } else {
        $retVal = false;
      }
    }
  }
  // Release the reference to the array element (needed since it is being updated)
  unset($team);
  // Clean up our database work
  $stmt_add->close();
  // Return if we were successful
  return $retVal;
}

function insertMissingMembersToTeam($con, $team_id, $roster, $existing_ids) {
  // Optimistically assume everything works
  $retVal = true;
  foreach ($roster as $member) {
    // If the member is not already a member of this team, we need to insert them into the list
    if (!in_array($member['id'], $existing_ids)) {
      $stmt_add_member = $con->prepare('INSERT INTO team_members (team_id, student_id, role) VALUES (?, ?, ?)');
      $stmt_add_member->bind_param('iis', $team_id, $member['id'], $member['role']);

      if (!$stmt_add_member->execute()) {
        $retVal = false;
      }
      $stmt_add_member->close();
    }
  }
  return $retVal;
}

function insertTeamMembers($con, $team_id, $roster) {
  // Optimistically assume everything works
  $retVal = true;
  $stmt_add = $con->prepare('INSERT INTO team_members (team_id, student_id, role) VALUES (?, ?, ?)');
  // Loop over each member in the team and add them to the database
  foreach ($roster as $person) {
    $stmt_add->bind_param('iis', $team_id, $person['id'], $person['role']);
    if (!$stmt_add->execute()) {
      $retVal = false;
    }
  }
  // Clean up our database work
  $stmt_add->close();
  // Return if we were successful
  return $retVal;
}

function insertMembers($con, $teams) {
  // Optimistically assume everything works
  $retVal = false;
  foreach ($teams as $team) {
    $result = insertTeamMembers($con, $team['id'], $team['roster']);
    if ($result) {
      $retVal = true;
    }
  }
  // Return if we were successful
  return $retVal;
}

function getIdsForAllRosters($con, $teams) {
  $ret_val = array('error' => array(), 'teams' => array());
  // Loop through each team in the teams array
  foreach ($teams as $name => $team_data) {
    // Verify the entries on the current line
    $line_data = getIdsForRoster($con, $name, $team_data['roster']);
    if (!empty($line_data['error'])) {
      $ret_val['error'] = array_merge($ret_val['error'], $line_data['error']);
    } else {
      $ret_val['teams'][$name] = array('roster' => $line_data['roster']);
      // Add the row to our list of (valid) rows
      if (array_key_exists('id', $team_data)) {
        $ret_val['teams'][$name]['id'] = $team_data['id'];
      }
    }
  }
  return $ret_val;
}
?>