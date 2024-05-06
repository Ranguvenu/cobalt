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
 * @subpackage  departments
 * @copyright  2012 Hemalatha arun <Hemaltha@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/lib.php');
require_once($CFG->dirroot . '/blocks/todays_timetable/lib.php');
global $DB, $CFG, $USER, $PAGE, $OUTPUT;
require_login();
$systemcontext = context_system::instance();
// Get the admin layout.
$PAGE->set_pagelayout('admin');
$PAGE->requires->js('/blocks/todays_timetable/js/jquery.min.js', true);
$PAGE->requires->js('/blocks/todays_timetable/js/Data.js', true);
$PAGE->requires->js('/blocks/todays_timetable/js/jquery.dataTables.min.js', true);
$PAGE->requires->css('/blocks/todays_timetable/css/jquery.dataTables.min.css');
// Check the context level of the user and check weather the user is login to the system or not.
$PAGE->set_context($systemcontext);
$heading = get_string('addsession', 'block_todays_timetable');
$PAGE->set_heading($heading);
$PAGE->set_url('/blocks/todays_timetable/add_newsessions.php');
$PAGE->set_title(get_string('add_newsession', 'block_todays_timetable'));
// Header and the navigation bar.
$PAGE->requires->jquery();
$PAGE->navbar->add(get_string('add_newsession', 'block_todays_timetable'));

require_once($CFG->dirroot . '/blocks/todays_timetable/lib.php');
require_once($CFG->dirroot . '/blocks/todays_timetable/filters_form.php');
echo $OUTPUT->header();
        $show ='';
        $renderer = $PAGE->get_renderer('block_todays_timetable');
        $filterparams = $renderer->add_courses_content(true);
        $mform = new filters_form(null, array('filterlist' => array('add_course'), 'filterparams' => $filterparams));
            if ($mform->is_cancelled()) {
                redirect($CFG->wwwroot . '/blocks/todays_timetable/add_newsessions.php');
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

    echo $OUTPUT->render_from_template('block_todays_timetable/global_filter', $filterparams);
    echo $renderer->add_courses_content();
    echo $OUTPUT->footer();
