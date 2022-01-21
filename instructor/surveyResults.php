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
require_once "../lib/surveyQueries.php";


// query information about the requester
$con = connectToDatabase();

// try to get information about the instructor who made this request by checking the session token and redirecting if invalid
$instructor = new InstructorInfo();
$instructor->check_session($con, 0);


// respond not found on no query string parameter
$sid = NULL;
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

// try to look up info about the requested survey
$survey_info = array();

$stmt = $con->prepare('SELECT course_id, name, start_date, expiration_date, rubric_id FROM surveys WHERE id=?');
$stmt->bind_param('i', $sid);
$stmt->execute();
$result = $stmt->get_result();
$survey_info = $result->fetch_all(MYSQLI_ASSOC);

// reply not found on no match
if ($result->num_rows != 1) {
  http_response_code(404);
  echo "404: Not found.";
  exit();
}
$survey_name = $survey_info[0]['name'];


// make sure the survey is for a course the current instructor actually teaches
$stmt = $con->prepare('SELECT code, name, semester, year FROM course WHERE id=? AND instructor_id=?');
$stmt->bind_param('ii', $survey_info[0]['course_id'], $instructor->id);
$stmt->execute();
$result = $stmt->get_result();
$course_info = $result->fetch_all(MYSQLI_ASSOC);

// reply forbidden if instructor did not create survey or the course is ambiguous
if ($result->num_rows != 1) {
  http_response_code(403);
  echo "403: Forbidden.";
  exit();
}
$course_name = $course_info[0]['name'];
$course_code = $course_info[0]['code'];
$course_term = SEMESTER_MAP_REVERSE[$course_info[0]['semester']];
$course_year = $course_info[0]['year'];

// TODO: Refactor this code so I do not need to duplicate it on download

// This wil be an array of arrays organized by the person BEING REVIEWED.
$scores = array();
// Array mapping email to total number of points
$totals = array();
// Array mapping email addresses to names
$emails = array();
// Array mapping email address to normalized results
$averages = array();

$stmt = $con->prepare('SELECT reviewer_email, students.name, SUM(rubric_scores.score) total_score, COUNT(DISTINCT teammate_email) expected, COUNT(DISTINCT evals.id) actual
                       FROM reviewers INNER JOIN students ON reviewers.reviewer_email=students.email LEFT JOIN evals ON evals.reviewers_id=reviewers.id LEFT JOIN scores2 ON scores2.eval_id=evals.id LEFT JOIN rubric_scores ON rubric_scores.id=scores2.score_id WHERE survey_id=? GROUP BY reviewer_email, students.name');
$stmt->bind_param('i', $sid);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_array(MYSQLI_NUM)) {
  $email_addr = $row[0];
  $emails[$email_addr] = $row[1];
  $scores[$email_addr] = array();
  // If the reviewer completed this survey
  if ($row[3] == $row[4]) {
    // Initialize the total number of points
    $totals[$email_addr] = $row[2] / $row[3];
  }
}
$stmt->close();

