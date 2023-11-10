-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Nov 09, 2023 at 10:55 AM
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
(50243480, 10115),
(50243535, 10117),
(50243535, 10118),
(50243536, 10117),
(50243536, 10118),
(50243537, 10117),
(50243537, 10118),
(50243538, 10117),
(50243538, 10118);

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
(94, 39),
(95, 40),
(96, 41),
(97, 42),
(110, 43),
(111, 44),
(112, 45),
(113, 46),
(114, 47),
(115, 48),
(116, 49),
(117, 50),
(118, 51),
(119, 52),
(120, 53),
(121, 54),
(122, 55),
(123, 56),
(124, 57),
(125, 58),
(126, 59),
(127, 60),
(128, 61),
(129, 62),
(130, 63),
(131, 64),
(132, 65),
(133, 66),
(134, 67),
(135, 68),
(136, 69),
(137, 70),
(138, 71),
(139, 72),
(140, 73),
(141, 74),
(142, 75),
(143, 76),
(144, 77),
(145, 78),
(146, 79),
(147, 80),
(148, 81),
(149, 82),
(150, 83),
(151, 84),
(152, 85),
(153, 86);

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
(0, 0, 'FreeForm One');

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
(1, '', 'hartloff@buffalo.edu', NULL, '16c861073c150ec14bc65bd63d5e11c1fc76b309e37a1dd034f22875fb4f96f8'),
(2, 'Paul Dickson', '', NULL, NULL),
(4, 'Matthew Hertz', 'mhertz@buffalo.edu', NULL, NULL);

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
(2, 2, 50243400, 12, 50243400, 1),
(5, 0, 50243480, 55, 50243480, 1),
(39, 20, 50243535, 15, 50243535, 1),
(40, 20, 50243535, 15, 50243536, 1),
(41, 20, 50243535, 15, 50243537, 1),
(42, 20, 50243535, 15, 50243538, 1),
(43, 20, 50243536, 15, 50243536, 1),
(44, 20, 50243536, 15, 50243537, 1),
(45, 20, 50243536, 15, 50243538, 1),
(46, 20, 50243536, 15, 50243535, 1),
(47, 20, 50243537, 15, 50243537, 1),
(48, 20, 50243537, 15, 50243538, 1),
(49, 20, 50243537, 15, 50243535, 1),
(50, 20, 50243537, 15, 50243536, 1),
(51, 20, 50243538, 15, 50243538, 1),
(52, 20, 50243538, 15, 50243535, 1),
(53, 20, 50243538, 15, 50243536, 1),
(54, 20, 50243538, 15, 50243537, 1),
(55, 18, 50243535, 16, 50243535, 1),
(56, 18, 50243535, 16, 50243536, 1),
(57, 18, 50243535, 16, 50243537, 1),
(58, 18, 50243535, 16, 50243538, 1),
(59, 18, 50243536, 16, 50243536, 1),
(60, 18, 50243536, 16, 50243537, 1),
(61, 18, 50243536, 16, 50243538, 1),
(62, 18, 50243536, 16, 50243535, 1),
(63, 18, 50243537, 16, 50243537, 1),
(64, 18, 50243537, 16, 50243538, 1),
(65, 18, 50243537, 16, 50243535, 1),
(66, 18, 50243537, 16, 50243536, 1),
(67, 18, 50243538, 16, 50243538, 1),
(68, 18, 50243538, 16, 50243535, 1),
(69, 18, 50243538, 16, 50243536, 1),
(70, 18, 50243538, 16, 50243537, 1),
(71, 19, 50243535, 17, 50243535, 1),
(72, 19, 50243535, 17, 50243536, 1),
(73, 19, 50243535, 17, 50243537, 1),
(74, 19, 50243535, 17, 50243538, 1),
(75, 19, 50243536, 17, 50243536, 1),
(76, 19, 50243536, 17, 50243537, 1),
(77, 19, 50243536, 17, 50243538, 1),
(78, 19, 50243536, 17, 50243535, 1),
(79, 19, 50243537, 17, 50243537, 1),
(80, 19, 50243537, 17, 50243538, 1),
(81, 19, 50243537, 17, 50243535, 1),
(82, 19, 50243537, 17, 50243536, 1),
(83, 19, 50243538, 17, 50243538, 1),
(84, 19, 50243538, 17, 50243535, 1),
(85, 19, 50243538, 17, 50243536, 1),
(86, 19, 50243538, 17, 50243537, 1);

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
(1, 'This is rubric id # one'),
(2, 'This is rubric id # two'),
(3, 'This is rubric id # three');

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
(1, 1, 'Im smitty JohnSon and I approve of this rubric ');

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
(1, 1, 'Smitty JohnSon', 55),
(2, 1, 'John Eggert', 82),
(3, 1, 'Smitty JohnSon', 67),
(4, 1, 'Smitty JohnSon', 99),
(5, 0, 'Jim Jones', 97),
(10, 1, 'Student One', 66),
(11, 1, 'Student One', 75),
(12, 1, 'Student One', 80),
(13, 1, 'Student One', 100),
(14, 1, 'Student One', 96),
(20, 1, 'Student Two', 68),
(21, 1, 'Student Two', 99),
(22, 1, 'Student Two', 82),
(23, 1, 'Student Two', 91),
(24, 1, 'Student Two', 97),
(25, 1, 'Student Three', 88),
(26, 1, 'Student Three', 95),
(27, 1, 'Student Three', 78),
(28, 1, 'Student Three', 81),
(29, 1, 'Student Three', 100),
(30, 1, 'Student Four', 79),
(31, 1, 'Student Four', 96),
(32, 1, 'Student Four', 64),
(33, 1, 'Student Four', 90),
(34, 1, 'Student Four', 81),
(35, 1, 'Low Review One', 20),
(36, 1, 'Low Review Two', 5),
(37, 1, 'Low Review Three', 10),
(38, 1, 'Low Review Four', 4),
(39, 1, 'Low Review Five', 8),
(40, 1, 'Zero Scored', 1);

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
(1, 1, 'Teamwork ', 'multiple_choice'),
(2, 1, 'Leadership', 'multiple_choice'),
(3, 1, 'Participation', 'multiple_choice'),
(4, 1, 'Professionalism', 'multiple_choice'),
(5, 1, 'Quality', 'multiple_choice');

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
(125, 2, 1),
(127, 1, 1),
(136, 3, 1),
(139, 3, 1),
(139, 4, 1),
(139, 5, 1),
(143, 2, 1),
(148, 4, 1),
(126, 2, 2),
(127, 2, 2),
(129, 5, 2),
(141, 5, 2),
(142, 5, 2),
(149, 3, 2),
(153, 4, 2),
(118, 4, 3),
(124, 5, 3),
(134, 4, 3),
(146, 2, 3),
(149, 5, 3),
(122, 3, 4),
(123, 2, 4),
(127, 5, 4),
(137, 4, 4),
(144, 1, 4),
(149, 2, 4),
(151, 1, 4),
(129, 4, 5),
(139, 2, 5),
(142, 4, 5),
(151, 4, 5),
(94, 1, 10),
(94, 2, 10),
(112, 5, 10),
(152, 1, 10),
(95, 2, 11),
(148, 5, 11),
(94, 3, 12),
(126, 1, 12),
(129, 3, 12),
(136, 4, 12),
(147, 4, 12),
(94, 4, 13),
(117, 1, 13),
(120, 2, 13),
(121, 4, 13),
(134, 3, 13),
(94, 5, 14),
(136, 5, 14),
(140, 1, 14),
(142, 3, 14),
(147, 2, 14),
(150, 3, 14),
(152, 3, 14),
(95, 1, 20),
(118, 5, 20),
(120, 3, 20),
(121, 3, 20),
(95, 3, 21),
(114, 2, 21),
(125, 4, 21),
(126, 3, 21),
(138, 4, 21),
(148, 2, 21),
(95, 4, 22),
(119, 2, 22),
(119, 4, 22),
(146, 5, 22),
(149, 1, 22),
(95, 5, 23),
(123, 5, 23),
(127, 4, 23),
(117, 3, 24),
(120, 4, 24),
(124, 3, 24),
(125, 5, 24),
(135, 5, 24),
(96, 1, 25),
(122, 4, 25),
(124, 1, 25),
(138, 5, 25),
(96, 2, 26),
(118, 2, 26),
(128, 2, 26),
(128, 3, 26),
(129, 2, 26),
(143, 1, 26),
(150, 4, 26),
(96, 3, 27),
(121, 2, 27),
(135, 4, 27),
(146, 3, 27),
(96, 4, 28),
(96, 5, 29),
(119, 5, 29),
(142, 1, 29),
(144, 2, 29),
(97, 1, 30),
(117, 2, 30),
(121, 1, 30),
(122, 1, 30),
(123, 1, 30),
(125, 1, 30),
(137, 2, 30),
(138, 2, 30),
(141, 2, 30),
(144, 5, 30),
(148, 1, 30),
(150, 5, 30),
(151, 2, 30),
(153, 3, 30),
(97, 2, 31),
(116, 1, 31),
(118, 1, 31),
(126, 5, 31),
(135, 3, 31),
(140, 3, 31),
(142, 2, 31),
(153, 2, 31),
(97, 3, 32),
(120, 1, 32),
(123, 3, 32),
(139, 1, 32),
(143, 4, 32),
(143, 5, 32),
(144, 4, 32),
(145, 5, 32),
(148, 3, 32),
(151, 3, 32),
(97, 4, 33),
(116, 3, 33),
(125, 3, 33),
(128, 4, 33),
(140, 2, 33),
(141, 1, 33),
(144, 3, 33),
(147, 3, 33),
(149, 4, 33),
(97, 5, 34),
(124, 4, 34),
(138, 3, 34),
(141, 4, 34),
(152, 2, 34),
(110, 1, 35),
(111, 5, 35),
(112, 1, 35),
(115, 1, 35),
(115, 2, 35),
(115, 4, 35),
(116, 2, 35),
(117, 5, 35),
(119, 3, 35),
(120, 5, 35),
(130, 1, 35),
(130, 3, 35),
(130, 5, 35),
(131, 1, 35),
(131, 5, 35),
(132, 3, 35),
(133, 2, 35),
(134, 1, 35),
(138, 1, 35),
(141, 3, 35),
(143, 3, 35),
(145, 3, 35),
(150, 1, 35),
(110, 2, 36),
(111, 4, 36),
(112, 3, 36),
(114, 4, 36),
(123, 4, 36),
(127, 3, 36),
(131, 2, 36),
(131, 4, 36),
(132, 4, 36),
(133, 5, 36),
(135, 2, 36),
(146, 1, 36),
(147, 1, 36),
(150, 2, 36),
(152, 5, 36),
(110, 3, 37),
(111, 3, 37),
(116, 5, 37),
(124, 2, 37),
(128, 5, 37),
(132, 1, 37),
(133, 1, 37),
(135, 1, 37),
(136, 2, 37),
(137, 5, 37),
(146, 4, 37),
(147, 5, 37),
(153, 5, 37),
(110, 4, 38),
(111, 2, 38),
(112, 4, 38),
(113, 2, 38),
(113, 3, 38),
(113, 4, 38),
(113, 5, 38),
(115, 3, 38),
(116, 4, 38),
(130, 4, 38),
(132, 2, 38),
(133, 4, 38),
(136, 1, 38),
(137, 3, 38),
(140, 4, 38),
(145, 2, 38),
(151, 5, 38),
(152, 4, 38),
(110, 5, 39),
(111, 1, 39),
(112, 2, 39),
(113, 1, 39),
(119, 1, 39),
(122, 2, 39),
(126, 4, 39),
(129, 1, 39),
(130, 2, 39),
(132, 5, 39),
(133, 3, 39),
(134, 2, 39),
(134, 5, 39),
(137, 1, 39),
(145, 1, 39),
(145, 4, 39),
(114, 1, 40),
(114, 3, 40),
(114, 5, 40),
(115, 5, 40),
(117, 4, 40),
(118, 3, 40),
(121, 5, 40),
(122, 5, 40),
(128, 1, 40),
(131, 3, 40),
(140, 5, 40),
(153, 1, 40);

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
(50243480, 'John Eggert', 'jeggert@buffalo.edu'),
(50243535, 'Student One', 'one@buffalo.edu'),
(50243536, 'Student Two', 'two@buffalo.edu'),
(50243537, 'Student Three', 'three@buffalo.edu'),
(50243538, 'Student Four', 'four@buffalo.edu');

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
(18, 10117, '2023-10-27 00:42:35', '2023-10-28 00:42:35', 'CSE 404', 1, 1),
(19, 10117, '2023-10-27 00:43:04', '2023-10-28 00:43:04', 'CSE 404 #2', 1, 1),
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
(1, 'Individual Reviewed by Individual', 'One row per review. Each row has 2 columns: email of the reviewer, email of the person being reviewed.', 0),
(2, 'Each Team Member Reviewed By Entire Team', 'One row per team. Each row contains the email addresses for all team members. Blank columns are ignored', 0),
(3, 'Each Team Member Reviewed by Entire Team + Manager', 'One row per team. Each row contains the email addresses for all team members with the manager email address listed last. Blank columns are ignored', 1),
(4, 'Single Individual Reviewed by Each Team Member', 'One row per individual being reviewed. Every row contains the email addresses of the reviewers and the person being reviewed. The person being reviewed MUST be in the final column in the row.', 0);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=98084020;

--
-- AUTO_INCREMENT for table `evals`
--
ALTER TABLE `evals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=154;

--
-- AUTO_INCREMENT for table `instructors`
--
ALTER TABLE `instructors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50243487;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=87;

--
-- AUTO_INCREMENT for table `rubrics`
--
ALTER TABLE `rubrics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `rubric_scores`
--
ALTER TABLE `rubric_scores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `rubric_topics`
--
ALTER TABLE `rubric_topics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50243539;

--
-- AUTO_INCREMENT for table `surveys`
--
ALTER TABLE `surveys`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `survey_types`
--
ALTER TABLE `survey_types`
  MODIFY `id` tinyint(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

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
