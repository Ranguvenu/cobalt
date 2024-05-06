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
 * displaying scheduled todays session and previous session
 *
 * @package    block_student_todays_timetable
 * @copyright  2015 hemalatha c arun {hemalatha@eabyas.in}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_student_todays_timetable extends block_base {
    // Initialisation of block.
    public function init() {
        $this->title = get_string('pluginname', 'block_student_todays_timetable');
    }
    // Return the content of this block.

    public function get_content() {
        if ($this->content !== null) {
            return $this->content;
        }
        global $CFG, $USER, $DB, $OUTPUT;
        require_once($CFG->dirroot . '/blocks/student_todays_timetable/lib.php');
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
        $this->content = new stdClass;
        $this->content->text = array();
        $todaydate = date('Y-M-d D');
        // Only for student.
        $today = date('Y-m-d');
        // Here we are calling the function in which the query was written.
        $courses = block_student_todays_timetable_get_studentclasslists();

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
            $data = block_student_todays_timetable_get_tablecontent($currentdayformat,
                $userid);
        }
        if(count($data) >= 5) {
            $viewmore = true;
        } else {
            $viewmore = false;
        }
        $filterparams = (object) [
            'data' => $data,
            'viewmore' => $viewmore,
            'cfgwwwroot' => $CFG->wwwroot,
        ];
        $users = $DB->get_records_sql("SELECT DISTINCT(r.id),r.shortname FROM {role} r
                JOIN {role_assignments} ra ON ra.roleid = r.id
                JOIN {user} u ON u.id = ra.userid
                WHERE u.id = {$USER->id} AND r.shortname = 'student'");
        if($users){
            $this->content = new \stdClass();
            $this->content->text = $OUTPUT->render_from_template('block_student_todays_timetable/ftable', $filterparams);
        }

    }

}
