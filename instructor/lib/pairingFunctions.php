<?php
function emitUpdateFileDescriptionFn() {
  echo '<script>function handlePairingChange() {';
  echo 'let selectObject = document.getElementById("pairing-mode");let numLevels = selectObject.value;let formatObject = document.getElementById("fileFormat");let multDiv = document.getElementById("mult_div");';
  echo 'switch(numLevels) {';
  echo '  case "1": formatObject.innerHTML = "One row per review. Each row has 2 column: reviewer email address, reviewee email address";multDiv.style.display="none";break;';
  echo '  case "2": formatObject.innerHTML = "One row per team. Each row contains the email addresses for all team members. Blank columns are ignored";multDiv.style.display="none";break;';
  echo '  case "3": formatObject.innerHTML = "One row per team. Each row contains the email addresses for all team members with the manager email address listed last. Blank columns are ignored";multDiv.style.display=null;break;';
  echo '  case "4": formatObject.innerHTML = "One row per individual being reviewed. Each row contains the email addresses for all of the reviewers AND must contain the person being reviewed as the final email address on the row";multDiv.style.display="none";break;';
  echo '  default: formatObject.innerHTML = "CSV file format needed for the pairing mode shown here";multDiv.style.display="none";break;';
  echo '}}</script>';
}

function emitSurveyTypeSelect($errorMsg, $pairing_mode, $pm_mult) {
  $class = "form-select";
  if (isset($errorMsg["pairing-mode"])) {
    $class = $class." is-invalid";
  }
  echo '<div class="form-floating mb-3 col-ms-auto">';
  echo '<select class="'.$class.'" id="pairing-mode" name="pairing-mode" onload="handlePairingChange();" onchange="handlePairingChange();">';
  $survey_modes = array(1 => "Individual Reviewed by Individual",
                        2 => "Team Reviewed by Team",
                        3 => "Team Reviewed by Team + 1",
                        4 => "1 Reviewed by Team");
  if (empty($pairing_mode)) {
    echo '<option value="-1" disabled selected>Select Pairing Mode</option>';
  }
  foreach ($survey_modes as $value => $name) {
    $selected_mode = "";
    if (!empty($pairing_mode) && $pairing_mode == $value) {
      $selected_mode = " selected";
    }
    echo '<option value="'.$value.'"'.$selected_mode.'>'.$name.'</option>';
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
  if ($pairing_mode == '1') {
    return parse_review_pairs($file_handle, $con);
  } else if ($pairing_mode == '2') {
    return parse_review_teams($file_handle, $con);
  } else if ($pairing_mode == '3') {
    return parse_review_managed_teams($file_handle, $pm_mult, $con);
  } else if ($pairing_mode == '4') {
    return parse_review_many_to_one($file_handle, $con);
  } else {
    return null;
  }
}
?>