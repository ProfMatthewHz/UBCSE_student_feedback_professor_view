<?php
function insertRubric($con, $name) {
  $stmt = $con->prepare('INSERT INTO rubrics (description) VALUES(?)');
  $stmt->bind_param('s', $name);
  $stmt->execute();
  $ret_val = $stmt->insert_id;
  $stmt->close();
  return $ret_val;
}

function insertRubricScore($con, $rubric_id, $name, $score) {
  $stmt = $con->prepare('INSERT INTO rubric_scores (rubric_id, name, score) VALUES(?,?,?)');
  $stmt->bind_param('isi', $rubric_id, $name, $score);
  $stmt->execute();
  $ret_val = $stmt->insert_id;
  $stmt->close();
  return $ret_val;  
}

function insertRubricTopic($con, $rubric_id, $question) {
  $stmt = $con->prepare('INSERT INTO rubric_topics (rubric_id, question) VALUES(?,?)');
  $stmt->bind_param('is', $rubric_id, $question);
  $stmt->execute();
  $ret_val = $stmt->insert_id;
  $stmt->close();
  return $ret_val;    
}

function insertRubricReponse($con, $topic_id, $level_id, $response) {
  $stmt = $con->prepare('INSERT INTO rubric_responses (rubric_id, score_id, response) VALUES(?,?,?)');
  $stmt->bind_param('iis', $topic_id, $level_id, $response);
  $stmt->execute();
  $ret_val = $stmt->insert_id;
  $stmt->close();
  return $ret_val;
}
?>