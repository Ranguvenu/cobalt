<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
/**
*
* @author eAbyas <info@eabyas.in>
* @package odl
* @subpackage local_assigroles
*/



require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/' . $CFG->admin . '/roles/lib.php');
require_once($CFG->dirroot . '/local/assignroles/lib.php');
global $PAGE;
require_login();
$systemcontext = context_system::instance();
require_once($CFG->libdir.'/adminlib.php');
$PAGE->set_context($systemcontext);
$heading = get_string('assignroles', 'local_assignroles');
$PAGE->set_heading($heading);
$pageurl = new moodle_url('/local/assignroles/index.php', array());
$PAGE->set_url($pageurl);
$PAGE->set_pagelayout("standard");
$PAGE->set_title($heading);
$PAGE->navbar->add($heading);
$PAGE->requires->js_call_amd('local_assignroles/newassignrole', 'load', array());
$PAGE->requires->js_call_amd('local_assignroles/popup', 'Datatable', array());
if (!(is_siteadmin() || has_capability('local/costcenter:manage_multiorganizations', $systemcontext) || has_capability(
    'local/assignroles:manageassignroles', $systemcontext))) {
    throw new moodle_exception(get_string('errornopermission', 'local_assignroles'));
}
echo $OUTPUT->header();
$PAGE->requires->js_call_amd('local_assignroles/popup', 'init', array(array('contextid' => $systemcontext->id,
    'selector' => '.rolesuserpopup')));
echo $PAGE->get_renderer('local_assignroles')->display_roles();
echo $OUTPUT->footer();