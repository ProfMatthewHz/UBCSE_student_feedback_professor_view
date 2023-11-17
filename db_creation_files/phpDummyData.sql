INSERT INTO `courses` (`id`, `code`, `name`, `semester`, `year`) VALUES
(10101, '', 'Computer Science 1 ', 0, '2023'),
(10115, 'CSE 116', 'Computer Science 2', 4, '2023'),
(10116, 'CSE 312', 'Introduction to Web Applications', 2, '2023'),
(10117, 'CSE 404', 'Software Project Managment', 4, '2023'),
(10118, 'CSE199', 'UB Seminar', 4, '2023'),
(10119, 'CSE 115', 'Computer Science 1', 4, '2023'),
(10120, 'CSE 305', 'Programming Languages', 4, '2023'),
(10121, 'CSE250', 'Data Structures', 2, '2023'),
(10122, 'CSE331', 'Algorithms and Complexity', 1, '2022'),
(10123, 'CSE 365', 'Computer Security', 1, '2022'),
(10124, 'CSE 220', 'Systems Programming', 3, '2022'),
(10125, 'CSE 241', 'Digital Systems', 3, '2022'),
(10126, 'CSE 410', 'Special Topics', 2, '2023'),
(10127, 'CSE 306', 'Software Quality in Practice', 3, '2022'),
(10128, 'CSE 234', 'Intro to Blockchain', 3, '2022'),
(42356, 'CSE 442', 'Software Engineering Concepts ', 0, '2023'),
(42357, '', '', 3, '2022'),
(42359, '', '', 3, '2022');

INSERT INTO `course_instructors` (`course_id`, `instructor_id`) VALUES
(10101, 0),
(10115, 1),
(10116, 1),
(10117, 1),
(10118, 1),
(10119, 1),
(10120, 1),
(10121, 1),
(10122, 1),
(10123, 1),
(10124, 1),
(10125, 1),
(10126, 1),
(10127, 1),
(10128, 1),
(42356, 2);

INSERT INTO `enrollments` (`student_id`, `course_id`) VALUES
(50243400, 42356),
(50243479, 10101),
(50243480, 10115);

INSERT INTO `evals` (`id`, `review_id`) VALUES
(5, 0),
(1, 1);

INSERT INTO `freeforms` (`eval_id`, `topic_id`, `response`) VALUES
(0, 0, 'FreeForm One'),
(1, 1, 'FreeForm two'),
(2, 2, NULL);

INSERT INTO `instructors` (`id`, `name`, `email`, `session_expiration`, `csrf_token`) VALUES
(1, '', 'hartloff@buffalo.edu', NULL, '16c861073c150ec14bc65bd63d5e11c1fc76b309e37a1dd034f22875fb4f96f8'),
(2, 'Paul Dickson', '', NULL, NULL),
(4, 'Matthew Hertz', 'mhertz@buffalo.edu', NULL, NULL);

INSERT INTO `reviews` (`id`, `survey_id`, `reviewer_id`, `team_id`, `reviewed_id`, `eval_weight`) VALUES
(1, 1, 50243479, 67, 50243479, 1),
(2, 2, 50243400, 12, 50243400, 1),
(5, 0, 50243480, 55, 50243480, 1);

INSERT INTO `rubrics` (`id`, `description`) VALUES
(1, 'This is rubric id # one'),
(2, 'This is rubric id # two'),
(3, 'This is rubric id # three'),
(4, 'This is rubric id # four'),
(5, 'This is rubric id # five'),
(6, 'rubric test');

INSERT INTO `rubric_responses` (`topic_id`, `rubric_score_id`, `response`) VALUES
(0, 0, 'Hello this was a decent rubric'),
(1, 1, 'Im smitty JohnSon and I approve of this rubric '),
(2, 2, '');
(6, 6, 'mastermind'),
(6, 7, 'amateur techincal skills'),
(6, 8, 'no technical skills'),
(7, 6, 'loved by all'),
(7, 7, 'has some friends'),
(7, 8, 'hated');


INSERT INTO `rubric_scores` (`id`, `rubric_id`, `name`, `score`) VALUES
(1, 1, 'Smitty JohnSon', 55),
(2, 2, 'John Eggert', 82),
(5, 0, 'Jim Jones', 97),
(6, 6, 'best', 10),
(7, 6, 'ok', 5),
(8, 6, 'bad', 1);

