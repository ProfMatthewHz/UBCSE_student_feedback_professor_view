<?php
function force_ascii($str) {

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
  
    return $str;
}

function parse_review_pairs($file_handle, $db_connection) {
  // return array
  $ret_val = array();

  $line_num = 0;
  while (($line_text = fgetcsv($file_handle)) !== FALSE) {
    $line_num = $line_num + 1;

    // Force the input into ASCII format
    $line_text = array_map("force_ascii", $line_text);

    // Clean up any whitespace oddities
    $line_text = array_map("trim", $line_text);

    $line_fields = count($line_text);

    // Verify the current line's data seems reasonable.
    if ($line_fields != 2) {
      $ret_val['error'] = 'Input CSV file has incorrect number of fields on line ' . $line_num;
      return $ret_val;
    } else {
      if (!email_already_exists($line_text[0], $db_connection)) {
        $ret_val['error'] = 'Input CSV file at line '. $line_num . ' includes an email that is not in system: ' . $line_text[0];
        return $ret_val;
      } else if (!email_already_exists($line_text[1], $db_connection)) {
        $ret_val['error'] = 'Input CSV file at line '. $line_num . ' includes an email that is not in system: ' . $line_text[1];
        return $ret_val;
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
  while (($line_text = fgetcsv($file_handle)) !== FALSE) {
    $line_num = $line_num + 1;

    // Force the input into ASCII format
    $line_text = array_map("force_ascii", $line_text);

    // Clean up any whitespace oddities
    $line_text = array_map("trim", $line_text);

    $line_fields = count($line_text);

    // Make sure the current line's data are valid
    for ($j = 0; $j < $line_fields; $j++) {
      if ((!empty($line_text[$line_fields])) && !email_already_exists($line_text[$j], $db_connection)) {
        $ret_val['error'] = 'Input CSV file at line '. $line_num . ' includes an email that is not in system: ' . $line_text[$j];
        return $ret_val;
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

function parse_review_managed_teams($file_handle, $db_connection) {
  // return array
  $ret_val = array();

  $line_num = 0;
  while (($line_text = fgetcsv($file_handle)) !== FALSE) {
    $team_members = array();
    $team_size = 0;

    $line_num = $line_num + 1;

    // Force the input into ASCII format
    $line_text = array_map("force_ascii", $line_text);

    // Clean up white space oddities
    $line_text = array_map("trim", $line_text);

    $line_fields = count($line_text) - 1;

    // Make sure the current line's data are valid
    for ($j = 0; $j < $line_fields; $j++) {
      if (!empty($line_text[$j])) {
        if (!email_already_exists($line_text[$j], $db_connection)) {
          $ret_val['error'] = 'Input CSV file at line '. $line_num . ' includes an email that is not in system: ' . $line_text[$j].".";
          return $ret_val;
        } else {
          $team_members[] = $line_text[$j];
          $team_size = $team_size + 1;
        }
      }
    }

    $manager = $line_text[$line_fields];
    if (!email_already_exists($manager, $db_connection)) {
      $ret_val['error'] = 'Input CSV file at line '. $line_num . ' includes an email that is not in system: ' . $manager;
      return $ret_val;
    }

    // Now that we know data are valid, create & add all possible team pairings
    for ($j = 0; $j < $team_size; $j++) {
      $managed = array();
      $managed[0] = $manager;
      $managed[1] = $team_members[$j];
      $ret_val[] = $managed;

      for ($k = 0; $k < $team_size; $k++) {
        $pairing = array();
        $pairing[0] = $team_members[$j];
        $pairing[1] = $team_members[$k];
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
  while (($line_text = fgetcsv($file_handle)) !== FALSE) {

    $line_num = $line_num + 1;

    // Force the input into ASCII format
    $line_text = array_map("force_ascii", $line_text);

    // Clean up whitespace oddities
    $line_text = array_map("trim", $line_text);

    $line_fields = count($line_text);

    if ($line_fields != 2) {
      $ret_val['error'] = 'Input CSV file has incorrect format at line ' . $line_num;
      return $ret_val;
    }

    if (!ctype_print($line_text[0])) {
      $ret_val['error'] = 'Input CSV file includes a name ('.$line_text[0].') with unprintable characters on line ' . $line_num;
      return $ret_val;
    }

    if (!filter_var($line_text[1], FILTER_VALIDATE_EMAIL)) {
      $ret_val['error'] = 'Input CSV file includes an improperly formatted email address on line ' . $line_num;
      return $ret_val;
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
  $count = mysqli_num_rows($result);

  // Return if that email address already exists
  return $count != 0;
}

function add_pairings($emails, $survey_id, $db_connection) {
  // prepare SQL statements
  $stmt_check = $db_connection->prepare('SELECT id FROM reviewers WHERE survey_id=? AND reviewer_email=? AND teammate_email=?');
  $stmt_add = $db_connection->prepare('INSERT INTO reviewers (survey_id, reviewer_email, teammate_email) VALUES (?, ?, ?)');

  // loop over each pairing
  foreach ($emails as $pairing) {
    // check if the pairing already exists
    $stmt_check->bind_param('iss', $survey_id, $pairing[0], $pairing[1]);
    $stmt_check->execute();
    $result = $stmt_check->get_result();
    $data = $result->fetch_all(MYSQLI_ASSOC);

    // add the pairing if it does not exist
    if ($result->num_rows == 0) {
      $stmt_add->bind_param('iss', $survey_id, $pairing[0], $pairing[1]);
      $stmt_add->execute();
    }
  }
}
?>
