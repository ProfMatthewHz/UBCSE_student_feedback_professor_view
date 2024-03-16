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
    echo json_encode(array("error" => "Forbidden: You must be logged in to access this page."));
    exit();
}

$json_data = file_get_contents('php://input');
$request_data = json_decode($json_data, true);

if ($request_data === null) {
    http_response_code(400);
    echo json_encode(array("error" => "Invalid JSON data in request body."));
    exit();
}

$reviewer_id = $request_data['reviewer_id'];
$survey_id = $request_data['survey_id'];

// Check if a related review exists
$sql_review_exists = "SELECT 1 FROM reviews WHERE reviewer_id = ? AND survey_id = ?";
$stmt_review_exists = $con->prepare($sql_review_exists);
$stmt_review_exists->bind_param("ii", $reviewer_id, $survey_id);
$stmt_review_exists->execute();
$result_review_exists = $stmt_review_exists->get_result();

if ($result_review_exists->num_rows > 0) {
    // Proceed if the review exists
    $current_timestamp = date('Y-m-d H:i:s');

    // Check if a record exists in student_visit_data for the given reviewer_id and survey_id
    $sql_check_visit = "SELECT visit_count FROM student_visit_data WHERE reviewer_id = ? AND survey_id = ?";
    $stmt_check_visit = $con->prepare($sql_check_visit);
    $stmt_check_visit->bind_param("ii", $reviewer_id, $survey_id);
    $stmt_check_visit->execute();
    $result_check_visit = $stmt_check_visit->get_result();

    if ($result_check_visit->num_rows > 0) {
        // If exists, update visit_count and last_visit
        $row = $result_check_visit->fetch_assoc();
        $visit_count = $row['visit_count'] + 1;

        $sql_update_visit = "UPDATE student_visit_data SET visit_count = ?, last_visit = ? WHERE reviewer_id = ? AND survey_id = ?";
        $stmt_update_visit = $con->prepare($sql_update_visit);
        $stmt_update_visit->bind_param("isii", $visit_count, $current_timestamp, $reviewer_id, $survey_id);
        $stmt_update_visit->execute();
    } else {
        // If not, insert new record with visit_count = 1
        $visit_count = 1;

        $sql_insert_visit = "INSERT INTO student_visit_data (reviewer_id, survey_id, visit_count, last_visit) VALUES (?, ?, ?, ?)";
        $stmt_insert_visit = $con->prepare($sql_insert_visit);
        $stmt_insert_visit->bind_param("iiis", $reviewer_id, $survey_id, $visit_count, $current_timestamp);
        $stmt_insert_visit->execute();
    }

    $response = [
        "reviewer_id" => $reviewer_id,
        "survey_id" => $survey_id,
        "count" => $visit_count,
        "message" => "Student visit data updated successfully."
    ];
} else {
    // Review does not exist
    $response = ["error" => "No related review found for given reviewer_id and survey_id."];
}

mysqli_close($con);
header('Content-Type: application/json');
echo json_encode($response);
?>