INSERT INTO `rubric_topics` (`id`, `rubric_id`, `question`, `question_response`) VALUES
(1, 1, 'Empty topic (also testing with no id) meant to be id of 1', 'multiple_choice'),
(2, 2, '', 'multiple_choice'),
(5, 0, 'Topic zero', 'multiple_choice'),
(6, 6, 'technical', 'multiple_choice'),
(7, 6, 'charistmatic', 'multiple_choice');

INSERT INTO `scores` (`eval_id`, `topic_id`, `rubric_score_id`) VALUES
(0, 0, 0),
(1, 1, 1),
(2, 2, 2);

INSERT INTO `students` (`id`, `name`, `email`) VALUES
(50243400, 'Smitty Johnson', ''),
(50243479, 'Jim Jones', 'jjones@buffalo.edu'),
(50243480, 'John Eggert', 'jeggert@buffalo.edu'),
(50243481, 'Student One', 's1@buffalo.edu'),
(50243482, 'Student Two', 's2@buffalo.edu'),
(50243483, 'Student Three', 's3@buffalo.edu'),
(50243484, 'Student Four', 's4@buffalo.edu'),
(50243485, 'Student Five', 's5@buffalo.edu'),
(50243486, 'Student Six', 's6@buffalo.edu'),
(50243487, 'Student Seven', 's7@buffalo.edu'),
(50243488, 'Student Eight', 's8@buffalo.edu'),
(50243489, 'Student Nine', 's9@buffalo.edu'),
(50243490, 'Student Ten', 's10@buffalo.edu'),
(50243491, 'Student Eleven', 's11@buffalo.edu'),
(50243492, 'Student Manager One', 'manager1@buffalo.edu'),
(50243493, 'Team One Member One', 't1m1@buffalo.edu'),
(50243494, 'Team One Member Two', 't1m2@buffalo.edu'),
(50243495, 'Team One Member Three', 't1m3@buffalo.edu'),
(50243496, 'Team One Member Four', 't1m4@buffalo.edu'),
(50243497, 'Student Manager Two', 'manager2@buffalo.edu'),
(50243498, 'Team Two Member One', 't2m1@buffalo.edu'),
(50243499, 'Team Two Member Two', 't2m2@buffalo.edu'),
(50243500, 'Team Two Member Three', 't2m3@buffalo.edu');

