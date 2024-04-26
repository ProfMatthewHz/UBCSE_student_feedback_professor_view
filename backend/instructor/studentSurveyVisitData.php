<?php
error_reporting(-1);
ini_set("display_errors", "1");
ini_set("log_errors", 1);
ini_set("error_log", "~/php-error.log");

session_start();
require_once "../lib/database.php";

// Ensure the database connection is established
$con = connectToDatabase();
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Ensure the user is logged in
if (!isset($_SESSION['id'])) {
    http_response_code(403);
    echo json_encode(["error" => "Forbidden: You must be logged in to access this page."]);
    exit();
}

// Validate CSRF token early in the script

// Validate CSRF token early in the script, this is for deployement 
// if (!isset($_SESSION['csrf_token'])) {
//     print ($csrf_token);
//     http_response_code(403);
//     echo json_encode(["error" => "CSRF token validation failed."]);
//     exit();
// }



$email = isset($_GET['email']) ? urldecode($_GET['email']) : null;
$survey_id = isset($_GET['survey_id']) ? (int) $_GET['survey_id'] : null;

if (empty($email) || empty($survey_id)) {
    http_response_code(400);
    echo json_encode(["error" => "Email and survey_id are required."]);
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid email format."]);
    exit();
}

// Lookup the student_id based on the decoded email
$student_id_query = "SELECT id FROM students WHERE email = ?";
$stmt = $con->prepare($student_id_query);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $student_id = $row['id'];
} else {
    http_response_code(404);
    echo json_encode(["error" => "No student found with the provided email."]);
    exit();
}

$current_timestamp = date('Y-m-d H:i:s');
$stmt_check_visit = $con->prepare("SELECT visit_count FROM student_visit_data WHERE reviewer_id = ? AND survey_id = ?");
$stmt_check_visit->bind_param("ii", $student_id, $survey_id);
$stmt_check_visit->execute();
$result_check_visit = $stmt_check_visit->get_result();

if ($result_check_visit->num_rows > 0) {
    $row = $result_check_visit->fetch_assoc();
    $visit_count = $row['visit_count'] + 1;

    $stmt_update_visit = $con->prepare("UPDATE student_visit_data SET visit_count = ?, last_visit = ? WHERE reviewer_id = ? AND survey_id = ?");
    $stmt_update_visit->bind_param("isii", $visit_count, $current_timestamp, $student_id, $survey_id);
    $stmt_update_visit->execute();
} else {
    $visit_count = 1;

    $stmt_insert_visit = $con->prepare("INSERT INTO student_visit_data (reviewer_id, survey_id, visit_count, last_visit) VALUES (?, ?, ?, ?)");
    $stmt_insert_visit->bind_param("iiis", $student_id, $survey_id, $visit_count, $current_timestamp);
    $stmt_insert_visit->execute();
}

$response = [
    "student_id" => $student_id,
    "survey_id" => $survey_id,
    "count" => $visit_count,
    "message" => "Student visit data updated successfully."
];

mysqli_close($con);
header('Content-Type: application/json');
echo json_encode($response);
?>