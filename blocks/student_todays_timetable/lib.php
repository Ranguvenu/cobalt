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
/*
*From here the student functions starts
*you can easily identify by looking at function names which is prefixed with block_todays_timetable
*/
function block_student_todays_timetable_get_studentclasslist() {
    global $DB , $CFG, $USER;
    $today = date('Y-m-d');
    $courses = array();
    $params = array();
    $params['userid'] = $USER->id;
    $params['student'] = 'student';
    $sql = "SELECT c.*,c.id AS courseid,c.fullname AS coursename
		      FROM {user} u
		      JOIN {role_assignments} ra ON ra.userid=u.id
		      JOIN {role} r ON r.id=ra.roleid AND r.shortname =
		           :student
		      JOIN {context} ctx ON ctx.id = ra.contextid
		      JOIN {course} c ON c.id = ctx.instanceid
		      JOIN {attendance} a ON a.course=c.id
		     WHERE u.id = :userid";
    $courses = $DB->get_records_sql($sql, $params);
    return $courses;
} // End of function block_todays_timetable_get_studentclasslist

function block_student_todays_timetable_get_studentclasslists() {
    global $DB , $CFG, $USER;
    $today = date('Y-m-d');
    $courses = array();
    $params = array();
    $params['userid'] = $USER->id;
    $params['student'] = 'student';
    $sql = "SELECT c.id AS courseid,c.*,c.fullname AS coursename
              FROM {user} u
              JOIN {role_assignments} ra ON ra.userid=u.id
              JOIN {role} r ON r.id=ra.roleid AND r.shortname =
                   :student
              JOIN {context} ctx ON ctx.id = ra.contextid
              JOIN {course} c ON c.id = ctx.instanceid
              JOIN {attendance} a ON a.course=c.id
             WHERE u.id = :userid LIMIT 10";
    $courses = $DB->get_records_sql($sql, $params);
    return $courses;
} // End of function block_todays_timetable_get_studentclasslist

/*
Get the faculty name enrolled to the course.
*/
function get_course_faculty_name($teacherid) {
    global $DB , $CFG, $USER;

    $params = array('teacherid' => $teacherid);
    // $doc = array();
    // $exp = array();
    /*$sql = "SELECT ue.userid, CONCAT(u.firstname,' ', u.lastname) as fullname  FROM {enrol} e
            JOIN {user_enrolments} ue ON ue.enrolid = e.id 
            JOIN {role_assignments} ra ON ra.userid = ue.userid 
            JOIN {role} r ON r.id = ra.roleid 
            JOIN {user} u ON u.id = ue.userid
            JOIN {attendance_sessions} ats ON u.id = ats.teacherid
            WHERE courseid = {$courseid} AND r.shortname = 'editingteacher' AND u.suspended <> 1  
            ";*/
    $sql = "SELECT id, CONCAT(firstname,' ', lastname) as fullname
             FROM {user}
            WHERE id = :teacherid ";

    $facultyname = $DB->get_record_sql($sql, $params);
    // print_object($facultyname);die;
    // $exp = implode(', ',  $facultyname);

    return $doc = $facultyname->fullname;
}

