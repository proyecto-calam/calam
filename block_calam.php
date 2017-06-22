<?php
class block_calam extends block_base {
	 public function init() {
        $this->title = get_string('calam', 'block_calam');
    }

    public function get_content() {
    	global $CFG;
    	global $COURSE;
	    if ($this->content !== null) {
	      return $this->content;
    	}    	

    	$this->content         =  new stdClass;
	    $this->content->text   =  html_writer::tag('a', get_string('dashboard', 'block_calam'), array('href' => $CFG->wwwroot.'/blocks/calam/dashboard.php?courseid='.$COURSE->id));
 	   	return $this->content;
	}

	public function instance_allow_multiple() {
  		return false;
	}

	function has_config() {return true;}
}
