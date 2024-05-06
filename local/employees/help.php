<?php
/**
 * This file is part of eAbyas
 *
 * Copyright eAbyas Info Solutons Pvt Ltd, India
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author eabyas  <info@eabyas.in>
 * @package
 * @subpackage local_employees
 */

require_once(dirname(__FILE__) . '/../../config.php');
global $CFG, $DB;
$systemcontext = get_context_instance(CONTEXT_SYSTEM);
$PAGE->set_context($systemcontext);
$PAGE->set_url('/local/employees/help.php');
$PAGE->set_pagelayout('standard');
$strheading = get_string('pluginname', 'local_employees') . ' : ' . get_string('manual', 'local_employees');
$PAGE->set_title($strheading);
if(!(has_capability('local/employees:manage', $systemcontext) && has_capability('local/employees:create', $systemcontext))){
	echo print_error('nopermissions');
}
if ($CFG->forcelogin) {
    require_login();
} else {
    user_accesstime_log();
}
$PAGE->navbar->add(get_string('pluginname', 'local_employees'), new moodle_url('/local/employees/index.php'));
$PAGE->navbar->add(get_string('uploadusers', 'local_employees'), new moodle_url('/local/employees/syncs/hrms_async.php'));
$PAGE->navbar->add(get_string('manual', 'local_employees'));
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('manual', 'local_employees'));
if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('helpmanual', 'local_employees'));
    // echo '<div style="float:right;"><a href="syncs/hrms_async.php"><button>' . get_string('back_upload', 'local_employees') . '</button></a></div>';
    echo '<div style="float:right;"><a href="syncs/hrms_async.php" class="btn btn-primary">' . get_string('back_upload', 'local_employees') . '</a></div>';
}
$country = get_string_manager()->get_list_of_countries();
$countries = array();
foreach ($country as $key => $value) {
    $countries[] = $key . ' => ' . $value;
}
$select = new single_select(new moodle_url('#'), 'proid', $countries, null, '');
$select->set_label('');
$countries = $OUTPUT->render($select);
$choices = core_date::get_list_of_timezones($CFG->forcetimezone);
$timezone = array();
foreach ($choices as $key => $value) {
    $timezone[] = $key . ' => ' . $value;
}
$select = new single_select(new moodle_url('#'), 'proid', $timezone, null, '');
$select->set_label('');
$timezones = $OUTPUT->render($select);
$labelstring = get_config('local_costcenter');
// print_object($labelstring);exit;
$string = new \stdClass();
$string->countries = $countries;
$string->timezones = $timezones;
$string->firstlevel = $labelstring->firstlevel;
$string->secondlevel = $labelstring->secondlevel;
$string->thirdlevel = $labelstring->thirdlevel;

if(is_siteadmin() || has_capability('local/costcenter:manage_multiorganizations',$systemcontext)){
	echo get_string('help_1', 'local_employees', $string);
    echo get_string('help_2', 'local_employees', $string);
}else if(!is_siteadmin() && has_capability('local/costcenter:manage_ownorganization',$systemcontext)){
	echo get_string('help_1_orghead', 'local_employees', $string);
    echo get_string('help_2_orghead', 'local_employees', $string);
}else if(!is_siteadmin() && has_capability('local/costcenter:manage_owndepartments',$systemcontext)){
	echo get_string('help_1_dephead', 'local_employees', $string);
    echo get_string('help_2_dephead', 'local_employees', $string);
}else if(!is_siteadmin() && has_capability('local/costcenter:manage_ownsubdepartments',$systemcontext)){
    echo get_string('help_1_subdephead', 'local_employees', $string);
    echo get_string('help_2_subdephead', 'local_employees', $string);
}
echo $OUTPUT->footer();
?>
