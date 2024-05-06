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
 * @author  Moodle India
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
require_once(dirname(__FILE__) . '/../../config.php');
global $DB, $PAGE, $CFG;
$systemcontext = context_system::instance();
$format = optional_param('format', '', PARAM_TEXT);
$PAGE->set_url($CFG->wwwroot . '/local/admissions/programs.php?format='.$format);
$PAGE->set_title(get_string('manage_admissions', 'local_admissions'));
if (isloggedin()) {
    redirect($CFG->wwwroot); 
} else {
    $PAGE->set_pagelayout('secure');
    $PAGE->set_context($systemcontext);
    $PAGE->set_heading(get_string('manage_admissions', 'local_admissions'));

    $renderer = $PAGE->get_renderer('local_admissions');

    $programsresult = $renderer->admission_programs();
    if (count($programsresult) > 0) {
        $programscontent = array_values($programsresult);
    } else {
        $programscontent = false;
    }
    $finaldata = [
        'programs' => $programscontent,
        'cfgroot' => $CFG->wwwroot,
    ];
    echo $OUTPUT->header();
    echo $OUTPUT->render_from_template('local_admissions/programs', $finaldata);
    echo $OUTPUT->footer();
}
