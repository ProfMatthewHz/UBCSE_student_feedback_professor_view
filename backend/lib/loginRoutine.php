<?php
function getStudentInfoFromEmail($db_connection, $email) {
  $ret_val = null;
  $stmt = $db_connection->prepare('SELECT id, name FROM students WHERE email=?');
  $stmt->bind_param('s',$email);
  $stmt->execute();
  $result = $stmt->get_result();
  $row = $result->fetch_array(MYSQLI_NUM);
  $ret_val = $row;
  $stmt->close();
  return $ret_val;
}

function getInstructorIdForEmail($con, $email) {
  // Pessimistically assume that this fails
  $retVal = 0;
  $stmt = $con->prepare('SELECT id 
                         FROM instructors
                         WHERE email=?');
  $stmt->bind_param('s', $email);
  $stmt->execute();
  $result = $stmt->get_result();
  $data = $result->fetch_all(MYSQLI_ASSOC);
  if ($result->num_rows > 0) {
    $retVal = $data[0]['id'];
  }
  $stmt->close();
  return $retVal;
}

function setSessionVariables($con, $ubit) {
  // Only do this when the connection is new
  if (empty($_SESSION['redirect'])) {
    $email = $ubit . "@buffalo.edu";
    $id = getInstructorIdForEmail($con, $email);
    $id_and_name = getStudentInfoFromEmail($con, $email);

    // Logic for when it is NOT an instructor BUT a student
    if (!empty($id_and_name)) {
          $_SESSION['student_id'] = $id_and_name[0];
          $_SESSION['ubit'] = $ubit;
          $_SESSION['redirect'] = 2;
    }
      
    if (!empty($id)) {
        $_SESSION['id'] = $id;
        $_SESSION['redirect'] = 1;
    }
  }
}
?>