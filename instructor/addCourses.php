<?php

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
require_once "../lib/infoClasses.php";
require_once "../lib/fileParse.php";


//query information about the requester
$con = connectToDatabase();

//try to get information about the instructor who made this request by checking the session token and redirecting if invalid
$instructor = new InstructorInfo();
$instructor->check_session($con, 0);

//stores error messages corresponding to form fields
$errorMsg = array();

// set flags
$course_code = NULL;
$course_name = NULL;
$semester = NULL;
$course_year = NULL;
$roster_file = NULL;

if($_SERVER['REQUEST_METHOD'] == 'POST') {

  // make sure values exist
  if (!isset($_POST['course-code']) || !isset($_POST['course-name']) || !isset($_POST['course-year']) || !isset($_FILES['roster-file']) || !isset($_POST['csrf-token']) ||
      !isset($_POST['semester'])) {
    http_response_code(400);
    echo "Bad Request: Missing parmeters.";
    exit();
  }

  // check CSRF token
  if (!hash_equals($instructor->csrf_token, $_POST['csrf-token']) || !is_uploaded_file($_FILES['file']['tmp_name'])) {
    http_response_code(403);
    echo "Forbidden: Incorrect parameters.";
    exit();
  }

  //check valid formatting
  $course_code = trim($_POST['course-code']);
  if (empty($course_code)) {
    $errorMsg['course-code'] = 'Course code cannot be blank.';
  } else if (!ctype_print($course_code)) {
    $errorMsg["course-code"] = "Course code cannot contain unprintable characters.";
  }

  $course_name = trim($_POST['course-name']);
  if (empty($course_name)) {
    $errorMsg['course-name'] = 'Course name cannot be blank.';
  } else if (!ctype_print($course_name)) {
    $errorMsg["course-name"] = "Course name cannot contain unprintable characters.";
  }

  $semester = trim($_POST['semester']);
  if (empty($semester)) {
    $errorMsg['semester'] = 'Please choose a semester.';
  } else if ($semester != "fall" and $semester != "winter" and $semester != "spring" and $semester != "summer") {
    //Prevent injections into 'semester' field
    $errorMsg["semester"] = "Please select a valid semester.";
  }

  $semester = SEMESTER_MAP[$semester];

  $course_year = trim($_POST['course-year']);
  if (empty($course_year))
  {
    $errorMsg['course-year'] = 'Course year cannot be blank.';
  }
  else if(!ctype_digit($course_year) || strlen($course_year) != 4)
  {
    $errorMsg["course-year"] = "Please enter a valid 4-digit year.";
  }

  // now validate the roster file
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

    $file_handle = @fopen($_FILES['roster-file']['tmp_name'], "r");

    // catch errors or continue parsing the file
    if (!$file_handle) {
      $errorMsg['roster-file'] = 'An error occured when uploading the file. Please try again.';
    } else {
      $names_emails = parse_roster_file($file_handle);

      // Clean up our file handling
      fclose($file_handle);

      // check for any errors
      if (isset($names_emails['error'])) {
        $errorMsg['roster-file'] = $names_emails['error'];
      } else {
        // now add the roster to the database if no other errors were set after adding the course to the database
        if (empty($errorMsg)) {
          // check for duplicate courses
          $stmt = $con->prepare('SELECT id FROM course WHERE code=? AND name=? AND semester=? AND year=? AND instructor_id=?');
          $stmt->bind_param('ssiii', $course_code, $course_name, $semester, $course_year, $instructor->id);
          $stmt->execute();
          $result = $stmt->get_result();
          $data = $result->fetch_all(MYSQLI_ASSOC);

          // only add if not a duplicate
          if ($result->num_rows == 0) {
            $stmt = $con->prepare('INSERT INTO course (code, name, semester, year, instructor_id) VALUES (?, ?, ?, ?, ?)');
            $stmt->bind_param('ssiii', $course_code, $course_name, $semester, $course_year, $instructor->id);
            $stmt->execute();

            // get the inserted course id
            $course_id = $con->insert_id;


            // now insert the roster into the roster database and the student database if needed
            $roster_size = count($names_emails);

            // prepare sql statements
            $stmt_check = $con->prepare('SELECT student_id FROM students WHERE email=?');
            $stmt_news = $con->prepare('INSERT INTO students (email, name) VALUES (?, ?)');

            for ($i = 0; $i < $roster_size; $i ++) {
              $stmt_check->bind_param('s', $names_emails[$i][1]);
              $stmt_check->execute();
              $result = $stmt_check->get_result();
              $student_info = $result->fetch_all(MYSQLI_ASSOC);
              $student_id = NULL;

              // check if the student already exists if they don't insert them
              if ($result->num_rows == 0)
              {
                $stmt_news->bind_param('ss', $names_emails[$i][1], $names_emails[$i][0]);
                $stmt_news->execute();

                $student_id = $con->insert_id;
              }
              else
              {
                $student_id = $student_info[0]['student_id'];
              }
            }

            // redirect to course page with message
            $_SESSION['course-add'] = "Successfully added course: " . htmlspecialchars($course_code) . ' - ' . htmlspecialchars($course_name) . ' - ' . SEMESTER_MAP_REVERSE[$semester] . ' ' . htmlspecialchars($course_year);

            http_response_code(302);
            header("Location: courses.php");
            exit();

          }
          else
          {
            $errorMsg['duplicate'] = 'Error: The entered course already exists.';
          }
        }
      }
    }
  }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <link rel="stylesheet" type="text/css" href="../styles/styles.css">
    <title>Add New Course :: UB CSE Peer Evaluation System</title>
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
      <form action="logout.php" method ="post"><input type="hidden" name="csrf-token" value="<?php echo $instructor->csrf_token; ?>" />
        <input class="w3-bar-item w3-button w3-mobile w3-right w3-border-right w3-border-left w3-border-white" type="submit" value="Logout"></form>
      <span class="w3-bar-item w3-mobile w3-right">Welcome, <?php echo htmlspecialchars($instructor->name); ?></span>
    </div>
  </header>
