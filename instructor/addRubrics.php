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
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous"></script>
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
          <label for="course-code">Rubric Name:</label>
        </div>
        <div class="form-floating mb-3">
          <input type="number" min="3" max="5" id="rubric-levels" class="form-control <?php if(isset($errorMsg["rubric-levels"])) {echo "is-invalid ";} ?>" name="rubric-levels" required value="<?php if (isset($rubric_levels)) {echo htmlspecialchars($rubric_levels);} ?>"></input>
          <label for="rubric-levels">Rubric Levels:</label>
        </div>
        <div class="container-fluid">
          <div class="row justify-content-center">
            <div class="col-sm-8">
              <div class="form-floating mb-3">
                <input id="level1-name" type="text" class="form-control <?php if(isset($errorMsg["level1-name"])) {echo "is-invalid ";} ?>" name="level1-name" required value="<?php if (isset($level1_name)) {echo htmlspecialchars($level1_name);} ?>"></input>
                <label for="level1-name">Name for Best Level:</label>
              </div>
            </div class="col-sm-2 offset-sm-2">
              <div class="form-floating ml-5 mb-3">
                <input id="level1-value" type="number" class="form-control <?php if(isset($errorMsg["level1-value"])) {echo "is-invalid ";} ?>" name="level1-value" required value="<?php if (isset($level1_value)) {echo htmlspecialchars($level1_value);} else { echo '4'; } ?>"></input>
                <label for="level1-value">Points for Best Level:</label>
              </div>
            </div>
          </div>
        </div>
        <div class="input-group mb-3" id="rubric-level2">
          <div class="form-floating mb-3">
            <input id="level2-name" type="text" class="form-control <?php if(isset($errorMsg["level2-name"])) {echo "is-invalid ";} ?>" name="level2-name" required value="<?php if (isset($level2_name)) {echo htmlspecialchars($level2_name);} ?>"></input>
            <label for="level2-name">Name for 2nd Best Level:</label>
          </div>
          <div class="form-floating ml-5 mb-3">
            <input id="level2-value" type="number" class="form-control <?php if(isset($errorMsg["level2-value"])) {echo "is-invalid ";} ?>" name="level1-value" required value="<?php if (isset($level2_value)) {echo htmlspecialchars($level2_value);} else { echo '3'; } ?>"></input>
            <label for="level2-value">Points for 2nd Best Level:</label>
          </div>
        </div>
        <div class="input-group mb-3" id="rubric-level3">
          <div class="form-floating mb-3">
            <input id="level3-name" type="text" class="form-control <?php if(isset($errorMsg["level3-name"])) {echo "is-invalid ";} ?>" name="level3-name" required value="<?php if (isset($level3_name)) {echo htmlspecialchars($level3_name);} ?>"></input>
            <label for="level3-name">Name for 2nd Best Level:</label>
          </div>
          <div class="form-floating ml-5 mb-3">
            <input id="level3-value" type="number" class="form-control <?php if(isset($errorMsg["level3-value"])) {echo "is-invalid ";} ?>" name="level1-value" required value="<?php if (isset($level3_value)) {echo htmlspecialchars($level3_value);} else { echo '2'; } ?>"></input>
            <label for="level3-value">Points for 2nd Best Level:</label>
          </div>
        </div>
        <div class="input-group mb-3" id="rubric-level4">
          <div class="form-floating mb-3">
            <input id="level4-name" type="text" class="form-control <?php if(isset($errorMsg["level4-name"])) {echo "is-invalid ";} ?>" name="level4-name" required value="<?php if (isset($level4_name)) {echo htmlspecialchars($level4_name);} ?>"></input>
            <label for="level4-name">Name for 2nd Word Level:</label>
          </div>
          <div class="form-floating ml-5 mb-3">
            <input id="level4-value" type="number" class="form-control <?php if(isset($errorMsg["level4-value"])) {echo "is-invalid ";} ?>" name="level4-value" required value="<?php if (isset($level4_value)) {echo htmlspecialchars($level4_value);} else { echo '1'; } ?>"></input>
            <label for="level4-value">Points for 2nd Worst Level:</label>
          </div>
        </div>
        <div class="input-group mb-3" id="rubric-level5">
          <div class="form-floating mb-3">
            <input id="level5-name" type="text" class="form-control <?php if(isset($errorMsg["level5-name"])) {echo "is-invalid ";} ?>" name="level5-name" required value="<?php if (isset($level5_name)) {echo htmlspecialchars($level5_name);} ?>"></input>
            <label for="level5-name">Name for Worst Level:</label>
          </div>
          <div class="form-floating ml-5 mb-3">
            <input id="level5-value" type="number" class="form-control <?php if(isset($errorMsg["level5-value"])) {echo "is-invalid ";} ?>" name="level1-value" required value="<?php if (isset($level5_value)) {echo htmlspecialchars($level5_value);} else { echo '0'; } ?>"></input>
            <label for="level5-value">Points for Worst Level:</label>
          </div>
        </div>

        <input type="hidden" name="csrf-token" value="<?php echo $instructor->csrf_token; ?>" />

        <input class="btn btn-success" type="submit" value="Create Rubric" />
      </div>
</form>
</div>
          </main>
</body>
</html>