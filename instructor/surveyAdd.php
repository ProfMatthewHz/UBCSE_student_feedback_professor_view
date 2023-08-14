<?php

//error logging
error_reporting(-1); // reports all errors
ini_set("display_errors", "1"); // shows all errors
ini_set("log_errors", 1);
ini_set("error_log", "~/php-error.log");

// //start the session variable
session_start();

// //bring in required code
require_once "../lib/database.php";
require_once "../lib/constants.php";
require_once '../lib/studentQueries.php';
require_once "lib/instructorQueries.php";
require_once "lib/fileParse.php";
require_once "lib/pairingFunctions.php";
require_once "lib/rubricQueries.php";
require_once "lib/surveyQueries.php";
require_once "lib/courseQueries.php";
require_once "lib/reviewQueries.php";

// set timezone
date_default_timezone_set('America/New_York');

// //query information about the requester
$con = connectToDatabase();

//try to get information about the instructor who made this request by checking the session token and redirecting if invalid
if (!isset($_SESSION['id'])) {
  http_response_code(403);
  echo "Forbidden: You must be logged in to access this page.";
  exit();
}
$instructor_id = $_SESSION['id'];

// store information about courses as array of array
$courses = array();

// Find out the term that we are currently in
$month = idate('m');
$term = MONTH_MAP_SEMESTER[$month];
$year = idate('Y');

// get information about the courses
$all_data = getAllCoursesForInstructor($con, $instructor_id);

// Only show courses from the current term and future terms
foreach ($all_data as $row) {
  if (($row['year'] >= $year) && ($row['semester'] >= $term)) {
    $course_info = array();
    $course_info['code'] = $row['code'];
    $course_info['name'] = $row['name'];
    $course_info['semester'] = SEMESTER_MAP_REVERSE[$row['semester']];
    $course_info['year'] = $row['year'];
    $course_info['id'] = $row['id'];
    $courses[] = $course_info;
  }
}

// store information about rubrics as array of array
$rubrics = getRubrics($con);

//stores error messages corresponding to form fields
$errorMsg = array();

// set flags
$course_id = NULL;
$rubric_id = NULL;
$start_date = NULL;
$end_date = NULL;
$start_time = NULL;
$end_time = NULL;
$pairing_mode = NULL;
$survey_name = NULL;
$pm_mult = 1;

