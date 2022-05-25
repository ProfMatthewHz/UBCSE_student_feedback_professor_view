<?php
  error_reporting(-1); // reports all errors
  ini_set("display_errors", "1"); // shows all errors
  ini_set("log_errors", 1);
  session_start();
  require "lib/constants.php";

  if(!isset($_SESSION['email'])) {
    header("Location: ".SITE_HOME."index.php");
    exit();
  }
  
  $email = $_SESSION['email'];
  require "lib/database.php";
  $con = connectToDatabase();
  $month = idate('m');
  $term = MONTH_MAP_SEMESTER[$month];
  $year = idate('Y');

  // TECHNICAL DEBT TODO: Make this into a separate function
  $past_surveys = array();
  $stmt_past = $con->prepare('SELECT course.name, surveys.name, surveys.id, surveys.expiration_date, COUNT(reviewers.id) assigned, COUNT(evals.id) submitted
                              FROM surveys
                              INNER JOIN course on course.id = surveys.course_id 
                              INNER JOIN reviewers on reviewers.survey_id=surveys.id
                              LEFT JOIN evals on evals.reviewers_id=reviewers.id
                              WHERE reviewers.reviewer_email=? AND course.semester='.$term.' AND course.year='.$year.' AND surveys.expiration_date < NOW()
                              GROUP BY course.name, surveys.name, surveys.id, surveys.expiration_date
                              ORDER BY surveys.expiration_date');
  $stmt_past->bind_param('s', $email);
  $stmt_past->execute();
  $stmt_past->bind_result($class_name,$survey_name, $survey_id, $expire, $assigned, $submitted);
  $stmt_past->store_result();
  while ($stmt_past->fetch()){
    $e = new DateTime($expire);
    $display_name = '('.$class_name.') '.$survey_name.' closed on '.$e->format('M d').' at '.$e->format('g:i a');
    $fully_submitted = ($submitted == $assigned);
    $value = array($display_name, $fully_submitted);
    $past_surveys[$survey_id] = $value;
  }
  $stmt_past->close();

  // TECHNICAL DEBT TODO: Make this into a separate function
  $current_surveys = array();
  $stmt_curr = $con->prepare('SELECT course.name, surveys.name, surveys.id, surveys.expiration_date, COUNT(reviewers.id) assigned, COUNT(evals.id) submitted
                              FROM surveys
                              INNER JOIN course on course.id = surveys.course_id 
                              INNER JOIN reviewers on reviewers.survey_id=surveys.id
                              LEFT JOIN evals on evals.reviewers_id=reviewers.id
                              WHERE reviewers.reviewer_email=? AND course.semester='.$term.' AND course.year='.$year.' AND surveys.start_date <= NOW() AND surveys.expiration_date > NOW()
                              GROUP BY course.name, surveys.name, surveys.id, surveys.expiration_date
                              ORDER BY surveys.expiration_date');
  $stmt_curr->bind_param('s', $email);
  $stmt_curr->execute();
  $stmt_curr->bind_result($class_name,$survey_name, $survey_id, $expire, $assigned, $submitted);
  $stmt_curr->store_result();
  while ($stmt_curr->fetch()){
    $e = new DateTime($expire);
    if ($assigned == $submitted) {
      $display_name = '('.$class_name.') '.$survey_name.' can be revised through '.$e->format('M d').' at '.$e->format('g:i a');
      $survey_id = $survey_id * -1;
    } else {
      $display_name = '('.$class_name.') '.$survey_name.' must be completed by '.$e->format('M d').' at '.$e->format('g:i a');
    }
    $current_surveys[htmlspecialchars($display_name)] = $survey_id;
  }
  $stmt_curr->close();

  // TECHNICAL DEBT TODO: Make this into a separate function
  $upcoming_surveys = array();
  $stmt_next = $con->prepare('SELECT DISTINCT course.name, surveys.name, surveys.id, surveys.start_date
                              FROM reviewers
                              INNER JOIN surveys ON reviewers.survey_id = surveys.id 
                              INNER JOIN course on course.id = surveys.course_id 
                              WHERE reviewers.reviewer_email=? AND course.semester='.$term.' AND course.year='.$year.' AND surveys.start_date > NOW()
                              ORDER BY surveys.start_date');
  $stmt_next->bind_param('s', $email);
  $stmt_next->execute();
  $stmt_next->bind_result($class_name,$survey_name, $survey_id, $start);
  $stmt_next->store_result();
  while ($stmt_next->fetch()){
    $e = new DateTime($start);
    $display_name = '('.$class_name.') '.$survey_name.' opening on '.$e->format('M d').' at '.$e->format('g:i a');
    $upcoming_surveys[] = $display_name;
  }
  $stmt_next->close();
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
                  echo('<p><i>'.$value[0].'</i> ');
                  echo ('<a href="'.SITE_HOME.'startResults.php?survey='.$key.'">My Averages</a>');
                  if ($value[1]) {
                    echo (' | <a href="'.SITE_HOME.'startReview.php?survey='.$key.'">My Submissions</a>');
                  } else {
                    echo (' | No evals submitted');
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
                  if ($value < 0) {
                    $value = $value * -1;
                    echo('<p><a href="'.SITE_HOME.'startConfirm.php?survey='.$value.'">'.$key.'</a></p>');
                  } else {
                    echo('<p><a href="'.SITE_HOME.'startSurvey.php?survey='.$value.'">'.$key.'</a></p>');
                  }
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
                foreach ($upcoming_surveys as $key) {
                  echo('<p>'.$key.'</p>');
                }
              } else {
                echo('<p><i>No known surveys upcoming. Check back later!</i></p>');
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
