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
require_once($CFG->dirroot . '/blocks/todays_timetable/lib.php');
$PAGE->requires->jquery();
$PAGE->requires->js_call_amd('todays_timetable/table', 'load', array());
$PAGE->requires->js_call_amd('todays_timetable/jquery.dataTables');
$action = required_param('action', PARAM_TEXT);
$costcenterid = optional_param('costcenterid', '', PARAM_INT);
$date = optional_param('date', '', PARAM_RAW);
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
$viewall = '';
switch ($action) {
    case 'todayssessions':
          $today = date('Y-m-d');
          $todaysinsquerys = block_instructors_timetable_get_studentclasslist();
          $day = date('D', strtotime($today));
          $currentdayformat = block_instructors_timetable_get_dayformat($day);
          $todaysdata = block_instructors_timetable_get_tablecontent($currentdayformat, $todaysinsquerys, null, 1, 1);
              $cnt=0;
          foreach($todaysinsquerys as $querycourse){
            $attendanceid = $DB->get_field('attendance', 'id', array('course' => $querycourse->id));
            $dates = $DB->get_records_sql("SELECT *
                                             FROM {attendance_sessions} ass
                                            WHERE attendanceid = $attendanceid AND DATE(FROM_UNIXTIME(ass.sessdate,
                                                  '%Y-%m-%d')) = DATE(NOW() - INTERVAL 0 DAY )");
             foreach ($dates as $date) {
                  $cnt++;
              }
          }

            if($cnt >= 5){
            echo  $viewall."<span class='timetable_viewall' id='viewmore'
           ><a  style='padding-top:20px;float:right;' href='".$CFG->wwwroot.
            "/blocks/todays_timetable/todayssession.php' target='_blank'>".get_string('viewall', 'block_todays_timetable');"</a></span>";
            }
    exit;
    break;
    case 'previoussessions':
          $previousdata = block_instructors_timetable_get_pretablecontent();
          $previouscount=count($previousdata);

          if($previousdata >= 5){
          echo $viewall."<span class='timetable_viewall' id='viewmore'>
          <a style='padding-top:20px;float:right;' href='".$CFG->wwwroot."/blocks/todays_timetable/previoussessions.php' target='_blank'>"
          .get_string('viewall', 'block_todays_timetable');"</a></span>";
          }
    exit;
    break;
    case 'addnewsessions':
          $addnewsessions = block_instructors_timetable_addnewsession_table();
          $addcount=count($addnewsessions);
          // print_object($addnewsessions);die;
          if($addnewsessions >= 5){
          echo  $viewall."<span class='timetable_viewall' id='viewmore'>
          <a style='padding-top:20px;float:right;' href='".$CFG->wwwroot."/blocks/todays_timetable/add_newsessions.php' target='_blank'>".
          get_string('viewall', 'block_todays_timetable');"</a></span>";
          }
    exit;
    break;
}

