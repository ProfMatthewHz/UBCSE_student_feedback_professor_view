<?php
define("SITE_HOME","http://cse.buffalo.edu/teamwork/");
define("OTP_EXPIRATION_SECONDS", 60 * 15);
define("SESSIONS_SALT", "session-salt");
define("PBKDF2_ITERS", 50000);
define("TOKEN_SIZE", 32);
define("INIT_AUTH_TOKEN_EXPIRATION_SECONDS", 60 * 60);
define("INIT_AUTH_COOKIE_NAME", "init-auth");
define("SESSION_COOKIE_NAME", "session-token");
define("SESSION_TOKEN_EXPIRATION_SECONDS", 60 * 60 * 12);
define("SEMESTER_MAP", array('winter' => 1, 'spring' => 2, 'summer' => 3, 'fall' => 4));
define("SEMESTER_MAP_REVERSE", array(1 => 'Winter', 2=> 'Spring', 3 => 'Summer', 4=> 'Fall'));
define("NO_SCORE_MARKER", "--");
?>
