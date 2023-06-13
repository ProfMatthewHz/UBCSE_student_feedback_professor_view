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
require_once "lib/rubricQueries.php";
require_once "lib/rubricTable.php";

//query information about the requester
$con = connectToDatabase();

//try to get information about the instructor who made this request by checking the session token and redirecting if invalid
if (!isset($_SESSION['id'])) {
  http_response_code(403);
  echo "Forbidden: You must be logged in to access this page.";
  exit();
}
$instructor_id = $_SESSION['id'];

// Verify we have already defined the rubric in total
if (!isset($_SESSION["rubric"])) {
  http_response_code(302);
  header("Location: ".INSTRUCTOR_HOME."rubricAdd.php");
  exit();
}
if (!isset($_SESSION["confirm"])) {
  http_response_code(302);
  header("Location: ".INSTRUCTOR_HOME."rubricCriteriaAdd.php");
  exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  if (isset($_POST["revise-rubric"])) {
    http_response_code(302);
    header("Location: ".INSTRUCTOR_HOME."rubricCriteriaAdd.php");
    exit();
  } else {
    // Add the rubric to the database and keep track of the id it was assigned. 
    $rubric_id = insertRubric($con, $_SESSION["rubric"]["name"]);


    // Now add the different scores/levels and keep track of each of them for later use
    $levels = count($_SESSION["rubric"]["levels"]["names"]);
    $level_ids = array();
    $level_ids["level1"] = insertRubricScore($con, $rubric_id, $_SESSION["rubric"]["levels"]["names"]["level1"], $_SESSION["rubric"]["levels"]["values"]["level1"]);
    if ($levels == 4 || $levels == 5) {
      $level_ids["level2"] = insertRubricScore($con, $rubric_id, $_SESSION["rubric"]["levels"]["names"]["level2"], $_SESSION["rubric"]["levels"]["values"]["level2"]);
    }
    if ($levels == 3 || $levels == 5) {
      $level_ids["level3"] = insertRubricScore($con, $rubric_id, $_SESSION["rubric"]["levels"]["names"]["level3"], $_SESSION["rubric"]["levels"]["values"]["level3"]);
    }
    if ($levels == 4 || $levels == 5) {
      $level_ids["level4"] = insertRubricScore($con, $rubric_id, $_SESSION["rubric"]["levels"]["names"]["level4"], $_SESSION["rubric"]["levels"]["values"]["level4"]);
    }
    $level_ids["level5"] = insertRubricScore($con, $rubric_id, $_SESSION["rubric"]["levels"]["names"]["level5"], $_SESSION["rubric"]["levels"]["values"]["level5"]);

    // Finally we insert the name of each criterion as a rubric topic and, if appropriate, all of its responses.
    foreach ($_SESSION["confirm"]["topics"] as $crit_data) {
      $topic_id = insertRubricTopic($con, $rubric_id, $crit_data["question"], $crit_data["type"]);
      if ($crit_data["type"] == MC_QUESTION_TYPE) {
        foreach ($level_ids as $key => $level_id) {
          insertRubricReponse($con, $topic_id, $level_id, $crit_data["responses"][$key]);
        }
      }
    }
    // And go back to the main page.
    unset($_SESSION["rubric"]);
    unset($_SESSION["confirm"]);
    http_response_code(302);
    header("Location: ".INSTRUCTOR_HOME."surveys.php");
    exit();
  }
}
?>
<!DOCTYPE HTML>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous"></script>
  <title>CSE Evaluation Survey System - Confirm Rubric</title>
</head>
<body class="text-center">
<!-- Header -->
<main>
  <div class="container-fluid">
    <div class="row justify-content-md-center bg-primary mt-1 mx-1 rounded-pill">
      <div class="col-sm-auto">
        <h4 class="text-white display-1">UB CSE Evalution System<br>Confirm Rubric</h4>
      </div>
    </div>
    <div class="row justify-content-md-center mt-5 mx-1">
      <div class="col-sm-auto text-center">
        <h4>Verifying rubric<br><?php echo $_SESSION["rubric"]["name"]; ?></h4>
      </div>
    </div>
		<div id="rubric-table" class="row pt-1 mx-1 align-items-center text-center border border-3 border-dark">
      <?php
        echo emitRubricTable($_SESSION["confirm"]["topics"], $_SESSION["confirm"]["scores"]);
      ?>
  	</div>
    <form id="confirm-rubric" method="post">
      <div class="row mt-2 mx-1">
        <div class="col">
          <input class="btn btn-outline-danger" name="revise-rubric" type="submit" value="Revise Rubic"></input>
        </div>
        <div class="col ms-auto">
          <input class="btn btn-success" name="save-rubric" type="submit" value="Save Rubic"></input>
        </div>
      </div>
    </form>
    <hr>
    <div class="row mx-1 mt-2 justify-content-center">
        <div class="col-auto">
					<a href="surveys.php" class="btn btn-outline-info" role="button" aria-disabled="false">Return to Instructor Home</a>
        </div>
      </div>
</div>

  </div>
</main>
</body>
</html>
