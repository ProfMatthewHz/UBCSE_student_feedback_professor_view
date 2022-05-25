<?php
function uploadRoster($con, $names_emails) {
  // now insert the roster into the roster database and the student database if needed
  $roster_size = count($names_emails);

  // prepare sql statements
  $stmt_check = $con->prepare('SELECT student_id FROM students WHERE email=?');
  $stmt_news = $con->prepare('INSERT INTO students (email, name) VALUES (?, ?)');

  for ($i = 0; $i < $roster_size; $i ++) {
    $stmt_check->bind_param('s', $names_emails[$i][1]);
    $stmt_check->execute();
    $result = $stmt_check->get_result();
    $student_info = $result->fetch_all(MYSQLI_ASSOC);

    // check if the student already exists if they don't insert them
    if ($result->num_rows == 0) {
      $stmt_news->bind_param('ss', $names_emails[$i][1], $names_emails[$i][0]);
      $stmt_news->execute();
    }
  }
}
?>