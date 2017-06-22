<?php
defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/formslib.php");
require_once($CFG->libdir.'/coursecatlib.php');
 
class time_platform extends moodleform {	

	public function definition() {		
	    global $CFG, $DB;

	    $coursename = $this->_customdata['coursename'];
	    $start_date = $this->_customdata['start_date'];
	    $end_date = $this->_customdata['end_date'];
	    $userid = $this->_customdata['userid'];
	    $courseid = $this->_customdata['courseid'];

	    $start_date = 

	    $date_start_default = array(
		    'startyear' => 1970, 
		    'stopyear'  => 2020,
		    'timezone'  => 99,
		    'optional'  => false
		);

	    $mform = $this->_form;	    
	    $mform->addElement('header', 'headertimeplatform', get_string('bcd_01', 'block_calam')." - ".$coursename);
	    $this->add_action_buttons();
	    $mform->addElement('hidden', 'start_date', (int)$start_date);
	    $mform->addElement('hidden', 'end_date', (int)$end_date);
	    $mform->addElement('hidden', 'userid', (int)$userid);
	    $mform->addElement('hidden', 'courseid', (int)$courseid);
	    $mform->addElement('date_selector', 'start_date_', get_string('bcd_01_graph_start_date', 'block_calam'), $date_start_default);
        $mform->setDefault('start_date_', $start_date);
	    $mform->addElement('date_selector', 'end_date_', get_string('bcd_01_graph_end_date', 'block_calam'));
		/*$buttonarray=array();		
		$buttonarray[] = &$mform->createElement('cancel' ,'return', get_string('backToDashboard' , 'block_calam'));
		$mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
		$mform->closeHeaderBefore('buttonar');		*/		
		$mform->addElement('html', '<div id="result" style="position: relative; padding-bottom: 80%; height: 0px;  overflow: hidden;" ></div>');
	}
}
?>