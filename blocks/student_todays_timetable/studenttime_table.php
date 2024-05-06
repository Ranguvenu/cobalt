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
 * Competencies to review page.
 *
 * @package    block_assignments
 * @copyright  Moodle India
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
global $PAGE, $OUTPUT, $DB, $USER;
require_login();
$url = new moodle_url('/blocks/student_todays_timetable/studenttime_table.php');
$PAGE->set_url($url);
$PAGE->set_title(get_string('pluginname', 'block_student_todays_timetable'));
$PAGE->set_heading(get_string('pluginname', 'block_student_todays_timetable'));
$PAGE->navbar->add(get_string('home', 'block_student_todays_timetable'), new moodle_url('/my'));
$PAGE->navbar->add(get_string('pluginname', 'block_student_todays_timetable'));
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
$PAGE->requires->jquery();
$PAGE->requires->js_call_amd('block_student_todays_timetable/table', 'CustomClassDatatable', array());

require_once($CFG->dirroot . '/blocks/student_todays_timetable/lib.php');
require_once($CFG->dirroot . '/blocks/student_todays_timetable/filters_form.php');

 $params = array();
        $params['userid'] = $USER->id;
        $params['student'] = 'student';
        $sql = "SELECT DISTINCT(u.id),r.shortname
                  FROM {user} u
                  JOIN {role_assignments} ra ON ra.userid = u.id
                  JOIN {role} r ON r.id = ra.roleid
                 WHERE r.shortname = :student AND u.id = :userid";
        $users = $DB->get_records_sql($sql, $params);
        $context = \context_user::instance($USER->id);
        $systemcontext = context_system::instance();
        // $this->content = new stdClass;
        $content->text = array();
        $todaydate = date('Y-M-d D');
        // Only for student.
        $today = date('Y-m-d');
        // Here we are calling the function in which the query was written.
        $courses = block_student_todays_timetable_get_studentclasslist();

        $count = 0;
        foreach ($courses as $key) {
            $count++;
        }
        // You can test with this $courses='' to check if no available courses.
        // Checking wether the uaers are available or not.
        if ($count > 1) {
            // We are getting the current date.
            $day = date('D', strtotime($today));
            $data = array();
            // Here we are calling the function to get the day format which suits with database tables.
            $currentdayformat = block_student_todays_timetable_get_dayformat($day);
            // Here we are calling the function to diaplay the table data and also sending the current day format and users data.
            $userid = $USER->id;
            $data = block_student_todays_timetable_get_tablecontents($currentdayformat,
                $userid);

            if (!$data) {
                $data = "No Sessions are scheduled today";
            }
            // Here we are calling the function to print the table.
        }
        
echo $OUTPUT->header();
    if($users) {
        $renderer = $PAGE->get_renderer('block_student_todays_timetable');
        $filterparams = $renderer->course_content(true);
        $mform = new filters_form(null, array('filterlist' => array('subject', 'faculty'), 'filterparams' => $filterparams));
        if ($mform->is_cancelled()) {
            redirect($CFG->wwwroot . '/blocks/student_todays_timetable/studenttime_table.php');
        }

     echo '<a class="btn-link btn-sm d-flex align-items-center filter_btn" href="javascript:void(0);" data-toggle="collapse" data-target="#local_users-filter_collapse" aria-expanded="false" aria-controls="local_users-filter_collapse">
                <span class="filter mr-2">Filters</span>
            <i class="m-0 fa fa-sliders fa-2x" aria-hidden="true"></i>
          </a>';
    echo  '<div class="mt-2 mb-2 collapse '.$show.'" id="local_users-filter_collapse">
                <div id="filters_form" class="card card-body p-2">';
                    $mform->display();
    echo        '</div>
            </div><br>';

        echo $OUTPUT->render_from_template('block_student_todays_timetable/global_filter', $filterparams);
        echo $renderer->course_content();
    } else {
        throw new moodle_exception(get_string('permissiondenied', 'block_student_todays_timetable'));
    }

echo $OUTPUT->footer();
