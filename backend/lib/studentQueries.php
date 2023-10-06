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

function forceIdsFromEmail($db_connection, $students) {
  $ret_val = array();
  $stmt_check = $db_connection->prepare('SELECT id FROM students WHERE email=?');
  $stmt_add = $db_connection->prepare('INSERT INTO students (email, name) VALUES (?, ?)');
  foreach ($students as $email => $name) {
    $stmt_check->bind_param('s',$email);
    $stmt_check->execute();
    $result = $stmt_check->get_result();
    $ids = $result->fetch_all(MYSQLI_NUM);
    if ($result->num_rows > 0) {
      $student_id = $ids[0][0];
    } else {
      $stmt_add->bind_param('ss', $email, $name);
      $stmt_add->execute();
      $student_id = $db_connection->insert_id;
    }
    $ret_val[$email] = $student_id;
  }
  $stmt_check->close();
  $stmt_add->close();
  return $ret_val;
}
?>