<?php
function addCourse($con, $course_code, $course_name, $semester, $course_year) {
  $stmt = $con->prepare('INSERT INTO courses (code, name, semester, year) VALUES (?, ?, ?, ?, ?)');
  $stmt->bind_param('ssiii', $course_code, $course_name, $semester, $course_year);
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
                         WHERE code=? AND name=? AND semester=? AND year=? AND course_instructors.id=?');
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
                         INNER JOIN course_instructors ON courses.id=course_instructor.course_id
                         WHERE id=? AND instructor_id=?');
  $stmt->bind_param('ii', $course_id, $instructor_id);
  $stmt->execute();
  $result = $stmt->get_result();
  $data = $result->fetch_all(MYSQLI_ASSOC);
  $retVal = $result->num_rows > 0;
  $stmt->close();
  return $retVal;
}

function getSurveys($con, $instrutor_id) {

}

function getAllCoursesForInstructor($con, $instructor_id) {
  $stmt = $con->prepare('SELECT id, code, name, semester, year 
                         FROM courses
                         INNER JOIN course_instructors ON courses.id=course_instructor.course_id
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
  $stmt = $con->prepare('SELECT id, code, name, semester, year 
                         FROM courses
                         INNER JOIN course_instructors ON courses.id=course_instructor.course_id
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
?>