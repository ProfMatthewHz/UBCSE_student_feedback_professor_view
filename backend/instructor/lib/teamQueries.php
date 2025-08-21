<?php
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
  return $rows;
}

function getIdsForRoster($con, $line_num, $roster) {
  // Otherwise we have a valid team and can try getting the ids
  $retVal = array('error' => '', 'roster' => array());
  // Make sure the current line's data are valid
  foreach ($roster as $column => $member) {
    $id_and_name = getStudentInfoFromEmail($con, $member['email']);
    if (empty($id_and_name)) {
      $retVal['error'] = $retVal['error'] . 'Line '. $line_num . '  column ' . $column . ' includes an unknown email (' . $member['email'] . ')<br>';
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
  $stmt_team = $con->prepare('SELECT id, team_name
                              FROM teams
                              WHERE survey_id = ?');
  $stmt_team->bind_param('i', $survey_id);
  $stmt_team->execute();
  $result = $stmt_team->get_result();
  $rows = $result->fetch_all(MYSQLI_ASSOC);
  foreach ($rows as $row) {
    $retVal[$row['team_name']] = array('id' => $row['id'], 'roster' => array());
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

function getSurveyTeamIds($con, $survey_id) {
  $retVal = array();
  $stmt_team = $con->prepare('SELECT id
                              FROM teams
                              WHERE survey_id = ?');
  $stmt_team->bind_param('i', $survey_id);
  $stmt_team->execute();
  $result = $stmt_team->get_result();
  $rows = $result->fetch_all(MYSQLI_ASSOC);
  foreach ($rows as $row) {
    $team_id = $row['id'];
    $retVal[] = $team_id;
  }
  // Clean up after out query
  $stmt_team->close();
  return $retVal;
}

function checkArrayForKey($array, $key, $value) {
  foreach ($array as $item) {
    if ($item[$key] == $value) {
      return true;
    }
  }
  return false;
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
    // If we are not pruning individual reviews, we just delete the reviews
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
 
      // If the member was not found, they have been pruned and so we need to delete
      //  * the member but only for this specific team
      //  * the evals including that member associated with this team (which automatically results in the reviews & scores being deleted)
      if (!$found) {
        $stmt_del_member->bind_param('ii', $team_id, $student_id);
        if (!$stmt_del_member->execute()) {
          $retVal = false;
        }
        // If we are pruning individual reviews, we need to delete the evals from this team
        //  that include this student as either a reviewer or reviewed. This cannot be done
        //  for collective reviews, however, and they will only prune reviews.
        $stmt_del_responses->bind_param('iii', $survey_id, $team_id, $student_id);
        if (!$stmt_del_responses->execute()) {
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

  // TODO: Insert any new teams (e.g., entries in teams not containing "id" as a key) into the database
  // TODO: This will require modifying the teams array to include the new team ids

  $existing_ids = getSurveyTeamIds($con, $survey_id);
  $stmt_del_team = $con->prepare('DELETE FROM teams WHERE survey_id = ? AND id = ?');
  $stmt_del_evals = $con->prepare('DELETE evals FROM evals INNER JOIN reviews ON reviews.eval_id=evals.id WHERE survey_id = ? AND team_id = ?');
  // Loop over each team and delete it from the database
  foreach ($existing_ids as $team_id) {
    $found = checkArrayForKey($teams, 'id', $team_id);

    // If the team was not found, the team has been pruned and so we need to delete
    //  * the team (which automatically results in the team members being deleted)
    //  * the evals for the team (which automatically results in the reviews & scores being deleted)
    if (!$found) {
      $stmt_del_team->bind_param('ii', $survey_id, $team_id);
      if (!$stmt_del_team->execute()) {
        $retVal = false;
      }
      $stmt_del_evals->bind_param('ii', $survey_id, $team_id);
      if (!$stmt_del_evals->execute()) {
        $retVal = false;
     }
    }
  }
  // Clean up our database work
  $stmt_del_team->close();
  $stmt_del_evals->close();
  // Return if we were successful
  return $retVal;
}

function insertTeams($con, $survey_id, &$teams) {
  // Optimistically assume everything works
  $retVal = true;
  $stmt_add = $con->prepare('INSERT INTO teams (survey_id, team_name) VALUES (?, ?)');

  // loop over each team and add it to the database
  foreach ($teams as $name => &$team) {
    $stmt_add->bind_param('is', $survey_id, $name);
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

function insertMissingMembersToTeam($con, $team_id, $roster, $existing_ids) {
  // Optimistically assume everything works
  $retVal = true;
  foreach ($roster as $member) {
    // If the member is not already a member of this team, we need to insert them into the list
    if (!in_array($member['id'], $existing_ids)) {
      $stmt_add_member = $con->prepare('INSERT INTO team_members (team_id, student_id, role) VALUES (?, ?, ?)');
      $stmt_add_member->bind_param('iis', $team_id, $member['id'], $member['role']);
      echo "Adding member " . $member['email'] . " to team " . $team_id . "<br>";

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
      $ret_val['error'][] = $ret_val['error'] . $line_data['error'];
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