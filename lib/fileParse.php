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

function parse_review_pairs($file_handle, $con) {
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
      $ret_val['error'] = 'CSV file does not have a review pair on line ' . $line_num;
      return $ret_val;
    } else {
      if (!email_already_exists($con, $line_text[0])) {
        $ret_val['error'] = 'CSV file at line '. $line_num . ' includes an email that is not in system: ' . $line_text[0];
        return $ret_val;
      } else if (!email_already_exists($con, $line_text[1])) {
        $ret_val['error'] = 'CSV file at line '. $line_num . ' includes an email that is not in system: ' . $line_text[1];
        return $ret_val;
      } else {
        // Default to a weighting of 1
        $line_text[] = 1;
        // Fast than array_push when appending large numbers of data
        $ret_val[] = $line_text;
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
    $line_text = array_map("force_ascii", $line_text);

    // Clean up any whitespace oddities
    $line_text = array_map("trim", $line_text);

    $line_fields = count($line_text);

    // Make sure the current line's data are valid
    for ($j = 0; $j < $line_fields; $j++) {
      if ( (!empty($line_text[$j])) && !email_already_exists($con, $line_text[$j])) {
        $ret_val['error'] = 'CSV file at line '. $line_num . ' includes an email that is not in system: ' . $line_text[$j];
        return $ret_val;
      }
    }

    // Now that we know data are valid, create & add all possible team pairings
    for ($j = 0; $j < $line_fields; $j++) {
      for ($k = 0; $k < $line_fields; $k++) {
        if ((!empty($line_text[$j])) && (!empty($line_text[$k]))) {
          $pairing = array();
          $pairing[0] = $line_text[$j];
          $pairing[1] = $line_text[$k];
          $pairing[2] = 1;
          $ret_val[] = $pairing;
        }
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
    $team_size = 0;
    unset($manager);

    $line_num = $line_num + 1;

    // Force the input into ASCII format
    $line_text = array_map("force_ascii", $line_text);

    // Clean up white space oddities
    $line_text = array_map("trim", $line_text);

    $line_fields = count($line_text) - 1;
    
    // Make sure the current line's data are valid
    for ($j = $line_fields; $j >= 0; $j--) {
      if (!empty($line_text[$j])) {
        if (!email_already_exists($con, $line_text[$j])) {
          $ret_val['error'] = 'CSV file at line '. $line_num . ' includes an email that is not in system: ' . $line_text[$j];
          return $ret_val;
        } else if (!isset($manager)) {
          $manager = $line_text[$j];
        } else {
          $team_members[] = $line_text[$j];
          $team_size = $team_size + 1;
        }
      }
    }
    // Skip over any lines that are entirely blank
    if (isset($manager)) {
      if ($team_size == 0) {
        $ret_val['error'] = 'CSV file at line '. $line_num . ' only lists a manager: ' . $manager;
        return $ret_val;
      }

      // Now that we know data are valid, create & add all possible team pairings
      for ($j = 0; $j < $team_size; $j++) {
        $managed = array();
        $managed[0] = $manager;
        $managed[1] = $team_members[$j];
        $managed[2] = $pm_mult;
        $ret_val[] = $managed;

        for ($k = 0; $k < $team_size; $k++) {
          $pairing = array();
          $pairing[0] = $team_members[$j];
          $pairing[1] = $team_members[$k];
          $pairing[2] = 1;
          $ret_val[] = $pairing;
        }
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
    $team_size = 0;
    unset($reviewee);

    $line_num = $line_num + 1;

    // Force the input into ASCII format
    $line_text = array_map("force_ascii", $line_text);

    // Clean up white space oddities
    $line_text = array_map("trim", $line_text);

    $line_fields = count($line_text) - 1;
    
    // Make sure the current line's data are valid
    for ($j = $line_fields; $j >= 0; $j--) {
      if (!empty($line_text[$j])) {
        if (!email_already_exists($con, $line_text[$j])) {
          $ret_val['error'] = 'CSV file at line '. $line_num . ' includes an email that is not in system: ' . $line_text[$j];
          return $ret_val;
        } else if (!isset($reviewee)) {
          $reviewee = $line_text[$j];
        } else {
          $team_members[] = $line_text[$j];
          $team_size = $team_size + 1;
        }
      }
    }
    // Skip over any lines that are entirely blank
    if (isset($reviewee)) {
      if ($team_size == 0) {
        $ret_val['error'] = 'CSV file at line '. $line_num . ' only lists the person being reviewed: ' . $reviewee;
        return $ret_val;
      }

      // Now that we know data are valid, create & add all possible team pairings
      for ($j = 0; $j < $team_size; $j++) {
        $managed = array();
        $managed[0] = $team_members[$j];
        $managed[1] = $reviewee;
        $managed[2] = 1;
        $ret_val[] = $managed;
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
?>
