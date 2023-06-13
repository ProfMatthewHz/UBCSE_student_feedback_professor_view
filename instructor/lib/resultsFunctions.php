<?php
function getIndividualsResults($teammates, $scores, $topics) {
  // Calculate the per-topic averages for each student
  $averages = calculateAverages(array_keys($teammates), $scores, $topics);

  // Now generate the array of results to output
  $ret_val = array();

  // Create the header row
  $header = array("Reviewee Name (Email)");
  foreach ($topics as $question) {
    $header[] = $question;
  }
  $ret_val[] = $header;

  // Then add one row per student who was reviewed
  foreach ($teammates as $id => $name_and_email) {
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
  // Calculate the normalized score for each survey that was completed
  $normalized = calculateNormalizedSurveyResults(array_keys($teammates), $scores, $topics, $team_data);
  
  // Finally, calculate the overall results for each student
  $overall = calculateOverallResults(array_keys($teammates), $scores, $normalized);

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
?>