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
 * @subpackage local_users
 */

require_once(dirname(__FILE__) . '/../../config.php');
global $CFG, $DB;
$systemcontext = get_context_instance(CONTEXT_SYSTEM);
$PAGE->set_context($systemcontext);
$PAGE->set_url('/local/users/help.php');
$semid = optional_param('tlid','',PARAM_INT);
// print_r($semid);exit;
$PAGE->set_pagelayout('standard');
$strheading = get_string('pluginname', 'local_users') . ' : ' . get_string('manual', 'local_users');
$PAGE->set_title($strheading);
// if(!(has_capability('local/users:manage', $systemcontext) && has_capability('local/users:create', $systemcontext))){
//     echo print_error('nopermissions');
// }
if ($CFG->forcelogin) {
    require_login();
} else {
    user_accesstime_log();
}

$PAGE->navbar->add(get_string('pluginname', 'local_timetable'), new moodle_url('/local/timetable/individual_session.php?tlid='.$semid));

$PAGE->navbar->add(get_string('uploadtimetable', 'local_timetable'), new moodle_url('/local/timetable/sync/hrms_async.php?semid='.$semid.'&action=1'));
$PAGE->navbar->add(get_string('manual', 'local_users'));
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('manual', 'local_users'));
if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('helpmanual', 'local_users'));
    // echo $OUTPUT->box(get_string('manualnote', 'local_timetable'));
    echo '<div style="float:right;"><a href="sync/hrms_async.php?semid='.$semid.'&action=1" class="btn btn-primary">' . get_string('back_upload', 'local_users') . '</a></div>';
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

if(is_siteadmin() || has_capability('local/costcenter:manage_multiorganizations',$systemcontext)){
    echo get_string('help_1', 'local_timetable', array('countries' => $countries, 'timezones' => $timezones));
}else if(!is_siteadmin() && has_capability('local/costcenter:manage_ownorganization',$systemcontext)){
    echo get_string('help_1_orghead', 'local_timetable', array('countries' => $countries, 'timezones' => $timezones));
}else if(!is_siteadmin() && has_capability('local/costcenter:manage_owndepartments',$systemcontext)){
    echo get_string('help_1_dephead', 'local_timetable', array('countries' => $countries, 'timezones' => $timezones));
}else if(!is_siteadmin() && has_capability('local/costcenter:manage_ownsubdepartments',$systemcontext)){
    echo get_string('help_1_subdephead', 'local_timetable', array('countries' => $countries, 'timezones' => $timezones));
}
echo get_string('help_2', 'local_timetable', array('countries' => $countries, 'timezones' => $timezones));
echo $OUTPUT->footer();
