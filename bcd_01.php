<?php
    require_once(dirname(__FILE__) . '/../../config.php');
    require_once('time_platform.php');	
    require_once('lib.php');

    $item = 'bcd_01';

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
    $PAGE->requires->js('/blocks/calam/js/bcd_01.js');

    echo $OUTPUT->header();	

    $end_date = get_ultimate_log_course($USER->id, $courseid);
    $mform = new time_platform(null, array(
        'coursename' => $course->fullname,
        'start_date' => date('d-m-Y', $course->timecreated),    
        'end_date' => date('d-m-Y', $end_date->timecreated),
        'userid' => $USER->id,
        'courseid' => $courseid
    ));
    $manageurl = new moodle_url('dashboard.php');
    if ($mform->is_cancelled()) {
    $manageurl->param('courseid', $courseid);
    redirect($manageurl);
    }
    else
        $mform->display();			

    function get_ultimate_log_course($userid, $courseid){
        global $DB;
        global $CFG;
        $query = "SELECT l.timecreated 
                FROM {$CFG->prefix}logstore_standard_log l
                WHERE l.userid = $userid AND l.courseid = $courseid 
                ORDER BY id DESC LIMIT 1";		
        $data = $DB->get_record_sql($query);
        return $data;
    }
    echo $OUTPUT->footer();
?>