<?php 

require_once "../lib/database.php";
require_once "../lib/constants.php";
require_once 'lib/courseQueries.php';


$con = connectToDatabase();

$validData_testResults = getInstructorTermCourses($con, 1, 2, 2024);
$invalidData_testResults = getInstructorTermCourses($con, 1, 3, 2024);

echo "Valid Data Output: ";
print_r($validData_testResults);

echo "<br>";

echo "Invalid Data Output: ";
print_r($invalidData_testResults);

?>