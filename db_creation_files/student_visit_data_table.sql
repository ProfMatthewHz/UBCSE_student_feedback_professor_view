CREATE TABLE `student_visit_data` (
  `student_id` int(11) NOT NULL,
  `survey_id` int(11) NOT NULL,
  `visit_count` int(11) DEFAULT 1,
  `last_visit` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_visit_data`
--

INSERT INTO `student_visit_data` (`student_id`, `survey_id`, `visit_count`, `last_visit`) VALUES
(50243490, 42, 11, '2024-02-29 14:30:06');

