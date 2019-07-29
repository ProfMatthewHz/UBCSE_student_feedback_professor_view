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

hr {
    clear: both;
    visibility: hidden;
}

</style>

<!-- Header -->
<header id="header" class="w3-container w3-theme w3-padding">
    <div id="headerContentName"  <font class="w3-center w3-theme"> <h1> UBCSE Peer Evaluation </h1> </font> </div>
</header>




<hr>

<div id="login" class="w3-row-padding w3-center w3-padding">

  <form id="loginEmail" class="w3-container w3-card-4 w3-light-blue" method='post'>
    <h2>Please type in the access code that has been sent to your UB email. You'll then be redirected to the peer evaluation form.</h2>
    <div id="codeEntry" class="w3-section w3-center ">
      <input placeholder="access code here" name ='accessCodeEntryText' id="accessCodeEntryText" class="w3-input w3-light-grey" type="text" pattern="^[a-zA-Z0-9]*$" required>
      <hr>
      <input type='submit' id="accessCodeEntryButton" class="w3-center w3-button w3-theme-dark" value='Access Peer Evaluation'></input>
      <h2>If your code is older than 15 minutes, click here to get a new one.</h2>
      <input type='button' onclick="window.location.href = 'index.php';" class="w3-center w3-button w3-theme-dark" value="Dont have a valid code?"/></input>
      <hr>
    </div>
  </form>

  <?php
//error logging
error_reporting(-1); // reports all errors
ini_set("display_errors", "1"); // shows all errors
ini_set("log_errors", 1);
ini_set("error_log", "~/php-error.log");

session_start();

$DATABASE_HOST = 'tethys.cse.buffalo.edu';
$DATABASE_USER = 'jeh24';
$DATABASE_PASS = '50172309';
$DATABASE_NAME = 'cse442_542_2019_summer_teame_db';

$con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);

if ( mysqli_connect_errno() ) {
	// If there is an error with the connection, stop the script and display the error.
 	die ('Failed to connect to MySQL: ' . mysqli_connect_error());
 	}

if(isset($_POST['accessCodeEntryText']) && !empty($_POST['accessCodeEntryText'])){
	$code = $_POST['accessCodeEntryText'];
	$stmt= $con->prepare('SELECT * FROM student_login WHERE password=?');
	$stmt->bind_param('s',$code);
	$stmt->execute();
	$stmt->store_result();
	if($stmt->num_rows == 0){
    echo '<script language="javascript">';
    echo 'alert("Code not found! Please check that you have typed the code correctly, or get a new one.")';
    echo '</script>';
		$stmt->close();
		exit();
	}

	$stmt = $con->prepare('SELECT id, email FROM student_login WHERE password=? AND expiration_time > ?');
	$time = time();
	$stmt->bind_param('si',$code,$time);
	$stmt->execute();
	$stmt->store_result();
	if($stmt->num_rows == 0){
    echo '<script language="javascript">';
    echo 'alert("Your access code has expired, please get a new code.")';
    echo '</script>';
		$stmt->close();
		exit();
	}
	$stmt->bind_result($id,$email);
	$stmt->fetch();
	session_regenerate_id();
	$_SESSION['loggedin'] = TRUE;
	$_SESSION['email'] = $email;
	$_SESSION['id'] = $id;
	$stmt->close();
	header("Location: courseSelect.php");
	exit();
}

?>
  <hr>


</div>

<!-- Footer -->
<footer id="footer" class="w3-container w3-theme-dark w3-padding-16">
  <h3>Acknowledgements</h3>
  <p>Powered by <a href="https://www.w3schools.com/w3css/default.asp" target="_blank">w3.css</a></p>
  <p> <a  class=" w3-theme-light" target="_blank"></a></p>
</footer>

</body>
</html>
