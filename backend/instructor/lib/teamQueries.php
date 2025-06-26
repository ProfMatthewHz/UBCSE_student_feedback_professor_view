<?php
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
  $team_data = array();
  foreach ($rows as $row) {
    $team_data[$row['id']] = $row['team_name'];
    $retVal[$row['team_name']] = array();
  }
  $stmt_team->close();
  // Get the members of each team
  $stmt_members = $con->prepare('SELECT email, role
                                 FROM team_members
                                 INNER JOIN students ON team_members.student_id = students.id
                                 WHERE team_id = ?');
  foreach ($team_data as $team_id => $team_name) {
    $stmt_members->bind_param('i', $team_id);
    $stmt_members->execute();
    $result_members = $stmt_members->get_result();
    $members = $result_members->fetch_all(MYSQLI_ASSOC);
    // Add the members to the team
    foreach ($members as $member) {
      $retVal[$team_name][] = array(
        'email' => $member['email'],
        'role' => $member['role']
      );
    }
  }
  // Clean up after our query
  $stmt_members->close();
  return $retVal;
}

function getTeamsInSurvey($con, $survey_id) {
  $retVal = array();
  $stmt_team = $con->prepare('SELECT id, team_name
                              FROM teams
                              WHERE survey_id = ?');
  $stmt_team->bind_param('i', $survey_id);
  $stmt_team->execute();
  $result = $stmt_team->get_result();
  $rows = $result->fetch_all(MYSQLI_ASSOC);
  foreach ($rows as $row) {
    $retVal[] = $row;
  }
  // Clean up after out query
  $stmt_team->close();
  return $retVal;
}

function deleteTeamsInSurvey($con, $survey_id, $team_ids) {
  // Optimistically assume everything works
  $retVal = true;
  $stmt_del = $con->prepare('DELETE FROM teams WHERE survey_id = ? AND id = ?');
  // Loop over each team and delete it from the database
  foreach ($team_ids as $team_id) {
    $stmt_del->bind_param('ii', $survey_id, $team_id);
    if (!$stmt_del->execute()) {
      $retVal = false;
    }
  }
  // Clean up our database work
  $stmt_del->close();
  // Return if we were successful
  return $retVal;
}

function updateTeamsInSurvey($con, $survey_id, &$teams) {
  // Optimistically assume everything works
  $retVal = true;
  $stmt_check = $con->prepare('SELECT id
                               FROM teams
                               WHERE survey_id = ? AND id = ?');
  $stmt_update = $con->prepare('UPDATE teams SET team_name = ? WHERE survey_id = ? AND id = ?');
  $stmt_add = $con->prepare('INSERT INTO teams (survey_id, team_name) VALUES (?, ?)');
  // loop over each team and add it to the database
  foreach ($teams as &$team) {
    // First we check to see if the team already exists
    $stmt_check->bind_param('ii', $survey_id, $team['id']);
    $stmt_check->execute();
    $result = $stmt_check->get_result();
    $data = $result->fetch_all(MYSQLI_ASSOC);
    // If the team already exists....
    if ($result->num_rows()) {
      // It does exist, so we just need to update it
      $stmt_update->bind_param('sii', $team['name'], $survey_id, $team['id']);
      if (!$stmt_update->execute()) {
        $retVal = false;
      }
    } else {
      $stmt_add->bind_param('is', $survey_id, $team['name']);
      if (!$stmt_add->execute()) {
        $retVal = false;
      } else {
        $team['id'] = $con->insert_id;
      }
    }
  }
  // Remove the reference to the team now that we do not need it
  unset($tean);
  // Clean up our database work
  $stmt_check->close();
  $stmt_update->close();
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

function addTeamMembers($con, $team_id, $roster) {
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
    $result = addTeamMembers($con, $team['id'], $team['roster']);
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
  foreach ($teams as $name => $roster) {
    // Verify the entries on the current line
    $line_data = getIdsForRoster($con, $name, $roster);
    if (!empty($line_data['error'])) {
      $ret_val['error'][] = $ret_val['error'] . $line_data['error'];
    } else {
      // Add the row to our list of (valid) rows
      $ret_val['teams'][$name] = array('roster' => $line_data['roster']);
    }
  }
  return $ret_val;
}
?>