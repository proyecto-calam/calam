<?php
	require_once(dirname(__FILE__) . '/../../config.php');


	$title = get_string('blockstring', 'block_calam');
	$my_url ='/blocks/calam/dashboard.php';

	$PAGE->set_url($my_url);
	$PAGE->set_title($title);
	$PAGE->set_heading($title);
	$PAGE->set_context(get_context_instance(CONTEXT_SYSTEM));

	$CFG->additionalhtmlhead .= '<meta name="robots" content="noindex" />';


	echo $OUTPUT->header();
echo "DASHBOARD";
	echo $OUTPUT->footer();
?>