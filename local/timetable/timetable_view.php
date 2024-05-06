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
 * List the tool provided in a course
 *
 * @package    local
 * @subpackage  Timetable
 * @copyright  2012 Hemalatha arun <Hemaltha@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/lib.php');
require_once($CFG->dirroot . '/local/timetable/lib.php');

global $CFG, $DB, $USER, $OUTPUT;
$semid = optional_param('semid', 0, PARAM_INT);
$systemcontext =  context_system::instance();
$PAGE->set_pagelayout('standard');
$PAGE->set_context($systemcontext);
require_login();
$PAGE->set_url('/local/timetable/timetavle_view.php?text=default&semid='.$semid);
if (is_siteadmin()) {
    $PAGE->set_title(get_string('timetable_view', 'local_timetable'));
    $PAGE->set_heading(get_string('calendarview', 'local_timetable'));
    $PAGE->navbar->add(get_string('timetablelayout', 'local_timetable'), new moodle_url('/local/timetable/timelayoutview.php'));
    $PAGE->navbar->add(get_string('individualsession', 'local_timetable'), new moodle_url('/local/timetable/individual_session.php?tlid='.$semid));
} else {
    $student = identify_role($USER->id);
    if ($student->shortname == 'student') {
        $PAGE->set_title(get_string('pluginname', 'local_timetable'));
        $PAGE->set_heading(get_string('pluginname', 'local_timetable'));
    } else {
        $PAGE->set_title(get_string('timetable_view', 'local_timetable'));
        $PAGE->set_heading(get_string('calendarview', 'local_timetable'));
        $PAGE->navbar->add(get_string('individualsession', 'local_timetable'), new moodle_url('/local/timetable/individual_session.php?tlid='.$semid));
    }
}
$PAGE->navbar->add(get_string('view', 'local_timetable'), new moodle_url('/local/timetable/timelayoutedit.php'));
$PAGE->requires->jquery();
$PAGE->requires->js('/local/timetable/js/event-calendar.min.js', true);
$PAGE->requires->js('/local/timetable/js/custom.js', true);
$PAGE->requires->css('/local/timetable/css/event-calendar.min.css');

echo $OUTPUT->header();
$renderer = $PAGE->get_renderer('local_timetable');
echo $renderer->render_timetable();
echo $OUTPUT->footer();
