<?php

function get_data_posted(&$data_array, $level, $suffix) {
  $key = $level.$suffix;
  if (isset($_POST[$key])) {
    $data_array[$level] = trim($_POST[$key]);
  } else {
    $data_array[$level] = "";
  }
}

function check_level($names, $values, $level, &$errorMsg) {
  // First check the level's name
  if (empty($names[$level])) {
    $errorMsg[$level."-name"] = "Level MUST have a name";
  } else {
    foreach ($names as $l_name => $name) {
      if ( ($l_name != $level) && ($names[$l_name] == $names[$level]) ) {
        $errorMsg[$level."-name"] = "Each level must have a UNIQUE name";
      }
    }
  }
  // Then check the level's value
  if (!ctype_digit($values[$level])) {
    $errorMsg[$level."-value"] = "Value MUST be a number";
  }
}

function check_value_monotonic($level_values, &$errorMsg) {
  $prev_level = PHP_INT_MIN;
  foreach ($level_values as $level => $value) {
    if (!key_exists($level, $errorMsg)) {
      $val = intval($value);
      if ($val < $prev_level) {
        $errorMsg[$level."-value"] = "Lower level CANNOT have higher value";
      }
      $prev_level = $val;
    }
  }
}

//error logging
error_reporting(-1); // reports all errors
ini_set("display_errors", "1"); // shows all errors
ini_set("log_errors", 1);
ini_set("error_log", "~/php-error.log");

//start the session variable
session_start();

//bring in required code
require_once "../lib/database.php";
require_once "../lib/constants.php";
require_once "lib/instructorQueries.php";
require_once "lib/rubricQueries.php";

//query information about the requester
$con = connectToDatabase();

//try to get information about the instructor who made this request by checking the session token and redirecting if invalid
if (!isset($_SESSION['id'])) {
  http_response_code(403);
  echo "Forbidden: You must be logged in to access this page.";
  exit();
}
$instructor_id = $_SESSION['id'];

//stores error messages corresponding to form fields
$errorMsg = array();
// Stores data we will be relying upon later
$level_names = array();
$level_values = array();

if($_SERVER['REQUEST_METHOD'] == 'POST') {
  // make sure values exist
  if (!isset($_POST['rubric-name']) || !isset($_POST['rubric-level']) || 
      !isset($_POST['level1-name']) || !isset($_POST['level1-value']) ||
      !isset($_POST['level5-name']) || !isset($_POST['level5-value'])) {
    http_response_code(400);
    echo "Bad Request: Missing parmeters.";
    exit();
  }

  // check CSRF token
  $csrf_token = getCSRFToken($con, $instructor_id);
  if (!hash_equals($csrf_token, $_POST['csrf-token'])) {
    http_response_code(403);
    echo "Forbidden: Incorrect parameters.";
    exit();
  }

  // Get all the data that was posted
  $rubric_name = trim($_POST['rubric-name']);
  $rubric_level = $_POST['rubric-level'];

  // Check the level was legal
  if ($rubric_level != '3' && $rubric_level != '4' && $rubric_level != '5') {
    http_response_code(400);
    echo "Bad Request: Missing parmeters.";
    exit();
  }

  // Levels are processed lowest-to-highest (reverse name order) for consistency in presentation
  get_data_posted($level_names, "level5", "-name");
  get_data_posted($level_values, "level5", "-value");
  if ( ($rubric_level == '4') || ($rubric_level == '5') ) {
    get_data_posted($level_names, "level4", "-name");
    get_data_posted($level_values, "level4", "-value");
  }
  if ( ($rubric_level == '3') || ($rubric_level == '5') ) {
    get_data_posted($level_names, "level3", "-name");
    get_data_posted($level_values, "level3", "-value");
  }
  if ( ($rubric_level == '4') || ($rubric_level == '5') ) {
    get_data_posted($level_names, "level2", "-name");
    get_data_posted($level_values, "level2", "-value");
  }
  get_data_posted($level_names, "level1", "-name");
  get_data_posted($level_values, "level1", "-value");


  // Check that the names & values that are used are valid
  check_level($level_names, $level_values, "level1", $errorMsg);
  check_level($level_names, $level_values, "level5", $errorMsg);

  if ( ($rubric_level == '3') || ($rubric_level == '5') ) {
    check_level($level_names, $level_values, "level3", $errorMsg);
  }
  if ( ($rubric_level == '4') || ($rubric_level == '5') ) {
    check_level($level_names, $level_values, "level2", $errorMsg);
    check_level($level_names, $level_values, "level4", $errorMsg);
  }
  check_value_monotonic($level_values, $errorMsg);
  // Finally, verify that this is a unique rubric name
  if (empty($rubric_name)) {
    $errorMsg['rubric-name'] = "Rubric MUST have a name";
  } else {
    $rubric_id = getIdFromDescription($con, $rubric_name);
    if (!empty($rubric_id)) {
      $errorMsg['rubric-name'] = "Rubric with that name already exists";
    }
  }
  if (count($errorMsg) == 0) {
    // Set the session variables so the data carries to the criterion page
    $_SESSION["rubric"] = array("name" => $rubric_name);
    $_SESSION["rubric"]["levels"] = array("names" => $level_names, "values" => $level_values);
    http_response_code(302);
    header("Location: ".INSTRUCTOR_HOME."rubricCriteriaAdd.php");
    exit();
  }
} else if (isset($_SESSION["rubric"])) {
  // This is a GET request, but we have a rubric in the session which means we are modifying an existing rubric
  $rubric_name = $_SESSION["rubric"]["name"]." copy";
  $rubric_level = count($_SESSION["rubric"]["levels"]["values"]);
  $level_values = $_SESSION["rubric"]["levels"]["values"];
  $level_names = $_SESSION["rubric"]["levels"]["names"];
}
unset($_SESSION["rubric"]);
$csrf_token = createCSRFToken($con, $instructor_id);
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous"></script>
  <script>
  function makeLevelHidden(levelNum) {
    let regionElem = document.getElementById("rubric-level" + levelNum);
    let nameElem = document.getElementById("level" + levelNum + "-name");
    let valueElem = document.getElementById("level" + levelNum + "-value");
    regionElem.style.display = 'none';
    nameElem.toggleAttribute('required', false);
    valueElem.toggleAttribute('required', false);
  } 
