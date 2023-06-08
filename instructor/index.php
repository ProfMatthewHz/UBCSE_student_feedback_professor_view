<?php

//error logging
error_reporting(-1); // reports all errors
ini_set("display_errors", "1"); // shows all errors
ini_set("log_errors", 1);
ini_set("error_log", "~/php-error.log");

// start the session variable
session_start();

// bring in required code
require_once "../lib/random.php";
require_once "../lib/database.php";
require_once "../lib/constants.php";
require_once "../lib/infoClasses.php";
require_once "lib/instructorQueries.php";


// query information about the requester
$con = connectToDatabase();

// try to get information about the instructor who made this request by checking the session cookie
// redirect to home page if already logged in
$instructor = new InstructorInfo();

// define needed variables
$email_error_message = "";
$email_error_message2 = "";

// handle data from shibboleth
if (!empty($_SERVER['uid'])) {
  // make sure the email is not just whitespace
  $email = $_SERVER['uid']."@buffalo.edu";

  // Now check if this is a recognized instructor
  $id = getInstructorId($con, $email);
  if (empty($id)) {
    // This user is not an instructor -- optimistically redirect them to the student-side of the system
    $loc_string = "Location: ".SITE_HOME."/index.php";
    header($loc_string);
    exit();
  }

  // first, generate the session cookie
  $session_cookie = random_bytes(TOKEN_SIZE);

  // hash the initial authorization cookie
  $hashed_cookie = hash_pbkdf2("sha256", $session_cookie, SESSIONS_SALT, PBKDF2_ITERS);

  // set the initial authorization cookie for 12 hours
  $session_expiration = time() + SESSION_TOKEN_EXPIRATION_SECONDS;
  $c_options['expires'] = $session_expiration;
  $c_options['samesite'] = 'Lax';
  setcookie(SESSION_COOKIE_NAME, bin2hex($session_cookie), $c_options);

  // now, generate the CSRF token
  $csrf_token = bin2hex(random_bytes(TOKEN_SIZE));

  // Update the information for this instructor
  updateInstructorInfo($con, $hashed_cookie, $session_expiration, $csrf_token, $id);

  // redirect the instructor to the next page
  http_response_code(302);
  header("Location: ".INSTRUCTOR_HOME."surveys.php");
  exit();
} else {
  http_response_code(400);
  echo "Could not connect: Error connecting to shibboleth. Talk to Matthew to get this fixed.";
  exit();
}
?>
