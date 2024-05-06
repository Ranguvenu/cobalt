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

global $CFG,$USER, $DB, $PAGE;
$PAGE->requires->jquery();

$id = optional_param('id', $USER->id, PARAM_INT);
// print_r($id);exit;
$PAGE->set_url('/local/employees/profile.php', array('id' => $id));
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
$PAGE->requires->js_call_amd('local_employees/newemployees', 'load', array());
$PAGE->requires->js_call_amd('local_costcenter/newcostcenter', 'load', array());
// $PAGE->requires->js_call_amd('local_employees/datatablesamd', 'load', array());
$PAGE->requires->js_call_amd('local_employees/popup', 'Datatable', array());
$PAGE->requires->js_call_amd('local_employees/popup', 'init', array(array('contextid' => $systemcontext->id,
    'selector' => '.rolesuserpopup')));
$PAGE->requires->js_call_amd('local_employees/studentpopup', 'Datatable', array());
$PAGE->requires->js_call_amd('local_employees/studentpopup', 'init', array(array('contextid' => $systemcontext->id,
    'selector' => '.rolesstudentpopup')));
$PAGE->requires->js_call_amd('local_employees/coursepopup', 'Datatable', array());
$PAGE->requires->js_call_amd('local_employees/coursepopup', 'init', array(array('contextid' => $systemcontext->id,
    'selector' => '.rolescoursepopup')));

require_login();

$PAGE->set_title(get_string('viewprofile', 'local_employees'));
if (($id != $USER->id) AND (!(is_siteadmin() OR has_capability('local/costcenter:manage_multiorganizations',$systemcontext)))) {
    $issupervisor = $DB->record_exists('user',array('id'=> $id, 'open_supervisorid' => $USER->id));
    if(has_capability('local/employees:create',$systemcontext) || $issupervisor){
        $usercostcenter = $DB->get_field('user', 'open_costcenterid', array('id'=>$id));
        $managercostcenter = $USER->open_costcenterid;

        $userdepartment = $DB->get_field('user', 'open_departmentid', array('id'=>$id));
        $managerdepartment = $USER->open_departmentid;
        if ($usercostcenter != $managercostcenter) {
            throw new moodle_exception(get_string('nopermission', 'local_employees'));
        }elseif(!has_capability('local/costcenter:manage_ownorganization', $systemcontext) && has_capability('local/costcenter:manage_owndepartments', $systemcontext)&&$userdepartment != $managerdepartment){
            throw new moodle_exception(get_string('nopermission', 'local_employees'));
        }
    } else {
        throw new moodle_exception(get_string('nopermission', 'local_employees'));
    }
}
    echo $OUTPUT->header();
    $renderer   = $PAGE->get_renderer('local_employees');
    echo $renderer->employees_profile_view($id);
    echo $OUTPUT->footer();
