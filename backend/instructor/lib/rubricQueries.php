<?php
function getIdFromDescription($con, $name) {
  // Pessimistically assume that the rubric doesn't exist
  $retVal = 0;
  $stmt = $con->prepare('SELECT id FROM rubrics WHERE description=?');
  $stmt->bind_param('s', $name);
  $stmt->execute();
  $result = $stmt->get_result();
  $data = $result->fetch_all(MYSQLI_ASSOC);
  if ($result->num_rows > 0) {
    $retVal = $data[0]['id'];
  }
  $stmt->close();
  return $retVal;
}

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

function insertRubricTopic($con, $rubric_id, $question, $question_type) {
  $stmt = $con->prepare('INSERT INTO rubric_topics (rubric_id, question, question_response) VALUES(?,?,?)');
  $stmt->bind_param('iss', $rubric_id, $question, $question_type);
  $stmt->execute();
  $ret_val = $stmt->insert_id;
  $stmt->close();
  return $ret_val;    
}

function insertRubricReponse($con, $topic_id, $level_id, $response) {
  $stmt = $con->prepare('INSERT INTO rubric_responses (topic_id, rubric_score_id, response) VALUES(?,?,?)');
  $stmt->bind_param('iis', $topic_id, $level_id, $response);
  $stmt->execute();
  $ret_val = $stmt->insert_id;
  $stmt->close();
  return $ret_val;
}

function getRubrics($con) {
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

function getRubricName($con, $rubric_id) {
  $stmt = $con->prepare('SELECT description FROM rubrics WHERE id=?');
  $stmt->bind_param('i', $rubric_id);
  $stmt->execute();
  $ret_val = "";
  $result = $stmt->get_result();
  while ($row = $result->fetch_array(MYSQLI_NUM)) {
    $ret_val = $row[0];
  }
  $stmt->close();
  return $ret_val;
}

function getRubricScores($con, $rubric_id) {
  $ret_val = array();
  $stmt = $con->prepare('SELECT id, name, score 
                         FROM rubric_scores
                         WHERE rubric_id=? 
                         ORDER BY score');
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

function getTopicResponses($con, $topic_id) {
  $ret_val = array();
  $stmt = $con->prepare('SELECT rubric_score_id, response 
                         FROM rubric_responses 
                         INNER JOIN rubric_scores ON rubric_score_id=rubric_scores.id
                         WHERE topic_id=? 
                         ORDER BY score');
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

function getRubricTopics($con, $rubric_id) {
  $ret_val = array();
  $stmt = $con->prepare('SELECT id, question, question_response 
                         FROM rubric_topics 
                         WHERE rubric_id=? 
                         ORDER BY id');
  $stmt->bind_param('i', $rubric_id);
  $stmt->execute();
  $result = $stmt->get_result();
  while ($row = $result->fetch_array(MYSQLI_NUM)) {
    $topic_data = array();
    $topic_id = $row[0];
    $question = $row[1];
    $question_type = $row[2];
    $topic_data["question"] = $question;
    $topic_responses = getTopicResponses($con, $topic_id);
    $topic_data["responses"] = $topic_responses;
    $topic_data["type"] = $question_type;
    $ret_val[] = $topic_data;
  }
  $stmt->close();
  return $ret_val;
}

function getRubricData($con, $rubric_id) {
  $ret_val = array();
  $rubric_scores = getRubricScores($con, $rubric_id);
  $ret_val["scores"] = $rubric_scores;
  $rubric_topics = getRubricTopics($con, $rubric_id, $rubric_scores);
  $ret_val["topics"] = $rubric_topics;
  return $ret_val;
}
?>