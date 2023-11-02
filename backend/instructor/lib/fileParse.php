<?php
function getIdsFromEmails($con, $line_num, $emails) {
  // Otherwise we have a valid team and can try getting the ids
  $retVal = array('error' => '', 'student_data' => array());
  // Make sure the current line's data are valid
  foreach ($emails as $column => $email) {
    $id_and_name = getStudentInfoFromEmail($con, $email);
    if (empty($id_and_name)) {
      $retVal['error'] = $retVal['error'] . 'Line '. $line_num . '  column ' . $column . ' includes an unknown email (' . $email . ')<br>';
    } else {
      $id = $id_and_name[0];
      $name = $id_and_name[1];
      $retVal['student_data'][$email] = array($name, $id);
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

function parse_review_pairs($rows, $student_data) {
  // return array
  $ret_val = array();
  foreach ($rows as $idx => $row) {
    // Get the reviewer infomation
    $reviewer = $row[0];
    $reviewer_id = $student_data[$reviewer][1];
    // Get the person being reviewed's info
    $reviewed = $row[1];
    $reviewed_id = $student_data[$reviewed][1];
    // Create the pairing.
    $ret_val[] = array($reviewer_id, $reviewed_id, $idx, 1);
  }
  return $ret_val;
}

function parse_review_teams($rows, $student_data) {
  // return array
  $ret_val = array();
  foreach ($rows as $idx => $row) {
    // We will need to create a review of each student for every other student.
    $id_len = count($row);
    foreach ($row as $reviewer_email) {
      $reviewer_id = $student_data[$reviewer_email][1];
      for ($k = 0; $k < $id_len; $k++) {
        $reviewed_email = $row[$k];
        $reviewed_id = $student_data[$reviewed_email][1];
        // Append pairings to our array; defaults to each team being independent and each review is equally weighted
        $ret_val[] = array($reviewer_id, $reviewed_id, $idx, 1);
      }
    }
  }
  return $ret_val;
}

function parse_managed_teams($rows, $student_data, $pm_mult) {
  // return array
  $ret_val = array();
  foreach ($rows as $idx => $row) {
    // Remove the manager from the list of students
    $manager_email = array_pop($row);
    $manager_id = $student_data[$manager_email][1];
    // We will need to create a review of each student for every other student.
    $id_len = count($row);
    foreach ($row as $reviewer_email) {
      $reviewer_id = $student_data[$reviewer_email][1];
      // Add the manager's review of this stuent
      $ret_val[] = array($manager_id, $reviewer_id, $idx, $pm_mult);
      for ($k = 0; $k < $id_len; $k++) {
        $reviewed_email = $row[$k];
        $reviewed_id = $student_data[$reviewed_email][1];
        // Append pairings to our array; defaults to each team being independent and each review is equally weighted
        $ret_val[] = array($reviewer_id, $reviewed_id, $idx, 1);
      }
    }
  }
  return $ret_val;
}

function parse_manager_review($rows, $student_data) {
  $ret_val = array();
  foreach ($rows as $idx => $row) {
    // We will need to create a review of each student for every other student.
    $id_len = count($row);
    // Remove the manager from the list of students
    $manager_email = array_pop($row);
    $manager_id = $student_data[$manager_email][1];
    foreach ($row as $reviewer_email) {
      $reviewer_id = $student_data[$reviewer_email][1];
      // Add the manager's review of this stuent
      $ret_val[] = array($reviewer_id, $manager_id, $idx, 1);
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
    if ($line_fields != 0 && $line_fields != 3) {
      $ret_val['error'] = $ret_val['error'] . 'Line ' . $line_num . ' does not contain an email, first name, and last name';
    } else if ($line_fields == 3) {
      $error = '';
      if (!filter_var($line_text[0], FILTER_VALIDATE_EMAIL)) {
        $error = $error . 'Line '. $line_num . ' includes an improperly formatted email address (' . $line_text[0] . ')';
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
        $ret_val['error'] = $ret_val['error'] . $error;
      }
    }
  }
  return $ret_val;
}

function processReviewFile($con, $require_pairs, $file_handle) {
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
      $ret_val['error'] = $ret_val['error'] . 'Line ' . $line_num . ' does not contain a proper review assignment<br>';
    } else {
      // Verify the entries on the current line
      $line_data = getIdsFromEmails($con, $line_num, $line_text);
      if (!empty($line_data['error'])) {
        $ret_val['error'] = $ret_val['error'] . $line_data['error'];
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



?>