// Get information completed by the reviewer -- how many were reviewed and the total points
$stmt_scores = $con->prepare('SELECT reviewer_email, teammate_email, topic_id, score 
                              FROM reviewers
                              LEFT JOIN evals on evals.reviewers_id=reviewers.id 
                              LEFT JOIN scores2 ON evals.id=scores2.eval_id
                              LEFT JOIN rubric_scores ON rubric_scores.id=scores2.score_id
                              WHERE survey_id=? AND teammate_email=?');
foreach ($emails as $email => $name) {
  $stmt_scores->bind_param('is',$sid, $email);
  $stmt_scores->execute();
  $result = $stmt_scores->get_result();
  while ($row = $result->fetch_array(MYSQLI_NUM)) {
    if (isset($row[2])) {
      if (!isset($scores[$email][$row[0]])) {
        $scores[$email][$row[0]] = array();
      }
      if (isset($row[2])) {
        $scores[$email][$row[0]][$row[2]] = $row[3];
      }
    }
  }
}
$stmt_scores->close();

$topics = getSurveyTopics($con, $sid);
foreach ($emails as $email => $name) {
  $sum_normalized = 0;
  $reviews = 0;
  $personal_average = array();
  foreach (array_keys($topics) as $topic_id) {
    $personal_average[$topic_id] = 0;
  }
  foreach ($scores[$email] as $reviewer => $scored) {
    // Verify that this reviewer completed all of their 
    if (isset($totals[$reviewer]) && ($totals[$reviewer] != NO_SCORE_MARKER)) {
      $sum = 0;
      foreach ($scored as $id => $score) {
        $sum = $sum + $score;
        $personal_average[$id] =  $personal_average[$id] + $score;
      }
      $scores[$email][$reviewer]['normalized'] = ($sum / $totals[$reviewer]);
      $sum_normalized = $sum_normalized + ($sum / $totals[$reviewer]);
      $reviews = $reviews + 1;
    } else {
      $scores[$email][$reviewer]['normalized'] = NO_SCORE_MARKER;
    }
  }
  foreach (array_keys($topics) as $topic_id) {
    if ($reviews == 0) {
      $averages[$email][$topic_id] = NO_SCORE_MARKER;
    } else {
      $averages[$email][$topic_id] = $personal_average[$topic_id] / $reviews;
    }
  }
  if ($reviews == 0) {
    $averages[$email]["overall"] = NO_SCORE_MARKER;
  } else {
    $averages[$email]["overall"] = $sum_normalized / $reviews;
  }
}
$topics['normalized'] = 'Normalized Score';
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous"></script>
  <title>CSE Evaluation Survey System - Survey Results</title>
</head>
<body class="text-center">
<!-- Header -->
<main>
  <div class="container-fluid">
    <div class="row justify-content-md-center bg-primary mt-1 mx-1 rounded-pill">
      <div class="col-sm-auto text-center">
        <h4 class="text-white display-1">UB CSE Evalution System<br>Survey Results</h4>
      </div>
    </div>

    <div class="row justify-content-md-center mt-5 mx-4">
      <div class="col-sm-auto text-center">
        <h4><?php echo $course_name.' ('.$course_code.')';?><br><?php echo $survey_name.' Results'; ?></h4>
      </div>
    </div>
  </div>
  <div class="container-fluid">
    <div class="row justify-content-md-center mt-5 mx-4">
      <ul id="results-present" class="nav nav-pills nav-fill" role="tablist">
      <li class="nav-item">
          <a class="nav-link active" id="full-normalized-pill" data-bs-toggle="tab" data-bs-target="#full-normalized" role="tab" aria-controls="raw-normalized" aria-selected="true">Raw Surveys</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" id="raw-pill" data-bs-toggle="tab" data-bs-target="#raw" role="tab" aria-controls="raw" aria-selected="false">Individual Averages</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" id="avg-normalized-pill" data-bs-toggle="tab" data-bs-target="#avg-normalized" role="tab" aria-controls="avg-normalized" aria-selected="false">Normalized Results</a>
        </li>
      </ul>
      <div id="results-tabs" class="tab-content border mt-2">
        <div class="tab-pane mt-2" id="raw" role="tabpanel" aria-labelledby="raw-pill">
          <div class="row justify-content-center">
            <div class="col-sm-auto">
              <a class="btn btn-outline-success" href="resultsDownload.php?survey=<?php echo $sid; ?>&type=individual" target="_blank">Download Individual Averages</a>
            </div>
          </div>
          <div class="row justify-content-center mt-1">
            <table class="table table-striped table-hover">
              <thead>
                <tr>
                  <th scope="col">Reviewee Name (Email)</th>
                  <?php
                  foreach ($topics as $topic_id => $question) {
                    if ($topic_id != 'normalized') {
                      echo '<th scope="col">'.$question.'</th>';
                    }
                  }
                  ?>
                </tr>
              </thead>
              <tbody>
              <?php
                foreach ($emails as $email => $name) {
                  echo '<tr><td>' . htmlspecialchars($email) . '<br>(' . htmlspecialchars($name) . ')' . '</td>';
                  foreach ($topics as $topic_id => $question) {
                    if ($topic_id != 'normalized') {
                      echo '<td>'.$averages[$email][$topic_id].'</td>';
                    }
                  }
                  echo '</tr>';
                }
              ?>
              </tbody>
            </table>
          </div>
        </div>
        <div class="tab-pane active show mt-2" id="full-normalized" role="tabpanel" aria-labelledby="full-normalized-pill">
          <div class="row justify-content-center">
            <div class="col-sm-auto">
              <a class="btn btn-outline-success" href="resultsDownload.php?survey=<?php echo $sid; ?>&type=raw-full" target="_blank">Download Raw Survey Results</a>
            </div>
          </div>
          <div class="row justify-content-center mt-1">
            <table class="table table-striped table-hover">
              <thead>
                <tr>
                  <th scope="col">Reviewer Name (Email)</th>
                  <th scope="col">Reviewee Name (Email)</th>
                  <?php
                  foreach ($topics as $topic_id => $question) {
                    echo '<th scope="col">'.$question.'</th>';
                  }
                  ?>
                </tr>
              </thead>
              <tbody>
              <?php
                foreach ($emails as $email => $name) {
                  foreach ($scores[$email] as $reviewer => $scored) {
                    echo '<tr><td>' . htmlspecialchars($reviewer) . '<br>(' . htmlspecialchars($emails[$reviewer]) . ')' . '</td><td>' . htmlspecialchars($email) . '<br>(' . htmlspecialchars($name) . ')' . '</td>';
                    foreach ($topics as $topic_id => $question) {
                      if (isset($scored[$topic_id])) {
                        echo '<td>'.$scored[$topic_id].'</td>';
                      } else {
                        echo '<td>--</td>';
                      }
                    }
                    echo '</tr>';
                  }
                }
              ?>
              </tbody>
            </table>
          </div>
        </div>
        <div class="tab-pane mt-2" id="avg-normalized" role="tabpanel" aria-labelledby="avg-normalized-pill">
          <div class="row justify-content-center">
            <div class="col-sm-auto">
              <a class="btn btn-outline-success" href="resultsDownload.php?survey=<?php echo $sid; ?>&type=average" target="_blank">Download Final Results</a>
            </div>
          </div>
          <div class="row justify-content-center mt-1">
            <table class="table table-striped table-hover">
              <thead>
                <tr>
                  <th scope="col">Name (Email)</th>
                  <th scope="col">Average Normalized Score</th>
                </tr>
              </thead>
              <tbody>
              <?php
                foreach ($averages as $email => $norm_array) {
                  echo '<tr><td>' . htmlspecialchars($emails[$email]) . '<br>(' . htmlspecialchars($email) . ')' . '</td>';
                  if ($norm_array["overall"] === NO_SCORE_MARKER) {
                    echo '<td>--</td></tr>';
                  } else {
                    echo '<td>' . $norm_array["overall"]  . '</td></tr>';
                  }
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
</main>
</body>
</html>
