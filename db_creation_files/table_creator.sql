-- course table
-- each row defines a specific course that uses this system
CREATE TABLE `course` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `code` text NOT NULL,
 `name` text NOT NULL,
 `semester` tinyint NOT NULL,
 `year` tinyint NOT NULL,
 `instructor_id` int(11) NOT NULL,
 PRIMARY KEY (`id`),
 KEY `course_instructor_idx` (`instructor_id`),
 CONSTRAINT `course_instructor_constraint` FOREIGN KEY (`instructor_id`) REFERENCES `instructors` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB;


-- surveys TABLE
-- each row represents a use of this system for a course. Students must only be able to submit evaluations between
-- the start date and end date listed
CREATE TABLE `surveys` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `course_id` int(11) NOT NULL,
 `start_date` datetime NOT NULL,
 `expiration_date` datetime NOT NULL,
 `name` CHARACTER SET utf8 NOT NULL,
 `rubric_id` int(11) NOT NULL,
 PRIMARY KEY (`id`),
 UNIQUE KEY `id` (`id`),
 KEY `survey_course_idx` (`course_id`),
 CONSTRAINT `survey_course_constraint` FOREIGN KEY (`course_id`) REFERENCES `course` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB;


-- students TABLE
-- each row is a distinct student who has been added to this system. Each student must only appear once EVEN IF they are registered in multiple classes
CREATE TABLE `students` (
 `student_id` int(11) NOT NULL AUTO_INCREMENT,
 `email` varchar(20) NOT NULL,
 `name` text NOT NULL,
 PRIMARY KEY (`student_id`),
 UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB;

-- eval table
-- each row defines a single peer- or self-evaluation. Rows are added/updated only as students complete their evaluations
CREATE TABLE `evals` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `reviewers_id` int(11) NOT NULL,
 PRIMARY KEY (`id`),
 KEY `eval_reviewers_id` (`reviewers_id`),
 CONSTRAINT `eval_reviewers_id_constraint` FOREIGN KEY (`reviewers_id`) REFERENCES `reviewers` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB;

-- faculty table
-- each row defines a single instructor who could use this system
CREATE TABLE `instructors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` TEXT NOT NULL,
  `email` VARCHAR(20) NOT NULL,
  `init_auth_id` VARCHAR(255),
  `session_token` VARCHAR(255),
  `session_expiration` INT,
  `csrf_token` VARCHAR(255),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `init_auth` (`init_auth_id`),
  UNIQUE KEY `session_token` (`session_token`),
  UNIQUE KEY `csrf_token` (`csrf_token`)
) ENGINE=InnoDB;

-- reviewers TABLE
-- each row represents a single peer- or self-evalution that must be completed
-- before each evaluation, MHz uploads the files containing all of the pairings into this table
CREATE TABLE `reviewers` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `survey_id` int(11) NOT NULL,
 `reviewer_email` varchar(20) NOT NULL,
 `teammate_email` varchar(20) NOT NULL,
 PRIMARY KEY (`id`),
 KEY `reviewers_survey_id` (`survey_id`),
 KEY `reviewers_reviewer_constraint` (`reviewer_email`),
 KEY `reviewers_teammate_constraint` (`teammate_email`),
 CONSTRAINT `reviewers_survey_id_constraint` FOREIGN KEY (`survey_id`) REFERENCES `surveys` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
 CONSTRAINT `reviewers_reviewer_constraint` FOREIGN KEY (`reviewer_email`) REFERENCES `students` (`email`),
 CONSTRAINT `reviewers_teammate_constraint` FOREIGN KEY (`teammate_email`) REFERENCES `students` (`email`)
) ENGINE=InnoDB;

-- these tables were created so that we can allow faculty to tailor the questions & answers with each survey
CREATE TABLE `rubrics` (
  `id` int(11) NOT NULL,
  `description` varchar(500) NOT NULL,
  PRIMARY KEY (`id`);
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `rubric_responses` (
  `id` int(11) NOT NULL,
  `topic_id` int(11) NOT NULL,
  `score_id` int(11) NOT NULL,
  `response` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `rubric_scores` (
  `id` int(11) NOT NULL,
  `rubric_id` int(11) NOT NULL,
  `name` text NOT NULL,
  `score` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `rubric_topics` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `rubric_id` int(11) NOT NULL,
 `question` text NOT NULL,
 `question_response` enum('multiple_choice','text') NOT NULL DEFAULT 'multiple_choice',
 PRIMARY KEY (`id`),
 KEY `fk_rubric` (`rubric_id`),
 CONSTRAINT `rubric_topics_rubrics_id_constraint` FOREIGN KEY (`rubric_id`) REFERENCES `rubrics` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=latin1

-- this is the table which holds all for multiple choice question responses
CREATE TABLE `scores2` (
 `eval_id` int(11) NOT NULL,
 `topic_id` int(11) NOT NULL,
 `score_id` int(11) DEFAULT NULL,
 PRIMARY KEY (`eval_id`,`topic_id`),
 KEY `fk_eval` (`eval_id`),
 KEY `scores2_topic_id_constraint` (`topic_id`),
 KEY `scores2_score_id_constraint` (`score_id`),
 CONSTRAINT `scores2_eval_id_constraint` FOREIGN KEY (`eval_id`) REFERENCES `evals` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
 CONSTRAINT `scores2_score_id_constraint` FOREIGN KEY (`score_id`) REFERENCES `rubric_scores` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
 CONSTRAINT `scores2_topic_id_constraint` FOREIGN KEY (`topic_id`) REFERENCES `rubric_topics` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1

-- this is the table which holds all freeform responses
CREATE TABLE `freeform` (
 `eval_id` int(11) NOT NULL,
 `topic_id` int(11) NOT NULL,
 `response` TEXT DEFAULT NULL,
 PRIMARY KEY (`eval_id`,`topic_id`),
 KEY `fk_eval` (`eval_id`),
 KEY `freeform_topic_id_constraint` (`topic_id`),
 CONSTRAINT `freeform_eval_id_constraint` FOREIGN KEY (`eval_id`) REFERENCES `evals` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
 CONSTRAINT `freeform_topic_id_constraint` FOREIGN KEY (`topic_id`) REFERENCES `rubric_topics` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1