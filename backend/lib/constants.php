<?php
# Local development mode
define("FRONTEND_HOME","http://localhost:3000/teamwork/instructor");
define("FRONTEND_SERVER","http://localhost:3000");

# Local testing mode
#define("FRONTEND_HOME","http://localhost/StudentSurvey/react-frontend/build/");
#define("FRONTEND_SERVER","http://localhost/");

# Student testing mode
#define("FRONTEND_HOME","http://www-student.cse.buffalo.edu/CSE442-542/2023-Fall/cse-302a/StudentSurvey/react-frontend/build/");
#define("FRONTEND_SERVER","http://www-student.cse.buffalo.edu/");

# Production mode
#define("FRONTEND_HOME","https://cse.buffalo.edu/teamwork/react-frontend/build/");
#define("FRONTEND_SERVER","https://cse.buffalo.edu");

define("ADMIN_EMAIL", "mhertz@buffalo.edu");
define("INIT_AUTH_TOKEN_EXPIRATION_SECONDS", 60 * 60);
define("INIT_AUTH_COOKIE_NAME", "init-auth");
define("SEMESTER_MAP", array('winter' => 1, 'spring' => 2, 'summer' => 3, 'fall' => 4));
define("SEMESTER_MAP_REVERSE", array(1 => 'Winter', 2 => 'Spring', 3 => 'Summer', 4=> 'Fall'));
define("PAIRING_TYPE_MAP", array("PAIRS" => 1, "TEAM_SELF" => 2, "TEAM_SELF_MANAGER" => 3, "PM" => 4, "TEAM_NO_SELF" => 5, "COLLECTIVE" => 6));
define("MONTH_MAP_SEMESTER", array(1 => 1, 2 => 2, 3 => 2, 4 => 2, 5 => 2, 6 => 3, 7 => 3, 8 => 3, 9 => 4, 10 => 4, 11 => 4, 12 => 4));
define("NO_SCORE_MARKER", "--");
define("MC_QUESTION_TYPE", "multiple_choice");
define("FREEFORM_QUESTION_TYPE", "text");
define("JSON_ENCODE_OPTIONS", JSON_HEX_TAG|JSON_HEX_AMP|JSON_UNESCAPED_UNICODE);
?>
