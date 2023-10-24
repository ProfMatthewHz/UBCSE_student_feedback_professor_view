--
-- Course and student fake data
--
INSERT INTO `courses` (`id`, `code`, `name`, `semester`, `year`) VALUES
(10101, '', 'Computer Science 1 ', 0, '2023'),
(10115, 'CSE 116', '', 2, '2024'),
(42356, 'CSE 442', 'Software Engineering Concepts ', 0, '2023');

INSERT INTO `instructors` (`id`, `name`, `email`, `session_expiration`, `csrf_token`) VALUES
(0, 'Matthew Hertz', 'mhertz@buffalo.edu', NULL, NULL),
(1, '', 'hartloff@buffalo.edu', NULL, NULL),
(2, 'Paul Dickson', '', NULL, NULL);

INSERT INTO `course_instructors` (`course_id`, `instructor_id`) VALUES
(10101, 0),
(10115, 1),
(42356, 2);

INSERT INTO `students` (`id`, `name`, `email`) VALUES
(50243400, 'Smitty Johnson', ''),
(50243479, 'Jim Jones', 'jjones@buffalo.edu'),
(50243480, 'John Eggert', 'jeggert@buffalo.edu');

INSERT INTO `enrollments` (`student_id`, `course_id`) VALUES
(50243400, 42356),
(50243479, 10101),
(50243480, 10115);


--
-- Rubric fake data
--

INSERT INTO `rubrics` (`id`, `description`) VALUES
(0, 'This is rubric Id # Zero'),
(1, ''),
(2, 'This is rubric id # three');

INSERT INTO `rubric_topics` (`id`, `rubric_id`, `question`, `question_response`) VALUES
(0, 0, 'Topic zero', 'multiple_choice'),
(1, 1, 'Empty topic (also testing with no id) meant to be id of 1', 'multiple_choice'),
(2, 2, '', 'multiple_choice');

INSERT INTO `rubric_scores` (`id`, `rubric_id`, `name`, `score`) VALUES
(0, 0, 'Jim Jones', 97),
(1, 1, 'Smitty JohnSon', 55),
(2, 2, 'John Eggert', 82);

INSERT INTO `rubric_responses` (`topic_id`, `rubric_score_id`, `response`) VALUES
(0, 0, 'Hello this was a decent rubric'),
(1, 1, 'Im smitty JohnSon and I approve of this rubric '),
(2, 2, '');


--
-- Survey & survey resopnses fake data
--

INSERT INTO `surveys` (`id`, `course_id`, `start_date`, `end_date`, `name`, `rubric_id`, `survey_type_id`) VALUES
(0, 10101, '2023-09-19 08:31:19', '2023-09-19 08:31:19', 'Survey #1', 0, 0),
(1, 10115, '2023-09-19 08:31:19', '2023-09-19 08:31:19', 'Survey #2', 1, 1),
(2, 42356, '2023-09-19 08:32:44', '2023-09-19 08:32:44', 'Survey #3', 2, 2);

INSERT INTO `reviews` (`id`, `survey_id`, `reviewer_id`, `team_id`, `reviewed_id`, `eval_weight`) VALUES
(0, 0, 50243480, 55, 50243480, 1),
(1, 1, 50243479, 67, 50243479, 1),
(2, 2, 50243400, 12, 50243400, 1);

INSERT INTO `evals` (`id`, `review_id`) VALUES
(0, 0),
(1, 1),
(2, 2);

INSERT INTO `freeforms` (`eval_id`, `topic_id`, `response`) VALUES
(0, 0, 'FreeForm One'),
(1, 1, 'FreeForm two'),
(2, 2, NULL);

INSERT INTO `scores` (`eval_id`, `topic_id`, `rubric_score_id`) VALUES
(0, 0, 0),
(1, 1, 1),
(2, 2, 2);

--
-- Survey tyoes fake data
--
INSERT INTO `survey_types` (`id`, `description`, `file_organization`, `display_multiplier`) VALUES
(1, 'Individual Reviewed by Individual', 'One row per review. Each row has 2 columns: email of the reviewer, email of the person being reviewed.', 0),
(2, 'Each Team Member Reviewed By Entire Team', 'One row per team. Each row contains the email addresses for all team members. Blank columns are ignored', 0),
(3, 'Each Team Member Reviewed by Entire Team + Manager', 'One row per team. Each row contains the email addresses for all team members with the manager email address listed last. Blank columns are ignored', 1),
(4, 'Single Individual Reviewed by Each Team Member', 'One row per individual being reviewed. Every row contains the email addresses of the reviewers and the person being reviewed. The person being reviewed MUST be in the final column in the row.', 0);


