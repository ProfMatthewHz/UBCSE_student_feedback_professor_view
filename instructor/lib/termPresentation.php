<?php
function emit_course_accordian($widgetId, $course_info) {
  echo 
' <div class="accordion ms-1" id="'.$widgetId.'">
    <div class="accordion-item shadow">
      <h2 class="accordion-header" id="header'.$widgetId.'">
        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse'.$widgetId.'" aria-expanded="false" aria-controls="collapse'.$widgetId.'">'.$course_info["name"].'
        </button>
      </h2>
      <div id="collapse'.$widgetId.'" class="accordion-collapse collapse" aria-labelledby="header'.$widgetId.'">
        <div class="accordion-body">';
            foreach ($course_info['upcoming'] as $survey) {
              echo '<div class="container"><div class="row justify-content-evenly">
                      <div class="col-2">'.$survey['name'].'</div>
                      <div class="col-5">'.$survey['start_date'].' to '.$survey['expiration_date'].'</div>
                      <div class="col-auto"><a href="surveyPairings.php?survey='.$survey['id'].'">Modify Assignments</a> | <a href="surveyDelete.php?survey=' . $survey['id'] . '">Delete</a></div>
                    </div></div>';
            }
            foreach ($course_info['active'] as $survey) {
              echo '<div class="container"><div class="row justify-content-evenly">
                      <div class="col-2">'.$survey['name'].'</div>
                      <div class="col-5">'.$survey['start_date'].' to '.$survey['expiration_date'].'</div>
                      <div class="col-auto"><a href="surveyResults.php?survey=' . $survey['id']. '">View Results</a> | <a href="surveyDelete.php?survey=' . $survey['id'] . '">Delete</a></div>
                    </div></div>';
            }
            foreach ($course_info['expired'] as $survey) {
              echo '<div class="container"><div class="row justify-content-evenly">
                      <div class="col-2">'.$survey['name'].'</div>
                      <div class="col-5">'.$survey['start_date'].' to '.$survey['expiration_date'].'</div>
                      <div class="col-auto"><a href="surveyResults.php?survey=' . $survey['id']. '">View Results</a> | <a href="surveyDelete.php?survey=' . $survey['id'] . '">Delete</a></div>
                    </div></div>';
            }
            if (count($course_info['upcoming']) + count($course_info['active']) + count($course_info['expired']) == 0) {
              echo '<div class="container"><div class="row justify-content-center"><p><i>No surveys created yet</i></p></div></div>';
            }
  echo
  '     </div>
      </div>
    </div>
  </div>';
}

function emit_term_accordian($counter, $name, $course_list) {
  echo
'   <div class="accordion-item shadow">
      <h2 class="accordion-header" id="header'.$counter.'">
        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse'.$counter.'" aria-expanded="false" aria-controls="collapse'.$counter.'">'.$name.'
        </button>
      </h2>
      <div id="collapse'.$counter.'" class="accordion-collapse collapse" aria-labelledby="header'.$counter.'">
        <div class="accordion-body">';
  $counterTwo = 0;
  foreach ($course_list as $id => $course) {
    $widgetId = $counter."part".$counterTwo;
    emit_course_accordian($widgetId, $course);
    $counterTwo++;
  }
  echo
'       </div>
      </div>
    </div>';
}