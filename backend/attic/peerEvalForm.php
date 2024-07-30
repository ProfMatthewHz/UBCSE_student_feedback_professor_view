<?php
//error logging
error_reporting(-1); // reports all errors
ini_set("display_errors", "1"); // shows all errors
ini_set("log_errors", 1);
session_start();
require "lib/constants.php";
if (!isset($_SESSION['student_id']) || !isset($_SESSION['survey_id']) || !isset($_SESSION['course_name']) || 
		!isset($_SESSION['survey_name']) || !isset($_SESSION['group_members']) || !isset($_SESSION['group_member_number']) ||
    !isset($_SESSION['mc_topics']) || !isset($_SESSION['mc_answers']) || !isset($_SESSION['ff_topics'])) {
	header("Location: " . SITE_HOME . "index.php");
	exit();
}
$student_id = $_SESSION['student_id'];
$course = $_SESSION['course_name'];
$survey_name = $_SESSION['survey_name'];

require "lib/database.php";
require "lib/scoreQueries.php";
require "lib/reviewQueries.php";
$con = connectToDatabase();

//get group members
$group_ids = array_keys($_SESSION['group_members']);

$num_of_group_members = count($_SESSION['group_members']);
$progress_pct = round((($_SESSION['group_member_number']+1) * 100) / $num_of_group_members);
$progress_text = ($_SESSION['group_member_number']+1).' of '.$num_of_group_members;
$review_id = $group_ids[$_SESSION['group_member_number']];
$name =  htmlspecialchars($_SESSION['group_members'][$review_id]);
$mc_topic_ids = array_keys($_SESSION['mc_topics']);
$ff_topic_ids = array_keys($_SESSION['ff_topics']);

//fetch eval id, if it exists
$eval_id = getEvalForReview($con, $review_id);
if (empty($eval_id)) {
	$student_scores=array();
	$student_texts=array();
} else {
	// Get any existing scores
	$student_scores=getEvalScores($con, $eval_id);
	$student_texts=getEvalTexts($con, $eval_id);
}

//When submit button is pressed
if (!empty($_POST)) {
	$actual = count($_SESSION['mc_topics']) + count($_SESSION['ff_topics']);
	if (count($_POST) != $actual) {
		echo "Bad Request: Expected ".$actual." items, but posted ".count($_POST);
		http_response_code(400);
		exit();
	}
	// Verify we have a response for each of the multiple choice topics (cause I'm paranoid)
	foreach ($mc_topic_ids as $topic_id) {
		$radio_name = 'Q'.$topic_id;
		if (!isset($_POST[$radio_name])) {
			echo "Bad Request: Missing POST parameter: ".$radio_name;
			http_response_code(400);
			exit();
		}
	}
	// And Verify we have a response for each of the multiple choice topics (cause I'm paranoid)
	foreach ($ff_topic_ids as $topic_id) {
		$textbox_name = 'Q'.$topic_id;
		if (!isset($_POST[$textbox_name])) {
			echo "Bad Request: Missing POST parameter: ".$radio_name;
			http_response_code(400);
			exit();
		}
	}
	// Only create the eval id when we have an evaluation for this review pairing.
	if (empty($eval_id)) {
		$eval_id = addNewEvaluation($con, $review_id);
	}

	// Add or update the scores in our multiple choice scores table
	foreach ($mc_topic_ids as $topic_id) {
		$radio_name = 'Q'.$topic_id;
		$score_id = intval($_POST[$radio_name]);
		// Check if this key existed previously
		if (array_key_exists($topic_id, $student_scores)) {
			// Update the existing score if it exists
			updateExistingScore($con, $eval_id, $topic_id, $score_id);
		} else {
			// Insert a new score if it had not existed
			insertNewScore($con, $eval_id, $topic_id, $score_id);
		}
	}
	// Add or update the scores in our freeform table
	foreach ($ff_topic_ids as $topic_id) {
		$textbox_name = 'Q'.$topic_id;
		$text = $_POST[$textbox_name];
		// Check if this key existed previously
		if (array_key_exists($topic_id, $student_texts)) {
			// Update the existing score if it exists
			updateExistingText($con, $eval_id, $topic_id, $text);
		} else {
			// Insert a new score if it had not existed
			insertNewText($con, $eval_id, $topic_id, $text);
		}
	}
	//move to next student in group
	if ($_SESSION['group_member_number'] < ($num_of_group_members - 1)) {
		$_SESSION['group_member_number'] += 1;
	  header("Location: ".SITE_HOME."peerEvalForm.php"); //refresh page with next group member
		exit();
	} else {
		header("Location: ".SITE_HOME."evalConfirm.php");
		exit();
	}
}
$button_text = '';
if ($_SESSION['group_member_number']<($num_of_group_members - 1)) {
	$button_text = 'Continue with next evaluation';
} else {
	$button_text = 'Finish evaluations';
}
?>
<!DOCTYPE HTML>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous"></script>
	<title>UB CSE Peer Evaluation</title>