function block_student_todays_timetable_get_dayformat($day) {
    global $DB , $CFG, $USER;
    $response = '';
    // Here we took switch statement to change the cureent day into singal word.
    switch ($day) {
        case "Mon":
            $response = 'M';
            break;
        case "Tue":
            $response = 'TU';
            break;
        case "Wed":
            $response = 'W';
            break;
        case "Thu":
            $response = 'TH';
            break;
        case "Fri":
            $response = 'F';
            break;
        case "Sat":
            $response = 'SA';
            break;
        case "Sun":
            $response = 'SU';
            break;
    }
    // Returning the result.
    return $response;
} // End of function block_todays_timetable_get_dayformat.
function block_student_todays_timetable_get_tablecontent ($currentdayformat, $userid) {
    global $DB , $CFG, $USER;
        $data1 = array();
   $sql = "SELECT ats.id, plc.courseid, c.fullname, ats.duration, ats.sessdate, ats.teacherid
                FROM {local_program_users} pu 
                JOIN {local_program_levels} pl ON pl.programid = pu.programid 
                JOIN {local_program_level_courses} plc ON plc.levelid = pl.id 
                JOIN {course} c ON c.id = plc.courseid 
                JOIN {attendance} a ON a.course = c.id 
                JOIN {attendance_sessions} ats ON ats.attendanceid = a.id 
                WHERE pu.userid = $userid AND pl.active = 1 
                AND DATE(FROM_UNIXTIME(ats.sessdate)) = DATE(NOW() - INTERVAL 0 DAY) 
                ORDER BY ats.sessdate ASC LIMIT 5";

        $courses = $DB->get_records_sql($sql);

        foreach ($courses as $course) {
            $line1 = array();
            $line1['classroom'] = $course->fullname;
        
            if ($currentdayformat) {
                    $line1['date'] = attendance_construct_session_time($course->sessdate, $course->duration);
            }

            $sql = "SELECT ue.userid, CONCAT(u.firstname,' ', u.lastname) as fullname FROM {enrol} e
                    JOIN {user_enrolments} ue ON ue.enrolid = e.id 
                    JOIN {role_assignments} ra ON ra.userid = ue.userid 
                    JOIN {role} r ON r.id = ra.roleid 
                    JOIN {user} u ON u.id = ue.userid
                    WHERE courseid = {$course->courseid} AND r.shortname = 'editingteacher' AND u.suspended <> 1 
                    GROUP BY courseid";

            $instructor = $DB->get_record_sql($sql);

            $val = get_course_faculty_name($course->teacherid);
            if($val) {
                $line1['faculty_fullname'] = $val;
            }else{
                $line1['faculty_fullname'] = 'Teacher not enrolled to the course';
            }
            $orgstring = strlen($val) > 10 ? substr($val, 0, 10)."..." : $val;
            
            if ($instructor) {
                $line1['instructor'] = $orgstring;
            } else {
                $line1['instructor'] = 'N/A';
            }
            // Here we assigning the array line[] to $data.
            $data1[] = $line1;

    }
    // End of foreach.
    // Returning the result.
    return $data1;
} // End of function block_todays_timetable_get_tablecontent.

function block_student_todays_timetable_get_tablecontents ($currentdayformat, $userid) {
    global $DB , $CFG, $USER;
        $data1 = array();
   $sql = "SELECT ats.id, plc.courseid, c.fullname, ats.duration, ats.sessdate
                FROM {local_program_users} pu 
                JOIN {local_program_levels} pl ON pl.programid = pu.programid 
                JOIN {local_program_level_courses} plc ON plc.levelid = pl.id 
                JOIN {course} c ON c.id = plc.courseid 
                JOIN {attendance} a ON a.course = c.id 
                JOIN {attendance_sessions} ats ON ats.attendanceid = a.id 
                WHERE pu.userid = $userid AND pl.active = 1 
                AND DATE(FROM_UNIXTIME(ats.sessdate)) = DATE(NOW() - INTERVAL 0 DAY) 
                ORDER BY ats.sessdate DESC";

        $courses = $DB->get_records_sql($sql);

        foreach ($courses as $course) {
            $line1 = array();
            $line1['classroom'] = $course->fullname;
        
            if ($currentdayformat) {
                    $line1['date'] = attendance_construct_session_time($course->sessdate, $course->duration);
            }

            $role = $DB->get_record('role', ['shortname' => 'editingteacher']);
            $context = get_context_instance(CONTEXT_COURSE, $course->courseid);
            $teachers = get_role_users($role->id, $context);
            foreach ($teachers as $teacher) {
                $instructor = $teacher->firstname.' '.$teacher->lastname;
            }
            if ($instructor) {
                $line1['instructor'] = $instructor;
            } else {
                $line1['instructor'] = 'N/A';
            }

            // Here we assigning the array line[] to $data.
            $data1[] = $line1;

    }
    // End of foreach.
    // Returning the result.
    return $data1;
} // End of function block_todays_timetable_get_tablecontent.

function block_student_todays_timetable_get_table ($data) {
    // Here we are craeting the table with header and sending the response.
    $table = new html_table();
    $table->id = "block_student_table";
    // Table head.
    $table->head = array(get_string('subject', 'block_student_todays_timetable'),
        get_string('timings', 'block_student_todays_timetable'),
        get_string('instructor', 'block_student_todays_timetable'));
    $table->data = $data;
    $table = html_writer::table($table);

    return $table;
}

