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
  if (!hash_equals($instructor->csrf_token, $_POST['csrf-token']) || !is_uploaded_file($_FILES['roster-file']['tmp_name'])) {
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
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous"></script>
  <title>CSE Evaluation Survey System - Add Course</title>
</head>
<body class="text-center">
<!-- Header -->
<main>
  <div class="container-fluid">
    <div class="row justify-content-md-center bg-primary mt-1 mx-1 rounded-pill">
      <div class="col-sm-auto text-center">
        <h4 class="text-white display-1">UB CSE Evalution System<br>Create New Course</h4>
      </div>
    </div>
    <form class="mt-5 mx-4" id="add-course" method="post" enctype="multipart/form-data">
      <p class="text-danger fs-3"><?php if(isset($errorMsg["duplicate"])) {echo $errorMsg["duplicate"];} ?></p>
      <div class="form-inline justify-content-center align-items-center">
        <div class="form-floating mb-3">
          <input type="text" id="course-code" class="form-control <?php if(isset($errorMsg["course-code"])) {echo "is-invalid ";} ?>" name="course-code" required placeholder="e.g, CSE442" value="<?php if ($course_code) {echo htmlspecialchars($course_code);} ?>"></input>
          <label for="course-code">Course Code:</label>
        </div>
        <div class="form-floating mb-3">
          <input type="text" id="course-name" class="form-control <?php if(isset($errorMsg["course-name"])) {echo "is-invalid ";} ?>" name="course-name" required placeholder="e.g, Software Engineering Concepts" value="<?php if ($course_name) {echo htmlspecialchars($course_name);} ?>"></input>
          <label for="course-name">Course Name:</label>
        </div>
        <div class="form-floating mb-3">
          <select class="form-select <?php if(isset($errorMsg["semester"])) {echo "is-invalid ";} ?>" id="semester" name="semester">
            <option value="" disabled <?php if (!$semester) {echo 'selected';} ?>>Choose semester:</option>
            <option value="winter" <?php if ($semester == 1) {echo 'selected';} ?>>Winter</option>
            <option value="spring" <?php if ($semester == 2) {echo 'selected';} ?>>Spring</option>
            <option value="summer" <?php if ($semester == 3) {echo 'selected';} ?>>Summer</option>
            <option value="fall" <?php if ($semester == 4) {echo 'selected';} ?>>Fall</option>
          </select>
          <label for="semester"><?php if(isset($errorMsg["semester"])) {echo $errorMsg["semester"]; } else { echo "Semester:";} ?></label>
        </div>
        <div class="form-floating mb-3">
          <input type="number" id="course-year" class="form-control <?php if(isset($errorMsg["course-year"])) {echo "is-invalid ";} ?>" name="course-year" required placeholder="e.g, 2020" value="<?php if ($course_year) {echo htmlspecialchars($course_year);} ?>"></input>
          <label for="course-year">Course Year:</label>
        </div>

        <span style="font-size:small;color:DarkGrey">File needs 2 columns per row: <tt>name</tt>, <tt>email address</tt></span>
        <div class="form-floating mt-0 mb-3">
          <input type="file" id="roster-file" class="form-control <?php if(isset($errorMsg["roster-file"])) {echo "is-invalid ";} ?>" name="roster-file" required></input>
          <label for="roster-file" style="transform: scale(.85) translateY(-.85rem) translateX(.15rem);"><?php if(isset($errorMsg["roster-file"])) {echo $errorMsg["roster-file"]; } else { echo "Roster (CSV File):";} ?></label>
        </div>

    <input type="hidden" name="csrf-token" value="<?php echo $instructor->csrf_token; ?>" />

    <input class="btn btn-success" type="submit" value="Create Course" />
    </div>
</form>
</div>
          </main>
</body>
</html>
