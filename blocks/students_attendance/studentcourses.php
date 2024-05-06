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
global $DB , $CFG, $USER;
require_login();
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
$PAGE->set_url('/blocks/students_attendance/studentcourses.php');
$PAGE->set_heading(get_string('myattendance', 'block_students_attendance'));
$PAGE->set_title(get_string('myattendance', 'block_students_attendance'));
require_once($CFG->dirroot . '/blocks/students_attendance/lib.php');
require_once($CFG->dirroot . '/blocks/students_attendance/filters_form.php');

echo $OUTPUT->header();
        $renderer = $PAGE->get_renderer('block_students_attendance');
        $filterparams = $renderer->courses_content(true);
        $mform = new filters_form(null, array('filterlist' => array('student_courses'), 'filterparams' => $filterparams));
            if ($mform->is_cancelled()) {
                redirect($CFG->wwwroot . '/blocks/students_attendance/studentcourses.php');
            }
    echo '<a class="btn-link btn-sm d-flex align-items-center filter_btn" href="javascript:void(0);" data-toggle="collapse" data-target="#local_employees-filter_collapse" aria-expanded="false" aria-controls="local_employees-filter_collapse">
        <span class="filter mr-2">Filters</span>
            <i class="m-0 fa fa-sliders fa-2x" aria-hidden="true"></i>
          </a>';
    echo  '<div class="mt-2 mb-2 collapse '.$show.'" id="local_employees-filter_collapse">
                <div id="filters_form" class="card card-body p-2">';
                    $mform->display();
    echo        '</div>
            </div>';

    echo $OUTPUT->render_from_template('block_students_attendance/global_filter', $filterparams);
    echo $renderer->courses_content();
    echo $OUTPUT->footer();
