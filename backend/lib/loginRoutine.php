<?php
require_once "database.php";
require_once "constants.php";
require_once "instructor/lib/instructorQueries.php";
require_once "studentQueries.php";

function setSessionVariables($ubit) {
  // Only do this when the connection is new
  if (empty($_SESSION['redirect'])) {
    $con = connectToDatabase();
    
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

function getStudentId() {
  // Ensure the session is started since that is how we currently track logged-in users
  session_start();
  
  if (!isset($_SESSION['student_id'])) {
    http_response_code(511);
    echo json_encode(array("Error" => "You must be logged in to access this page."));
    exit();
  }
  $ret_val = $_SESSION['student_id'];
  return $ret_val;
}
?>