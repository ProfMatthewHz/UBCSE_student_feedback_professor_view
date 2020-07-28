<?php

//error logging
error_reporting(-1); // reports all errors
ini_set("display_errors", "1"); // shows all errors
ini_set("log_errors", 1);
ini_set("error_log", "~/php-error.log");

// start the session variable
session_start();

// bring in required code
require_once "../lib/database.php";
require_once "../lib/constants.php";
require_once "../lib/infoClasses.php";


// query information about the requester
$con = connectToDatabase();

// try to get information about the instructor who made this request by checking the session token and redirecting if invalid
$instructor = new InstructorInfo();
$instructor->check_session($con, 0);


// check for the query string or post parameter
$sid = NULL;
if($_SERVER['REQUEST_METHOD'] == 'GET')
{
  // respond not found on no query string parameter
  if (!isset($_GET['survey']))
  {
    http_response_code(404);   
    echo "404: Not found.";
    exit();
  }

  // make sure the query string is an integer, reply 404 otherwise
  $sid = intval($_GET['survey']);

  if ($sid === 0)
  {
    http_response_code(404);   
    echo "404: Not found.";
    exit();
  }
}
else
{
  // respond bad request if bad post parameters
  if (!isset($_POST['survey']) or !isset($_POST['csrf-token']))
  {
    http_response_code(400);
    echo "Bad Request: Missing parmeters.";
    exit();
  }
  
  // check CSRF token
  if (!hash_equals($instructor->csrf_token, $_POST['csrf-token']))
  {
    http_response_code(403);
    echo "Forbidden: Incorrect parameters.";
    exit();
  }

  // make sure the post survey id is an integer, reply 400 otherwise
  $sid = intval($_POST['survey']);

  if ($sid === 0)
  {
    http_response_code(400);
    echo "Bad Request: Invalid parameters.";
    exit();
  }
  
}

// try to look up info about the requested survey
$survey_info = array();

$stmt = $con->prepare('SELECT course_id, start_date, expiration_date, rubric_id FROM surveys WHERE id=?');
$stmt->bind_param('i', $sid);
$stmt->execute();
$result = $stmt->get_result();

$survey_info = $result->fetch_all(MYSQLI_ASSOC);

// reply not found on no match
if ($result->num_rows == 0)
{
  http_response_code(404);   
  echo "404: Not found.";
  exit();
}

// make sure the survey is for a course the current instructor actually teaches
$stmt = $con->prepare('SELECT year FROM course WHERE id=? AND instructor_id=?');
$stmt->bind_param('ii', $survey_info[0]['course_id'], $instructor->id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_all(MYSQLI_ASSOC);

// reply forbidden if instructor did not create survey
if ($result->num_rows == 0)
{
  http_response_code(403);   
  echo "403: Forbidden.";
  exit();
}

// now perform the possible deletion function
// first set some flags
$errorMsg = array();

// now perform the basic checks
if($_SERVER['REQUEST_METHOD'] == 'POST')
{
 
  // now check for the agreement checkbox
  if (!isset($_POST['agreement']))
  {
    $errorMsg['agreement'] = 'Please read the statement next to the checkbox and check it if you agree.';
  }
  else if ($_POST['agreement'] != "1")
  {
    $errorMsg['agreement'] = 'Please read the statement next to the checkbox and check it if you agree.';
  }
  
  // now delete the survey if agreement
  if (empty($errorMsg))
  {
    $stmt = $con->prepare('DELETE FROM surveys WHERE id=?');
    $stmt->bind_param('i', $sid);
    $stmt->execute();
    
    // redirect to next page and set message
    $_SESSION['survey-delete'] = "Successfully deleted survey.";
      
    http_response_code(302);   
    header("Location: surveys.php");
    exit();
  }
  
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <link rel="stylesheet" type="text/css" href="../styles/styles.css">
    <title>Delete Survey :: UB CSE Peer Evaluation System</title>
</head>
<body>
<header>
    <div class="w3-container">
          <img src="../images/logo_UB.png" class="header-img" alt="UB Logo">
          <h1 class="header-text">UB CSE Peer Evaluation System</h1>
    </div>
    <div class="w3-bar w3-blue w3-mobile w3-border-blue">
      <a href="surveys.php" class="w3-bar-item w3-button w3-mobile w3-border-right w3-border-left w3-border-white">Surveys</a>
      <a href="courses.php" class="w3-bar-item w3-button w3-mobile w3-border-right w3-border-white">Courses</a>
      <form action="logout.php" method ="post"><input type="hidden" name="csrf-token" value="<?php echo $instructor->csrf_token; ?>" /><input class="w3-bar-item w3-button w3-mobile w3-right w3-border-right w3-border-left w3-border-white" type="submit" value="Logout"></form>
      <span class="w3-bar-item w3-mobile w3-right">Welcome, <?php echo htmlspecialchars($instructor->name); ?></span>
    </div>
</header>
<div class="main-content">
    <div class="w3-container w3-center">
        <h2>Delete Survey</h2>
    </div>
    <br />

      <form action="surveyDelete.php?survey=<?php echo $sid; ?>" method ="post" class="w3-container">  
        <span class="w3-card w3-red"><?php if(isset($errorMsg["agreement"])) {echo $errorMsg["agreement"];} ?></span><br />
        <input type="checkbox" id="agreement" name="agreement" value="1">
        <label for="agreement">I understand that deleting this survey will delete all scores associated with this survey.</label><br /><br />
        
        <input type="hidden" name="survey" value="<?php echo $sid; ?>" />
        
        <input type="hidden" name="csrf-token" value="<?php echo $instructor->csrf_token; ?>" />
        
        <input type="submit" class="w3-button w3-red" value="Delete Survey" />
      </form>
    <br />
</div>
</body>
</html> 