/**
 * Description: Course/Subject filter code
 * @param  [mform object]  $mform[the form object where the form is initiated]
 */
function subject_filter($mform){
    global $DB, $USER;
    $systemcontext = context_system::instance();
    $params = array('studentid' => $USER->id);
    $sql = "SELECT c.id, c.fullname 
            FROM {local_program_users} pu 
            JOIN {local_program_levels} pl ON pl.programid = pu.programid 
            JOIN {local_program_level_courses} plc ON plc.levelid = pl.id 
            JOIN {course} c ON c.id = plc.courseid 
            JOIN {attendance} a ON a.course = c.id 
            JOIN {attendance_sessions} ats ON ats.attendanceid = a.id 
            WHERE pu.userid = :studentid AND pl.active = 1 
            AND DATE(FROM_UNIXTIME(ats.sessdate)) = DATE(NOW() - INTERVAL 0 DAY) 
            ORDER BY ats.sessdate DESC ";

    $courselist = $DB->get_records_sql_menu($sql, $params);

    $select = $mform->addElement('autocomplete', 'subject', '', $courselist,
                                    array(
                                        'placeholder' => get_string('subject','block_student_todays_timetable')
                                    )
                                );
    $mform->setType('subject', PARAM_RAW);
    $select->setMultiple(true);
}

/**
 * Description: Faculty filter code
 * @param  [mform object]  $mform[the form object where the form is initiated]
 */
function faculty_filter($mform){
    global $DB, $USER;
    $systemcontext = context_system::instance();
    $params = array('userid' => $USER->id, 'active'=>1);
    $sql = "SELECT ue.userid, CONCAT(u.firstname,' ', u.lastname) as userfullname, c.id as courseid, c.fullname 
            FROM {local_program_users} pu 
            JOIN {local_program_levels} pl ON pl.programid = pu.programid 
            JOIN {local_program_level_courses} plc ON plc.levelid = pl.id 
            JOIN {course} c ON c.id = plc.courseid 
            JOIN {attendance} a ON a.course = c.id 
            JOIN {attendance_sessions} ats ON ats.attendanceid = a.id 
            JOIN {enrol} e ON e. courseid = c.id 
            JOIN {user_enrolments} ue ON ue.enrolid = e.id 
            JOIN {role_assignments} ra ON ra.userid = ue.userid 
            JOIN {role} r ON r.id = ra.roleid 
            JOIN {user} u ON u.id = ue.userid 
            WHERE pl.active= :active AND pu.userid = :userid AND r.shortname = 'editingteacher' 
            AND DATE(FROM_UNIXTIME(ats.sessdate)) = DATE(NOW() - INTERVAL 0 DAY) 
            ORDER BY ats.sessdate DESC";

    $facultylist = $DB->get_records_sql_menu($sql, $params);

    $select = $mform->addElement('autocomplete', 'faculty', '', $facultylist,
                                    array(
                                        'placeholder' => get_string('faculty','block_student_todays_timetable')
                                    )
                                );
    $mform->setType('faculty', PARAM_RAW);
    $select->setMultiple(true);
}


