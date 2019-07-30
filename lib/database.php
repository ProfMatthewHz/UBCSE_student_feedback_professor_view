<?php

function connectToDatabase()
{
//login to sql
session_start();
//Change this to your connection info.
$DATABASE_HOST = 'tethys.cse.buffalo.edu';
$DATABASE_USER = 'jeh24';
$DATABASE_PASS = '50172309';
$DATABASE_NAME = 'cse442_542_2019_summer_teame_db';
 // Try and connect using the info above.
$con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
if ( mysqli_connect_errno() ) {
        // If there is an error with the connection, stop the script and display the error.
        die ('Failed to connect to MySQL: ' . mysqli_connect_error());
 }
 return $con;
}