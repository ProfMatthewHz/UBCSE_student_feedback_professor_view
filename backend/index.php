  <?php
  // JWT //
  require_once __DIR__ . '/../vendor/autoload.php';
  use Firebase\JWT\JWT;
  use Firebase\JWT\Key;


//error logging
error_reporting(-1); // reports all errors
ini_set("display_errors", "1"); // shows all errors
ini_set("log_errors", 1);
ini_set("error_log", "~/php-error.log");


require "lib/constants.php";
require "lib/database.php";
require "lib/studentQueries.php";
$con = connectToDatabase();

// initialize a response array
  $response = [];


if(!empty($_SERVER['uid'])) {
  $email = $_SERVER['uid']."@buffalo.edu";
  $id_and_name = getStudentInfoFromEmail($con, $email);
  if (empty($id)) {
     http_response_code(400);
     $response['error'] = 'Double-check UBIT: ' . $email . ' is not in the system.';

      header('Content-Type: application/json');
      echo json_encode($response);
      exit();
  }
  // get rid of this session shit, replace it with a token.
//  session_regenerate_id();
//	$_SESSION['student_id'] = $id_and_name[0];
//	header("Location: ".SITE_HOME."/courseSelect.php");
//	exit();

    // replacement //
    $secretKey = "myAppJWTKey2024!#$";
    $payload = [
        "data" => [
            "student_id" => $id_and_name[0] // Custom data
        ]
    ];
    $jwt_studentId = JWT::encode($payload, $secretKey, 'HS256');

    // sending this shit as a cookie //
    http_response_code(200);
    setcookie('student_id', $jwt_studentId);
    header("Location: ".SITE_HOME."/courseSelect.php");
    exit();


} else {
  http_response_code(400);
  $response['error'] = "Error connecting to shibboleth. Let Matthew know since this should not happen.";
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}
?>