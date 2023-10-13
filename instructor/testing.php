<?php 

require_once "../lib/database.php";
require_once "../lib/constants.php";
require_once 'lib/courseQueries.php';


$con = connectToDatabase();

//$testResults = getInstructorTermCourses($con, 1, 2, 2024);



$testArray = [

    'testOne' =>  getInstructorTerms($con, 55502, 4, 2023),
    //testOne should print one record. 
    'testTwo' => getInstructorTerms($con, 1, 4, 2024),
    // testTwo should print one record 
    'testThree' => getInstructorTerms($con, 15742, 4, 2023),
    //test three should print two records
    'testFour' => getInstructorTerms($con, 25025, 4, 2023)
    // test four should print No terms found for instructor
];


echo "<pre>";
print_r($testArray);
echo "<pre>";

// current would be 4 2023
//- 1 winter
//- 2 spring 
//- 3 summer 
//- 4 fall  

?>

