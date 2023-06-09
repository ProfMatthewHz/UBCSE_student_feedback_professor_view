<?php
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

function parse_review_pairs($file_handle, $con) {
  // return array
  $ret_val = array();

  $line_num = 0;
  while (($line_text = fgetcsv($file_handle)) !== FALSE) {
    $line_num = $line_num + 1;

    // Force the input into ASCII format
    $line_text = array_map("clean_to_ascii", $line_text);

    $line_fields = count($line_text);

    // Verify the current line's data seems reasonable while allowing us to skip blank lines
    if ($line_fields == 1 || $line_fields > 2) {
      $ret_val['error'] = $ret_val['error'] . 'Line ' . $line_num . ' does not contain a review pair\n';
    } else if ($line_fields == 2) {
      $reviewer_id = getIdFromEmail($con, $line_text[0]);
      if (empty($reviewer_id)) {
        $ret_val['error'] = $ret_val['error'] . 'Line '. $line_num . ' includes an unknown reviewer (' . $line_text[0] . ')\n';
      }
      $reviewed_id = getIdFromEmail($con, $line_text[1]);
      if (empty($reviewed_id)) {
        $ret_val['error'] = $ret_val['error'] . 'Line '. $line_num . ' includes an unknown person being reviewed (' . $line_text[1] . ')\n';
        return $ret_val;
      }
      if (!empty($reviewer_id) && !empty($reviewed_id)) {
        // Fastest way to append an array; assumes each eval is independent and so is equally weighted
        $ret_val[] = array(strval($reviewer_id), strval($reviewed_id), $line_num, 1);
      }
    }
  }
  return $ret_val;
}

function parse_review_teams($file_handle, $con) {
  // return array
  $ret_val = array();

  $line_num = 0;
  while (($line_text = fgetcsv($file_handle)) !== FALSE) {
    $line_num = $line_num + 1;

    // Force the input into ASCII format
    $line_text = array_map("clean_to_ascii", $line_text);

    // Create the array of student id's for this line
    $ids = array();
    $error = '';
    $column = 0;
    // Make sure the current line's data are valid
    foreach ($line_text as $email) {
      if (!empty($email)) {
        $student_id = getIdFromEmail($con, $email);
        if (empty($student_id)) {
          $error = $error . 'Line '. $line_num . ' at column ' . $column . ' includes an unknown person (' . $email . ')\n';
        } else {
          $ids[] = $student_id;
        }
      }
      $column = $column + 1;
    }
    if (empty($error)) {
      // Now that we know data are valid, create & add all possible team pairings
      $id_len = count($ids);
      foreach ($ids as $reviewer_id) {
        for ($k = 0; $k < $id_len; $k++) {
          // Append pairings to our array array; defaults to each team being independent and each review is equally weighted
          $pairing = array($reviewer_id, $ids[$k], $line_num, 1);
          $ret_val[] = $pairing;
        }
      }
    } else {
      if (isset($ret_val['error'])) {
        $ret_val['error'] = $ret_val['error'] . $error;
      } else {
        $ret_val['error'] = $error;
      }
    }
  }

  return $ret_val;
}

