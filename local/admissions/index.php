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
 * @package local_admissions
 * @author  eAbyas Info Solutions
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
require_once(dirname(__FILE__) . '/../../config.php');
global $OUTPUT, $CFG, $PAGE, $DB;
require_once($CFG->dirroot . '/local/users/lib.php');
require_once($CFG->dirroot . '/local/admissions/lib.php');


if(isloggedin()) {
    redirect($CFG->wwwroot);
} else {

    $PAGE->set_url($CFG->wwwroot . '/local/admissions/index.php?format=list');
    $PAGE->set_title(get_string('manage_admissions', 'local_admissions'));
    $PAGE->requires->js_call_amd('local_admissions/semestertabs', 'dataArguments');
    $PAGE->requires->js_call_amd('local_admissions/newadmission', 'load', array());
    $systemcontext = context_system::instance();
    $PAGE->set_context($systemcontext);
    $PAGE->set_pagelayout('secure');
    $heading = get_string('manage_admissions', 'local_admissions');
    $PAGE->set_heading($heading);
    $PAGE->navbar->add($heading);
    $renderer = $PAGE->get_renderer('local_admissions');
    // $currenttime = time();
    $currtime = date('d-M-Y', time());
    $currenttime = strtotime($currtime);
    $programidsarray = array();
    $levelidsarray = array();

    $progidssql = "SELECT lpl.programid FROM {local_program_levels} as lpl
                    JOIN {local_program} as lp ON lp.id = lpl.programid
                    WHERE lp.enddate >= {$currenttime} AND lp.hasadmissions = 1";
    $programidsarray = $DB->get_fieldset_sql($progidssql);
    if ($programidsarray) {
        $programids = implode(',', $programidsarray);
        $levelidssql = "SELECT id FROM {local_program_levels} WHERE programid IN($programids)";
        $levelidsarray = $DB->get_fieldset_sql($levelidssql);
    }

    $progres = $renderer->programsdata($programidsarray, $levelidsarray);

    $finaldata = [
        'programs' => $progres,
        'configwwwroot' => $CFG->wwwroot,
    ];

    echo $OUTPUT->header();
    echo $OUTPUT->render_from_template('local_admissions/index', $finaldata);
    echo $OUTPUT->footer();
}
