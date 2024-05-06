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
 * @package   block_students_attendance
 * @copyright 2022 eAbyas Info Solutions Pvt. Ltd.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_students_attendance extends block_base {
    public function init() {
        $this->title = get_string('myattendance', 'block_students_attendance');
    }
    public function get_content() {
        global $OUTPUT, $DB, $USER, $COURSE, $CFG;
        require_login();
        if ($this->content !== null) {
            return $this->content;
        }
        require_once($CFG->dirroot.'/local/includes.php');


        $params = array();
        $params['student'] = 'student';
        $params['userid'] = $USER->id;
        $coursesql = "SELECT   c.id,c.fullname,pu.programid
                   FROM {user} u
                   JOIN {local_program_users} pu ON pu.userid=u.id
                   JOIN {role_assignments} ra ON ra.userid = pu.userid
                   JOIN {role} r ON r.id = ra.roleid AND r.shortname = :student
                   JOIN {context} ctx ON ctx.id = ra.contextid
                   JOIN {course} c ON c.id = ctx.instanceid
                   JOIN {attendance} a ON a.course = c.id
                  WHERE u.id = :userid";

        $courses = $DB->get_records_sql($coursesql, $params);
        $countcourse =count($courses);

        $data = array();
        $count = 0;
        foreach ($courses as $course) {
            $line = array();
            $class = $DB->get_record('course', array('id' => $course->id));
            
            $userpresentedsql = "SELECT alog.id,atst.acronym,
                                att.course as courseid 
                        FROM {attendance_sessions} as atses 
                        JOIN {attendance} as att 
                        ON att.id = atses.attendanceid
                        JOIN {attendance_statuses} as atst 
                        ON atst.attendanceid = att.id
                        JOIN {attendance_log} as alog 
                        ON alog.statusid = atst.id
                        WHERE att.course = $course->id 
                        AND atst.acronym IN ('P','L') 
                        AND alog.studentid = $USER->id GROUP BY alog.id ";

            $userpresented = $DB->get_records_sql($userpresentedsql);

            $userabsentedsql = "SELECT alog.id,atst.acronym,
                                att.course as courseid 
                        FROM {attendance_sessions} as atses 
                        JOIN {attendance} as att 
                        ON att.id = atses.attendanceid
                        JOIN {attendance_statuses} as atst 
                        ON atst.attendanceid = att.id
                        JOIN {attendance_log} as alog 
                        ON alog.statusid = atst.id
                        WHERE att.course = $course->id 
                        AND atst.acronym IN ('A','E') 
                        AND alog.studentid = $USER->id GROUP BY alog.id ";

            $userabsented = $DB->get_records_sql($userabsentedsql);


            $totalattdencesql = "SELECT alog.id,atst.acronym,
                                att.course as courseid 
                        FROM {attendance_sessions} as atses 
                        JOIN {attendance} as att 
                        ON att.id = atses.attendanceid
                        JOIN {attendance_statuses} as atst 
                        ON atst.attendanceid = att.id
                        JOIN {attendance_log} as alog 
                        ON alog.statusid = atst.id
                        WHERE att.course = $course->id 
                        AND atst.acronym IN ('P','A','E','L') 
                        AND alog.studentid = $USER->id GROUP BY alog.id";
            $totalattdence = $DB->get_records_sql($totalattdencesql);

            $presented=count($userpresented);
            $absented=count($userabsented);
            $total=count($totalattdence);
        
            $percentage = ($presented / $total * 100);

            if ($percentage == 0 || $percentage == 100) {
                $avgerageparticipants = intval($percentage );
            } else {
                $avgerageparticipants = number_format($percentage, 2, '.', '.');
            }
           
                $coursename=$class->fullname;
                if (strlen($class->fullname) > 30) {
                    $class->fullname = substr($class->fullname, 0, 30).'...';
                }
                if(empty($total)){
                    $avgerageparticipants =0;
                }
                
                $decimal = round($avgerageparticipants);
                $float1 = $avgerageparticipants;
                $two = $decimal.'.'.'00';
                if($float1 == $two){
                     $float1 = ceil($float1);
                }else{
                     $float1;
                }

                $line['cid'] = $course->id;
                $line['fullname'] = $class->fullname;
                $line['userpresented'] = $presented;
                $line['userabsented'] = $absented;
                $line['totalattdence'] = $total;
                $line['pecentage'] =$float1;
                $line['coursename'] =$coursename;
                $data[] = $line;

                $count++;
                if($count == 5){
                    break;
                }
        }
        if($countcourse  > 5){
            $counts = $countcourse;
        }
        $student = $DB->get_records_sql("SELECT DISTINCT(r.id),r.shortname 
                            FROM {role} r
                            JOIN {role_assignments} ra ON ra.roleid = r.id
                            JOIN {user} u ON u.id = ra.userid
                            WHERE u.id = {$USER->id} AND r.shortname = 'student'");
        $coursedata = [
            'coursecounts' => $counts,
            'courses' => $data,
        ];
        if ($student) {
            $this->content = new \stdClass();
            $this->content->text = $OUTPUT->render_from_template('block_students_attendance/index', $coursedata);
        }
        return $this->content;
    }
}
