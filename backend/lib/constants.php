<?php
# Local development mode
define("SITE_HOME","http://localhost/StudentSurvey/backend/");
define("INSTRUCTOR_HOME","http://localhost/StudentSurvey/backend/instructor/");
define("FRONTEND_HOME","http://localhost:3000/teamwork/instructor");

# Local testing mode
#define("SITE_HOME","http://localhost/StudentSurvey/backend/");
#define("INSTRUCTOR_HOME","http://localhost/StudentSurvey/backend/instructor/");
#define("FRONTEND_HOME","http://localhost/StudentSurvey/react-frontend/build/");

# Student testing mode
#define("SITE_HOME","https://www-student.cse.buffalo.edu/CSE442-542/2023-Fall/cse-302a/StudentSurvey/backend/");
#define("INSTRUCTOR_HOME","https://www-student.cse.buffalo.edu/CSE442-542/2023-Fall/cse-302a/StudentSurvey/backend/instructor/");
#define("FRONTEND_HOME","http://www-student.cse.buffalo.edu/CSE442-542/2023-Fall/cse-302a/StudentSurvey/react-frontend/build/");

# Production mode
#define("SITE_HOME","https://cse.buffalo.edu/teamwork/backend/");
#define("INSTRUCTOR_HOME","https://cse.buffalo.edu/teamwork/backend/instructor/");
#define("FRONTEND_HOME","https://cse.buffalo.edu/teamwork/react-frontend/build/");

define("SESSIONS_SALT", "session-salt");
define("PBKDF2_ITERS", 50000);
define("TOKEN_SIZE", 32);
define("INIT_AUTH_TOKEN_EXPIRATION_SECONDS", 60 * 60);
define("INIT_AUTH_COOKIE_NAME", "init-auth");
define("SESSION_COOKIE_NAME", "session-token");
define("SESSION_TOKEN_EXPIRATION_SECONDS", 60 * 60 * 12);
define("SEMESTER_MAP", array('winter' => 1, 'spring' => 2, 'summer' => 3, 'fall' => 4));
define("SEMESTER_MAP_REVERSE", array(1 => 'Winter', 2 => 'Spring', 3 => 'Summer', 4=> 'Fall'));
define("MONTH_MAP_SEMESTER", array(1 => 1, 2 => 2, 3 => 2, 4 => 2, 5 => 2, 6 => 3, 7 => 3, 8 => 3, 9 => 4, 10 => 4, 11 => 4, 12 => 4));
define("NO_SCORE_MARKER", "--");
define("MC_QUESTION_TYPE", "multiple_choice");
define("FREEFORM_QUESTION_TYPE", "text");
define("JSON_ENCODE_OPTIONS", JSON_HEX_TAG|JSON_HEX_AMP|JSON_UNESCAPED_UNICODE);
?>
