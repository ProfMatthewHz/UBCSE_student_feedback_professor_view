<?php
function processIndividualSurvey($con, $course_id, $pairing_mode) {
  $retval = array( 'errors' => array() );

  // Process the pairing file and return the student and tean information
  if ($_FILES['pairing-file']['error'] == UPLOAD_ERR_INI_SIZE) {
    $retval['errors']['pairing-file'] = array('The evaluation file is too large.');
  } else if ($_FILES['pairing-file']['error'] != UPLOAD_ERR_OK) {
    $retval['errors']['pairing-file'] = array('An error occured when uploading the evaluation file. Please try again.');
  } else {
    // If there are no errors, then we can process and return the student and team info
    $roster = getRoster($con, $course_id);

    // start parsing the file
    $file_handle = @fopen($_FILES['pairing-file']['tmp_name'], "r");

    // Catch errors when the file cannot be opened
    if (!$file_handle) {
      $retval['errors']['pairing-file'] = array('An error occured when uploading the evaluation file. Please try again.');
    } else {

      // Get the data from the review file 
      $file_data = processReviewFile($con, ($pairing_mode == 1),  $file_handle, $roster);
      
      // Clean up our file handling
      fclose($file_handle);

      // check for any errors
      if (!empty($file_data['error'])) { #
        $retval['errors']['pairing-file'] = $file_data['error'];
      } else {  
        // Process the file rows to calculate complete individual & team data
        $team_info = processFileRows($file_data['rows'], $pairing_mode);

        // Use the team info to add the reviewing and reviewed information to the individual data
        $retval["data"] = array('individuals' => $file_data['individuals'], 'teams' => $team_info);
      }
    }
  }
  return $retval;
}

function processAggregatedSurvey($con, $course_id, $pairing_mode) {
  $retval = array( 'errors' => array() );

  // Check that we have the (otherwise unusued) file with team assignments
  if (!isset($_FILES['team-file']['name'])) {
      http_response_code(400);
      echo "Bad Request: Missing parameters.";
      exit();
  }
  // Process the file of teams' rosters
  if ($_FILES['team-file']['error'] == UPLOAD_ERR_INI_SIZE) {
    $retval['errors']['team-file'] = array('The team roster file is too large.');
  } else if ($_FILES['team-file']['error'] != UPLOAD_ERR_OK) {
    $retval['errors']['team-file'] = array('An error occured when uploading the team roster file. Please try again.');
  } else {
    // If there are no errors, then we can process and return the student and team info
    $roster = getRoster($con, $course_id);
    // Parsing the team file
    $file_handle = @fopen($_FILES['team-file']['tmp_name'], "r");
    // Catch errors when the file cannot be opened
    if (!$file_handle) {
      $retval['errors']['team-file'] = array('An error occured when uploading the team roster file. Please try again.');
    } else {
      $team_data = processTeamFile($con, $file_handle, $roster);

      // Clean up our file handling
      fclose($file_handle);
      // check for any errors
      if (!empty($team_data['error'])) { #
        $retval['errors']['team-file'] = $team_data['error'];
      }
    }
  }
  // Process the pairing file
  if ($_FILES['pairing-file']['error'] == UPLOAD_ERR_INI_SIZE) {
    $retval['errors']['pairing-file'] = array('The team evaluation assignment file is too large.');
  } else if ($_FILES['pairing-file']['error'] != UPLOAD_ERR_OK) {
    $retval['errors']['pairing-file'] = array('An error occured when uploading the team evaluation assignment file. Please try again.');
  } else {
    // start parsing the file
    $file_handle = @fopen($_FILES['pairing-file']['tmp_name'], "r");

    // Catch errors when the file cannot be opened
    if (!$file_handle) {
      $retval['errors']['pairing-file'] = array('An error occured when uploading the team evaluation assignment file. Please try again.');
    } else {
      // Get the data from the review file
      $pairing_data = processAggregateReviewFile($con, $file_handle, $team_data['teams']); 
      
      // Clean up our file handling
      fclose($file_handle);

      // check for any errors
      if (!empty($pairing_data['error'])) {
        $retval['errors']['pairing-file'] = $pairing_data['error'];
      } else {
        // Use the team info to add the reviewing and reviewed information to the individual data
        $retval["data"] = array('individuals' => $team_data['individuals'], 'teams' => $team_data['teams'], 'pairings' => $pairing_data['matchups']);
      }
    }
  }
  return $retval;
}

// //bring in required code
require_once "../lib/database.php";
require_once "../lib/constants.php";
require_once '../lib/studentQueries.php';
require_once "lib/enrollmentFunctions.php";
require_once "lib/instructorQueries.php";
require_once "lib/fileParse.php";
require_once "lib/pairingFunctions.php";
require_once "lib/rubricQueries.php";
require_once "lib/surveyQueries.php";
require_once "lib/courseQueries.php";
require_once "lib/reviewQueries.php";
require_once "lib/loginStatus.php";

//error logging
error_reporting(-1); // reports all errors
ini_set("display_errors", "1"); // shows all errors
ini_set("log_errors", 1);
ini_set("error_log", "~/php-error.log");

$instructor_id = getInstructorId();

// Verify that this is a proper request 
if($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405); // Method Not Allowed
  echo "Only POST requests are allowed.";
  exit();
}


// make sure the required values exist
if (!isset($_POST['pairing-mode']) || !isset($_FILES['pairing-file']) ||
    !isset($_POST['course-id']) ) {
  http_response_code(400);
  echo "Bad Request: Missing parameters.";
  exit();
}

// query information about the requester
$con = connectToDatabase();

//stores error messages corresponding to form fields
$errorMsg = array();

// check course is not empty
$course_id = intval($_POST['course-id']);
if ($course_id === 0) {
  http_response_code(400);
  echo "Bad Request: Incorrect parameters.";
  exit();
}

// Verify that this is a valid course for this instructor
if (!isCourseInstructor($con, $course_id, $instructor_id)) {
  http_response_code(400);
  echo "Bad Request: Do not have permission for this action.";
  exit();
}

// Verify that the pairing mode is valid
$pairing_mode = intval($_POST['pairing-mode']);
$surveyTypes = getSurveyTypes($con);
if (!array_key_exists($pairing_mode, $surveyTypes)) {
  http_response_code(400);
  echo "Bad Request: Incorrect parameters.";
  exit();
}

$response = null;

// We split out when we have aggregated surveys and individual surveys.
if ($pairing_mode == 6) {
  $response = processAggregatedSurvey($con, $course_id, $pairing_mode);
} else {
  // We are not in aggregated survey mode, so we only have the pairing file to process
  $response = processIndividualSurvey($con, $course_id, $pairing_mode);
}
// Emit the response
header("Content-Type: application/json; charset=UTF-8");
$responseJSON = json_encode($response);
echo $responseJSON;
?>