</head>
<body>
	<main>
	  <div class="container-fluid">
			<!-- Header -->
			<div class="row justify-content-md-center bg-primary mt-1 mx-1 rounded-pill">
				<div class="col-sm-auto text-center">
					<h1 class="text-white display-1"><?php echo $course?> <?php echo $survey_name?> Evaluation</h1><br>
					<p class="text-white display-4">Evaluating: <?php echo $name?></p>
				</div>
			</div>
			<div class="row justify-content-md-center mt-4 mx-1 border border-dark border-2">
				<div class="progress">
						<div class="progress-bar" role="progressbar" height="20px;" style="width: <?php echo($progress_pct);?>%;" aria-valuenow="<?php echo($_SESSION['group_member_number']);?>" aria-valuemin="0" aria-valuemax="<?php echo($num_of_group_members);?>"><b><?php echo($progress_text);?></b></div>
				</div>
			</div>
			<form id="peerEval" method='post'>
				<?php
				foreach ($_SESSION['mc_topics'] as $topic_id => $topic) {
					echo '<div class="row mt-5 mx-1">';
					echo '   <div class="col-12 bg-primary text-white"><b>Select the best description of '.$name.'\'s '.$topic.'</b></div>';
					echo '</div>';
					echo '<div class="row pt-1 mx-1 align-items-center">';
					$end_str = '">';
					foreach ($_SESSION['mc_answers'][$topic_id] as $score_id => $response) {
						echo '<div class="col ';
						echo $end_str;
						echo '<input type="radio" class="btn-check" name="Q'.$topic_id.'" id="Q'.$topic_id.$score_id.'" autocomplete="off" required value="'.$score_id.'"';
						if (array_key_exists($topic_id, $student_scores) && $student_scores[$topic_id] == $score_id) {
							echo 'checked ';
						}
						echo '><label class="btn btn-outline-secondary" for="Q'.$topic_id.$score_id.'">'.$response.'</label>';
						echo '</div>';
						// Update formatting so that all but first score use size correctly
						$end_str = 'ms-auto">';
					}
					echo '</div>';
				}
				foreach ($_SESSION['ff_topics'] as $topic_id => $topic) {
					echo '<div class="row mt-5 mx-1">';
					echo '   <div class="col-12 bg-primary text-white"><b>Enter any feedback on '.$name.'\'s '.$topic.'</b></div>';
					echo '</div>';
					echo '<div class="row pt-1 mx-1 align-items-center">';
					echo '<div class="col-12"><textarea class="form-control" name="Q'.$topic_id.'" id="Q'.$topic_id.'" rows="3" placeholder="Provide any feedback here">';
					if (array_key_exists($topic_id, $student_texts)) {
						echo htmlspecialchars($student_texts[$topic_id]);
					}
					echo '</textarea></div></div>';
				}
				?>
				<hr>
				<div class="row pt-1 mx-1 justify-content-end">
					<div class="col-auto" id="login">
						<input type='submit' id="EvalSubmit" value="<?php echo $button_text; ?>"></input>
					</div>
				</div>
				<br>
			</form>
	  </div>
	</main>
</body>
</html>
