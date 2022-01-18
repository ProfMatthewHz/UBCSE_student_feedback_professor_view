<?php
function emit_course($widgetId, $course_info) {
  echo 
' <div class="accordion ms-1" id="'.$widgetId.'">
    <div class="accordion-item shadow">
      <h2 class="accordion-header" id="header'.$widgetId.'">
        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse'.$widgetId.'" aria-expanded="false" aria-controls="collapse'.$widgetId.'">'.$course_info["code"].' '.$course_info["name"].'
        </button>
      </h2>
      <div id="collapse'.$widgetId.'" class="accordion-collapse collapse" aria-labelledby="header'.$widgetId.'">
        <div class="accordion-body"><div class="container">';
          if (count($course_info['upcoming']) + count($course_info['active']) + count($course_info['expired']) != 0) {
            echo
            '<div class="row justify-content-evenly">
              <div class="col"><b>Survey Name</b></div>
              <div class="col-6"><b>Dates Available</b></div>
              <div class="col"><b>Actions</b></div>
            </div>';
          }
          foreach ($course_info['upcoming'] as $survey) {
            echo '<div class="row justify-content-evenly">
                    <div class="col">'.$survey['name'].'</div>
                    <div class="col-6">'.$survey['start_date'].' to '.$survey['expiration_date'].'</div>
                    <div class="col"><a href="surveyPairings.php?survey='.$survey['id'].'">Modify Assignments</a> | <a href="surveyDelete.php?survey=' . $survey['id'] . '">Delete</a></div>
                  </div>';
          }
          foreach ($course_info['active'] as $survey) {
            echo '<div class="row justify-content-evenly">
                    <div class="col">'.$survey['name'].'</div>
                    <div class="col-6">'.$survey['start_date'].' to '.$survey['expiration_date'].'</div>
                    <div class="col"><a href="surveyResults.php?survey=' . $survey['id']. '">View Results</a> | <a href="surveyDelete.php?survey=' . $survey['id'] . '">Delete</a></div>
                  </div>';
          }
          foreach ($course_info['expired'] as $survey) {
            echo '<div class="row justify-content-evenly">
                    <div class="col">'.$survey['name'].'</div>
                    <div class="col-6">'.$survey['start_date'].' to '.$survey['expiration_date'].'</div>
                    <div class="col"><a href="surveyResults.php?survey=' . $survey['id']. '">View Results</a> | <a href="surveyDelete.php?survey=' . $survey['id'] . '">Delete</a></div>
                  </div>';
          }
          if (count($course_info['upcoming']) + count($course_info['active']) + count($course_info['expired']) == 0) {
            echo '<div class="row justify-content-center"><p><i>No surveys created yet</i></p></div>';
          }
          if ($course_info['mutable']) {
            echo '<div class="row justify-content-center"><div class="col-auto"><a href="surveyAdd.php" class="btn btn-outline-success">+ Add Survey</a></div></div>';
          }
  echo
  '     </div></div>
      </div>
    </div>
  </div>';
}

function emit_term($counter, $name, $course_list) {
  echo
'   <div class="accordion-item shadow">
      <h2 class="accordion-header" id="header'.$counter.'">
        <button class="accordion-button fs-4" type="button" data-bs-toggle="collapse" data-bs-target="#collapse'.$counter.'" aria-expanded="true" aria-controls="collapse'.$counter.'">'.$name.'
        </button>
      </h2>
      <div id="collapse'.$counter.'" class="accordion-collapse collapse show" aria-labelledby="header'.$counter.'">
        <div class="accordion-body">';
  $counterTwo = 0;
  foreach ($course_list as $id => $course) {
    $widgetId = $counter."part".$counterTwo;
    emit_course($widgetId, $course);
    $counterTwo++;
  }
  echo
'       </div>
      </div>
    </div>';
}
?>