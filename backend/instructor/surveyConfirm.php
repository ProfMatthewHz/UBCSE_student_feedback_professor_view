<?php
//error logging
error_reporting(-1); // reports all errors
ini_set("display_errors", "1"); // shows all errors
ini_set("log_errors", 1);
ini_set("error_log", "~/php-error.log");

//start the session variable
session_start();

//bring in required code
require_once "../lib/database.php";
require_once "../lib/constants.php";
require_once "../lib/studentQueries.php";
require_once "lib/rubricQueries.php";
require_once "lib/courseQueries.php";
require_once "lib/surveyQueries.php";
require_once "lib/reviewQueries.php";
require_once "lib/enrollmentFunctions.php";
require_once "lib/fileParse.php";
require_once "lib/pairingFunctions.php";

//query information about the requester
$con = connectToDatabase();

//try to get information about the instructor who made this request by checking the session token and redirecting if invalid
if (!isset($_SESSION['id'])) {
  http_response_code(403);
  echo "Forbidden: You must be logged in to access this page.";
  exit();
}
$instructor_id = $_SESSION['id'];

// Verify we have already defined all the data required for this conformation.
if (!isset($_SESSION["survey_data"]) || !isset($_SESSION["survey_course_id"]) || !isset($_SESSION["survey_file"]) || !isset($_SESSION["survey_students"])) {
  http_response_code(302);
  header("Location: ".INSTRUCTOR_HOME."surveys.php");
  exit();
}
// Get the pairings we will be using in this survey
$file_data = $_SESSION["survey_file"];
// Get the survey data we will work with
$survey_data = $_SESSION["survey_data"];
// Get the course id of the course whose roster is being updated
$course_id = $_SESSION['survey_course_id'];
// Get information about the students in this course
$survey_students = $_SESSION["survey_students"];
// Break out the information about the survey
$survey_name = $survey_data['name'];
$survey_s = $survey_data['start'];
$survey_begin = $survey_s->format('M j').' at '. $survey_s->format('g:i A');
$survey_e = $survey_data['end'];
$survey_end = $survey_e->format('M j').' at '. $survey_e->format('g:i A');
$survey_type = $survey_data['pairing_mode'];
$pm_mult = $survey_data['multiplier'];
$rubric_id = $survey_data['rubric'];

if (!array_key_exists($survey_type, $_SESSION["surveyTypes"])) {
  http_response_code(400);
  echo "400: Request uses an incorrect survey type";
  exit();
}
$rubric_name = getRubricName($con, $rubric_id);
if (empty($rubric_name)) {
  http_response_code(400);
  echo "400: Request specifies an incorrect rubric";
  exit();
}

// make sure the survey is for a course the current instructor actually teaches
if (!isCourseInstructor($con, $course_id, $instructor_id)) {
  http_response_code(403);
  echo "403: Forbidden.";
  exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  if (isset($_POST["cancel-survey"]) || isset($_POST["return-survey"])) {
    // Cancel out of the changes and exit back to the instructor home page
    unset($_SESSION["survey_file"]);
    unset($_SESSION["survey_data"]);
    unset($_SESSION["survey_course_id"]);
    unset($_SESSION["survey_students"]);
    unset($_SESSION["pairings"]);

    http_response_code(302);
    header("Location: ".INSTRUCTOR_HOME."surveys.php");
    exit();
  } else {
    $survey_id = insertSurvey($con, $course_id, $survey_name, $survey_s, $survey_e, $rubric_id, $survey_type);
    addReviewsToSurvey($con, $survey_id, $_SESSION['pairings']);
    http_response_code(302);
    header("Location: ".INSTRUCTOR_HOME."surveys.php");
    exit();
  }
}
$course_info = getSingleCourseInfo($con, $course_id, $instructor_id);
$course_code = $course_info['code'];
$course_name = $course_info['name'];
$roster = getRoster($con, $course_id);
$non_roster = getNonRosterStudents($survey_students, $roster);
$file_results = processReviewRows($file_data, $survey_students, $pm_mult, $survey_type);
$_SESSION["pairings"] = $file_results["pairings"];
$roles = $file_results["roles"];
?>
<!DOCTYPE HTML>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">
	<link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous"></script>
  <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
  <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
  <title>CSE Evaluation Survey System - Confirm Survey</title>
