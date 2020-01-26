<!DOCTYPE HTML>
<?php
//error logging
error_reporting(-1); // reports all errors
ini_set("display_errors", "1"); // shows all errors
ini_set("log_errors", 1);
session_start();

$email = $_SESSION['email'];
$id = $_SESSION['id'];
$student_ID= $_SESSION['student_ID'];
$surveys_ID=$_SESSION['surveys_ID'];
$course = $_SESSION['course'];

require "lib/database.php";
$con = connectToDatabase();

//get group members
$group_members=array();
$group_IDs=array();

$stmt = $con->prepare('SELECT surveys.rubric_id FROM surveys WHERE surveys.id =?;');
$stmt->bind_param('i',$surveys_ID);
$stmt->execute();
$stmt->bind_result($rubric_id);
$stmt->store_result();
$stmt->fetch();
$stmt = $con->prepare('SELECT students.name, students.student_ID FROM teammates
	                     INNER JOIN students ON teammates.teammate_ID = students.student_ID WHERE teammates.survey_ID =? AND teammates.student_ID=?;');
$stmt->bind_param('ii',$surveys_ID,$student_ID);
$stmt->execute();
$stmt->bind_result($group_member,$group_ID);
$stmt->store_result();
while ($stmt->fetch()){
	array_push($group_members,$group_member);
	array_push($group_IDs,$group_ID);
}

$num_of_group_members =  count($group_members);
if (!isset($_SESSION['group_member_number'])){
	$_SESSION['group_member_number'] = 0;
}

$Name =  $group_members[$_SESSION['group_member_number']];
$name_ID = $group_IDs[$_SESSION['group_member_number']];

//fetch eval id, if it exists
$stmt = $con->prepare('SELECT id FROM eval WHERE survey_id=? AND submitter_ID=? AND teammate_id=?');
$stmt->bind_param('iii', $surveys_ID, $student_ID,$name_ID);
$stmt->execute();
$stmt->bind_result($eval_ID);
$stmt->store_result();
$stmt->fetch();
if ($stmt->num_rows == 0){
  //create eval id if does not exist and get get the eval_ID
	$stmt = $con->prepare('INSERT INTO eval (survey_id, submitter_ID, teammate_ID) VALUES(?, ?, ?)');
	$stmt->bind_param('iii', $surveys_ID, $student_ID,$name_ID);
	$stmt->execute();

	$stmt = $con->prepare('SELECT id FROM eval WHERE survey_id=? AND submitter_ID=? AND teammate_ID=?');
	$stmt->bind_param('iii', $surveys_ID, $student_ID,$name_ID);
	$stmt->execute();
	$stmt->bind_result($eval_ID);
	$stmt->store_result();
	$stmt->fetch();
}

$student_scores=array(-1,-1,-1,-1,-1);
//grab scores if they exist
$stmt = $con->prepare('SELECT score1, score2, score3, score4, score5 FROM scores WHERE eval_id=?');
$stmt->bind_param('i', $eval_ID);
$stmt->execute();
$stmt->bind_result($score1, $score2, $score3, $score4, $score5);
$stmt->store_result();
while ($stmt->fetch()) {
	$student_scores=array($score1, $score2, $score3, $score4, $score5);
}

//When submit button is pressed
if ( !empty($_POST) && isset($_POST)) {
	//save results
	$a=intval($_POST['Q1']); $b=intval($_POST['Q2']); $c=intval($_POST['Q3']); $d=intval($_POST['Q4']); $e=intval($_POST['Q5']);
  //if scores don't exist
  if($student_scores[1] == -1){
    $stmt = $con->prepare('INSERT INTO scores (score1, score2, score3, score4, score5, eval_id) VALUES(?,?,?,?,?,?)');
    $stmt->bind_param('iiiiii',$a, $b,$c,$d,$e , $eval_ID);
    $stmt->execute();
  } else {
		$stmt = $con->prepare('UPDATE scores set score1=?, score2=?, score3=?, score4=?, score5=? WHERE eval_id=?');
		$stmt->bind_param('iiiiii',$a, $b,$c,$d,$e , $eval_ID);
		$stmt->execute();
  }
	$stmt = $con->prepare('SELECT score1, score2, score3, score4, score5 FROM scores WHERE eval_id=?');
	$stmt->bind_param('i', $eval_ID);
	$stmt->execute();
	$stmt->bind_result($score1, $score2, $score3, $score4, $score5);
	$stmt->store_result();

	//move to next student in group
	if ($_SESSION['group_member_number'] < ($num_of_group_members - 1)) {
		$_SESSION['group_member_number'] +=1;
	  header("Location: peerEvalForm.php"); //refresh page with next group member
		exit();
	} else{
    //evaluated all students
		$_SESSION = array();
		header("Location: evalConfirm.php");
		exit();
	}
}
?>
<html>
<title>UB CSE Peer Evaluation</title>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
<link rel="stylesheet" href="https://www.w3schools.com/lib/w3-theme-blue.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.3.0/css/font-awesome.min.css">
<body>


<style>
hr {
    clear: both;
    visibility: hidden;
}
input[type=radio]
{
  /* Double-sized Checkboxes */
  -ms-transform: scale(2); /* IE */
  -moz-transform: scale(2); /* FF */
  -webkit-transform: scale(2); /* Safari and Chrome */
  -o-transform: scale(2); /* Opera */
  transform: scale(2);
  padding: 10px;
}
.checkboxtext
{
  /* Checkbox text */
  font-size: 160%;
  display: inline;
}
select {
  width: 950px;
  max-width: 100%;
  /* So it doesn't overflow from it's parent */
}

option {
  /* wrap text in compatible browsers */
  -moz-white-space: pre-wrap;
  -o-white-space: pre-wrap;
  white-space: pre-wrap;
  /* hide text that can't wrap with an ellipsis */
  overflow: hidden;
  text-overflow: ellipsis;
  word-wrap: break-word;
  /* add border after every option */
  border-bottom: 1px solid #DDD;
}
</style>

<!-- Header -->
<header id="header" class="w3-container w3-theme w3-padding">
  <div id="headerContentName"><font color="black"><h1><?php echo $_SESSION['course'];?> Evaluation Form</h1></font></div>
</header>

<hr>
<div id="login" class="w3-row-padding w3-padding">
  <form id="peerEval" class="w3-container w3-card-4 w3-light-blue" method='post'>
    <h1>Current person you're evaluating: <?php echo $Name?></h1>
		<h4>Evaluation <?php echo($_SESSION['group_member_number']+1)?> of <?php echo($num_of_group_members)?> </h4>
    <hr>
		<?php
		$stmt = $con->prepare('SELECT description FROM rubrics WHERE rubrics.id=?');
		$stmt->bind_param('i', $rubric_id);
		$stmt->execute();
		$stmt->bind_result($description);
		$stmt->store_result();
		$stmt->fetch();

		echo("<h1>".$description."</h1>");


		$stmt = $con->prepare('SELECT rubric_questions.question, rubric_responses.response
FROM rubrics
INNER JOIN rubric_questions ON rubrics.id = rubric_questions.rubric_id
INNER JOIN rubric_responses ON rubric_questions.id = rubric_responses.question_id
WHERE rubrics.id =?');
		$questions = array(array());
		$stmt->bind_param('i', $rubric_id);
		$stmt->execute();
		$stmt->bind_result($question,$response);
		$stmt->store_result();
		while ($stmt->fetch()) {
			if(!array_key_exists($question,$questions)){$questions[$question] =array($response); }
			else{$questions[$question][] =$response;
}
		}
		//var_dump($questions);

		$prev_question = "";
		$question_num = 0;
		$response_num =0;
		unset($questions[0]);
		foreach ($questions as $question => $responses) {
			$response_num =0;
			$question_num +=1;

			echo nl2br("<hr> \n");
			echo nl2br("<h3>Question ". $question_num. ": ". $question. "</h3>\n");
			echo nl2br("<select name=\"Q".$question_num. "\" required class=\"w3-select\">\n");
			echo nl2br ("<option name=\"Q1".$question_num. "\" hidden disabled selected value>--select an option --</option>\n");

			foreach ($responses as $response) {
				echo("<option value=\"".$response_num. "\"name=\"Q".$question_num."\"");
				echo nl2br((($student_scores[$question_num -1]== $response_num)?"selected='selected'>":">").$response."</option>\n");
				$response_num +=1;
				}
				echo "</select>";

		}
		?>

    <hr>
    <div id="login" class="w3-row-padding w3-center w3-padding">
    <input type='submit' id="EvalSubmit" class="w3-center w3-button w3-theme-dark" value=<?php if ($_SESSION['group_member_number']<($num_of_group_members - 1)): ?>
                                                                                            "Continue with next evaluation"
                                                                                          <?php else: ?>
                                                                                            'Finish evaluations'
																						<?php endif; ?>></input>
  </div>
  <hr>
  </form>
  </div>
  <hr>

<!-- Footer -->
<footer id="footer" class="w3-container w3-theme-dark w3-padding-16">
  <h3>Acknowledgements</h3>
  <p>Powered by <a href="https://www.w3schools.com/w3css/default.asp" target="_blank">w3.css</a></p>
  <p> <a  class=" w3-theme-light" target="_blank"></a></p>
</footer>

</body>
</html>
