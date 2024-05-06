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
 * Form for editing HTML block instances.
 *
 * @package   block_myattendance
 * @copyright 1999 onwards Martin Dougiamas (http://dougiamas.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_myattendance extends block_base {

  function init() {

       $this->title = get_string('pluginname', 'block_myattendance');
 }
       function specialization() {

       if (isset($this->config))
      {
      if (empty($this->config->title))
      {
          $this->title = get_string('pluginname', 'block_myattendance');
      } else {
         $this->title = $this->config->title;
      }

      if (empty($this->config->text)) {
      $this->config->text = get_string('plugintext', 'block_myattendance');
      }
  }
}


function has_config() {
  return true;
}
  function get_content(){
      global $PAGE,$OUTPUT,$DB,$USER;
        require_login();
      if ($this->content !== NULL) {
        return $this->content;
      }
//manager and teacher view
$courselist= $DB->get_records_sql('SELECT c.fullname,a.id FROM {enrol} e
  JOIN {user_enrolments} ue ON ue.enrolid = e.id
  JOIN {course} c ON c.id = e.courseid
  JOIN {attendance} a On c.id=a.course');

 foreach ($courselist as $cour)
{
  $cmid = $DB->get_record('course_modules', array('course'=>$cour->id,'module'=>26));
  $line = array();
  $line[] = '<a href="/moodle4/mod/attendance/manage.php?id='.$cmid->id.'">' . $cour->fullname . '</a>';
  $course[] = $line;
 }

  $table = new html_table();

$context = context_system::instance($course->id);

if(has_capability('block/myattendance:addinstance', $context)){

 $table->data = $course;

}
              echo '</table>';
              $this->content = new stdClass;
              $this->content->text = html_writer::table($table);


//user view
  $table = new html_table();
  $table->id = block_Myattendance;
  $table->head = array(
    'coursename',
    'Attendance'
  );

$courses= $DB->get_records_sql('SELECT c.fullname,c.id FROM {enrol} e
JOIN {user_enrolments} ue ON ue.enrolid=e.id
JOIN {course} c ON c.id=e.courseid');

  $context = context_system::instance($course->id);

   if(!has_capability('block/myattendance:addinstance', $context)){

  foreach ($courses As $course)
  {
  $sql="SELECT COUNT(ats.id) FROM {attendance_sessions} ats
    JOIN {attendance} msc ON ats.attendanceid=msc.id
    WHERE msc.course =$course->id";

$totalcount = $DB->count_records_sql($sql);

$sql2="SELECT count(stat.id) FROM {attendance} AS a
JOIN {attendance_sessions} AS ats ON ats.attendanceid=a.id
JOIN {attendance_log} as al ON al.sessionid=ats.id
JOIN {attendance_statuses} stat ON stat.id=al.statusid
where a.course=$course->id and al.studentid=$USER->id and stat.acronym IN ('P','L') ";

  $sessionuser=$DB->count_records_sql($sql2);

  $attendancecount=$sessionuser.' / '.$totalcount;
                  $table->data[]=array($course->fullname,$attendancecount);
              }
                echo '</table>';
                $this->content = new stdClass;
                $this->content->text = html_writer::table($table);
                }
}
}
