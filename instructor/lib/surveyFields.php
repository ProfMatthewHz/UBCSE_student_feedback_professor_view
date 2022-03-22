<?php
function emitUpdateFileDescriptionFn() {
  echo '<script>function handlePairingChange() {';
  echo 'let selectObject = document.getElementById("pairing-mode");let numLevels = selectObject.value;let formatObject = document.getElementById("fileFormat");';
  echo 'switch(numLevels) {';
  echo '  case "1": formatObject.innerHTML = "One row per review. Each row has 2 column: reviewer email address, reviewee email address";break;';
  echo '  case "2": formatObject.innerHTML = "One row per team. Each row contains the email addresses for all team members. Blank columns are ignored";break;';
  echo '  case "3": formatObject.innerHTML = "One row per team. Each row contains the email addresses for all team members with the manager email address listed last. Blank columns are ignored";break;';
  echo '  case "4": formatObject.innerHTML = "One row per individual being reviewed. Each row contains the email addresses for all of the reviewers. The person being reviewed should be the final email address on the row"; break;';
  echo '  default: formatObject.innerHTML = "CSV file format needed for the pairing mode shown here"; break;';
  echo '}}</script>';
}

function emitSurveyTypeSelect($errorMsg, $pairing_mode) {
  $class = "form-select";
  if (isset($errorMsg["pairing-mode"])) {
    $class = $class." is-invalid";
  }
  echo '<select class="'.$class.'" id="pairing-mode" name="pairing-mode" onload="handlePairingChange();" onchange="handlePairingChange();">';
  $survey_modes = array(1 => "Individual Review",
                        2 => "Team",
                        3 => "Team + Manager",
                        4 => "Many-to-1");
  if (!$pairing_mode) {
    echo '<option value="-1" disabled selected">Select Pairing Mode</option>';
  }
  foreach ($survey_modes as $value => $name) {
    $selected_mode = "";
    if ($pairing_mode == $value) {
      $selected_mode = " selected";
    }
    echo '<option value="'.$value.'" "'.$selected_mode.'>'.$name.'</option>';
  }
  echo '</select>';
  $message = "Pairing Mode:";
  if (isset($errorMsg["pairing-mode"])) {
    $message = $errorMsg["pairing-mode"];
  }
  echo '<label for="pairing-mode">'.$message.'</label>';
}
?>