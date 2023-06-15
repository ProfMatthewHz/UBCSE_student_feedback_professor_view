<?php

function create_levels_array($scores) {
	$ret_val = array("names" => array(), "values" => array());
	$max_level = count($scores) - 1;
	$cur_level = 0;
	foreach (array_values($scores) as $score) {
		if ($cur_level == 0) {
			$ret_val["names"]["level5"] = $score["name"];
			$ret_val["values"]["level5"] = $score["score"];
		} else if ($cur_level == $max_level) {
			$ret_val["names"]["level1"] = $score["name"];
			$ret_val["values"]["level1"] = $score["score"];
		} else if ($cur_level == 1) {
			if (($max_level == 3) || ($max_level == 4)) {
				$ret_val["names"]["level4"] = $score["name"];
				$ret_val["values"]["level4"] = $score["score"];
			} else {
				$ret_val["names"]["level3"] = $score["name"];
				$ret_val["values"]["level3"] = $score["score"];
			}
		} else if ($cur_level == 2) {
			if ($max_level == 3) {
				$ret_val["names"]["level2"] = $score["name"];
				$ret_val["values"]["level2"] = $score["score"];
			} else {
				$ret_val["names"]["level3"] = $score["name"];
				$ret_val["values"]["level3"] = $score["score"];
			}
		} else if ($cur_level == 3) {
			$ret_val["names"]["level2"] = $score["name"];
			$ret_val["values"]["level2"] = $score["score"];
		}
		$cur_level = $cur_level + 1;
	}
	return $ret_val;
}

function create_topics_array($topics) {
	$ret_val = array();
	foreach ($topics as $topic) {
		$topic_data = array();
		$topic_data["question"] = $topic["question"];
		$topic_data["type"] = $topic["type"];
		$topic_data["responses"] = array();
		$max_level = count($topic["responses"]) - 1;
		$cur_level = 0;
		foreach (array_values($topic["responses"]) as $response) {
			if ($cur_level == 0) {
				$topic_data["responses"]["level5"] = $response;
			} else if ($cur_level == $max_level) {
				$topic_data["responses"]["level1"] = $response;
			} else if ($cur_level == 1) {
				if (($max_level == 3) || ($max_level == 4)) {
					$topic_data["responses"]["level4"] = $response;
				} else {
					$topic_data["responses"]["level3"] = $response;
				}
			} else if ($cur_level == 2) {
				if ($max_level == 3) {
					$topic_data["responses"]["level2"] = $response;
				} else {
					$topic_data["responses"]["level3"] = $response;
				}
			} else if ($cur_level == 3) {
				$topic_data["responses"]["level2"] = $response;
			}
			$cur_level = $cur_level + 1;
		}
		$ret_val[] = $topic_data;
	}
	return $ret_val;
}

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
require_once "lib/instructorQueries.php";
require_once "lib/rubricQueries.php";

//query information about the requester
$con = connectToDatabase();

//try to get information about the instructor who made this request by checking the session token and redirecting if invalid
if (!isset($_SESSION['id'])) {
  http_response_code(403);
  echo "Forbidden: You must be logged in to access this page.";
  exit();
}
$instructor_id = $_SESSION['id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  // check CSRF token
	$csrf_token = getCSRFToken($con, $instructor_id);
  if (!hash_equals($csrf_token, $_POST['csrf-token'])) {
    http_response_code(403);
    echo "Forbidden: Incorrect parameters.";
    exit();
  }
	if (!isset($_SESSION["rubric_reviewed"])) {
    http_response_code(400);
    echo "Bad Request: Missing parmeters.";
    exit();
	}
	$rubric_id = $_SESSION["rubric_reviewed"];
	unset($_SESSION["rubric_reviewed"]);
	$rubric_name = getRubricName($con, $rubric_id);
	$_SESSION["rubric"] = array("name" => $rubric_name);

	$scores = getRubricScores($con, $rubric_id);
	$_SESSION["rubric"]["levels"] = create_levels_array($scores);

	$topics = getRubricTopics($con, $rubric_id);
	$topics_data = create_topics_array($topics);
	$_SESSION["confirm"] = array("topics" => $topics_data);
	http_response_code(302);
	header("Location: ".INSTRUCTOR_HOME."rubricAdd.php");
	exit();
}

$rubrics = getRubrics($con);
$csrf_token = createCSRFToken($con, $instructor_id);
// Just to be certain, we will unset the session variable that tracks the rubric we are currently reviewing
unset($_SESSION["rubric_reviewed"]);
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
			let duplicateBtn = document.getElementById("duplicate-button");
			duplicateBtn.setAttribute("aria-disabled", "false");
			duplicateBtn.classList.remove("disabled");
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
		<hr>
    <form class="mt-2 mx-1" id="duplicate-rubric" method="post">
		<input type="hidden" name="csrf-token" value="<?php echo $csrf_token; ?>"></input>
      <div class="row mx-1 mt-2 justify-content-center">
      <div class="col-auto">
      <input id='duplicate-button' class="btn btn-outline-secondary disabled" type="submit" aria-disabled="true" value="Duplicate Rubric"></input>
</div></div>
    </form>
    <hr>
		<div class="row mx-1 mt-2 justify-content-center">
        <div class="col-auto">
					<a href="surveys.php" class="btn btn-outline-info" role="button" aria-disabled="false">Return to Instructor Home</a>
        </div>
      </div>
	</div>
	</main>
</body>
</html>
