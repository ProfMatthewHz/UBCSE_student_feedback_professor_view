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
  $stmt = $con->prepare('INSERT INTO rubric_responses (topic_id, score_id, response) VALUES(?,?,?)');
  $stmt->bind_param('iis', $topic_id, $level_id, $response);
  $stmt->execute();
  $ret_val = $stmt->insert_id;
  $stmt->close();
  return $ret_val;
}

function selectRubrics($con) {
  $ret_val = array();
  $stmt = $con->prepare('SELECT id, description FROM rubrics');
  $stmt->execute();
  $result = $stmt->get_result();
  while ($row = $result->fetch_array(MYSQLI_NUM)) {
    $rubric_id = $row[0];
    $description = $row[1];
    $ret_val[$rubric_id] = $description;
  }
  $stmt->close();
  return $ret_val;
}

function selectRubricScores($con, $rubric_id) {
  $ret_val = array();
  $stmt = $con->prepare('SELECT id, name, score FROM rubric_scores WHERE rubric_id=?');
  $stmt->bind_param('i', $rubric_id);
  $stmt->execute();
  $result = $stmt->get_result();
  while ($row = $result->fetch_array(MYSQLI_NUM)) {
    $score_data = array();
    $score_id = $row[0];
    $score_data["name"] = $row[1];
    $score_data["score"] = $row[2];
    $ret_val[$score_id] = $score_data;
  }
  $stmt->close();
  return $ret_val;
}

function selectTopicResponses($con, $topic_id) {
  $ret_val = array();
  $stmt = $con->prepare('SELECT score_id, response FROM rubric_responses WHERE topic_id=?');
  $stmt->bind_param('i', $topic_id);
  $stmt->execute();
  $result = $stmt->get_result();
  while ($row = $result->fetch_array(MYSQLI_NUM)) {
    $score_id = $row[0];
    $response = $row[1];
    $ret_val[$score_id] = $response;
  }
  $stmt->close();
  return $ret_val;
}

function selectTopics($con, $rubric_id) {
  $ret_val = array();
  $stmt = $con->prepare('SELECT id, question FROM rubric_topics WHERE rubric_id=?');
  $stmt->bind_param('i', $rubric_id);
  $stmt->execute();
  $result = $stmt->get_result();
  while ($row = $result->fetch_array(MYSQLI_NUM)) {
    $topic_data = array();
    $topic_id = $row[0];
    $question = $row[1];
    $topic_data["question"] = $question;
    $topic_responses = selectTopicResponses($con, $topic_id);
    $topic_data["responses"] = $topic_responses;
    $ret_val[] = $topic_data;
  }
  $stmt->close();
  return $ret_val;
}

function getRubricData($con, $rubric_id) {
  $ret_val = array();
  $rubric_scores = selectRubricScores($con, $rubric_id);
  $ret_val["scores"] = $rubric_scores;
  $rubric_topics = selectTopics($con, $rubric_id, $rubric_scores);
  $ret_val["topics"] = $rubric_topics;
  return $ret_val;
}
?>