<?php
//error logging
error_reporting(-1); // reports all errors
ini_set("display_errors", "1"); // shows all errors
ini_set("log_errors", 1);
ini_set("error_log", "~/php-error.log");

//bring in required code
require "../lib/database.php";
require "../lib/constants.php";
require "lib/rubricQueries.php";
require "lib/loginStatus.php";

$instructor_id = getInstructorId();

//query information about the requester
$con = connectToDatabase();

// Verify we have already defined the rubric basics
if (!isset($_SESSION['rubric-format'])) {
  http_response_code(400);
  echo "You must initialize the rubric's name and levels before setting the criteria";
  exit();
}

$defined_rubric_name = $_SESSION['rubric-format']['name'];
$defined_rubric_levels = $_SESSION['rubric-format']['levels'];
// unsure if we should unset or not
// unset($_SESSION['rubric-format']); 

$errorMsg = array();

// Check if we are revising a previous submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  // make sure minimum set of values exist

  $jsonPOST = file_get_contents('php://input');
  $post_data = json_decode($jsonPOST, $flags=JSON_OBJECT_AS_ARRAY);

  $_POST = $post_data;

  if (!isset($_POST['topics'])) {
    http_response_code(400);
    echo "Bad Request: Missing parameters to define criteria";
    exit();
  }

  // Process criterion data post 
  $topics = $_POST['topics'];

  $criterion_num = -1;
  $criteria = array();
  foreach ($topics as $criterion){
    $criterion_num++;
    $criterion_id = "criterion-".$criterion_num;

    $criterion_name = trim($criterion['question']);
    $criterion_responses = $criterion['responses'];
    $criterion_type = $criterion['type'];

    $valid_types = array(MC_QUESTION_TYPE, FREEFORM_QUESTION_TYPE);
    if ( !in_array($criterion_type, $valid_types)) {
      http_response_code(400);
      echo "Bad Request: Invalid response type";
      exit();
    }

    $criterion_errors = array();
    // check name
    if (empty($criterion_name)) {
      $criterion_errors['name'] = "Each criterion must have a description";
    } elseif ( array_key_exists($criterion_name, $criteria) ) {
      $criterion_errors['name'] = "Each criterion needs a UNIQUE description";
    }

    if (count($criterion_responses) != count($defined_rubric_levels)){
        http_response_code(400);
        echo "Bad Request: Number of responses for each criterion must match the number of levels for rubric.";
        exit();
    }
    // check criterion responses
    foreach ($criterion_responses as $level_num => $response_text){
        $response_text = trim($response_text);
        
        if (empty($response_text)){
            $criterion_errors["level-".$level_num] = "Level description cannot be empty";
        }
    }
    
    $criterion_info = array();
    $criterion_info['responses'] = $criterion_responses;
    $criterion_info['type'] = $criterion_type;

    $criteria[$criterion_name] = $criterion_info;

    if (empty($criterion_errors)){

    } else {
        $errorMsg[$criterion_id] = $criterion_errors;
    }

  }
  unset($criterion);

  // means criterion_num was never updated and that there are no criterions, no max
  if ($criterion_num === -1){
    $errorMsg['criteria'] = "There must be at least 1 criterion defined";
  }
 
  $errors_response = array("errors" => array());

  if (empty($errorMsg)){

    $defined_rubric = array('name' => $defined_rubric_name, 'levels' => $defined_rubric_levels);
    $defined_rubric['criteria'] = array();

    $num_criteria = 0;
    foreach ($criteria as $name => $info){
        $criterion_id = "criterion-".$num_criteria;
        $criterion_responses = $info['responses'];
        $criterion_type = $info['type'];

        $criterion_data = array();
        $criterion_data['name'] = $name;
        $criterion_data['responses'] = $criterion_responses;
        $criterion_data['type'] = $criterion_type;

        $defined_rubric['criteria'][$criterion_id] = $criterion_data;

        $num_criteria++;
    }
    unset($name, $info);
    // $rubric_criteria = $criteria

    $_SESSION['rubric-preview'] = $defined_rubric;
  } else{
    $errors_response['errors'] = $errorMsg;
  }

  header("Content-Type: application/json; charset=UTF-8");
  $errorsJSON = json_encode($errors_response, JSON_ENCODE_OPTIONS);
  echo $errorsJSON;

}
?>
