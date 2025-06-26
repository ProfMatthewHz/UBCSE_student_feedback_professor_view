<?php
function processFileRows($rows, $pairing_mode) {
  $ret_val = array();
  $pure_pairs = ($pairing_mode == 1);
  $manager_mode = ( ($pairing_mode == 3) || ($pairing_mode == 4) );
  $team_num = 1;
  foreach ($rows as $emails) {
    $team = array();
    $last_index = count($emails) - 1;
    foreach ($emails as $idx => $email) {
      if ($pure_pairs) {
        // If we are in pure pairing mode, we only have two emails per row
        if ($idx == 0) {
          $role = 'reviewed';
        } else {
          $role = 'reviewer';
        }
      }
      else if ( !$manager_mode || ($idx !== $last_index) ) {
        $role = 'member';
      } else {
        $role = 'manager';
      }
      $team[] = array('email' => $email, 'role' => $role);
    }
    // Create a team name which, for now, is based on the index
    $teamname = 'team '.$team_num;
    $team_num++;
    $ret_val[$teamname] = $team;
  }
  return $ret_val;
}

function getSurveyTypes($con) {
  $ret_val = array();
  $stmt = $con->prepare('SELECT id, description, file_organization, display_multiplier, text, review_class
                         FROM survey_types
                         ORDER BY id');
  $stmt->execute();
  $result = $stmt->get_result();
  while ($row = $result->fetch_array(MYSQLI_NUM)) {
    $key = array_shift($row);
    $ret_val[$key] = $row;
  }
  $stmt->close();
  return $ret_val;
}

function validatePairs($team_data) {
  $ret_val = array();
  // Check that each team has exactly two members
  foreach ($team_data as $name => $team) {
    // Get the team's roster
    $roster = $team['roster'];
    // Check that the team has exactly two members
    if (count($roster) != 2) {
      $ret_val[] = 'Team '.$name.' must have exactly two members.';
    }
    // Check that the roles are reviewer and reviewing
    if (! ( ( ($roster[0]['role'] == 'reviewed') && ($roster[1]['role'] != 'reviewer') ) ||
            ( ($roster[0]['role'] == 'reviewer') && ($roster[1]['role'] != 'reviewed') ) ) ) {
      $ret_val[] = 'Team '.$name.' must have one reviewer and one reviewed.';
    }
  }
  return $ret_val;
}

function validateUnmanagedTeams($team_data) {
  $ret_val = array();
  // Check that each team has exactly two members
  foreach ($team_data as $name => $team) {
    // Get the team's roster
    $roster = $team['roster'];
    // Check that the team has exactly two members
    if (count($roster) < 2) {
      $ret_val[] = 'Team '.$name.' must have at least two members.';
    }
    foreach ($roster as $member) {
      // Check that each member has the role of member
      if ($member['role'] != 'member') {
        $ret_val[] = 'Team '.$name.' has a member with an invalid role: '.$member['email'];
      }
    }
  }
  return $ret_val;
}

function validateManagedTeams($team_data) {
  $ret_val = array();
  // Check that each team has exactly two members
  foreach ($team_data as $name => $team) {
    // Get the team's roster 
    $roster = $team['roster'];
    // Check that the team has exactly two members
    if (count($roster) < 2) {
      $ret_val[] = 'Team '.$name.' must have at least two members.';
    }
    $managers = 0;
    foreach ($roster as $member) {
      // Check that each member has the role of member
      if ($member['role'] == 'manager') {
        $managers++;
      } else if ($member['role'] != 'member') {
        $ret_val[] = 'Team '.$name.' has a member with an invalid role: '.$member['email'];
      }
    }
    if ($managers == 0) {
      $ret_val[] = 'Team '.$name.' does not include a manager.';
    }
  }
  return $ret_val;
}
 
function validateTeams($pairing_mode, $team_data) {
  switch ($pairing_mode) {
    case 1: // Pairs
      $ret_val = validatePairs($team_data);
      break;
    case 2: // Team + SELF
    case 5: // TEAM (w/o self review)
      $ret_val = validateUnmanagedTeams($team_data);
      break;
    case 3: // Team + SELF + MANAGER
    case 4: // PM
      $ret_val = validateManagedTeams($team_data);  
      break;
    }
  return $ret_val;
}

function pairings_from_pairs($teams) {
  // return array
  $ret_val = array();
  foreach ($teams as $team) {
    $reviewer = -1;
    $reviewed = -1;
    foreach ($team['roster'] as $member) {
      // Already validated all the teams, so only need to make pairs
      if ($member['role'] == 'reviewer') {
        $reviewer = $member['id'];
      } else {
        $reviewed = $member['id'];
      }
    }
    $pairing = array($reviewer, $reviewed, $team['id'], 1);
    $ret_val[] = $pairing;
  }
  return $ret_val;
}

function pairings_from_unmanaged_teams($teams, $self_reviews) {
  // return array
  $ret_val = array();
  foreach ($teams as $team) {
    $roster = $team['roster'];
    $team_id = $team['id'];
    $roster_len = count($roster);
    foreach ($roster as $idx => $member) {
      $reviewer_id = $member['id'];
      for ($k = 0; $k < $roster_len; $k++) {
        $reviewed_id = $roster[$k]['id'];
        if ($self_reviews || ($idx != $k)) {
          // Append pairings to our array; defaults to each team being independent and each review is equally weighted
          $ret_val[] = array($reviewer_id, $reviewed_id, $team_id, 1);
        }
      }
    }
  }
  return $ret_val;
}

function pairings_from_managed_teams($teams, $pm_mult) {
  // return array
  $ret_val = array();
  foreach ($teams as $team) {
    $roster = $team['roster'];
    $roster_len = count($roster);
    foreach ($roster as $member) {
      $weight = 1;
      $reviewer_id = $member['id'];
      if ($member['role'] == 'manager') {
        // If the member is a manager, then we will use the pm_mult to weight their reviews
        $weight = $pm_mult;
      }
      for ($k = 0; $k < $roster_len; $k++) {
        $reviewed = $roster[$k];
        if ($reviewed['role'] != 'manager') {
          // Append pairings to our array; defaults to each team being independent and each review is equally weighted
          $ret_val[] = array($reviewer_id, $reviewed['id'], $team['id'], $weight);
        }
      }
    }
  }
  return $ret_val;
}

function pairings_for_managers($teams) {
// return array
  $ret_val = array();
  foreach ($teams as $team) {
    $roster = $team['roster'];
    $roster_len = count($roster);
    foreach ($roster as $member) {
      if ($member['role'] === 'member') {
        $reviewer_id = $member['id'];
        for ($k = 0; $k < $roster_len; $k++) {
          $reviewed = $roster[$k];
          if ($reviewed['role'] === 'manager') {
            // Append pairings to our array; defaults to each team being independent and each review is equally weighted
            $ret_val[] = array($reviewer_id, $reviewed['id'], $team['id'], 1);
          }
        }
      }
    }
  }
  return $ret_val;
}

function generatePairingsFromTeams($teams, $pm_mult, $pairing_mode) {
  $pairings = null;
  switch ($pairing_mode) {
    case 1: // Pairs
      $pairings = pairings_from_pairs($teams);
      break;
    case 2: // Team + SELF
      $pairings = pairings_from_unmanaged_teams($teams, true);
      break;
    case 3: // Team + SELF + MANAGER
      $pairings = pairings_from_managed_teams($teams, $pm_mult);
      break;
    case 4: // PM
      $pairings = pairings_for_managers($teams);
      break;
    case 5: // TEAM (w/o self review)
      $pairings = pairings_from_unmanaged_teams($teams, false);
      break;
  }
  return $pairings;
}
?>