// check for the query string or post parameter
if($_SERVER['REQUEST_METHOD'] == 'GET') {
  // respond not found on no query string parameter
  if (isset($_GET['course'])) {
    $course_id = intval($_GET['course']);
  } else {
    http_response_code(400);
    echo "Bad Request: Missing parameters.";
    exit();
  }
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {

  // make sure values exist
  if (!isset($_POST['pairing-mode']) || !isset($_FILES['pairing-file']) || !isset($_POST['start-date']) || !isset($_POST['start-time']) || !isset($_POST['end-date']) || !isset($_POST['end-time']) || !isset($_POST['csrf-token']) ||
      !isset($_POST['course-id']) || !isset($_POST['survey-name']) || !isset($_POST['rubric-id']))
  {
    http_response_code(400);
    echo "Bad Request: Missing parameters.";
    exit();
  }

  // check CSRF token
  $csrf_token = getCSRFToken($con, $instructor_id);
  if ((!hash_equals($csrf_token, $_POST['csrf-token'])) || !is_uploaded_file($_FILES['pairing-file']['tmp_name']))
  {
    http_response_code(403);
    echo "Forbidden: Incorrect parameters.";
    exit();
  }

  // get the name of this survey
  $survey_name = trim($_POST['survey-name']);

  // check course is not empty
  $course_id = $_POST['course-id'];
  $course_id = intval($course_id);
  if ($course_id === 0) {
    $errorMsg['course-id'] = "Please choose a valid course.";
  }

  // check rubric is not empty
  $rubric_id = $_POST['rubric-id'];
  $rubric_id = intval($rubric_id);
  if ($rubric_id === 0) {
    $errorMsg['rubric-id'] = "Please choose a valid rubric.";
  } else if (!array_key_exists($rubric_id, $rubrics)) {
    $errorMsg['rubric-id'] = "Please choose a valid rubric.";
  }

  if (!isCourseInstructor($con, $course_id, $instructor_id)) {
    $errorMsg['course-id'] = "Please choose a valid course.";
  }

  $start_date = trim($_POST['start-date']);
  $end_date = trim($_POST['end-date']);
  if (empty($start_date)) {
    $errorMsg['start-date'] = "Please choose a start date.";
  }
  if (empty($end_date)) {
    $errorMsg['end-date'] = "Please choose a end date.";
  }

  // check the date's validity
  if (!isset($errorMsg['start-date']) and !isset($errorMsg['end-date']))
  {
    $start = DateTime::createFromFormat('Y-m-d', $start_date);
    if (!$start) {
      $errorMsg['start-date'] = "Please choose a valid start date (YYYY-MM-DD)";
    } else if ($start->format('Y-m-d') != $start_date) {
      $errorMsg['start-date'] = "Please choose a valid start date (YYYY-MM-DD)";
    }

    $end = DateTime::createFromFormat('Y-m-d', $end_date);
    if (!$end) {
      $errorMsg['end-date'] = "Please choose a valid end date (YYYY-MM-DD)";
    } else if ($end->format('Y-m-d') != $end_date) {
      $errorMsg['end-date'] = "Please choose a valid end date (YYYY-MM-DD)";
    }
  }

  $start_time = trim($_POST['start-time']);
  $end_time = trim($_POST['end-time']);

  if (empty($start_time)) {
    $errorMsg['start-time'] = "Please choose a start time.";
  }
  if (empty($end_time)) {
    $errorMsg['end-time'] = "Please choose a end time.";
  }

  if (!isset($errorMsg['start-time']) && !isset($errorMsg['end-time'])) {
    $start = DateTime::createFromFormat('H:i', $start_time);
    if (!$start) {
      $errorMsg['start-time'] = "Please choose a valid start time (HH:MM) (Ex: 15:00)";
    } else if ($start->format('H:i') != $start_time) {
      $errorMsg['start-time'] = "Please choose a valid start time (HH:MM) (Ex: 15:00)";
    }

    $end = DateTime::createFromFormat('H:i', $end_time);
    if (!$end) {
      $errorMsg['end-time'] = "Please choose a valid end time (HH:MM) (Ex: 15:00)";
    } else if ($end->format('H:i') != $end_time) {
      $errorMsg['end-time'] = "Please choose a valid end time (HH:MM) (Ex: 15:00)";
    }
  }

  // check dates and times
  if (!isset($errorMsg['start-date']) && !isset($errorMsg['start-time']) && !isset($errorMsg['end-date']) && !isset($errorMsg['end-time'])) {
    $s = new DateTime($start_date . ' ' . $start_time);
    $e = new DateTime($end_date . ' ' . $end_time);
    $today = new DateTime();

    if ($e < $s) {
      $errorMsg['end-date'] = "End date and time cannot be before start date and time.";
      $errorMsg['end-time'] = "End date and time cannot be before start date and time.";
      $errorMsg['start-date'] = "End date and time cannot be before start date and time.";
      $errorMsg['start-time'] = "End date and time cannot be before start date and time.";
    } else if ($e < $today) {
      $errorMsg['end-date'] = "End date and time must occur in the future.";
      $errorMsg['end-time'] = "End date and time must occur in the future.";
    }
  }

  // Get the multiplier used for pm evaluations
  $pm_mult = intval($_POST['pm-mult']);

  // check the pairing mode
  $pairing_mode = intval($_POST['pairing-mode']);
  if (!array_key_exists($pairing_mode, $_SESSION["surveyTypes"])) {
    $errorMsg['pairing-mode'] = 'Please choose a valid mode for the pairing file.';
  }
  
  // validate the uploaded file
  if ($_FILES['pairing-file']['error'] == UPLOAD_ERR_INI_SIZE) {
    $errorMsg['pairing-file'] = 'The selected file is too large.';
  } else if ($_FILES['pairing-file']['error'] == UPLOAD_ERR_PARTIAL) {
    $errorMsg['pairing-file'] = 'The selected file was only paritally uploaded. Please try again.';
  } else if ($_FILES['pairing-file']['error'] == UPLOAD_ERR_NO_FILE) {
    $errorMsg['pairing-file'] = 'A pairing file must be provided.';
  } else if ($_FILES['pairing-file']['error'] != UPLOAD_ERR_OK)  {
    $errorMsg['pairing-file'] = 'An error occured when uploading the file. Please try again.';
  } else {
    // start parsing the file
    $file_handle = @fopen($_FILES['pairing-file']['tmp_name'], "r");

    // catch errors or continue parsing the file
    if (!$file_handle) {
      $errorMsg['pairing-file'] = 'An error occured when uploading the file. Please try again.';
    } else {
      // Get the data from the review file 
      $file_data = processReviewFile($con, ($pairing_mode == 1),  $file_handle);
      
      // Clean up our file handling
      fclose($file_handle);

      // check for any errors
      if (!empty($pairings['error'])) {
        $errorMsg['pairing-file'] = $file_data['error'];
      } else if (empty($errorMsg)) {
        // Save the data we will need for the confirmation page
        $_SESSION["survey_course_id"] = $course_id;
        $_SESSION["survey_file"] = $file_data['rows'];
        $_SESSION["survey_students"] = $file_data['individuals'];
        $_SESSION["survey_data"] = array('start' => $s, 'end' => $e, 'pairing_mode' => $pairing_mode, 'multiplier' => $pm_mult, 'rubric' => $rubric_id, 'name' => $survey_name);
        http_response_code(302);
        header("Location: ".INSTRUCTOR_HOME."surveyConfirm.php");
        exit();
        // finally add the pairings to the database if no other error message were set so far
        // first add the survey details to the database
        // if (empty($errorMsg)) {
        //   $sdate = $start_date . ' ' . $start_time;
        //   $edate = $end_date . ' ' . $end_time;
        //   $survey_id = insertSurvey($con, $course_id, $survey_name, $sdate, $edate, $rubric_id, $pairing_mode);
        //   addReviewsToSurvey($con, $survey_id, $pairings['ids']);
        //   http_response_code(302);
        //   header("Location: ".INSTRUCTOR_HOME."surveys.php");
        //   exit();
        // }
      }
    }
  }
}
if ( (!isset($rubric_id)) && (count($rubrics) == 1)) {
  $rubric_id = array_key_first($rubrics);
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
  <title>CSE Evaluation Survey System - Add Survey</title>
  <?php emitUpdateFileDescriptionFn(); ?>
</head>
<body class="text-center">
<!-- Header -->
<main>
  <div class="container-fluid">
    <div class="row justify-content-md-center bg-primary mt-1 mx-1 rounded-pill">
      <div class="col-sm-auto text-center">
        <h4 class="text-white display-1">UB CSE Evalution System<br>Create New Survey</h4>
      </div>
    </div>
    <?php
    if (!empty($errorMsg['pairing-file'])) {
      echo '<div class="modal fade" id="errorModalDisplay" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="errorModalTitle" aria-hidden="true">';
      echo '  <div class="modal-dialog modal-dialog-scrollable">';
      echo '    <div class="modal-content">';
      echo '      <div class="modal-header">';
      echo '        <h5 class="modal-title" id="errorModalTitle">Review File Errors</h5>';
      echo '        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>';
      echo '      </div>';
      echo '      <div class="modal-body">';
      echo '        <p>' . $errorMsg['pairing-file'] . '</p>';
      echo '      </div>';
      echo '      <div class="modal-footer">';
      echo '         <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>';
      echo '      </div>';
      echo '    </div>';
      echo '  </div>';
      echo '</div>';
    }
    ?>
    <form class="mt-5 mx-4" id="add-survey" method="post" enctype="multipart/form-data">
    <div class="form-inline justify-content-center align-items-center">
      <div class="form-floating mb-3">
          <select class="form-select <?php if(isset($errorMsg["course-id"])) {echo "is-invalid ";} ?>" id="course-id" name="course-id">
            <option value="-1" disabled <?php if (isset($course_id)) {echo 'selected';} ?>>Select Course</option>
            <?php
            foreach ($courses as $course) {
              if ($course_id == $course['id']) {
                echo '<option value="' . $course['id'] . '" selected>' . htmlspecialchars($course['code']) . ' ' . htmlspecialchars($course['name']) . ' - ' . htmlspecialchars($course['semester']) . ' ' . htmlspecialchars($course['year']) . '</option>';
              } else {
                echo '<option value="' . $course['id'] . '" >' . htmlspecialchars($course['code']) . ' ' . htmlspecialchars($course['name']) . ' - ' . htmlspecialchars($course['semester']) . ' ' . htmlspecialchars($course['year']) . '</option>';
              }
            }
            ?>
          </select>
          <label for="course-id"><?php if(isset($errorMsg["course-id"])) {echo $errorMsg["course-id"]; } else { echo "Course:";} ?></label>
      </div>
      <div class="form-floating mb-3">
          <input type="text" id="survey-name" class="form-control" name="survey-name" required <?php if ($survey_name) {echo 'value="' . htmlspecialchars($survey_name) . '"';} ?>></input>
          <label for="survey-name">Survey Name:</label>
      </div>
      <div class="form-floating mb-3">
          <input type="date" id="start-date" name="start-date" class="form-control <?php if(isset($errorMsg["start-date"])) {echo "is-invalid ";} ?>" required value="<?php if ($start_date) {echo htmlspecialchars($start_date);} else {echo date("Y-m-d");} ?>"></input>
          <label for="start-date"><?php if(isset($errorMsg["start-date"])) {echo $errorMsg["start-date"]; } else { echo "Start Date:";} ?></label>
      </div>
      <div class="form-floating mb-3">
          <input type="time" id="start-time" name="start-time" class="form-control <?php if(isset($errorMsg["start-time"])) {echo "is-invalid ";} ?>" required value="<?php if ($start_time) {echo htmlspecialchars($start_time);} else {echo "00:00";} ?>"></input>
          <label for="start-time"><?php if(isset($errorMsg["start-time"])) {echo $errorMsg["start-time"]; } else { echo "Start Time:";} ?></label>
      </div>
      <div class="form-floating mb-3">
          <input type="date" id="end-date" name="end-date" class="form-control <?php if(isset($errorMsg["end-date"])) {echo "is-invalid ";} ?>" required value="<?php if ($end_date) {echo htmlspecialchars($end_date);} else {echo date("Y-m-d");} ?>"></input>
          <label for="end-date"><?php if(isset($errorMsg["end-date"])) {echo $errorMsg["end-date"]; } else { echo "End Date:";} ?></label>
      </div>
      <div class="form-floating mb-3">
          <input type="time" id="end-time" name="end-time" class="form-control <?php if(isset($errorMsg["end-time"])) {echo "is-invalid ";} ?>" required value="<?php if ($end_time) {echo htmlspecialchars($end_time);} else {echo "23:59";} ?>"></input>
          <label for="end-time"><?php if(isset($errorMsg["end-time"])) {echo $errorMsg["end-time"]; } else { echo "End Time:";} ?></label>
      </div>
      <div class="form-floating mb-3">
          <select class="form-select <?php if(isset($errorMsg["rubric-id"])) {echo "is-invalid ";} ?>" id="rubric-id" name="rubric-id" required>
            <option value="" disabled <?php if (!$rubric_id) {echo 'selected';} ?>>Select Rubric</option>
            <?php
            foreach ($rubrics as $id => $description) {
              if ($rubric_id == $id) {
                echo '<option value="' . $id . '" selected>' . htmlspecialchars($description) . '</option>';
              } else {
                echo '<option value="' . $id . '" >' . htmlspecialchars($description) . '</option>';
              }
            }
            ?>
          </select>
          <label for="rubric-id"><?php if(isset($errorMsg["rubric-id"])) {echo $errorMsg["rubric-id"]; } else { echo "Rubric:";} ?></label>
      </div>
      <?php emitSurveyTypeSelect($errorMsg, $pairing_mode, $pm_mult); ?>
      <span id="fileFormat" style="font-size:small;color:DarkGrey"></span>
      <div class="form-floating mt-0 mb-3">
        <input type="file" id="pairing-file" class="form-control <?php if(isset($errorMsg["pairing-file"])) {echo "is-invalid ";} ?>" name="pairing-file" required></input>
        <label for="pairing-file" style="transform: scale(.85) translateY(-.85rem) translateX(.15rem);"><?php if(isset($errorMsg["pairing-file"])) {echo 'Errors in file need to be fixed.'; } else { echo "Review Assignments (CSV File):";} ?></label>
      </div>

      <input type="hidden" name="csrf-token" value="<?php echo $csrf_token; ?>" />

      <input type="submit" class="btn btn-success" value="Create Survey">
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
<?php
// Show the modal box with all of the file errors when there were file errors.
if (!empty($errorMsg["pairing-file"])) {
  echo '<script>let errorModal = new bootstrap.Modal(document.getElementById("errorModalDisplay"));errorModal.show();</script>';
}
?>
</body>
</html>
