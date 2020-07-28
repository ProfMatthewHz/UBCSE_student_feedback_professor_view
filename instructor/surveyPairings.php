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
if($_SERVER['REQUEST_METHOD'] == 'GET')
{
  // respond not found on no query string parameter
  if (!isset($_GET['survey']))
  {
    http_response_code(404);
    echo "404: Not found.";
    exit();
  }

  // make sure the query string is an integer, reply 404 otherwise
  $sid = intval($_GET['survey']);

  if ($sid === 0)
  {
    http_response_code(404);
    echo "404: Not found.";
    exit();
  }
}
else
{
  // respond bad request if bad post parameters
  if (!isset($_POST['survey']))
  {
    http_response_code(400);
    echo "Bad Request: Missing parmeters.";
    exit();
  }

  // make sure the post survey id is an integer, reply 400 otherwise
  $sid = intval($_POST['survey']);

  if ($sid === 0)
  {
    http_response_code(400);
    echo "Bad Request: Invalid parameters.";
    exit();
  }

}

// try to look up info about the requested survey
$survey_info = array();

$stmt = $con->prepare('SELECT course_id, start_date, expiration_date, rubric_id FROM surveys WHERE id=?');
$stmt->bind_param('i', $sid);
$stmt->execute();
$result = $stmt->get_result();

$survey_info = $result->fetch_all(MYSQLI_ASSOC);

// reply not found on no match
if ($result->num_rows == 0)
{
  http_response_code(404);
  echo "404: Not found.";
  exit();
}

