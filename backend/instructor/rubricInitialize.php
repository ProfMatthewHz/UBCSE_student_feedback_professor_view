<?php

function check_level_name($level_name, $names_seen, &$level_errors){
  // check for errors in name

  if (empty($level_name)){
    $level_errors['name'] = "Level MUST have a name";
  } elseif (in_array($level_name, $names_seen)) {
    $level_errors['name'] = "Each level must have a UNIQUE name";
  }
}
function check_level_score($level_score, $prev_score, &$level_errors){
  // check for errors in level
  
  if (!ctype_digit($level_score)){
    $level_errors['level'] = "Value MUST be a whole number";
  } elseif ( $level_score < $prev_score ) {
    $level_errors['level'] = "Lower Level CANNOT have higher value";
  }
}

function get_levels_data($levels_data, &$errorMsg){
  $curr_level = 0;
  $prev_score = PHP_INT_MIN;
  

  // names and scores will be in order from level 1-5
  $names_and_scores = array('level_names' => array(), 'level_scores' => array());  
  foreach ($levels_data as $level_data) {

    $level_id = "level-".$curr_level;
    $level_name = trim($level_data['name']);
    $level_score = $level_data['score'];
    
    $score_val = intval($level_score);
    $score_str = strval($level_score);
    // ctype_digit() only works on strings
    
    $level_errors = array();
    check_level_name($level_name, $names_and_scores['level_names'], $level_errors);
    check_level_score($score_str, $prev_score, $level_errors);

    $names_and_scores['level_names'][] = $level_name;
    $names_and_scores['level_scores'][] = $level_score;

    // set errors if there are any seen for this level
    if (!empty($level_errors)){
      $errorMsg[$level_id] = $level_errors;
    }

    if (ctype_digit($score_str)){
      $prev_score = max($prev_score, $score_val);
    }

    $curr_level++;
  }
  unset($level_data);

  return $names_and_scores;
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

//stores error messages corresponding to form field
$errorMsg = array();

if($_SERVER['REQUEST_METHOD'] == 'POST') {
  // make sure values exist

  $jsonPOST = file_get_contents('php://input');
  $post_data = json_decode($jsonPOST, $flags=JSON_OBJECT_AS_ARRAY);

  $_POST = $post_data;

  if (!isset($_POST['name']) || !isset($_POST['levels'])) {
    http_response_code(400);
    echo "Bad Request: Missing parameters to initialize rubric.";
    exit();
  }

  // check CSRF token
  // $csrf_token = getCSRFToken($con, $instructor_id);
  // if (!hash_equals($csrf_token, $_POST['csrf-token'])) {
  //   http_response_code(403);
  //   echo "Forbidden: Incorrect parameters.";
  //   exit();
  // }

  // Get all the data that was posted
  $rubric_name = trim($_POST['name']);
  $rubric_levels = $_POST['levels'];

  $num_levels = count($rubric_levels);
  $min_levels = 2;
  $max_levels = 5;

  if( $num_levels < $min_levels || $num_levels > $max_levels){
    http_response_code(400);
    echo "Bad Request: Number of Levels must be: 2 <= {Number of Levels} <= 5";
    exit();
  }
  
  // verify valid rubric name either 
  if (empty($rubric_name)) {
    $errorMsg['rubric-name'] = "Rubric MUST have a name";
  } else {
    $rubric_id = getIdFromDescription($con, $rubric_name);
    if (!empty($rubric_id)) {
      $errorMsg['rubric-name'] = "Rubric with that name already exists";
    }
  }

  $levels_data = get_levels_data($rubric_levels, $errorMsg);


  // set response
  $errors_response = array('errors' => array());

  if (empty($errorMsg)){
    $rubric_levels = array();

    $levels = array_combine($levels_data['level_names'], $levels_data['level_scores']);
    $level_num = 0;
    foreach ($levels as $name => $score){
      $level_id = "level-".$level_num;
      $rubric_levels[$level_id] = array('name' => $name, 'score' => $score);
      $level_num++;
    }
    unset($name, $score);
    
    $_SESSION['rubric-format'] = array('name' => $rubric_name, 'levels' => array());
    $_SESSION['rubric-format']['levels'] = $rubric_levels;

  } else {
    $errors_response['errors'] = $errorMsg;
  } 

  // in case we need to change it to send back data, 
  // set 'data' to be $rubric_data if there are no errors

  // $response = array('data' => array(), 'errors' => array());

  header("Content-Type: application/json; charset=UTF-8");
  $errorsJSON = json_encode($errors_response);
  echo $errorsJSON;

  echo "\n";   
  print_r($_SESSION['rubric-format']);

}
//   if (count($errorMsg) == 0) {
//     // Set the session variables so the data carries to the criterion page
//     $_SESSION["rubric"] = array("name" => $rubric_name);
//     $_SESSION["rubric"]["levels"] = array("names" => $level_names, "values" => $level_values);
//     http_response_code(302);
//     header("Location: ".INSTRUCTOR_HOME."rubricCriteriaAdd.php");
//     exit();
//   }
// } else if (isset($_SESSION["rubric"])) {
//   // This is a GET request, but we have a rubric in the session which means we are modifying an existing rubric
//   $rubric_name = $_SESSION["rubric"]["name"]." copy";
//   $rubric_level = count($_SESSION["rubric"]["levels"]["values"]);
//   $level_values = $_SESSION["rubric"]["levels"]["values"];
//   $level_names = $_SESSION["rubric"]["levels"]["names"];
// }
// unset($_SESSION["rubric"]);

$csrf_token = createCSRFToken($con, $instructor_id);


?>