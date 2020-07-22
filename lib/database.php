<?php

function connectToDatabase()
{
//login to sql
//Change this to your connection info.
$DATABASE_HOST = 'tethys.cse.buffalo.edu';
$DATABASE_USER = 'FIXME';
$DATABASE_PASS = 'FIXME';
$DATABASE_NAME = 'FIXME';
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
