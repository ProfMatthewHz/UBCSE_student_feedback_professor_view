<?php
// Error logging
error_reporting(-1); // Reports all errors
ini_set("display_errors", "1"); // Shows all errors
ini_set("log_errors", 1);
ini_set("error_log", "~/php-error.log");

// Bring in required code
require "lib/database.php";
require "lib/constants.php";
require "lib/loginStatus.php";
require "lib/loginRoutine.php";

session_start();

// Sanity check that prevents this from being used on the production server
if (!empty($_SERVER['uid'])) {
    http_response_code(400);
    $response['error'] = "Bad Request: This page is not meant to be used in production.";
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
  }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (empty($_POST["UBIT"])) {
        http_response_code(400);
        echo "Bad Request: Missing parameters.";
        exit();
    }
    $con = connectToDatabase();
    setSessionVariables($con, $_POST["UBIT"]);
    
    if (!empty($_SESSION['redirect'])) {
        http_response_code(302);
        header("Location: " . FRONTEND_HOME);
        exit();
    }

    // Not an Instructor OR a student
    http_response_code(403);
    echo 'Unauthorized access attempt, please talk to Professor Hertz if this should NOT be the case';
    exit();
}

?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4"
            crossorigin="anonymous"></script>
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
        </div>
    </div>
</form>
</body>

</html>