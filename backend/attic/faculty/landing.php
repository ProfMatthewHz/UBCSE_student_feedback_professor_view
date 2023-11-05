    
<?php
error_reporting(-1); // reports all errors
ini_set("display_errors", "1"); // shows all errors
ini_set("log_errors", 1);
session_start();
if(!isset($_SESSION['id'])) {
   header("Location: https://www-student.cse.buffalo.edu/CSE442-542/2019-Summer/cse-442e/index.php");
   exit();
 }
$email = $_SESSION['email'];
$id = $_SESSION['id'];
$DATABASE_HOST = 'tethys.cse.buffalo.edu';
$DATABASE_USER = 'jeh24';
$DATABASE_PASS = '50172309';
$DATABASE_NAME = 'cse442_542_2019_summer_teame_db';
 // Try and connect using the info above.
 mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
if ( mysqli_connect_errno() ) {
        // If there is an error with the connection, stop the script and display the error.
        die ('Failed to connect to MySQL: ' . mysqli_connect_error());
 }
 $stmt = $con->prepare('SELECT Name FROM faculty WHERE email=?');
 $stmt->bind_param('s', $email);
 $stmt->execute();
 $stmt->bind_result($name);
 $stmt->store_result();
 $stmt->fetch();
 ?>
<!DOCTYPE HTML>
<html>
<title>UB CSE faculty</title>
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
.dropbtn {
  background-color: #4CAF50;
  color: white;
  padding: 16px;
  font-size: 16px;
  border: none;
}
.dropdown {
  position: relative;
  display: inline-block;
}
.dropdown-content {
  display: none;
  position: absolute;
  background-color: #f1f1f1;
  min-width: 160px;
  box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
  z-index: 1;
}
.dropdown-content a {
  color: black;
  padding: 12px 16px;
  text-decoration: none;
  display: block;
}
.dropdown-content a:hover {background-color: #ddd;}
.dropdown:hover .dropdown-content {display: block;}
.dropdown:hover .dropbtn {background-color: #3e8e41;}
</style>

<!-- Header -->
<header id="header" class="w3-container w3-theme w3-padding">
    <div id="headerContentName"  <font class="w3-center w3-theme"> <h1> <?php echo ('Welcome '.$name.'!'); ?> </h1> </font> </div>
</header>

<hr>


</div>

</body>
</html>