INSERT INTO `surveys` (`id`, `course_id`, `start_date`, `end_date`, `name`, `rubric_id`, `survey_type_id`) VALUES
(1, 10115, '2023-09-19 08:31:19', '2023-09-19 08:31:19', 'Survey #2', 1, 1),
(2, 42356, '2023-09-19 08:32:44', '2023-09-19 08:32:44', 'Survey #3', 2, 2),
(5, 10115, '2023-09-19 08:31:19', '2023-09-19 08:31:19', 'Survey #1', 0, 1),
(6, 10115, '2023-09-19 08:31:19', '2023-09-19 08:31:19', 'Survey #2', 1, 1),
(7, 10115, '2023-10-26 22:39:52', '2023-10-27 22:39:52', 'Survey #4', 1, 1),
(8, 10115, '2023-10-26 22:41:41', '2023-10-27 22:41:41', 'Survey #5', 1, 1),
(9, 10115, '2023-09-19 08:32:44', '2023-09-19 08:32:44', 'Survey #3', 2, 2),
(10, 10116, '2023-10-27 22:46:46', '2023-10-27 22:46:46', 'Dummy Name 2', 1, 1),
(11, 10116, '2023-10-27 22:44:38', '2023-10-27 22:44:38', 'Dummy Name 1', 1, 1),
(12, 10119, '2023-10-27 00:37:18', '2023-10-28 00:37:18', 'Dummy Name 1', 1, 1),
(13, 10119, '2023-10-27 00:37:57', '2023-10-28 00:37:57', 'Dummy Name 2', 1, 1),
(14, 10120, '2023-10-27 00:39:53', '2023-10-28 00:39:53', 'Dummy Name 1', 1, 1),
(15, 10120, '2023-10-27 00:40:37', '2023-10-28 00:40:37', 'Dummy Name 2', 1, 1),
(16, 10120, '2023-10-27 00:40:59', '2023-10-28 00:40:59', 'Dummy Name 3', 1, 1),
(17, 10120, '2023-10-27 00:41:25', '2023-10-28 00:41:25', 'Dummy Name 4', 1, 1),
(18, 10117, '2023-10-27 00:42:35', '2023-10-28 00:42:35', 'Dummy Name 1', 1, 1),
(19, 10117, '2023-10-27 00:43:04', '2023-10-28 00:43:04', 'Dummy Name 2', 1, 1),
(20, 10118, '2023-10-27 00:43:43', '2023-10-28 00:43:43', 'A Very Very Big Dummy Name 1', 1, 1),
(21, 10116, '2023-10-27 00:47:03', '2023-10-28 00:47:03', 'Dummy Name 1', 1, 1),
(22, 10116, '2023-10-27 00:47:25', '2023-10-28 00:47:25', 'Dummy Name 2', 1, 1),
(23, 10123, '2023-10-27 01:58:06', '2023-10-28 01:58:06', 'Dummy Name 1', 1, 1),
(24, 10123, '2023-10-27 02:05:33', '2023-10-28 02:05:33', 'Dummy Name 2', 1, 1),
(25, 10123, '2023-10-27 02:06:08', '2023-10-28 02:06:08', 'Dummy Name 3', 1, 1),
(26, 10101, '2023-09-19 08:31:19', '2023-09-19 08:31:19', 'Survey #1', 0, 0),
(27, 10122, '2023-10-27 04:25:09', '2023-10-28 04:25:09', 'Dummy Name 1', 1, 1),
(28, 10121, '2023-10-27 04:38:21', '2023-10-28 04:38:21', 'Dummy Name 1', 1, 1),
(29, 10121, '2023-10-27 04:38:21', '2023-10-28 04:38:21', 'Dummy Name 2', 1, 1),
(30, 10121, '2023-10-27 04:38:21', '2023-10-28 04:38:21', 'Dummy Name 3', 1, 1),
(31, 10121, '2023-10-27 04:38:21', '2023-10-28 04:38:21', 'Dummy Name 4', 1, 1),
(32, 10121, '2023-10-27 04:38:21', '2023-10-28 04:38:21', 'Dummy Name 5', 1, 1),
(33, 10127, '2023-10-27 04:38:21', '2023-10-28 04:38:21', 'Dummy Name 1', 1, 1),
(34, 10127, '2023-10-27 04:38:21', '2023-10-28 04:38:21', 'Dummy Name 2', 1, 1),
(35, 10127, '2023-10-27 04:38:21', '2023-10-28 04:38:21', 'Dummy Name 3', 1, 1),
(36, 10128, '2023-10-27 04:38:21', '2023-10-28 04:38:21', 'Dummy Name 1', 1, 1),
(37, 10128, '2023-10-27 04:38:21', '2023-10-28 04:38:21', 'Dummy Name 2', 1, 1),
(38, 10128, '2023-10-27 04:38:21', '2023-10-28 04:38:21', 'Dummy Name 3', 1, 1),
(39, 10122, '2023-10-27 04:46:05', '2023-10-28 04:46:05', 'Dummy Name 2', 1, 1);
(40, 10118, '2023-11-08 04:38:21', '2023-12-01 04:38:21', 'Active Survey 1', 1, 1),
(41, 10118, '2023-11-10 04:46:05', '2023-11-28 04:46:05', 'Active Survey 2', 1, 1);

INSERT INTO `survey_types` (`id`, `description`, `file_organization`, `display_multiplier`) VALUES
(1, 'Individual Reviewed by Individual', 'One row per review. Each row has 2 columns: email of the reviewer, email of the person being reviewed.', 0),
(2, 'Each Team Member Reviewed By Entire Team', 'One row per team. Each row contains the email addresses for all team members. Blank columns are ignored', 0),
(3, 'Each Team Member Reviewed by Entire Team + Manager', 'One row per team. Each row contains the email addresses for all team members with the manager email address listed last. Blank columns are ignored', 1),
(4, 'Single Individual Reviewed by Each Team Member', 'One row per individual being reviewed. Every row contains the email addresses of the reviewers and the person being reviewed. The person being reviewed MUST be in the final column in the row.', 0);