function get_listof_courses_and_faculty ($stable, $filterdata) {
    global $DB, $USER, $CFG;
    
    $params = array();
    $params['studentid'] = $stable->studentid;

    $countsql = "SELECT count(distinct ats.id) ";

    /*$selectsql = " SELECT ats.id as attendanceid, plc.courseid, pu.userid, c.fullname as coursefullname, ats.sessdate, ats.duration, CONCAT(u.firstname,' ', u.lastname) as userfullname ";*/
    $selectsql = "SELECT ats.id, plc.courseid, c.fullname, ats.duration, ats.sessdate, ats.teacherid";

    /*$fromsql = " FROM {local_program_users} pu 
                JOIN {local_program_levels} pl ON pl.programid = pu.programid 
                JOIN {local_program_level_courses} plc ON plc.levelid = pl.id 
                JOIN {attendance} a ON a.course = plc.courseid 
                JOIN {course} c ON c.id = a.course 
                JOIN {attendance_sessions} ats ON ats.attendanceid = a.id 
                JOIN {enrol} e ON e. courseid = c.id 
                LEFT JOIN {user_enrolments} ue ON ue.enrolid = e.id 
                LEFT JOIN {role_assignments} ra ON ra.userid = ue.userid 
                LEFT JOIN {role} r ON r.id = ra.roleid 
                LEFT JOIN {user} u ON u.id = ue.userid ";*/

    $fromsql = " FROM {local_program_users} pu 
                 JOIN {local_program_levels} pl ON pl.programid = pu.programid 
                 JOIN {local_program_level_courses} plc ON plc.levelid = pl.id 
                 JOIN {course} c ON c.id = plc.courseid 
                 JOIN {attendance} a ON a.course = c.id 
                 JOIN {attendance_sessions} ats ON ats.attendanceid = a.id
                 JOIN {user} u ON ats.teacherid = u.id ";

    $fromsql .= " WHERE pl.active= 1 AND pu.userid = :studentid  
                  AND DATE(FROM_UNIXTIME(ats.sessdate)) = DATE(NOW() - INTERVAL 0 DAY) ";
    
    // For "Global (search box)" filter.
    if (isset($filterdata->search_query) && trim($filterdata->search_query) != '') {
        $filteredquizzes = array_filter(explode(',', $filterdata->search_query));
        $quizzesarray = array();
        if (!empty($filteredquizzes)) {
            foreach ($filteredquizzes as $key => $value) {
                $quizzesarray[] = "u.firstname LIKE '%".trim($value)."%' OR  u.lastname LIKE '%".trim($value)."%'";
                $quizzesarray[] = "c.fullname LIKE '%".trim($value)."%' ";
            }
            $imploderequests = implode(' OR ', $quizzesarray);
            $fromsql .= " AND ($imploderequests)";
        }
    }

    // Course name filter.
    if (!empty($filterdata->subject)) {
        $filteredquizzes = array_filter(explode(',', $filterdata->subject), 'is_numeric');
        if(!empty($filteredquizzes)) {
            $quizzesarray = array();
            foreach($filteredquizzes as $key => $value) {
                $quizzesarray[] = "c.id = $value"; 
            }
            $quizzesimplode = implode(' OR ', $quizzesarray);
            $fromsql .= " AND ($quizzesimplode) ";
        }
    }

    // Faculty name filter.
    if (!empty($filterdata->faculty)) {
        $filteredcourse = array_filter(explode(',', $filterdata->faculty), 'is_numeric');
        if(!empty($filteredcourse)) {
            $coursearray = array();
            foreach($filteredcourse as $key => $value) {
                $coursearray[] = "ats.teacherid = $value"; 
            }
            $courseimplode = implode(' OR ', $coursearray);
            $fromsql .= " AND ($courseimplode) ";
        }
    }

    $count = $DB->count_records_sql($countsql.$fromsql, $params);

    $groupby = " GROUP BY ats.id  ";

    $orderby = " ORDER BY ats.sessdate ASC ";
    
    $allquizs = $DB->get_records_sql($selectsql.$fromsql.$groupby.$orderby, $params, $stable->start, $stable->length);

    $alldata = array();

    foreach ($allquizs as $key => $quizvalues) {
        $doc = array();
        $quizid = $quizvalues->userid;

        $doc['id'] = $quizid;
        $doc['subject'] = $quizvalues->fullname;
        if ($quizvalues->sessdate) {
            $doc['timings'] = attendance_construct_session_time($quizvalues->sessdate, $quizvalues->duration);
        }
        $coursefname = get_course_faculty_name($quizvalues->teacherid);
        
        /*if(!$quizvalues->userfullname) {
            $doc['instructor'] = 'N/A';
        } else {
            $doc['instructor'] = $quizvalues->userfullname;
        }*/
        if($coursefname) {
            $doc['faculty_name'] = $coursefname;
        }else{
            $doc['faculty_name'] = 'N/A';
        }
        $doc['cfgwwwroot'] = $CFG->wwwroot;

        $alldata[] = $doc;
    }
    $coursesContext = [
        'hascourses' => $alldata,
        'length' => count($alldata),
        'count' => $count,
    ];

    return $coursesContext; 
}
