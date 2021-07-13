<?php
function survey_cmp($a, $b) {
  if ($a['sort_start_date'] < $b['sort_start_date']) {
    return 1;
  } else if ($a['sort_start_date'] > $b['sort_start_date']) {
    return -1;
  } else if ($a['sort_expiration_date'] < $b['sort_expiration_date']) {
    return 1;
  } else if ($a['sort_expiration_date'] > $b['sort_expiration_date']) {
    return -1;
  }
  return 0;
}

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

// set timezone
date_default_timezone_set('America/New_York');

// query information about the requester
$con = connectToDatabase();

// try to get information about the instructor who made this request by checking the session token and redirecting if invalid
$instructor = new InstructorInfo();
$instructor->check_session($con, 0);

// store information about surveys as three arrays for each type
$surveys_upcoming = array();
$surveys_active = array();
$surveys_expired = array();

// store information about courses as map of course ids
$courses = array();

// get information about all courses an instructor teaches
$stmt1 = $con->prepare('SELECT name, semester, year, code, id FROM course WHERE instructor_id=?');
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
  $courses[$row['id']] = $tempSurvey;
}

// get today's date
$today = new DateTime();

// then, get information about surveys an instructor has active surveys for
foreach($courses as $course) {

    $stmt2 = $con->prepare('SELECT course_id, name, start_date, expiration_date, rubric_id, id FROM surveys WHERE course_id=?');
    $stmt2->bind_param('i', $course['id']);
    $stmt2->execute();
    $result2 = $stmt2->get_result();

    while ($row = $result2->fetch_assoc()) {
        $survey_info = array();
        $survey_info['course_id'] = $row['course_id'];
        $survey_info['name'] = $row['name'];
        $survey_info['start_date'] = $row['start_date'];
        $survey_info['expiration_date'] = $row['expiration_date'];
        $survey_info['rubric_id'] = $row['rubric_id'];
        $survey_info['id'] = $row['id'];

        // determine the completion of each survey
        // first get the total number of surveys assigned
        $stmt_total = $con->prepare('SELECT COUNT(id) AS total FROM reviewers WHERE survey_id=?');
        $stmt_total->bind_param('i', $survey_info['id']);
        $stmt_total->execute();
        $result_total = $stmt_total->get_result();
        $data_total = $result_total->fetch_all(MYSQLI_ASSOC);

        // now, get the number of completed evaluation associated with the survey
        $stmt_completed = $con->prepare('SELECT COUNT(scores.evals_id) AS completed FROM scores INNER JOIN evals ON (scores.evals_id=evals.id) INNER JOIN reviewers ON (reviewers.id=evals.reviewers_id)
                                        WHERE reviewers.survey_id=?');
        $stmt_completed->bind_param('i', $survey_info['id']);
        $stmt_completed->execute();
        $result_completed = $stmt_completed->get_result();
        $data_completed = $result_completed->fetch_all(MYSQLI_ASSOC);

        // now generate and store the progress text
        $percentage = 0;
        if ($data_total[0]['total'] != 0)
        {
          $percentage = floor(($data_completed[0]['completed'] / $data_total[0]['total']) * 100);
        }
        $survey_info['completion'] = $data_completed[0]['completed'] . '/' . $data_total[0]['total'] . '<br />(' . $percentage . '%)';

        // determine status of survey. then adjust dates to more friendly format
        $s = new DateTime($survey_info['start_date']);
        $e = new DateTime($survey_info['expiration_date']);
        $survey_info['sort_start_date'] = $survey_info['start_date'];
        $survey_info['sort_expiration_date'] = $survey_info['expiration_date'];
        $survey_info['start_date'] = $s->format('F j, Y') . '<br />' . $s->format('g:i A');
        $survey_info['expiration_date'] = $e->format('F j, Y') . '<br />' . $e->format('g:i A');

        if ($today < $s)
        {
          array_push($surveys_upcoming, $survey_info);
        }
        else if ($today < $e)
        {
          array_push($surveys_active, $survey_info);
        }
        else
        {
          array_push($surveys_expired, $survey_info);
        }
    }
  }
  // Finally sort each of the arrays so that the presentation looks correct.
  usort($surveys_upcoming, "survey_cmp");
  usort($surveys_active, "survey_cmp");
  usort($surveys_expired, "survey_cmp");
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <link rel="stylesheet" type="text/css" href="../styles/styles.css">
    <title>Surveys :: CSE Peer Evaluation System</title>
</head>
<body>
<header>
    <div class="w3-container">
          <h1 class="header-text">CSE Peer Evaluation System</h1>
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
        <h2>Surveys</h2>
    </div>

    <?php
      // echo the success message if any
      if (isset($_SESSION['survey-add']) and $_SESSION['survey-add'])
      {
        echo '<div class="w3-container w3-center w3-green">' . $_SESSION['survey-add'] . '</div><br />';
        $_SESSION['survey-add'] = NULL;
      }
      // echo deletion message
      else if (isset($_SESSION['survey-delete']) and $_SESSION['survey-delete'])
      {
        echo '<div class="w3-container w3-center w3-green">' . $_SESSION['survey-delete'] . '</div><br />';
        $_SESSION['survey-delete'] = NULL;
      }

    ?>

    <div class="w3-responsive">
    <h3>Upcoming Surveys</h3>
    <?php
      if (count($surveys_upcoming) > 0) {
          echo '<table class="w3-table w3-mobile w3-centered" border=1.0 style="width:100%">';
          echo '  <tr>';
          echo ' <th>Survey</th>';
          echo ' <th>Start Date and Time</th>';
          echo ' <th>End Date and Time</th>';
          echo ' <th>Actions</th>';
          echo '  </tr>';
          foreach ($surveys_upcoming as $survey)
          {
            echo '<tr><td>' . htmlspecialchars($courses[$survey['course_id']]['code'] . ' ' . $courses[$survey['course_id']]['name'] . ' - ' . $courses[$survey['course_id']]['semester'] . ' ' . $courses[$survey['course_id']]['year']) . '<br/>' . $survey['name'] . '</td>';
            echo '<td>' . $survey['start_date'] . '</td><td>' . $survey['expiration_date'] . '</td><td><a href="surveyPairings.php?survey=' . $survey['id'] . '">View or Edit Pairings</a> | <a href="surveyDelete.php?survey=' . $survey['id'] . '">Delete Survey</a></td></tr>';
          }
          echo '</table>';
        } else {
          echo '<p><i>No upcoming surveys</i></p>';
        }
    ?>
    <hr>
    <h3>Active Surveys</h3>
    <?php
      if (count($surveys_active) > 0) {
        echo '<table class="w3-table w3-mobile w3-centered" border=1.0 style="width:100%">';
        echo '  <tr>';
        echo ' <th>Survey</th>';
        echo ' <th>Evaluations Completed</th>';
        echo ' <th>Start Date and Time</th>';
        echo ' <th>End Date and Time</th>';
        echo ' <th>Actions</th>';
        echo '  </tr>';
        foreach ($surveys_active as $survey)
        {
          echo '<tr><td>' . htmlspecialchars($courses[$survey['course_id']]['code'] . ' ' . $courses[$survey['course_id']]['name'] . ' - ' . $courses[$survey['course_id']]['semester'] . ' ' . $courses[$survey['course_id']]['year']) . '<br/>' . $survey['name'] . '</td>';
          echo '<td>' . $survey['completion'] . '</td><td>' . $survey['start_date'] . '</td><td>' . $survey['expiration_date'] . '</td><td><a href="surveyResults.php?survey=' . $survey['id']. '">View Results</a> | <a href="surveyPairings.php?survey=' . $survey['id'] . '">View Pairings</a> | <a href="surveyDelete.php?survey=' . $survey['id'] . '">Delete Survey</a></td></tr>';
      }
        echo '</table>';
      } else {
        echo '<p><i>No current surveys</i></p>';
      }
  ?>
  <hr>
    <h3>Expired Surveys</h3>
    <?php
      if (count($surveys_expired) > 0) {
        echo '<table class="w3-table w3-mobile w3-centered" border=1.0 style="width:100%">';
        echo '  <tr>';
        echo ' <th>Survey</th>';
        echo ' <th>Evaluations Completed</th>';
        echo ' <th>Start Date and Time</th>';
        echo ' <th>End Date and Time</th>';
        echo ' <th>Actions</th>';
        echo '  </tr>';
        foreach ($surveys_expired as $survey)
        {
          echo '<tr><td>' . htmlspecialchars($courses[$survey['course_id']]['code'] . ' ' . $courses[$survey['course_id']]['name'] . ' - ' . $courses[$survey['course_id']]['semester'] . ' ' . $courses[$survey['course_id']]['year']) . '<br/>' . $survey['name'] . '</td>';
          echo '<td>' . $survey['completion'] . '</td><td>' . $survey['start_date'] . '</td><td>' . $survey['expiration_date'] . '</td><td><a href="surveyResults.php?survey=' . $survey['id']. '">View Results</a> | <a href="surveyPairings.php?survey=' . $survey['id'] . '">View Pairings</a> | <a href="surveyDelete.php?survey=' . $survey['id'] . '">Delete Survey</a></td></tr>';
      }
        echo '</table>';
      } else {
        echo '<p><i>No past surveys</i></p>';
      }
  ?>
    </div>
    <br />
<div class = "w3-center w3-mobile">
    <a href="addSurveys.php"><button class="w3-button w3-green">+ Add Survey</button></a>
</div>
</div>
</body>
</html>