<?php

// TODO: This is currently not being used as changes made by MHz make this moot
//       Revisit this decision if we ever need to add students in the system

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


// query information about the requester
$con = connectToDatabase();

// try to get information about the instructor who made this request by checking the session token and redirecting if invalid
$instructor = new InstructorInfo();
$instructor->check_session($con, 0);


// check for the query string or post parameter
$cid = NULL;
if($_SERVER['REQUEST_METHOD'] == 'GET')
{
  // respond not found on no query string parameter
  if (!isset($_GET['course']))
  {
    http_response_code(404);
    echo "404: Not found.";
    exit();
  }

  // make sure the query string is an integer, reply 404 otherwise
  $cid = intval($_GET['course']);

  if ($cid === 0)
  {
    http_response_code(404);
    echo "404: Not found.";
    exit();
  }
}
else
{
  // respond bad request if bad post parameters
  if (!isset($_POST['course']))
  {
    http_response_code(400);
    echo "Bad Request: Missing parmeters.";
    exit();
  }

  // make sure the post survey id is an integer, reply 400 otherwise
  $cid = intval($_POST['course']);

  if ($cid === 0)
  {
    http_response_code(400);
    echo "Bad Request: Invalid parameters.";
    exit();
  }

}

// try to look up info about the requested course and make sure the current instructor teaches it
$stmt = $con->prepare('SELECT code, name, semester, year FROM course WHERE id=? AND instructor_id=?');
$stmt->bind_param('ii', $cid, $instructor->id);
$stmt->execute();
$result = $stmt->get_result();
$course_info = $result->fetch_all(MYSQLI_ASSOC);

// reply forbidden if instructor did not create course or course does not exist
if ($result->num_rows == 0)
{
  http_response_code(403);
  echo "403: Forbidden.";
  exit();
}

// now perform the possible roster modification functions
// first set some flags
$errorMsg = array();

// check if the course roster can be modified
$stmt = $con->prepare('SELECT id FROM surveys WHERE course_id=? LIMIT 1');
$stmt->bind_param('i', $cid);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_all(MYSQLI_ASSOC);

// check if a survey already exists for the course
if ($result->num_rows > 0)
{
  $errorMsg['modifiable'] = 'Course already has active surveys.';
}

// now perform the validation and parsing
if($_SERVER['REQUEST_METHOD'] == 'POST')
{
  // make sure values exist
  if (!isset($_FILES['roster-file']) or !isset($_POST['csrf-token']))
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
  if ($_FILES['roster-file']['error'] == UPLOAD_ERR_INI_SIZE)
  {
    $errorMsg['roster-file'] = 'The selected file is too large.';
  }
  else if ($_FILES['roster-file']['error'] == UPLOAD_ERR_PARTIAL)
  {
    $errorMsg['roster-file'] = 'The selected file was only paritally uploaded. Please try again.';
  }
  else if ($_FILES['roster-file']['error'] == UPLOAD_ERR_NO_FILE)
  {
    $errorMsg['roster-file'] = 'A roster file must be provided.';
  }
  else if ($_FILES['roster-file']['error'] != UPLOAD_ERR_OK)
  {
    $errorMsg['roster-file'] = 'An error occured when uploading the file. Please try again.';
  }
  // start parsing the file
  else
  {

    $file_string = file_get_contents($_FILES['roster-file']['tmp_name']);

    // get rid of BOM if it exists
    if (strlen($file_string) >= 3)
    {
      if ($file_string[0] == "\xef" and $file_string[1] == "\xbb" and $file_string[2] == "\xbf")
      {
        $file_string = substr($file_string, 3);
      }
    }

    // catch errors or continue parsing the file
    if ($file_string === false)
    {
      $errorMsg['roster-file'] = 'An error occured when uploading the file. Please try again.';
    }
    else
    {
      $names_emails = // TODO: FIXME parse_pairings("3", $file_string);

      // check for any errors
      if (isset($names_emails['error']))
      {
        $errorMsg['roster-file'] = $names_emails['error'];
      }
      else
      {

        // now add the roster to the database if no other errors were set after deleting the roster info first
        if (empty($errorMsg))
        {

          // first delete the old entries
          $stmt = $con->prepare('DELETE FROM roster WHERE course_id=?');
          $stmt->bind_param('i', $cid);
          $stmt->execute();

          // now insert the roster into the roster database and the student database if needed
          $roster_size = count($names_emails);

          // prepare sql statements
          $stmt_check = $con->prepare('SELECT student_id FROM students WHERE email=?');
          $stmt_news = $con->prepare('INSERT INTO students (email, name) VALUES (?, ?)');
          $stmt_checkros = $con->prepare('SELECT id FROM roster WHERE student_id=? AND course_id=?');
          $stmt_ros = $con->prepare('INSERT INTO roster (student_id, course_id) VALUES (?, ?)');

          for ($i = 0; $i < $roster_size; $i += 2)
          {

            $stmt_check->bind_param('s', $names_emails[$i + 1]);
            $stmt_check->execute();
            $result = $stmt_check->get_result();
            $student_info = $result->fetch_all(MYSQLI_ASSOC);
            $student_id = NULL;

            // check if the student already exists if they don't insert them
            if ($result->num_rows == 0)
            {
              $stmt_news->bind_param('ss', $names_emails[$i + 1], $names_emails[$i]);
              $stmt_news->execute();

              $student_id = $con->insert_id;
            }
            else
            {
              $student_id = $student_info[0]['student_id'];
            }

            // now, insert the student into the roster if they do not exist already
            $stmt_checkros->bind_param('ii', $student_id, $cid);
            $stmt_checkros->execute();
            $result = $stmt_checkros->get_result();
            $data = $result->fetch_all(MYSQLI_ASSOC);

            if ($result->num_rows == 0)
            {
              $stmt_ros->bind_param('ii', $student_id, $cid);
              $stmt_ros->execute();
            }

          }
        }
      }
    }
  }
}

