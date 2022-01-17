<?php

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
require_once "../lib/infoClasses.php";
require_once "../lib/fileParse.php";

//query information about the requester
$con = connectToDatabase();

//try to get information about the instructor who made this request by checking the session token and redirecting if invalid
$instructor = new InstructorInfo();
$instructor->check_session($con, 0);

//stores error messages corresponding to form fields
$errorMsg = array();

if($_SERVER['REQUEST_METHOD'] == 'POST') {

  // make sure values exist
  if (!isset($_POST['rubric-name']) || !isset($_POST['rubric-levels'])) {
    http_response_code(400);
    echo "Bad Request: Missing parmeters.";
    exit();
  }
}
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous"></script>
  <script>
  function handleLevelChange() {
    let selectObject = document.getElementById("rubric-level");
    let numLevels = selectObject.value;
    if (numLevels == "3") {
      let highObject = document.getElementById("rubric-level2");
      highObject.style.display = 'none';
      let lowObject = document.getElementById("rubric-level4");
      lowObject.style.display = 'none';
      let midObject = document.getElementById("rubric-level3");
      midObject.style.display = 'block';
    } else if (numLevels == "4") {
      let highObject = document.getElementById("rubric-level2");
      highObject.style.display = 'block';
      let lowObject = document.getElementById("rubric-level4");
      lowObject.style.display = 'block';
      let midObject = document.getElementById("rubric-level3");
      midObject.style.display = 'none';
    } else {
      let highObject = document.getElementById("rubric-level2");
      highObject.style.display = 'block';
      let lowObject = document.getElementById("rubric-level4");
      lowObject.style.display = 'block';
      let midObject = document.getElementById("rubric-level3");
      midObject.style.display = 'block';     
    }
  }
  </script>
  <title>CSE Evaluation Survey System - Add Rubric</title>
