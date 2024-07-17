<?php
function getVisitCount($con, $survey_id, $student_id) {
  $ret_val = 0;
  $stmt = $con->prepare("SELECT visit_count FROM student_visit_data WHERE reviewer_id = ? AND survey_id = ?");
  $stmt->bind_param("ii", $student_id, $survey_id);
  $stmt->execute();
  $stmt->bind_result($ret_val);
  if (!$stmt->fetch()) {
    $ret_val = 0;
  }
  $stmt->close();
  return $ret_val;
}

function createFirstVisit($con, $survey_id, $student_id) {
  $current_timestamp = date('Y-m-d H:i:s');
  $stmt = $con->prepare("INSERT INTO student_visit_data (reviewer_id, survey_id, visit_count, last_visit) VALUES (?, ?, 1, ?)");
  $stmt->bind_param("iis", $student_id, $survey_id, $current_timestamp);
  $stmt->execute();
  $stmt->close();
}

function updateVisitCount($con, $survey_id, $student_id, $visit_count) {
  $current_timestamp = date('Y-m-d H:i:s');
  $stmt = $con->prepare("UPDATE student_visit_data SET visit_count = ?, last_visit = ? WHERE reviewer_id = ? AND survey_id = ?");
  $stmt->bind_param("isii", $visit_count, $current_timestamp, $student_id, $survey_id);
  $stmt->execute();
  $stmt->close();
}
?>