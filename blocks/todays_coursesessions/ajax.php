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
 * @subpackage  users
 * @copyright  2016 manikanta <manikantam@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../config.php');
require_login();
global $DB, $CFG, $USER, $PAGE, $OUTPUT;
require_once($CFG->dirroot . '/blocks/todays_coursesessions/lib.php');
$PAGE->requires->jquery();
$PAGE->requires->js_call_amd('todays_coursesessions/table', 'load', array());
$PAGE->requires->js_call_amd('todays_coursesessions/jquery.dataTables');
$action = required_param('action', PARAM_TEXT);
$costcenterid = optional_param('costcenterid', '', PARAM_INT);
$date = optional_param('date', '', PARAM_RAW);
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
$viewall = '';
          $today = date('Y-m-d');
          $todaysinsquerys = block_instructors_session_get_studentclasslist();
          $day = date('D', strtotime($today));
          $currentdayformat = block_instructors_session_get_dayformat($day);
          $todaysdata = block_instructors_session_get_tablecontent($currentdayformat, $todaysinsquerys, null, 1, 1);
          
            echo  $viewall."<spanclass='timetable_viewall'
            style='padding-top:20px;float:right;'><a href='".$CFG->wwwroot.
            "/blocks/todays_coursesessions/todayssession.php'>".get_string('viewall', 'block_todays_coursesessions');"</a></span>";
