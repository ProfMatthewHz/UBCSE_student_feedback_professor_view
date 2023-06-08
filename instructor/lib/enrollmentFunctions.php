<?php
function clearRoster($con, $course_id) {
  // delete all enrollments for this course
  $stmt = $con->prepare('DELETE FROM enrollments WHERE course_id=?');
  $stmt->bind_param('i', $course_id);
  $retVal = $stmt->execute();
  $stmt->close();
  return $retVal;
}

function addToCourse($con, $course_id, $names_emails) {
  // Optimistically assume this will be successful.
  $retVal = true;

  // Create the prepared statements
  $stmt_check = $con->prepare('SELECT student_id FROM students WHERE email=?');
  $stmt_news = $con->prepare('INSERT INTO students (email, name) VALUES (?, ?)');
  $stmt_enroll = $con->prepare('INSERT INTO enrollments (student_id, course_id) VALUES (?, ?)');  
  foreach ($names_emails as list($email, $name)) {
    $stmt_check->bind_param('s', $email);
    $stmt_check->execute();
    $result = $stmt_check->get_result();
    $student_info = $result->fetch_all(MYSQLI_ASSOC);
    $student_id = 0;
    // check if the student already exists if they don't insert them
    if ($result->num_rows == 0) {
      $stmt_news->bind_param('ss', $email, $name);
      $retVal = $retVal && $stmt_news->execute();
      $student_id = $con->insert_id;
    } else {
      $student_id = $student_info[0]['student_id'];
    }
    // An id of 0 is used by MySQL for the ID when the insert failed. This should always be non-zero
    if ($student_id != 0) {
      $stmt_enroll->bind_param('ii', $student_id, $course_id);
      $retVal = $retVal && $stmt_enroll->execute();
    }
  }
  // Clean up our SQL queries
  $stmt_check->close();
  $stmt_news->close();
  $stmt_enroll->close();
  return $retVal;
}
?>