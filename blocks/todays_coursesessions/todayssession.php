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
 * @package    block
 * @subpackage  departments
 * @copyright  2012 Hemalatha arun <Hemaltha@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/lib.php');
require_once($CFG->dirroot . '/blocks/todays_coursesessions/lib.php');
require_once($CFG->dirroot .'/mod/attendance/locallib.php');

global $CFG, $DB;
require_login();

$systemcontext = context_system::instance();
// Get the admin layout.
$PAGE->set_pagelayout('admin');
$PAGE->set_heading(get_string('head_todays_sessions', 'block_todays_coursesessions'));
$PAGE->set_title(get_string('head_todays_sessions', 'block_todays_coursesessions'));
$PAGE->requires->js('/blocks/todays_coursesessions/js/jquery.min.js', true);
$PAGE->requires->js('/blocks/todays_coursesessions/js/Data.js', true);
$PAGE->requires->js('/blocks/todays_coursesessions/js/jquery.dataTables.min.js', true);
// $PAGE->requires->css('/blocks/todays_coursesessions/css/jquery.dataTables.min.css');
// Check the context level of the user and check weather the user is login to the system or not.
$PAGE->set_context($systemcontext);
$PAGE->set_url('/blocks/todays_coursesessions/todayssession.php');
$PAGE->requires->js_call_amd('block_todays_coursesessions/table', 'load', array());
$PAGE->requires->js_call_amd('block_todays_coursesessions/tabs', 'load', array());
// Header and the navigation bar.
$PAGE->requires->jquery();
// $PAGE->navbar->add(get_string('todays_sessions', 'block_todays_coursesessions'));
echo $OUTPUT->header();
// Manage_dept heading.
global $DB , $CFG, $USER;
    $today = date('Y-m-d');
    $insquerys = array();
    $data1 = array();
    $params = array();
    $params['editingteacher'] = 'editingteacher';
    $params['userid'] = $USER->id;
         $courses = "SELECT   c.*
                   FROM {user} u
                   JOIN {role_assignments} ra ON ra.userid = u.id
                   JOIN {role} r ON r.id = ra.roleid AND r.shortname = :editingteacher
                   JOIN {context} ctx ON ctx.id = ra.contextid
                   JOIN {course} c ON c.id = ctx.instanceid
                   JOIN {attendance} a ON a.course = c.id
                  WHERE u.id = :userid";

    $insquerys = $DB->get_records_sql($courses, $params);

     $day = date('D', strtotime($today));

    $currentdayformat = '';
    // Here we took switch statement to change the cureent day into singal word.
switch ($day) {
    case "Mon":
        $currentdayformat = 'M';
        break;
    case "Tue":
        $currentdayformat = 'TU';
        break;
    case "Wed":
        $currentdayformat = 'W';
        break;
    case "Thu":
        $currentdayformat = 'TH';
        break;
    case "Fri":
        $currentdayformat = 'F';
        break;
    case "Sat":
        $currentdayformat = 'SA';
        break;
    case "Sun":
        $currentdayformat = 'SU';
        break;
}
foreach ($insquerys as $insquery) {
    $line1 = array();
    $class = $DB->get_record('course', array('id' => $insquery->id));
    if ($class) {
        $line1['courses'] = $class->fullname;
        // End of timings.
        $coursmodules = $DB->get_records_sql("SELECT cm.id
                                                FROM {course_modules} cm
                                                JOIN {modules} m ON cm.module = m.id
                                               WHERE m.name = 'attendance'
                                                     AND cm.course = $insquery->id");
        foreach ($coursmodules as $takes) {
            $page = $takes->id;
        }
        $line1['pagesid'] = $page;
    }
    if ($currentdayformat) {
        $attendanceid = $DB->get_field('attendance', 'id', array('course' => $insquery->id));
        $dates = $DB->get_records_sql("SELECT *
                                         FROM {attendance_sessions} ass
                                        WHERE attendanceid = $attendanceid
                                              AND DATE(FROM_UNIXTIME(ass.sessdate, '%Y-%m-%d')) = DATE(NOW() - INTERVAL 0 DAY )");
        foreach ($dates as $date) {
            $line1['dates'] = attendance_construct_session_time($date->sessdate, $date->duration);
            $data1[] = $line1;
        }
    }
}
if ($currentdayformat) {
    $context = [
        'obj' => $data1,
    ];
    echo $OUTPUT->render_from_template('block_todays_coursesessions/todays_sessionstable', $context);
}
echo $OUTPUT->footer();

