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

$errorMsg = array();
$criteria = array();

// Verify we have already defined the rubric basics
if (!isset($_SESSION["rubric"])) {
  http_response_code(302);
  header("Location: ".INSTRUCTOR_HOME."rubricAdd.php");
  exit();
}
// Check if we are revising a previous submission
if (($_SERVER['REQUEST_METHOD'] != 'POST') && isset($_SESSION["confirm"])) {
  $criteria = $_SESSION["confirm"]["topics"];
} else if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  // make sure minimum set of values exist
  if (!isset($_POST['criterion1-question'])) {
    http_response_code(400);
    echo "Bad Request: Missing parmeters.";
    exit();
  }
  // check CSRF token
  $csrf_token = getCSRFToken($con, $instructor_id);
  if (!hash_equals($csrf_token, $_POST['csrf-token'])) {
    http_response_code(403);
    echo "Forbidden: Incorrect parameters.";
    exit();
  }

  // Process data so that it is available in a manner that parallels how rubric queries prepares a rubric.
  $crit_num = 1;
  $crit_id = "criterion".$crit_num;
  while (key_exists($crit_id.'-question', $_POST)) {
    $crit_data = array();
    
    $crit_data["question"] = trim($_POST[$crit_id.'-question']);
    if (empty($crit_data["question"])) {
      $errorMsg[$crit_id.'-question'] = "Each criterion needs a description";
    }
    // Translate the posted type to the values we actually use
    if (empty($_POST[$crit_id.'-type'])) {
      $crit_data["type"] = MC_QUESTION_TYPE;
      // When this is a multiple choice question, record each of the different responses
      $crit_data["responses"] = array();
      foreach ($_SESSION["rubric"]["levels"]["names"] as $level_name => $text) {
        $crit_data["responses"][$level_name] = check_level_response($crit_id, $level_name, $text, $errorMsg);
      }
    } else {
      $crit_data["type"] = FREEFORM_QUESTION_TYPE;
    }
    $criteria[] = $crit_data;
    $crit_num = $crit_num + 1;
    $crit_id = "criterion".$crit_num;
  }
  for ($i = 1; $i < $crit_num; $i++) {
    $trait = $criteria[$i-1]["question"];
    if (!empty($trait)) {
      for ($j = 1; $j < $crit_num; $j++) {
        if ( ($i != $j) && ($trait == $criteria[$j-1]["question"]) ) {
          $errorMsg["criterion".$i.'-question'] = "Each criterion needs UNIQUE description";
        }
      }
    }
  }
  if (count($errorMsg) == 0) {
    // Prepare the rubric for cofirmation
    $_SESSION["confirm"] = array();

    // Prepare the score data for confirmation
    $_SESSION["confirm"]["scores"] = array();
    foreach ($_SESSION["rubric"]["levels"]["names"] as $level => $name) {
      $_SESSION["confirm"]["scores"][$level] = array("name" => $name, "score" => $_SESSION["rubric"]["levels"]["values"][$level]);
    }

    // Prepare the topics & their reponses for confirmation
    $_SESSION["confirm"]["topics"] = $criteria;
    http_response_code(302);
    header("Location: ".INSTRUCTOR_HOME."rubricConfirm.php");
    exit();
  }
}

