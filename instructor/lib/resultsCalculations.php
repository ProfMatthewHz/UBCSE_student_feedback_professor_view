<?php
function calculateAverages($students, $scores, $topics) {
  $ret_val = array();
  // Loop through each of the individuals who was reviewed in thie evaluation
  foreach ($students as $student_id) {
    $averages = array();

    // Loop through each topic -- this will eventually allow the option to weight topics differently
    foreach (array_keys($topics) as $topic_id) {
      $total_points = 0;
      $total_weight = 0;
      
      // Loop through each of the evaluations that this individual received
      foreach ($scores[$student_id] as $responses) {
         $weight = $responses["weight"];
         // Update the totals on this topic
         $total_points = $total_points + ($responses[$topic_id] * $weight);
         $total_weight = $total_weight + $weight;
         
      }
      if ($total_weight == 0) {
        $averages[$topic_id] = NO_SCORE_MARKER;
      } else {
        $averages[$topic_id] = $total_points / $total_weight;
      }
    }
    $ret_val[$student_id] = $averages;
  }
  return $ret_val;
}

function calculateFinalNormalizedScore($students, $scores, $topics, $team_data) {
  // Calculate the normalized score for each survey that was completed
  $normalized = calculateNormalizedSurveyResults($students, $scores, $topics, $team_data);
  
  // Finally, calculate the overall results for each student
  $ret_val = calculateOverallResults($students, $scores, $normalized);
  return $ret_val;
}

function calculateNormalizedSurveyResults($students, $scores, $topics, $team_data) {
  $ret_val = array();
  // Loop through each of the individuals who was reviewed in thie evaluation
  foreach ($students as $student_id) {
    $ret_val[$student_id] = array();
    // Loop through each student who reviewed this individual
    foreach ($scores[$student_id] as $reviewer_id => $responses) {
      $weight = $responses["weight"];
      $team = $responses["team"];
      $reviewer_total_points = $team_data[$reviewer_id][$team]["total_score"];
      $reviewer_total_reviews = $team_data[$reviewer_id][$team]["total_people"];
      
      // Check that the student completed all of the surveys and that this review "counts"
      if (($team_data[$reviewer_id][$team]["completion"]) && ($weight != 0)) {
        // Handle the degenerate case where nobody on the team was assigned points
        if ($reviewer_total_points == 0) {
          // Assign everyone a result of 0   
          $ret_val[$student_id][$reviewer_id] = 0;
        } else {
          $total = 0;
          // Loop through each topic -- this will eventually allow the option to weight topics differently
          foreach (array_keys($topics) as $topic_id) {
            $total = $total + $responses[$topic_id];
          }
          // Now normalize the total against the total score for this reviewer to this team
          $total = $total / $reviewer_total_points;
          // Finally, multiply by the number of reviews to get a result independent of team size
          $total = $total * $reviewer_total_reviews;
          $ret_val[$student_id][$reviewer_id] = $total;
        }
      } else {
        $ret_val[$student_id][$reviewer_id] = NO_SCORE_MARKER;
      }
    }
  }
  return $ret_val;
}

function calculateOverallResults($students, $scores, $normalized) {
  $ret_val = array();
  // Loop through each of the individuals who was reviewed in thie evaluation
  foreach ($students as $student_id) {
    $sum = 0;
    $sum_weights = 0;
    // Loop through each student who reviewed this individual
    foreach ($scores[$student_id] as $reviewer_id => $responses) {
      $weight = $responses["weight"];
      // Check that the student completed all of the surveys and that this review "counts"
      if ($normalized[$student_id][$reviewer_id] != NO_SCORE_MARKER) {
        $sum = $sum + ($normalized[$student_id][$reviewer_id] * $weight);
        $sum_weights = $sum_weights + $weight;
      }
    }
    if ($sum == 0) {
      $ret_val[$student_id] = NO_SCORE_MARKER;
    } else {
      $ret_val[$student_id] = $sum / $sum_weights;
    }
  }
  return $ret_val;
}
?>