</head>
<body class="text-center">
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
          <label for="rubric-name">Rubric Name:</label>
        </div>

        <div class="form-floating ms-1 mb-3">
          <select class="form-select" id="rubric-level" name="rubric-level" required onchange="handleLevelChange();">
            <option value="-1" disabled <?php if (!isset($rubric_level)) {echo 'selected';} ?>>Levels Used</option>
            <option value="3" <?php if ($isset($rubric_level) && $rubric_level == 3) {echo 'selected';} ?>>Highest-Middle-Lowest</option>
            <option value="4" <?php if ($isset($rubric_level) && $rubric_level == 4) {echo 'selected';} ?>>Highest-High-Low-Lowest</option>
            <option value="5" <?php if ($isset($rubric_level) && $rubric_level == 5) {echo 'selected';} ?>>Highest-High-Middle-Low-Lowest</option>
          </select>
          <label for="rubric-level"><?php if(isset($errorMsg["rubric-level"])) {echo $errorMsg["rubric-level"]; } else { echo "Types of Levels Rubric Uses:";} ?></label>
        </div>

        <div id="rubric-level1" class="border-top border-bottom ms-1 mb-3">
          <div class="row mx-1">
            <div class="col text-start align-top">
              <span style="font-size:small;color:DarkGrey">Highest level:</span>
            </div>
          </div>
          <div class="row mx-1">
            <div class="col-sm-8">
              <div class="form-floating mt-1 mb-3">
                <input id="level1-name" type="text" class="form-control <?php if(isset($errorMsg["level1-name"])) {echo "is-invalid ";} ?>" name="level1-name" required value="<?php if (isset($level1_name)) {echo htmlspecialchars($level1_name);} ?>"></input>
                <label for="level1-name">Name:</label>
              </div>
            </div>
            <div class="col-sm-4 gl-3">
              <div class="form-floating mt-1 mb-3">
                <input id="level1-value" type="number" class="form-control <?php if(isset($errorMsg["level1-value"])) {echo "is-invalid ";} ?>" name="level1-value" required value="<?php if (isset($level1_value)) {echo htmlspecialchars($level1_value);} else { echo '4'; } ?>"></input>
                <label for="level1-value">Points:</label>
              </div>
            </div>
          </div>
        </div>

        <div id="rubric-level2" class="border-top border-bottom ms-1 mb-3">
          <div class="row mx-1">
            <div class="col text-start align-top">
              <span style="font-size:small;color:DarkGrey">High level:</span>
            </div>
          </div>
          <div class="row mx-1">
            <div class="col-sm-8">
              <div class="form-floating mt-1 mb-3">
                <input id="level2-name" type="text" class="form-control <?php if(isset($errorMsg["level2-name"])) {echo "is-invalid ";} ?>" name="level2-name" required value="<?php if (isset($level1_name)) {echo htmlspecialchars($level1_name);} ?>"></input>
                <label for="level2-name">Name:</label>
              </div>
            </div>
            <div class="col-sm-4 gl-3">
              <div class="form-floating mt-1 mb-3">
                <input id="level2-value" type="number" class="form-control <?php if(isset($errorMsg["level2-value"])) {echo "is-invalid ";} ?>" name="level2-value" required value="<?php if (isset($level1_value)) {echo htmlspecialchars($level1_value);} else { echo '3'; } ?>"></input>
                <label for="level2-value">Points:</label>
              </div>
            </div>
          </div>
        </div>

        <div id="rubric-level3" class="border-top border-bottom ms-1 mb-3">
          <div class="row mx-1">
            <div class="col text-start align-top">
              <span style="font-size:small;color:DarkGrey">Middle level:</span>
            </div>
          </div>
          <div class="row mx-1">
            <div class="col-sm-8">
              <div class="form-floating mt-1 mb-3">
                <input id="level3-name" type="text" class="form-control <?php if(isset($errorMsg["level3-name"])) {echo "is-invalid ";} ?>" name="level3-name" required value="<?php if (isset($level1_name)) {echo htmlspecialchars($level1_name);} ?>"></input>
                <label for="level3-name">Name:</label>
              </div>
            </div>
            <div class="col-sm-4 gl-3">
              <div class="form-floating mt-1 mb-3">
                <input id="level3-value" type="number" class="form-control <?php if(isset($errorMsg["level3-value"])) {echo "is-invalid ";} ?>" name="level3-value" required value="<?php if (isset($level1_value)) {echo htmlspecialchars($level1_value);} else { echo '2'; } ?>"></input>
                <label for="level3-value">Points:</label>
              </div>
            </div>
          </div>
        </div>

        <div id="rubric-level4" class="border-top border-bottom ms-1 mb-3">
          <div class="row mx-1">
            <div class="col text-start align-top">
              <span style="font-size:small;color:DarkGrey">Low level:</span>
            </div>
          </div>
          <div class="row mx-1">
            <div class="col-sm-8">
              <div class="form-floating mt-1 mb-3">
                <input id="level4-name" type="text" class="form-control <?php if(isset($errorMsg["level4-name"])) {echo "is-invalid ";} ?>" name="level4-name" required value="<?php if (isset($level1_name)) {echo htmlspecialchars($level1_name);} ?>"></input>
                <label for="level4-name">Name :</label>
              </div>
            </div>
            <div class="col-sm-4 gl-3">
              <div class="form-floating mt-1 mb-3">
                <input id="level4-value" type="number" class="form-control <?php if(isset($errorMsg["level4-value"])) {echo "is-invalid ";} ?>" name="level4-value" required value="<?php if (isset($level1_value)) {echo htmlspecialchars($level1_value);} else { echo '1'; } ?>"></input>
                <label for="level4-value">Points:</label>
              </div>
            </div>
          </div>
        </div>

        <div id="rubric-level2" class="border-top border-bottom ms-1 mb-3">
          <div class="row mx-1">
            <div class="col text-start align-top">
              <span style="font-size:small;color:DarkGrey">Lowest level:</span>
            </div>
          </div>
          <div class="row mx-1">
            <div class="col-sm-8">
              <div class="form-floating mt-1 mb-3">
                <input id="level5-name" type="text" class="form-control <?php if(isset($errorMsg["level5-name"])) {echo "is-invalid ";} ?>" name="level5-name" required value="<?php if (isset($level1_name)) {echo htmlspecialchars($level1_name);} ?>"></input>
                <label for="level5-name">Name:</label>
              </div>
            </div>
            <div class="col-sm-4 gl-3">
              <div class="form-floating mt-1 mb-3">
                <input id="level5-value" type="number" class="form-control <?php if(isset($errorMsg["level5-value"])) {echo "is-invalid ";} ?>" name="level5-value" required value="<?php if (isset($level1_value)) {echo htmlspecialchars($level1_value);} else { echo '0'; } ?>"></input>
                <label for="level5-value">Points:</label>
              </div>
            </div>
          </div>
        </div>
      </div>

      <input type="hidden" name="csrf-token" value="<?php echo $instructor->csrf_token; ?>" />

      <input class="btn btn-success" type="submit" value="Define Criteria" />
    </form>
</div>
</main>
</body>
</html>
