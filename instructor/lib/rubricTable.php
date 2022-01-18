<?php
  function emitRubricTable($topics, $scores) {
    echo '<div class="col-2"><b>Criterion</b></div>';
    foreach ($scores as $score_id => $score_data) {
      $score_name = $score_data[0];
      $score_points = $score_data[1];
      echo '<div class="col-2 ms-auto"><b>'.$score_name.' ('.$score_points.' pts)</b></div>';
    }
    echo '</div>';
    $shaded = true;
    foreach ($topics as $topic_id => $topic_data) {
        if ($shaded) {
          $bg_color = "#e1e1e1";
        } else {
          $bg_color = "#f8f8f8";
        }
        $name = $topic_data["question"];
        $responses = $topic_data["responses"];
        echo '<div class="row py-2 mx-1 align-items-stretch border-bottom border-1 border-secondary" style="background-color:'.$bg_color.'">';
        echo '  <div class="col-2 text-center"><b>'.$name.'</b></div>';
        foreach (array_keys($scores) as $score_id) {
          $response_text = $responses[$score_id];
          echo '<div class="col-2 ms-auto">'.$response_text.'</div>';
        }
        echo '</div>';
        $shaded = !$shaded;
    }
  }
?>