
<?php
function getSurveyResultsInfo($db_connection, $survey_id, $student_id) {
    $query_str = 'surveys.end_date <= NOW()';
    $student_id_field = 'reviewed_id';
    return handleSurveyQuery($db_connection, $survey_id, $student_id, $student_id_field, $query_str);
}

function getCompletedSurveyInfo($db_connection, $survey_id, $student_id) {
    $query_str = 'surveys.end_date <= NOW()';
    $student_id_field = 'reviewer_id';
    return handleSurveyQuery($db_connection, $survey_id, $student_id, $student_id_field, $query_str);
}

function getActiveSurveyInfo($db_connection, $survey_id, $student_id) {
    $query_str = 'surveys.start_date <= NOW() AND surveys.end_date > NOW()';
    $student_id_field = 'reviewer_id';
    return handleSurveyQuery($db_connection, $survey_id, $student_id, $student_id_field, $query_str);
}

function handleSurveyQuery($db_connection, $survey_id, $student_id, $student_id_field, $addl_query) {
    // Pessimistically assume this fails
    $ret_val = null;
    $query = 'SELECT DISTINCT courses.name course_name, surveys.name survey_name, survey_type_id survey_type 
              FROM surveys
              INNER JOIN reviews ON reviews.survey_id = surveys.id 
              INNER JOIN courses on courses.id = surveys.course_id 
              WHERE surveys.id=? AND reviews.'.$student_id_field.'=? AND '.$addl_query;
    $stmt = $db_connection->prepare($query);
    $stmt->bind_param('ii', $survey_id, $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_row()) {
        $ret_val = array("survey_id" => $survey_id, "course_name" => $row[0], "survey_name" => $row[1], "survey_type" => $row[2]);
    }
    $stmt->close();
    return $ret_val;
}


function getSurveyMultipleChoiceTopics($db_connection, $survey_id) {
    $ret_val = array();
    $query_str = 'SELECT rubric_topics.id, question
                  FROM surveys 
                  INNER JOIN rubric_topics ON surveys.rubric_id = rubric_topics.rubric_id
                  WHERE surveys.id = ? AND rubric_topics.question_response = "'.MC_QUESTION_TYPE.'"
                  ORDER BY rubric_topics.id';
    $stmt_topics = $db_connection->prepare($query_str);
    $stmt_topics->bind_param('i', $survey_id);
    $stmt_topics->execute();
    $result = $stmt_topics->get_result();
    while ($row = $result->fetch_array(MYSQLI_NUM)) {
        $ret_val[$row[0]] = strtoupper($row[1]);
    }
    $stmt_topics->close();
    return $ret_val;
}

function getSurveyFreeformTopics($db_connection, $survey_id) {
    $ret_val = array();
    $query_str = 'SELECT rubric_topics.id, question
                  FROM surveys 
                  INNER JOIN rubric_topics ON surveys.rubric_id = rubric_topics.rubric_id
                  WHERE surveys.id = ? AND rubric_topics.question_response = "'.FREEFORM_QUESTION_TYPE.'"
                  ORDER BY rubric_topics.id';
    $stmt_topics = $db_connection->prepare($query_str);
    $stmt_topics->bind_param('i', $survey_id);
    $stmt_topics->execute();
    $result = $stmt_topics->get_result();
    while ($row = $result->fetch_array(MYSQLI_NUM)) {
        $ret_val[$row[0]] = strtoupper($row[1]);
    }
    $stmt_topics->close();
    return $ret_val;
}

function getSurveyMultipleChoiceResponses($db_connection, $topic_id, $include_score) {
    $ret_val = array();
    $query_str = 'SELECT rubric_responses.rubric_score_id, response, score
                  FROM rubric_responses
                  INNER JOIN rubric_scores ON rubric_scores.id = rubric_responses.rubric_score_id
                  WHERE topic_id = ? 
                  ORDER BY rubric_responses.rubric_score_id';
    $stmt_responses = $db_connection->prepare($query_str);
    $stmt_responses->bind_param('i', $topic_id);
    $stmt_responses->execute();
    $result = $stmt_responses->get_result();
    while ($row = $result->fetch_array(MYSQLI_NUM)) {
        if ($include_score) {
            $ret_val[$row[0]] = array($row[1], $row[2]);
        } else {
            $ret_val[$row[0]] = $row[1];
        }
    }
    $stmt_responses->close();
    return $ret_val;
}

function createActiveQueryReviewer($con, $date_clause) {
    $sql = 'SELECT courses.name, surveys.name, surveys.id, surveys.start_date, surveys.end_date, COUNT(reviews.id) AS review_count, COUNT(evals.id) AS eval_count
            FROM surveys
            INNER JOIN courses ON courses.id = surveys.course_id 
            INNER JOIN reviews ON reviews.survey_id = surveys.id
            LEFT JOIN evals ON evals.id = reviews.eval_id AND evals.completed = 1
            WHERE reviews.reviewer_id=?';
    if (!empty($date_clause)) {
        $sql .= ' AND ' . $date_clause;
    }
    $sql .= ' GROUP BY courses.name, surveys.name, surveys.id, surveys.start_date, surveys.end_date';
    $stmt = $con->prepare($sql);
    return $stmt;
}

