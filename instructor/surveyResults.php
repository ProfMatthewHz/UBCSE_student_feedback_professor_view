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
if (!isset($_GET['survey']))
{
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

// This wil be an array of arrays organized by the person BEING REVIEWED.
$scores = array();
// Array mapping email to total number of points
$totals = array();
// Array mapping email addresses to names
$emails = array();
// Array mapping email address to normalized results
$normalized = array();

$stmt = $con->prepare('SELECT reviewer_email, students.name, SUM(rubric_scores.score) total_score, COUNT(teammate_email) total_reviews
                       FROM reviewers INNER JOIN students ON reviewers.reviewer_email=students.email LEFT JOIN evals ON evals.reviewers_id=reviewers.id LEFT JOIN scores2 ON scores2.eval_id=evals.id LEFT JOIN rubric_scores ON rubric_scores.id=scores2.score_id WHERE survey_id=? GROUP BY reviewer_email, students.name');
$stmt->bind_param('i', $sid);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_array(MYSQLI_NUM)) {
  $email_addr = $row[0];
  $emails[$email_addr] = $row[1];
  $scores[$email_addr] = array();
  // If the reviewer completed this survey
  if (isset($row[2])) {
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
foreach ($totals as $email => $points) {
  $stmt_scores->bind_param('is',$sid, $email);
  $stmt_scores->execute();
  $result = $stmt_scores->get_result();
  while ($row = $result->fetch_array(MYSQLI_NUM)) {
    if (!isset($scores[$email][$row[0]])) {
      $scores[$email][$row[0]] = array();
    }
    if (isset($row[2])) {
      $scores[$email][$row[0]][$row[2]] = $row[3];
    }
  }
}
$stmt_scores->close();

foreach ($emails as $email => $name) {
  $sum_normalized = 0;
  $reviews = 0;
  foreach ($scores[$email] as $reviewer => $scored) {
    // Verify that this reviewer completed all of their 
    if (isset($totals[$reviewer])) {
      $sum = 0;
      foreach ($scored as $id => $score) {
        $sum = $sum + $score;
      }
      $scores[$email][$reviewer]['normalized'] = ($sum / $totals[$reviewer]);
      $sum_normalized = $sum_normalized + ($sum / $totals[$reviewer]);
      $reviews = $reviews + 1;
    }
  }
  if ($reviews == 0) {
    $normalized[$email] = NO_SCORE_MARKER;
  } else {
    $normalized[$email] = $sum_normalized / $reviews;
  }
}
$topics = getSurveyTopics($con, $sid);
$topics['normalized'] = 'Normalized Score';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <link rel="stylesheet" type="text/css" href="../styles/styles.css">
    <title>Survey Results :: UB CSE Peer Evaluation System</title>
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
        <h2>Download Survey Results</h2>
        <a href="resultsDownload.php?survey=<?php echo $sid; ?>&type=raw" target="_blank"><button class="w3-button w3-blue">Download Raw Survey Results</button></a>
        <a href="resultsDownload.php?survey=<?php echo $sid; ?>&type=normalized" target="_blank"><button class="w3-button w3-blue">Download Normalized Survey Results</button></a>
        <a href="resultsDownload.php?survey=<?php echo $sid; ?>&type=average" target="_blank"><button class="w3-button w3-blue">Download Average Normalized Survey Results</button></a>
    </div>
    <hr />
    <div class="w3-container w3-center">
        <h2>Raw Survey Results</h2>
    </div>
    <table class="w3-table w3-mobile w3-centered" border=1.0 style="width:100%">
        <tr>
        <th>Reviewer Email (Name)</th>
        <th>Reviewee Email (Name)</th>
        <?php
        foreach ($topics as $topic_id => $question) {
          echo '<th>'.$question.'</th>';
        }
        ?>
        </tr>
        <?php
          foreach ($emails as $email => $name) {
            foreach ($scores[$email] as $reviewer => $scored) {
              echo '<tr><td>' . htmlspecialchars($reviewer) . '<br />(' . htmlspecialchars($emails[$reviewer]) . ')' . '</td><td>' . htmlspecialchars($email) . '<br />(' . htmlspecialchars($name) . ')' . '</td>';
              foreach ($topics as $topic_id => $question) {
                if (isset($scored[$topic_id])) {
                  echo '<td>'.$scored[$topic_id].'</td>';
                } else {
                  echo '<td>--</td>';
                }
              }
            }
            echo '</tr>';
          }
        ?>
    </table>
    <hr />
    <div class="w3-container w3-center">
        <h2>Average Normalized Survey Results</h2>
    </div>
    <table class="w3-table w3-mobile w3-centered" border=1.0 style="width:100%">
        <tr>
        <th>Email (Name)</th>
        <th>Average Normalized Score</th>
        </tr>
        <?php
          foreach ($normalized as $email => $norm) {
            echo '<tr><td>' . htmlspecialchars($email) . '<br />(' . htmlspecialchars($emails[$email]) . ')' . '</td>';
            if ($norm === NO_SCORE_MARKER) {
              echo '<td>--</td></tr>';
            } else {
              echo '<td>' . $norm . '</td></tr>';
            }
          }
          ?>
    </table>
</div>
</body>
</html>
