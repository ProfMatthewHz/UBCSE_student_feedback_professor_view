<?php
function getInstructorId($con, $email) {
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

function updateInstructorInfo($con, $hash, $expiration, $csrf_token, $instructor_id) {
  // store the new tokens and expiration dates in the database, NULL out the initial authorization id
  $stmt = $con->prepare('UPDATE instructors SET session_token=?, session_expiration=?, csrf_token=? WHERE id=?');
  $stmt->bind_param('sisi', $hash, $expiration, $csrf_token, $instructor_id);
  $retVal = $stmt->execute();
  return $retVal;
}

?>