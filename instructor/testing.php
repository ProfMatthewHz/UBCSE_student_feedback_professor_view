<?php 

require_once "../lib/database.php";
require_once "../lib/constants.php";
require_once 'lib/courseQueries.php';


$con = connectToDatabase();

//$testResults = getInstructorTermCourses($con, 1, 2, 2024);



$testArray= [

    'testOne' =>  getInstructorTerms($con, 55502, 4, 2023),
    //testOne should print one record. 
    'testTwo' => getInstructorTerms($con, 1, 4, 2024),
    // testTwo should print two records 
    'testThree' => getInstructorTerms($con, 15742, 4, 2023),
    //test three should print two records
    'testFour' => getInstructorTerms($con, 25025, 4, 2023),
    // test four should print No terms found for instructor
    'testFive' => getInstructorTerms($con, 1, 2, 2024)
    // test five should print 1 record
];



echo "<pre>";
//print_r($testArray);
echo "<pre>";




$testInstructorData = [
    // Should print one record for getInstructorTerms, 
    // One record for current Terms , 
    // One survey record
    'testOne' => instructorData($con,1,2,2024,10115),

    // Should print one record for getInstructorTerms,
    // No record for current Terms 
    // One survey record
    'testTwo' => instructorData($con,2,2,2006,42356),

    // Should print no record for getInstructorTerms,
    // One record for current Terms 
    // One survey record
    'testThree' => instructorData($con,0,1,2015,10101)


];





print_r($testInstructorData);
//- 1 winter
//- 2 spring 
//- 3 summer 
//- 4 fall  

?>

