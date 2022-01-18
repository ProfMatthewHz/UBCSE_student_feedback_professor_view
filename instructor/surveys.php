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
require_once "lib/termPresentation.php";

// set timezone
date_default_timezone_set('America/New_York');

// query information about the requester
$con = connectToDatabase();

// try to get information about the instructor who made this request by checking the session token and redirecting if invalid
$instructor = new InstructorInfo();
$instructor->check_session($con, 0);

// Find out the term that we are currently in
$month = idate('m');
$term = MONTH_MAP_SEMESTER[$month];
$year = idate('Y');

// store information about courses based on the terms. The value for each term will be a map of course ids to courses
$terms = array();

// get information about all courses an instructor teaches in priority order
$stmt1 = $con->prepare('SELECT name, semester, year, code, id FROM course WHERE instructor_id=? ORDER BY year DESC, semester DESC, code DESC');
$stmt1->bind_param('i', $instructor->id);
$stmt1->execute();
$result1 = $stmt1->get_result();

while ($row = $result1->fetch_assoc()) {
  $tempSurvey = array();
  $tempSurvey['name'] = $row['name'];
  $tempSurvey['semester'] = SEMESTER_MAP_REVERSE[$row['semester']];
  $tempSurvey['year'] = $row['year'];
  $tempSurvey['code'] = $row['code'];
  $tempSurvey['id'] = $row['id'];
  // If this course is current or in the future, we can create new surveys for it
  $tempSurvey['mutable'] = ($tempSurvey['year'] >= $year) && ($row['semester'] >= $term);
  // Create the arrays we will need for later
  $tempSurvey['upcoming'] = array();
  $tempSurvey['active'] = array();
  $tempSurvey['expired'] = array();
  $term_name = $tempSurvey['year']." ".$tempSurvey['semester'];
  $term_courses = null;
  if (array_key_exists($term_name, $terms)) {
    $term_courses = $terms[$term_name];
  } else {
    $term_courses = array();
  }
  $term_courses[$tempSurvey['id']] = $tempSurvey;
  $terms[$term_name] = $term_courses;
}

// get today's date
$today = new DateTime();

// Now get data on all of the surveys in each of those courses
foreach ($terms as $name => &$term_courses) {
  foreach($term_courses as $id => &$course) {
    // Get the course's surveys in reverse chronological order
    $stmt2 = $con->prepare('SELECT name, start_date, expiration_date, rubric_id, id FROM surveys WHERE course_id=? ORDER BY start_date DESC, expiration_date DESC');
    $stmt2->bind_param('i', $id);
    $stmt2->execute();
    $result2 = $stmt2->get_result();

    while ($row = $result2->fetch_assoc()) {
        $survey_info = array();
        $survey_info['course_id'] = $id;
        $survey_info['name'] = $row['name'];
        $survey_info['start_date'] = $row['start_date'];
        $survey_info['expiration_date'] = $row['expiration_date'];
        $survey_info['rubric_id'] = $row['rubric_id'];
        $survey_info['id'] = $row['id'];

        // Determine the completion rate of the survey
        $stmt_total = $con->prepare('SELECT COUNT(reviewers.id) AS total, COUNT(evals.id) AS completed 
                                    FROM reviewers 
                                    LEFT JOIN evals on evals.reviewers_id=reviewers.id
                                    WHERE survey_id=?');
        $stmt_total->bind_param('i', $survey_info['id']);
        $stmt_total->execute();
        $result_total = $stmt_total->get_result();
        $data_total = $result_total->fetch_all(MYSQLI_ASSOC);

        // Generate and store that progress as text
        $percentage = 0;
        if ($data_total[0]['total'] != 0) {
          $percentage = floor(($data_total[0]['completed'] / $data_total[0]['total']) * 100);
        }
        $survey_info['completion'] = $data_total[0]['completed'] . '/' . $data_total[0]['total'] . '<br />(' . $percentage . '%)';

        // determine status of survey. then adjust dates to more friendly format
        $s = new DateTime($survey_info['start_date']);
        $e = new DateTime($survey_info['expiration_date']);
        $survey_info['sort_start_date'] = $survey_info['start_date'];
        $survey_info['sort_expiration_date'] = $survey_info['expiration_date'];
        $survey_info['start_date'] = $s->format('M j').' at '. $s->format('g:i A');
        $survey_info['expiration_date'] = $e->format('M j').' at '. $e->format('g:i A');

        if ($today < $s) {
          $course['upcoming'][] = $survey_info;
        } else if ($today < $e) {
          $course['active'][] = $survey_info;
        } else {
          $course['expired'][] = $survey_info;
        }
      }
    }
    unset($course);
  }
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous"></script>
  <title>CSE Evaluation Survey System - Instuctor Overview</title>
</head>
<body class="text-center">
<!-- Header -->
<main>
  <div class="container-fluid">
    <div class="row justify-content-md-center bg-primary mt-1 mx-1 rounded-pill">
      <div class="col-sm-auto text-center">
        <h4 class="text-white display-1">UB CSE Evalution System<br>Instructor Overview</h4>
      </div>
    </div>
    <div class="row mt-5 mx-4">
    <div class="col">
        <a href="rubricReview.php" class="btn btn-outline-secondary btn-lg">View Existing Rubrics</a>
      </div>
      <div class="col ms-auto">
        <a href="courseAdd.php" class="btn btn-success btn-lg">+ Add Class</a>
      </div>
      <div class="col ms-auto">
        <a href="rubricAdd.php" class="btn btn-outline-secondary btn-lg">+ Add Rubric</a>
      </div>
    </div>
    <div class="row justify-content-md-center mt-5 mx-4">
      <div class="accordion" id="surveys">
        <?php
        $counter = 0;
        foreach ($terms as $name => $course_list) {
          emit_term($counter,$name, $course_list);
          $counter++;
        }
        ?>
      </div>
    </div>
  </div>
</main>
</body>
</html>