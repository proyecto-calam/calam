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
	    
	    $start_date_format = DateTime::createFromFormat("d-m-Y", $start_date);
	    $end_date_format = DateTime::createFromFormat("d-m-Y", $end_date);

	    $start_date_default = array(
	        'day' => (int)$start_date_format->format("d"),
	        'month' => (int)$start_date_format->format("m"),
	        'year' => (int)$start_date_format->format("Y")
        );

        $end_date_default = array(
	        'day' => (int)$end_date_format->format("d"),
	        'month' => (int)$end_date_format->format("m"),
	        'year' => (int)$end_date_format->format("Y")
        );

	    $year_range = array(
		    'startyear' => (int)$start_date_format->format("Y"),
		    'stopyear'  => (int)$end_date_format->format("Y"),
		    'timezone'  => 99,
		    'optional'  => false
		);
	    
	    $mform = $this->_form;	    
	    $mform->addElement('header', 'headertimeplatform', get_string('bcd_01', 'block_calam')." - ".$coursename);
	    $mform->addElement('hidden', 'start_date', $start_date);
	    $mform->addElement('hidden', 'manual', 0);
	    $mform->addElement('hidden', 'end_date', $end_date);
	    $mform->addElement('hidden', 'userid', (int)$userid);
	    $mform->addElement('hidden', 'courseid', (int)$courseid);
	    $mform->addElement('date_selector', 'start_date_', get_string('bcd_01_graph_start_date', 'block_calam'), $year_range);
		$mform->setDefault('start_date_', $start_date_default);
	    $mform->addElement('date_selector', 'end_date_', get_string('bcd_01_graph_end_date', 'block_calam'), $year_range);
	    $mform->setDefault('end_date_', $end_date_default);	    
	    $buttonarray=array();
		$buttonarray[] =& $mform->createElement('button', 'submitbutton', "calcular");
		$buttonarray[] =& $mform->createElement('cancel');
		$mform->addGroup($buttonarray, 'buttonar', '', '', false);
		$mform->addElement('html', '<div id="result" style="position: relative; padding-bottom: 80%; height: 0px;  overflow: hidden;" ></div>');
	}
}
?>