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
require_once "../lib/infoClasses.php";
require_once "lib/pairingFunctions.php";
require_once "lib/surveyQueries.php";
require_once "../lib/fileParse.php";

// set timezone
date_default_timezone_set('America/New_York');

// query information about the requester
$con = connectToDatabase();

// try to get information about the instructor who made this request by checking the session token and redirecting if invalid
$instructor = new InstructorInfo();
$instructor->check_session($con, 0);


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
  if (!isset($_POST['survey'])) {
    http_response_code(400);
    echo "Bad Request: Missing parmeters.";
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

// Look up info about the requested survey
$survey_info = getSurveyRubric($con, $sid);
$survey_name = $survey_info['name'];

// make sure the survey is for a course the current instructor actually teaches
$stmt = $con->prepare('SELECT code, name, semester, year FROM course WHERE id=? AND instructor_id=?');
$stmt->bind_param('ii', $survey_info['course_id'], $instructor->id);
$stmt->execute();
$result = $stmt->get_result();
$course_info = $result->fetch_all(MYSQLI_ASSOC);

// reply forbidden if instructor did not create survey or if survey was ambiguous
if ($result->num_rows != 1) {
  http_response_code(403);
  echo "403: Forbidden.";
  exit();
}
$course_name = $course_info[0]['name'];
$course_code = $course_info[0]['code'];
$course_term = SEMESTER_MAP_REVERSE[$course_info[0]['semester']];
$course_year = $course_info[0]['year'];

// now perform the possible pairing modification functions
// first set some flags
$errorMsg = array();
$pairing_mode = NULL;
$pm_mult = 1;

// check if the survey's pairings can be modified
$stored_start_date = new DateTime($survey_info['start_date']);
$current_date = new DateTime();

if ($current_date > $stored_start_date) {
  $errorMsg['modifiable'] = 'Survey already past start date.';
}

// now perform the validation and parsing
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  // make sure values exist
  if (!isset($_POST['pairing-mode']) || !isset($_FILES['pairing-file']) || !isset($_POST['csrf-token']) || !isset($_POST['pm-mult'])) {
    http_response_code(400);
    echo "Bad Request: Missing parmeters.";
    exit();
  }

  // check CSRF token
  if (!hash_equals($instructor->csrf_token, $_POST['csrf-token'])) {
    http_response_code(403);
    echo "Forbidden: Incorrect parameters.";
    exit();
  }

  // now check for the agreement checkbox
  if (!isset($_POST['agreement'])) {
    $errorMsg['agreement'] = 'Please read the statement next to the checkbox and check it if you agree.';
  } else if ($_POST['agreement'] != "1") {
    $errorMsg['agreement'] = 'Please read the statement next to the checkbox and check it if you agree.';
  }

  // check the pairing mode
  $pairing_mode = trim($_POST['pairing-mode']);
  
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
      $pairings = getPairingResults($con, $pairing_mode, $pm_mult, $file_handle);
      if (empty($pairings)) {
        $errorMsg['pairing-mode'] = 'Please choose a valid mode for the pairing file.';
      }

      // Clean up our file handling
      fclose($file_handle);

      // Delete the old pairings from the database and then add the new pairings to the database if no other error message were set so far
      if (empty($errorMsg)) {
        $stmt = $con->prepare('DELETE FROM reviewers WHERE survey_id=?');
        $stmt->bind_param('i', $sid);
        $stmt->execute();
        add_pairings($pairings, $sid, $con);
      }
    }
  }
}

// finally, store information about survey pairings as array of array

// get information about the pairings
$stmt = $con->prepare('SELECT reviewer_email, teammate_email FROM reviewers WHERE survey_id=? ORDER BY id');
$stmt->bind_param('i', $sid);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
  $pair_info = array();
  $pair_info['reviewer_email'] = $row['reviewer_email'];
  $pair_info['teammate_email'] = $row['teammate_email'];
  $pairings[] = $pair_info;
}

// now get the names for each pairing
$stmt = $con->prepare('SELECT name FROM students WHERE email=?');

