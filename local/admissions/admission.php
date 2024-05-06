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
$programid = optional_param('id', '', PARAM_INT);

$PAGE->set_url($CFG->wwwroot . '/local/admissions/admission.php');
$PAGE->set_title(get_string('manage_admissions', 'local_admissions'));
if (isloggedin()) {
    redirect($CFG->wwwroot); 
} else {
    $PAGE->set_pagelayout('secure');
    $PAGE->set_context($systemcontext);
    $PAGE->requires->js_call_amd('local_admissions/semestertabs', 'semTabsCollection');

    $renderer = $PAGE->get_renderer('local_admissions');
    $programdata = $DB->get_record('local_program', array('id' => $programid));
    $PAGE->set_heading($programdata->name);
    $programsresult = $renderer->admission_prog_tabsdata($programid);
    $programsfaculty = $renderer->program_faculty($programid);

    foreach ($programsfaculty as $faculty) {
        $userpicture = new user_picture($faculty, array('size' => 60, 'class' => 'userpic', 'link' => false));
        $userpicture = $userpicture->get_url($PAGE);
        $faculty->teacherurl = $userpicture->out();
    }

    $finaldata = [
        'programlevels' => array_values($programsresult),
        'description' => html_entity_decode($programdata->description),
        'prerequisite' => $programdata->prerequisite,
        'programid' => $programid,
        'programfaculty' => array_values($programsfaculty),
        'configwwwroot' => $CFG->wwwroot,
    ];

    echo $OUTPUT->header();
    echo $OUTPUT->render_from_template('local_admissions/admission', $finaldata);
    echo $OUTPUT->footer();
}
