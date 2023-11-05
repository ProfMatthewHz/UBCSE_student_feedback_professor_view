<?php
function emit_course($widgetId, $course_id, $course_info) {
  echo 
' <div class="accordion ms-1" id="'.$widgetId.'">
    <div class="accordion-item shadow">
      <h2 class="accordion-header" id="header'.$widgetId.'">
        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse'.$widgetId.'" aria-expanded="false" aria-controls="collapse'.$widgetId.'">'.$course_info["code"].' '.$course_info["name"].'
        </button>
      </h2>
      <div id="collapse'.$widgetId.'" class="accordion-collapse collapse" aria-labelledby="header'.$widgetId.'">
        <div class="accordion-body"><div class="container">';
         if ($course_info['mutable']) {
          echo '<div class="row justify-content-end pb-3"><div class="col-auto"><button type="button" class="btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#rosterUpdateModal" data-bs-courseid="'.$course_id.'" data-bs-coursename="'.$course_info["name"].'">Update Roster</button></div></div>';
         } 
         if (count($course_info['upcoming']) + count($course_info['active']) + count($course_info['expired']) != 0) {
            echo
            '<div class="row justify-content-evenly">
              <div class="col"><b>Survey Name</b></div>
              <div class="col"><b>Dates Available</b></div>
              <div class="col"><b>Completion Rates</b></div>
              <div class="col"><b>Actions</b></div>
            </div>';
          }
          foreach ($course_info['upcoming'] as $survey) {
            echo '<div class="row pb-2 justify-content-evenly">
                    <div class="col">'.$survey['name'].'</div>
                    <div class="col">'.$survey['start_date'].' to '.$survey['end_date'].'</div>
                    <div class="col">Not yet active</div><div class="col">';
            echo '<a href="surveyUpdate.php?survey='.$survey['id'].'">Update</a> | <a href="surveyDuplicate.php?survey=' . $survey['id'] . '">Duplicate</a> | ';
            echo '<a href="surveyPairings.php?survey='.$survey['id'].'">Modify Assignments</a> | <a href="surveyDelete.php?survey=' . $survey['id'] . '">Delete</a></div>
                  </div>';
          }
          foreach ($course_info['active'] as $survey) {
            echo '<div class="row row pb-2 justify-content-evenly">
                    <div class="col">'.$survey['name'].'</div>
                    <div class="col">'.$survey['start_date'].' to '.$survey['end_date'].'</div>
                    <div class="col">'.$survey['completion'].'</div><div class="col">';
            if ($course_info['mutable']) {
              echo '<a href="surveyUpdate.php?survey='.$survey['id'].'">Extend</a> | <a href="surveyDuplicate.php?survey=' . $survey['id'] . '">Duplicate</a> | ';
            }
            echo '<a href="surveyResults.php?survey=' . $survey['id']. '">View Results</a> | <a href="surveyDelete.php?survey=' . $survey['id'] . '">Delete</a></div>
                  </div>';
          }
          foreach ($course_info['expired'] as $survey) {
            echo '<div class="row row pb-2 justify-content-evenly">
                    <div class="col">'.$survey['name'].'</div>
                    <div class="col">'.$survey['start_date'].' to '.$survey['end_date'].'</div>
                    <div class="col">'.$survey['completion'].'</div><div class="col">';
            if ($course_info['mutable']) {
              echo '<a href="surveyDuplicate.php?survey=' . $survey['id'] . '">Duplicate</a> | ';
            }
            echo '<a href="reviewResults.php?survey='.$survey['id'].'">View Reviewers</a> | <a href="surveyResults.php?survey=' . $survey['id']. '">View Results</a> | <a href="surveyDelete.php?survey=' . $survey['id'] . '">Delete</a></div></div>';
          }
          if (count($course_info['upcoming']) + count($course_info['active']) + count($course_info['expired']) == 0) {
            echo '<div class="row justify-content-center"><p><i>No surveys created yet</i></p></div>';
          }
          if ($course_info['mutable']) {
            echo '<div class="row justify-content-center pt-3"><div class="col-auto"><a href="surveyAdd.php?course='.$course_id.'" class="btn btn-outline-success">+ Add Survey</a></div></div>';
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
        <button class="accordion-button fs-4" type="button" data-bs-toggle="collapse" data-bs-target="#collapse'.$counter.'" aria-expanded="true" aria-controls="collapse'.$counter.'">'.$name.'</button>
      </h2>
      <div id="collapse'.$counter.'" class="accordion-collapse collapse show" aria-labelledby="header'.$counter.'">
        <div class="accordion-body">';
  $counterTwo = 0;
  foreach ($course_list as $id => $course) {
    $widgetId = $counter."part".$counterTwo;
    emit_course($widgetId, $id, $course);
    $counterTwo++;
  }
  echo
'       </div>
      </div>
    </div>';
}
?>