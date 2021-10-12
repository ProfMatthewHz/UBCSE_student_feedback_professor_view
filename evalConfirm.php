<?php
require "lib/constants.php";
//error logging
error_reporting(-1); // reports all errors
ini_set("display_errors", "1"); // shows all errors
ini_set("log_errors", 1);
session_start();

if (!isset($_SESSION['email']) || !isset($_SESSION['survey_id']) || !isset($_SESSION['course']) || 
	!isset($_SESSION['group_members']) || !isset($_SESSION['group_ids']) || !isset($_SESSION['group_member_number']) ||
    !isset($_SESSION['topics']) || !isset($_SESSION['answers'])) {
    header("Location: " . SITE_HOME . "index.php");
    exit();
} else {
  require "lib/database.php";
  $con = connectToDatabase();
  $course = $_SESSION['course'];
  $survey_id = $_SESSION['survey_id'];
  $num_of_group_members = count($_SESSION['group_ids']);
  $topics = $_SESSION['topics'];
  $answers = $_SESSION['answers'];
  $names = $_SESSION['group_members'];

  // Store the scores submitted for each teammate
  $scores = array();
  for ($idx = 0; $idx < count($_SESSION['group_ids']); $idx++) {
    // Select the scores for this student
    $stmt = $con->prepare('SELECT score1, score2, score3, score4, score5 FROM scores INNER JOIN evals ON scores.evals_id=evals.id WHERE evals.reviewers_id=?');
    $stmt->bind_param('i', $_SESSION['group_ids'][$idx]);
    $stmt->execute();
    $stmt->bind_result($score1, $score2, $score3, $score4, $score5);
    $stmt->store_result();
    $stmt->fetch();
    if ($stmt->num_rows != 1) {
        // This is not a valid survey for this student
        echo "Cannot find a survey submission: Talk to your instructor about this error.";
        http_response_code(400);
        exit();
    }
    $student_scores=array($score1, $score2, $score3, $score4, $score5);
    $scores[$names[$idx]] = $student_scores;
  }
  unset($_SESSION['surveys_id']);
  unset($_SESSION['course']);
  unset($_SESSION['group_members']);
  unset($_SESSION['group_ids']);
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
	<title>UB CSE Peer Evaluation Confirmation</title>
</head>
<body>
	<main>
	  <div class="container-fluid">
			<!-- Header -->
			<div class="row justify-content-md-center bg-primary mt-1 mx-1 rounded-pill">
				<div class="col-sm-auto text-center">
					<h1 class="text-white display-1"><?php echo $course?> Teamwork Evaluation</h1><br>
					<p class="text-white lead">Evaluation Review</p>
				</div>
			</div>
            <div class="row mt-5 mx-1">
                <div class="col-12 bg-primary text-white text-center"><b class="lead">Your Submissions</b></div>
            </div>
            <div class="row pt-1 mx-1 align-items-center text-center border-bottom border-3 border-dark">
                <div class="col-2"><b>Name</b></div>
            <?php
                foreach ($topics as $topic) {
                    echo '<div class="col-2 ms-auto"><b>'.$topic.'</b></div>';
                }
                echo '</div>';
                $shaded = true;
                foreach ($names as $name) {
                    if ($shaded) {
                        $bg_color = "#e1e1e1";
                    } else {
                        $bg_color = "#f8f8f8";
                    }
                    echo '<div class="row py-2 mx-1 align-items-center border-bottom border-1 border-secondary" style="background-color:'.$bg_color.'">';
                    echo '  <div class="col-2 text-center"><b>'.$name.'</b></div>';
                    for ($idx = 0; $idx < count($topics); $idx++) {
                        echo '<div class="col-2 ms-auto">'.$answers[$topics[$idx]][$scores[$name][$idx]].'</div>';
                    }
                    echo '</div>';
                    $shaded = !$shaded;
                }
            ?>
        </div>
        <div class="row pt-1 mx-1">
            <div class="col-auto align-items-right">
               <a class="btn btn-outline-primary" href="<?php echo(SITE_HOME . 'index.php');?>" role="button">Return to evaluation center</a>
            </div>
            <div class="col-auto align-items-left me-auto">
                <a class="btn btn-secondary" href="<?php echo(SITE_HOME . 'startSurvey.php?survey='.$survey_id);?>" role="button">Revise these evaluations</a>
            </div>
        </div>
    </main>
</body>
</html>