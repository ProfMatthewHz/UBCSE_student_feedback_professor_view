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
require_once "../lib/infoClasses.php";
require_once "../lib/fileParse.php";
require_once 'lib/rubricQueries.php';

// set timezone
date_default_timezone_set('America/New_York');


// //query information about the requester
$con = connectToDatabase();

// //try to get information about the instructor who made this request by checking the session token and redirecting if invalid
$instructor = new InstructorInfo();
$instructor->check_session($con, 0);

//stores error messages corresponding to form fields
$errorMsg = array();

// set flags
$course_id = NULL;
$survey_id = NULL;
$rubric_id = NULL;
$start_data = NULL;
$end_data = NULL;
$start_date = NULL;
$end_date = NULL;
$start_time = NULL;
$end_time = NULL;

// check for the survey number we are working with
if (($_SERVER['REQUEST_METHOD'] == 'GET') && isset($_GET['survey'])) {
  $survey_id = intval($_GET['survey']);
} else if (($_SERVER['REQUEST_METHOD'] == 'POST') && isset($_POST['survey-id'])) {
  $survey_id = intval($_POST['survey-id']);
} else {
  http_response_code(400);
  echo "Bad Request: Missing parameters.";
  exit();
}

// Get rubric info
$rubrics = selectRubrics($con);

