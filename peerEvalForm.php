<!DOCTYPE HTML>
<?php
//error logging
error_reporting(-1); // reports all errors
ini_set("display_errors", "1"); // shows all errors
ini_set("log_errors", 1);
session_start();
  
$email = $_SESSION['email'];
$id = $_SESSION['id'];
$Student_ID= $_SESSION['Student_ID'];
$course_ID=$_SESSION['course_ID'];
$course = $_SESSION['course'];

require "lib/database.php";
$con = connectToDatabase();

 //fetch teammate key for current student
 	$stmt = $con->prepare('SELECT Teammate_key FROM Teammates WHERE Student_ID=? AND Course_ID =?');
    $stmt->bind_param('ss', $Student_ID, $course_ID);
    $stmt->execute();
	$stmt->bind_result($Teammate_key);
	$stmt->store_result();
	$stmt->fetch();

	if($stmt->num_rows == 0){ //student has not submitted yet.
	//TODO: make an error here
		//exit();
	}
  //check if grades are already submitted
  //if(!empty($old_scores_string){
    //$old_scores = explode(":", $old_scores_string);
  //}

	//get group members
	$group_members=array();
	$group_IDs=array();
	$stmt = $con->prepare('SELECT Students.Name, Students.Student_ID FROM Teammates
	INNER JOIN Students ON Teammates.Teammate_ID = Students.Student_ID WHERE
	Teammates.Teammate_key=? AND Teammates.Course_ID =? AND Teammates.Student_ID=?;');
    $stmt->bind_param('iii',$Teammate_key, $course_ID,$Student_ID);
    $stmt->execute();
	$stmt->bind_result($group_member,$group_ID);
	$stmt->store_result();
	while ($stmt->fetch()){
		array_push($group_members,$group_member);
		array_push($group_IDs,$group_ID);
	}

	$num_of_group_members =  count($group_members);
	if(!isset($_SESSION['group_member_number'])){
		$_SESSION['group_member_number'] = 0;
	}

	$Name =  $group_members[$_SESSION['group_member_number']];
	$Name_ID = $group_IDs[$_SESSION['group_member_number']];

	//fetch eval id, if it exists
	$stmt = $con->prepare('SELECT id FROM Eval WHERE Teammate_key=? AND Course_ID =? AND Submitter_ID=? AND Teammate_ID=?');
    $stmt->bind_param('iiii', $Teammate_key, $course_ID,$Student_ID,$Name_ID);
    $stmt->execute();
	$stmt->bind_result($Eval_ID);
	$stmt->store_result();
	$stmt->fetch();
	if($stmt->num_rows == 0){//create eval id if does not exist and get get the Eval_ID
		$stmt = $con->prepare('INSERT INTO Eval (Teammate_key, Course_ID, Submitter_ID, Teammate_ID) VALUES(?, ?, ?, ?)');
		$stmt->bind_param('iiii', $Teammate_key, $course_ID,$Student_ID,$Name_ID);
		$stmt->execute();

		$stmt = $con->prepare('SELECT id FROM Eval WHERE Teammate_key=? AND Course_ID =? AND Submitter_ID=? AND Teammate_ID=?');
		$stmt->bind_param('iiii', $Teammate_key, $course_ID,$Student_ID,$Name_ID);
		$stmt->execute();
		$stmt->bind_result($Eval_ID);
		$stmt->store_result();
		$stmt->fetch();
	}

	$current_student_scores=array(-1,-1,-1,-1,-1);
	//grab scores if they exist
	$stmt = $con->prepare('SELECT Score1, Score2, Score3, Score4, Score5 FROM Scores WHERE Eval_key=?');
	$stmt->bind_param('i', $Eval_ID);
	$stmt->execute();
	$stmt->bind_result($Score1, $Score2, $Score3, $Score4, $Score5);
	$stmt->store_result();
	while($stmt->fetch()){
		$current_student_scores=array($Score1, $Score2, $Score3, $Score4, $Score5);
	}
	//if scores don't exist
	if($current_student_scores[1] ==-1){
		$stmt = $con->prepare('INSERT INTO Scores (Eval_key) VALUES(?)');
		$a=0;$b=0;$c=0;$d=0;$e=0;
		$stmt->bind_param('i', $Eval_ID);
		$stmt->execute();
	}

	//When submit button is pressed
	if ( !empty($_POST) && isset($_POST)){
		//save results
		$a=intval($_POST['Q1']); $b=intval($_POST['Q2']); $c=intval($_POST['Q3']); $d=intval($_POST['Q4']); $e=intval($_POST['Q5']);
		$stmt = $con->prepare('UPDATE Scores set Score1=?, Score2=?, Score3=?, Score4=?, Score5=? WHERE Eval_key=?');

		$stmt->bind_param('iiiiii',$a, $b,$c,$d,$e , $Eval_ID);
		$stmt->execute();

		$stmt = $con->prepare('SELECT Score1, Score2, Score3, Score4, Score5 FROM Scores WHERE Eval_key=?');
		$stmt->bind_param('i', $Eval_ID);
		$stmt->execute();
		$stmt->bind_result($Score1, $Score2, $Score3, $Score4, $Score5);
		$stmt->store_result();
		var_dump($Score1, $Score2, $Score3, $Score4, $Score5);


		//move to next student in group
		if($_SESSION['group_member_number'] < ($num_of_group_members - 1)){
			$_SESSION['group_member_number'] +=1;
			    header("Location: peerEvalForm.php"); //refresh page with next group member
				exit();
		}
		else{//evaluated all students
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
</style>

<!-- Header -->
<header id="header" class="w3-container w3-theme w3-padding">
  <div id="headerContentName"  <font color="black"> <h1><?php echo $_SESSION['course'];?> Peer Evaluation Form </h1> </font> </div>
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
    <fieldset id="Question1" >
      <input type="radio"  name="Q1" value="0" <?php if($current_student_scores[0]==0){echo("checked");}?> required><big>' Does not willingly assume team roles, rarely completes assigned work.</big><br>
      <input type="radio"  name="Q1" value="1" <?php if($current_student_scores[0]==1){echo("checked");}?> required><big>' Usually accepts assigned team roles, occasionally completes assigned work.</big><br>
      <input type="radio"  name="Q1" value="2" <?php if($current_student_scores[0]==2){echo("checked");}?> required><big>' Accepts assigned team roles, mostly completes assigned work.</big><br>
      <input type="radio"  name="Q1" value="3" <?php if($current_student_scores[0]==3){echo("checked");}?> required><big>' Accepts all assigned team roles, always completes assigned work.</big><br>
    </fieldset>

    <hr>
    <h3>Question 2: Leadership</h3>
    <fieldset id="Question2" >
      <input type="radio"  name="Q2" value="0" <?php if($current_student_scores[1]==0){echo("checked");}?> required><big>' Rarely takes leadership role, does not collaborate, sometimes willing to assist teammates.</big><br>
      <input type="radio"  name="Q2" value="1" <?php if($current_student_scores[1]==1){echo("checked");}?> required><big>' Occasionally shows leadership, mostly collaborates, generally willin to assist teammates.</big><br>
      <input type="radio"  name="Q2" value="2" <?php if($current_student_scores[1]==2){echo("checked");}?> required><big>' Shows an ability to lead when necessary, willing to collaborate, willing to assist teammates.</big><br>
      <input type="radio"  name="Q2" value="3" <?php if($current_student_scores[1]==3){echo("checked");}?> required><big>' Takes leadership role, is a good collaborator, always willing to assist teammates.</big><br>
    </fieldset>

    <hr>
    <h3>Question 3: Participation</h3>
    <fieldset id="Question3" >
      <input type="radio"  name="Q3" value="0" <?php if($current_student_scores[2]==0){echo("checked");}?> required><big>' Often misses meetings, routinely unprepared for meetings, rarely participates in meetings and doesnt share ideas.</big><br>
      <input type="radio"  name="Q3" value="1" <?php if($current_student_scores[2]==1){echo("checked");}?> required><big>' Occasionally misses/ doesn't participate in meetings, somewhat unprepared for meetings, offers unclear/ unhelpful ideas.</big><br>
      <input type="radio"  name="Q3" value="2" <?php if($current_student_scores[2]==2){echo("checked");}?> required><big>' Attends and participates in most meetings, comes prepared, and offers useful ideas.</big><br>
      <input type="radio"  name="Q3" value="3" <?php if($current_student_scores[2]==3){echo("checked");}?> required><big>' Attends and participates in all meetings, comes prepared, and clearly expresses well-developed ideas.</big><br>
    </fieldset>

    <hr>
    <h3>Question 4: Professionalism</h3>
    <fieldset id="Question4" >
      <input type="radio"  name="Q4" value="0" <?php if($current_student_scores[3]==0){echo("checked");}?> required><big>' Often discourteous and/or openly critical of teammates, doesn't want to listen to alternative perspectives.</big><br>
      <input type="radio"  name="Q4" value="1" <?php if($current_student_scores[3]==1){echo("checked");}?> required><big>' Not always considerate or courteous towards teammates, usually appreciates teammates perspectives but often unwilling to consider them.</big><br>
      <input type="radio"  name="Q4" value="2" <?php if($current_student_scores[3]==2){echo("checked");}?> required><big>' Mostly courteous to teammates, values teammates' perspectives and often willing to consider them.</big><br>
      <input type="radio"  name="Q4" value="3" <?php if($current_student_scores[3]==3){echo("checked");}?> required><big>' Always courteous to teammates, values teammates' perspectives, knowledge, and experience, and always willing to consider them.</big><br>
    </fieldset>

    <hr>
    <h3>Question 5: Quality</h3>
    <fieldset id="Question5" >
      <input type="radio"  name="Q5" value="0" <?php if($current_student_scores[4]==0){echo("checked");}?> required><big>' Rarely commits to shared documents, others often required to revise, debug, or fix their work.</big><br>
      <input type="radio"  name="Q5" value="1" <?php if($current_student_scores[4]==1){echo("checked");}?> required><big>' Occasionally commits to shared documents, others sometimes needed to revise, debug, or fix their work.</big><br>
      <input type="radio"  name="Q5" value="2" <?php if($current_student_scores[4]==2){echo("checked");}?> required><big>' Often commits to shared documents, others occasionally needed to revise, debug, or fix their work.</big><br>
      <input type="radio"  name="Q5" value="3" <?php if($current_student_scores[4]==3){echo("checked");}?> required><big>' Frequently commits to shared documents, others rarely need to revise, debug, or fix their work.</big><br>
    </fieldset>

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


</div>

<!-- Footer -->
<footer id="footer" class="w3-container w3-theme-dark w3-padding-16">
  <h3>Acknowledgements</h3>
  <p>Powered by <a href="https://www.w3schools.com/w3css/default.asp" target="_blank">w3.css</a></p>
  <p> <a  class=" w3-theme-light" target="_blank"></a></p>
</footer>

</body>
</html>
