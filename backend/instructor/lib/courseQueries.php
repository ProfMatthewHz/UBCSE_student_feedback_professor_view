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


function getSurveysForCourses($con, &$terms) {
  $today = new DateTime();

  // Now get data on all of the surveys in each of those courses
  $stmt = $con->prepare('SELECT name, start_date, end_date, rubric_id, surveys.id, COUNT(reviews.id) AS total, COUNT(evals.id) AS completed
                         FROM surveys
                         LEFT JOIN reviews ON reviews.survey_id=surveys.id
                         LEFT JOIN evals ON evals.review_id=reviews.id
                         WHERE course_id=?
                         GROUP BY name, start_date, end_date, rubric_id
                         ORDER BY start_date DESC, end_date DESC');
  foreach ($terms as $name => &$term_courses) {
    foreach($term_courses as $id => &$course) {
      // Get the course's surveys in reverse chronological order
      $stmt->bind_param('i', $id);
      $stmt->execute(); 
      $result = $stmt->get_result();
      while ($row = $result->fetch_assoc()) {
        $survey_info = array();
        $survey_info['course_id'] = $id;
        $survey_info['name'] = $row['name'];
        $survey_info['start_date'] = $row['start_date'];
        $survey_info['end_date'] = $row['end_date'];
        $survey_info['rubric_id'] = $row['rubric_id'];
        $survey_info['id'] = $row['id'];
        // Generate and store that progress as text
        $percentage = 0;
        if ($row['total'] != 0) {
          $percentage = floor(($row['completed'] / $row['total']) * 100);
        }
        $survey_info['completion'] = $percentage . '% completed';

        // determine status of survey. then adjust dates to more friendly format
        $s = new DateTime($survey_info['start_date']);
        $e = new DateTime($survey_info['end_date']);
        $survey_info['sort_start_date'] = $survey_info['start_date'];
        $survey_info['sort_expiration_date'] = $survey_info['end_date'];
        $survey_info['start_date'] = $s->format('M j').' at '. $s->format('g:i A');
        $survey_info['end_date'] = $e->format('M j').' at '. $e->format('g:i A');

        if ($today < $s) {
          $course['upcoming'][] = $survey_info;
        } else if ($today < $e) {
          $course['active'][] = $survey_info;
        } else {
          $course['expired'][] = $survey_info;
        }
      }
    }
    unset($course);
  }
  $stmt->close();
  return $terms;
}

function getAllCoursesForInstructor($con, $instructor_id) {
  $stmt = $con->prepare('SELECT id, code, name, semester, year 
                         FROM courses
                         INNER JOIN course_instructors ON courses.id=course_instructors.course_id
                         WHERE instructor_id=? 
                         ORDER BY year DESC, semester DESC, code DESC');
  $stmt->bind_param('i', $instructor_id);
  $stmt->execute();
  $result = $stmt->get_result();
  $course_info = $result->fetch_all(MYSQLI_ASSOC);
  $stmt->close();
  return $course_info;
}

function getSingleCourseInfo($con, $course_id, $instructor_id) {
  // Pessmisticaly assume that the course fails
  $retVal = null;
  $stmt = $con->prepare('SELECT code, name, semester, year 
                         FROM courses
                         INNER JOIN course_instructors ON courses.id=course_instructors.course_id
                         WHERE id=? AND instructor_id=?');
  $stmt->bind_param('ii', $course_id, $instructor_id);
  $stmt->execute();
  $result = $stmt->get_result();
  $course_info = $result->fetch_all(MYSQLI_ASSOC);
  if ($result->num_rows > 0) {
    $retVal = $course_info[0];
  }
  $stmt->close();
  return $retVal;
}

//Korey wrote this 
function getInstructorTermCourses($con, $instructor_id, $semester, $year){

  $retVal = null;

  $stmt = $con->prepare('SELECT id, code, name, semester, year 
                         FROM courses
                         INNER JOIN course_instructors ON courses.id=course_instructors.course_id
                         WHERE instructor_id=? AND semester=? AND year=?
                         ORDER BY year DESC, semester DESC, code DESC');
  $stmt->bind_param('iii', $instructor_id, $semester, $year);
  $stmt->execute();
  $result = $stmt->get_result();
  if ($result->num_rows > 0){
    $courses_info = $result->fetch_all(MYSQLI_ASSOC);
    $retVal = $courses_info;
  }
  $stmt->close();

  if (is_null($retVal)){
    $semesterName = SEMESTER_MAP_REVERSE[$semester];
    $noCoursesMessage = sprintf("You (instructor_id = %u) do not currently have courses for <b> %s %u! </b>", $instructor_id, $semesterName, $year);
    echo $noCoursesMessage;
  }
  return $retVal;

} 
// korey wrote this 
function getSurveysFromSingleCourse($con, $course_id){

  $retVal = array();

  // Set expected key-value pairs (survey availability)  and error if there is one.
  $retVal["error"] = "";
  $retVal["upcoming"] = array();
  $retVal["active"] = array();
  $retVal["expired"] = array();
  

  $stmt = $con->prepare('SELECT name, start_date, end_date, rubric_id, surveys.id, COUNT(reviews.id) AS total, COUNT(evals.id) AS completed
                         FROM surveys
                         LEFT JOIN reviews ON reviews.survey_id=surveys.id
                         LEFT JOIN evals ON evals.review_id=reviews.id
                         WHERE course_id=?
                         GROUP BY name, start_date, end_date, rubric_id
                         ORDER BY start_date DESC, end_date DESC');

  $stmt->bind_param('i', $course_id);
  $stmt->execute();

  $result = $stmt->get_result();
  
  if ($result->num_rows > 0){
    $surveys = $result->fetch_all(MYSQLI_ASSOC);

    $today = new DateTime();

    foreach ($surveys as $s){

      $survey_info = array();
      $survey_info['course_id'] = $course_id;
      $survey_info['name'] = $s['name'];
      $survey_info['start_date'] = $s['start_date'];
      $survey_info['end_date'] = $s['end_date'];
      $survey_info['rubric_id'] = $s['rubric_id'];
      $survey_info['id'] = $s['id'];
      // Generate and store that progress as text
      $percentage = 0;
      if ($s['total'] != 0) {
        $percentage = floor(($s['completed'] / $s['total']) * 100);
      }
      $survey_info['completion'] = $percentage . '% completed';

      // determine status of survey. then adjust dates to more friendly format
      $s = new DateTime($survey_info['start_date']);
      $e = new DateTime($survey_info['end_date']);

      $survey_info['sort_start_date'] = $survey_info['start_date'];
      $survey_info['sort_expiration_date'] = $survey_info['end_date'];
      $survey_info['start_date'] = $s->format('M j').' at '. $s->format('g:i A');
      $survey_info['end_date'] = $e->format('M j').' at '. $e->format('g:i A');

      if ($today < $s) {
        $retVal['upcoming'][] = $survey_info;
      } else if ($today < $e) {
        $retVal['active'][] = $survey_info;
      } else {
        $retVal['expired'][] = $survey_info;
      }

    }
    unset($s);
    
  } else {
    $retVal['error'] = "There is no survey data for course [" . $course_id . "]";
  }
  $stmt->close();
  
  return $retVal;
}



function getInstructorTerms($con, $instructor_id, $currentSemester, $currentYear) {
  // Semester mapping
  $semesterNames = [
    1 => 'winter',
    2 => 'spring',
    3 => 'summer',
    4 => 'fall',
  ];
  
  $stmt = $con->prepare('SELECT DISTINCT semester, year
                         FROM courses
                         INNER JOIN course_instructors ON courses.id = course_instructors.course_id
                         WHERE course_instructors.instructor_id = ?
                         AND (year < ? OR (year = ? AND semester < ?))');

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
    $term['semester'] = $semesterNames[$term['semester']];
  }

  return $terms;
}


function instructorData($con, $instructor_id,$currentSemester,$currentYear,$course_id){
  //function getInstructorTerms($con, $instructor_id, $currentSemester, $currentYear)
  //function getInstructorTermCourses($con, $instructor_id, $semester, $year)
  //function getSurveysFromSingleCourse($con, $course_id)

  $outPutAray = [
    'previous Instructor Terms' => getInstructorTerms($con, $instructor_id, $currentSemester, $currentYear) ,
    'instructor current Term Courses' => getInstructorTermCourses($con, $instructor_id, $currentSemester, $currentYear),
    'Instructor surveys from single Course' => getSurveysFromSingleCourse($con, $course_id)
  ];
 

  return $outPutAray;
  
}



?>