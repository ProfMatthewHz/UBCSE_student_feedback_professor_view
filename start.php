  <?php
//error logging
error_reporting(-1); // reports all errors
ini_set("display_errors", "1"); // shows all errors
ini_set("log_errors", 1);
ini_set("error_log", "~/php-error.log");

session_start();
require "lib/constants.php";
require "lib/database.php";
$con = connectToDatabase();

if(!empty($_SERVER['uid'])) {
  $email = $_SERVER['uid']."@buffalo.edu";

  $stmt = $con->prepare('SELECT student_id FROM students WHERE email=?');
  $stmt->bind_param('s', $email);
  $stmt->execute();
  $stmt->bind_result($student_ID);
  $stmt->store_result();
  if ($stmt->num_rows == 0) {
    http_response_code(400);
    echo "Unknown email address entered. Talk to your professor to get you added as a site user.";
    exit();
  }
  $stmt->fetch();
	session_regenerate_id();
	$_SESSION['loggedin'] = TRUE;
	$_SESSION['email'] = $email;
	$_SESSION['student_ID'] = $student_ID;
	$stmt->close();
	header("Location: ".SITE_HOME."/courseSelect.php");
	exit();
} else {
  http_response_code(400);
  echo "Could not connect: Error connecting to shibboleth. Talk to Matthew to get this fixed.";
  exit();
}
