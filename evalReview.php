<?php
require "lib/constants.php";
//error logging
error_reporting(-1); // reports all errors
ini_set("display_errors", "1"); // shows all errors
ini_set("log_errors", 1);
session_start();

if (!isset($_SESSION['email']) || !isset($_SESSION['survey_id']) || !isset($_SESSION['course_name']) || 
    !isset($_SESSION['survey_name']) || !isset($_SESSION['group_members']) ||
    !isset($_SESSION['topics']) || !isset($_SESSION['answers'])) {
    header("Location: " . SITE_HOME . "index.php");
    exit();
} else {
  require "lib/database.php";
  require "lib/scoreQueries.php";
  $con = connectToDatabase();
  $course = $_SESSION['course_name'];
  $survey_name = $_SESSION['survey_name'];
  $survey_id = $_SESSION['survey_id'];
  $num_of_group_members = count($_SESSION['group_members']);
  $topics = $_SESSION['topics'];
  $answers = $_SESSION['answers'];
  $members = $_SESSION['group_members'];

  // Store the scores submitted for each teammate
  $scores = array();
  foreach ($members as $reviewer_id => $name) {
    $scores[$name] = getReviewScores($con, $reviewer_id, $topics, $answers);
  }
  unset($_SESSION['surveys_id']);
  unset($_SESSION['course_name']);
  unset($_SESSION['survey_name']);
  unset($_SESSION['group_members']);
  unset($_SESSION['group_member_number']);
  unset($_SESSION['topics']);
  unset($_SESSION['answers']);
}
?>
<!DOCTYPE HTML>
<html>
<head>
    <meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous"></script>
	<title>UB CSE Peer Evaluation Review</title>
</head>
<body>
	<main>
	    <div class="container-fluid">
			<!-- Header -->
			<div class="row justify-content-md-center bg-primary mt-1 mx-1 rounded-pill">
				<div class="col-sm-auto text-center">
                    <h1 class="text-white display-1"><?php echo $course?> <?php echo $survey_name?> Evaluation</h1><br>
					<p class="text-white lead">Evaluation Review</p>
				</div>
			</div>
            <div class="row mt-5 mx-1">
                <div class="col-12 bg-primary text-white text-center"><b class="lead">Your Submissions</b></div>
            </div>
            <div class="row pt-1 mx-1 align-items-center text-center border-bottom border-3 border-dark">
                <?php
                    emitResultsTable($topics, $answers, $scores, $members);
                ?>
            </div>
            <div class="row pt-1 mx-1">
                <div class="col-auto align-self-end">
                    <a class="btn btn-primary" href="<?php echo(SITE_HOME . 'index.php');?>" role="button">Return to evaluation center</a>
                </div>
            </div>
        </div>
    </main>
</body>
</html>