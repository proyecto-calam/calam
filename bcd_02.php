<?php
	require_once(dirname(__FILE__) . '/../../config.php');
	
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




	echo $OUTPUT->header();
	echo $tag.' '.$course->fullname;



	if(!is_null($groups_course))
	{
		echo "<form id='gropus_data' method='POST'>";
			echo "<input type='hidden' name='course' value='$courseid'>";
			foreach ($course_start_date as $startdate){
				echo "<input type='hidden' name='startdate' value='$startdate->startdate'>";
			}			
			echo "<table>";
				echo "<tr>";
					echo "<td>";
						echo "<p>Seleccione el grupo a consultar: </p>";
					echo "</td>";
					echo "<td>";
						echo "<select id='groups' name='groups'>";
							foreach ($groups_course as $grupo){
								echo "<option id='grupo$grupo->id' value='$grupo->id'>".$grupo->name."</option>";
							}
						echo "</select>";
					echo "</td>";
				echo "</tr>";
			echo "</table>";	
		echo "</form>";
		echo "<div id='grafica'></div>";
	}

	echo '<div id="return">';	
	echo '<a href="dashboard.php?courseid='.$course->id.'">'.get_string('backToDashboard', 'block_calam').'</a>';
	echo '</div>';
	echo $OUTPUT->footer();

	function get_groups_course($courseid){
		global $DB;
		global $CFG;
		$query = "SELECT gr.*
			FROM {$CFG->prefix}groups AS gr 
			WHERE gr.courseid = $courseid
			ORDER BY gr.timecreated ASC";
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
