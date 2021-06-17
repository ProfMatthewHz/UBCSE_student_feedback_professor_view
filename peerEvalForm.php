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
}
$email = $_SESSION['email'];
$surveys_id=$_SESSION['surveys_id'];
$course = $_SESSION['course'];

require "lib/database.php";
$con = connectToDatabase();

//get group members
$group_members=$_SESSION['group_members'];
$group_ids=$_SESSION['group_ids'];

$num_of_group_members = count($group_members);
$progress_pct = round(($_SESSION['group_member_number'] * 100) / $num_of_group_members);
$name =  htmlspecialchars($group_members[$_SESSION['group_member_number']]);
$reviewers_id = $group_ids[$_SESSION['group_member_number']];

//fetch eval id, if it exists
$stmt = $con->prepare('SELECT id FROM evals WHERE reviewers_id=?');
$stmt->bind_param('i', $reviewers_id);
$stmt->execute();
$stmt->bind_result($eval_id);
$stmt->store_result();
$stmt->fetch();
if ($stmt->num_rows == 0){
  //create eval id if does not exist and get get the eval_id
	$stmt = $con->prepare('INSERT INTO evals (reviewers_id) VALUES(?)');
	$stmt->bind_param('i', $reviewers_id);
	$stmt->execute();

	$stmt = $con->prepare('SELECT id FROM evals WHERE reviewers_id=?');
	$stmt->bind_param('i', $reviewers_id);
	$stmt->execute();
	$stmt->bind_result($eval_id);
	$stmt->store_result();
	$stmt->fetch();
}

// force students to submit results
$student_scores=array(-1,-1,-1,-1,-1);
//grab scores if they exist
$stmt = $con->prepare('SELECT score1, score2, score3, score4, score5 FROM scores WHERE evals_id=?');
$stmt->bind_param('i', $eval_id);
$stmt->execute();
$stmt->bind_result($score1, $score2, $score3, $score4, $score5);
$stmt->store_result();
while ($stmt->fetch()) {
	$student_scores=array($score1, $score2, $score3, $score4, $score5);
}
//When submit button is pressed
if ( !empty($_POST) && isset($_POST)) {
  if (!isset($_POST['Q0']) || !isset($_POST['Q1']) || !isset($_POST['Q2']) || !isset($_POST['Q3']) || !isset($_POST['Q4'])) {
		echo "Bad Request: Missing POST parameters";
		http_response_code(400);
		exit();
	}
	//save results
	$a=intval($_POST['Q0']);
	$b=intval($_POST['Q1']);
	$c=intval($_POST['Q2']);
	$d=intval($_POST['Q3']);
	$e=intval($_POST['Q4']);
  //if scores don't exist
	if($student_scores[1] == -1) {
    $stmt = $con->prepare('INSERT INTO scores (score1, score2, score3, score4, score5, evals_id) VALUES(?,?,?,?,?,?)');
    $stmt->bind_param('iiiiii',$a, $b,$c,$d,$e , $eval_id);
    $stmt->execute();
	 } else {
    $stmt = $con->prepare('UPDATE scores set score1=?, score2=?, score3=?, score4=?, score5=? WHERE evals_id=?');
    $stmt->bind_param('iiiiii',$a, $b,$c,$d,$e , $eval_id);
    $stmt->execute();
  }
	$stmt = $con->prepare('SELECT score1, score2, score3, score4, score5 FROM scores WHERE evals_id=?');
	$stmt->bind_param('i', $eval_id);
	$stmt->execute();
	$stmt->bind_result($score1, $score2, $score3, $score4, $score5);
	$stmt->store_result();

/* When we eventually switch to a normalized tables of scores, this would be the code to update the results
	$question_count = 0;
	foreach($res as $score){
  	if(empty($student_scores)){
    	$stmt = $con->prepare('INSERT INTO scores2 (score, eval_id, question_number) VALUES(?,?,?)');
    	$stmt->bind_param('iii',$score,$eval_id,$question_count);
    	$stmt->execute();
  	} else {
			$stmt = $con->prepare('UPDATE scores2 set score=? WHERE eval_id=? AND question_number=?');
			$stmt->bind_param('iii',$score, $eval_id, $question_count);
			$stmt->execute();
  	}
		$question_count +=1;
	} */

	//move to next student in group
	if ($_SESSION['group_member_number'] < ($num_of_group_members - 1)) {
		$_SESSION['group_member_number'] +=1;
	  header("Location: ".SITE_HOME."peerEvalForm.php"); //refresh page with next group member
		exit();
	} else {
		header("Location: ".SITE_HOME."evalConfirm.php");
		exit();
	}
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
			<h1 class="display-1"><?php echo $_SESSION['course'];?> Teamwork Evaluation</h1>
  		<form id="peerEval" method='post'>
				<div class="text-primary"><p class="lead">Evaluating: <?php echo $name?></p></div>
				<div class="progress">
					<div class="progress-bar" role="progressbar" height="32px;" style="width: <?php echo($progress_pct);?>%;" aria-valuenow="<?php echo($_SESSION['group_member_number']);?>" aria-valuemin="0" aria-valuemax="<?php echo($num_of_group_members);?>"><b><?php echo($progress_pct);?>%</b></div>
				</div>
				<br>
				<?php
				$topic_num = 0;
				foreach ($_SESSION['topics'] as $topic) {
					echo '<div class="row mt-5 mx-1">';
					echo '   <div class="col-12 bg-primary text-white"><b>Select the best description of '.$name.'\'s '.$topic.'</b></div>';
					echo '</div>';
					echo '<div class="row pt-1 mx-1 align-items-center">';
					for ($score_num = 0; $score_num < count($_SESSION['scores']); $score_num++) {
						if ($score_num == 0) {
							$end_str = '">';
						} else {
							$end_str = 'ms-auto">';
						}
						echo '<div class="col-3 ';
						echo $end_str;
						echo '<input type="radio" class="btn-check" name="Q'.$topic_num.'" id="Q'.$topic_num.$score_num.'" autocomplete="off" required value="'.$score_num.'"';
						if ($student_scores[$topic_num] == $score_num) {
							echo 'checked ';
						}
						echo '><label class="btn btn-outline-secondary" for="Q'.$topic_num.$score_num.'">';
						echo $_SESSION['answers'][$topic][$score_num];
						echo '</label>';
						echo '</div>';
					}
					echo '</div>';
					$topic_num = $topic_num + 1;
				}
				?>
				<hr>
				<div id="login">
					<input type='submit' id="EvalSubmit" value=<?php if ($_SESSION['group_member_number']<($num_of_group_members - 1)): ?>
																																																	'Continue with next evaluation'
																																																<?php else: ?>
																																																	'Finish evaluations'
																									<?php endif; ?>></input>
				</div>
				<br>
			</form>
	  </div>
	</main>
</body>
</html>
