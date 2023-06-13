<?php

//error logging
error_reporting(-1); // reports all errors
ini_set("display_errors", "1"); // shows all errors
ini_set("log_errors", 1);
ini_set("error_log", "~/php-error.log");

// start the session variable
session_start();

// bring in required code
require_once "../lib/database.php";
require_once "../lib/constants.php";
require_once "lib/instructorQueries.php";
require_once "lib/courseQueries.php";
require_once "lib/surveyQueries.php";


// query information about the requester
$con = connectToDatabase();

//try to get information about the instructor who made this request by checking the session token and redirecting if invalid
if (!isset($_SESSION['id'])) {
  http_response_code(403);
  echo "Forbidden: You must be logged in to access this page.";
  exit();
}
$instructor_id = $_SESSION['id'];

// check for the query string or post parameter
$sid = NULL;
if($_SERVER['REQUEST_METHOD'] == 'GET') {
  // respond not found on no query string parameter
  if (!isset($_GET['survey'])) {
    http_response_code(404);   
    echo "404: Not found.";
    exit();
  }

  // make sure the query string is an integer, reply 404 otherwise
  $sid = intval($_GET['survey']);

  if ($sid === 0) {
    http_response_code(404);   
    echo "404: Not found.";
    exit();
  }
} else {
  // respond bad request if bad post parameters
  if (!isset($_POST['survey']) or !isset($_POST['csrf-token'])) {
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

  // make sure the post survey id is an integer, reply 400 otherwise
  $sid = intval($_POST['survey']);

  if ($sid === 0) {
    http_response_code(400);
    echo "Bad Request: Invalid parameters.";
    exit();
  }
  
}

// try to look up info about the requested survey
$survey_info = getSurveyData($con, $sid);
if (empty($survey_info)) {
  http_response_code(403);
  echo "403: Forbidden.";
  exit();
}
$survey_name = $survey_info['name'];

// Get the info for the course that this instructor teaches 
$course_info = getSingleCourseInfo($con, $survey_info['course_id'], $instructor_id);
if (empty($course_info)) {
  http_response_code(403);
  echo "403: Forbidden.";
  exit();
}
$course_name = $course_info['name'];
$course_code = $course_info['code'];
$course_term = SEMESTER_MAP_REVERSE[$course_info['semester']];
$course_year = $course_info['year'];

// now perform the possible deletion function
// first set some flags
$errorMsg = array();

// now perform the basic checks
if($_SERVER['REQUEST_METHOD'] == 'POST') {
  // now check for the agreement checkbox
  if (!isset($_POST['agreement'])) {
    $errorMsg['agreement'] = 'Please read the statement next to the checkbox and check it if you agree.';
  } else if ($_POST['agreement'] != "1") {
    $errorMsg['agreement'] = 'Please read the statement next to the checkbox and check it if you agree.';
  }
  
  // now delete the survey if agreement
  if (empty($errorMsg)) {
    if (deleteSurvey($con, $sid)) { 
      // redirect to next page and set message
      $_SESSION['survey-delete'] = "Successfully deleted survey.";
    } else {
      $_SESSION['survey-delete'] = "Error: Could not delete survey.";
    }
    http_response_code(302);   
    header("Location: ".INSTRUCTOR_HOME."surveys.php");
    exit();
  } 
}
$csrf_token = createCSRFToken($con, $instructor_id);
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous"></script>
  <title>CSE Evaluation Survey System - Delete Survey</title>
</head>
<body class="text-center">
<!-- Header -->
<main>
  <div class="container-fluid">
    <div class="row justify-content-md-center bg-primary mt-1 mx-1 rounded-pill">
      <div class="col-sm-auto text-center">
        <h4 class="text-white display-1">UB CSE Evalution System<br>Delete Existing Survey</h4>
      </div>
    </div>
    <div class="row justify-content-md-center mt-5 mx-4">
      <div class="col-sm-auto text-center">
        <h4><?php echo $course_name.' ('.$course_code.')';?><br><?php echo $survey_name.' Results'; ?></h4>
      </div>
    </div>
  </div>
  <div class="container-fluid">
    <form class="mt-5 mx-4" action="surveyDelete.php?survey=<?php echo $sid; ?>" id="delete-survey" method="post">
      <div class="row justify-content-center align-items-center">
        <p class="text-danger fs-3"><?php if(isset($errorMsg["agreement"])) {echo $errorMsg["agreement"];} ?></p>
      </div>
      <div class="row justify-content-center align-items-center">
        <div class="col-sm-auto form-check">
          <input class="form-check-input" type="checkbox" id="agreement" name="agreement" value="1"></input>
          <label class="form-check-label" for="agreement">
            I understand that deleting this survey will delete all scores associated with this survey.</label>
        </div>
        <input type="hidden" name="survey" value="<?php echo $sid; ?>"></input>
        <input type="hidden" name="csrf-token" value="<?php echo $csrf_token; ?>"></input>
        <div class="row justify-content-center align-items-center">
          <div class="col-sm-auto form-check">
            <input type="submit" class="btn btn-danger" value="Delete Survey"></input>
          </div>
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
</main>
</body>
</html>

