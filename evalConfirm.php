<?php
//error logging
error_reporting(-1); // reports all errors
ini_set("display_errors", "1"); // shows all errors
ini_set("log_errors", 1);
session_start();

if (!isset($_SESSION['email']) || !isset($_SESSION['surveys_id']) || !isset($_SESSION['course']) || 
	!isset($_SESSION['group_members']) || !isset($_SESSION['group_ids']) || !isset($_SESSION['group_member_number']) ||
    !isset($_SESSION['topics']) || !isset($_SESSION['answers'])) {
    header("Location: " . SITE_HOME . "/index.php");
    exit();
} else {
  require "lib/database.php";
  $con = connectToDatabase();
  $course = $_SESSION['course'];
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
			<h1 class="display-1"><?php echo $course;?> Teamwork Evaluation</h1>
            <div class="text-primary"><p class="lead">Review of Evaluations</p></div>
            <div class="progress">
                <div class="progress-bar" role="progressbar" height="32px;" style="width: 100%;" aria-valuenow="<?php echo($num_of_group_members);?>" aria-valuemin="0" aria-valuemax="<?php echo($num_of_group_members);?>"><b>100%</b></div>
            </div>
            <br>
            <div class="row mt-5 mx-1">
                <div class="col-12 bg-primary text-white"><b>Your Submitted Evaluations</b></div>
            </div>
            <div class="row pt-1 mx-1 align-items-center">
                <div class="col-2"><b>Name</b></div>
            <?php
                foreach ($topics as $topic) {
                    echo '<div class="col-2 ms-auto"><b>'.$topic.'</b></div>';
                }
                echo '</div>';
                foreach ($names as $name) {
                    echo '<div class="row mt-2 mx-1 align-items-center">';
                    echo '  <div class="col-2"><b>'.$name.'</b></div>';
                    for ($idx = 0; $idx < count($topics); $idx++) {
                        echo '<div class="col-2 ms-auto">'.$answers[$topics[$idx]][$scores[$name][$idx]].'</div>';
                    }
                    echo '</div>';
                }
            ?>
        </div>
    </main>
</body>
</html>