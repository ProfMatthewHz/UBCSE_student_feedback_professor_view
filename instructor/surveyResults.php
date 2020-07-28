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

if ($sid === 0)
{
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

// now, get information about survey pairings and scores as array of array
// also store information about each reviewer and reviewee
$datas = array();
$overalls = array();

// Get information completed by the reviewer -- how many were reviewed and the total points
$stmt_scores = $con->prepare('SELECT stu_reviewer.name reviewer_name, stu_reviewee.name reviewee_name, stu_reviewee.email reviewee_email,score1, score2, score3, score4, score5 FROM reviewers LEFT JOIN students stu_reviewer ON reviewers.reviewer_email=stu_reviewer.email LEFT JOIN students stu_reviewee ON reviewers.reviewee_email stu_reviewee.email
                             LEFT JOIN evals on evals.reviewer_id=reviewer.id LEFT JOIN scores ON evals.id=scores.evals_id WHERE survey_id=? AND reviewers.reviewer_email=?');
$stmt = $con->prepare('SELECT reviewer_email, COUNT(reviewers.id) num_intended, COUNT(eval_id) num_scored, SUM(score1 + score2 + score3 + score4 + score5) total_score
                       FROM reviewers LEFT JOIN evals ON evals.reviewer_id=reviewers.id LEFT JOIN scores ON scores.evals_id=evals.id GROUP BY reviewer_email WHERE survey_id=?');
$stmt->bind_param('i', $sid);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {

  // now get the names for each pairing and scores for each pairing
  $stmt_scores->bind_param('is',$sid, $row['reviewer_email']);
  $stmt_scores->execute();
  $result_scores = $stmt_scores->get_result();

  while ($score = $result_scores->fetch_assoc()) {
    $pair_info = array();
    $pair_info['reviewer_email'] = $row['reviewer_email'];
    $pair_info['reviewer_name'] = $score['reviewer_name'];
    $pair_info['reviewee_email'] = $score['reviewee_email'];
    $pair_info['reviewee_name'] = $score['reviewee_name'];
    if (isset($score['score1'])) {
      $pair_info['score1'] = $score['score1'];
      $pair_info['score2'] = $score['score2'];
      $pair_info['score3'] = $score['score3'];
      $pair_info['score4'] = $score['score4'];
      $pair_info['score5'] = $score['score5'];

      //  Check if this is a result that allows normalization
      if ($row['num_intended'] == $row['num_scored']) {
        $pair_info['normalized'] = (($score['score1'] + $score['score2'] + $score['score3'] + $score['score4'] + $score['score5']) / total_score) * num_scored;

        // initialize reviewer and reviewee info arrays
        if (!isset($overalls[$score['reviewee_name']])) {
          $overalls[$score['reviewee_name']] = array('reviewee_email' => $score['reviewee_email'], 'running_sum' => $pair_info['normalized'], 'num_of_evals' => 1);
        } else {
          $overalls[$score['reviewee_name']]['running_sum'] +=$pair_info['normalized'];
          $overalls[$score['reviewee_name']]['num_of_evals'] += 1;
        }
      } else {
        $pair_info['normalized'] = NO_SCORE_MARKER;

        // initialize reviewer and reviewee info arrays
        if (!isset($overalls[$score['reviewee_name']])) {
          $overalls[$score['reviewee_name']] = array('reviewee_email' => $score['reviewee_email'], 'running_sum' => 0, 'num_of_evals' => 0);
        }
      }
      array_push($datas, $pair_info);
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
        <th>Score 1</th>
        <th>Score 2</th>
        <th>Score 3</th>
        <th>Score 4</th>
        <th>Score 5</th>
        <th>Normalized Score</th>
        </tr>
        <?php
          foreach ($datas as $datum) {
            echo '<tr><td>' . htmlspecialchars($datum['reviewer_email']) . '<br />(' . htmlspecialchars($datum['reviewer_name']) . ')' . '</td><td>' . htmlspecialchars($datum['reviewee_email']) . '<br />(' . htmlspecialchars($datum['reviewee_name']) . ')' . '</td>';
            echo '<td>' . $datum['score1'] . '</td><td>' . $datum['score2'] . '</td><td>' . $datum['score3'] . '</td><td>' . $datum['score4'] . '</td><td>' . $datum['score5'] . '</td>';
            if ($datum['normalized'] === NO_SCORE_MARKER) {
              echo '<td>--</td></tr>';
            } else {
              echo '<td>' . $datum['normalized'] . '</td></tr>';
            }
          }
          ?>
    </table>
    <hr />
    <div class="w3-container w3-center">
        <h2>Average Normalized Survey Results</h2>
    </div>
    <table class="w3-table w3-mobile w3-centered" border=1.0 style="width:100%">
        <tr>
        <th>Reviewee Email (Name)</th>
        <th>Average Normalized Score</th>
        </tr>
        <?php
          foreach ($overalls as $overall) {
            echo '<tr><td>' . htmlspecialchars($overall['teammate_email']) . '<br />(' . htmlspecialchars($overall['teammate_name']) . ')' . '</td>';

            if ($overall['num_of_evals'] === 0) {
              echo '<td>--</td></tr>';
            } else {
              echo '<td>' . $overall['running_sum'] / $overall['num_of_evals'] . '</td></tr>';
            }
          }
          ?>
    </table>
</div>
</body>
</html>
