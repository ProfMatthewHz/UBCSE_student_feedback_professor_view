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

function getCSRFToken($con, $id) {
  // Pessimistically assume that this fails
  $retVal = 0;
  $stmt = $con->prepare('SELECT csrf_token 
                         FROM instructors
                         WHERE id=?');
  $stmt->bind_param('i', $id);
  $stmt->execute();
  $result = $stmt->get_result();
  $data = $result->fetch_all(MYSQLI_ASSOC);
  if ($result->num_rows > 0) {
    $retVal = $data[0]['csrf_token'];
  }
  $stmt->close();
  return $retVal;
}

function createCSRFToken($con, $instructor_id) {
  // Create the new random CSRF token
  $csrf_token = bin2hex(random_bytes(32));
  // store the new tokens and expiration dates in the database, NULL out the initial authorization id
  $stmt = $con->prepare('UPDATE instructors SET csrf_token=? WHERE id=?');
  $stmt->bind_param('si', $csrf_token, $instructor_id);
  if ($stmt->execute()) {
    return $csrf_token;
  } else {
    return null;
  }
}

?>