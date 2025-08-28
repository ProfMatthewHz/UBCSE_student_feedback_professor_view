<?php
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