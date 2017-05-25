<?php
	require_once(dirname(__FILE__) . '/../../config.php');
	
	$item = 'bcd_02';
	$tag = get_string($item, 'block_calam');

	$title = $tag;
	$my_url ='/blocks/calam/dashboard.php';

	$PAGE->set_url($my_url);
	$PAGE->set_title($title);
	$PAGE->set_heading($title);
	$PAGE->set_context(get_context_instance(CONTEXT_SYSTEM));
	$PAGE->requires->css('/blocks/calam/css/style.css');	

	$PAGE->set_pagelayout('standard');
	$courseid = required_param('courseid', PARAM_INT);
	$course = get_course($courseid);

	$CFG->additionalhtmlhead .= '<meta name="robots" content="noindex" />';


	echo $OUTPUT->header();
	echo $tag.' '.$course->fullname;

	echo '<div id="return">';	
	echo '<a href="dashboard.php?courseid='.$course->id.'">'.get_string('backToDashboard', 'block_calam').'</a>';
	echo '</div>';
	echo $OUTPUT->footer();
?>
