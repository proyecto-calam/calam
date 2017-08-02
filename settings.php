<?php
defined('MOODLE_INTERNAL') || die;

$settings->add(new admin_setting_heading(
            'headerconfig',
            get_string('headerconfig', 'block_calam'),
            get_string('descconfig', 'block_calam')
        ));

$settings->add(new admin_setting_configtext('calam/window_time',
         get_string('windowtime', 'block_calam'),
	 get_string('windowtime_desc', 'block_calam'),
         1800, PARAM_INT));

$allroles = role_fix_names(get_all_roles(), null, ROLENAME_ORIGINALANDSHORT, true);
$settings->add(new admin_setting_configmultiselect('calam/allowed_roles', 
	get_string('allowedroles', 'block_calam'),
	get_string('allowedroles_desc', 'block_calam'),
	array(), $allroles));

$start_days = array(get_string('startday', 'block_calam'));
for($i = 1; $i < 32; $i++)  {
	array_push($start_days,$i);
}
$settings->add(new admin_setting_configselect('calam/start_day',
         get_string('startday', 'block_calam'),
	 get_string('startday_desc', 'block_calam'),
         0, $start_days));

$start_months = array(get_string('startmonth', 'block_calam'));
for($i = 1; $i <= 12; $i++)  {
	array_push($start_months, get_string('month_'.$i, 'block_calam'));
}
$settings->add(new admin_setting_configselect('calam/start_month',
         get_string('startmonth', 'block_calam'),
	 get_string('startmonth_desc', 'block_calam'),
         0, $start_months));

$year_s = 2010;
$year_f = (int)date ("Y");  

$start_years = array(get_string('startyear', 'block_calam'));
for($i = $year_s; $i <= $year_f; $i++)  {
	$start_years[$i] = $i;
}
$settings->add(new admin_setting_configselect('calam/start_year',
         get_string('startyear', 'block_calam'),
	 get_string('startyear_desc', 'block_calam'),
         0, $start_years));