<div class="main-content">


    <div class="w3-container w3-center">
        <h2>Add New Course</h2>
    </div>

<span class="w3-card w3-red"><?php if(isset($errorMsg["duplicate"])) {echo $errorMsg["duplicate"];} ?></span>
<form action="addCourses.php" method ="post" enctype="multipart/form-data" style="width:60%" class="w3-container w3-mobile">
    <span class="w3-card w3-red"><?php if(isset($errorMsg["course-code"])) {echo $errorMsg["course-code"];} ?></span><br />
    <label for="course-code">Course Code:</label><br>
    <input type="text" id="course-code" class="w3-input w3-border" name="course-code" placeholder="e.g, CSE442" <?php if ($course_code) {echo 'value="' . htmlspecialchars($course_code) . '"';} required ?>><br>


    <span class="w3-card w3-red"><?php if(isset($errorMsg["course-name"])) {echo $errorMsg["course-name"];} ?></span><br />
    <label for="course-name">Course Name:</label><br>
    <input type="text" id="course-name" class="w3-input w3-border" name="course-name" placeholder="e.g, Software Engineering Concepts" <?php if ($course_name) {echo 'value="' . htmlspecialchars($course_name) . '"';} required ?>><br>

    <span class="w3-card w3-red"><?php if(isset($errorMsg["semester"])) {echo $errorMsg["semester"];} ?></span><br />
    <label for="semester">Course Semester:</label><br>
    <select class="w3-select w3-border" name="semester">
        <option value="" disabled <?php if (!$semester) {echo 'selected';} ?>>Choose semester:</option>
        <option value="fall" <?php if ($semester == 4) {echo 'selected';} ?>>Fall</option>
        <option value="winter" <?php if ($semester == 1) {echo 'selected';} ?>>Winter</option>
        <option value="spring" <?php if ($semester == 2) {echo 'selected';} ?>>Spring</option>
        <option value="summer" <?php if ($semester == 3) {echo 'selected';} ?>>Summer</option>
    </select><br><br>

    <span class="w3-card w3-red"><?php if(isset($errorMsg["course-year"])) {echo $errorMsg["course-year"];} ?></span><br />
    <label for="year">Course Year:</label><br>
    <input type="number" id="year" class="w3-input w3-border" name="course-year" placeholder="e.g, 2020" <?php if ($course_year) {echo 'value="' . htmlspecialchars($course_year) . '"';} required ?>><br>

    <span class="w3-card w3-red"><?php if(isset($errorMsg["roster-file"])) {echo $errorMsg["roster-file"];} ?></span><br />
    <label for="roster-file">Roster (CSV File):</label><br>
    <input type="file" id="roster-file" class="w3-input w3-border" name="roster-file" required><br><br />

    <input type="hidden" name="csrf-token" value="<?php echo $instructor->csrf_token; ?>" />

    <input class="w3-button w3-green" type="submit" value="Add Course" />
</form>
</div>

</body>
</html>
