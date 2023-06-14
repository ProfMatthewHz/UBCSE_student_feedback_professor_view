<?php
function clearRoster($con, $course_id) {
  // delete all enrollments for this course
  $stmt = $con->prepare('DELETE FROM enrollments WHERE course_id=?');
  $stmt->bind_param('i', $course_id);
  $retVal = $stmt->execute();
  $stmt->close();
  return $retVal;
}

function addStudents($con, $course_id, $names_emails) {
  // Optimistically assume this will be successful.
  $retVal = true;

  // Create the prepared statements
  $stmt_student_check = $con->prepare('SELECT id FROM students WHERE email=?');
  $stmt_add_student = $con->prepare('INSERT INTO students (email, name) VALUES (?, ?)');
  $stmt_enroll_check = $con->prepare('SELECT student_id FROM enrollments WHERE student_id=? AND course_id=?');
  $stmt_add_enroll = $con->prepare('INSERT INTO enrollments (student_id, course_id) VALUES (?, ?)');  
  foreach ($names_emails as list($name, $email)) {
    $stmt_student_check->bind_param('s', $email);
    $stmt_student_check->execute();
    $result = $stmt_student_check->get_result();
    $student_info = $result->fetch_all(MYSQLI_ASSOC);
    $student_id = 0;
    // check if the student already exists if they don't insert them
    if ($result->num_rows == 0) {
      $stmt_add_student->bind_param('ss', $email, $name);
      $retVal = $retVal && $stmt_add_student->execute();
      $student_id = $con->insert_id;
    } else {
      $student_id = $student_info[0]['id'];
    }
    // An id of 0 is used by MySQL for the ID when the insert failed. This should always be non-zero
    if ($student_id != 0) {
      // Check if the student is already enrolled in the course
      $stmt_enroll_check->bind_param('ii', $student_id, $course_id);
      $retVal = $retVal && $stmt_enroll_check->execute();
      $result_enroll = $stmt_enroll_check->get_result();
      $result_enroll->fetch_all(MYSQLI_ASSOC);
      if ($result_enroll->num_rows == 0) {
        $stmt_add_enroll->bind_param('ii', $student_id, $course_id);
        $retVal = $retVal && $stmt_add_enroll->execute();
      }
    }
  }
  // Clean up our SQL queries
  $stmt_student_check->close();
  $stmt_add_student->close();
  $stmt_enroll_check->close();
  $stmt_add_enroll->close();
  return $retVal;
}
?>