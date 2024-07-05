<?php
function getSurveyTypes($con, $instructor_id) {
  $ret_val = array();
  $stmt = $con->prepare('SELECT id, description, file_organization, display_multiplier
                         FROM survey_types
                         ORDER BY id');
  $stmt->execute();
  $result = $stmt->get_result();
  while ($row = $result->fetch_array(MYSQLI_NUM)) {
    $key = $row[0];
    $ret_val[$key] = array($row[1], $row[2], $row[3]);
  }
  $stmt->close();
  return $ret_val;
}

function calculateRoles($pairings) {
  $ret_val = array();
  foreach ($pairings as $pair) {
    $reviewer = $pair[0];
    if (array_key_exists($reviewer, $ret_val)) {
      $ret_val[$reviewer][0] = true;
    } else {  
      $ret_val[$reviewer] = array(true, false);
    }
    $reviewed = $pair[1];
    if (array_key_exists($reviewed, $ret_val)) {
      $ret_val[$reviewed][1] = true;
    } else {  
      $ret_val[$reviewed] = array(false, true);
    }
  }
  return $ret_val;
}

function processReviewRows($rows, $student_data, $pm_mult, $pairing_mode) {
  $pairings = null;
  if ($pairing_mode == 1) {
    $pairings = parse_review_pairs($rows, $student_data);
  } else if ($pairing_mode == 2) {
    $pairings = parse_review_teams($rows, $student_data);
  } else if ($pairing_mode == 3) {
    $pairings = parse_managed_teams($rows, $student_data, $pm_mult);
  } else if ($pairing_mode == 4) {
    $pairings = parse_manager_review($rows, $student_data);
  } else if ($pairing_mode == 5){
    $pairings = parse_manager_review($rows, $student_data);
  }
  $roles = calculateRoles($pairings);
  $ret_val = array("pairings" => $pairings, "roles" => $roles);
  return $ret_val;
}
?>