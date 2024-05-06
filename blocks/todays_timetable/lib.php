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
/* block_instructors_timetable_get_studentclasslist
* todo it will get instructor enrolled  classes scheuled session (which lies between todays date)
*/
function block_instructors_timetable_get_studentclasslist() {
    global $DB , $CFG, $USER;
    $today = date('Y-m-d');
    $insquerys = array();
    $params = array();
    $params['editingteacher'] = 'editingteacher';
    $params['userid'] = $USER->id;
    $courses = "SELECT c.*
              FROM {user} u
              JOIN {role_assignments} ra ON ra.userid = u.id
              JOIN {role} r ON r.id=ra.roleid AND r.shortname=:editingteacher
              JOIN {context} ctx ON ctx.id = ra.contextid
              JOIN {course} c ON c.id = ctx.instanceid
              JOIN {attendance} a ON a.course = c.id
             WHERE u.id = :userid";
             $insquerys = $DB->get_records_sql($courses, $params);
             return $insquerys;
} // End of function block_instructors_timetable_get_studentclasslist.
function block_instructors_timetable_get_dayformat($day) {
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
} // End of function block_instructors_timetable_get_dayformat.
function block_instructors_timetable_get_pretablecontent() {
    global $DB , $CFG, $USER, $OUTPUT;
    $count = 0;
    $coursecount = 0;
    $countpre = 0;
    $sessionlist = array();
    $today = date('Y-m-d');
    $sessionlist = block_instructors_timetable_get_studentclasslist();
    foreach ($sessionlist as $insquery) {
        $coursecount++;
        $line1 = array();
        // $class = $DB->get_record('course', array('id' => $insquery->id));
        // if ($class) {
        $levelid = $DB->get_field('local_program_level_courses', 'levelid', array('courseid' => $insquery->id));
        $line1['courseid'] = $insquery->id;
        $line1['courses'] = $insquery->fullname;
        $line1['levelid'] = $levelid;
            // End of timings.
        $params = array();
        $params['attendance'] = 'attendance';
        $params['insqueryid'] = $insquery->id;
        $cmodules = "SELECT cm.id
                       FROM {course_modules} cm
                       JOIN {modules} m ON cm.module = m.id
                      WHERE m.name = :attendance AND cm.course = :insqueryid";
        $coursmodules = $DB->get_record_sql($cmodules, $params);
            // foreach ($coursmodules as $takes) {
            //     $page = $takes->id;
            // }
        if (!empty($coursmodules)) {
            $page = $coursmodules->id;
            $line1['previouslink'] = $page;
        }
        // }
        $previoussession = '';
        if ($previoussession == null) {
            $attendanceid = $DB->get_field('attendance', 'id', ['course' => $insquery->id]);
            $params = array();
            $params['attendanceid'] = $attendanceid;
            $previoussessionlist = "SELECT *
                                      FROM {attendance_sessions} ass
                                     WHERE attendanceid = :attendanceid
                                           AND ass.teacherid= $USER->id AND DATE(FROM_UNIXTIME(ass.sessdate, '%Y-%m-%d')) < DATE(NOW())";
            $datesofprevious = $DB->get_records_sql($previoussessionlist, $params);
            $data1 = [];
            $cnt +=count($datesofprevious);
            if ($datesofprevious) {
                foreach ($datesofprevious as $datess) {
                    $linecontent = date('d M Y ', $datess->sessdate);
                    $count++;
                    $nottakenatten = true;
                    if ($datess->lasttaken > 0 || $datess->lasttakenby > 0) {
                        $nottakenatten = false;
                    }
                    $line1['atttaken'] = $nottakenatten;
                    $line1['date'] = $linecontent;
                    $line1['sessionlink'] = $datess->id;
                    $line1['count'] = $cnt;
                    $data1[] = $line1;
                    if ($count == 5) {
                        break;
                    }
                }
            $previouss = [
                'previousssess' => $data1,
            ];
            echo $OUTPUT->render_from_template('block_todays_timetable/previous_session', $previouss);
            }
            if ($coursecount == 5 || $count == 5) {
                break;
            }
        }

    }
    // if (!$previoussession) {

        
        return $cnt;
    // }
} // end of function block_instructors_timetable_get_tablecontent.
function block_instructors_timetable_get_tablecontent ($currentdayformat=null, $insquerys, $previoussession=1,
    $previoussessiontime='', $filter) {
    global $DB , $CFG, $USER, $OUTPUT;
    $count = 0;
    $countpre = 0;
    $coursecount = 0;
    $data1 = array();
    foreach ($insquerys as $insquery) {
        $coursecount++;
        $line1 = array();
        $levelid = $DB->get_field('local_program_level_courses', 'levelid', array('courseid' => $insquery->id));
        // if ($class) {
            $useremail = $insquery->fullname;
        if(strlen($useremail) > 20){
            $useremail = substr($useremail, 0, 20).'..';
        }
        $line1['fullcourses'] = $insquery->fullname;
        $line1['courses'] = $useremail;
        $line1['tlid'] = $levelid;
        // End of timings.
        $coursmodule = $DB->get_record_sql("SELECT cm.*
                                                FROM {course_modules} cm
                                                JOIN {modules} m ON cm.module=m.id
                                               WHERE m.name='attendance' AND cm.course=$insquery->id");
        // echo "<pre>";
        // print_r($coursmodule);
        // foreach ($coursmodules as $takes) {
        //     $page = $takes->id;
        // }
        if (!empty($coursmodule)) {
            $page = $coursmodule->id;
        }
        $line1['attendanceid'] = $page;
        // }
        // Here we assigning the array line[] to $data.
        if ($currentdayformat) {
            $attendanceid = $DB->get_field('attendance', 'id', array('course' => $insquery->id));
            $dates = $DB->get_records_sql("SELECT *
                                             FROM {attendance_sessions} ass
                                            WHERE attendanceid = $attendanceid AND ass.teacherid=$USER->id  AND DATE(FROM_UNIXTIME(ass.sessdate,
                                                  '%Y-%m-%d')) = DATE(NOW() - INTERVAL 0 DAY ) ORDER BY ass.sessdate");
            foreach ($dates as $date) {
                $count++;
                $line1['dates'] = attendance_construct_session_time($date->sessdate, $date->duration);
                $line1['sessid'] = $date->id;
                $data1[] = $line1;

                if ($count == 5) {
                    break;
                }
            }
        }
        if ($coursecount == 5 || $count == 5) {
            break;
        }
    }
    if ($currentdayformat) {
        $context = [
            'obj' => $data1,
        ];
        echo $OUTPUT->render_from_template('block_todays_timetable/todays_session', $context);
        return $data1;
    }
} // end of function block_instructors_timetable_get_tablecontent
function block_instructors_sessions_data_for_add($sessionsinfo, $today = false, $previoustime = '', $filter) {
    global $CFG;

    $starray = explode(':', $sessionsinfo->startdate);
    $startdate = $sessionsinfo->startdate;
    $enddate = $sessionsinfo->enddate;
    if ($today) {
        $enddate = strtotime(date('Y-m-d'));
    }
    $days = (int) ceil(($enddate - $startdate) / DAYSECS);
    if ($enddate < $startdate) {
        return null;
    }
    // Getting first day of week.
    $startdate = $startdate;
    $dinfo = usergetdate($startdate);
    if ($CFG->calendar_startwday === '0') {
        // Week start from sunday.
        $startweek = $startdate - $dinfo['wday'] * DAYSECS;
        // Call new variable.
    } else {
        $wday = $dinfo['wday'] === 0 ? 7 : $dinfo['wday'];
        $startweek = $startdate - ($wday - 1) * DAYSECS;
    }
    $wdaydesc = array(0 => 'SU', 'M', 'TU', 'W', 'TH', 'F', 'SA');
    $sdays = array('SU', 'M', 'TU', 'W', 'TH', 'F', 'SA');
    while ($startdate <= $enddate) {
        if ($startdate <= $startweek + WEEKSECS) {
            $dinfo = usergetdate($startdate);
            $testvalue = in_array($wdaydesc[$dinfo['wday']], $sdays);
            if (isset($sdays) && in_array($wdaydesc[$dinfo['wday']], $sdays)) {
                $session = new stdClass();
                $session->id = $sessionsinfo->id;
                $session->sessdate = usergetmidnight($startdate);
                $session->date = date('y-m-d', $session->sessdate);
                $sessions[] = $session;
            }
            $startdate += DAYSECS;
        } else {
            $period = 1;
            $startweek += WEEKSECS * $period;
            $startdate = $startweek;
        }
    }
    if (empty($sessions)) {
        $sessions = 0;
    }
    return $sessions;
}
function block_todaystimetable_instructor_sessions_view() {
    global $USER, $DB, $CFG, $PAGE, $OUTPUT;
    // Tab.
    $ttab = get_string('todays_seessions', 'block_todays_timetable');
    $ptab = get_string('previous_sessions', 'block_todays_timetable');
    $atab = get_string('addnew_sessions', 'block_todays_timetable');
    $previouss = [
        'ttab' => $ttab,
        'ptab' => $ptab,
        'atab' => $atab
    ];
    return $OUTPUT->render_from_template('block_todays_timetable/tabs', $previouss);

} // end of block_todaystimetable_instructor_sessions_view function
function block_instructors_timetable_addnewsession_table() {
    global $DB, $CFG, $USER, $PAGE, $OUTPUT;
    $PAGE->requires->js_init_call('include_addnewsession_js', array('id' => 'block_todays_timetable_addnewsessions'), true);
    $today = date('Y-m-d');
    $courses = "SELECT  DISTINCT c.*,c.id AS courseid,
	 		       c.fullname AS coursename
			  FROM {user} u
			  JOIN {role_assignments} ra ON ra.userid=u.id
			  JOIN {role} r ON r.id=ra.roleid AND r.shortname='editingteacher'
			  JOIN {context} AS ctx ON ctx.id = ra.contextid
			  JOIN {course} c ON c.id = ctx.instanceid
			  JOIN {attendance} a ON a.course=c.id
			 WHERE u.id = $USER->id";
    $classes = $DB->get_records_sql($courses);
    $data = array();
    if (!empty($classes)) {
        $addsessioncount = 0;
        $classidarray = array();
        foreach ($classes as $class) {
            $classidarray[] = $class->id;
        }
        $classid = implode(', ', $classidarray);
        $params = array();
        $params['attendance'] = 'attendance';
        if ($classid) {
            $sessionsql = "SELECT cm.id, lplc.courseid, lplc.levelid, c.fullname
                             FROM {course_modules} cm
                             JOIN {modules} m ON cm.module = m.id
                             JOIN {course} c ON cm.course = c.id
                             JOIN {local_program_level_courses} lplc ON lplc.courseid = c.id
                            WHERE m.name = :attendance
                       AND cm.course IN ($classid)";
            $session = $DB->get_records_sql($sessionsql, $params);
            $cnt += count($session);
            $line = array();
            foreach ($session as $attendance) {
                $newsession = $attendance->levelid;
                $line['courseid'] = $attendance->courseid;
                $line['course'] = $attendance->fullname;
                $line['addsession'] = $newsession;
                $line['count'] = $cnt;

                $data[] = $line;
                $addsessioncount++;
                if ($addsessioncount == 5) {
                    break;
                }
            }
        } else {
            $data = null;
        }
    }
    $context = [
        'obj' => $data
    ];
    echo $OUTPUT->render_from_template('block_todays_timetable/add_newsession', $context);
    return $cnt;
} // end of function

function todays_course_filter($mform){
    global $DB, $USER;
    $systemcontext = context_system::instance();
    $params = array('studentid' => $USER->id);
    $sql = "SELECT  DISTINCT(c.id), c.fullname
                FROM {user} u
                JOIN {role_assignments} ra ON ra.userid = u.id
                JOIN {role} r ON r.id = ra.roleid 
                AND r.shortname = 'editingteacher'
                JOIN {context} ctx ON ctx.id = ra.contextid
                JOIN {course} c ON c.id = ctx.instanceid
                JOIN {attendance} a ON a.course = c.id
                JOIN {attendance_sessions} ats ON ats.attendanceid = a.id  
                WHERE u.id = :studentid  
                AND DATE(FROM_UNIXTIME(ats.sessdate)) = DATE(NOW() )  ";

    $courselist = $DB->get_records_sql_menu($sql, $params);
    $select = $mform->addElement('autocomplete', 'course', '', $courselist,
                                    array(
                                        'placeholder' => get_string('courses','block_todays_timetable')
                                    )
                                );
    $mform->setType('course', PARAM_RAW);
    $select->setMultiple(true);
}

function previous_course_filter($mform){
    global $DB, $USER;
    $systemcontext = context_system::instance();
    $params = array('studentid' => $USER->id);
    $sql = "SELECT  DISTINCT(c.id), c.fullname
                FROM {user} u
                JOIN {role_assignments} ra ON ra.userid = u.id
                JOIN {role} r ON r.id = ra.roleid 
                AND r.shortname = 'editingteacher'
                JOIN {context} ctx ON ctx.id = ra.contextid
                JOIN {course} c ON c.id = ctx.instanceid
                JOIN {attendance} a ON a.course = c.id
                JOIN {attendance_sessions} ats ON ats.attendanceid = a.id
                WHERE u.id = :studentid 
                AND DATE(FROM_UNIXTIME(ats.sessdate)) < DATE(NOW() )  ";

    $courselist = $DB->get_records_sql_menu($sql, $params);
    $select = $mform->addElement('autocomplete', 'course', '', $courselist,
                                    array(
                                        'placeholder' => get_string('courses','block_todays_timetable')
                                    )
                                );
    $mform->setType('course', PARAM_RAW);
    $select->setMultiple(true);
}
function add_course_filter($mform){
    global $DB, $USER;
    $systemcontext = context_system::instance();
    $params = array('studentid' => $USER->id);
    $sql = "SELECT  DISTINCT(c.id), c.fullname
                FROM {user} u
                JOIN {role_assignments} ra ON ra.userid = u.id
                JOIN {role} r ON r.id = ra.roleid 
                AND r.shortname = 'editingteacher'
                JOIN {context} ctx ON ctx.id = ra.contextid
                JOIN {course} c ON c.id = ctx.instanceid
                JOIN {attendance} a ON a.course = c.id
                WHERE u.id = :studentid";

    $courselist = $DB->get_records_sql_menu($sql, $params);
    $select = $mform->addElement('autocomplete', 'course', '', $courselist,
                                    array(
                                        'placeholder' => get_string('courses','block_todays_timetable')
                                    )
                                );
    $mform->setType('course', PARAM_RAW);
    $select->setMultiple(true);
}


function get_todays_courses($stable, $filterdata) {
    global $DB, $USER, $CFG;

    $params = array();
    $params['studentid'] = $stable->studentid;
    $countsql = "SELECT count(ats.id) ";

    $selectsql = " SELECT ats.id, lplc.levelid, c.id as courseid, u.id as userid, c.fullname as coursefullname, ats.sessdate, ats.duration ";
                             
        $fromsql = "FROM {user} u
                JOIN {role_assignments} ra ON ra.userid = u.id
                JOIN {role} r ON r.id = ra.roleid AND r.shortname = 'editingteacher'
                JOIN {context} ctx ON ctx.id = ra.contextid
                JOIN {course} c ON c.id = ctx.instanceid
                JOIN {local_program_level_courses} lplc ON lplc.courseid = c.id
                JOIN {attendance} a ON a.course = c.id
                JOIN {attendance_sessions} ats ON ats.attendanceid = a.id  ";

        $fromsql .= "WHERE u.id = :studentid AND DATE(FROM_UNIXTIME(ats.sessdate)) = DATE(NOW() - INTERVAL 0 DAY)   ";

    // For "Global (search box)" filter.
    if (isset($filterdata->search_query) && trim($filterdata->search_query) != '') {
        $filteredquizzes = array_filter(explode(',', $filterdata->search_query));
        $quizzesarray = array();
        if (!empty($filteredquizzes)) {
            foreach ($filteredquizzes as $key => $value) {
                $quizzesarray[] = "CONCAT(u.firstname, u.lastname) LIKE '%".trim($value)."%' OR c.fullname LIKE '%".trim($value)."%'";
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

    $orderby = " ORDER BY ats.sessdate,ats.duration ASC";
    $alltodayssess = $DB->get_records_sql($selectsql.$fromsql.$orderby, $params, $stable->start, $stable->length);

    $alldata = array();
if (!empty($alltodayssess)) {
    foreach ($alltodayssess as $key => $tdysesvalue) {
        $doc = array();
        $teacherid = $tdysesvalue->userid;

        $doc['sessid'] = $tdysesvalue->id;
        $doc['teacherid'] = $teacherid;
        $doc['levelid'] = $tdysesvalue->levelid;
        $doc['fullname'] = $tdysesvalue->coursefullname;

            $coursmodules = $DB->get_record_sql("SELECT cm.id
                                    FROM {course_modules} cm
                                    JOIN {modules} m ON cm.module = m.id
                                    WHERE m.name = 'attendance'
                                    AND cm.course = $tdysesvalue->courseid");
            if (!empty($coursmodules)) {
                $page = $coursmodules->id;
                $doc['attenid'] = $page;
            }

        if ($tdysesvalue->sessdate) {
            $doc['timings'] = attendance_construct_session_time($tdysesvalue->sessdate, $tdysesvalue->duration);
        }

        $doc['cfg_url'] = $CFG->wwwroot;

        $alldata[] = $doc;
    }
}else{
    $alltodayssess = null;
}
        $coursesContext = [
            'hascourses' => $alldata,
            'length' => count($alldata),
            'count' => $count,
        ];
    return $coursesContext; 
}


function get_previous_courses($stable, $filterdata) {
    global $DB, $USER, $CFG;

    $params = array();
    $params['studentid'] = $stable->studentid;
    $params['userid'] = $USER->id;

    $countsql = "SELECT count(ats.id) ";

    $selectsql = " SELECT ats.id as session, lplc.levelid, c.id, u.id as userid, c.fullname as coursefullname, ats.sessdate ";
                             
    $fromsql = "FROM {user} u
                JOIN {role_assignments} ra ON ra.userid = u.id
                JOIN {role} r ON r.id = ra.roleid AND r.shortname = 'editingteacher'
                JOIN {context} ctx ON ctx.id = ra.contextid
                JOIN {course} c ON c.id = ctx.instanceid
                JOIN {local_program_level_courses} lplc ON lplc.courseid = c.id
                JOIN {attendance} a ON a.course = c.id
                JOIN {attendance_sessions} ats ON ats.attendanceid = a.id ";

    $fromsql .= "WHERE u.id = :userid AND DATE(FROM_UNIXTIME(ats.sessdate)) < DATE(NOW() )   ";

    if (isset($filterdata->search_query) && trim($filterdata->search_query) != '') {
            $filteredquizzes = array_filter(explode(',', $filterdata->search_query));
            $quizzesarray = array();
            if (!empty($filteredquizzes)) {
                foreach ($filteredquizzes as $key => $value) {
                    $quizzesarray[] = "CONCAT(u.firstname, u.lastname) LIKE '%".trim($value)."%' OR c.fullname LIKE '%".trim($value)."%'";
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

    $orderby = " ORDER BY ats.id DESC ";

    $sessionlist = $DB->get_records_sql($selectsql.$fromsql.$orderby, $params,$stable->start, $stable->length);

    $data1 = [];
    foreach ($sessionlist as $datess) {
        if (!empty($datess)) {
        
            $coursmodules = $DB->get_record_sql("SELECT cm.id
                                                 FROM {course_modules} cm
                                                 JOIN {modules} m ON cm.module=m.id
                                                WHERE m.name = 'attendance' AND cm.course = $datess->id");
            // foreach ($coursmodules as $takes) {
            //     $page = $takes->id;
            // }
            if (!empty($coursmodules)) {
                $page = $coursmodules->id;
                $line1['previouslink'] = $page;
            }
            $nottakenatten = true;
            if ($datess->lasttaken > 0 || $datess->lasttakenby > 0) {
                $nottakenatten = false;
            }
            $linecontent = date('d M Y ', $datess->sessdate);
            $line1['atttaken'] = $nottakenatten;
            $line1['sessid'] = $datess->session;
            $line1['courseid'] = $datess->id;
            $line1['date'] = $linecontent;
            $line1['sessionlink'] = $datess->levelid;
            $line1['cfg_url'] = $CFG->wwwroot;
            $line1['courses'] = $datess->coursefullname;
            $data1[] = $line1;
        } else {
            $data1[] = null;
        } 
    }

    $previouss = [
        'hascourses' => $data1,
        'length' => count($data1),
        'count' => $count,
    ];

    return $previouss; 
}

function get_add_courses($stable, $filterdata) {
    global $DB, $USER, $CFG;

    $sessionlist = array();
    $today = date('Y-m-d');
    $insquerys = array();
    $params = array();
    $params['studentid'] = $stable->studentid;

    $countsql = "SELECT count(c.id) ";

    $selectsql = " SELECT  DISTINCT(c.id), c.fullname ";
                             
        $fromsql = "FROM {user} u
                JOIN {role_assignments} ra ON ra.userid = u.id
                JOIN {role} r ON r.id = ra.roleid AND r.shortname = 'editingteacher'
                JOIN {context} ctx ON ctx.id = ra.contextid
                JOIN {course} c ON c.id = ctx.instanceid
                JOIN {attendance} a ON a.course = c.id";
        $fromsql .= " WHERE u.id = :studentid ";

    if (isset($filterdata->search_query) && trim($filterdata->search_query) != '') {
            $filteredquizzes = array_filter(explode(',', $filterdata->search_query));
            $quizzesarray = array();
            if (!empty($filteredquizzes)) {
                foreach ($filteredquizzes as $key => $value) {
                    $quizzesarray[] = "CONCAT(u.firstname, u.lastname) LIKE '%".trim($value)."%' OR c.fullname LIKE '%".trim($value)."%'";
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
    $groupby = " GROUP BY c.id ";
    $orderby = " ORDER BY c.id DESC ";
    $sessionlist = $DB->get_records_sql($selectsql.$fromsql.$groupby.$orderby, $params,$stable->start, $stable->length);

    $data = array();
    if (!empty($sessionlist)) {
        $addsessioncount = 0;
        $classidarray = array();
        foreach ($sessionlist as $class) {
            $classidarray[] = $class->id;
        }
        $classid = implode(', ', $classidarray);
        $params = array();
        $params['attendance'] = 'attendance';
        if ($classid) {
            $sessionsql = "SELECT cm.id, lplc.courseid, lplc.levelid, c.fullname
                             FROM {course_modules} cm
                             JOIN {modules} m ON cm.module = m.id
                             JOIN {course} c ON cm.course = c.id
                             JOIN {local_program_level_courses} lplc ON lplc.courseid = c.id
                            WHERE m.name = :attendance
                       AND cm.course IN ($classid)";
            $session = $DB->get_records_sql($sessionsql, $params);
            $line = array();
            foreach ($session as $attendance) {
                $newsession = $attendance->levelid;
                $line['coursesid'] = $attendance->courseid;
                $line['courses'] = $attendance->fullname;
                $line['sessionlink'] = $newsession;
                $line['cfg_url'] = $CFG->wwwroot;
                $data[] = $line;
            }
        } else {
            $data = null;
        }
    }
    $addnewsessions = [
        'hascourses' => $data,
        'length' => count($data),
        'count' => $count,
    ];

    return $addnewsessions; 
}
