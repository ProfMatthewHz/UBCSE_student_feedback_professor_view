<?php
function getIdsFromEmails($con, $line_num, $emails, $roster) {
  // Otherwise we have a valid team and can try getting the ids
  $retVal = array('error' => array(), 'student_data' => array());
  // Make sure the current line's data are valid
  foreach ($emails as $column => $email) {
    $id_and_name = getStudentInfoFromEmail($con, $email);
    if (empty($id_and_name)) {
      $retVal['error'][] = 'Line '. $line_num . '  column ' . $column . ' includes an unknown email (' . $email . ')';
    } else {
      $name = $id_and_name[1];
      $rostered = array_key_exists($email, $roster);
      $retVal['student_data'][$email] = array(
        'name' => $name,
        'email' => $email,
        'rostered' => $rostered
      );
    }
  }
  return $retVal;
}

function clean_to_ascii($str) {
  // detect the character encoding
  $encoding = mb_detect_encoding($str, ['ASCII','UTF-8']);
    
  // escape all of the question marks to remove any illegal artifacts
  $str = str_replace( "?", "[question_mark]", $str);
    
  // convert the string to the target encoding
  $str = mb_convert_encoding($str, "ASCII", $encoding);
    
  // remove any question marks that have been introduced because of illegal characters
  $str = str_replace( "?", "", $str);
    
  // replace the token string "[question_mark]" with the symbol "?"
  $str = str_replace("[question_mark]", "?", $str);
  
  // Finally, clean up the results by removing any extra whitespace
  $str = trim($str);
  return $str;
}

function parse_roster_file($file_handle) {
  // return array
  $ret_val = array('error' => array(), 'ids' => array());

  $line_num = 0;
  while (($line_text = fgetcsv($file_handle)) !== FALSE) {
    $line_num = $line_num + 1;

    // Force the input into ASCII format
    $line_text = array_map("clean_to_ascii", $line_text);
    // Remove any blank entries from the array
    $line_text = array_filter($line_text);

    $line_fields = count($line_text);

    // Verify the current line's data seems reasonable while allowing us to skip blank lines
    if ($line_fields != 0 && $line_fields < 3) {
      $ret_val['error'][] = 'Line ' . $line_num . ' missing at least 1 of: email address, first name, and last name';
    } else if ($line_fields > 3) {
      $ret_val['error'][] = 'Line ' . $line_num . ' has more than just: email address, first name, and last name';
    } else if ($line_fields == 3) {
      // Must have 3 fields on this line
      $error = '';
      if (!filter_var($line_text[0], FILTER_VALIDATE_EMAIL)) {
        $error = 'Line '. $line_num . ' includes an improperly formatted email address (' . $line_text[0] . ')';
      }
      if (!ctype_print($line_text[1])) {
        $error = 'Line '. $line_num . ' includes a first name with unprintable characters (' . $line_text[1] . ')';
      }
      if (!ctype_print($line_text[2])) {
        $error = 'Line '. $line_num . ' includes a last name with unprintable characters (' . $line_text[2] . ')';
      }
      if (empty($error)) {
        // add the fields to the array
        $ret_val['ids'][$line_text[0]] = $line_text[1] . " " . $line_text[2];
      } else {
        $ret_val['error'][] = $error;
      }
    }
  }
  return $ret_val;
}

