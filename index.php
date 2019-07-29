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
    <div id="headerContentName"  <font color="black"> <h1> UBCSE Peer Evaluation </h1> </font> </div>
</header>




<hr>

<div id="login" class="w3-row-padding w3-center w3-padding">

  <form id="loginEmail" class="w3-container w3-card-4 w3-light-blue" method='post'>
    <h2>Please type in your UB email address! You'll then receive a verification code you can type in further down the page.</h2>
    <div id="loginEmailEntry" class="w3-section">
      <input placeholder="ubitname@buffalo.edu" name ='loginEmailEntryText' id="loginEmailEntryText" class="w3-input w3-light-grey" type="email" pattern="^[a-zA-Z0-9]+@buffalo.edu$" required>
      <hr>
      <input type='submit' id="loginEmailEntryButton" class="w3-center w3-button w3-theme-dark" value='Get Verification Code'></input>
      <hr>
      <input type='button' onclick="window.location.href = 'accessCodePage.php';" class="w3-center w3-button w3-theme-dark" value="Already have valid code?"/></input>
      <hr>
    </div>
  </form>
<?php
//error logging
error_reporting(-1); // reports all errors
ini_set("display_errors", "1"); // shows all errors
ini_set("log_errors", 1);
ini_set("error_log", "~/php-error.log");


require "lib/random.php";
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
if(isset($_POST['loginEmailEntryText']) && !empty($_POST['loginEmailEntryText']) ){
    $email = $_POST['loginEmailEntryText'];
    $expiration_time = time()+ 60 * 15;
    //update passcode and timestamp
    $stmt = $con->prepare('UPDATE student_login SET expiration_time =? WHERE email=?');
    $stmt->bind_param('is', $expiration_time, $email);
    $stmt->execute();
    if($stmt->affected_rows == 0){
      $stmt = $con->prepare('INSERT INTO student_login (email,expiration_time) VALUES(?,?)');
      $stmt->bind_param('si', $email, $expiration_time);
      $stmt->execute();
    }
    $codeFree = false;
    //if password is taken try until it's not taken
    while(!$codeFree){
      $code = random_string(10);
      $stmt = $con->prepare('UPDATE student_login SET password =? WHERE email=?');
      $stmt->bind_param('ss', $code, $email);
      $codeFree = $stmt->execute();
    }
    $date = new DateTime("@$expiration_time");
    $date->setTimezone(new DateTimeZone('America/New_York'));
    $humanExpTime = $date->format('h:i a');
    //be careful the email text is whitespace sensitive
  mail($email,"Access Code", "<h1>Your code is: ".$code."</h1>
        <p>It will expire at ".$humanExpTime." EST</p>
        </hr>
        Use it here: https://www-student.cse.buffalo.edu/CSE442-542/2019-Summer/cse-442e/accessCodePage.php",
        'Content-type: text/html; charset=utf-8');
      header("Location: emailConfirmation.php"); /* Redirect browser to a test link*/
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