function parse_review_managed_teams($file_handle, $pm_mult, $con) {
  // return array
  $ret_val = array();

  $line_num = 0;
  while (($line_text = fgetcsv($file_handle)) !== FALSE) {
    $team_members = array();
    unset($manager);
    $error = '';
    $line_num = $line_num + 1;

    // Force the input into ASCII format
    $line_text = array_map("clean_to_ascii", $line_text);

    $column = 0;
    // Make sure the current line's data are valid
    foreach ($line_text as $email) {
      if (!empty($email)) {
        $student_id = getIdFromEmail($con, $email);
        if (empty($student_id)) {
          $error = $error . 'Line '. $line_num . ' at column ' . $column . ' includes an unknown person (' . $email . ')\n';
        } else {
          if (isset($manager)) {
            // We process each column as if it is the manager. We now add it to the team since we know it is not.
            $team_members[] = $manager;
          }
          $manager = $student_id;
        }
      }
    }
    // Any lines that do not have at least 1 team member and 1 manager are an error
    if (empty($team_members) && isset($manager) && empty($error)) {
        $error = 'Line '. $line_num . ' only includes a manager\n';
    }
    // Only add lines that do not have errors
    if (empty($error)) {
      $team_size = count($team_members);
      // Now that we know data are valid, create & add all possible team pairings
      foreach ($team_members as $member) {
        // Add the manager's review
        $managed = array($manager, $member, $line_num, $pm_mult);
        $ret_val[] = $managed;
        // Now add all of the team member's reviews
        for ($k = 0; $k < $team_size; $k++) {
          $pairing = array($member, $team_members[$k], $line_num, 1);
          $ret_val[] = $pairing;
        }
      }
    } else {
      if (isset($ret_val['error'])) {
        $ret_val['error'] = $ret_val['error'] . $error;
      } else {
        $ret_val['error'] = $error;
      }
    }
  }

  return $ret_val;
}

function parse_review_many_to_one($file_handle, $con) {
  // return array
  $ret_val = array();

  $line_num = 0;
  while (($line_text = fgetcsv($file_handle)) !== FALSE) {
    $team_members = array();
    unset($manager);
    $error = '';
    $line_num = $line_num + 1;

    // Force the input into ASCII format
    $line_text = array_map("clean_to_ascii", $line_text);

    $column = 0;
    // Make sure the current line's data are valid
    foreach ($line_text as $email) {
      if (!empty($email)) {
        $student_id = getIdFromEmail($con, $email);
        if (empty($student_id)) {
          $error = $error . 'Line '. $line_num . ' at column ' . $column . ' includes an unknown person (' . $email . ')\n';
        } else {
          if (isset($manager)) {
            // We process each column as if it is the manager. We now add it to the team since we know it is not.
            $team_members[] = $manager;
          }
          $manager = $student_id;
        }
      }
    }
    // Any lines that do not have at least 1 team member and 1 manager are an error
    if (empty($team_members) && isset($manager) && empty($error)) {
        $error = 'Line '. $line_num . ' only includes a manager\n';
    }
    // Only add lines that do not have errors
    if (empty($error)) {
      // Now that we know data are valid, create & add all possible team pairings
      foreach ($team_members as $member) {
        // Add the manager's review
        $managed = array($member, $manager, $line_num, 1);
        $ret_val[] = $managed;
      }
    } else {
      if (isset($ret_val['error'])) {
        $ret_val['error'] = $ret_val['error'] . $error;
      } else {
        $ret_val['error'] = $error;
      }
    }
  }

  return $ret_val;
}

function parse_roster_file($file_handle) {
  // return array
  $ret_val = array();

  $line_num = 0;
  while (($line_text = fgetcsv($file_handle)) !== FALSE) {
    $line_num = $line_num + 1;

    // Force the input into ASCII format
    $line_text = array_map("clean_to_ascii", $line_text);

    $line_fields = count($line_text);

    // Verify the current line's data seems reasonable while allowing us to skip blank lines
    if ($line_fields == 1 || $line_fields > 2) {
      $ret_val['error'] = $ret_val['error'] . 'Line ' . $line_num . ' does not contain a name and email\n';
    } else if ($line_fields == 2) {
      $error = '';
      if (!ctype_print($line_text[0])) {
        $error = 'Line '. $line_num . ' includes a name with unprintable characters (' . $line_text[0] . ')\n';
      }
      if (!filter_var($line_text[1], FILTER_VALIDATE_EMAIL)) {
        $error = $error . 'Line '. $line_num . ' includes an improperly formatted email address (' . $line_text[1] . ')\n';
      }
      if (empty($error)) {
        // add the fields to the array
        $ret_val[] = $line_text;
      } else {
        if (isset($ret_val['error'])) {
          $ret_val['error'] = $ret_val['error'] . $error;
        } else {
          $ret_val['error'] = $error;
        }
      }
    }
  }
  return $ret_val;
}
?>
