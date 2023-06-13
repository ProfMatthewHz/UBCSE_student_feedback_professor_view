<?php
function getIdFromEmail($db_connection, $email) {
  $ret_val = 0;
  $query_str = 'SELECT id FROM students WHERE email=?';
  $stmt = $db_connection->prepare($query_str);
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
?>