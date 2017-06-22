<?php
defined('MOODLE_INTERNAL') || die;

$settings->add(new admin_setting_heading(
            'headerconfig',
            get_string('headerconfig', 'block_calam'),
            get_string('descconfig', 'block_calam')
        ));


$settings->add(new admin_setting_configtext('calam/Window_Time',
         get_string('windowtime', 'block_calam'),
	 get_string('windowtime_desc', 'block_calam'),
         1800, PARAM_INT));


$allroles = role_fix_names(get_all_roles(), null, ROLENAME_ORIGINALANDSHORT, true);
$settings->add(new admin_setting_configmultiselect('calam/Allowed_Roles', 
	get_string('allowedroles', 'block_calam'),
	get_string('allowedroles_desc', 'block_calam'),
	array(), $allroles));

