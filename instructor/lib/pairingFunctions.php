<?php

function getSurveyTypes($con) {
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

function emitUpdateFileDescriptionFn() {
  echo '<script>function handlePairingChange() {';
  echo 'let selectObject = document.getElementById("pairing-mode");let numLevels = selectObject.value;let formatObject = document.getElementById("fileFormat");let multDiv = document.getElementById("mult_div");';
  echo 'switch(numLevels) {';
  foreach ($_SESSION["surveyTypes"] as $key => $details) {
    echo '  case "' . $key . '": formatObject.innerHTML = "' . $details[1] . '";multDiv.style.display=';
    if ($details[2]) {
      echo 'null';
    } else {
      echo '"none"';
    }
    echo ';break;';
  }
  echo '  default: formatObject.innerHTML = "CSV file format needed for the pairing mode shown here";multDiv.style.display="none";break;';
  echo '}}</script>';
}

function emitSurveyTypeSelect($errorMsg, $pairing_mode, $pm_mult) {
  $class = "form-select";
  if (isset($errorMsg["pairing-mode"])) {
    $class = $class." is-invalid";
  }
  echo '<div class="form-floating mb-3 col-ms-auto">';
  echo '<select class="'.$class.'" id="pairing-mode" name="pairing-mode" onload="handlePairingChange();" onchange="handlePairingChange();" required>';
  if (empty($pairing_mode)) {
    echo '<option value="" disabled selected>Select Pairing Mode</option>';
  }
  foreach ($_SESSION["surveyTypes"] as $key => $description_and_file_org) {
    $selected_mode = "";
    if (!empty($pairing_mode) && $pairing_mode == $key) {
      $selected_mode = " selected";
    }
    echo '<option value="'.$key.'"'.$selected_mode.'>'.$description_and_file_org[0].'</option>';
  }
  echo '</select>';
  $message = "Pairing Mode:";
  if (isset($errorMsg["pairing-mode"])) {
    $message = $errorMsg["pairing-mode"];
  }
  echo '<label for="pairing-mode">'.$message.'</label>';
  echo '</div>';
  $style = '';
  if ($pairing_mode != 3) {
    $style = ' style="display:none"';
  }
  echo '<div class="form-floating col-2"'.$style.' id="mult_div">';
  echo '<input type="number" class="form-control" min="1" step="1" id="pm-mult" name="pm-mult" value="'.$pm_mult.'">';
  echo '<label for="pm-mult">PM Eval. Multiplier:</label>';
  echo '</div>';
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
  }
  $roles = calculateRoles($pairings);
  $ret_val = array("pairings" => $pairings, "roles" => $roles);
  return $ret_val;
}
?>