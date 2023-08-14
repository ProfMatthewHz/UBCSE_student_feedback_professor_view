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
require_once "../lib/surveyQueries.php";
require_once "lib/surveyQueries.php";
require_once "lib/courseQueries.php";
require_once "lib/resultsCalculations.php";
require_once "lib/resultsFunctions.php";


// query information about the requester
$con = connectToDatabase();

//try to get information about the instructor who made this request by checking the session token and redirecting if invalid
if (!isset($_SESSION['id'])) {
  http_response_code(403);
  echo "Forbidden: You must be logged in to access this page.";
  exit();
}
$instructor_id = $_SESSION['id'];

// respond not found on no query string parameter
$survey_id = NULL;
if (!isset($_GET['survey'])) {
  http_response_code(404);
  echo "404: Not found.";
  exit();
}

// make sure the query string is an integer, reply 404 otherwise
$survey_id = intval($_GET['survey']);

if ($survey_id === 0) {
  http_response_code(404);
  echo "404: Not found.";
  exit();
}

// Look up info about the requested survey
$survey_info = getSurveyData($con, $survey_id);
if (empty($survey_info)) {
  http_response_code(404);
  echo "404: Not found.";
  exit();
}
$survey_name = $survey_info['name'];

// Get data for this single course
$course_info = getSingleCourseInfo($con, $survey_info['course_id'], $instructor_id);
// reply forbidden if instructor did not create survey or the course is ambiguous
if (empty($course_info)) {
  http_response_code(403);
  echo "403: Forbidden.";
  exit();
}
$course_name = $course_info['name'];
$course_code = $course_info['code'];
$course_term = SEMESTER_MAP_REVERSE[$course_info['semester']];
$course_year = $course_info['year'];

// Retrieves the ids, names, & emails of everyone who was reviewed in this survey.
$teammates = getReviewedData($con, $survey_id);

// Get the survey results organized by the student being reviewed since this is how we actually do our calculations
$scores = getSurveyScores($con, $survey_id, $teammates);

// Averages only exist for multiple-choice topics, so that is all we get for now
$topics = getSurveyMultipleChoiceTopics($con, $survey_id);

// Retrieves the ids, names, & emails of everyone who was a reviewer in this survey.
$reviewers = getReviewerData($con, $survey_id);

// Retrieves the per-team records organized by reviewer
$team_data = getReviewerPerTeamResults($con, $survey_id);

// Calculate the per-topic averages for each student
$averages = calculateAverages(array_keys($teammates), $scores, $topics);

// Now generate the array containing each *reviewers* difference from the mean review of their group.
$differences = getReviewerReviewResults($reviewers, $scores, $topics, $team_data);
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">
	<link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous"></script>
  <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
  <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
  <title>CSE Evaluation Survey System - Survey Results</title>
</head>
<body class="text-center">
<!-- Header -->
<main>
  <div class="container-fluid">
    <div class="row justify-content-md-center bg-primary mt-1 mx-1 rounded-pill">
      <div class="col-sm-auto text-center">
        <h4 class="text-white display-1">UB CSE Evalution System<br>Survey Results Review</h4>
      </div>
    </div>

    <div class="row justify-content-md-center mt-5 mx-4">
      <div class="col-sm-auto text-center">
        <h4><?php echo $course_name.' ('.$course_code.')';?><br><?php echo $survey_name.' Survey Results Review'; ?></h4>
      </div>
    </div>
  </div>
  <div class="container-fluid">
    <div class="row justify-content-md-center mt-5 mx-4">
      <ul id="results-present" class="nav nav-pills nav-fill" role="tablist">
        <li class="nav-item">
          <a class="nav-link active" id="averages-pill" data-bs-toggle="tab" data-bs-target="#averages" role="tab" aria-controls="averages" aria-selected="true">Reviewer Results</a>
        </li>
      </ul>
      <div id="results-tabs" class="tab-content border mt-2">
        <div class="tab-pane active show mt-2" id="averages" role="tabpanel" aria-labelledby="averages-pill">
          <div class="row justify-content-center mt-1">
            <table class="table table-striped table-hover text-start align-middle" id="individualTable">
              <thead>
                <tr>
                  <th score="col">Reviewer Name (Email)</th>
                  <th scope="col">Review</th>
                  <th scope="col">Average Difference from Group Normalized Totals</th>
                </tr>
              </thead>
              <tbody>
                <?php
                  foreach ($differences as $key=>$result) {
                    echo '<tr>';
                    echo '<td>'.htmlspecialchars($key).'</td>';
                    echo '<td>';
                    echo '<table class="table table-striped table-hover text-start align-middle">';
                    echo '<thead><tr>';
                    echo '<th scope="col">Reviewee</th>';
                    foreach ($topics as $topic_id=>$topic_name) {
                      echo '<th scope="col">'.htmlspecialchars($topic_name).'</th>';
                    }
                    echo '<th>NORMALIZED</th>';
                    echo '</tr></thead>';
                    echo '<tbody>';
                    // foreach ()
                    echo '</table></td>';
                    echo '<td>'.$result.'</td>';
                    echo '</tr>';
                  }
                ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
  </div>
  <hr>
		<div class="row mx-1 mt-2 justify-content-center">
        <div class="col-auto">
					<a href="surveys.php" class="btn btn-outline-info" role="button" aria-disabled="false">Return to Instructor Home</a>
        </div>
      </div>
</div>
<script>
    $(document).ready(function () {
      $('#individualTable').DataTable({
        columnDefs: [ { orderable: false, targets: 1 }],
        order: [[2, 'desc']]
      });
    });
  </script>
</main>
</body>
</html>
