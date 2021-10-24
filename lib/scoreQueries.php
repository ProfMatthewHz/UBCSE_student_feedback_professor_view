<?php
  function getEvalScores($db_connection, $eval_id) {
    $retVal = array();
    $stmt = $db_connection->prepare('SELECT topic_id, score_id FROM scores2 WHERE eval_id=? AND score_id IS NOT NULL');
    $stmt->bind_param('i', $eval_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_array(MYSQLI_NUM)) {
      $retVal[$row[0]] = $row[1];
    }
    return $retVal;
  }

  function getReviewScores($db_connection, $review_id, $topics, $answers) {
    $score1 = 0;
    $score2 = 0;
    $score3 = 0;
    $score4 = 0;
    $score5 = 0;
    $query_str = 'SELECT score1, score2, score3, score4, score5 FROM scores INNER JOIN evals ON scores.evals_id=evals.id WHERE evals.reviewers_id=?';
    // Select the scores for this student
    $stmt = $db_connection->prepare($query_str);
    $stmt->bind_param('i', $review_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if (mysqli_num_rows($result) != 1) {
        // This is not a valid survey for this student
        echo "Cannot find a survey submission: Talk to your instructor about this error.";
        http_response_code(400);
        exit();
    }
    $retVal = array();
    // MHz -- HACK to make this look like we are using a dynamic rubric
    $row = $result->fetch_array(MYSQLI_NUM);
    $topic_ids = array_keys($topics);
    for ($idx = 0; $idx < count($topic_ids); $idx++) {
      // Need to add 1 to the score data to make it equivalent to the rubric_score.id field
      $retVal[$topic_ids[$idx]] = $row[$idx] + 1;
    }
    $stmt->close();
    return $retVal;
  }
  function insertNewReview($db_connection, $eval_id, $student_scores) {
    $query_str = 'INSERT INTO scores (evals_id, score1, score2, score3, score4, score5) VALUES (?,?,?,?,?,?)';
    $stmt = $db_connection->prepare($query_str);
    $a =$student_scores[0];
    $b =$student_scores[1];
    $c =$student_scores[2];
    $d =$student_scores[3];
    $e =$student_scores[4];
    $stmt->bind_param('iiiiii',$eval_id, $a, $b, $c, $d, $e);
    $stmt->execute();
    $stmt->close();
  }

  function updateExistingReview($db_connection, $eval_id, $student_scores) {
    $query_str = 'UPDATE scores SET score1 = ?, score2=?, score3=?, score4=?, score5=? WHERE evals_id=?';
    $stmt = $db_connection->prepare($query_str);
    $a =$student_scores[0];
    $b =$student_scores[1];
    $c =$student_scores[2];
    $d =$student_scores[3];
    $e =$student_scores[4];
    $stmt->bind_param('iiiiii',$a, $b, $c, $d, $e, $eval_id);
    $stmt->execute();
    $stmt->close();
  }

  function insertNewScore($db_connection, $eval_id, $topic_id, $score_id) {
    $query_str = 'INSERT INTO scores2 (eval_id, topic_id, score_id) VALUES (?,?,?)';
    $stmt = $db_connection->prepare($query_str);
    $stmt->bind_param('iii',$eval_id, $topic_id, $score_id);
    $stmt->execute();
    $stmt->close();
  }

  function updateExistingScore($db_connection, $eval_id, $topic_id, $score_id) {
    $query_str = 'UPDATE scores2 SET score_id=? WHERE eval_id=? AND topic_id=?';
    $stmt = $db_connection->prepare($query_str);
    $stmt->bind_param('iii',$score_id, $eval_id, $topic_id);
    $stmt->execute();
    $stmt->close();
  }
?>