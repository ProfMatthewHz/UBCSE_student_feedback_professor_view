<?php
function getInstructorId() {
  // Ensure the session is started since that is how we currently track logged-in users
  session_start();
  
  // Verify that the user is logged in
  if (!isset($_SESSION['id'])) {
    http_response_code(511);
    echo json_encode(array("Error" => "You must be logged in to access this page."));
    exit();
  }
  $ret_val = $_SESSION['id'];
  return $ret_val;
}
?>