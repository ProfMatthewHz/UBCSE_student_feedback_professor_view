<?php
function breakoutRosters($old_roster, $new_roster) {
  // Setup the return value
  $ret_val = array("new" => array(), "continuing" => array(), "remaining" => array());
  // Start by looping through the set of new roster to identify who are new and who are continuing students
  foreach ($new_roster as $email=>$name) {
    if (array_key_exists($email, $old_roster)) {
      $ret_val["continuing"][$email] = $name;
    } else {
      $ret_val["new"][$email] = $name;
    }
  }
  // Now loop through the roster to identify students who have been removed
  foreach ($old_roster as $email => $name_and_id) {
    if (!array_key_exists($email, $new_roster)) {
      $ret_val["remaining"][$email] = $name_and_id;
    }
  }
  // foreach ($ret_val as $key => $value) {
  //   echo $key . "\n";
  //   foreach ($value as $email => $name) {
  //     echo $key ." " . $email . "\n";
  //   }
  // }
  return $ret_val;
}

function getRoster($con, $course_id) {
  $ret_val = array();
  // delete all enrollments for this course
  $stmt = $con->prepare('SELECT email, name, student_id 
                         FROM enrollments
                         INNER JOIN students ON enrollments.student_id=students.id
                         WHERE course_id=?');
  $stmt->bind_param('i', $course_id);
  $stmt->execute();
  $result = $stmt->get_result();
  while ($row = $result->fetch_array(MYSQLI_NUM)) {
    $email = array_shift($row);
    $ret_val[$email] = $row;
  }
  $stmt->close();
  return $ret_val;
}

function removeFromRoster($con, $course_id, $students) {
  // Optimistically assume that we will be successful
  $retVal = true;
  // Prepare the statement we will be using to remove the students
  $stmt = $con->prepare('DELETE FROM enrollments WHERE course_id=? AND student_id=?');
  foreach ($students as $email => $student) {
    $stmt->bind_param('ii', $course_id, $student[1]);
    $retVal = $retVal && $stmt->execute();
  }
  $stmt->close();
  return $retVal;
}

function addStudents($con, $course_id, $names_emails) {
  // Optimistically assume this will be successful.
  $retVal = true;

  // Get the student ID for every student, adding students where it is needed
  $student_ids = forceIdsFromEmail($con, $names_emails);
  // Create the prepared statement
  $stmt = $con->prepare('INSERT INTO enrollments (student_id, course_id) VALUES (?, ?)');
  // And get the current roster to avoid adding a student a second time.
  $course_roster = getRoster($con, $course_id);
  foreach ($names_emails as $email => $name) {
    $student_id = $student_ids[$email];
    // An ID of 0 means the student was not added to the database
    if ($student_id != 0) {
      // Check if the student is already enrolled in the course
      if (!array_key_exists($email, $course_roster)) {
        $stmt->bind_param('ii', $student_id, $course_id);
        $retVal = $retVal && $stmt->execute();
      }
    } else {
      $retVal = false;
    }
  }
  // Clean up our SQL queries
  $stmt->close();
  return $retVal;
}

function getNonRosterStudents($full_list, $roster) {
  $ret_val = array();
  foreach ($full_list as $email => $data) {
    if (!array_key_exists($email, $roster)) {
      $ret_val[$email] = $data;
    }
  }
  return $ret_val;
}
?>