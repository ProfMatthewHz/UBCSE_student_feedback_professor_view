<?php
  error_reporting(-1); // reports all errors
  ini_set("display_errors", "1"); // shows all errors
  ini_set("log_errors", 1);
  session_start();
  require "lib/constants.php";

  if(!isset($_SESSION['id'])) {
    header("Location: ".SITE_HOME."index.php");
    exit();
  }
  $email = $_SESSION['email'];
  $id = $_SESSION['id'];
  $student_ID = $_SESSION['student_ID'];
  require "lib/database.php";
  $con = connectToDatabase();
  $student_classes = array();
  $stmt = $con->prepare('SELECT DISTINCT course.name, surveys.id FROM `teammates`  INNER JOIN surveys
ON teammates.survey_id = surveys.id INNER JOIN course on course.id = surveys.course_id where teammates.student_id =? AND surveys.expiration_date > NOW() AND surveys.start_date <= NOW()');
  $stmt->bind_param('i', $student_ID);
  $stmt->execute();
  $stmt->bind_result($class_name,$surveys_id);
  $stmt->store_result();
  while ($stmt->fetch()){
    $student_classes[$class_name] = $surveys_id;
  }
  $_SESSION['student_classes'] = $student_classes;

  if(isset($_POST['courseSelect'])){
    $_SESSION['course'] = $_POST['courseSelect'];
    $_SESSION['surveys_ID'] = $_SESSION['student_classes'][$_SESSION['course']];

    header("Location: peerEvalForm.php");
    exit();
  }
 ?>
<!DOCTYPE HTML>
<html>
<title>UB CSE Evaluation Survey Selection</title>
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
    <div id="headerContentName"><font class="w3-center w3-theme"><h1>Please select the course you would like to complete a peer evaluation for.</h1></font></div>
</header>
<hr>
<form id="courseSelect" class="w3-container w3-card-4 w3-light-blue" method='post'>
  <div id= "dropdown" style="text-align:center;" class="dropdown w3-theme w3-center">
    <select name ="courseSelect">
      <?php
        if(isset($_SESSION['student_classes'])) {
          foreach ($student_classes as $key => $value) {
            echo ('<option value="' . $key .'">' . $key .'</option>');
          }
        }
      ?>
    </select>
  </div>
  <input type='submit' id="EvalSubmit" class="w3-center w3-button w3-theme-dark" value="Continue"></input>
</form>
</body>
</html>
