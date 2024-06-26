<?php
// Error logging
error_reporting(-1); // Reports all errors
ini_set("display_errors", "1"); // Shows all errors
ini_set("log_errors", 1);
ini_set("error_log", "~/php-error.log");

session_start();

// // Generate fixed CSRF token for testing remove for deployment, replace with commented out code underneath Deployment CSRF token
// $_SESSION['csrf_token'] = "testing";

// // // Deployment CSRF token
// // if (empty($_SESSION['csrf_token'])) {
// //     $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
// // }

// Bring in required code
require_once "lib/random.php";
require_once "lib/database.php";
require_once "lib/constants.php";
require_once "instructor/lib/pairingFunctions.php";
require_once "instructor/lib/instructorQueries.php";
require "lib/studentQueries.php";

// Sanity check that prevents this from being used on the production server
if (!empty($_SERVER['uid'])) {
    header("Location: " . SITE_HOME);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate CSRF token
    // if (!isset($_POST['csrf_token']) || $_SESSION['csrf_token'] !== $_POST['csrf_token']) {
    //     http_response_code(403);
    //     echo "CSRF token validation failed.";
    //     exit();
    //  }


    $con = connectToDatabase();

    if (empty($_POST["UBIT"])) {
        http_response_code(400);
        echo "Bad Request: Missing parameters.";
        exit();
    }

    $email = $_POST['UBIT'] . "@buffalo.edu";
    $id = getInstructorId($con, $email);
    $id_and_name = getStudentInfoFromEmail($con, $email);


// If it is an instructor
    if (!empty($id)) {
        $_SESSION['id'] = $id;
        $_SESSION["surveyTypes"] = getSurveyTypes($con);
        $_SESSION['redirect'] = 1;
// Redirect the instructor to the next page
        http_response_code(302);
        header("Location: " . "http://localhost/StudentSurvey/react-frontend/build");
        //header("Location: ". "https://www-student.cse.buffalo.edu/CSE442-542/2023-Fall/cse-302a/StudentSurvey/react-frontend/build");
        exit();
    }


// Logic for when it is NOT an instructor BUT a student
    if (!empty($id_and_name)) {
        session_regenerate_id();
        $_SESSION['student_id'] = $id_and_name[0];
        $_SESSION['ubit'] = $_POST['UBIT'];
        $_SESSION['redirect'] = 2;
        http_response_code(302);
        header("Location: " . "http://localhost/StudentSurvey/react-frontend/build");
        // header("Location: ". "https://www-student.cse.buffalo.edu/CSE442-542/2023-Fall/cse-302a/StudentSurvey/react-frontend/build");
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
            <!-- Include the CSRF token in a hidden field -->
<!--            <input type="hidden" name="csrf_token" value="--><?php //echo $_SESSION['csrf_token']; ?><!--"> -->
            <input class="btn btn-success" type="submit" value="Pretend Login"></input>
        </div>
    </div>
</form>
</body>

</html>