function makeLevelVisible(levelNum) {
    let regionElem = document.getElementById("rubric-level" + levelNum);
    let nameElem = document.getElementById("level" + levelNum + "-name");
    let valueElem = document.getElementById("level" + levelNum + "-value");
    regionElem.style.display = 'block';
    nameElem.toggleAttribute('required', true);
    valueElem.toggleAttribute('required', true)
  } 
  function handleLevelChange() {
    let selectObject = document.getElementById("rubric-level");
    let numLevels = selectObject.value;
    if (numLevels == "3") {
      makeLevelHidden("2");
      makeLevelHidden("4");
      makeLevelVisible("3");
    } else if (numLevels == "4") {
      makeLevelVisible("2");
      makeLevelVisible("4");
      makeLevelHidden("3");
    } else {
      makeLevelVisible("2");
      makeLevelVisible("4");
      makeLevelVisible("3");
    }
  }
  </script>
  <title>CSE Evaluation Survey System - Add Rubric</title>
</head>
<body class="text-center" onload="handleLevelChange()">
<!-- Header -->
<main>
  <div class="container-fluid">
    <div class="row justify-content-md-center bg-primary mt-1 mx-1 rounded-pill">
      <div class="col-sm-auto text-center">
        <h4 class="text-white display-1">UB CSE Evalution System<br>Create New Rubric</h4>
      </div>
    </div>
    <form class="mt-5 mx-4" id="add-rubric" method="post">
      <div class="form-inline justify-content-center align-items-center">
        <div class="form-floating mb-3">
          <input type="text" id="rubric-name" class="form-control <?php if(isset($errorMsg["rubric-name"])) {echo "is-invalid ";} ?>" name="rubric-name" required value="<?php if (isset($rubric_name)) {echo htmlspecialchars($rubric_name);} ?>"></input>
          <label for="rubric-name"><?php if(isset($errorMsg["rubric-name"])) {echo $errorMsg["rubric-name"]; } else { echo "Rubric Name:";} ?></label>
        </div>

        <div class="form-floating ms-1 mb-3">
          <select class="form-select <?php if(isset($errorMsg["rubric-level"])) {echo "is-invalid ";} ?>" id="rubric-level" name="rubric-level" required onchange="handleLevelChange();">
            <option value="3" <?php if (isset($rubric_level) && $rubric_level == 3) {echo 'selected';} ?>>Lowest-Middle-Highest</option>
            <option value="4" <?php if (isset($rubric_level) && $rubric_level == 4) {echo 'selected';} ?>>Lowest-Low-High-Highest</option>
            <option value="5" <?php if (isset($rubric_level) && $rubric_level == 5) {echo 'selected';} ?>>Lowest-Low-Middle-High-Highest</option>
          </select>
          <label for="rubric-level">Types of Levels Rubric Uses:</label>
        </div>

        <div id="rubric-level5" class="border-top border-bottom">
          <div class="row mx-1">
            <div class="col text-start align-top">
              <span style="font-size:small;color:DarkGrey">Lowest level:</span>
            </div>
          </div>
          <div class="row mx-1">
            <div class="col-sm-8">
              <div class="form-floating mt-1 mb-3">
                <input id="level5-name" type="text" class="form-control <?php if(isset($errorMsg["level5-name"])) {echo "is-invalid ";} ?>" name="level5-name" required value="<?php if (key_exists('level5', $level_names)) {echo htmlspecialchars($level_names['level5']);} ?>"></input>
                <label for="level5-name"><?php if(isset($errorMsg["level5-name"])) {echo $errorMsg["level5-name"]; } else { echo "Name:";} ?></label>
              </div>
            </div>
            <div class="col-sm-4 gl-3">
              <div class="form-floating mt-1 mb-3">
                <input id="level5-value" type="number" class="form-control <?php if(isset($errorMsg["level5-value"])) {echo "is-invalid ";} ?>" name="level5-value" required value="<?php if (key_exists('level5', $level_values)) {echo htmlspecialchars($level_values['level5']);} else { echo '0'; } ?>"></input>
                <label for="level5-value"><?php if(isset($errorMsg["level5-value"])) {echo $errorMsg["level5-value"]; } else { echo "Points:";} ?></label>
              </div>
            </div>
          </div>
        </div>
      </div>

        <div id="rubric-level4" class="border-top border-bottom">
          <div class="row mx-1">
            <div class="col text-start align-top">
              <span style="font-size:small;color:DarkGrey">Low level:</span>
            </div>
          </div>
          <div class="row mx-1">
            <div class="col-sm-8">
              <div class="form-floating mt-1 mb-3">
                <input id="level4-name" type="text" class="form-control <?php if(isset($errorMsg["level4-name"])) {echo "is-invalid ";} ?>" name="level4-name" required value="<?php if (key_exists('level4', $level_names)) {echo htmlspecialchars($level_names['level4']);} ?>"></input>
                <label for="level4-name"><?php if(isset($errorMsg["level4-name"])) {echo $errorMsg["level4-name"]; } else { echo "Name:";} ?></label>
              </div>
            </div>
            <div class="col-sm-4 gl-3">
              <div class="form-floating mt-1 mb-3">
                <input id="level4-value" type="number" class="form-control <?php if(isset($errorMsg["level4-value"])) {echo "is-invalid ";} ?>" name="level4-value" required value="<?php if (key_exists('level4', $level_values)) {echo htmlspecialchars($level_values['level4']);} else { echo '1'; } ?>"></input>
                <label for="level4-value"><?php if(isset($errorMsg["level4-value"])) {echo $errorMsg["level4-value"]; } else { echo "Points:";} ?></label>
              </div>
            </div>
          </div>
        </div>

        <div id="rubric-level3" class="border-top border-bottom">
          <div class="row mx-1">
            <div class="col text-start align-top">
              <span style="font-size:small;color:DarkGrey">Middle level:</span>
            </div>
          </div>
          <div class="row mx-1">
            <div class="col-sm-8">
              <div class="form-floating mt-1 mb-3">
                <input id="level3-name" type="text" class="form-control <?php if(isset($errorMsg["level3-name"])) {echo "is-invalid ";} ?>" name="level3-name" required value="<?php if (key_exists('level3', $level_names)) {echo htmlspecialchars($level_names['level3']);} ?>"></input>
                <label for="level3-name"><?php if(isset($errorMsg["level3-name"])) {echo $errorMsg["level3-name"]; } else { echo "Name:";} ?></label>
              </div>
            </div>
            <div class="col-sm-4 gl-3">
              <div class="form-floating mt-1 mb-3">
                <input id="level3-value" type="number" class="form-control <?php if(isset($errorMsg["level3-value"])) {echo "is-invalid ";} ?>" name="level3-value" required value="<?php if (key_exists('level3', $level_values)) {echo htmlspecialchars($level_values['level3']);} else { echo '2'; } ?>"></input>
                <label for="level3-value"><?php if(isset($errorMsg["level3-value"])) {echo $errorMsg["level3-value"]; } else { echo "Points:";} ?></label>
              </div>
            </div>
          </div>
        </div>

        <div id="rubric-level2" class="border-top border-bottom">
          <div class="row mx-1">
            <div class="col text-start align-top">
              <span style="font-size:small;color:DarkGrey">High level:</span>
            </div>
          </div>
          <div class="row mx-1">
            <div class="col-sm-8">
              <div class="form-floating mt-1 mb-3">
                <input id="level2-name" type="text" class="form-control <?php if(isset($errorMsg["level2-name"])) {echo "is-invalid ";} ?>" name="level2-name" required value="<?php if (key_exists('level2', $level_names)) {echo htmlspecialchars($level_names['level2']);} ?>"></input>
                <label for="level2-name"><?php if(isset($errorMsg["level2-name"])) {echo $errorMsg["level2-name"]; } else { echo "Name:";} ?></label>
              </div>
            </div>
            <div class="col-sm-4 gl-3">
              <div class="form-floating mt-1 mb-3">
                <input id="level2-value" type="number" class="form-control <?php if(isset($errorMsg["level2-value"])) {echo "is-invalid ";} ?>" name="level2-value" required value="<?php if (key_exists('level2', $level_values)) {echo htmlspecialchars($level_values['level2']);} else { echo '3'; } ?>"></input>
                <label for="level2-value"><?php if(isset($errorMsg["level2-value"])) {echo $errorMsg["level2-value"]; } else { echo "Points:";} ?></label>
              </div>
            </div>
          </div>
        </div>

        <div id="rubric-level1" class="border-top border-bottom">
          <div class="row mx-1">
            <div class="col text-start align-top">
              <span style="font-size:small;color:DarkGrey">Highest level:</span>
            </div>
          </div>
          <div class="row mx-1">
            <div class="col-sm-8">
              <div class="form-floating mt-1 mb-3">
                <input id="level1-name" type="text" class="form-control <?php if(isset($errorMsg["level1-name"])) {echo "is-invalid ";} ?>" name="level1-name" required value="<?php if (key_exists('level1', $level_names)) {echo htmlspecialchars($level_names['level1']);} ?>"></input>
                <label for="level1-name"><?php if(isset($errorMsg["level1-name"])) {echo $errorMsg["level1-name"]; } else { echo "Name:";} ?></label>
              </div>
            </div>
            <div class="col-sm-4 gl-3">
              <div class="form-floating mt-1 mb-3">
                <input id="level1-value" type="number" class="form-control <?php if(isset($errorMsg["level1-value"])) {echo "is-invalid ";} ?>" name="level1-value" required value="<?php if (key_exists('level1', $level_values)) {echo htmlspecialchars($level_values['level1']);} else { echo '4'; } ?>"></input>
                <label for="level1-value"><?php if(isset($errorMsg["level1-value"])) {echo $errorMsg["level1-value"]; } else { echo "Points:";} ?></label>
              </div>
            </div>
          </div>
        </div>

      <input type="hidden" name="csrf-token" value="<?php echo $csrf_token; ?>"></input>
      <div class="row mx-1 mt-2 justify-content-center">
      <div class="col-auto">
      <input class="btn btn-success" type="submit" value="Define Criteria"></input>
</div></div>
    </form>
    <hr>
    <div class="row mx-1 mt-2 justify-content-center">
        <div class="col-auto">
					<a href="surveys.php" class="btn btn-outline-info" role="button" aria-disabled="false">Return to Instructor Home</a>
        </div>
      </div>
</div>
</main>
</body>
</html>