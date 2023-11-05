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
  $id_and_name = getStudentInfoFromEmail($con, $email);
  if (empty($id)) {
     http_response_code(400);
     echo 'Double-check UBIT: ' . $email . ' is not in the system.';
     exit();
  }
  session_regenerate_id();
	$_SESSION['student_id'] = $id_and_name[0];
	header("Location: ".SITE_HOME."/courseSelect.php");
	exit();
} else {
  http_response_code(400);
  echo "Error connecting to shibboleth. Let Matthew know since this should not happen.";
  exit();
}
