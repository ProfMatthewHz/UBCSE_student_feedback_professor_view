  <?php
//error logging
error_reporting(-1); // reports all errors
ini_set("display_errors", "1"); // shows all errors
ini_set("log_errors", 1);
ini_set("error_log", "~/php-error.log");

session_start();
require "lib/constants.php";
require "lib/database.php";
require "lib/studentQueries.php";
$con = connectToDatabase();

if(!empty($_SERVER['uid'])) {
  $email = $_SERVER['uid']."@buffalo.edu";
  $id = getIdFromEmail($con, $email);
  if (empty($id)) {
     http_response_code(400);
     echo 'Double-check UBIT: ' . $email . ' is not in the system.';
     exit();
  }
  session_regenerate_id();
	$_SESSION['email'] = $email;
	$_SESSION['student_ID'] = $student_ID;
	$stmt->close();
	header("Location: ".SITE_HOME."/courseSelect.php");
	exit();
} else {
  http_response_code(400);
  echo "Error connecting to shibboleth. Let Matthew know since this should not happen.";
  exit();
}
