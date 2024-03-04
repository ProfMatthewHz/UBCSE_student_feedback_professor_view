<?php
error_reporting(-1); // reports all errors
ini_set("display_errors", "1"); // shows all errors
ini_set("log_errors", 1);
ini_set("error_log", "~/php-error.log");

session_start();
require_once "../lib/database.php";
$con = connectToDatabase();

// Check if connection is successful
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

if (!isset($_SESSION['id'])) {
    http_response_code(403);
    echo json_encode(array("error" => "Forbidden: You must be logged in to access this page."));
    exit();
}
$instructor_id = $_SESSION['id'];

// Define the student ID and survey ID from GET parameters
$student_id_to_check = $_GET['student_id'];
$survey_id = $_GET['survey_id'];

// Perform the SQL query to check if the student exists
$sql = "SELECT id FROM students WHERE id = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $student_id_to_check);
$stmt->execute();
$result = $stmt->get_result();

// Check if query was successful
if ($result->num_rows > 0) {
    // Student exists
    $response = array();

    // Update visit_count and last_visit based on student_id and survey_id
    $current_timestamp = date('Y-m-d H:i:s');
    $sql_update = "UPDATE student_visit_data SET visit_count = visit_count + 1, last_visit = ? WHERE student_id = ? AND survey_id = ?";
    $stmt_update = $con->prepare($sql_update);
    $stmt_update->bind_param("sii", $current_timestamp, $student_id_to_check, $survey_id);
    $stmt_update->execute();
    $affected_rows = $stmt_update->affected_rows;

    if ($affected_rows > 0) {
        $response["message"] = "Visit count and last visit timestamp updated successfully.";
    } else {
        $response["error"] = "Failed to update visit count and last visit timestamp.";
    }
} else {
    // Student does not exist
    $response["error"] = "Student with ID $student_id_to_check does not exist in the database.";
}

// Close the connection
mysqli_close($con);

// Return response as JSON
header('Content-Type: application/json');
echo json_encode($response);
?>
