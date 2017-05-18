<?php
class block_calam extends block_list {
public function init() {
$this->title = get_string('calam', 'block_calam');
}

public function get_content() {
  if ($this->content !== null) {
    return $this->content;
  }
//	  html_writer::tag('a', 'Menu Option 1', array('href' => 'some_file.php'));
/*	  $this->content         = new stdClass;
	  $this->content->items  = array();
	  $this->content->icons  = array();
	  $this->content->footer = 'Footer here...';
	 
	  $this->content->items[] =
	  $this->content->icons[] = html_writer::empty_tag('img', array('src' => 'images/icons/1.gif', 'class' => 'icon'));
	 
	  // Add more list items here
	*/
        $this->content         =  new stdClass;
        $this->content->text   = 'The content of our CALAM block!';
        $this->content->footer = 'Footer here...';
 
        return $this->content; 
	  return $this->content;
	}
/*

	public function specialization() {
		if (isset($this->config)) {
		    if (empty($this->config->title)) {
		        $this->title = get_string('defaulttitle', 'block_calam');            
		    } else {
		        $this->title = $this->config->title;
		    }
 
		    if (empty($this->config->text)) {
		        $this->config->text = get_string('defaulttext', 'block_calam');
		    }    
		}
	}

    public function instance_allow_multiple() {
        return true;
    }

    public function has_config() {
		return true;
	}

/*
	public function instance_config_save($data,$nolongerused =false) {
	  if(get_config('calam', 'Allow_HTML') == '1') {
		$data->text = strip_tags($data->text);
	  }
	 
	  // And now forward to the default implementation defined in the parent class
	  return parent::instance_config_save($data,$nolongerused);
	}
*/
}
