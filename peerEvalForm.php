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
    <h1>You will fill out an evaluation form for yourself and each of your team mates. </h1>
    <hr>
    <h1>Current person you're evaluating: <?php echo $Name?> </h1>
    <hr>
    <h1>Please select the option for each prompt that best fits for each question.</h1>
    <hr>
    <h3>Question 1: Role</h3>
	  <select name="Q1" required class="w3-select">
     <option name="Q1" hidden disabled selected value>--select an option --</option>
	   <option value="0" name="Q1" <?php if($student_scores[0]==0){echo("selected='selected'");}?> >Does not willingly assume team roles, rarely completes assigned work.</option>
	   <option value="1" name="Q1" <?php if($student_scores[0]==1){echo("selected='selected'");}?> >Usually accepts assigned team roles, occasionally completes assigned work.</option>
	   <option value="2" name="Q1" <?php if($student_scores[0]==2){echo("selected='selected'");}?> >Accepts assigned team roles, mostly completes assigned work.</option>
	   <option value="3" name="Q1" <?php if($student_scores[0]==3){echo("selected='selected'");}?> >Accepts all assigned team roles, always completes assigned work.</option>
	  </select>

    <hr>
    <h3>Question 2: Leadership</h3>
	 <select name="Q2" required class="w3-select">
     <option name="Q2" hidden disabled selected value>--select an option --</option>
	   <option value="0" name="Q2" <?php if($student_scores[1]==0){echo("selected='selected'");}?> >Rarely takes leadership role, does not collaborate, sometimes willing to assist teammates.</option>
	   <option value="1" name="Q2" <?php if($student_scores[1]==1){echo("selected='selected'");}?> >Occasionally shows leadership, mostly collaborates, generally willin to assist teammates.</option>
	   <option value="2" name="Q2" <?php if($student_scores[1]==2){echo("selected='selected'");}?> >Shows an ability to lead when necessary, willing to collaborate, willing to assist teammates.</option>
	   <option value="3" name="Q2" <?php if($student_scores[1]==3){echo("selected='selected'");}?> >Takes leadership role, is a good collaborator, always willing to assist teammates.</option>
	  </select>

    <hr>
    <h3>Question 3: Participation</h3>
	  <select name="Q3" required class="w3-select">
     <option name="Q3" hidden disabled selected value>--select an option --</option>
	   <option value="0" name="Q3" <?php if($student_scores[2]==0){echo("selected='selected'");}?> >Often misses meetings, routinely unprepared for meetings, rarely participates in meetings and doesnt share ideas.</option>
	   <option value="1" name="Q3" <?php if($student_scores[2]==1){echo("selected='selected'");}?> >Occasionally misses/ doesn't participate in meetings, somewhat unprepared for meetings, offers unclear/ unhelpful ideas.</option>
	   <option value="2" name="Q3" <?php if($student_scores[2]==2){echo("selected='selected'");}?> >Attends and participates in most meetings, comes prepared, and offers useful ideas.</option>
	   <option value="3" name="Q3" <?php if($student_scores[2]==3){echo("selected='selected'");}?> >Attends and participates in all meetings, comes prepared, and clearly expresses well-developed ideas.</option>
	  </select>

    <hr>
    <h3>Question 4: Professionalism</h3>
	  <select name="Q4" required class="w3-select">
     <option name="Q4" hidden disabled selected value>--select an option --</option>
	   <option value="0" name="Q4" <?php if($student_scores[3]==0){echo("selected='selected'");}?> >Often discourteous and/or openly critical of teammates, doesn't want to listen to alternative perspectives.</option>
	   <option value="1" name="Q4" <?php if($student_scores[3]==1){echo("selected='selected'");}?> >Not always considerate or courteous towards teammates, usually appreciates teammates perspectives but often unwilling to consider them.</option>
	   <option value="2" name="Q4" <?php if($student_scores[3]==2){echo("selected='selected'");}?> >Mostly courteous to teammates, values teammates' perspectives and often willing to consider them.</option>
	   <option value="3" name="Q4" <?php if($student_scores[3]==3){echo("selected='selected'");}?> >Always courteous to teammates, values teammates' perspectives, knowledge, and experience, and always willing to consider them.</option>
	  </select>

    <hr>
    <h3>Question 5: Quality</h3>
	  <select name="Q5" required class="w3-select">
     <option name="Q4" hidden disabled selected value>--select an option --</option>
	   <option value="0" name="Q5" <?php if($student_scores[4]==0){echo("selected='selected'");}?> >Rarely commits to shared documents, others often required to revise, debug, or fix their work.</option>
	   <option value="1" name="Q5" <?php if($student_scores[4]==1){echo("selected='selected'");}?> >Occasionally commits to shared documents, others sometimes needed to revise, debug, or fix their work.</option>
	   <option value="2" name="Q5" <?php if($student_scores[4]==2){echo("selected='selected'");}?> >Often commits to shared documents, others occasionally needed to revise, debug, or fix their work.</option>
	   <option value="3" name="Q5" <?php if($student_scores[4]==3){echo("selected='selected'");}?> >Frequently commits to shared documents, others rarely need to revise, debug, or fix their work.</option>
	  </select>

    <hr>
    <div id="login" class="w3-row-padding w3-center w3-padding">
    <input type='submit' id="EvalSubmit" class="w3-center w3-button w3-theme-dark" value=<?php if ($_SESSION['group_member_number']<($num_of_group_members - 1)): ?>
                                                                                            "Continue"
                                                                                          <?php else: ?>
                                                                                            'Submit Peer Evaluation'
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