function createClosedQueryReviewer($con, $date_clause) {
    $sql = 'SELECT courses.name, surveys.name, surveys.id, surveys.start_date, surveys.end_date, COUNT(reviews.id) AS review_count, COUNT(evals.id) AS eval_count
            FROM surveys
            INNER JOIN courses ON courses.id = surveys.course_id 
            INNER JOIN reviews ON reviews.survey_id = surveys.id
            LEFT JOIN evals ON evals.id = reviews.eval_id AND evals.completed = 1
            WHERE reviews.reviewer_id=? AND courses.semester=? AND courses.year=?';
    if (!empty($date_clause)) {
        $sql .= ' AND ' . $date_clause;
    }
    $sql .= ' GROUP BY courses.name, surveys.name, surveys.id, surveys.start_date, surveys.end_date';
    $stmt = $con->prepare($sql);
    return $stmt;
}

function createQueryReviewed($con, $date_clause) {
    $sql = 'SELECT courses.name, surveys.name, surveys.id, surveys.start_date, surveys.end_date, COUNT(evals.id) AS eval_count
            FROM surveys
            INNER JOIN courses ON courses.id = surveys.course_id 
            INNER JOIN reviews ON reviews.survey_id = surveys.id
            LEFT JOIN evals ON evals.id = reviews.eval_id AND evals.completed = 1
            WHERE reviews.reviewed_id=? AND reviews.reviewer_id<>? AND courses.semester=? AND courses.year=?';
    if (!empty($date_clause)) {
        $sql .= ' AND ' . $date_clause;
    }
    $sql .= ' GROUP BY courses.name, surveys.name, surveys.id, surveys.start_date, surveys.end_date';
    $stmt = $con->prepare($sql);
    return $stmt;
}

function reverseChronologicalComparator($a, $b) {
    return -1 * chronologicalComparator($a, $b);
}

function chronologicalComparator($a, $b) {
    $a_datetime = $a[1];
    $b_datetime = $b[1];
    if ($a_datetime < $b_datetime) {
        return 1;
    } else if ($a_datetime > $b_datetime) {
        return -1;
    } else {
        return 0;
    }
}

function getClosedSurveysForTerm($con, $term, $year, $id) {
    $retVal = array();
    $stmt = createClosedQueryReviewer($con, 'surveys.end_date < NOW()');
    $stmt->bind_param('iii', $id, $term, $year);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_array(MYSQLI_NUM)) {
        $e = new DateTime($row[4]);
        $s = new DateTime($row[3]);
        $fully_submitted = ($row[5] == $row[6]);
        $retVal[$row[2]] = array($row[0], $row[1], $e, true, $fully_submitted, false, false, $s);
    }
    $stmt->close();

    $stmt = createQueryReviewed($con, 'surveys.end_date < NOW()');
    $stmt->bind_param('iiii', $id, $id, $term, $year);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_array(MYSQLI_NUM)) {
        if (array_key_exists($row[2], $retVal)) {
            $survey = $retVal[$row[2]];
            // Update that they could be reviewed on this survey
            $survey[5] = true;
            // Update that they were reviewed on this survey
            $survey[6] = ($row[5] > 0);
            $retVal[$row[2]] = $survey;
        } else {
            $e = new DateTime($row[4]);
            $s = new DateTime($row[3]);
            $evaluated = ($row[5] > 0);
            $retVal[$row[2]] = array($row[0], $row[1], $e, false, false, true, $evaluated, $s);
        }
    }
    $stmt->close();
    // Sort the array in reverse chronological order
    uasort($retVal, 'reverseChronologicalComparator');
    return $retVal;
}

function getCurrentSurveys($con, $id) {
    $retVal = array();
    $stmt = createActiveQueryReviewer($con, 'surveys.start_date <= NOW() AND surveys.end_date > NOW()');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_array(MYSQLI_NUM)) {
        $e = new DateTime($row[4]);
        $s = new DateTime($row[3]);
        $fully_submitted = ($row[5] == $row[6]);
        $retVal[$row[2]] = array($row[0], $row[1], $e, true, $fully_submitted, false, false, $s);
    }
    $stmt->close();
    // Sort the array to be in chronological order
    uasort($retVal, 'chronologicalComparator');
    return $retVal;
}

function getUpcomingSurveys($con, $id) {
    $retVal = array();
    $stmt = createActiveQueryReviewer($con, 'surveys.start_date > NOW()');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_array(MYSQLI_NUM)) {
        $s = new DateTime($row[3]);
        $e = new DateTime($row[4]);
        $retVal[$row[2]] = array($row[0], $row[1], $s, true, false, false, false, $e);
    }
    $stmt->close();
    // Sort the array to be in chronological order
    uasort($retVal, 'chronologicalComparator');
    return $retVal;
}

function wasReviewedInSurvey($con, $survey_id, $student_id) {
    $count = 0;
    $stmt = $con->prepare('SELECT COUNT(*) FROM reviews WHERE survey_id=? AND reviewed_id=?');
    $stmt->bind_param('ii', $survey_id, $student_id);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    return $count > 0;
}

function getCompletionRate($con, $survey_id, $student_id) {
    // Calculate the completion rate for a given student in a survey
    $sql = "SELECT COUNT(reviews.id) reviews, COUNT(evals.id) completed
            FROM reviews
            LEFT JOIN evals ON reviews.eval_id = evals.id AND evals.completed = 1
            WHERE survey_id = ? AND reviewer_id = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("ii", $survey_id, $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
        $denominator = $row['reviews'];
        $numerator = $row['completed'];
    }
    $stmt->close();
    $compRate = $numerator/$denominator;
    return $compRate;
}
?>