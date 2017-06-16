<?php
defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/formslib.php");
require_once($CFG->libdir.'/coursecatlib.php');
 
class course_deliverie extends moodleform {	
	/**
     * The form definition.
     */
	public function definition() {		
	    global $CFG, $DB;
	    $groups_course = $this->_customdata['groups_course'];
	    $courseid = $this->_customdata['courseid'];
	    $coursename = $this->_customdata['coursename'];
	    $startdate = $this->_customdata['startdate'];

	    $mform = $this->_form;
	    $mform->addElement('header', 'headercoursedeliveries', get_string('bcd_02', 'block_calam')." - ".$coursename);
	    $mform->addElement('hidden', 'courseid', $courseid);
	    $mform->addElement('hidden', 'course', $courseid);
   	    $mform->addElement('hidden', 'bcd_02_error_group', get_string('bcd_02_error_group', 'block_calam'));
   	    $mform->addElement('hidden', 'bcd_02_graph_qualification', get_string('bcd_02_graph_qualification', 'block_calam'));
   	    $mform->addElement('hidden', 'bcd_02_graph_no_data', get_string('bcd_02_graph_no_data', 'block_calam'));
   	    $mform->addElement('hidden', 'bcd_02_graph_title', get_string('bcd_02_graph_title', 'block_calam'));
   	    $mform->addElement('hidden', 'bcd_02_graph_activities', get_string('bcd_02_graph_activities', 'block_calam'));
   	    $mform->addElement('hidden', 'bcd_02_graph_students', get_string('bcd_02_graph_students', 'block_calam'));
   	    $mform->addElement('hidden', 'bcd_02_graph_unde_tasks', get_string('bcd_02_graph_unde_tasks', 'block_calam'));
   	    $mform->addElement('hidden', 'bcd_02_graph_task_with_qual', get_string('bcd_02_graph_task_with_qual', 'block_calam'));
   	    $mform->addElement('hidden', 'bcd_02_graph_less_than_60', get_string('bcd_02_graph_less_than_60', 'block_calam'));
   	    $mform->addElement('hidden', 'bcd_02_graph_activ_betw_67', get_string('bcd_02_graph_activ_betw_67', 'block_calam'));
   	    $mform->addElement('hidden', 'bcd_02_graph_activ_betw_81', get_string('bcd_02_graph_activ_betw_81', 'block_calam'));
	    $mform->addElement('hidden', 'startdate', $startdate);	    
	    $mform->addElement('html', '<div id="error"></div>');
	    if(count($groups_course)>1)
			$mform->addElement('select', 'groups', get_string('groups', 'block_calam'), $groups_course);					
		else
			$mform->addElement('hidden', 'groups', 0);		
		$buttonarray=array();		
		$buttonarray[] = &$mform->createElement('cancel' ,'return', get_string('backToDashboard' , 'block_calam'));
		$mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
		$mform->closeHeaderBefore('buttonar');
		$mform->addElement('html', '<div id="grafica"></div>');
	}
}
?>
