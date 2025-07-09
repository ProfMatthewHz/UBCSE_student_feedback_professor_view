<?php
function calculateSingleCriterionAverage($evals, $crit_scores, $topics) {
  $ret_val = array();

  // Loop through each of the topics being evaluated in this survey
  foreach (array_keys($topics) as $topic_id) {
    $total_score = 0;
    $total_weight = 0;
    
    // Loop through each evaluation on which this student was reviewed
    foreach ($evals as $eval_id => $eval_weight) {
      $total_weight += $eval_weight;
      // Get the normalized score from this reviewer x team combo
      $total_score += $crit_scores[$eval_id][$topic_id] * $eval_weight;
    }

    // Handle the degenerate case where the only completed reviews had a weight of 0
    if ($total_weight == 0) {
      $ret_val[$topic_id] = NO_SCORE_MARKER;
    } else {
      $ret_val[$topic_id] =  $total_score / $total_weight;
    }
  }
  return $ret_val;
}

function calculateAllCriterionAverages($students, $valid_evals, $crit_scores, $topics) {
  $ret_val = array();
  // Loop through each of the individuals who was reviewed in this evaluation
  foreach (array_keys($students) as $student_id) {
    // Check if this student has any valid evaluations we wish to calculate
    if (array_key_exists($student_id, $valid_evals)) {
      // Get the valid reviews for this student
      $evals = $valid_evals[$student_id];
      $averages = calculateSingleCriterionAverage($evals, $crit_scores, $topics);
      $ret_val[$student_id] = $averages;
    } else {
      // Handle the case where a student does not have any valid reviews
      $ret_val[$student_id] = array();
      foreach (array_keys($topics) as $topic_id) {
        $ret_val[$student_id][$topic_id] = NO_SCORE_MARKER;
      }
    }
  }
  return $ret_val;
}

function calculateSingleNormalizedAverage($evals, $eval_totals, $reviewer_totals) {
  $total_score = 0;
  $total_weight = 0;

  // Loop through each evaluation on which this student was reviewed
  foreach ($evals as $eval_id => $eval_weight) {
    // Increase the total weight of the evaluations by this amount
    $total_weight += $eval_weight;
    // Get the normalized score from this evaluation
    $total_score += ($eval_totals[$eval_id] / $reviewer_totals[$eval_id]) * $eval_weight;
  }
  
  // Handle the degenerate case where the only completed reviews had a weight of 0
  if ($total_weight == 0) {
    return NO_SCORE_MARKER;
  } else {
    return $total_score / $total_weight;
  }
}

function calculateAllNormalizedAverages($students, $valid_evals, $eval_totals, $reviewer_totals) {
  $ret_val = array();
  // Loop through each of the individuals who was reviewed in this evaluation
  foreach (array_keys($students) as $student_id) {
    // Check if this student has any valid evaluations we wish to calculate
    if (array_key_exists($student_id, $valid_evals)) {
      // Get the valid reviews for this student
      $evals = $valid_evals[$student_id];
      $norm_avg = calculateSingleNormalizedAverage($evals, $eval_totals, $reviewer_totals);
      $ret_val[$student_id] = $norm_avg;
    } else {
      // Handle the case where a student does not have any valid reviews
      $ret_val[$student_id] = NO_SCORE_MARKER;
    }
  }
  return $ret_val;
}


function calculateEvalNormalizedScore($eval_totals, $eval_normalized, $reviewer_totals) {
  $ret_val = array();

  // Loop through each of the evaluations that were completed
  foreach ($eval_totals as $eval_id => $current_total) {
    // Check if this student has any valid evaluations we wish to calculate
    if (array_key_exists($eval_id, $eval_normalized)) {
      $ret_val[$eval_id] = $current_total / $reviewer_totals[$eval_id];
    } else {
      // Handle the case where this evaluation was completed but cannot be normalized properly
      $ret_val[$eval_id] = NO_SCORE_MARKER;
    }
  }
  return $ret_val;
}

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
          // Assign everyone a result of 1
          $ret_val[$student_id][$reviewer_id] = 1;
        } else {
          $total = 0;
          // Loop through each topic -- this will eventually allow the option to weight topics differently
          foreach (array_keys($topics) as $topic_id) {
            $total = $total + (array_key_exists($topic_id, $responses) ? $responses[$topic_id] : 0);
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
      if ($normalized[$student_id][$reviewer_id] !== NO_SCORE_MARKER) {
        $sum = $sum + ($normalized[$student_id][$reviewer_id] * $weight);
        $sum_weights = $sum_weights + $weight;
      }
    }
    if ($sum_weights == 0) {
      $ret_val[$student_id] = NO_SCORE_MARKER;
    } else {
      $ret_val[$student_id] = $sum / $sum_weights;
    }
  }
  return $ret_val;
}
?>