</head>
<body class="text-center">
<!-- Header -->
<main>
  <div class="container-fluid">
    <div class="row justify-content-md-center bg-primary mt-1 mx-1 rounded-pill">
      <div class="col-sm-auto">
        <h4 class="text-white display-1">UB CSE Evalution System<br>Confirm Survey</h4>
      </div>
    </div>
    <div class="row justify-content-md-center mt-5 mx-1">
      <div class="col-sm-auto text-center">
        <h4 class="display-4">Adding Survey for <br><?php echo $course_code . " - " . $course_name; ?></h4>
      </div>
    </div>
    <div class="row justify-content-md-center mt-5 align-items-center">
      <div class="col text-end">
        Survey Name:
      </div>
      <div class="col text-start">
         <span class="fs-5"><?php echo $survey_name; ?></span>
      </div>
    </div>
    <div class="row justify-content-md-center align-items-center">
      <div class="col text-end">
        Rubric:
      </div>
      <div class="col text-start">
        <span class="fs-5"><?php echo $rubric_name; ?></span>
      </div>
    </div>
    <div class="row justify-content-md-center align-items-center mb-3">
      <div class="col text-end">
        <span>Survey Active:</span> 
      </div>
      <div class="col text-start">
         <span class="fs-5"><?php echo $survey_begin; ?> to <?php echo $survey_end; ?></span>
      </div>
    </div>
    <div class="row justify-content-md-center">
      <div class="accordion" id="roster_changes">
        <div class="accordion-item shadow">
          <h2 class="accordion-header" id="headerRoster">
            <button class="accordion-button fs-4" type="button" data-bs-toggle="collapse" data-bs-target="#collapseRoster" aria-expanded="true" aria-controls="collapseRoster">Course Roster</button>
          </h2>
          <div id="collapseRoster" class="accordion-collapse collapse show" aria-labelledby="headerRoster">
            <div class="accordion-body">
              <div class="table-responsive">
              <table class="table table-hover table-striped text-start align-middle" id="rosterTable">
                <thead>
                  <tr>
                    <th scope="col">Email</th>
                    <th scope="col">Name</th>
                    <th score="col">Reviewing Others</th>
                    <th score="col">Being Reviewed</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                    // Add a row for each student in the course
                    foreach ($roster as $email => $name_and_id) {
                      echo '<tr>
                              <td>'.$email.'</td>
                              <td>'.$name_and_id[0].'</td>';
                      if (array_key_exists($name_and_id[1], $roles) && $roles[$name_and_id[1]][0]) {
                        echo '<td class="text-success" style="font-weight: bold;">&check;</td>';
                      } else {
                        echo '<td class="text-danger" style="font-weight: bold;">&#x2715;</td>';
                      }
                      if (array_key_exists($name_and_id[1], $roles) && $roles[$name_and_id[1]][1]) {
                        echo '<td class="text-success" style="font-weight: bold;">&check;</td>';
                      } else {
                        echo '<td class="text-danger" style="font-weight: bold;">&#x2715;</td>';
                      }
                      echo '</tr>';
                    }
                  ?>
                </tbody>
              </table>
              </div>
            </div>
          </div>
        </div>
        <div class="accordion-item shadow">
          <h2 class="accordion-header" id="headerExternal">
            <button class="accordion-button fs-4" type="button" data-bs-toggle="collapse" data-bs-target="#collapseExternal" aria-expanded="true" aria-controls="collapseExternal">Non-Course Students</button>
          </h2>
          <div id="collapseExternal" class="accordion-collapse collapse" aria-labelledby="headerExternal">
            <div class="accordion-body">
            <div class="table-responsive">
              <table class="table table-hover table-striped text-start" id="nonRosterTable">
                <thead>
                  <tr>
                    <th scope="col">Email</th>
                    <th scope="col">Name</th>
                    <th score="col">Reviewing Others</th>
                    <th score="col">Being Reviewed</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                    // Add a row for each student in the course
                    foreach ($non_roster as $email => $name_and_id) {
                      echo '<tr>
                              <td>'.$email.'</td>
                              <td>'.$name_and_id[0].'</td>';
                      if (array_key_exists($name_and_id[1], $roles) && $roles[$name_and_id[1]][0]) {
                        echo '<td class="text-success" style="font-weight: bold;">&check;</td>';
                      } else {
                        echo '<td class="text-danger" style="font-weight: bold;">&#x2715;</td>';
                      }
                      if (array_key_exists($name_and_id[1], $roles) && $roles[$name_and_id[1]][1]) {
                        echo '<td class="text-success" style="font-weight: bold;">&check;</td>';
                      } else {
                        echo '<td class="text-danger" style="font-weight: bold;">&#x2715;</td>';
                      }
                      echo '</tr>';
                    }
                  ?>
                </tbody>
              </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <form id="confirm-rubric" method="post">
      <div class="row mt-2 mx-1">
        <div class="col">
          <input class="btn btn-outline-danger" name="cancel-survey" type="submit" value="Cancel Survey"></input>
        </div>
        <div class="col ms-auto">
          <input class="btn btn-success" name="save-survey" type="submit" value="Accept Survey"></input>
        </div>
      </div>
      <hr>
      <div class="row mx-1 mt-2 justify-content-center">
        <div class="col-auto">
          <input class="btn btn-outline-info" name="return-survey" type="submit" value="Return to Instructor Home"></input>
        </div>
      </div>
    </form>
  </div>
  <script>
    $(document).ready(function () {
      $('#rosterTable').DataTable({order: [[3, 'desc'], [2, 'desc']], 'iDisplayLength': 25});
      $('#nonRosterTable').DataTable({order: [[3, 'desc'], [2, 'desc']], 'iDisplayLength': 25});
    });
  </script>
</main>
</body>
</html>