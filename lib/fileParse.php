<?php

function parse_review_pairs($file_handle, $db_connection) {
  // return array
  $ret_val = array();

  $line_num = 0;
  while (($line_text = fgetcsv($file_handle, 1000, ",")) !== FALSE) {
    $line_num = $line_num + 1;

    $line_fields = count($line_text);

    // Verify the current line's data seems reasonable.
    if ($line_fields != 2) {
      $ret_val['error'] = 'Input CSV file has incorrect number of fields on line: {$line_num}';
    } else {
      // Clean up any whitespace oddities
      $line_text[0] = trim($line_text[0]);
      $line_text[1] = trim($line_text[1]);

      if (!email_already_exists($line_text[0], $db_connection)) {
        $ret_val['error'] = 'Input CSV file line: {$line_num} has email that is not in system: {$line_text[0]}';
      } else if (!email_already_exists($line_text[1], $db_connection)) {
        $ret_val['error'] = 'Input CSV file line: {$line_num} has email that is not in system: {$line_text[1]}';
      } else {
        // Fast than array_push when appending large numbers of data
        $ret_val[] = $line_text;
      }
    }
  }

  return $ret_val;
}

function parse_review_teams($file_handle, $db_connection) {
  // return array
  $ret_val = array();

  $line_num = 0;
  while (($line_text = fgetcsv($file_handle, 1000, ",")) !== FALSE) {
    $line_num = $line_num + 1;

    $line_fields = count($line_text);

    // Make sure the current line's data are valid
    for ($j = 0; $j < $line_fields; $j++) {
      $line_fields[$j] = trim($line_text[$j]);
      if (!email_already_exists($line_text[$j], $db_connection)) {
        $ret_val['error'] = 'Input CSV file line: {$line_num} has email that is not in system: {$line_text[$j]}';
      }
    }

    // Now that we know data are valid, create & add all possible team pairings
    for ($j = 0; $j < $line_fields; $j++) {
      for ($k = 0; $k < $line_fields; $k++) {
        $pairing = array();
        $pairing[0] = $line_text[$j];
        $pairing[1] = $line_text[$k];
        $ret_val[] = $pairing;
      }
    }
  }

  return $ret_val;
}

function parse_roster_file($file_handle) {
  // return array
  $rev_val = array();

  $line_num = 0;
  while (($line_text = fgetcsv($file_handle, 1000, ",")) !== FALSE) {

    $line_num = $line_num + 1;

    $line_fields = count($line_text);

    if ($line_fields != 2) {
      $split['error'] = 'Input CSV file has incorrect number of fields at line: {$line_num}.';
      return $split;
    }

    // Clean up any whitespace oddities
    $line_text[0] = trim($line_text[0]);
    $line_text[1] = trim($line_text[1]);

    if (!ctype_print($line_text[0])) {
      $split['error'] = 'Input CSV file has name with unprintable character at line: {$line_num}.';
      return $split;
    }

    if (!filter_var($line_text[1], FILTER_VALIDATE_EMAIL)) {
      $split['error'] = 'Input CSV file has incorrect email address at line: {$line_num}.';
      return $split;
    }
    // add the fields to the array
    $ret_val[] = $line_text;
  }
  return $ret_val;
}

function email_already_exists($email_addr, $db_connection) {
  // Create the SQL statement which we can use to verify the email address exists
  $stmt = $db_connection->prepare('SELECT students.student_id FROM students WHERE students.email=?');
  $stmt->bind_param('s', $email_addr);
  $stmt->execute();
  $result = $stmt->get_result();
  $data = $result->fetch_all(MYSQLI_ASSOC);

  // Return if that email address already exists
  return $result->num_rows != 0;
}

function add_pairings($emails, $survey_id, $db_connection) {
  // prepare SQL statements
  $stmt_check = $db_connection->prepare('SELECT id FROM reviewers WHERE survey_id=? AND reviewer_email=? AND teammate_email=?');
  $stmt_add = $db_connection->prepare('INSERT INTO reviewers (survey_id, reviewer_email, teammate_email) VALUES (?, ?, ?)');

  // loop over each array
  $size = count($emails);

  // loop over each pairing
  for ($i = 0; $i < $size; $i++) {
    // check if the pairing already exists
    $stmt_check->bind_param('iss', $survey_id, $emails[$i][0], $emails[$i][1]);
    $stmt_check->execute();
    $result = $stmt_check->get_result();
    $data = $result->fetch_all(MYSQLI_ASSOC);

    // add the pairing if it does not exist
    if ($result->num_rows == 0) {
      $stmt_add->bind_param('issii', $survey_id, $emails[$i][0], $emails[$i][1]);
      $stmt_add->execute();
    }
  }
}
?>