// Get the survey's current data 
$stmt = $con->prepare('SELECT surveys.*, course.code, course.name, course.semester, course.year FROM surveys INNER JOIN course on surveys.course_id=course.id WHERE surveys.id=? AND course.instructor_id=?');
$stmt->bind_param('ii', $survey_id, $instructor->id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_array(MYSQLI_NUM)) {
  $course_id = $row[1];
  $start_data = $row[2];
  $end_data = $row[3];
  $survey_name = $row[4];
  $rubric_id = $row[5];
  $course_code = $row[6];
  $course_name = $row[7];
  $course_term = SEMESTER_MAP_REVERSE[$row[8]];
  $course_year = $row[9];
}
if (!isset($course_id)) {
  http_response_code(400);
  echo "Bad Request: Invalid survey id.";
  exit();
}
$s = new DateTime($start_data);
$e = new DateTime($end_data);
$now = new DateTime();
$start_date = $s->format('Y-m-d');
$start_time = $s->format('H:i');
$end_date = $e->format('Y-m-d');
$end_time = $e->format('H:i');
$full_perms = $now > $s;

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
  if (!hash_equals($instructor->csrf_token, $_POST['csrf-token']) || !is_uploaded_file($_FILES['pairing-file']['tmp_name']))
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
  }

  $stmt = $con->prepare('SELECT year FROM course WHERE id=? AND instructor_id=?');
  $stmt->bind_param('ii', $course_id, $instructor->id);
  $stmt->execute();
  $result = $stmt->get_result();
  $data = $result->fetch_all(MYSQLI_ASSOC);

  // reply forbidden if instructor did not create survey
  if ($result->num_rows == 0) {
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

  // check the pairing mode
  $pairing_mode = trim($_POST['pairing-mode']);
  if (empty($pairing_mode)) {
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
      $pairings = getPairingResults($con, $pairing_mode, $file_handle);
      if (empty($pairings)) {
        $errorMsg['pairing-mode'] = 'Please choose a valid mode for the pairing file.';
      }
      
      // Clean up our file handling
      fclose($file_handle);

      // check for any errors
      if (isset($pairings['error'])) {
        $errorMsg['pairing-file'] = $pairings['error'];
      } else {
        // finally add the pairings to the database if no other error message were set so far
        // first add the survey details to the database
        if (empty($errorMsg)) {
          $sdate = $start_date . ' ' . $start_time;
          $edate = $end_date . ' ' . $end_time;
          $stmt = $con->prepare('INSERT INTO surveys (course_id, name, start_date, expiration_date, rubric_id) VALUES (?, ?, ?, ?, ?)');
          $stmt->bind_param('isssi', $course_id, $survey_name, $sdate, $edate, $rubric_id);
          $stmt->execute();

          add_pairings($pairings, $con->insert_id, $con);

          // redirect to survey page with sucess message
          $_SESSION['survey-add'] = "Successfully added survey.";

          http_response_code(302);
          header("Location: surveys.php");
          exit();
        }
      }
    }
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
  <title>CSE Evaluation Survey System - Update Survey</title>
</head>
<body class="text-center">
<!-- Header -->
<main>
  <div class="container-fluid">
    <div class="row justify-content-md-center bg-primary mt-1 mx-1 rounded-pill">
      <div class="col-sm-auto text-center">
        <h4 class="text-white display-1">UB CSE Evalution System<br>Update Existing Survey</h4>
      </div>
    </div>
    <form class="mt-5 mx-4" id="add-survey" method="post" enctype="multipart/form-data">
    <div class="form-inline justify-content-center align-items-center">
      <div class="form-floating mb-3">
          <select class="form-select" id="course-id" name="course-id" disabled>
            <?php
                echo '<option value="' . $course_id . '" selected>' . htmlspecialchars($course_code) . ' ' . htmlspecialchars($course_name) . ' - ' . htmlspecialchars($course_term) . ' ' . htmlspecialchars($course_year) . '</option>';
            ?>
          </select>
          <label for="course-id"><?php if(isset($errorMsg["course-id"])) {echo $errorMsg["course-id"]; } else { echo "Course:";} ?></label>
      </div>
      <div class="form-floating mb-3">
          <input type="text" id="survey-name" class="form-control" name="survey-name" required <?php if ($survey_name) {echo 'value="' . htmlspecialchars($survey_name) . '"';} if (!$full_perms) { echo "readonly"; }?>></input>
          <label for="survey-name">Survey Name:</label>
      </div>
      <div class="form-floating mb-3">
          <input type="date" id="start-date" name="start-date" class="form-control <?php if(isset($errorMsg["start-date"])) {echo "is-invalid ";} ?>" required value="<?php echo htmlspecialchars($start_date); ?>" <?php if (!$full_perms) { echo "readonly"; }?>></input>
          <label for="start-date"><?php if(isset($errorMsg["start-date"])) {echo $errorMsg["start-date"]; } else { echo "Start Date:";} ?></label>
      </div>
      <div class="form-floating mb-3">
          <input type="time" id="start-time" name="start-time" class="form-control <?php if(isset($errorMsg["start-time"])) {echo "is-invalid ";} ?>" required value="<?php echo htmlspecialchars($start_time); ?>" <?php if (!$full_perms) { echo "readonly"; }?>></input>
          <label for="start-time"><?php if(isset($errorMsg["start-time"])) {echo $errorMsg["start-time"]; } else { echo "Start Time:";} ?></label>
      </div>
      <div class="form-floating mb-3">
          <input type="date" id="end-date" name="end-date" class="form-control <?php if(isset($errorMsg["end-date"])) {echo "is-invalid ";} ?>" required value="<?php echo htmlspecialchars($end_date); ?>"></input>
          <label for="end-date"><?php if(isset($errorMsg["end-date"])) {echo $errorMsg["end-date"]; } else { echo "End Date:";} ?></label>
      </div>
      <div class="form-floating mb-3">
          <input type="time" id="end-time" name="end-time" class="form-control <?php if(isset($errorMsg["end-time"])) {echo "is-invalid ";} ?>" required value="<?php echo htmlspecialchars($end_time);?>"></input>
          <label for="end-time"><?php if(isset($errorMsg["end-time"])) {echo $errorMsg["end-time"]; } else { echo "End Time:";} ?></label>
      </div>
      <div class="form-floating mb-3">
          <select class="form-select <?php if(isset($errorMsg["rubric-id"])) {echo "is-invalid ";} ?>" id="rubric-id" name="rubric-id" <?php if (!$full_perms) { echo "disabled"; }?>>
            <?php
            if (!$full_perms) {
              echo '<option value="' . $rubric_id . '" selected>' . htmlspecialchars($rubrics[$rubric_id]['description']) . '</option>';
            } else {
              foreach ($rubrics as $rubric) {
                if ($rubric_id == $rubric['id']) {
                  echo '<option value="' . $rubric['id'] . '" selected>' . htmlspecialchars($rubric['description']) . '</option>';
                } else {
                  echo '<option value="' . $rubric['id'] . '" >' . htmlspecialchars($rubric['description']) . '</option>';
                }
              }
            }
            ?>
          </select>
          <label for="rubric-id"><?php if(isset($errorMsg["rubric-id"])) {echo $errorMsg["rubric-id"]; } else { echo "Rubric:";} ?></label>
      </div>

      <input type="hidden" name="survey_id" value="<?php echo $survey_id; ?>" />

      <input type="hidden" name="csrf-token" value="<?php echo $instructor->csrf_token; ?>" />

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
</body>
</html>
