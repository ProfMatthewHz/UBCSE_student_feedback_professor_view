<?php
error_reporting(-1);
ini_set("display_errors", "1");
ini_set("log_errors", 1);
ini_set("error_log", "~/php-error.log");

session_start();
require_once "../lib/database.php";
$con = connectToDatabase();

if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

if (!isset($_SESSION['id'])) {
    http_response_code(403);
    echo json_encode(["error" => "Forbidden: You must be logged in to access this page."]);
    exit();
}

$reviewer_id = isset($_GET['reviewer_id']) ? (int)$_GET['reviewer_id'] : null;
$survey_id = isset($_GET['survey_id']) ? (int)$_GET['survey_id'] : null;

if (empty($reviewer_id) || empty($survey_id)) {
    http_response_code(400);
    echo json_encode(["error" => "reviewer_id and survey_id are required."]);
    exit();
}

// Fetch survey name based on survey_id
$survey_name = "";
$stmt_survey_name = $con->prepare("SELECT name FROM surveys WHERE id = ?");
$stmt_survey_name->bind_param("i", $survey_id);
$stmt_survey_name->execute();
$result_survey_name = $stmt_survey_name->get_result();
if ($row = $result_survey_name->fetch_assoc()) {
    $survey_name = $row['name'];
} else {
    echo json_encode(["error" => "Survey not found with the provided survey_id."]);
    exit();
}

// Check and update/insert into student_visit_data
$current_timestamp = date('Y-m-d H:i:s');
$stmt_check_visit = $con->prepare("SELECT visit_count FROM student_visit_data WHERE reviewer_id = ? AND survey_id = ?");
$stmt_check_visit->bind_param("ii", $reviewer_id, $survey_id);
$stmt_check_visit->execute();
$result_check_visit = $stmt_check_visit->get_result();

if ($result_check_visit->num_rows > 0) {
    // Entry exists, update it
    $row = $result_check_visit->fetch_assoc();
    $visit_count = $row['visit_count'] + 1;

    $stmt_update_visit = $con->prepare("UPDATE student_visit_data SET visit_count = ?, last_visit = ? WHERE reviewer_id = ? AND survey_id = ?");
    $stmt_update_visit->bind_param("isii", $visit_count, $current_timestamp, $reviewer_id, $survey_id);
    $stmt_update_visit->execute();
} else {
    // No entry exists, insert a new one
    $visit_count = 1;

    $stmt_insert_visit = $con->prepare("INSERT INTO student_visit_data (reviewer_id, survey_id, visit_count, last_visit) VALUES (?, ?, ?, ?)");
    $stmt_insert_visit->bind_param("iiis", $reviewer_id, $survey_id, $visit_count, $current_timestamp);
    $stmt_insert_visit->execute();
}

$response = [
    "reviewer_id" => $reviewer_id,
    "survey_id" => $survey_id,
    "count" => $visit_count,
    "survey_name" => $survey_name,
    "message" => "Student visit data updated successfully."
];

mysqli_close($con);
header('Content-Type: application/json');
echo json_encode($response);
?>
