<?php
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
?>