<?php
  function emitRubricTable($topics, $scores) {
    $ret_val = '<div class="col-2"><b>Criterion</b></div>';
    foreach ($scores as $score_id => $score_data) {
      $score_name = $score_data["name"];
      $score_points = $score_data["score"];
      $ret_val = $ret_val.'<div class="col-2 ms-auto"><b>'.$score_name.' ('.$score_points.' pts)</b></div>';
    }
    $ret_val = $ret_val.'</div>';
    $shaded = true;
    foreach ($topics as $topic_data) {
        if ($shaded) {
          $bg_color = "#e1e1e1";
        } else {
          $bg_color = "#f8f8f8";
        }
        $name = $topic_data["question"];
        $ret_val = $ret_val.'<div class="row py-2 mx-1 align-items-stretch border-bottom border-1 border-secondary" style="background-color:'.$bg_color.'">';
        $ret_val = $ret_val.'  <div class="col-2 text-center"><b>'.$name.'</b></div>';
        if ($topic_data["type"] == MC_QUESTION_TYPE) {
          $responses = $topic_data["responses"];
          foreach (array_keys($scores) as $score_id) {
            $response_text = $responses[$score_id];
            $ret_val = $ret_val.'<div class="col-2 ms-auto">'.$response_text.'</div>';
          }
        } else {
          $num_cols = count($scores) * 2;
          $ret_val = $ret_val.'<div class="col-'.$num_cols.' ms-auto">Freeform response</div>';
        }
        $ret_val = $ret_val.'</div>';
        $shaded = !$shaded;
    }
    return $ret_val;
  }
?>