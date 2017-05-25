<?php
	require_once(dirname(__FILE__) . '/../../config.php');
	$total_items = 3;

	$title = get_string('pluginname', 'block_calam');
	$my_url ='/blocks/calam/dashboard.php';

	$PAGE->set_url($my_url);
	$PAGE->set_title($title);
	$PAGE->set_heading($title);
	$PAGE->set_context(get_context_instance(CONTEXT_SYSTEM));
	$PAGE->requires->css('/blocks/calam/css/style.css');	

	$PAGE->set_pagelayout('standard');
	$courseid = required_param('courseid', PARAM_INT);
	$course = get_course($courseid);
//	$PAGE->set_context(get_context_instance(CONTEXT_COURSE, $courseid));
	$CFG->additionalhtmlhead .= '<meta name="robots" content="noindex" />';


	echo $OUTPUT->header();
	echo "DASHBOARD " . $course->fullname;

	echo '<div id="container">';
	for($i = 1; $i <= $total_items; $i++){
		$item = 'bcd_0'.$i;
		$tag = get_string($item, 'block_calam');
		echo '<div class="block_calam_dashboard_element" id="'.$item.'"><a href="'.$item.'.php?courseid='.$courseid.'"> '.$tag.'</a></div>';
	}

	echo '<div id="return">';	
	echo '<a href="'.$CFG->wwwroot.'/course/view.php?id='.$course->id.'">'.get_string('backToCourse', 'block_calam'). $course->fullname . '</a>';
	echo '</div>';
	echo $OUTPUT->footer();
?>
