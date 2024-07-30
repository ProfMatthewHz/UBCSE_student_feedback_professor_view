<?php

//error logging
error_reporting(-1); // reports all errors
ini_set("display_errors", "1"); // shows all errors
ini_set("log_errors", 1);
ini_set("error_log", "~/php-error.log");

// start the session variable
session_start();

// bring in required code
require_once "../lib/database.php";
require_once "../lib/constants.php";
require_once "../lib/infoClasses.php";
require_once "lib/courseQueries.php";


// query information about the requester
$con = connectToDatabase();

// try to get information about the instructor who made this request by checking the session token and redirecting if invalid
$instructor = new InstructorInfo();
$instructor->check_session($con, 0);


// store information about courses as array of array
$courses = array();

// get information about the courses taught by this instructor
$result = getAllCourses($con, $instructor->id);

while ($row = $result->fetch_assoc()) {
  $course_info = array();
  $course_info['code'] = $row['code'];
  $course_info['name'] = $row['name'];
  $course_info['semester'] = SEMESTER_MAP_REVERSE[$row['semester']];
  $course_info['year'] = $row['year'];
  $course_info['id'] = $row['id'];
  array_push($courses, $course_info);
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <link rel="stylesheet" type="text/css" href="../styles/styles.css">
    <title>Courses :: UB CSE Peer Evaluation System</title>
</head>
<body>
<header>
    <div class="w3-container">
          <img src="../images/logo_UB.png" class="header-img" alt="UB Logo">
          <h1 class="header-text">UB CSE Peer Evaluation System</h1>
    </div>
    <div class="w3-bar w3-blue w3-mobile w3-border-blue">
      <a href="surveys.php" class="w3-bar-item w3-button w3-mobile w3-border-right w3-border-left w3-border-white">Surveys</a>
      <a href="courses.php" class="w3-bar-item w3-button w3-mobile w3-border-right w3-border-white">Courses</a>
      <form action="logout.php" method ="post">
        <input type="hidden" name="csrf-token" value="<?php echo $instructor->csrf_token; ?>" />
        <input class="w3-bar-item w3-button w3-mobile w3-right w3-border-right w3-border-left w3-border-white" type="submit" value="Logout"></form>
      <span class="w3-bar-item w3-mobile w3-right">Welcome, <?php echo htmlspecialchars($instructor->name); ?></span>
    </div>
</header>
<div class="main-content">
    <div class="w3-container w3-center">
        <h2>Courses</h2>
    </div>

    <?php

      // echo the success message if any
      if (isset($_SESSION['course-add']) and $_SESSION['course-add'])
      {
        echo '<div class="w3-container w3-center w3-green">' . $_SESSION['course-add'] . '</div><br />';
        $_SESSION['course-add'] = NULL;
      }

    ?>

    <table class="w3-table w3-mobile w3-centered" border=1 style="width:100%">
      <tr>
      <th>Code</th>
      <th>Name</th>
      <th>Semester</th>
      <th>Instructor</th>
      <!-- TODO: Removed option to modify a course's roster<th>Actions</th> -->
      </tr>
      <?php
        foreach ($courses as $course)
        {
          // TODO: Removed option to modify a course's roster
          echo '<tr><td>' . htmlspecialchars($course['code']) . '</td><td>' . htmlspecialchars($course['name']) . '</td><td>' . htmlspecialchars($course['semester']) . ' ' . htmlspecialchars($course['year']) . '</td><td>' . htmlspecialchars($instructor->name) . '</td></tr>';
        }
      ?>
    </table>

    <br />
<div class = "w3-center w3-mobile">
    <a href="addCourses.php"><button class="w3-button w3-green">+ Add Course</button></a>
</div>
</div>

</body>
</html>
