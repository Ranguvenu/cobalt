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
 * Version information
 *
 * @package    local_costcenter
 * @copyright  2022 eAbyas Info Solutions Pvt. Ltd.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('../../config.php');
require_once($CFG->dirroot.'/local/costcenter/lib.php');
require_once($CFG->dirroot.'/local/costcenter/renderer.php');

$id = required_param('id', PARAM_INT);
global $DB, $OUTPUT, $CFG, $PAGE;

require_login();
$systemcontext = context_system::instance();

if (!has_capability('local/costcenter:view', $systemcontext)) {
    throw new moodle_exception('nopermissiontoviewpage');
}

if (!$depart = $DB->get_record('local_costcenter', array('id' => $id))) {
    throw new moodle_exception('invalidschoolid');
}

if (!is_siteadmin() || !has_capability('local/costcenter:manage_multiorganizations', $systemcontext)) {
    if ($depart->parentid) {
        if (!($DB->record_exists('user', array('open_departmentid' => $id, 'id' => $USER->id)) ||
                has_capability('local/costcenter:manage_ownorganization', $systemcontext))) {
            throw new moodle_exception('nopermissiontoviewpage');
        }
    } else if (!$DB->record_exists('user', array('open_costcenterid' => $id, 'id' => $USER->id))) {
        throw new moodle_exception('nopermissiontoviewpage');
    }
}

$PAGE->requires->js_call_amd('local_costcenter/costcenterdatatables', 'costcenterDatatable', array());
$PAGE->requires->js_call_amd('local_costcenter/newcostcenter', 'load', array());
$PAGE->requires->js_call_amd('theme_bloom/quickactions', 'quickactionsCall');

$PAGE->set_pagelayout('standard');
$PAGE->set_context($systemcontext);
$PAGE->set_url('/local/costcenter/costcenterview.php?id='.$id);
$PAGE->navbar->ignore_active();
$labelstring = get_config('local_costcenter');
if (is_siteadmin() || has_capability('local/costcenter:manage_multiorganizations', $systemcontext)) {
    $PAGE->navbar->add(get_string('orgmanage', 'local_costcenter', $labelstring), new moodle_url('/local/costcenter/index.php'));
}
if (!((is_siteadmin()) || has_capability('local/costcenter:manage_multiorganizations', $systemcontext) ||
        has_capability('local/costcenter:manage_ownorganization', $systemcontext))) {
    if ($USER->open_departmentid != $id) {
        redirect($CFG->wwwroot . '/local/costcenter/costcenterview.php?id='.$USER->open_departmentid);
    }
}

if ($depart->parentid) {
    if (!has_capability('local/costcenter:manage_owndepartments', $systemcontext) || is_siteadmin()) {
        $PAGE->navbar->add($DB->get_field('local_costcenter', 'fullname', array('id' => $depart->parentid)),
            new moodle_url('/local/costcenter/costcenterview.php', array('id' => $depart->parentid)));
        $PAGE->navbar->add(get_string('viewsubdepartments', 'local_costcenter', $labelstring));
    }
    $PAGE->set_heading(get_string('department_structure', 'local_costcenter',$labelstring));
    $PAGE->set_title(get_string('department_structure', 'local_costcenter', $labelstring));
} else {
    $PAGE->navbar->add(get_string('viewcostcenter', 'local_costcenter', $labelstring));
    $PAGE->set_heading(get_string('orgStructure', 'local_costcenter', $labelstring));
    $PAGE->set_title(get_string('orgStructure', 'local_costcenter', $labelstring));
}

echo $OUTPUT->header();
$renderer = $PAGE->get_renderer('local_costcenter');
echo $renderer->get_dept_view_btns($id);
if ($depart->parentid) {
    echo $renderer->department_view($id, $systemcontext);
} else {
    echo $renderer->costcenterview($id, $systemcontext);
}
echo $OUTPUT->footer();
