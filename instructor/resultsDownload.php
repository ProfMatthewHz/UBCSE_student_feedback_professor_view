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


// respond not found on no query string parameters
$sid = NULL;
if (!isset($_GET['survey']))
{
  http_response_code(404);
  echo "404: Not found.";
  exit();
}
if (!isset($_GET['type']))
{
  http_response_code(404);
  echo "404: Not found.";
  exit();
}

// make sure the type query is one of the valid types. if not, respond not found
if ($_GET['type'] !== 'raw' && $_GET['type'] !== 'normalized' && $_GET['type'] !== 'average')
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
if ($result->num_rows == 0) {
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
if ($result->num_rows == 0) {
  http_response_code(403);
  echo "403: Forbidden.";
  exit();
}

// now, get information about survey pairings and scores as array of array
// also store information about each reviewer and reviewee
$datas = array();
$overalls = array();

// Get information completed by the reviewer -- how many were reviewed and the total points
$stmt_scores = $con->prepare('SELECT stu_reviewer.name reviewer_name, stu_reviewee.name reviewee_name, stu_reviewee.email reviewee_email,score1, score2, score3, score4, score5 FROM reviewers LEFT JOIN students stu_reviewer ON reviewers.reviewer_email=stu_reviewer.email LEFT JOIN students stu_reviewee ON reviewers.teammate_email=stu_reviewee.email
                             LEFT JOIN evals on evals.reviewers_id=reviewers.id LEFT JOIN scores ON evals.id=scores.evals_id WHERE survey_id=? AND reviewers.reviewer_email=?');
$stmt = $con->prepare('SELECT reviewer_email, COUNT(reviewers.id) num_intended, COUNT(evals_id) num_scored, SUM(score1 + score2 + score3 + score4 + score5) total_score
                       FROM reviewers LEFT JOIN evals ON evals.reviewers_id=reviewers.id LEFT JOIN scores ON scores.evals_id=evals.id AND scores.score1 <> -1 WHERE survey_id=? GROUP BY reviewer_email ');
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
        $pair_info['normalized'] = (($score['score1'] + $score['score2'] + $score['score3'] + $score['score4'] + $score['score5']) / $row['total_score']) * $row['num_scored'];

        // initialize reviewer and reviewee info arrays
        if (!isset($overalls[$score['reviewee_name']])) {
          $overalls[$score['reviewee_name']] = array('reviewee_name' => $score['reviewee_name'], 'reviewee_email' => $score['reviewee_email'], 'running_sum' => $pair_info['normalized'], 'num_of_evals' => 1);
        } else {
          $overalls[$score['reviewee_name']]['running_sum'] += $pair_info['normalized'];
          $overalls[$score['reviewee_name']]['num_of_evals'] += 1;
        }
      } else {
        $pair_info['normalized'] = NO_SCORE_MARKER;

        // initialize reviewer and reviewee info arrays
        if (!isset($overalls[$score['reviewee_name']])) {
          $overalls[$score['reviewee_name']] = array('reviewee_name' => $score['reviewee_name'], 'reviewee_email' => $score['reviewee_email'], 'running_sum' => 0, 'num_of_evals' => 0);
        }
      }
      array_push($datas, $pair_info);
    }
  }
}

// now generate the raw scores output
if ($_GET['type'] === 'raw')
{
  $raw_output = "";
  // start the download
  foreach ($datas as $datum) {
    $raw_output .= $datum['reviewer_email'] . ',' . $datum['reviewee_email'] . ',' . $datum['score1'] . ',' . $datum['score2'] . ',' . $datum['score3'] . ',' . $datum['score4'] . ',' . $datum['score5'] ."\n";
  }

  // generate the correct headers for the file download
  header('Content-Type: text/csv; charset=UTF-8');
  header('Content-Disposition: attachment; filename="survey-' . $sid . '-raw-results.csv"');

  // ouput the data
  echo $raw_output;
} else if ($_GET['type'] === 'normalized') {
  // generate the correct headers for the file download
  header('Content-Type: text/csv; charset=UTF-8');
  header('Content-Disposition: attachment; filename="survey-' . $sid . '-normalized-results.csv"');

  $normal_output = "";

  // start the download
  foreach ($datas as $datum) {
    $normal_output .= $datum['reviewer_email'] . ',' . $datum['reviewee_email'] . ',' . $datum['score1'] . ',' . $datum['score2'] . ',' . $datum['score3'] . ',' . $datum['score4'] . ',' . $datum['score5'] . ',' . $datum['normalized'] ."\n";
  }

  // ouput the data
  echo $normal_output;
} else {
  // generate the correct headers for the file download
  header('Content-Type: text/csv; charset=UTF-8');
  header('Content-Disposition: attachment; filename="survey-' . $sid . '-normalized-averages.csv"');

  $result_output = "";

  // start the download
  foreach ($overalls as $overall) {
    if ($overall['num_of_evals'] == 0) {
      $result_output .= $overall['reviewee_email'] . ", -- \n";
    } else {
      $result_output .= $overall['reviewee_email'] . ',' . $overall['running_sum'] / $overall['num_of_evals'] ."\n";
    }
  }
  echo $result_output;
}
?>
