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

// try to get information about the instructor who made this request by checking the intial authorization token and the session cookie
// redirect to home page if already logged in
$instructor = new InstructorInfo();
$instructor->check_session($con, 1);
$instructor->check_init_auth($con);

// set basic error flags
$blank_otp = false;
$invalid_otp = false;

// handle access code submissions only if the initial authorization token exists
if (($_SERVER['REQUEST_METHOD'] == 'POST') and ($instructor->init_auth_status != 1))
{

  // make sure the access code has been sent
  if (!isset($_POST['otp']))
  {
    http_response_code(400);
    echo "Bad Request: Missing parmeters.";
    exit();
  }

  // make sure the access code is not just whitespace
  $supplied_otp = trim($_POST['otp']);

  // update error flag
  if (empty($supplied_otp))
  {
    $blank_otp = true;
  }

  // continue only if the access code has not expired and the entered access code was not blank
  if ($instructor->otp_status == 0 and !$blank_otp)
  {

    // now make sure the entered password is valid
    if (password_verify($supplied_otp, $instructor->otp))
    {

      // password good, so start generating needed tokens
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

      // store the new tokens and expiration dates in the database, NULL out the initial authorization id
      $stmt = $con->prepare('UPDATE instructors SET init_auth_id=NULL, session_token=?, session_expiration=?, csrf_token=? WHERE id=?');
      $stmt->bind_param('sisi', $hashed_cookie, $session_expiration, $csrf_token, $instructor->id);
      $stmt->execute();

      // redirect the instructor to the next page
      http_response_code(302);
      header("Location: ".INSTRUCTOR_HOME."surveys.php");
      exit();

    }
    else
    {
      $invalid_otp = true;
    }

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
    <title>Instructor Access Code Entry :: UB CSE Peer Evaluation System</title>
</head>
<body>
<header>
    <div class="w3-container">
          <img src="../images/logo_UB.png" class="header-img" alt="UB Logo">
          <h1 class="header-text">UB CSE Peer Evaluation System</h1>
    </div>
    <div class="w3-bar w3-blue w3-mobile w3-border-blue">
      <a class="w3-text-blue w3-bar-item">Placeholder</a>
    </div>
</header>
    <div class="w3-container w3-center">
        <h2>Instructor Login</h2>
</div>

    <div class="form-group">

      <?php
        if ($_SERVER['REQUEST_METHOD'] == 'GET')
        {
          if ($instructor->init_auth_status == 1)
          {
            echo '<div class="w3-card w3-red"> Error: Please <a href="instructorLogin.php">request an access code</a> first.</div>';
          }
          else if ($instructor->otp_status == 1)
          {
            echo '<div class="w3-card w3-red"> Error: Your access code has expired. Please <a href="instructorLogin.php">request a new one</a>.</div>';
          }
          else
          {
            echo '<div class="w3-card w3-green"> Successfully sent email to ' . htmlspecialchars($instructor->email) . '<br /> The access code expires in 15 minutes.</div>';
          }
        }
      ?>

      <br />
      <br />
      <p>Please enter the access code that was sent to your email.</p><br />
      <span class="w3-red">

        <?php
          if ($_SERVER['REQUEST_METHOD'] == 'POST')
          {
            if ($instructor->init_auth_status == 1)
            {
              echo 'Error: Please <a href="instructorLogin.php">request an access code</a> first.';
            }
            else if ($instructor->otp_status == 1)
            {
              echo 'Error: Your access code has expired. Please <a href="instructorLogin.php">request a new one</a>.';
            }
            else if ($blank_otp)
            {
              echo 'Error: The access code field cannot be blank.';
            }
            else if ($invalid_otp)
            {
              echo 'Error: The entered access code was incorrect. Please check your typing for any mistakes.';
            }
          }
        ?>

      </span>
      <form method="post" action="instructorOTPEntry.php">
        <label for="otp">Access Code:</label><br />
        <input class = "w3-input w3-border" type="text" id="otp" placeholder="**********" name="otp" /><br />
        <input type="submit" class="w3-button w3-green" value="Submit Access Code" />
        <a href="instructorLogin.php"><button type='button' class="w3-button w3-blue" />I need a new access code.</button></a>
      </form>
    </div>
</body>
</html>
