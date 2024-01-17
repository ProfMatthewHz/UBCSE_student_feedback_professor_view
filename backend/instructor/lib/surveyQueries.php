<?php
function deleteSurvey($con, $survey_id) {
  $stmt = $con->prepare('DELETE FROM surveys WHERE id=?');
  $stmt->bind_param('i', $survey_id);
  $retVal = $stmt->execute();
  $stmt->close();
  return $retVal;
}

function insertSurvey($con, $course_id, $name, $start, $end, $rubric_id, $survey_type) {
  $start_string = $start->format('Y-m-d H:i:s');
  $end_string = $end->format('Y-m-d H:i:s');
  $stmt = $con->prepare('INSERT INTO surveys (course_id, name, start_date, end_date, rubric_id, survey_type_id) VALUES (?, ?, ?, ?, ?, ?)');
  $stmt->bind_param('isssii', $course_id, $name, $start_string, $end_string, $rubric_id, $survey_type);
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

function extendSurvey($con, $survey_id, $end) {
  $stmt = $con->prepare('UPDATE surveys SET end_date = ? WHERE id = ?');
  $stmt->bind_param('si', $end, $survey_id);
  $retVal = $stmt->execute();
  $stmt->close();
  return $retVal;
}

function updateSurveyPairing($con, $survey_id, $survey_type) {
  $stmt = $con->prepare('UPDATE surveys SET survey_type_id = ? WHERE id = ?');
  $stmt->bind_param('ii', $survey_type, $survey_id);
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

function getSurveyCourse($con, $survey_id){

  # no courses where course-id is 0
  $retVal = 0;
  $stmt = $con->prepare('SELECT surveys.course_id
                        FROM surveys
                        WHERE id=?');
  $stmt->bind_param('i', $survey_id);
  $stmt->execute();
  $result = $stmt->get_result();

  if (!($result->num_rows < 1)){

    $data = $result->fetch_assoc();
    $course_id = $data['course_id'];
    $retVal = $course_id;
  }
  $stmt->close();

  return $retVal;
  
}

function getSurveyData($con, $survey_id) {
  // Pessimistically assume that this fails
  $retVal = null;
  $stmt = $con->prepare('SELECT course_id, start_date, end_date, name, rubric_id, survey_type_id 
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

function getReviewerData($con, $survey_id) {
  return getSurveyParticipantData($con, $survey_id, 'reviewer_id');
}

function getReviewedData($con, $survey_id) {
  return getSurveyParticipantData($con, $survey_id, 'reviewed_id');
}

function getSurveyParticipantData($con, $survey_id, $retrieved_field) {
  $ret_val = array();
  $stmt = $con->prepare('SELECT DISTINCT students.id, email, name
                         FROM reviews 
                         INNER JOIN students ON reviews.' . $retrieved_field . '=students.id 
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

function getReviewerPerTeamResults($con, $survey_id) {
  $ret_val = array();

  $stmt = $con->prepare('SELECT reviews.reviewer_id, reviews.team_id, COUNT(DISTINCT reviews.id), COUNT(reviews.id), COUNT(evals.id), SUM(score)
                         FROM reviews
                         LEFT JOIN evals ON evals.review_id=reviews.id
                         LEFT JOIN scores ON evals.id=scores.eval_id
                         LEFT JOIN rubric_scores ON rubric_scores.id=scores.rubric_score_id
                         LEFT JOIN rubric_topics ON rubric_topics.id=scores.topic_id
                         WHERE survey_id=? AND (question_response is null OR question_response <> "'.FREEFORM_QUESTION_TYPE.'")
                         GROUP BY reviewer_id, reviews.team_id');
  $stmt->bind_param('i', $survey_id);
  $stmt->execute();
  $result = $stmt->get_result();
  while ($row = $result->fetch_array(MYSQLI_NUM)) {
    $reviewer_id = $row[0];
    $team_id = $row[1];
    $reviews = $row[2];
    $completed = ($row[3] == $row[4]);
    $sum_weighted_score = $row[5];
    $team_result = array("completion" => $completed, "total_score" => $sum_weighted_score, "total_people" => $reviews);
    if (!array_key_exists($reviewer_id, $ret_val)) {
      $ret_val[$reviewer_id] = array($team_id => $team_result);
    } else {
      // Add the current team's resultions to our results
      $ret_val[$reviewer_id][$team_id] = $team_result;
    }
  }
  $stmt->close();
  return $ret_val;
}

function getSurveyScores($con, $survey_id, $teammates) {
  $ret_val = array();
  $stmt = $con->prepare('SELECT reviewer_id, team_id, eval_weight, topic_id, score
                         FROM reviews
                         INNER JOIN evals on evals.review_id=reviews.id 
                         INNER JOIN scores ON evals.id=scores.eval_id
                         INNER JOIN rubric_scores ON rubric_scores.id=scores.rubric_score_id
                         WHERE survey_id=? AND reviews.reviewed_id=?');
  foreach (array_keys($teammates) as $student_id) {
    // Create the space for this teammate -- ASSUMES TEAMMATES ARE UNIQUE      
    $ret_val[$student_id] = array();
    $stmt->bind_param('ii',$survey_id, $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_array(MYSQLI_NUM)) {
      $reviewer_id = $row[0];
      $team_id = $row[1];
      $weight = $row[2];
      $topic_id = $row[3];
      $score = $row[4];
      // Keeps track of each student's reviews organized by the reviewer and topic
      if (!array_key_exists($reviewer_id, $ret_val[$student_id])) {
        $ret_val[$student_id][$reviewer_id] = array("team" => $team_id, "weight" => $weight, $topic_id => $score);
      } else {
        $ret_val[$student_id][$reviewer_id][$topic_id] = $score;
      }
    }
  }
  $stmt->close();
  return $ret_val;
}
?>