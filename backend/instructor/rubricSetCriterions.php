<?php
function check_level_response($crit, $level_name, $text, &$errorMsg) {
  if (!isset($_POST[$crit."-".$level_name])) {
    http_response_code(403);
    echo "Forbidden: Incorrect parameters.";
    exit();
  }
  $ret_val = trim($_POST[$crit."-".$level_name]);
  if (empty($ret_val)) {
    $errorMsg[$crit.$level_name] = "Response for ".$text." cannot be empty";
  }
  return $ret_val;
}

//error logging
error_reporting(-1); // reports all errors
ini_set("display_errors", "1"); // shows all errors
ini_set("log_errors", 1);
ini_set("error_log", "~/php-error.log");

//start the session variable
session_start();

//bring in required code
require_once "../../lib/database.php";
require_once "../../lib/constants.php";
require_once "../lib/instructorQueries.php";
require_once "../lib/rubricQueries.php";

//query information about the requester
$con = connectToDatabase();

//try to get information about the instructor who made this request by checking the session token and redirecting if invalid
if (!isset($_SESSION['id'])) {
  http_response_code(403);
  echo "Forbidden: You must be logged in to access this page.";
  exit();
}
$instructor_id = $_SESSION['id'];

$errorMsg = array();
$criteria = array();

// Verify we have already defined the rubric basics
if (!isset($_SESSION["rubric"])) {
  http_response_code(302);
  header("Location: ".INSTRUCTOR_HOME."rubricAdd.php");
  exit();
}
// Check if we are revising a previous submission
if (($_SERVER['REQUEST_METHOD'] != 'POST') && isset($_SESSION["confirm"])) {
  $criteria = $_SESSION["confirm"]["topics"];
} else if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  // make sure minimum set of values exist
  if (!isset($_POST['criterion1-question'])) {
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

  // Process data so that it is available in a manner that parallels how rubric queries prepares a rubric.
  $crit_num = 1;
  $crit_id = "criterion".$crit_num;
  while (key_exists($crit_id.'-question', $_POST)) {
    $crit_data = array();
    
    $crit_data["question"] = trim($_POST[$crit_id.'-question']);
    if (empty($crit_data["question"])) {
      $errorMsg[$crit_id.'-question'] = "Each criterion needs a description";
    }
    // Translate the posted type to the values we actually use
    if (empty($_POST[$crit_id.'-type'])) {
      $crit_data["type"] = MC_QUESTION_TYPE;
      // When this is a multiple choice question, record each of the different responses
      $crit_data["responses"] = array();
      foreach ($_SESSION["rubric"]["levels"]["names"] as $level_name => $text) {
        $crit_data["responses"][$level_name] = check_level_response($crit_id, $level_name, $text, $errorMsg);
      }
    } else {
      $crit_data["type"] = FREEFORM_QUESTION_TYPE;
    }
    $criteria[] = $crit_data;
    $crit_num = $crit_num + 1;
    $crit_id = "criterion".$crit_num;
  }
  for ($i = 1; $i < $crit_num; $i++) {
    $trait = $criteria[$i-1]["question"];
    if (!empty($trait)) {
      for ($j = 1; $j < $crit_num; $j++) {
        if ( ($i != $j) && ($trait == $criteria[$j-1]["question"]) ) {
          $errorMsg["criterion".$i.'-question'] = "Each criterion needs UNIQUE description";
        }
      }
    }
  }
  if (count($errorMsg) == 0) {
    // Prepare the rubric for cofirmation
    $_SESSION["confirm"] = array();

    // Prepare the score data for confirmation
    $_SESSION["confirm"]["scores"] = array();
    foreach ($_SESSION["rubric"]["levels"]["names"] as $level => $name) {
      $_SESSION["confirm"]["scores"][$level] = array("name" => $name, "score" => $_SESSION["rubric"]["levels"]["values"][$level]);
    }

    // Prepare the topics & their reponses for confirmation
    $_SESSION["confirm"]["topics"] = $criteria;
    http_response_code(302);
    header("Location: ".INSTRUCTOR_HOME."rubricConfirm.php");
    exit();
  }
}

// Avoid problems in the verification screen from double-submitting a rubric
unset($_SESSION["confirm"]);
$csrf_token = createCSRFToken($con, $instructor_id);
$level_keys_for_js = json_encode(array_keys($_SESSION["rubric"]["levels"]["names"]));
$level_names_for_js =  json_encode(array_values($_SESSION["rubric"]["levels"]["names"]));
?>
