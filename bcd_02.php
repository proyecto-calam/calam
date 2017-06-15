<?php
	require_once(dirname(__FILE__) . '/../../config.php');
	require_once('course_deliverie.php');	
	require_once('lib.php');	


	$item = 'bcd_02';

	$tag = get_string($item, 'block_calam');
	$title = $tag;
	$my_url ='/blocks/calam/'.$item.'.php';
	$PAGE->set_url($my_url);
	$PAGE->set_title($title);
	$PAGE->set_heading($title);
	$PAGE->set_context(get_context_instance(CONTEXT_SYSTEM));
	$PAGE->requires->css('/blocks/calam/css/style.css');	
	$PAGE->set_pagelayout('standard');
	$courseid = required_param('courseid', PARAM_INT);
	$course = get_course($courseid);
	$CFG->additionalhtmlhead .= '<meta name="robots" content="noindex" />';
	$PAGE->requires->js('/blocks/calam/js/jquery-2.2.3.min.js');					
	$PAGE->requires->js('/blocks/calam/js/jquery.canvasjs.min.js');
	$PAGE->requires->js('/blocks/calam/js/jquery-ui-1.11.4.custom/jquery-ui.js');
	$PAGE->requires->js('/blocks/calam/js/bcd_02.js');

	
	$groups_course = get_groups_course($courseid);	
	$course_start_date = get_course_start_day($courseid);
	$startdateform = 1;
	$groups_select = array();
	if(count($groups_course) > 0){
		$groups_select[-1] = get_string('selectGroup', 'block_calam');
		foreach ($groups_course as $value) {
			$groups_select[$value->id] = $value->name;
		}
	}	
	foreach ($course_start_date as $startdate){
		$startdateform =$startdate->startdate;
	}
	echo $OUTPUT->header();	
	$mform = new course_deliverie(null, array(
    'groups_course' => $groups_select,
    'courseid' => $courseid,
    'coursename' => $course->fullname,
    'startdate' => $startdateform
	));

	$manageurl = new moodle_url('dashboard.php');
	if ($mform->is_cancelled()) {
    	$manageurl->param('courseid', $courseid);
    	redirect($manageurl);
	}
	else{	
		$mform->display();
	}
			
	echo $OUTPUT->footer();	


	function get_groups_course($courseid){
		global $DB;
		global $CFG;
		global $USER;
		$query = "SELECT DISTINCT gr.*
			FROM {$CFG->prefix}groups AS gr 
			INNER JOIN {$CFG->prefix}groups_members as gm ON (gr.id = gm.groupid)
			WHERE gr.courseid = $courseid";
		if (!is_siteadmin()){
			$query .= " AND userid = {$USER->id}";
		}
		$query .= " ORDER BY gr.timecreated ASC";
		$data = $DB->get_records_sql($query);
		return $data;    
	}

	function get_course_start_day($courseid){
		global $DB;
		global $CFG;
		$query = "SELECT c.startdate
			FROM {$CFG->prefix}course AS c
			WHERE c.id = $courseid";
		$data = $DB->get_records_sql($query);
		return $data;
	}	
?>
