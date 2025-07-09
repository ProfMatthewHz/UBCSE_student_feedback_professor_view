<?php
function createNormalizedAveragesResult($students, $averages, $views) {
  $ret_val = array();
  // Create the header row for this data
  $header = array("Name", "Email", "Norm. Avg.", "Feedback Views");
  $ret_val[] = $header;
  foreach ($students as $student_id => $email_and_name) {
    $row = array($email_and_name['name'], $email_and_name['email']);
    if (array_key_exists($student_id, $averages)) {
      $row[] = $averages[$student_id];
    } else {
      // If there are no results for this student, fill in with NO_SCORE_MARKER
      $row[] = NO_SCORE_MARKER;
    }
    // Add in the number of views by this student
    $row[] = $views[$student_id];
    // And add this row to our results
    $ret_val[] = $row;
  }
  return $ret_val;
}

function createIndividualAverageResult($students, $averages, $column_names) {
  $ret_val = array();
  // Create the header row for this data
  $header = array("Name", "Email");
  foreach ($column_names as $question) {
    $header[] = $question;
  }
  $ret_val[] = $header;
  foreach ($students as $student_id => $email_and_name) {
    $row = array($email_and_name['name'], $email_and_name['email']);
    if (array_key_exists($student_id, $averages)) {
      // Loop through each of the results for this student
      foreach (array_keys($column_names) as $topic_id) {
        $row[]= $averages[$student_id][$topic_id];
      }
    } else {
      foreach (array_keys($column_names) as $topic_id) {
        $row[]= NO_SCORE_MARKER;
      }
    }
    $ret_val[] = $row;
  }
  return $ret_val;
}

function createRawDataResult($students, $scores, $normalized_total, $column_names) {
  $ret_val = array();
  // Create the header row for this data
  $header = array("Reviewer", "Reviewee");
  foreach ($column_names as $question) {
    $header[] = $question;
  }
  $header[] = "Norm. Avg.";
  $ret_val[] = $header;
  foreach ($scores as $eval_id => $crit_scores) {
    $row = array();
    // Add the reviewer information
    $row[] = $students[$eval_id][0] . ' (' . $students[$eval_id][1] . ')';
    // Add the reviewee information
    $row[] = $students[$eval_id][2] . ' (' . $students[$eval_id][3] . ')';
    // Next loop through all of criterion scores for this eval
    foreach (array_keys($column_names) as $topic_id) {
      $row[] = $crit_scores[$topic_id];
    }
    // Finally, add the normalized average for this review
    $row[] = $normalized_total[$eval_id];
    // Add this row to our results
    $ret_val[] = $row;
  }
  return $ret_val;
}

function getEvalNormalizedScores($con, $survey_id) { 
  // Get the data reuired to calculate the normalized scores for each evaluation
  $eval_totals = getEvalsTotalPoints($con, $survey_id);
  $reviewer_totals = getReviewersTotalPoints($con, $survey_id);
  $validations = getValidEvalsOfStudentByTeam($con, $survey_id);
  // Calculate the normalized averages for each evaluation
  $ret_val = calculateEvalNormalizedScore($eval_totals, $validations['eval_normalized'], $reviewer_totals);
  return $ret_val;
}

function getNormalizedAverages($con, $survey_id) { 
  // Get the data reuired to calculate the normalized scores for each evaluation
  $eval_totals = getEvalsTotalPoints($con, $survey_id);
  $reviewer_totals = getReviewersTotalPoints($con, $survey_id);
  $validations = getValidEvalsOfStudentByTeam($con, $survey_id);
  // Calculate the normalized averages for each evaluation
  $ret_val = calculateEvalNormalizedScore($eval_totals, $validations['eval_normalized'], $reviewer_totals);
  return $ret_val;
}

function sumDifferencesSquare($reviews, $averages, $topic_id) {
  $ret_val = 0;
  foreach ($reviews as $reviewee => $review_data) {
    // Calculate the difference between what the reviewer scored this student the student's average score on this topic
    $diff = ($review_data[$topic_id] - $averages[$reviewee][$topic_id]);
    // Add the square of the difference to our accumulator
    $ret_val += pow($diff, 2);
  }
  return $ret_val;
}
?>