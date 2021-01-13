<?php

// needed files
require_once "constants.php";

// define a class that handles information related to instructors
class InstructorInfo {

  // standard data members
  public $id = NULL;
  public $name = NULL;
  public $email = NULL;
  public $otp = NULL;
  public $otp_expiration = NULL;
  public $session_expiration = NULL;
  public $csrf_token = NULL;

  // flag data members
  public $init_auth_status = 1;
  public $otp_status = 1;
  public $session_status = 2;

  // function members
  // checks the value of the initial authorization cookie
  public function check_init_auth($db_connection) {

    // try to get the value of the initial authorization cookie
    $init_auth = NULL;

    if (isset($_COOKIE[INIT_AUTH_COOKIE_NAME])) {
      $init_auth = $_COOKIE[INIT_AUTH_COOKIE_NAME];
    } else {
      $this->init_auth_status = 1;
      return;
    }

    // hash the value in order to lookup the value stored in the database
    $init_auth = hex2bin($init_auth);

    // check for errors
    if (!$init_auth) {
      $this->init_auth_status = 1;
      return;
    }

    $init_auth = hash_pbkdf2("sha256", $init_auth, SESSIONS_SALT, PBKDF2_ITERS);

    // query the database for the associated instructor
    $stmt = $db_connection->prepare('SELECT id, email, otp, otp_expiration FROM instructors WHERE init_auth_id=?');
    $stmt->bind_param('s', $init_auth);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_all(MYSQLI_ASSOC);

    // check for no match
    if ($result->num_rows != 1) {
      $this->init_auth_status = 1;
      return;
    }

    // set init authorization as valid
    $this->init_auth_status = 0;

    // populate the necessary data into the object
    $this->id = $data[0]['id'];
    $this->email = $data[0]['email'];
    $this->otp = $data[0]['otp'];
    $this->otp_expiration = $data[0]['otp_expiration'];

    // check the otp expiration status
    if (time() < $this->otp_expiration) {
      $this->otp_status = 0;
    } else {
      $this->otp_status = 1;
    }
  }

  // checks the value of the session cookie
  public function check_session($db_connection, $action) {

    // try to get the value of the session cookie
    $session_cookie = NULL;

    if (isset($_COOKIE[SESSION_COOKIE_NAME])) {
      $session_cookie = $_COOKIE[SESSION_COOKIE_NAME];
    } else {
      $this->session_status = 2;

      if ($action == 0) {
        http_response_code(302);
        header("Location: ".INSTRUCTOR_HOME."index.php");
        exit();
      } else if ($action == 2) {
        http_response_code(403);
        echo "Forbidden";
        exit();
      }

      return;
    }

    // hash the value in order to lookup the value stored in the database
    $session_cookie = hex2bin($session_cookie);

    // check for errors
    if (!$session_cookie) {
      $this->session_status = 2;

      if ($action == 0) {
        http_response_code(302);
        header("Location: ".INSTRUCTOR_HOME."index.php");
        exit();
      } else if ($action == 2) {
        http_response_code(403);
        echo "Forbidden";
        exit();
      }

      return;
    }

    $session_cookie = hash_pbkdf2("sha256", $session_cookie, SESSIONS_SALT, PBKDF2_ITERS);

    // query the database for the associated instructor
    $stmt = $db_connection->prepare('SELECT id, name, csrf_token, session_expiration FROM instructors WHERE session_token=?');
    $stmt->bind_param('s', $session_cookie);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_all(MYSQLI_ASSOC);

    // check for no match
    if ($result->num_rows != 1) {
      $this->session_status = 2;

      if ($action == 0) {
        http_response_code(302);
        header("Location: ".INSTRUCTOR_HOME."index.php");
        exit();
      } else if ($action == 2) {
        http_response_code(403);
        echo "Forbidden";
        exit();
      }

      return;
    }

    // populate the necessary data into the object
    $this->id = $data[0]['id'];
    $this->name = $data[0]['name'];
    $this->csrf_token = $data[0]['csrf_token'];
    $this->session_expiration = $data[0]['session_expiration'];

    // check the session expiration status
    if (time() < $this->session_expiration) {
      $this->session_status = 0;
    } else {
      $this->session_status = 1;

      // now null out the session token field
      $stmt = $db_connection->prepare('UPDATE instructors SET session_token=NULL WHERE id=?');
      $stmt->bind_param('i', $this->id);
      $stmt->execute();

      if ($action == 0) {
        http_response_code(302);
        header("Location: ".INSTRUCTOR_HOME."index.php");
        exit();
      } else if ($action == 2) {
        http_response_code(403);
        echo "Forbidden";
        exit();
      }

      return;
    }

    // finally last redirect action
    if ($action == 1) {
      http_response_code(302);
      header("Location: ".INSTRUCTOR_HOME."surveys.php");
      exit();
    }

  }

}
?>
