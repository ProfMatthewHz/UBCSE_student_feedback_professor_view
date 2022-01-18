<?php

function check_level_response($crit, $level_name, $text, &$errorMsg) {
  if (!isset($_POST[$crit."-".$level_name])) {
    http_response_code(403);
    echo "Forbidden: Incorrect parameters.";
    exit();
  }
  $ret_val = trim($_POST[$crit."-".$level_name]);
  if (empty($ret_val)) {
    $errorMsg[$crit.$level_name] = "Response for ".$text." cannot be empty";
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
require_once "../lib/infoClasses.php";
require_once "../lib/fileParse.php";
require_once "lib/rubricQueries.php";

//query information about the requester
$con = connectToDatabase();

//try to get information about the instructor who made this request by checking the session token and redirecting if invalid
$instructor = new InstructorInfo();
$instructor->check_session($con, 0);

$errorMsg = array();
$criteria = array();

// Verify we have already defined the rubric basics
if (!isset($_SESSION["rubric"])) {
  http_response_code(400);
  echo "Bad Request: Missing parmeters.";
  exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {

  // make sure minimum set of values exist
  if (!isset($_POST['criterion1-question'])) {
    http_response_code(400);
    echo "Bad Request: Missing parmeters.";
    exit();
  }
  // check CSRF token
  if (!hash_equals($instructor->csrf_token, $_POST['csrf-token'])) {
    http_response_code(403);
    echo "Forbidden: Incorrect parameters.";
    exit();
  }
  $crit_num = 1;
  $crit_id = "criterion".$crit_num;
  while (key_exists($crit_id.'-question', $_POST)) {
    $crit_data = array();
    
    $crit_data["topic"] = trim($_POST[$crit_id.'-question']);
    if (empty($crit_data["topic"])) {
      $errorMsg[$crit_id.'-question'] = "Each criterion needs a description";
    }
    foreach ($_SESSION["rubric"]["levels"]["names"] as $level_name => $text) {
      $crit_data[$level_name] = check_level_response($crit_id, $level_name, $text, $errorMsg);
    }
    $criteria[] = $crit_data;
    $crit_num = $crit_num + 1;
    $crit_id = "criterion".$crit_num;
  }
  for ($i = 1; $i < $crit_num; $i++) {
    $trait = $criteria[$i-1]["topic"];
    if (!empty($trait)) {
      for ($j = 1; $j < $crit_num; $j++) {
        if ( ($i != $j) && ($trait == $criteria[$j-1]["topic"]) ) {
          $errorMsg["criterion".$i.'-question'] = "Each criterion needs UNIQUE description";
        }
      }
    }
  }
  if (count($errorMsg) == 0) {
    // Upload the rubric

    // Add the rubric to the database and keep track of the id it was assigned. 
    $rubric_id = insertRubric($con, $_SESSION["rubric"]["name"]);

    // Now add the different scores/levels and keep track of each of them for later use
    $levels = count($_SESSION["rubric"]["levels"]["names"]);
    $level_ids = array();
    $level_ids["level1-name"] = insertRubricScore($con, $rubric_id, $_SESSION["rubric"]["levels"]["names"]["level1-name"], $_SESSION["rubric"]["levels"]["values"]["level1-value"]);
    if ($levels == 4 || $levels == 5) {
      $level_ids["level2-name"] = insertRubricScore($con, $rubric_id, $_SESSION["rubric"]["levels"]["names"]["level2-name"], $_SESSION["rubric"]["levels"]["values"]["level2-value"]);
    }
    if ($levels == 3 || $levels == 5) {
      $level_ids["level3-name"] = insertRubricScore($con, $rubric_id, $_SESSION["rubric"]["levels"]["names"]["level3-name"], $_SESSION["rubric"]["levels"]["values"]["level3-value"]);
    }
    if ($levels == 4 || $levels == 5) {
      $level_ids["level4-name"] = insertRubricScore($con, $rubric_id, $_SESSION["rubric"]["levels"]["names"]["level4-name"], $_SESSION["rubric"]["levels"]["values"]["level4-value"]);
    }
    $level_ids["level5-name"] = insertRubricScore($con, $rubric_id, $_SESSION["rubric"]["levels"]["names"]["level5-name"], $_SESSION["rubric"]["levels"]["values"]["level5-value"]);

    // Finally we insert the name of each criterion as a rubric topic and all of its responses.
    foreach ($criteria as $crit_data) {
      $topic_id = insertRubricTopic($con, $rubric_id, $crit_data["topic"]);
      foreach ($level_ids as $key => $level_id) {
        insertRubricReponse($con, $topic_id, $level_id, $crit_data[$key]);
      }
    }

    // Finally, we insert each response to every question

    // And go back to the main page.
    unset($_SESSION["rubric"]);
    http_response_code(302);
    header("Location: ".INSTRUCTOR_HOME."surveys.php");
  }
}

$level_keys_for_js = json_encode(array_keys($_SESSION["rubric"]["levels"]["names"]));
$level_names_for_js =  json_encode(array_values($_SESSION["rubric"]["levels"]["names"]));
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous"></script>
  <title>CSE Evaluation Survey System - Add Criteria</title>
  <script>
  function makeCritTopRow(num) {
    let retVal = document.createElement("div");
    retVal.className = "row mx-1 mt-1";
    retVal.innerHTML = '<div class="col text-start align-top"><span id="criterion' + num + '-num" style="font-size:small;color:DarkGrey">Criterion #' + num + ':</span></div><div class="col ms-auto"><button type="button" class="btn btn-outline-danger btn-sm" onclick="removeCriterion(this)">-Remove Criterion</button></div>"';
    return retVal;
  }
  function makeCritNameRow(name) {
    let realName = name + "-question";
    let labId = name + "-q-lab";
    let retVal = document.createElement("div");
    retVal.className = "row mx-1 justify-content-start";
    retVal.innerHTML = '<div class="col-7"><div class="form-floating"><input type="text" id="'+realName+'" class="form-control" name="'+realName+'" required value=""><label id="'+labId+'" for="'+realName+'">Description of Trait:</label></div></div></div>';
    return retVal;
  }
  function makeCritLevelRow(name) {
    let retVal = document.createElement("div");
    retVal.className = "row pt-1 mx-1 mb-3 align-items-center";
    let endStr = '">';
    const keys = <?php echo $level_keys_for_js ?>;
    const names = <?php echo $level_names_for_js ?>;
    let htmlStr = "";
    for (let idx in keys) {
      loopId = name+'-'+keys[idx];
      htmlStr = htmlStr + '<div class="col' +endStr+'<div class="form-floating"><textarea id="'+loopId+'" class="form-control" name="'+loopId+'" required value=""></textarea>';
      htmlStr = htmlStr + '<label id="'+loopId+'-lab" for="'+loopId+'">Response for '+names[idx]+':</label></div></div>';
      // Update formatting so that all but first score use size correctly
      end_str = ' ms-auto">';
    }
    retVal.innerHTML = htmlStr;
    return retVal;
  }

  function addCriterion() {
    let criteriaDivs = document.querySelectorAll(".criterion");
    let criterionNum = criteriaDivs.length + 1;
    let criterion = document.createElement("div");
    criterion.id = "criterion" + criterionNum;
    criterion.className = "border-top border-bottom criterion";
    let topRow = makeCritTopRow(criterionNum)
    criterion.appendChild(topRow);
    let midRow = makeCritNameRow(criterion.id);
    criterion.appendChild(midRow);
    let lastRow = makeCritLevelRow(criterion.id);
    criterion.appendChild(lastRow);
    let criterionList = document.getElementById("crit-list");
    criterionList.appendChild(criterion);
  }
  function removeCriterion(button) {
    let criteriaDivs = document.querySelectorAll(".criterion");
    let criterionToRemove = button.parentElement.parentElement.parentElement;
    let removedNum = Number(criterionToRemove.id.substring(9));
    criterionToRemove.remove();
    const keys = <?php echo $level_keys_for_js ?>;
    for (let i = removedNum + 1; i <= criteriaDivs.length; i++) {
      const prev = i-1;
      let criterion = criteriaDivs[prev];
      criterion.id = "criterion" + prev;
      let numSpan = document.getElementById("criterion" + i + "-num");
      numSpan.innerHTML = 'Criterion #' + prev;
      numSpan.id = "criterion" + prev + "-num";
      let questionInp = document.getElementById("criterion" + i + "-question");
      let questionLab = document.getElementById("criterion" + i + "-q-lab");
      questionLab.id = "criterion" + prev + "-q-lab";
      questionLab.for = "criterion" + prev + "-question";
      questionInp.id = "criterion" + prev + "-question";
      questionInp.name = questionInp.id;
      for (let key of keys) {
        let questionInp = document.getElementById("criterion" + i + "-"+key);
        let questionLab = document.getElementById("criterion" + i + "-"+key+"-lab");
        questionLab.id = "criterion" + prev + "-" + key + "-lab";
        questionLab.for = "criterion" + prev + "-" + key;
        questionInp.id = "criterion" + prev + "-" + key;
        questionInp.name = questionInp.id;
      }
    }
  }
  function initialize() {
    <?php
    if (count($criteria) == 0) {
      echo 'addCriterion();';
    } else {
      $crit_num = 1;
      foreach ($criteria as $criterion) {
        echo 'addCriterion();';
        if (!empty($criterion["topic"])) {
          echo 'document.getElementById("criterion'.$crit_num.'-question").value="'.$criterion["topic"].'";';
        }
        if (isset($errorMsg['criterion'.$crit_num.'-question'])) {
          echo 'document.getElementById("criterion'.$crit_num.'-question").classList.add("is-invalid");';
          echo 'document.getElementById("criterion'.$crit_num.'-q-lab").innerHTML = "'.$errorMsg['criterion'.$crit_num.'-question'].'";';
        }
        foreach ($_SESSION["rubric"]["levels"]["names"] as $level_name => $text) {
          if (!empty($criterion[$level_name])) {
            echo 'document.getElementById("criterion'.$crit_num.'-'.$level_name.'").value="'.$criterion[$level_name].'";';
          }
          if (isset($errorMsg['criterion'.$crit_num.'-'.$level_name])) {
            echo 'document.getElementById("criterion'.$crit_num.'-'.$level_name.'").classList.add("is-invalid");';
            echo 'document.getElementById("criterion'.$crit_num.'-'.$level_name.'-lab").innerHTML = "'.$errorMsg['criterion'.$crit_num.'-'.$level_name].'";';
          }
        }
        $crit_num = $crit_num + 1;
      }
    }
    ?>
  }
  </script>
</head>
<body class="text-center" onload="initialize();">
<!-- Header -->
<main>
  <div class="container-fluid">
    <div class="row justify-content-md-center bg-primary mt-1 mx-1 rounded-pill">
      <div class="col-sm-auto text-center">
        <h4 class="text-white display-1">UB CSE Evalution System<br>Specify Rubric Criteria</h4>
      </div>
    </div>

    <div class="row justify-content-md-center mt-5 mx-1">
      <div class="col-sm-auto text-center">
        <h4><?php echo "Criteria for ".$_SESSION["rubric"]["name"]; ?></h4>
      </div>
    </div>

    <form class="mt-5 mx-1" id="define-rubric" method="post">
      <div id="crit-list">
      </div>
      <input type="hidden" name="csrf-token" value="<?php echo $instructor->csrf_token; ?>"></input>
      <div class="row mx-1 mt-2">
        <div class="col">
          <button type="button" class="btn btn-outline-secondary" onclick="addCriterion()">+ Add Criterion</button>
        </div>
        <div class="col ms-auto">
          <input class="btn btn-success" type="submit" value="Submit Rubic"></input>
        </div>
      </div>
    </form>
  </div>
</main>
</body>
</html>