// Avoid problems in the verification screen from double-submitting a rubric
unset($_SESSION["confirm"]);
$csrf_token = createCSRFToken($con, $instructor_id);
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
    retVal.innerHTML = '<div class="col text-start align-top"><span id="criterion' + num + '-num" style="font-size:small;color:DarkGrey">Criterion #' + num + ':</span></div><div class="col ms-auto"><button type="button" class="btn btn-outline-danger btn-sm" onclick="removeCriterion(this)">-Remove Criterion</button></div>';
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
  function makeCritCheckBoxRow(name) {
    let realName = name + "-type";
    let labId = name + "-type-lab";
    let retVal = document.createElement("div");
    retVal.className = "row mx-1 justify-content-start";
    retVal.innerHTML = '<div class="col-md-auto"><input type="checkbox" id="'+realName+'"class="form-check-input" name="'+realName+'" onclick="showHideLevels(this)"><label id="'+labId+'" class="form-check-label" for="'+realName+'">Use freeform response</label></div>';
    return retVal;
  }
  function makeCritLevelRow(name) {
    let retVal = document.createElement("div");
    retVal.className = "row pt-1 mx-1 mb-3 align-items-center";
    retVal.id = name + "-levels";
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
  function showHideLevels(button) {
    let criterion = button.parentElement.parentElement.parentElement;
    let critNum = Number(criterion.id.substring(9));
    let levels = document.getElementById("criterion"+critNum+"-levels");
    let requireChoices;
    if (button.checked) {
      levels.style.display = "none";
      requireChoices = false;
    } else {
      levels.style.display = null;
      requireChoices = true;
    }
    const keys = <?php echo $level_keys_for_js ?>;
    for (let key of keys) {
        let questionInp = document.getElementById("criterion" + critNum + "-"+key);
        let questionLab = document.getElementById("criterion" + critNum + "-"+key+"-lab");
        questionInp.required = requireChoices;
        questionLab.required = requireChoices;
      }
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
    let checkBoxRow = makeCritCheckBoxRow(criterion.id);
    criterion.appendChild(checkBoxRow);
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
      let checkboxInp = document.getElementById("criterion" + i + "-type");
      let checkboxLab = document.getElementById("criterion" + i + "-type-lab");
      checkboxLab.id = "criterion" + prev + "-type-lab";
      checkboxLab.for = "criterion" + prev + "-type";
      checkboxInp.id = "criterion" + prev + "-type";
      checkboxInp.name = checkboxLab.id;
      let levelsDiv = document.getElementById("criterion" + i + "-levels");
      levelsDic.id = "criterion" + prev + "-levels";
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
        if (!empty($criterion["question"])) {
          echo 'document.getElementById("criterion'.$crit_num.'-question").value="'.$criterion["question"].'";';
        }
        if (isset($errorMsg['criterion'.$crit_num.'-question'])) {
          echo 'document.getElementById("criterion'.$crit_num.'-question").classList.add("is-invalid");';
          echo 'document.getElementById("criterion'.$crit_num.'-q-lab").innerHTML = "'.$errorMsg['criterion'.$crit_num.'-question'].'";';
        }
        if ($criterion["type"] == MC_QUESTION_TYPE) {
          foreach ($_SESSION["rubric"]["levels"]["names"] as $level_name => $text) {
            if (!empty($criterion["responses"][$level_name])) {
              echo 'document.getElementById("criterion'.$crit_num.'-'.$level_name.'").value="'.$criterion["responses"][$level_name].'";';
            }
            if (isset($errorMsg['criterion'.$crit_num.'-'.$level_name])) {
              echo 'document.getElementById("criterion'.$crit_num.'-'.$level_name.'").classList.add("is-invalid");';
              echo 'document.getElementById("criterion'.$crit_num.'-'.$level_name.'-lab").innerHTML = "'.$errorMsg['criterion'.$crit_num.'-'.$level_name].'";';
            }
          }
        } else {
          echo 'document.getElementById("criterion'.$crit_num.'-type").checked = true;';
          echo 'showHideLevels(document.getElementById("criterion'.$crit_num.'-type"));';
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
      <input type="hidden" name="csrf-token" value="<?php echo $csrf_token; ?>"></input>
      <div class="row mx-1 mt-2">
        <div class="col">
          <button type="button" class="btn btn-outline-secondary" onclick="addCriterion()">+ Add Criterion</button>
        </div>
        <div class="col ms-auto">
          <input class="btn btn-success" type="submit" value="Verify Rubic"></input>
        </div>
      </div>
    </form>
    <hr>
    <div class="row mx-1 mt-2 justify-content-center">
        <div class="col-auto">
					<a href="surveys.php" class="btn btn-outline-info" role="button" aria-disabled="false">Return to Instructor Home</a>
        </div>
      </div>
</div>
  </div>
</main>
</body>
</html>