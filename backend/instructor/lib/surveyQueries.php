<?php
function deleteEvalsForSurvey($con, $survey_id) {
  $stmt = $con->prepare('DELETE evals 
                         FROM evals
                         INNER JOIN reviews on reviews.eval_id=evals.id
                         WHERE survey_id=?');
  $stmt->bind_param('i', $survey_id);
  $retVal = $stmt->execute();
  $stmt->close();
  return $retVal;
}

function deleteSurvey($con, $survey_id) {
  $stmt = $con->prepare('DELETE FROM surveys WHERE id=?');
  $stmt->bind_param('i', $survey_id);
  $retVal = $stmt->execute();
  $stmt->close();
  return $retVal;
}

function insertSurvey($con, $course_id, $name, $start, $end, $rubric_id, $survey_type, $pm_mult) {
  $start_string = $start->format('Y-m-d H:i:s');
  $end_string = $end->format('Y-m-d H:i:s');
  $stmt = $con->prepare('INSERT INTO surveys (course_id, name, start_date, end_date, rubric_id, survey_type_id, pm_weight) VALUES (?, ?, ?, ?, ?, ?, ?)');
  $stmt->bind_param('isssiii', $course_id, $name, $start_string, $end_string, $rubric_id, $survey_type, $pm_mult);
  $stmt->execute();
  $survey_id = $con->insert_id;
  $stmt->close();
  return $survey_id;
}

function updateSurvey($con, $survey_id, $name, $start, $end, $rubric_id) {
  $stmt = $con->prepare('UPDATE surveys SET name = ?, start_date = ?, end_date = ?, rubric_id = ? WHERE id = ?');
  $stmt->bind_param('sssii', $name, $start, $end, $rubric_id, $survey_id);
  $retVal = $stmt->execute();
  $stmt->close();
  return $retVal;
}

function isSurveyInstructor($con, $survey_id, $instructor_id) {
  $stmt = $con->prepare('SELECT surveys.course_id 
                         FROM surveys
                         INNER JOIN courses ON surveys.course_id=courses.id
                         INNER JOIN course_instructors ON courses.id=course_instructors.course_id
                         WHERE surveys.id=? AND course_instructors.instructor_id=?');
  $stmt->bind_param('ii', $survey_id, $instructor_id);
  $stmt->execute();
  $result = $stmt->get_result();
  $data = $result->fetch_all(MYSQLI_ASSOC);
  $retVal = $result->num_rows > 0;
  $stmt->close();
  return $retVal;
}

function getSurveyData($con, $survey_id) {
  // Pessimistically assume that this fails
  $retVal = null;
  $stmt = $con->prepare('SELECT course_id, start_date, end_date, name, rubric_id, survey_type_id, pm_weight 
                         FROM surveys 
                         WHERE id=?');
  $stmt->bind_param('i', $survey_id);
  $stmt->execute();
  $result = $stmt->get_result();
  $row = $result->fetch_all(MYSQLI_ASSOC);
  // reply not found on no match
  if ($result->num_rows > 0) {
    $retVal = $row[0];
  }
  $stmt->close();
  return $retVal;
}

function getReviewedData($con, $survey_id) {
  $ret_val = array();
  $stmt = $con->prepare('SELECT DISTINCT students.id, email, name
                         FROM reviews
                         INNER JOIN students ON reviews.reviewed_id=students.id 
                         WHERE survey_id=?');
  $stmt->bind_param('i', $survey_id);
  $stmt->execute();
  $result = $stmt->get_result();
  while ($row = $result->fetch_array(MYSQLI_NUM)) {
    $student_id = $row[0];
    $ret_val[$student_id] = array("email"=>$row[1], "name"=>$row[2]);
  }
  $stmt->close();
  return $ret_val;
}

function getReviewerResultViewsCount($con, $survey_id) {
  $ret_val = array();
  $stmt = $con->prepare('SELECT students.id, visit_count
                         FROM student_visit_data
                         INNER JOIN students ON students.id=student_id
                         WHERE survey_id=?');
  $stmt->bind_param('i', $survey_id);
  $stmt->execute();
  $result = $stmt->get_result();
  while ($row = $result->fetch_array(MYSQLI_NUM)) {
    $id = $row[0];
    $count = $row[1];
    $ret_val[$id] = $count;
  }
  $stmt->close();
  return $ret_val;
}
?>