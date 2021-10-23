<?php
  function emitResultsTable($topics, $answers, $scores, $members) {
    echo '<div class="col-2"><b>Name</b></div>';
    foreach ($topics as $topic_id => $topic) {
        echo '<div class="col-2 ms-auto"><b>'.$topic.'</b></div>';
    }
    echo '</div>';
    $shaded = true;
    foreach ($members as $reviewer_id => $name) {
        if ($shaded) {
            $bg_color = "#e1e1e1";
        } else {
            $bg_color = "#f8f8f8";
        }
        echo '<div class="row py-2 mx-1 align-items-center border-bottom border-1 border-secondary" style="background-color:'.$bg_color.'">';
        echo '  <div class="col-2 text-center"><b>'.$name.'</b></div>';
        foreach ($topics as $topic_id => $topic) {
            echo '<div class="col-2 ms-auto">'.$answers[$topic_id][$scores[$name][$topic_id]].'</div>';
        }
        echo '</div>';
        $shaded = !$shaded;
    }
  }
?>