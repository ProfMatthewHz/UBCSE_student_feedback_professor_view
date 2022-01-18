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
require_once "../lib/infoClasses.php";
require_once "../lib/fileParse.php";
require_once "lib/rubricQueries.php";

//query information about the requester
$con = connectToDatabase();

//try to get information about the instructor who made this request by checking the session token and redirecting if invalid
$instructor = new InstructorInfo();
$instructor->check_session($con, 0);

$errorMsg = array();
$rubrics = selectRubrics($con);
?>
<!DOCTYPE HTML>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous"></script>
  <title>CSE Evaluation Survey System - Rubric Review</title>
	<script>
		function ajaxPostRequest(path, data, callback){
				let request = new XMLHttpRequest();
				request.onreadystatechange = function(){
						if (this.readyState===4&&this.status ===200){
								callback(this.response);
						}
				};
				request.open("POST", path);
				request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
				request.send(data);
		}
		function showTable(response) {
			let table_html = JSON.parse(response);
			document.getElementById("rubric-table").innerHTML = table_html;
		}
		function updateRubric() {
			let rubric_id = document.getElementById("rubric-select").value;
			ajaxPostRequest('lib/getRubricTable.php', "rubric="+rubric_id, showTable);
		}
	</script>
</head>
<body class="text-center">
<!-- Header -->
<main>
  <div class="container-fluid">
    <div class="row justify-content-md-center bg-primary mt-1 mx-1 rounded-pill">
      <div class="col-sm-auto">
        <h4 class="text-white display-1">UB CSE Evalution System<br>View Rubric</h4>
      </div>
    </div>
    <div class="row justify-content-md-center mt-5 mx-4">
      <div class="col-sm-auto">
        <div class="form-floating mb-3">
          <select id="rubric-select" class="form-select form-select-lg" onchange="updateRubric();">
            <option value="-1" disabled selected>Select Rubric to Review:</option>
						<?php
						foreach ($rubrics as $id=>$name) {
							echo '<option value="'.$id.'">'.$name.'</option>';
						}
						?>
					</select>
					<label for="rubric-select">Rubric to View:</label>
				</div>
     	</div>
  	</div>
		<div id="rubric-table" class="row pt-1 mx-1 align-items-center text-center border border-3 border-dark">
		<div class="col"><i>Selected rubric will appear here</i></div>
		</div>
	</div>
	</main>
</body>
</html>
