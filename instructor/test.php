<?php 

require_once "../lib/database.php";
require_once "../lib/constants.php";
require_once 'lib/courseQueries.php';


$con = connectToDatabase();

$testResults = getInstructorTermCourses($con, 1, 2, 2024);

print_r($testResults);

?>