// finally, store information about course roster as array of array
$students = array();

// get information about the pairings
$stmt = $con->prepare('SELECT roster.student_id, students.name, students.email FROM roster JOIN students ON roster.student_id=students.student_id WHERE roster.course_id=? ORDER BY roster.id');
$stmt->bind_param('i', $cid);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc())
{
  $stu_info = array();
  $stu_info['name'] = $row['name'];
  $stu_info['email'] = $row['email'];
  array_push($students, $stu_info);
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <link rel="stylesheet" type="text/css" href="../styles/styles.css">
    <title>Course Roster :: UB CSE Peer Evaluation System</title>
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
        <h2>Course Roster</h2>
        <p><?php echo htmlspecialchars($course_info[0]['code']) . ' ' . htmlspecialchars($course_info[0]['name']) . ' - ' . htmlspecialchars(SEMESTER_MAP_REVERSE[$course_info[0]['semester']]) . ' ' . htmlspecialchars($course_info[0]['year']) ?></p>
    </div>

    <?php
      // indicate any error messages
      if ($_SERVER['REQUEST_METHOD'] == 'POST')
      {
        if (!empty($errorMsg))
        {
          echo '<div class="w3-container w3-center w3-red">Course Roster Modification Failed. <br /> See error messages at the bottom of the page for more details.</div><br />';
        }
        else
        {
          echo '<div class="w3-container w3-center w3-green">Course Roster Modification Successful</div><br />';
        }
      }
    ?>
    <div class="w3-container w3-center">
        <h2>Update Course Roster</h2>
    </div>
    <form action="courseRoster.php?course=<?php echo $cid; ?>" method ="post" enctype="multipart/form-data" class="w3-container">
        <span class="w3-card w3-red"><?php if(isset($errorMsg["roster-file"])) {echo $errorMsg["roster-file"];} ?></span><br />
        <label for="roster-file">Roster (CSV File):</label><br>
        <input type="file" id="roster-file" class="w3-input w3-border" style="width:61%" name="roster-file"><br>

        <span class="w3-card w3-red"><?php if(isset($errorMsg["agreement"])) {echo $errorMsg["agreement"];} ?></span><br />
        <input type="checkbox" id="agreement" name="agreement" value="1">
        <label for="agreement">I understand that modifying the course roster will overwrite all previously supplied roster information for this course.</label><br /><br />

        <input type="hidden" name="course" value="<?php echo $cid; ?>" />

        <input type="hidden" name="csrf-token" value="<?php echo $instructor->csrf_token; ?>" />

        <input type="submit" class="w3-button w3-green" value="Modify Course Roster" />
    </form>
</div>
</body>
</html>
