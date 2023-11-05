<?php
function getIndividualsAverages($students, $scores, $topics) {
  // Calculate the per-topic averages for each student
  $averages = calculateAverages(array_keys($students), $scores, $topics);

  // Now generate the array of results to output
  $ret_val = array();

  // Create the header row
  $header = array("Reviewee Name (Email)");
  foreach ($topics as $question) {
    $header[] = $question;
  }
  $ret_val[] = $header;

  // Then add one row per student who was reviewed
  foreach ($students as $id => $name_and_email) {
    $line = array($name_and_email['name'] . ' (' . $name_and_email['email'] . ')');
    foreach ($topics as $topic_id => $question) {
      $line[] = $averages[$id][$topic_id];
    }
    $ret_val[] = $line;
  }
  return $ret_val;
}

function getRawResults($teammates, $scores, $topics, $reviewers, $team_data) {
  // Calculate the normalized score for each survey that was completed
  $normalized = calculateNormalizedSurveyResults(array_keys($teammates), $scores, $topics, $team_data);

  // Now generate the array of results to output
  $ret_val = array();

  // Create the header row
  $header = array("Reviewee Name (Email)", "Reviewer Name (Email)");
  foreach ($topics as $question) {
    $header[] = $question;
  }
  $header[] = "Normalized Total";
  $ret_val[] = $header;

  // Then add one row per student who was reviewed
  foreach ($teammates as $id => $name_and_email) {      
    foreach ($scores[$id] as $reviewer => $scored) {
      $line = array($name_and_email['name'] . ' (' . $name_and_email['email'] . ')');
      $line[] = $reviewers[$reviewer]["name"] . ' (' . $reviewers[$reviewer]["email"] . ')';
      foreach ($topics as $topic_id => $question) {
        $line[] = $scored[$topic_id];
      }
      $line[] = $normalized[$id][$reviewer];
      $ret_val[] = $line;
    }
  }
  return $ret_val;
}

function getFinalResults($teammates, $scores, $topics, $team_data) {
  // Finally, calculate the overall results for each student
  $overall = calculateFinalNormalizedScore(array_keys($teammates), $scores, $topics, $team_data);

  // Now generate the array of results to output
  $ret_val = array();

  // Create the header row
  $header = array("Reviewee Name (Email)", "Average Normalized Result");
  $ret_val[] = $header;

  // Then add one row per student who was reviewed
  foreach ($teammates as $id => $name_and_email) {
    $line = array($name_and_email['name'] .' (' . $name_and_email['email'] . ')', $overall[$id]);
    $ret_val[] = $line;
  }
  return $ret_val;
}

function transposeArray($scores) {
  $ret_val = array();
  // Loop through each person who was reviewed
  foreach ($scores as $reviewee => $reviewers_array) {
    // Loop through each person who reviewed them
    foreach ($reviewers_array as $reviewer => $review_data) {
      // Update our results with the reviewer & reviewee locations swapped
      if (!array_key_exists($reviewer, $ret_val)) {
        $ret_val[$reviewer] = array($reviewee => $review_data);
      } else {
        $ret_val[$reviewer][$reviewee] = $review_data;
      }
    }
  }
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

function getReviewerReviewResults($reviewers, $scores, $topics, $team_data) {
  // Generate the array of results to output
  $ret_val = array();

  // Get the normalized scores and results that we will also need
  $normals = calculateNormalizedSurveyResults(array_keys($scores), $scores, $topics, $team_data);
  $avg_normal = calculateOverallResults(array_keys($scores), $scores, $normals);
  // Transpose the results array so they are indexed as reviewer->reviewee rather than reviewee->reviewer
  $invert_scores = transposeArray($scores);
  $invert_normals = transposeArray($normals);

  // Then add one row per student who was reviewed
  foreach ($reviewers as $id => $name_and_email) {
    $key = $name_and_email['name'] . ' (' . $name_and_email['email'] . ')';
    $result = NO_SCORE_MARKER;
    // Check if this person actually completed any reviews
    if (array_key_exists($id, $invert_scores)) {
      $count = 0;
      $total = 0;
      foreach ($invert_normals[$id] as $reviewee => $normal) {
        if ($normal != NO_SCORE_MARKER) {
          $count++;
          $total = $total + (pow($normal - $avg_normal[$reviewee], 2));
        }
      }
      if ($count != 0) {
        $result = round($total / $count, 3);
      }
    }
    $ret_val[$key] = $result;
  }
  return $ret_val;
}
?>