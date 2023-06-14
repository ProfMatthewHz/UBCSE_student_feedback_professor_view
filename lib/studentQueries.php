<?php
function getIdFromEmail($db_connection, $email) {
  $ret_val = 0;
  $stmt = $db_connection->prepare('SELECT id FROM students WHERE email=?');
  $stmt->bind_param('s',$email);
  $stmt->execute();
  $stmt->bind_result($ret_val);
  $stmt->store_result();
  if (!$stmt->fetch()) {
    $ret_val = 0;
  }
  $stmt->close();
  return $ret_val;
}

function forceIdsFromEmail($db_connection, $students) {
  $ret_val = array();
  $stmt_check = $db_connection->prepare('SELECT id FROM students WHERE email=?');
  $stmt_add = $db_connection->prepare('INSERT INTO students (email, name) VALUES (?, ?)');
  foreach ($students as $student) {
    $email = $student[1];
    $stmt_check->bind_param('s',$email);
    $stmt_check->execute();
    $result = $stmt_check->get_result();
    $ids = $result->fetch_all(MYSQLI_NUM);
    if ($result->num_rows > 0) {
      $student_id = $ids[0][0];
    } else {
      $name = $student[0];
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