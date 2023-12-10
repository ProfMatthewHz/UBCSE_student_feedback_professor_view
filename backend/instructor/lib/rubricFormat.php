<?php
function format_scores($rubric_scores) {
	$levels = array();
	// levels[i] = array("name" => [name], "score" => [score])

	foreach ($rubric_scores as $score_id => $level_data){
		// $level_data['level_id'] = $score_id;
		$levels[] = $level_data;
	}

	return $levels;
}

function format_topics($rubric_topics) {
	// akin to criterions

	$topics = array();
	foreach( $rubric_topics as $topic ){

		$single_criterion = array();

		$criterion_name = $topic['question'];
		$criterion_responses = $topic['responses'];
		$criterion_type = $topic['type'];

		$single_criterion['question'] = $criterion_name;
		$single_criterion['responses'] = array_values($criterion_responses);
		$single_criterion['type'] = $criterion_type;

		$topics[] = $single_criterion;
	}

	return $topics;
}

function format_rubric_data($rubric_name, $rubric_scores, $rubric_topics){
    // create rubric data formatted correctly. 
    // $rubric_scores from getRubricScores, $rubric_topics from getRubricTopics;
     

    $rubric_data = array();

    $levels_data = format_scores($rubric_scores);
    $topics_data = format_topics($rubric_topics);

    $rubric_data['name'] = $rubric_name;
    $rubric_data['levels'] = $levels_data;
    $rubric_data['topics'] = $topics_data;

    return $rubric_data;

}

?>