$size = count($pairings);
for ($i = 0; $i < $size; $i++) {
  $stmt->bind_param('s', $pairings[$i]['reviewer_email']);
  $stmt->execute();
  $result = $stmt->get_result();
  $data = $result->fetch_all(MYSQLI_ASSOC);
  $pairings[$i]['reviewer_name'] = $data[0]['name'];

  $stmt->bind_param('s', $pairings[$i]['teammate_email']);
  $stmt->execute();
  $result = $stmt->get_result();
  $data = $result->fetch_all(MYSQLI_ASSOC);
  $pairings[$i]['teammate_name'] = $data[0]['name'];
}
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous"></script>
  <title>CSE Evaluation Survey System - Modify Survey</title>
  <?php emitUpdateFileDescriptionFn(); ?>
</head>
<body class="text-center">
</body>
</html>
<main>
  <div class="container-fluid">
    <div class="row justify-content-md-center bg-primary mt-1 mx-1 rounded-pill">
      <div class="col-sm-auto text-center">
        <h4 class="text-white display-1">UB CSE Evalution System<br>Modify Survey</h4>
      </div>
    </div>

    <div class="row justify-content-md-center mt-5 mx-1">
      <div class="col-sm-auto text-center">
        <h4><?php echo $course_name.' ('.$course_code.')';?><br><?php echo $survey_name.' Plan'; ?></h4>
      </div>
    </div>
    <?php
      // indicate any error messages
      if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (!empty($errorMsg)) {
          echo '<p class="text-danger fs-3">Survey Pairing Modification Failed. <br> See error messages at the bottom of the page for more details.</p>';
        } else {
          echo '<p class="text-success fs-3">Survey Pairing Modification Successful.</p>';
        }
      }
    ?>
    <div class="container-sm">
      <div class="row justify-content-md-center mt-5 mx-4">
        <div class="col-auto">
          <a href="pairingDownload.php?survey=<?php echo $sid; ?>" target="_blank" class="btn btn-success fs-4">Download Pairings</a>
        </div>
      </div>
    </div>
    <div class="container-sm">
      <div class="row justify-content-md-center mt-5 mx-4">
        <table class="table table-striped table-hover">
          <thead>
            <tr>
              <th scope="col">Reviewer Name (Email)</th>
              <th scope="col">Reviewee Name (Email)</th>
            </tr>
          </thead>
          <tbody>
        <?php
          foreach ($pairings as $pair) {
            echo '<tr><td>' . htmlspecialchars($pair['reviewer_name']) . ' (' . htmlspecialchars($pair['reviewer_email']) . ')</td><td>' . htmlspecialchars($pair['teammate_name']) . ' (' . htmlspecialchars($pair['teammate_email']) . ')</td></tr>';
          }
        ?>
        </tbody>
        </table>
        </div>
      </table>
    </div>
  </div>
  <?php if (!isset($errorMsg['modifiable'])): ?>
  <div class="container-sm">
    <div class="row justify-content-md-center mt-5 mx-4">
      <div class="col-auto">
          <h4>Modify Survey</h4>
      </div>
    </div>
    <form action="surveyPairings.php?survey=<?php echo $sid; ?>" method ="post" enctype="multipart/form-data">
      <div class="cointainer-sm">
      <div class="row justify-content-md-center mt-5 mx-4">
        <div class="row mb-3">
          <?php emitSurveyTypeSelect($errorMsg, $pairing_mode, $pm_mult); ?>
        </div>
        <span id="fileFormat" style="font-size:small;color:DarkGrey"></span>
        <div class="form-floating mt-0 mb-3">
          <input type="file" id="pairing-file" class="form-control <?php if(isset($errorMsg["pairing-file"])) {echo "is-invalid ";} ?>" name="pairing-file" required></input>
          <label for="pairing-file" style="transform: scale(.85) translateY(-.85rem) translateX(.15rem);"><?php if(isset($errorMsg["pairing-file"])) {echo $errorMsg["pairing-file"]; } else { echo "Review Assignments (CSV File):";} ?></label>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="agreement" name="agreement" value="1">
          <label class="form-check-label" for="agreement">
          I understand that modifying survey pairings will overwrite all previously supplied pairings for this survey.</label>
        </div>
        <input type="hidden" name="survey" value="<?php echo $sid; ?>" />
        <input type="hidden" name="csrf-token" value="<?php echo $instructor->csrf_token; ?>" />
        <input type="submit" class="btn btn-danger" value="Modify Survey Pairings"></input>
  </div></div>
      </form>
    <?php endif; ?>
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