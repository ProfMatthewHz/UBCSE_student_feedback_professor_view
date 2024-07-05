<?php 

require_once "../lib/database.php";
require_once "../lib/constants.php";
require_once 'lib/courseQueries.php';


$con = connectToDatabase();


$testArray= [

    'testOne' =>  getInstructorTerms($con, 55502, 4, 2023),
    //testOne should print one record. 
    'testTwo' => getInstructorTerms($con, 1, 4, 2024),
    // testTwo should print two records 
    'testThree' => getInstructorTerms($con, 15742, 4, 2023),
    //test three should print two records
    'testFour' => getInstructorTerms($con, 25025, 4, 2023),
    // test four should print No terms found for instructor
    'testFive' => getInstructorTerms($con, 1, 2, 2024),
    // test five should print 1 record
    'testSix' => getInstructorTerms($con,50243486,1,2021)
    // test six should print 2 records
];




echo "<pre>";
print_r($testArray);
echo "</pre>";

?>

