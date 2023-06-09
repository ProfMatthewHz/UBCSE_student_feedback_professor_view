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
require_once "lib/surveyQueries.php";
require_once "lib/instructorQueries.php";

// query information about the requester
$con = connectToDatabase();

// try to get information about the instructor who made this request by checking the session token and redirecting if invalid
$instructor = new InstructorInfo();
$instructor->check_session($con, 0);


// respond not found on no query string parameters
$survey_id = NULL;
if (!isset($_GET['survey'])) {
  http_response_code(404);
  echo "404: Not found.";
  exit();
}
if (!isset($_GET['type'])) {
  http_response_code(404);
  echo "404: Not found.";
  exit();
}

// make sure the type query is one of the valid types. if not, respond not found
if ($_GET['type'] !== 'raw-full' && $_GET['type'] !== 'individual' && $_GET['type'] !== 'average') {
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

// make sure the survey is for a course the current instructor actually teaches
if (!isCourseInstructor($con, $survey_info['course_id'], $instructor->id)) {
  http_response_code(403);
  echo "403: Forbidden.";
  exit();
}

// Array mapping email address to normalized results
$averages = array();

// Get the per-reviewer data
$survey_complete = getCompletionData($con, $survey_id);

// Get the info for everyone who will be evaluated
$teammates = getReviewedData($con, $survey_id);

// Get information completed by the reviewer -- how many were reviewed and the total points
$scores = getSurveyScores($con, $survey_id, $teammates);

// Get how much we should be weighting each of the reviews
$weights = getSurveyWeights($con, $survey_id, $teammates);

// Get the total number of points this reviewer provided on the surveys
$totals = getSurveyTotals($con, $survey_id, $teammates);

$topics = getSurveyMultipleChoiceTopics($con, $survey_id);

foreach ($teammates as $id => $value) {
  $sum_normalized = 0;
  $review_weights = 0;
  $norm_review_weights = 0;
  $personal_average = array();
  foreach (array_keys($topics) as $topic_id) {
    $personal_average[$topic_id] = 0;
  }
  foreach ($scores[$email] as $reviewer => $scored) {
    $weight = $weights[$email][$reviewer];
    $sum = 0;
    foreach ($scored as $id => $score) {
      $weighted_score = $score * $weight;
      $sum = $sum + $weighted_score;
      $personal_average[$id] =  $personal_average[$id] + $weighted_score;
    }
    $review_weights = $review_weights + $weight;
    // Verify that this reviewer completed all of their 
    if ($survey_complete[$reviewer]) {
      // Normalize the sum by calculating the percentage of points this student received and then adjust for the weight of the evaluation
      $normalized_sum = ($sum / $totals[$reviewer]) * ($weights[$reviewer]["total"]/$weight);
      $scores[$email][$reviewer]['normalized'] = $normalized_sum;
      $sum_normalized = $sum_normalized + ($normalized_sum * $weight);
      $norm_review_weights = $norm_review_weights + $weight;
    } else {
      $scores[$email][$reviewer]['normalized'] = NO_SCORE_MARKER;
    }
  }
  foreach (array_keys($topics) as $topic_id) {
    if ($review_weights == 0) {
      $averages[$email][$topic_id] = NO_SCORE_MARKER;
    } else {
      $averages[$email][$topic_id] = $personal_average[$topic_id] / $review_weights;
    }
  }
  if ($norm_review_weights == 0) {
    $averages[$email]["overall"] = NO_SCORE_MARKER;
  } else {
    $averages[$email]["overall"] = $sum_normalized / $norm_review_weights;
  }
}

// now generate the raw scores output
if ($_GET['type'] === 'individual') {
  header('Content-Type: text/csv; charset=UTF-8');
  header('Content-Disposition: attachment; filename="survey-' . $survey_id . '-individual-results.csv"');
  $out = fopen('php://output', 'w');
  $header = array("Reviewee");
  foreach ($topics as $topic_id => $question) {
    array_push($header,$question);
  }
  fputcsv($out, $header);
  foreach ($teammates as $email => $name) {
    $line = array($email);
    foreach ($topics as $topic_id => $question) {
      $line[] = $averages[$email][$topic_id];
    }
    fputcsv($out, $line);
  }
  fclose($out);
} else if ($_GET['type'] === 'raw-full') {
  $topics['normalized'] = 'Normalized Score';
  // generate the correct headers for the file download
  header('Content-Type: text/csv; charset=UTF-8');
  header('Content-Disposition: attachment; filename="survey-' . $survey_id . '-raw-results.csv"');
  $out = fopen('php://output', 'w');
  $header = array("Reviewer","Reviewee");
  foreach ($topics as $topic_id => $question) {
    array_push($header,$question);
  }
  fputcsv($out, $header);
  foreach (array_keys($teammates) as $email) {
    foreach ($scores[$email] as $reviewer => $scored) {
      $line = array();
      array_push($line, $reviewer);
      array_push($line, $email);
      foreach ($topics as $topic_id => $question) {
        if (isset($scored[$topic_id])) {
          array_push($line, $scored[$topic_id]);
        } else {
          array_push($line, '--');
        }
      }
      fputcsv($out, $line);
    }
  }
  fclose($out);
} else {
  // generate the correct headers for the file download
  header('Content-Type: text/csv; charset=UTF-8');
  header('Content-Disposition: attachment; filename="survey-' . $survey_id . '-normalized-averages.csv"');
  $out = fopen('php://output', 'w');
  fputcsv($out, array("Reviewee","Average Normalized Score"));
  foreach ($averages as $email => $avg_results) {
    $line = array();
    array_push($line, $email);
    if ($avg_results["overall"] === NO_SCORE_MARKER) {
      array_push($line, '--');
    } else {
      array_push($line, $avg_results["overall"]);
    }
    fputcsv($out, $line);
  }
  fclose($out);
}
?>
