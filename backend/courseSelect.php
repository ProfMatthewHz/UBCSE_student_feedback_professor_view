<?php
  error_reporting(-1); // reports all errors
  ini_set("display_errors", "1"); // shows all errors
  ini_set("log_errors", 1);
  session_start();
  require "lib/constants.php";

  if(!isset($_SESSION['student_id'])) {
    header("Location: ".SITE_HOME."index.php");
    exit();
  }
  
  $student_id = $_SESSION['student_id'];
  require "lib/database.php";
  require "lib/surveyQueries.php";
  $con = connectToDatabase();
  $month = idate('m');
  $term = MONTH_MAP_SEMESTER[$month];
  $year = idate('Y');

  $past_surveys = getClosedSurveysForTerm($con, $term, $year, $_SESSION['student_id']);
  $current_surveys = getCurrentSurveysForTerm($con, $term, $year, $_SESSION['student_id']);
  $upcoming_surveys = getUpcomingSurveysForTerm($con, $term, $year, $_SESSION['student_id']);
 ?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous"></script>
  <title>UB CSE Evaluation Survey Selection</title>
</head>
<body class="text-center">
<!-- Header -->
<main>
  <div class="container-fluid">
    <div class="row justify-content-md-center bg-primary mt-1 mx-1 rounded-pill">
      <div class="col-sm-auto text-center">
        <h1 class="text-white display-1">UB CSE Evalution System</h1><br>
        <p class="text-white lead">All of this term's known evaluations listed below</p>
      </div>
    </div>
    <div class="row justify-content-md-center mt-5 mx-4">
      <div class="accordion" id="surveys">
        <div class="accordion-item shadow">
          <h2 class="accordion-header" id="headerPrior">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePrior" aria-expanded="false" aria-controls="collapsePrior">
            Closed Surveys
            </button>
          </h2>
          <div id="collapsePrior" class="accordion-collapse collapse" aria-labelledby="headerPrior" data-bs-parent="#surveys">
            <div class="accordion-body">
              <?php
              if(count($past_surveys) > 0) {
                foreach ($past_surveys as $key => $value) {
                  $e = $value[2];
                  $display_name = '('.$value[0].') '.$value[1].' closed '.$e->format('M d').' at '.$e->format('g:i a');
                  echo('<p><i>'.$display_name.'</i> ');
                  // Check to see if the student was evaluated in this survey
                  if ($value[5]) {
                    if ($value[6]) {
                      echo (' <a href="'.SITE_HOME.'startResults.php?survey='.$key.'">My Averages</a> ');
                    } else {
                      echo (' No evaluations received ');
                    }
                  }
                  if ($value[3]) {
                    if ($value[4]) {
                      echo (' <a href="'.SITE_HOME.'startReview.php?survey='.$key.'">My Submissions</a> ');
                    } else {
                      echo (' Submission not completed ');
                    }
                  }
                  echo('</p>');
                }
              } else {
                echo('<p><i>No closed surveys for this term</i></p>');
              }
              ?>
            </div>
          </div>
        </div>
        <div class="accordion-item shadow">
          <h2 class="accordion-header" id="headerCurrent">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCurrent" aria-expanded="true" aria-controls="collapseCurrent">
            Ongoing Surveys
            </button>
          </h2>
          <div id="collapseCurrent" class="accordion-collapse collapse show" aria-labelledby="headerCurrent" data-bs-parent="#surveys">
            <div class="accordion-body">
              <?php
              if(count($current_surveys) > 0) {
                foreach ($current_surveys as $key => $value) {
                  $deadline_text = ' due ';
                  $link = 'startSurvey.php';
                  if ($value[4]) {
                    $deadline_text = ' can be revised until ';
                    $link = 'startConfirm.php';
                  }
                  $e = $value[2];
                  $display_name = '('.$value[0].') '.$value[1].$deadline_text.$e->format('M d').' at '.$e->format('g:i a');
                  echo('<p><a href="' . SITE_HOME . $link . '?survey='.$key.'">'.$display_name.'</a></p>');
                }
              } else {
                echo('<p><i>No surveys currently available</i></p>');
              }
              ?>
            </div>
          </div>
        </div>
        <div class="accordion-item shadow">
          <h2 class="accordion-header" id="headerUpcoming">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseUpcoming" aria-expanded="false" aria-controls="collapseUpcoming">
            Upcoming Surveys
            </button>
          </h2>
          <div id="collapseUpcoming" class="accordion-collapse collapse" aria-labelledby="headerUpcoming" data-bs-parent="#surveys">
            <div class="accordion-body">
              <?php
              if(count($upcoming_surveys) > 0) {
                foreach ($upcoming_surveys as $key => $value) {
                  $s = $value[2];
                  $display_name = '('.$value[0].') '.$value[1].' will open '.$s->format('M d').' at '.$s->format('g:i a');
                  echo('<p>'.$diplay_name.'</p>');
                }
              } else {
                echo('<p><i>Nothing planned yet. Check back later!</i></p>');
              }
              ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>
</body>
</html>
