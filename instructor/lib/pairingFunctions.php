<?php

function getSurveyTypes($con) {
  $ret_val = array();
  $stmt = $con->prepare('SELECT id, description, file_organization
                         FROM survey_types
                         ORDER BY id');
  $stmt->execute();
  $result = $stmt->get_result();
  while ($row = $result->fetch_array(MYSQLI_NUM)) {
    $key = $row[0];
    $ret_val[$key] = array($row[1], $row[2]);
  }
  $stmt->close();
  return $ret_val;
}

function emitUpdateFileDescriptionFn() {
  echo '<script>function handlePairingChange() {';
  echo 'let selectObject = document.getElementById("pairing-mode");let numLevels = selectObject.value;let formatObject = document.getElementById("fileFormat");let multDiv = document.getElementById("mult_div");';
  echo 'switch(numLevels) {';
  foreach ($_SESSION["surveyTypes"] as $key => $description_and_file_org) {
    echo '  case "' . $key . '": formatObject.innerHTML = "' . $description_and_file_org[1] . '";multDiv.style.display="none";break;';
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

function getPairingResults($con, $pairing_mode, $pm_mult, $file_handle) {
  if ($pairing_mode == 1) {
    return parse_review_pairs($file_handle, $con);
  } else if ($pairing_mode == 2) {
    return parse_review_teams($file_handle, $con);
  } else if ($pairing_mode == 3) {
    return parse_review_managed_teams($file_handle, $pm_mult, $con);
  } else if ($pairing_mode == 4) {
    return parse_review_many_to_one($file_handle, $con);
  } else {
    return null;
  }
}
?>