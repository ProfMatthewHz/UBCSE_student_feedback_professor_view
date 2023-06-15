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

// Sanity check that prevents this from being used on the production server
if(!empty($_SERVER['uid'])) {
 	header("Location: ".SITE_HOME);
 	exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  // query information about the requester
  $con = connectToDatabase();
  if (empty($_POST["UBIT"])) {
    http_response_code(400);
    echo "Bad Request: Missing parameters.";
    exit();
  }
  $email = $_POST['UBIT']."@buffalo.edu";
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
}
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous"></script>
  <title>UB CSE Evaluation Survey Selection</title>
</head>
<body class="text-center">
<form class="mt-2 mx-1" id="fake_shibboleth" method="post">
    <div class="row mx-1 mt-2 justify-content-center">
      <div class="col-sm-8">
          <div class="form-floating mt-1 mb-3">
            <input id="UBIT" type="text" class="form-control" name="UBIT" required value=""></input>
            <label for="UBIT">UBIT From Shibboleth:</label>
          </div>
      </div>
    </div>
    <div class="row mx-1 mt-2 justify-content-center">
      <div class="col-auto">
      <input class="btn btn-success" type="submit" value="Pretend Login"></input>
      </div></div>
    </form>
</body></html>
