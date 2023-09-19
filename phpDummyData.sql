-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Sep 20, 2023 at 12:51 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `test`
--

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `code` text NOT NULL,
  `name` text NOT NULL,
  `semester` tinyint(4) NOT NULL,
  `year` year(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `code`, `name`, `semester`, `year`) VALUES
(10101, '', 'Computer Science 1 ', 0, '2023'),
(10115, 'CSE 116', '', 2, '2024'),
(42356, 'CSE 442', 'Software Engineering Concepts ', 0, '2023');

-- --------------------------------------------------------

--
-- Table structure for table `course_instructors`
--

CREATE TABLE `course_instructors` (
  `course_id` int(11) NOT NULL,
  `instructor_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `course_instructors`
--

INSERT INTO `course_instructors` (`course_id`, `instructor_id`) VALUES
(10101, 0),
(10115, 1),
(42356, 2);

-- --------------------------------------------------------

--
-- Table structure for table `enrollments`
--

CREATE TABLE `enrollments` (
  `student_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enrollments`
--

INSERT INTO `enrollments` (`student_id`, `course_id`) VALUES
(50243400, 42356),
(50243479, 10101),
(50243480, 10115);

-- --------------------------------------------------------

--
-- Table structure for table `evals`
--

CREATE TABLE `evals` (
  `id` int(11) NOT NULL,
  `review_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `evals`
--

INSERT INTO `evals` (`id`, `review_id`) VALUES
(0, 0),
(1, 1),
(2, 2);

-- --------------------------------------------------------

--
-- Table structure for table `freeforms`
--

CREATE TABLE `freeforms` (
  `eval_id` int(11) NOT NULL,
  `topic_id` int(11) NOT NULL,
  `response` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `freeforms`
--

INSERT INTO `freeforms` (`eval_id`, `topic_id`, `response`) VALUES
(0, 0, 'FreeForm One'),
(1, 1, 'FreeForm two'),
(2, 2, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `instructors`
--

CREATE TABLE `instructors` (
  `id` int(11) NOT NULL,
  `name` text NOT NULL,
  `email` varchar(20) NOT NULL,
  `session_expiration` int(11) DEFAULT NULL,
  `csrf_token` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `instructors`
--

INSERT INTO `instructors` (`id`, `name`, `email`, `session_expiration`, `csrf_token`) VALUES
(0, 'Matthew Hertz', 'mhertz@buffalo.edu', NULL, NULL),
(1, '', 'hartloff@buffalo.edu', NULL, NULL),
(2, 'Paul Dickson', '', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `survey_id` int(11) NOT NULL,
  `reviewer_id` int(11) NOT NULL,
  `team_id` int(11) NOT NULL,
  `reviewed_id` int(11) NOT NULL,
  `eval_weight` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `survey_id`, `reviewer_id`, `team_id`, `reviewed_id`, `eval_weight`) VALUES
(0, 0, 50243480, 55, 50243480, 1),
(1, 1, 50243479, 67, 50243479, 1),
(2, 2, 50243400, 12, 50243400, 1);

-- --------------------------------------------------------

--
-- Table structure for table `rubrics`
--

CREATE TABLE `rubrics` (
  `id` int(11) NOT NULL,
  `description` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rubrics`
--

INSERT INTO `rubrics` (`id`, `description`) VALUES
(0, 'This is rubric Id # Zero'),
(1, ''),
(2, 'This is rubric id # three');

-- --------------------------------------------------------

--
-- Table structure for table `rubric_responses`
--

CREATE TABLE `rubric_responses` (
  `topic_id` int(11) NOT NULL,
  `rubric_score_id` int(11) NOT NULL,
  `response` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rubric_responses`
--

INSERT INTO `rubric_responses` (`topic_id`, `rubric_score_id`, `response`) VALUES
(0, 0, 'Hello this was a decent rubric'),
(1, 1, 'Im smitty JohnSon and I approve of this rubric '),
(2, 2, '');

-- --------------------------------------------------------

--
-- Table structure for table `rubric_scores`
--

CREATE TABLE `rubric_scores` (
  `id` int(11) NOT NULL,
  `rubric_id` int(11) NOT NULL,
  `name` text NOT NULL,
  `score` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rubric_scores`
--

INSERT INTO `rubric_scores` (`id`, `rubric_id`, `name`, `score`) VALUES
(0, 0, 'Jim Jones', 97),
(1, 1, 'Smitty JohnSon', 55),
(2, 2, 'John Eggert', 82);

-- --------------------------------------------------------

--
-- Table structure for table `rubric_topics`
--

CREATE TABLE `rubric_topics` (
  `id` int(11) NOT NULL,
  `rubric_id` int(11) NOT NULL,
  `question` text NOT NULL,
  `question_response` enum('multiple_choice','text') NOT NULL DEFAULT 'multiple_choice'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rubric_topics`
--

INSERT INTO `rubric_topics` (`id`, `rubric_id`, `question`, `question_response`) VALUES
(0, 0, 'Topic zero', 'multiple_choice'),
(1, 1, 'Empty topic (also testing with no id) meant to be id of 1', 'multiple_choice'),
(2, 2, '', 'multiple_choice');

-- --------------------------------------------------------

--
-- Table structure for table `scores`
--

CREATE TABLE `scores` (
  `eval_id` int(11) NOT NULL,
  `topic_id` int(11) NOT NULL,
  `rubric_score_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `scores`
--

INSERT INTO `scores` (`eval_id`, `topic_id`, `rubric_score_id`) VALUES
(0, 0, 0),
(1, 1, 1),
(2, 2, 2);

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `name` text NOT NULL,
  `email` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `name`, `email`) VALUES
(50243400, 'Smitty Johnson', ''),
(50243479, 'Jim Jones', 'jjones@buffalo.edu'),
(50243480, 'John Eggert\r\n', 'jeggert@buffalo.edu');

-- --------------------------------------------------------

--
-- Table structure for table `surveys`
--

CREATE TABLE `surveys` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `name` varchar(30) NOT NULL,
  `rubric_id` int(11) NOT NULL,
  `survey_type_id` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `surveys`
--

INSERT INTO `surveys` (`id`, `course_id`, `start_date`, `end_date`, `name`, `rubric_id`, `survey_type_id`) VALUES
(0, 10101, '2023-09-19 08:31:19', '2023-09-19 08:31:19', 'Survey #1', 0, 0),
(1, 10115, '2023-09-19 08:31:19', '2023-09-19 08:31:19', 'Survey #2', 1, 1),
(2, 42356, '2023-09-19 08:32:44', '2023-09-19 08:32:44', 'Survey #3', 2, 2);

-- --------------------------------------------------------

--
-- Table structure for table `survey_types`
--

CREATE TABLE `survey_types` (
  `id` tinyint(4) NOT NULL,
  `description` text NOT NULL,
  `file_organization` text NOT NULL,
  `display_multiplier` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `survey_types`
--

INSERT INTO `survey_types` (`id`, `description`, `file_organization`, `display_multiplier`) VALUES
(0, 'This is survey type One', 'University at Buffalo', 0),
(1, '', 'University at Buffalo', 1),
(2, 'This is survey type 3', '', 2);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `course_instructors`
--
ALTER TABLE `course_instructors`
  ADD PRIMARY KEY (`course_id`,`instructor_id`),
  ADD KEY `course_instructors_course_idx` (`course_id`),
  ADD KEY `course_instructors_instructor_idx` (`instructor_id`);

--
-- Indexes for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`student_id`,`course_id`),
  ADD KEY `enrollments_student_idx` (`student_id`),
  ADD KEY `enrollments_course_idx` (`course_id`);

--
-- Indexes for table `evals`
--
ALTER TABLE `evals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `evals_review_idx` (`review_id`);

--
-- Indexes for table `freeforms`
--
ALTER TABLE `freeforms`
  ADD PRIMARY KEY (`eval_id`,`topic_id`),
  ADD KEY `freeforms_eval_idx` (`eval_id`),
  ADD KEY `freeforms_topic_idx` (`topic_id`);

--
-- Indexes for table `instructors`
--
ALTER TABLE `instructors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `csrf_token` (`csrf_token`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reviews_survey_idx` (`survey_id`),
  ADD KEY `reviews_reviewer_idx` (`reviewer_id`),
  ADD KEY `reviews_reviewed_idx` (`reviewed_id`);

--
-- Indexes for table `rubrics`
--
ALTER TABLE `rubrics`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `rubric_responses`
--
ALTER TABLE `rubric_responses`
  ADD PRIMARY KEY (`topic_id`,`rubric_score_id`),
  ADD KEY `rubric_responses_topic_idx` (`topic_id`),
  ADD KEY `rubric_responses_rubric_score_idx` (`rubric_score_id`);

--
-- Indexes for table `rubric_scores`
--
ALTER TABLE `rubric_scores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `rubric_scores_rubric_idx` (`rubric_id`);

--
-- Indexes for table `rubric_topics`
--
ALTER TABLE `rubric_topics`
  ADD PRIMARY KEY (`id`),
  ADD KEY `rubric_topics_rubric_idx` (`rubric_id`);

--
-- Indexes for table `scores`
--
ALTER TABLE `scores`
  ADD PRIMARY KEY (`eval_id`,`topic_id`),
  ADD KEY `scores_eval_idx` (`eval_id`),
  ADD KEY `scores_topic_idx` (`topic_id`),
  ADD KEY `scores_rubric_score_idx` (`rubric_score_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `surveys`
--
ALTER TABLE `surveys`
  ADD PRIMARY KEY (`id`),
  ADD KEY `surveys_course_idx` (`course_id`),
  ADD KEY `surveys_rubric_idx` (`rubric_id`),
  ADD KEY `surveys_survey_type_constraint` (`survey_type_id`);

--
-- Indexes for table `survey_types`
--
ALTER TABLE `survey_types`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=98083768;

--
-- AUTO_INCREMENT for table `evals`
--
ALTER TABLE `evals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `instructors`
--
ALTER TABLE `instructors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `rubrics`
--
ALTER TABLE `rubrics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `rubric_scores`
--
ALTER TABLE `rubric_scores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `rubric_topics`
--
ALTER TABLE `rubric_topics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50243481;

--
-- AUTO_INCREMENT for table `surveys`
--
ALTER TABLE `surveys`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `survey_types`
--
ALTER TABLE `survey_types`
  MODIFY `id` tinyint(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `course_instructors`
--
ALTER TABLE `course_instructors`
  ADD CONSTRAINT `course_instructors_course_constraint` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `course_instructors_instructor_constraint` FOREIGN KEY (`instructor_id`) REFERENCES `instructors` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD CONSTRAINT `enrollments_course_constraint` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `enrollments_student_constraint` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `evals`
--
ALTER TABLE `evals`
  ADD CONSTRAINT `evals_review_constraint` FOREIGN KEY (`review_id`) REFERENCES `reviews` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `freeforms`
--
ALTER TABLE `freeforms`
  ADD CONSTRAINT `freeforms_eval_constraint` FOREIGN KEY (`eval_id`) REFERENCES `evals` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `freeforms_topic_constraint` FOREIGN KEY (`topic_id`) REFERENCES `rubric_topics` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_reviewed_constraint` FOREIGN KEY (`reviewed_id`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `reviews_reviewer_constraint` FOREIGN KEY (`reviewer_id`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `reviews_survey_constraint` FOREIGN KEY (`survey_id`) REFERENCES `surveys` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `rubric_responses`
--
ALTER TABLE `rubric_responses`
  ADD CONSTRAINT `rubric_responses_rubric_score_constraint` FOREIGN KEY (`rubric_score_id`) REFERENCES `rubric_scores` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `rubric_responses_topic_constraint` FOREIGN KEY (`topic_id`) REFERENCES `rubric_topics` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `rubric_scores`
--
ALTER TABLE `rubric_scores`
  ADD CONSTRAINT `rubric_scores_rubric_constraint` FOREIGN KEY (`rubric_id`) REFERENCES `rubrics` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `rubric_topics`
--
ALTER TABLE `rubric_topics`
  ADD CONSTRAINT `rubric_topics_rubric_constraint` FOREIGN KEY (`rubric_id`) REFERENCES `rubrics` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `scores`
--
ALTER TABLE `scores`
  ADD CONSTRAINT `scores_eval_constraint` FOREIGN KEY (`eval_id`) REFERENCES `evals` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `scores_rubric_score_constraint` FOREIGN KEY (`rubric_score_id`) REFERENCES `rubric_scores` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `scores_topic_constraint` FOREIGN KEY (`topic_id`) REFERENCES `rubric_topics` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `surveys`
--
ALTER TABLE `surveys`
  ADD CONSTRAINT `surveys_course_constraint` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `surveys_rubric_constraint` FOREIGN KEY (`rubric_id`) REFERENCES `rubrics` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `surveys_survey_type_constraint` FOREIGN KEY (`survey_type_id`) REFERENCES `survey_types` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
