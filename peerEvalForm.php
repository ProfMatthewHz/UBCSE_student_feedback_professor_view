<!DOCTYPE HTML>

<?php
//error logging
error_reporting(-1); // reports all errors
ini_set("display_errors", "1"); // shows all errors
ini_set("log_errors", 1);
ini_set("error_log", "~/php-error.log");
session_start();
 if(!isset($_SESSION['id'])) {
    header("Location: https://www-student.cse.buffalo.edu/CSE442-542/2019-Summer/cse-442e/index.php");
    exit();
  }
$email = $_SESSION['email'];
$id = $_SESSION['id'];
$course = $_SESSION['course'];
//Change this to your connection info.
$DATABASE_HOST = 'tethys.cse.buffalo.edu';
$DATABASE_USER = 'jeh24';
$DATABASE_PASS = '50172309';
$DATABASE_NAME = 'cse442_542_2019_summer_teame_db';
 // Try and connect using the info above.
 mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
if ( mysqli_connect_errno() ) {
        // If there is an error with the connection, stop the script and display the error.
        die ('Failed to connect to MySQL: ' . mysqli_connect_error());
 }
 //fetch group number for current student
	$stmt = $con->prepare('SELECT group_number,submitted_scores FROM cse442 WHERE email=? AND course =?');
    $stmt->bind_param('ss', $email, $course);
    $stmt->execute();
	$stmt->bind_result($group_number, $old_scores_string);
	$stmt->store_result();
	$stmt->fetch();
	
	if($stmt->num_rows == 0){ //If student is not in selected class display an error.
	//TODO: make an error here
		exit();
	}
  //check if grades are already submitted
  if(!empty($old_scores_string)) {
    $old_scores = explode(":", $old_scores_string);
  }
	//get group members
	$group_members=array();
	$stmt = $con->prepare('SELECT email FROM cse442 WHERE group_number=? AND course =?');
    $stmt->bind_param('is', $group_number,$course);
    $stmt->execute();
	$stmt->bind_result($group_member);
	$stmt->store_result();
	while ($stmt->fetch()){
		array_push($group_members,$group_member);
	}
	$num_of_group_members =  count($group_members);
	if(!isset($_SESSION['group_member_number'])){
		$_SESSION['group_member_number'] = 0;
	}
	$current_group_member =  $group_members[$_SESSION['group_member_number']];
  //fetch name for student to be evaluated
   $stmt = $con->prepare('SELECT Name FROM cse442 WHERE email=?');
     $stmt->bind_param('s', $current_group_member);
     $stmt->execute();
   $stmt->bind_result($Name);
   $stmt->store_result();
   $stmt->fetch();
  if(isset($old_scores)) {
    foreach($old_scores as $old) {
      if(strpos($old, $current_group_member) !== false){
        $old_score = explode(",", $old);
      }
    }
  }
	//When submit button is pressed
	if ( !empty($_POST) ) {
		$current_student_feedback_string = "";
		if($_SESSION['group_member_number'] != 0){
			$current_student_feedback_string = ":";
		}
		$current_student_feedback_string = $current_student_feedback_string . $current_group_member . "," . strval($_POST['Q1']) . "," . strval($_POST['Q2']) . "," . strval($_POST['Q3'])
		 . "," . strval($_POST['Q4']) . "," . strval($_POST['Q5']);
		if(!isset($_SESSION['feedback_string'])){
			$_SESSION['feedback_string'] = $current_student_feedback_string;
		}
		else{
			$_SESSION['feedback_string'] = $_SESSION['feedback_string'] . $current_student_feedback_string;
		}
		//move to next student in group
		if($_SESSION['group_member_number'] < ($num_of_group_members - 1)){
			$_SESSION['group_member_number'] +=1;
			     header("Location: peerEvalForm.php"); //refresh page with next group member
				 exit();
		}
		else{//evaluated all students
			$stmt = $con->prepare('UPDATE cse442 SET submitted_scores = ? WHERE email=? AND course=?');
			$stmt->bind_param('sss',$_SESSION['feedback_string'], $email,$course);
			$stmt->execute();
			$indiviual_eval = explode(":",$_SESSION['feedback_string']);
			foreach($indiviual_eval as $indiv){//indiv is each student in the group that recieved feedback
				$indiv_explode = explode(",",$indiv);
				$recievers_email = $indiv_explode[0];
				$indiv_explode[0] = $email;
				$reciever_implode = implode(",",$indiv_explode);//reciever implode now has the evaluaters email.
				//access the recieved scores.
				$stmt = $con->prepare('SELECT recieved_scores FROM cse442 WHERE email=? AND course=?');
				$stmt->bind_param('ss', $recievers_email,$course);
				$stmt->execute();
				$stmt->bind_result($reciever_scores);
				$stmt->store_result();
				$stmt->fetch();
				if(strpos($reciever_scores,$email) !== false){ //if the student has already recieved a score from this student.
					$reciever_explode = explode(":",$reciever_scores);
					$reciever_scores_array = array();
					foreach($reciever_explode as $exploded){
						if(strpos($exploded, $email) !== false){
							array_push($reciever_scores_array ,$reciever_implode);
						}
						else{
							array_push($reciever_scores_array , $exploded);
						}
					}
					//var_dump($reciever_scores_array);
					$reciever_scores = implode(":",$reciever_scores_array);
					//echo $reciever_scores;
				}
				else{
					if($reciever_scores == ""){
						$reciever_scores = $reciever_implode;
					}
					else{
					$reciever_scores = $reciever_scores . ":" . $reciever_implode;
					}
				}
				$stmt = $con->prepare('UPDATE cse442 SET recieved_scores=? WHERE email=? AND course=?');
				$stmt->bind_param('sss',$reciever_scores, $recievers_email,$course);
				$stmt->execute();
			}
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
      <input type="radio"  name="Q1" value="0" <?php if(isset($old_score) && $old_score[1]==0){echo("checked");}?> required><big>' Does not willingly assume team roles, rarely completes assigned work.</big><br>
      <input type="radio"  name="Q1" value="1" <?php if(isset($old_score) && $old_score[1]==1){echo("checked");}?> required><big>' Usually accepts assigned team roles, occasionally completes assigned work.</big><br>
      <input type="radio"  name="Q1" value="2" <?php if(isset($old_score) && $old_score[1]==2){echo("checked");}?> required><big>' Accepts assigned team roles, mostly completes assigned work.</big><br>
      <input type="radio"  name="Q1" value="3" <?php if(isset($old_score) && $old_score[1]==3){echo("checked");}?> required><big>' Accepts all assigned team roles, always completes assigned work.</big><br>
    </fieldset>

    <hr>
    <h3>Question 2: Leadership</h3>
    <fieldset id="Question2" >
      <input type="radio"  name="Q2" value="0" <?php if(isset($old_score) && $old_score[2]==0){echo("checked");}?> required><big>' Rarely takes leadership role, does not collaborate, sometimes willing to assist teammates.</big><br>
      <input type="radio"  name="Q2" value="1" <?php if(isset($old_score) && $old_score[2]==1){echo("checked");}?> required><big>' Occasionally shows leadership, mostly collaborates, generally willin to assist teammates.</big><br>
      <input type="radio"  name="Q2" value="2" <?php if(isset($old_score) && $old_score[2]==2){echo("checked");}?> required><big>' Shows an ability to lead when necessary, willing to collaborate, willing to assist teammates.</big><br>
      <input type="radio"  name="Q2" value="3" <?php if(isset($old_score) && $old_score[2]==3){echo("checked");}?> required><big>' Takes leadership role, is a good collaborator, always willing to assist teammates.</big><br>
    </fieldset>

    <hr>
    <h3>Question 3: Participation</h3>
    <fieldset id="Question3" >
      <input type="radio"  name="Q3" value="0" <?php if(isset($old_score) && $old_score[3]==0){echo("checked");}?> required><big>' Often misses meetings, routinely unprepared for meetings, rarely participates in meetings and doesnt share ideas.</big><br>
      <input type="radio"  name="Q3" value="1" <?php if(isset($old_score) && $old_score[3]==1){echo("checked");}?> required><big>' Occasionally misses/ doesn't participate in meetings, somewhat unprepared for meetings, offers unclear/ unhelpful ideas.</big><br>
      <input type="radio"  name="Q3" value="2" <?php if(isset($old_score) && $old_score[3]==2){echo("checked");}?> required><big>' Attends and participates in most meetings, comes prepared, and offers useful ideas.</big><br>
      <input type="radio"  name="Q3" value="3" <?php if(isset($old_score) && $old_score[3]==3){echo("checked");}?> required><big>' Attends and participates in all meetings, comes prepared, and clearly expresses well-developed ideas.</big><br>
    </fieldset>

    <hr>
    <h3>Question 4: Professionalism</h3>
    <fieldset id="Question4" >
      <input type="radio"  name="Q4" value="0" <?php if(isset($old_score) && $old_score[4]==0){echo("checked");}?> required><big>' Often discourteous and/or openly critical of teammates, doesn't want to listen to alternative perspectives.</big><br>
      <input type="radio"  name="Q4" value="1" <?php if(isset($old_score) && $old_score[4]==1){echo("checked");}?> required><big>' Not always considerate or courteous towards teammates, usually appreciates teammates perspectives but often unwilling to consider them.</big><br>
      <input type="radio"  name="Q4" value="2" <?php if(isset($old_score) && $old_score[4]==2){echo("checked");}?> required><big>' Mostly courteous to teammates, values teammates' perspectives and often willing to consider them.</big><br>
      <input type="radio"  name="Q4" value="3" <?php if(isset($old_score) && $old_score[4]==3){echo("checked");}?> required><big>' Always courteous to teammates, values teammates' perspectives, knowledge, and experience, and always willing to consider them.</big><br>
    </fieldset>

    <hr>
    <h3>Question 5: Quality</h3>
    <fieldset id="Question5" >
      <input type="radio"  name="Q5" value="0" <?php if(isset($old_score) && $old_score[5]==0){echo("checked");}?> required><big>' Rarely commits to shared documents, others often required to revise, debug, or fix their work.</big><br>
      <input type="radio"  name="Q5" value="1" <?php if(isset($old_score) && $old_score[5]==1){echo("checked");}?> required><big>' Occasionally commits to shared documents, others sometimes needed to revise, debug, or fix their work.</big><br>
      <input type="radio"  name="Q5" value="2" <?php if(isset($old_score) && $old_score[5]==2){echo("checked");}?> required><big>' Often commits to shared documents, others occasionally needed to revise, debug, or fix their work.</big><br>
      <input type="radio"  name="Q5" value="3" <?php if(isset($old_score) && $old_score[5]==3){echo("checked");}?> required><big>' Frequently commits to shared documents, others rarely need to revise, debug, or fix their work.</big><br>
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