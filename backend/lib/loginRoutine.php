<?php
require_once "lib/database.php";
require_once "lib/constants.php";
require_once "instructor/lib/instructorQueries.php";
require_once "lib/studentQueries.php";

function setSessionVariables($ubit) {
  // Only do this when the connection is new
  if (empty($_SESSION['redirect'])) {
    $con = connectToDatabase();
    
    $email = $ubit . "@buffalo.edu";
    $id = getInstructorId($con, $email);
    $id_and_name = getStudentInfoFromEmail($con, $email);

    // Logic for when it is NOT an instructor BUT a student
    if (!empty($id_and_name)) {
          $_SESSION['student_id'] = $id_and_name[0];
          $_SESSION['ubit'] = $_POST['UBIT'];
          $_SESSION['redirect'] = 2;
    }
      
    if (!empty($id)) {
        $_SESSION['id'] = $id;
        $_SESSION['redirect'] = 1;
    }
  }
}
?>