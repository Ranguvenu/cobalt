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
 * @package    manage_departments
 * @subpackage  list of all functions which is used in departments plugin
 * @copyright  2015 K.V.Shriram Gupta <sriram.korada@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Class manage_dept contsins list of functions which is used by department plugin.
defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/local/lib.php');
require_once($CFG->dirroot .'/mod/attendance/locallib.php');

/**
 * Description: Course/Subject filter code
 * @param  [mform object]  $mform[the form object where the form is initiated]
 */
function student_courses_filter($mform){
    global $DB, $USER;
    $systemcontext = context_system::instance();
    $params = array('studentid' => $USER->id);
   $sql = "SELECT c.id,c.fullname 
             FROM {user} u
             JOIN {role_assignments} ra ON ra.userid = u.id
             JOIN {role} r ON r.id = ra.roleid AND r.shortname = 'student'
             JOIN {context} ctx ON ctx.id = ra.contextid
             JOIN {course} c ON c.id = ctx.instanceid
            WHERE u.id = :studentid";

    $courselist = $DB->get_records_sql_menu($sql, $params);

    $select = $mform->addElement('autocomplete', 'course', '', $courselist,
                                    array(
                                        'placeholder' => get_string('course','block_students_attendance')
                                    )
                                );
    $mform->setType('course', PARAM_RAW);
    $select->setMultiple(true);
}

function get_student_courses($stable, $filterdata) {
    global $DB, $USER, $CFG;

    $params = array();
    $params['studentid'] = $stable->studentid;

    $countsql = "SELECT count(c.id) ";

    $selectsql = " SELECT c.id,c.fullname,pu.programid ";

    $fromsql = " FROM {user} u
                 JOIN {local_program_users} pu ON pu.userid=u.id
                 JOIN {role_assignments} ra ON ra.userid = pu.userid
                 JOIN {role} r ON r.id = ra.roleid AND r.shortname = 'student'
                 JOIN {context} ctx ON ctx.id = ra.contextid
                 JOIN {course} c ON c.id = ctx.instanceid
                 JOIN {attendance} a ON a.course = c.id  ";

    $fromsql .= " WHERE u.id = :studentid ";

    
        // For "Global (search box)" filter.
        if (isset($filterdata->search_query) && trim($filterdata->search_query) != '') {
            $filteredquizzes = array_filter(explode(',', $filterdata->search_query));
            $quizzesarray = array();
            if (!empty($filteredquizzes)) {
                foreach ($filteredquizzes as $key => $value) {
                    $quizzesarray[] = " c.fullname LIKE '%".trim($value)."%'";
                }
                $imploderequests = implode(' OR ', $quizzesarray);
                $fromsql .= " AND ($imploderequests)";
            }
        }

    if (!empty($filterdata->course)) {
        $filteredquizzes = array_filter(explode(',', $filterdata->course), 'is_numeric');
        if(!empty($filteredquizzes)) {
            $quizzesarray = array();
            foreach($filteredquizzes as $key => $value) {
                $quizzesarray[] = "c.id = $value"; 
            }
            $quizzesimplode = implode(' OR ', $quizzesarray);
            $fromsql .= " AND ($quizzesimplode) ";
        }
    }

    $count = $DB->count_records_sql($countsql.$fromsql, $params);
   
    $courses = $DB->get_records_sql($selectsql.$fromsql, $params, $stable->start, $stable->length);
        $data = array();
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
                        ON alog.statusid=atst.id
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
                        ON alog.statusid=atst.id
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
                        ON alog.statusid=atst.id
                        WHERE att.course = $course->id 
                        AND atst.acronym IN ('P','A','E','L')
                        AND alog.studentid = $USER->id GROUP BY alog.id";
            $totalattdence = $DB->get_records_sql($totalattdencesql);

            $presented =count($userpresented);
            $absented =count($userabsented);
            $total =count($totalattdence);
        
            $percentage=($presented / $total * 100);

            if ($percentage == 0 || $percentage == 100) {
                $participantsaverage = intval($percentage );
            } else {
                $participantsaverage = number_format($percentage, 2, '.', '.');
            }
                if (strlen($class->fullname) > 20) {
                    $class->fullname = substr($class->fullname, 0, 20).'...';
                }
                if(empty($total)){
                    $participantsaverage =0;
                }

                $decimal = round($participantsaverage);
                $float1 = $participantsaverage;
                $two = $decimal.'.'.'00';
                
                if($float1 == $two){
                    $float1 = ceil($float1);
                }else{
                    $float1;
                }

                $line['cid'] = $course->id;
                $line['fullname'] = $class->fullname;
                $line['fullnames'] = $course->fullname;
                $line['userpresented'] = $presented;
                $line['userabsented'] = $absented;
                $line['totalattdence'] = $total;
                $line['pecentage'] =$float1;
                $line['cfg_url'] =$CFG->wwwroot;
                $data[] = $line;
        }

    $coursesContext = [
        'hascourses' => $data,
        'length' => count($data),
        'count' => $count,
    ];
    return $coursesContext; 
}






