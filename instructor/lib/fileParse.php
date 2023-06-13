<?php
require_once '../lib/studentQueries.php';

function getIdsFromEmails($con, $line_num, $emails) {
  // We do not allow teams of 1, so flag this as an error right away.
  if (count($emails) == 1) {
    $retVal = array('error' => 'Line ' . $line_num . ' only contains 1 name\n');
    return $retVal;
  }
  // Otherwise we have a valid team and can try getting the ids
  $retVal = array('error' => '', 'ids' => array());
  // Make sure the current line's data are valid
  foreach ($emails as $column => $email) {
    $student_id = getIdFromEmail($con, $email);
    if (empty($student_id)) {
      $retVal['error'] = $$retVal['error'] . 'Line '. $line_num . ' at column ' . $column . ' includes an unknown person (' . $email . ')\n';
    } else {
      $retVal['ids'][] = $student_id;
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

function parse_review_pairs($file_handle, $con) {
  // return array
  $ret_val = array();

  $line_num = 0;
  while (($line_text = fgetcsv($file_handle)) !== FALSE) {
    $line_num = $line_num + 1;

    // Force the input into ASCII format
    $line_text = array_map("clean_to_ascii", $line_text);
    // Remove any blank entries from the array
    $line_text = array_filter($line_text);

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
      }
      if (!empty($reviewer_id) && !empty($reviewed_id)) {
        // Fastest way to append an array; assumes each eval is independent and so is equally weighted
        $ret_val[] = array($reviewer_id, $reviewed_id, $line_num, 1);
      }
    }
  }
  return $ret_val;
}

function parse_review_teams($file_handle, $con) {
  // return array
  $ret_val = array('error' => '', 'ids' => array());

  $line_num = -1;
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

    // Get the errors and ids from the current line
    $line_data = getIdsFromEmails($con, $line_num, $line_text);

    if (empty($line_data['error'])) {
      // Now that we know data are valid, create & add all possible team pairings
      $id_len = count($line_data['ids']);
      foreach ($line_data['ids'] as $reviewer_id) {
        for ($k = 0; $k < $id_len; $k++) {
          // Append pairings to our array array; defaults to each team being independent and each review is equally weighted
          $pairing = array($reviewer_id, $line_data['ids'][$k], $line_num, 1);
          $ret_val['ids'][] = $pairing;
        }
      }
    } else {
      $ret_val['error'] = $ret_val['error'] . $line_data['error'];
    }
  }

  return $ret_val;
}

function parse_review_managed_teams($file_handle, $pm_mult, $con) {
  // return array
  $ret_val = array('error' => '', 'ids' => array());

  $line_num = -1;
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
    
    // Get the errors and ids from the current line
    $line_data = getIdsFromEmails($con, $line_num, $line_text);

    // Only add lines that do not have errors
    if (empty($error)) {
      $manager = array_pop($line_data['ids']);
      $team_size = count($line_data['ids']);
      // Now that we know data are valid, create & add all possible team pairings
      foreach ($line_data['ids'] as $member) {
        // Add the manager's review
        $managed = array($manager, $member, $line_num, $pm_mult);
        $ret_val['ids'][] = $managed;
        // Now add all of the team member's reviews
        for ($k = 0; $k < $team_size; $k++) {
          $pairing = array($member, $line_data['ids'][$k], $line_num, 1);
          $ret_val['ids'][] = $pairing;
        }
      }
    } else {
      $ret_val['error'] = $ret_val['error'] . $line_data['error'];
    }
  }

  return $ret_val;
}

function parse_review_many_to_one($file_handle, $con) {
  // return array
  $ret_val = array('error' => '', 'ids' => array());

  $line_num = -1;
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

    // Get the errors and ids from the current line
    $line_data = getIdsFromEmails($con, $line_num, $line_text);

    // Only add lines that do not have errors
    if (empty($error)) {
      $manager = array_pop($line_data['ids']);
      // Now that we know data are valid, create & add all possible team pairings
      foreach ($line_data['ids'] as $member) {
        // Add the manager's review
        $managed = array($member, $manager, $line_num, 1);
        $ret_val['ids'][] = $managed;
      }
    } else {
      $ret_val['error'] = $ret_val['error'] . $line_data['error'];
    }
  }

  return $ret_val;
}

function parse_roster_file($file_handle) {
  // return array
  $ret_val = array('error' => '', 'ids' => array());

  $line_num = 0;
  while (($line_text = fgetcsv($file_handle)) !== FALSE) {
    $line_num = $line_num + 1;

    // Force the input into ASCII format
    $line_text = array_map("clean_to_ascii", $line_text);
    // Remove any blank entries from the array
    $line_text = array_filter($line_text);

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
        $ret_val['ids'] = $line_text;
      } else {
        $ret_val['error'] = $ret_val['error'] . $error;
      }
    }
  }
  return $ret_val;
}
?>
