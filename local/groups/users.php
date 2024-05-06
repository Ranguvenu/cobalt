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
 * @package    local_groups
 * @copyright  2022 eAbyas Info Solutions Pvt. Ltd.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
global $CFG, $DB, $PAGE, $USER, $OUTPUT;
require_once($CFG->dirroot.'/local/program/lib.php');
require_login();
use local_program\program;
$programid = required_param('bcid', PARAM_INT);
$download = optional_param('download', 0, PARAM_INT);
$type = optional_param('type', '', PARAM_RAW);
$search = optional_param_array('search', '', PARAM_RAW);
require_login();
$context = context_system::instance();
$program = $DB->get_record('local_program', array('id' => $programid));
if (empty($program)) {
    throw new moodle_exception(get_string('programsnotfound', 'local_groups'));
}
if ((has_capability('local/program:manageprogram', context_system::instance()))
        && (!is_siteadmin() && (!has_capability('local/program:manage_multiorganizations', context_system::instance())
            && !has_capability('local/costcenter:manage_multiorganizations', context_system::instance())))) {
    if ($program->costcenter != $USER->open_costcenterid) {
        throw new moodle_exception(get_string('donthavepermissions', 'local_groups'));
    }

    if ((has_capability('local/program:manage_owndepartments', context_system::instance())
     || has_capability('local/costcenter:manage_owndepartments', context_system::instance()))) {
        if ($program->department != $USER->open_departmentid) {
            throw new moodle_exception(get_string('donthavepermissions', 'local_groups'));
        }
    }
}
$PAGE->set_context($context);
$url = new moodle_url($CFG->wwwroot . '/local/groups/users.php', array('bcid' => $programid));
$PAGE->requires->js_call_amd('local_program/program', 'GroupsDatatable',
                    array(array('programid' => $programid)));
$renderer = $PAGE->get_renderer('local_program');
$PAGE->set_url($url);
$PAGE->navbar->add(get_string("cohorts", 'local_groups'), new moodle_url('/local/groups/index.php'));
$PAGE->navbar->add(get_string("users", 'local_program'));
$batch = $DB->get_record_sql("SELECT c.id,c.name
                               FROM {cohort} c
                               JOIN {local_program} p ON c.id=p.batchid
                              WHERE p.id=$program->id");
$PAGE->set_heading($batch->name);

echo $OUTPUT->header();
$stable = new stdClass();
$stable->thead = true;
$stable->start = 0;
$stable->length = -1;
$stable->search = '';
$stable->programid = $programid;
$stable->batchid = $batch->id;
echo $renderer->viewprogramusers($stable, true);
echo $OUTPUT->footer();
