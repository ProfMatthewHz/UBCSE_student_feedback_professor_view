<?php
// This is pairingModePhotos.php file
// this is in backend/instructor 
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
// $instructor_id = $_SESSION['id'];

// Check if description parameter is provided via GET request
if (isset($_GET['description'])) {
    // Sanitize the description parameter
    $description = mysqli_real_escape_string($con, $_GET['description']);

    // Prepare and execute SQL query to fetch URL based on description
    $sql = "SELECT URL FROM survey_types WHERE description = '$description'";
    $result = mysqli_query($con, $sql);

    if ($result) {
        // Check if any row is returned
        if (mysqli_num_rows($result) > 0) {
            // Fetch the URL from the first row
            $row = mysqli_fetch_assoc($result);
            $url = $row['URL'];

            // Redirect to the image URL
            header("Location: $url");
            exit;
        } else {
            // No matching description found
            http_response_code(404);
            echo json_encode(array("error" => "Description not found in database."));
        }
    } else {
        // Error executing SQL query
        http_response_code(500);
        echo json_encode(array("error" => "Internal Server Error: Failed to execute database query."));
    }
} else {
    // Description parameter not provided
    http_response_code(400);
    echo json_encode(array("error" => "Bad Request: Description parameter is missing."));
}
?>