// make sure the survey is for a course the current instructor actually teaches
$stmt = $con->prepare('SELECT year FROM course WHERE id=? AND instructor_id=?');
$stmt->bind_param('ii', $survey_info[0]['course_id'], $instructor->id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_all(MYSQLI_ASSOC);

// reply forbidden if instructor did not create survey
if ($result->num_rows == 0)
{
  http_response_code(403);
  echo "403: Forbidden.";
  exit();
}

// now perform the possible pairing modification functions
// first set some flags
$errorMsg = array();
$pairing_mode = NULL;

// check if the survey's pairings can be modified
$stored_start_date = new DateTime($survey_info[0]['start_date']);
$current_date = new DateTime();

if ($current_date > $stored_start_date)
{
  $errorMsg['modifiable'] = 'Survey already past start date.';
}

// now perform the validation and parsing
if($_SERVER['REQUEST_METHOD'] == 'POST')
{
  // make sure values exist
  if (!isset($_POST['pairing-mode']) || !isset($_FILES['pairing-file']) || !isset($_POST['csrf-token']))
  {
    http_response_code(400);
    echo "Bad Request: Missing parmeters.";
    exit();
  }

  // check CSRF token
  if (!hash_equals($instructor->csrf_token, $_POST['csrf-token']))
  {
    http_response_code(403);
    echo "Forbidden: Incorrect parameters.";
    exit();
  }

  // check the pairing mode
  $pairing_mode = trim($_POST['pairing-mode']);
  if (empty($pairing_mode))
  {
    $errorMsg['pairing-mode'] = 'Please choose a valid mode for the pairing file.';
  }
  else if ($pairing_mode != '1' and $pairing_mode != '2')
  {
    $errorMsg['pairing-mode'] = 'Please choose a valid mode for the pairing file.';
  }

  // now check for the agreement checkbox
  if (!isset($_POST['agreement']))
  {
    $errorMsg['agreement'] = 'Please read the statement next to the checkbox and check it if you agree.';
  }
  else if ($_POST['agreement'] != "1")
  {
    $errorMsg['agreement'] = 'Please read the statement next to the checkbox and check it if you agree.';
  }

  // validate the uploaded file
  if ($_FILES['pairing-file']['error'] == UPLOAD_ERR_INI_SIZE)
  {
    $errorMsg['pairing-file'] = 'The selected file is too large.';
  }
  else if ($_FILES['pairing-file']['error'] == UPLOAD_ERR_PARTIAL)
  {
    $errorMsg['pairing-file'] = 'The selected file was only paritally uploaded. Please try again.';
  }
  else if ($_FILES['pairing-file']['error'] == UPLOAD_ERR_NO_FILE)
  {
    $errorMsg['pairing-file'] = 'A pairing file must be provided.';
  }
  else if ($_FILES['pairing-file']['error'] != UPLOAD_ERR_OK)
  {
    $errorMsg['pairing-file'] = 'An error occured when uploading the file. Please try again.';
  } else {
    // start parsing the file
    $file_handle = @fopen($_FILES['pairing-file']['tmp_name'], "r");

    // catch errors or continue parsing the file
    if (!$file_handle) {
      $errorMsg['pairing-file'] = 'An error occured when uploading the file. Please try again.';
    }
    else {
      if ($pairing_mode == '1') {
        $pairings = parse_review_pairs($file_handle, $con);
      } else {
        $pairings = parse_review_teams($file_handle, $con);
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
$pairings = array();

// get information about the pairings
$stmt = $con->prepare('SELECT reviewer_email, teammate_email FROM reviewers WHERE survey_id=? ORDER BY id');
$stmt->bind_param('i', $sid);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc())
{
  $pair_info = array();
  $pair_info['reviewer_email'] = $row['reviewer_email'];
  $pair_info['teammate_email'] = $row['teammate_email'];
  $pairings[] = $pair_info;
}

// now get the names for each pairing
$stmt = $con->prepare('SELECT name FROM students WHERE email=?');

$size = count($pairings);
for ($i = 0; $i < $size; $i++)
{
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
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <link rel="stylesheet" type="text/css" href="../styles/styles.css">
    <title>Survey Pairings :: UB CSE Peer Evaluation System</title>
</head>
<body>
<header>
    <div class="w3-container">
          <img src="../images/logo_UB.png" class="header-img" alt="UB Logo">
          <h1 class="header-text">UB CSE Peer Evaluation System</h1>
    </div>
    <div class="w3-bar w3-blue w3-mobile w3-border-blue">
      <a href="surveys.php" class="w3-bar-item w3-button w3-mobile w3-border-right w3-border-left w3-border-white">Surveys</a>
      <a href="courses.php" class="w3-bar-item w3-button w3-mobile w3-border-right w3-border-white">Courses</a>
      <form action="logout.php" method ="post"><input type="hidden" name="csrf-token" value="<?php echo $instructor->csrf_token; ?>" /><input class="w3-bar-item w3-button w3-mobile w3-right w3-border-right w3-border-left w3-border-white" type="submit" value="Logout"></form>
      <span class="w3-bar-item w3-mobile w3-right">Welcome, <?php echo htmlspecialchars($instructor->name); ?></span>
    </div>
</header>
<div class="main-content">
    <div class="w3-container w3-center">
        <h2>Survey Pairings</h2>
    </div>

    <?php
      // indicate any error messages
      if ($_SERVER['REQUEST_METHOD'] == 'POST')
      {
        if (!empty($errorMsg))
        {
          echo '<div class="w3-container w3-center w3-red">Survey Pairing Modification Failed. <br /> See error messages at the bottom of the page for more details.</div><br />';
        }
        else
        {
          echo '<div class="w3-container w3-center w3-green">Survey Pairing Modification Successful</div><br />';
        }
      }
    ?>

    <table class="w3-table w3-mobile w3-centered" border=1.0 style="width:100%">
      <tr>
      <th>Reviewer Name (Email)</th>
      <th>Reviewee Name (Email)</th>
      </tr>
      <?php
        foreach ($pairings as $pair)
        {
          echo '<tr><td>' . htmlspecialchars($pair['reviewer_name']) . ' (' . htmlspecialchars($pair['reviewer_email']) . ')</td><td>' . htmlspecialchars($pair['teammate_name']) . ' (' . htmlspecialchars($pair['teammate_email']) . ')</td></tr>';
        }
      ?>
    </table>
    <br />
    <div class="w3-container w3-center">
      <a href="pairingDownload.php?survey=<?php echo $sid; ?>" target="_blank"><button class="w3-button w3-blue">Download Pairings as CSV File</button></a>
    </div>
    <hr />
    <div class="w3-container w3-center">
        <h2>Modify Survey Pairings</h2>
    </div>
    <?php if (!isset($errorMsg['modifiable'])): ?>
      <form action="surveyPairings.php?survey=<?php echo $sid; ?>" method ="post" enctype="multipart/form-data" class="w3-container">
        <span class="w3-card w3-red"><?php if(isset($errorMsg["pairing-mode"])) {echo $errorMsg["pairing-mode"];} ?></span><br />
        <label for="pairing-mode">Pairing File Mode:</label><br>
        <select id="pairing-mode" class="w3-select w3-border" style="width:61%" name="pairing-mode">
            <option value="1" <?php if (!$pairing_mode) {echo 'selected';} ?>>Raw</option>
            <option value="2" <?php if ($pairing_mode == 2) {echo 'selected';} ?>>Team</option>
        </select><br><br>

        <span class="w3-card w3-red"><?php if(isset($errorMsg["pairing-file"])) {echo $errorMsg["pairing-file"];} ?></span><br />
        <label for="pairing-file">Pairings (CSV File):</label><br>
        <input type="file" id="pairing-file" class="w3-input w3-border" style="width:61%" name="pairing-file"><br>

        <span class="w3-card w3-red"><?php if(isset($errorMsg["agreement"])) {echo $errorMsg["agreement"];} ?></span><br />
        <input type="checkbox" id="agreement" name="agreement" value="1">
        <label for="agreement">I understand that modifying survey pairings will overwrite all previously supplied pairings for this survey. In addition, any scores associated with these prior pairings will be lost.</label><br /><br />

        <input type="hidden" name="survey" value="<?php echo $sid; ?>" />

        <input type="hidden" name="csrf-token" value="<?php echo $instructor->csrf_token; ?>" />

        <input type="submit" class="w3-button w3-green" value="Modify Survey Pairings" />
      </form>
    <?php else: ?>
      <div class="w3-container w3-red">
        <p>Survey pairings cannot be modified once the survey's start date has passed.</p>
      </div>
    <?php endif; ?>
    <br />
</div>
</body>
</html>
