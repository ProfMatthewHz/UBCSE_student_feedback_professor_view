<?php
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