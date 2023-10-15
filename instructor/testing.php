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
print_r($testArray);
echo "<pre>";

//define("SEMESTER_MAP_REVERSE", array(1 => 'Winter', 2=> 'Spring', 3 => 'Summer', 4=> 'Fall'));


// first test for instructorData and also testing all thre functions 
// further testing needed.
$testInstructorData = instructorData($con,1,2,2024,2024); 
//instructor id = 1 = hartloff
//semester = 2 = winter 
//current semester = 2 = winter
//year = 2024
//course id = 10115 = CSE
//current year = 2024
//nstructorData($con, $instructor_id,$semester,$currentSemester,$year,$course_id,$currentYear);


//print_r($testInstructorData);
// current would be 4 2023
//- 1 winter
//- 2 spring 
//- 3 summer 
//- 4 fall  

?>

