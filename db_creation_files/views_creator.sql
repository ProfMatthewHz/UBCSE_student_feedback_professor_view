-- evals_by_student view:
-- query which can be used to get the evaluations a student completed and simplify normalizing
CREATE VIEW `evals_by_student` AS
SELECT `surveys`.`course_id` AS `course_id`,`surveys`.`id` AS `survey_id`,`eval`.`id` AS `eval_id`,`eval`.`submitter_id` AS `submitter_id`,`eval`.`teammate_id` AS `teammate_id`,`scores`.`score1` AS `score1`,`scores`.`score2` AS `score2`,`scores`.`score3` AS `score3`,`scores`.`score4` AS `score4`,`scores`.`score5` AS `score5`
FROM ((`surveys`
  JOIN `eval` on((`surveys`.`id` = `eval`.`survey_id`)))
  JOIN `scores` on((`eval`.`id` = `scores`.`eval_id`)))
ORDER BY `surveys`.`course_id`,`surveys`.`id`,`eval`.`submitter_id`;

-- teammates view
-- query which generates the listing of teammates. This helped with our having faculty upload email addresses, but the DB use id numbers
CREATE VIEW `teammates` AS
SELECT `reviewers`.`id` AS `id`,`reviewers`.`survey_id` AS `survey_id`,`stu_rev`.`student_id` AS `student_id`,`stu_tm`.`student_id` AS `teammate_id`
FROM ((`reviewers`
  JOIN `students` `stu_rev` on((`reviewers`.`reviewer_email` = `stu_rev`.`email`)))
  JOIN `students` `stu_tm` on((`reviewers`.`teammate_email` = `stu_tm`.`email`)));

-- expected_evals view:
-- query used to count the number of teammates a student should evaluate; used to exclude evaluations that left some students data incomplete
CREATE VIEW `expected_evals` AS
SELECT `surveys`.`course_id` AS `course_id`,`surveys`.`id` AS `survey_id`,`teammates`.`student_id` AS `student_id`, count(distinct `teammates`.`teammate_id`) AS `teammates`
FROM (`surveys`
  JOIN `teammates` on ((`teammates`.`survey_id` = `surveys`.`id`)))
GROUP BY `surveys`.`course_id`,`surveys`.`id`,`teammates`.`student_id`;

-- total_points_per_submission view:
-- query that calculates the total number of points a student awarded across all their evaluations AND the total number of students they evaluated
CREATE VIEW `total_points_per_submission` AS
SELECT `surveys`.`course_id` AS `course_id`,`surveys`.`id` AS `survey_id`,`eval`.`submitter_id` AS `submitter_id`,sum(((((`scores`.`score1` + `scores`.`score2`) + `scores`.`score3`) + `scores`.`score4`) + `scores`.`score5`)) AS `TOTAL_POINTS`,count(distinct `scores`.`eval_id`) AS `EVALUATIONS`
FROM ((`surveys`
  JOIN `eval` on((`surveys`.`id` = `eval`.`survey_id`)))
  JOIN `scores` on((`eval`.`id` = `scores`.`eval_id`)))
GROUP BY `surveys`.`course_id`,`surveys`.`id`,`eval`.`submitter_id`;

-- evals_of_student view:
-- query that calculates each students' evaluations' NORMALIZED results
CREATE VIEW `evals_of_student` AS
SELECT `ebs`.`course_id` AS `course_id`,`ebs`.`survey_id` AS `survey_id`,`ebs`.`teammate_id` AS `evaluatee_id`,`ebs`.`submitter_id` AS `submitter_id`,(((((`ebs`.`score1` + `ebs`.`score2`) + `ebs`.`score3`) + `ebs`.`score4`) + `ebs`.`score5`) / `tpps`.`TOTAL_POINTS`) AS `normalized_score`
FROM (`evals_by_student` `ebs`
  JOIN `total_points_per_submission` `tpps` on(((`ebs`.`course_id` = `tpps`.`course_id`) and (`ebs`.`survey_id` = `tpps`.`survey_id`) and (`ebs`.`submitter_id` = `tpps`.`submitter_id`))))
ORDER BY `ebs`.`course_id`,`ebs`.`survey_id`,`ebs`.`teammate_id`;

-- normalized_score VIEW
-- query that calculates each students overall NORMALIZED score for a given survey
CREATE VIEW `normalized_student_score` AS
SELECT `ebs`.`course_id` AS `course_id`,`ebs`.`survey_id` AS `survey_id`,`ebs`.`teammate_id` AS `evaluatee_id`,`students`.`email` AS `email`, avg(((((((`ebs`.`score1` + `ebs`.`score2`) + `ebs`.`score3`) + `ebs`.`score4`) + `ebs`.`score5`) / `tpps`.`TOTAL_POINTS`) * `tpps`.`EVALUATIONS`)) AS `normalized_score`
FROM (((`evals_by_student` `ebs`
  JOIN `total_points_per_submission` `tpps` on(((`ebs`.`course_id` = `tpps`.`course_id`) and (`ebs`.`survey_id` = `tpps`.`survey_id`) and (`ebs`.`submitter_id` = `tpps`.`submitter_id`))))
  JOIN `expected_evals` on(((`expected_evals`.`course_id` = `tpps`.`course_id`) and (`expected_evals`.`survey_id` = `tpps`.`survey_id`) and (`expected_evals`.`student_id` = `tpps`.`submitter_id`) and (`expected_evals`.`teammates` = `tpps`.`EVALUATIONS`))))
  JOIN `students` on((`students`.`student_id` = `ebs`.`teammate_id`)))
GROUP BY `ebs`.`course_id`,`ebs`.`survey_id`,`ebs`.`teammate_id`,`students`.`email`
ORDER BY `ebs`.`course_id`,`ebs`.`survey_id`,`ebs`.`teammate_id`;

-- readable_evals view
-- query that generates an easy to read listing of evaluations; MHz originally created this for debugging, but it also helps when handling student complaints
CREATE VIEW `readable_evals` AS
SELECT `surveys`.`course_id` AS `course_id`,`surveys`.`id` AS `survey_id`,`stu1`.`email` AS `submitter_email`,`stu2`.`email` AS `teammate_email`,`scores`.`score1` AS `score1`,`scores`.`score2` AS `score2`,`scores`.`score3` AS `score3`,`scores`.`score4` AS `score4`,`scores`.`score5` AS `score5`
FROM ((((`surveys`
  JOIN `eval` on((`surveys`.`id` = `eval`.`survey_id`)))
  JOIN `scores` on((`eval`.`id` = `scores`.`eval_id`)))
  JOIN `students` `stu1` on((`eval`.`submitter_id` = `stu1`.`student_id`)))
  JOIN `students` `stu2` on((`eval`.`teammate_id` = `stu2`.`student_id`)))
ORDER BY `eval`.`submitter_id`,`eval`.`survey_id`;
