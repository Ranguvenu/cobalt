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
 * @package    block_students_attendance
 * @copyright  2022 eAbyas Info Solutions Pvt. Ltd.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/lib.php');
require_once($CFG->dirroot .'/mod/attendance/locallib.php');
require_once($CFG->dirroot.'/local/includes.php');
global $CFG, $DB;
require_login();

$systemcontext = context_system::instance();
// Get the admin layout.
$PAGE->requires->js_call_amd('local_employees/studentpopup', 'init', array(array('contextid' => $systemcontext->id, 'selector' => '.rolesstudentpopup')));

$PAGE->requires->js_call_amd('block_teacher_courses/table', 'load', array());
// Check the context level of the user and check weather the user is login to the system or not.
$PAGE->set_context($systemcontext);
$PAGE->set_url('/blocks/teacher_courses/teachercourses.php');
$PAGE->set_heading(get_string('teachercourse', 'block_teacher_courses'));
$PAGE->set_title(get_string('teachercourse', 'block_teacher_courses'));

require_once($CFG->dirroot . '/blocks/teacher_courses/lib.php');
require_once($CFG->dirroot . '/blocks/teacher_courses/filters_form.php');

echo $OUTPUT->header();
    $renderer = $PAGE->get_renderer('block_teacher_courses');
    $filterparams = $renderer->teachers_content(true);
    $mform = new filters_form(null, array('filterlist' => array('teacher_courses'), 'filterparams' => $filterparams));
    if ($mform->is_cancelled()) {
        redirect($CFG->wwwroot . '/blocks/teacher_courses/studentcourses.php');
    }

echo '<a class="btn-link btn-sm d-flex align-items-center filter_btn" href="javascript:void(0);" data-toggle="collapse" data-target="#local_courses-filter_collapse" aria-expanded="false" aria-controls="local_courses-filter_collapse">
           <span class="filter mr-2">Filters</span>
        <i class="m-0 fa fa-sliders fa-2x" aria-hidden="true"></i>

      </a>';
echo  '<div class="collapse '.$show.'" id="local_courses-filter_collapse">
            <div id="filters_form" class="card card-body p-2">';
                $mform->display();
echo        '</div>
        </div>';
echo $OUTPUT->render_from_template('block_teacher_courses/global_filter', $filterparams);

echo $renderer->openteachers_content();
echo $OUTPUT->footer();
