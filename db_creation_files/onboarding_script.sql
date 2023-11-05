-- faculty table
-- each row defines a single instructor who could use this system
CREATE TABLE `instructors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` TEXT NOT NULL,
  `email` VARCHAR(20) NOT NULL,
  `session_expiration` INT, -- future expansion
  `csrf_token` VARCHAR(255),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `csrf_token` (`csrf_token`)
) ENGINE=InnoDB;


-- courses table
-- each row defines a specific course that uses this system
CREATE TABLE `courses` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `code` text NOT NULL,
 `name` text NOT NULL,
 `semester` tinyint NOT NULL,
 `year` year NOT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB;


-- course_instructors table
-- each row defines an instructor of a course; this normalization allows a course to include multiple instructors 
CREATE TABLE `course_instructors` (
 `course_id` int(11) NOT NULL,
 `instructor_id` int(11) NOT NULL,
 PRIMARY KEY (`course_id`,`instructor_id`),
 KEY `course_instructors_course_idx` (`course_id`),
 KEY `course_instructors_instructor_idx` (`instructor_id`),
 CONSTRAINT `course_instructors_course_constraint` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
 CONSTRAINT `course_instructors_instructor_constraint` FOREIGN KEY (`instructor_id`) REFERENCES `instructors` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB;


-- students TABLE
-- each row is a distinct student who has been added to this system. Each student must only appear once EVEN IF they are registered in multiple classes
CREATE TABLE `students` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `name` text NOT NULL,
 `email` varchar(20) NOT NULL,
 PRIMARY KEY (`id`),
 UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB;

-- enrollments TABLE
-- each row is a distinct enrollment of a student in a course within this system. Each student may appear in a course at most once.
CREATE TABLE `enrollments` (
 `student_id` int(11) NOT NULL,
 `course_id` int(11) NOT NULL,
  PRIMARY KEY (`student_id`,`course_id`),
  KEY `enrollments_student_idx` (`student_id`),
  KEY `enrollments_course_idx` (`course_id`),
  CONSTRAINT `enrollments_student_constraint` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `enrollments_course_constraint` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB;


-- rubrics TABLE
-- each row represents a single rubric that we can use
CREATE TABLE `rubrics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `description` varchar(500) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

-- survey_types TABLE
-- each row represents a type of survey organization that is used by this system. By requiring that all survey types
-- be defined in this table, we can ensure that the backend, frontend, and database remain in sync.
CREATE TABLE `survey_types` (
  `id` tinyint NOT NULL AUTO_INCREMENT,
  `description` text NOT NULL,
  `file_organization` text NOT NULL,
  `display_multiplier` tinyint NOT NULL,
   PRIMARY KEY (`id`)
) ENGINE=InnoDB;

-- surveys TABLE
-- each row represents a use of this system for a course. Students must only be able to submit evaluations between
-- the start date and end date listed
CREATE TABLE `surveys` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `course_id` int(11) NOT NULL,
 `start_date` datetime NOT NULL,
 `end_date` datetime NOT NULL,
 `name` VARCHAR(30) NOT NULL,
 `rubric_id` int(11) NOT NULL,
 `survey_type_id` tinyint NOT NULL,
 PRIMARY KEY (`id`),
 KEY `surveys_course_idx` (`course_id`),
 KEY `surveys_rubric_idx` (`rubric_id`),
 CONSTRAINT `surveys_course_constraint` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
 CONSTRAINT `surveys_rubric_constraint` FOREIGN KEY (`rubric_id`) REFERENCES `rubrics` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
 CONSTRAINT `surveys_survey_type_constraint` FOREIGN KEY (`survey_type_id`) REFERENCES `survey_types` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB;



-- reviews TABLE
-- each row represents a set of evaluations that will need to be completed
CREATE TABLE `reviews` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `survey_id` int(11) NOT NULL,
 `reviewer_id` int(11) NOT NULL,
 `team_id` int(11) NOT NULL,
 `reviewed_id` int(11) NOT NULL,
 `eval_weight` int(11) NOT NULL DEFAULT 1,
 PRIMARY KEY (`id`),
 KEY `reviews_survey_idx` (`survey_id`),
 KEY `reviews_reviewer_idx` (`reviewer_id`),
 KEY `reviews_reviewed_idx` (`reviewed_id`),
 CONSTRAINT `reviews_survey_constraint` FOREIGN KEY (`survey_id`) REFERENCES `surveys` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
 CONSTRAINT `reviews_reviewer_constraint` FOREIGN KEY (`reviewer_id`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
 CONSTRAINT `reviews_reviewed_constraint` FOREIGN KEY (`reviewed_id`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB;


-- evals table
-- each row defines a single peer- or self-evaluation. Rows are added/updated only as students complete their evaluations
CREATE TABLE `evals` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `review_id` int(11) NOT NULL,
 PRIMARY KEY (`id`),
 KEY `evals_review_idx` (`review_id`),
 CONSTRAINT `evals_review_constraint` FOREIGN KEY (`review_id`) REFERENCES `reviews` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB;


-- rubric_scores TABLE
-- each row represents a score levels from the multiple choice questions in this rubric
CREATE TABLE `rubric_scores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rubric_id` int(11) NOT NULL,
  `name` text NOT NULL,
  `score` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `rubric_scores_rubric_idx` (`rubric_id`),
  CONSTRAINT `rubric_scores_rubric_constraint` FOREIGN KEY (`rubric_id`) REFERENCES `rubrics` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB;


-- rubric_topics TABLE
-- each row represents a score levels from the multiple choice questions in this rubric
CREATE TABLE `rubric_topics` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `rubric_id` int(11) NOT NULL,
 `question` text NOT NULL, 
 `question_response` enum('multiple_choice','text') NOT NULL DEFAULT 'multiple_choice',
 PRIMARY KEY (`id`),
 KEY `rubric_topics_rubric_idx` (`rubric_id`),
 CONSTRAINT `rubric_topics_rubric_constraint` FOREIGN KEY (`rubric_id`) REFERENCES `rubrics` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB;

-- rubric_responses TABLE
-- each row contains the text of the response at a specific score level on the specific topic
CREATE TABLE `rubric_responses` (
  `topic_id` int(11) NOT NULL,
  `rubric_score_id` int(11) NOT NULL,
  `response` text NOT NULL,
  PRIMARY KEY (`topic_id`,`rubric_score_id`),
  KEY `rubric_responses_topic_idx` (`topic_id`),
  KEY `rubric_responses_rubric_score_idx` (`rubric_score_id`),
  CONSTRAINT `rubric_responses_topic_constraint` FOREIGN KEY (`topic_id`) REFERENCES `rubric_topics` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `rubric_responses_rubric_score_constraint` FOREIGN KEY (`rubric_score_id`) REFERENCES `rubric_scores` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB;

-- scores TABLE
-- each row represents the score entered by a student on an evaluation in response to a topic
CREATE TABLE `scores` (
 `eval_id` int(11) NOT NULL,
 `topic_id` int(11) NOT NULL,
 `rubric_score_id` int(11) NOT NULL,
 PRIMARY KEY (`eval_id`,`topic_id`),
 KEY `scores_eval_idx` (`eval_id`),
 KEY `scores_topic_idx` (`topic_id`),
 KEY `scores_rubric_score_idx` (`rubric_score_id`),
 CONSTRAINT `scores_eval_constraint` FOREIGN KEY (`eval_id`) REFERENCES `evals` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
 CONSTRAINT `scores_topic_constraint` FOREIGN KEY (`topic_id`) REFERENCES `rubric_topics` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
 CONSTRAINT `scores_rubric_score_constraint` FOREIGN KEY (`rubric_score_id`) REFERENCES `rubric_scores` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- freeforms TABLE
-- each row represents the freeform response entered on an evaluation in response to a freeform question
CREATE TABLE `freeforms` (
 `eval_id` int(11) NOT NULL,
 `topic_id` int(11) NOT NULL,
 `response` TEXT DEFAULT NULL,
 PRIMARY KEY (`eval_id`,`topic_id`),
 KEY `freeforms_eval_idx` (`eval_id`),
 KEY `freeforms_topic_idx` (`topic_id`),
 CONSTRAINT `freeforms_eval_constraint` FOREIGN KEY (`eval_id`) REFERENCES `evals` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
 CONSTRAINT `freeforms_topic_constraint` FOREIGN KEY (`topic_id`) REFERENCES `rubric_topics` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;
