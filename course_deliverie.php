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
	    $mform->addElement('hidden', 'startdate', $startdate);	    
	    $mform->addElement('html', '<div id="error"></div>');
		$mform->addElement('select', 'groups', get_string('groups', 'block_calam'), $groups_course);		
		$mform->addElement('button', 'return', get_string('backToDashboard' , 'block_calam'));
		$mform->addElement('html', '<div id="grafica"></div>');
	}
}
?>