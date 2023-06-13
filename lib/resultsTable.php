<?php
function emitAveragesTable($mc_topics, $mc_answers, $ff_topics, $texts, $scores) {
    echo '<div class="row py-2 mx-1 align-items-stretch border-bottom border-1 border-secondary" style="background-color:#f8f8f8">';
    echo '  <div class="col-2"><b>Criterion</b></div>';
    echo '  <div class="col-2 ms-auto"><b>Average Score</b></div>';
    echo '  <div class="col-2 ms-auto"><b>Median</b></div>';
    echo '</div>';
    $color = '#e1e1e1';
    foreach ($mc_topics as $topic_id => $topic) {
        echo '<div class="row py-2 mx-1 align-items-stretch border-bottom border-1 border-secondary" style="background-color:'.$color.'">';
        echo '  <div class="col-2"><b>'.$topic.'</b></div>';
        $sum = 0;
        $count = 0;
        $med_score = array();
        foreach ($scores as $submit) {
            if (isset($submit[$topic_id])) {
                $sum += $submit[$topic_id];
                $count++;
                $med_score[] = $submit[$topic_id];
            }
        }
        if ($count > 0) {
            $average = round($sum / $count, 2);
            $max = end($mc_answers[$topic_id])[1];
            foreach ($mc_answers[$topic_id] as $response) {
                if ($response[1] > $max) {
                    $max = $response[1];
                }
            }
            echo '<div class="col-2 ms-auto text-center"><b>'.$average.'</b> (out of '.$max.')</div>';
            sort($med_score);
            $mid_point = intdiv(count($med_score), 2);
            if ((count($med_score) % 2 == 0) && ($med_score[$mid_point - 1] !=  $med_score[$mid_point])){
                $low_med = $med_score[$mid_point - 1];
                $hi_med = $med_score[$mid_point];
                foreach ($mc_answers[$topic_id] as $response) {
                    if ($response[1] == $low_med) {
                        $low_text = $response[0];
                    } else if ($response[1] == $hi_med) {
                        $hi_text = $response[0];
                    }
                }
                echo '<div class="col-2 ms-auto text-center"><b>'.$low_text.'</b><br>to<br><b>'.$hi_text.'</b></div>';
            } else {
                $median = $med_score[$mid_point];
                foreach ($mc_answers[$topic_id] as $response) {
                    if ($response[1] == $median) {
                        $med_text = $response[0];
                    }
                }
                echo '<div class="col-2 ms-auto text-center"><b>'.$med_text.'</b></div>';
            }
        } else {
            echo '<div class="col-2 ms-auto text-center">'.NO_SCORE_MARKER.'</div>';
            echo '<div class="col-2 ms-auto text-center">'.NO_SCORE_MARKER.'</div>';
        }
        echo '</div>';
    }
    foreach ($ff_topics as $topic_id => $topic) {
        echo '<div class="row py-2 mx-1 align-items-stretch border-bottom border-1 border-secondary" style="background-color:'.$color.'">';
        echo '  <div class="col-2"><b>'.$topic.'</b></div>';
        $empty = true;
        $text = NO_SCORE_MARKER;
        // Randomly order the freeform answers so that whomever entered each one cannot be guesses (and the order changes each time)
        shuffle($texts);
        foreach ($texts as $submit) {
            if (isset($submit[$topic_id])) {
              if (!$empty) {
                $text .= "<br>".htmlspecialchars($submit[$topic_id]);
              } else {
                $text = htmlspecialchars($submit[$topic_id]);
                $empty = false;
              }
            }
        }
        echo '<div class="col-4 ms-auto text-center"><b>'.$text.'</b></div>';
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