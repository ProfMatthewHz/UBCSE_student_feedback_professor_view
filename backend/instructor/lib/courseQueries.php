<?php
function addCourse($con, $course_code, $course_name, $semester, $course_year) {
  $stmt = $con->prepare('INSERT INTO courses (code, name, semester, year) VALUES (?, ?, ?, ?)');
  $stmt->bind_param('ssii', $course_code, $course_name, $semester, $course_year);
  $stmt->execute();

  // Return the inserted course's id
  $retVal = $con->insert_id;
  $stmt->close();
  return $retVal;
}

function addInstructor($con, $course_id, $instructor_id) {
  // Add the instructor to the course
  $stmt = $con->prepare('INSERT INTO course_instructors (course_id, instructor_id) VALUES (?, ?)');
  $stmt->bind_param('ii', $course_id, $instructor_id);
  $retVal = $stmt->execute();
  $stmt->close();
  // Return if the insert was successful
  return $retVal; 
}

function courseExists($con, $course_code, $course_name, $semester, $course_year, $instructor_id) {
  $stmt = $con->prepare('SELECT id 
                         FROM courses
                         INNER JOIN course_instructors ON courses.id = course_instructors.course_id
                         WHERE code=? AND name=? AND semester=? AND year=? AND course_instructors.instructor_id=?');
  $stmt->bind_param('ssiii', $course_code, $course_name, $semester, $course_year, $instructor_id);
  $stmt->execute();
  $result = $stmt->get_result();
  $data = $result->fetch_all(MYSQLI_ASSOC);
  $retVal = $result->num_rows > 0;
  $stmt->close();
  return $retVal;
}

function isCourseInstructor($con, $course_id, $instructor_id) {
  // Make sure the survey is for a course the current instructor actually teaches
  $stmt = $con->prepare('SELECT id 
                         FROM courses 
                         INNER JOIN course_instructors ON courses.id=course_instructors.course_id
                         WHERE id=? AND instructor_id=?');
  $stmt->bind_param('ii', $course_id, $instructor_id);
  $stmt->execute();
  $result = $stmt->get_result();
  $data = $result->fetch_all(MYSQLI_ASSOC);
  $retVal = $result->num_rows > 0;
  $stmt->close();
  return $retVal;
}

function getInstructorTermCourses($con, $instructor_id, $semester, $year){
  $retVal = array();

  $stmt = $con->prepare('SELECT id, code, name, semester, year 
                         FROM courses
                         INNER JOIN course_instructors ON courses.id=course_instructors.course_id
                         WHERE instructor_id=? AND semester=? AND year=?
                         ORDER BY code');
  $stmt->bind_param('iii', $instructor_id, $semester, $year);
  $stmt->execute();
  $result = $stmt->get_result();
  if ($result->num_rows > 0){
    $courses_info = $result->fetch_all(MYSQLI_ASSOC);
    $retVal = $courses_info;
  }
  $stmt->close();
  return $retVal;
} 

function getSurveysFromSingleCourse($con, $course_id){
  $retVal = array();
  // Set expected key-value pairs (survey availability)  and error if there is one.
  $retVal["error"] = "";
  $retVal["upcoming"] = array();
  $retVal["active"] = array(); 
  $retVal["expired"] = array();
  
  $stmt = $con->prepare('SELECT name, start_date, end_date, rubric_id, surveys.id, surveys.survey_type_id, surveys.pm_weight, COUNT(eval_id) AS total, COUNT(evals.id) AS completed
                         FROM surveys
                         LEFT JOIN reviews ON reviews.survey_id=surveys.id
                         LEFT JOIN evals ON evals.id=reviews.eval_id
                         WHERE course_id=?
                         GROUP BY name, start_date, end_date, rubric_id, surveys.id
                         ORDER BY start_date DESC, end_date DESC');
  $stmt->bind_param('i', $course_id);
  $stmt->execute();

  $result = $stmt->get_result();
  
  if ($result->num_rows > 0){
    $surveys = $result->fetch_all(MYSQLI_ASSOC);

    $today = new DateTime();

    foreach ($surveys as $s) {
      $survey_info = array();
      $survey_info['course_id'] = $course_id;
      $survey_info['name'] = $s['name'];
      $survey_info['start_date'] = $s['start_date'];
      $survey_info['end_date'] = $s['end_date'];
      $survey_info['rubric_id'] = $s['rubric_id'];
      $survey_info['id'] = $s['id'];
      $survey_info['survey_type'] = $s['survey_type_id'];
      $survey_info['pm_weight'] = $s['pm_weight'];

      // Generate and store that progress as text
      $percentage = 0;
      if ($s['total'] != 0) {
        $percentage = round(($s['completed'] / $s['total']) * 100);
      }
      $survey_info['completion'] = $percentage . '% completed';

      // determine status of survey. then adjust dates to more friendly format
      $start = new DateTime($survey_info['start_date']);
      $end = new DateTime($survey_info['end_date']);

      $survey_info['sort_start_date'] = $survey_info['start_date'];
      $survey_info['sort_expiration_date'] = $survey_info['end_date'];
      $survey_info['start_date'] = $start->format('M j').' at '. $start->format('g:i A');
      $survey_info['end_date'] = $end->format('M j').' at '. $end->format('g:i A');

      if ($today < $start) {
        $retVal['upcoming'][] = $survey_info;
      } else if ($today < $end) {
        $retVal['active'][] = $survey_info;
      } else {
        $retVal['expired'][] = $survey_info;
      }
    }
  } else {
    $retVal['error'] = "There is no survey data for course [" . $course_id . "]";
  }
  $stmt->close();
  
  return $retVal;
}

function getInstructorHistoricalTerms($con, $instructor_id, $month_map, $semester_map) {
  // Get the current semester and year
  $currentMonth = idate('m');
  $currentSemester = $month_map[$currentMonth];
  $currentYear = idate('Y');
  $stmt = $con->prepare('SELECT DISTINCT semester, year
                         FROM courses
                         INNER JOIN course_instructors ON courses.id = course_instructors.course_id
                         WHERE course_instructors.instructor_id = ?
                         AND (year < ? OR (year = ? AND semester < ?))
                         ORDER BY year, semester');
  $stmt->bind_param('iiii', $instructor_id, $currentYear, $currentYear, $currentSemester);
  $stmt->execute();
  $result = $stmt->get_result();
  $terms = $result->fetch_all(MYSQLI_ASSOC);
  $stmt->close();

  if (empty($terms)) {
    return "No terms found for the instructor.";
  } 

  // Map numeric semesters to string values
  foreach ($terms as &$term) {
    $term['semester'] = $semester_map[$term['semester']];
  }

  return $terms;
}
?>