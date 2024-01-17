<?php
// This is the file needed to make a connection to the database, 
// that is hosted on oceanus.cse.buffalo.edu:3306
// $DATABASE_USER = 'ardianmu'; can be changed to your username if you have access
// $DATABASE_PASS = '50243486'; can be changed to your password if you have access
function connectToDatabase() {
  //login to sql
  //Change this to your connection info.
  $DATABASE_HOST = 'oceanus.cse.buffalo.edu:3306';
  $DATABASE_USER = 'ardianmu';
  $DATABASE_PASS = '50243486';
  $DATABASE_NAME = 'cse302_2023_fall_team_a_db';
  // Try and connect using the info above.
  mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
  try {
    $con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
    if ( mysqli_connect_errno() ) {
        // If there is an error with the connection, stop the script and display the error.
        die ('Failed to connect to MySQL: ' . mysqli_connect_error());
    }
    return $con;
  } catch (Exception $e) {
    die ('Failed to connect to MySQL: ' . $e->getMessage());
  }
}
?>
