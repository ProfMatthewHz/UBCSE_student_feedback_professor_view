<!DOCTYPE HTML>
<html>
<title>UB CSE Peer Evaluation</title>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
<link rel="stylesheet" href="https://www.w3schools.com/lib/w3-theme-blue.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.3.0/css/font-awesome.min.css">
<body>


<style>
.grid-container {
  display: grid;
  grid-column-start: 1;
  grid-column-end: 3;
  grid-template-columns: auto auto auto;
  background-color: #2196F3;
  padding: 10px;
}
hr {
    clear: both;
    visibility: hidden;
}

</style>

<!-- Header -->
<header id="header" class="w3-container w3-center w3-theme w3-padding">
    <div id="headerContentName"  <font color="black"> <h1> CSE Professor Login </h1> </font> </div>
</header>




<hr>

<div id="login" class="w3-row-padding w3-center w3-padding">

  <form id="loginEmail" class="w3-container w3-card-4 w3-light-blue"   method='post'>
      <div id="loginEmailEntry" class="w3-section">

      <input placeholder="ubitname@buffalo.edu" name ='email' id="email" class="w3-input w3-light-grey" type="email" pattern="^[a-zA-Z0-9]+@buffalo.edu$" required>
      <hr>
	  <input placeholder="password" name="password" id="password" class="w3-input w3-light-grey" type="password">
	  <hr>
      <input type='submit' id="loginEmailEntryButton" class="w3-center w3-button w3-theme-dark" value='Login'></input>
      <hr>
	  </div>
  </form>
  <form class="w3-container w3-light-blue" action="resetPassword.php">
  	  <input type='submit'  id='reset' class="w3-center w3-button w3-theme-dark" value="Reset Password"></input>
	  <hr>

  </form>
</div>

  <hr>
<?php
require "lib/password.php";
//error reporting
error_reporting(-1); // reports all errors
ini_set("display_errors", "1"); // shows all errors
ini_set("log_errors", 1);
ini_set("error_log", "/web/CSE442-542/2019-Summer/cse-442e/faculty/php-error.log");

session_start();
// Change this to your connection info.
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

// Now we check if the data from the login form was submitted, isset() will check if the data exists.
if ( isset($_POST['email'],$_POST['password']) && !empty($_POST['password'])) {
	// Could not get the data that should have been sent.
	//die ('Please fill both the username and password field!');

// Prepare our SQL, preparing the SQL statement will prevent SQL injection.
if ($stmt = $con->prepare('SELECT id, password FROM faculty WHERE email = ?')) {
	// Bind parameters (s = string, i = int, b = blob, etc), in our case the username is a string so we use "s"
	$stmt->bind_param('s', $_POST['email']);
	$stmt->execute();
	// Store the result so we can check if the account exists in the database.
	$stmt->store_result();
if ($stmt->num_rows > 0) {
	$stmt->bind_result($id, $password);
	$stmt->fetch();
	// Account exists, now we verify the password.
	// Note: remember to use password_hash in your registration file to store the hashed passwords.
	if (password_verify($_POST['password'], $password)) {
		// Verification success! User has loggedin!
		// Create sessions so we know the user is logged in, they basically act like cookies but remember the data on the server.
		session_regenerate_id();
		$_SESSION['loggedin'] = TRUE;
		$_SESSION['id'] = $id;
		$_SESSION['email'] = $_POST['email'];
		header('Location: landing.php');
		$stmt->close();
		die();
		//echo 'Welcome ' . $_SESSION['name'] . '!';
	} else {
		echo 'Incorrect password!';
	}
} else {
	echo 'Incorrect username!';
}
$stmt->close();
}
}
?>

</div>

<!-- Footer -->
<footer id="footer" class="w3-container w3-theme-dark w3-padding-16">
  <h3>Acknowledgements</h3>
  <p>Powered by <a href="https://www.w3schools.com/w3css/default.asp" target="_blank">w3.css</a></p>
  <p> <a  class=" w3-theme-light" target="_blank"></a></p>
</footer>

</body>
</html>
