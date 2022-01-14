<?php
function emit_course_accordian($course_id, $course_info) {
  echo 
' <div class="accordion ms-1" id="'.$course_info["name"].$course_id.'">
    <div class="accordion-item shadow">
      <h2 class="accordion-header" id="'.$course_info["name"].$course_id.'head">
        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePrior" aria-expanded="false" aria-controls="collapsePrior">'.$course_info["name"].'
        </button>
      </h2>
      <div id="collapsePrior" class="accordion-collapse collapse" aria-labelledby="headerPrior">
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

function emit_term_accordian($name, $course_list) {
  echo
'   <div class="accordion-item shadow">
      <h2 class="accordion-header" id="header'.$name.'">
        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse'.$name.'" aria-expanded="false" aria-controls="collapse'.$name.'">'.$name.'
        </button>
      </h2>
      <div id="collapse'.$name.'" class="accordion-collapse collapse" aria-labelledby="header'.$name.'">
        <div class="accordion-body">';
  foreach ($course_list as $id => $course) {
    emit_course_accordian($id, $course);
  }
  echo
'       </div>
      </div>
    </div>';
}