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

$errorMsg = array();
$question_names = array();
$answer_names = array();
// Verify we have already defined the rubric basics
if (!isset($_SESSION["rubric"])) {
  http_response_code(400);
  echo "Bad Request: Missing parmeters.";
  exit();
}
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous"></script>
  <title>CSE Evaluation Survey System - Add Criteria</title>
</head>
<body class="text-center">
<!-- Header -->
<main>
  <div class="container-fluid">
    <div class="row justify-content-md-center bg-primary mt-1 mx-1 rounded-pill">
      <div class="col-sm-auto text-center">
        <h4 class="text-white display-1">UB CSE Evalution System<br>Specify Rubric Criteria</h4>
      </div>
    </div>

    <div class="row justify-content-md-center mt-5 mx-1">
      <div class="col-sm-auto text-center">
        <h4><?php echo "Criteria for ".$_SESSION["rubric"]["name"]; ?></h4>
      </div>
    </div>

    <form class="mt-5 mx-1" id="define-rubric" method="post">
      <div id="criterion1" class="border-top border-bottom">
        <div class="row mx-1">
          <div class="col text-start align-top">
            <span style="font-size:small;color:DarkGrey">Criterion #1:</span>
          </div>
        </div>
        <div class="row mx-1">
          <div class="col-auto form-floating">
            <input type="text" id="criterion1-question" class="form-control <?php if(isset($errorMsg["criterion1-question"])) {echo "is-invalid ";} ?>" name="criterion1-question" required value="<?php if (key_exists('criterion1-question', $question_names)) {echo htmlspecialchars($question_names['criterion1-question']);} ?>"></input>
            <label for="criterion1-question"><?php if(isset($errorMsg["criterion1-question"])) {echo $errorMsg["criterion1-question"]; } else { echo "Description of Trait:";} ?></label>
          </div>
        </div>
        <div class="row pt-1 mx-1 mb-3 align-items-center">
        <?
          $end_str = '">';
          foreach ($_SESSION["rubric"]["levels"]["names"] as $key => $name) { 
            echo '<div class="col ';
            echo $end_str;
            echo '<div class="form-floating">
              <textarea id="criterion1-'.$key.'" class="form-control ';
              if (isset($errorMsg["criterion1-'.$key.'"])) {
                echo "is-invalid ";
              }
              echo '" name="criterion1-'.$key.'" required value="';
              if (key_exists('criterion1-'.$key, $answer_names)) {
                echo htmlspecialchars($answer_names['criterion1-'.$key]);
              }
              echo '"></textarea>
              <label for="criterion1-'.$key.'">';
              if (isset($errorMsg["criterion1-'.$key.'"])) {
                echo $errorMsg["criterion1-'.$key.'"]; 
              } else { 
                echo 'Response for '.$name.':';
              }
              echo '</label></div></div>';
            // Update formatting so that all but first score use size correctly
            $end_str = 'ms-auto">';
          }
        ?>
      </div>
    </form>
  </div>
</main>
</body>
</html>