function processTeamFile($con, $file_handle, $roster) {
  $ret_val = array('error' => array(), 'individuals' => array(), 'teams' => array());

  $line_num = -1;
  // Loop through each row in the (CSV) file
  while (($line_text = fgetcsv($file_handle)) !== FALSE) {
    $line_num = $line_num + 1;

    // Force the input into ASCII format
    $line_text = array_map("clean_to_ascii", $line_text);
    // Remove any blank entries from the array
    $line_text = array_filter($line_text);

    // This is horrible practice, but it handles the degenerate case of a blank line and makes the code much easier to read
    if (empty($line_text)) {
      continue;
    }

    $team_name = array_shift($line_text);

    if (count($line_text) == 0) {
      $ret_val['error'][] = 'Line ' . $line_num . ' with team name ' . $team_name . ' does not include team members';
    } else {
      // Verify the entries on the current line
      $line_data = getIdsFromEmails($con, $line_num, $line_text, $roster);
      if (!empty($line_data['error'])) {
        $ret_val['error'] = array_merge($ret_val['error'], $line_data['error']);
      } else {
        // Add any new individuals to our tracking
        $ret_val['individuals'] = array_merge($ret_val['individuals'], $line_data['student_data']);
        $ret_val['teams'][$team_name] = array('roster' => array());
        // Add the team and its members in the proper format so we can view it as a valid team
        foreach ($line_text as $email) {
          $ret_val['teams'][$team_name]['roster'][] = array("email" => $email, "role" => "member");
        }
      }
    }
  }
  return $ret_val;
}

function processReviewFile($con, $require_pairs, $file_handle, $roster) {
  $ret_val = array('error' => '', 'rows' => array(), 'individuals' => array());

  $line_num = -1;
  // Loop through each row in the (CSV) file
  while (($line_text = fgetcsv($file_handle)) !== FALSE) {
    $line_num = $line_num + 1;

    // Force the input into ASCII format
    $line_text = array_map("clean_to_ascii", $line_text);
    // Remove any blank entries from the array
    $line_text = array_filter($line_text);

    // This is horrible practice, but it handles the degenerate case of a blank line and makes the code much easier to read
    if (empty($line_text)) {
      continue;
    }
    // Check for an incorrect number of entries on the current line
    if ( $require_pairs && (count($line_text) !== 2) ) {
      $ret_val['error'] = $ret_val['error'] . 'Line ' . $line_num . ' does not contain a proper review assignment';
    } else {
      // Verify the entries on the current line
      $line_data = getIdsFromEmails($con, $line_num, $line_text, $roster);
      if (!empty($line_data['error'])) {
        $ret_val['error'] = array_merge($ret_val['error'], $line_data['error']);
      } else {
        // Add any new individuals to our tracking
        $ret_val['individuals'] = array_merge($ret_val['individuals'], $line_data['student_data']);
        // Add the row to our list of (valid) rows
        $ret_val['rows'][] = $line_text;
      }
    }
  }
  return $ret_val;
}

function processAggregateReviewFile($con, $file_handle, $teams) {
  $ret_val = array('error' => array(), 'matchups' => array());

  $line_num = -1;
  // Loop through each row in the (CSV) file
  while (($line_text = fgetcsv($file_handle)) !== FALSE) {
    $line_num = $line_num + 1;

    // Force the input into ASCII format
    $line_text = array_map("clean_to_ascii", $line_text);
    // Remove any blank entries from the array
    $line_text = array_filter($line_text);

    // This is horrible practice, but it handles the degenerate case of a blank line and makes the code much easier to read
    if (empty($line_text)) {
      continue;
    }

    if (count($line_text) != 2) {
      $ret_val['error'][] = 'Line ' . $line_num . ' does not contain a proper review assignment';
    } else {
      // Verify the entries on the current line
      $reviewer = $line_text[0];
      $reviewed = $line_text[1];
      $reviewer_known = array_key_exists($reviewer, $teams);
      $reviewed_known = array_key_exists($reviewed, $teams);
      if (!$reviewer_known) {
        $ret_val['error'][] = 'Line ' . $line_num . ' includes an unknown reviewing team (' . $reviewer . ')';
      } 
      if (!$reviewed_known) {
        $ret_val['error'][] = 'Line ' . $line_num . ' includes an unknown team being reviewed (' . $reviewed . ')';
      }
      if ($reviewed_known && $reviewer_known) {
        $ret_val['matchups'][] = array("reviewing" => $reviewer, "reviewed" => $reviewed);
      }
    }
  }
  return $ret_val;
} 

?>