<?php
function emitAveragesTable($mc_topics, $mc_answers, $ff_topics, $texts, $scores) {
    echo '<div class="row py-2 mx-1 align-items-stretch border-bottom border-1 border-secondary" style="background-color:#f8f8f8">';
    foreach ($mc_topics as $topic_id => $topic) {
        echo '<div class="col-2 ms-auto"><b>'.$topic.'</b></div>';
    }
    foreach ($ff_topics as $topic_id => $topic) {
        echo '<div class="col-2 ms-auto"><b>'.$topic.'</b></div>';
    }
    echo '</div>';
    // echo '<div class="row py-2 mx-1 align-items-stretch border-bottom border-1 border-secondary" style="background-color:#e1e1e1">';
    // foreach ($mc_topics as $topic_id => $topic) {
    //     echo '<div class="col-2 ms-auto"><b>'.end($mc_answers[$topic_id])[0].'</b></div>';
    // }
    // foreach ($ff_topics as $topic_id => $topic) {
    //     echo '<div class="col-2 ms-auto"></div>';
    // }
    // echo '</div>';
    echo '<div class="row py-2 mx-1 align-items-stretch border-bottom border-1 border-secondary" style="background-color:#f8f8f8"">';
    foreach ($mc_topics as $topic_id => $topic) {
        $sum = 0;
        $count = 0;
        foreach ($scores as $submit) {
            if (isset($submit[$topic_id])) {
              $sum += $submit[$topic_id];
              $count++;
            }
        }
        if ($count > 0) {
            $average = $sum / $count;
            sort($scores);
            if (count($scores) % 2 == 0) {
                $median = ($scores[count($scores) / 2] + $scores[count($scores) / 2 - 1]) / 2;
            } else {
                $median = $scores[count($scores) / 2];
            }
            echo '<div class="col-2 ms-auto text-center"><b>'.$average.'</b> (out of '.end($mc_answers[$topic_id])[1].')</div>';
            echo '<!-- Median: <b>'.$median.'</b></div> -->';
        } else {
            echo '<div class="col-2 ms-auto text-center">'.NO_SCORE_MARKER.'</div>';
        }
    }
    foreach ($ff_topics as $topic_id => $topic) {
        $count = 0;
        $text = '';
        foreach ($texts as $submit) {
            if (isset($submit[$topic_id])) {
              if ($count != 0) {
                $text .= "<br>";
              }
              $text .= htmlspecialchars($submit[$topic_id]);
              $count++;
            }
        }
        if ($count > 0) {
            echo '<div class="col-2 ms-auto text-center"><b>'.$text.'</b></div>';
        } else {
            echo '<div class="col-2 ms-auto text-center">'.NO_SCORE_MARKER.'</div>';
        }
    }
    echo '</div>';
  }

  function emitResultsTable($mc_topics, $mc_answers, $ff_topics, $ff_answers, $scores, $members) {
    echo '<div class="col-2"><b>Name</b></div>';
    foreach ($mc_topics as $topic_id => $topic) {
        echo '<div class="col-2 ms-auto"><b>'.$topic.'</b></div>';
    }
    foreach ($ff_topics as $topic_id => $topic) {
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
        echo '<div class="row py-2 mx-1 align-items-stretch border-bottom border-1 border-secondary" style="background-color:'.$bg_color.'">';
        echo '  <div class="col-2 text-center"><b>'.$name.'</b></div>';
        foreach ($mc_topics as $topic_id => $topic) {
            echo '<div class="col-2 ms-auto">'.$mc_answers[$topic_id][$scores[$reviewer_id][$topic_id]].'</div>';
        }
        foreach ($ff_topics as $topic_id => $topic) {
            echo '<div class="col-2 ms-auto">'.htmlspecialchars($ff_answers[$reviewer_id][$topic_id]).'</div>';
        }
        echo '</div>';
        $shaded = !$shaded;
    }
  }
?>