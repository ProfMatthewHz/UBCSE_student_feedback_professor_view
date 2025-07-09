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

function calculateAllCriterionAverages($valid_evals, $crit_scores, $topics) {
  $ret_val = array();
  // Loop through each of the individuals who was reviewed in this evaluation
  foreach ($valid_evals as $student_id => $evals) {
    $averages = calculateSingleCriterionAverage($evals, $crit_scores, $topics);
    $ret_val[$student_id] = $averages;
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

function calculateAllNormalizedAverages($valid_evals, $eval_totals, $reviewer_totals) {
  $ret_val = array();
  // Loop through each of the individuals who was reviewed in this evaluation
  foreach ($valid_evals as $student_id => $evals) {
    // Get the valid reviews for this student
    $norm_avg = calculateSingleNormalizedAverage($evals, $eval_totals, $reviewer_totals);
    $ret_val[$student_id] = $norm_avg;
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
?>