<?php
  function getEvalScores($db_connection, $eval_id) {
    $query_str = 'SELECT topic_id, rubric_score_id 
                  FROM scores
                  WHERE eval_id=?';
    $retVal = array();
    $stmt = $db_connection->prepare($query_str);
    $stmt->bind_param('i', $eval_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_array(MYSQLI_NUM)) {
      $retVal[$row[0]] = $row[1];
    }
    return $retVal;
  }

  function getEvalTexts($db_connection, $eval_id) {
    $query_str = 'SELECT topic_id, response 
                  FROM freeforms
                  WHERE eval_id=? AND response IS NOT NULL';
    $retVal = array();
    $stmt = $db_connection->prepare($query_str);
    $stmt->bind_param('i', $eval_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_array(MYSQLI_NUM)) {
      $retVal[$row[0]] = $row[1];
    }
    return $retVal;
  }

  function getReviewPoints($db_connection, $review_id, $topics) {
    $query_str = 'SELECT topic_id, score 
                  FROM scores
                  INNER JOIN rubric_scores ON scores.rubric_score_id=rubric_scores.id 
                  INNER JOIN evals ON scores.eval_id=evals.id
                  WHERE evals.review_id=?';
    $retVal = array();
    // Prepare the next selection statement
    $stmt = $db_connection->prepare($query_str);
    $stmt->bind_param('i', $review_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_array(MYSQLI_NUM)) {
      $topic_id = $row[0];
      $score = $row[1];
      if (!array_key_exists($topic_id, $topics)) {
        // This should never be able to happen
        echo "ERROR: Survey submission was invalid. Talk to your instructor about this error.";
        http_response_code(400);
        exit();
      }
      $retVal[$topic_id] = $score;
    }
    $stmt->close();
    return $retVal;
  }

  function getReviewScores($db_connection, $review_id, $topics) {
    $query_str = 'SELECT topic_id, rubric_score_id 
                  FROM scores
                  INNER JOIN evals ON scores.eval_id=evals.id 
                  WHERE evals.review_id=?';
    $retVal = array();
    // Prepare the next selection statement
    $stmt = $db_connection->prepare($query_str);
    $stmt->bind_param('i', $review_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_array(MYSQLI_NUM)) {
      $topic_id = $row[0];
      $score = $row[1];
      if (!array_key_exists($topic_id, $topics)) {
        // This should not ever be able to happen
        echo "ERROR: Survey submission was invalid. Talk to your instructor about this error.";
        http_response_code(400);
        exit();
      }
      $retVal[$topic_id] = $score;
    }
    $stmt->close();
    return $retVal;
  }

  function getReviewText($db_connection, $review_id, $topics) {
    $query_str = 'SELECT topic_id, response 
                  FROM freeforms
                  INNER JOIN evals ON freeforms.eval_id=evals.id 
                  WHERE evals.review_id=?';
    $retVal = array();
    // Prepare the next selection statement
    $stmt = $db_connection->prepare($query_str);
    $stmt->bind_param('i', $review_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_array(MYSQLI_NUM)) {
      $topic_id = $row[0];
      $text = $row[1];
      if (!array_key_exists($topic_id, $topics)) {
        // This should not be able to happen
        echo "ERROR: Survey submission was invalid. Talk to your instructor about this error.";
        http_response_code(400);
        exit();
      }
      $retVal[$topic_id] = $text;
    }
    $stmt->close();
    return $retVal;
  }

  function insertNewScore($db_connection, $eval_id, $topic_id, $score_id) {
    $query_str = 'INSERT INTO scores (eval_id, topic_id, rubric_score_id) VALUES (?,?,?)';
    $stmt = $db_connection->prepare($query_str);
    $stmt->bind_param('iii',$eval_id, $topic_id, $score_id);
    $stmt->execute();
    $stmt->close();
  }

  function updateExistingScore($db_connection, $eval_id, $topic_id, $score_id) {
    $query_str = 'UPDATE scores SET rubric_score_id=? WHERE eval_id=? AND topic_id=?';
    $stmt = $db_connection->prepare($query_str);
    $stmt->bind_param('iii',$score_id, $eval_id, $topic_id);
    $stmt->execute();
    $stmt->close();
  }

  function insertNewText($db_connection, $eval_id, $topic_id, $text) {
    $query_str = 'INSERT INTO freeforms (eval_id, topic_id, response) VALUES (?,?,?)';
    $stmt = $db_connection->prepare($query_str);
    $stmt->bind_param('iis',$eval_id, $topic_id, $text);
    $stmt->execute();
    $stmt->close();
  }

  function updateExistingText($db_connection, $eval_id, $topic_id, $text) {
    $query_str = 'UPDATE freeforms SET response=? WHERE eval_id=? AND topic_id=?';
    $stmt = $db_connection->prepare($query_str);
    $stmt->bind_param('sii',$text, $eval_id, $topic_id);
    $stmt->execute();
    $stmt->close();
  }
?>