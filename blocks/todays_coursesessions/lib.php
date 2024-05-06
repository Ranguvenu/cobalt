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
/* block_instructors_session_get_studentclasslist
* todo it will get instructor enrolled  classes scheuled session (which lies between todays date)
*/
function block_instructors_session_get_studentclasslist() {
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
} // End of function block_instructors_session_get_studentclasslist.
function block_instructors_session_get_dayformat($day) {
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
} // End of function block_instructors_session_get_dayformat.
function block_instructors_session_get_pretablecontent() {
    global $DB , $CFG, $USER, $OUTPUT;
    $count = 0;
    $coursecount = 0;
    $countpre = 0;
    $sessionlist = array();
    $today = date('Y-m-d');
    $sessionlist = block_instructors_session_get_studentclasslist();
    foreach ($sessionlist as $insquery) {
        $coursecount++;
        $line1 = array();
        $class = $DB->get_record('course', array('id' => $insquery->id));
        if ($class) {
            $line1['courses'] = $class->fullname;
            // End of timings.
            $params = array();
            $params['attendance'] = 'attendance';
            $params['insqueryid'] = $insquery->id;
            $cmodules = "SELECT cm.id
                           FROM {course_modules} cm
                           JOIN {modules} m ON cm.module = m.id
                          WHERE m.name = :attendance AND cm.course = :insqueryid";
            $coursmodules = $DB->get_records_sql($cmodules, $params);
            foreach ($coursmodules as $takes) {
                $page = $takes->id;
            }
            $line1['previouslink'] = $page;
        }
            $previoussession = '';
        if ($previoussession == null) {
            $attendanceid = $DB->get_field('attendance', 'id', ['course' => $insquery->id]);
            $params = array();
            $params['attendanceid'] = $attendanceid;
            $previoussessionlist = "SELECT *
                                      FROM {attendance_sessions} ass
                                     WHERE attendanceid = :attendanceid
                                           AND DATE(FROM_UNIXTIME(ass.sessdate, '%Y-%m-%d')) < DATE(NOW())";
            $datesofprevious = $DB->get_records_sql($previoussessionlist, $params);
            if ($datesofprevious) {
                foreach ($datesofprevious as $datess) {
                    $linecontent = date('d M Y ', $datess->sessdate);
                    $count++;
                    $line1['date'] = $linecontent;
                    $line1['sessionlink'] = $datess->id;
                    $data1[] = $line1;
                    if ($count == 3) {
                        break;
                    }
                }
            }
        }
        if ($coursecount == 3 || $count == 3) {
            break;
        }
    }
    if (!$previoussession) {
        $previouss = [
          'previousssess' => $data1,
        ];
        echo $OUTPUT->render_from_template('block_todays_coursesessions/previous_session', $previouss);
        return $datesofprevious;
    }
} // end of function block_instructors_session_get_tablecontent.
function block_instructors_session_get_tablecontent ($currentdayformat=null, $insquerys, $previoussession=1,
    $previoussessiontime='', $filter) {
    global $DB , $CFG, $USER, $OUTPUT;
    $count = 0;
    $countpre = 0;
    $data1 = array();
    foreach ($insquerys as $insquery) {
        $line1 = array();
        $class = $DB->get_record('course', array('id' => $insquery->id));
        if ($class) {
            $line1['courses'] = $class->fullname;
            // End of timings.
            $coursmodules = $DB->get_records_sql("SELECT cm.id
                                                    FROM {course_modules} cm
                                                    JOIN {modules} m ON cm.module=m.id
                                                   WHERE m.name='attendance' AND cm.course=$insquery->id");
            foreach ($coursmodules as $takes) {
                $page = $takes->id;
            }
            $line1['pagesid'] = $page;
        }
        // Here we assigning the array line[] to $data.
        if ($currentdayformat) {
            $attendanceid = $DB->get_field('attendance', 'id', array('course' => $insquery->id));
            $dates = $DB->get_records_sql("SELECT *
                                             FROM {attendance_sessions} ass
                                            WHERE attendanceid = $attendanceid AND DATE(FROM_UNIXTIME(ass.sessdate,
                                                  '%Y-%m-%d')) = DATE(NOW() - INTERVAL 0 DAY )");
            foreach ($dates as $date) {
                $count++;
                $line1['dates'] = attendance_construct_session_time($date->sessdate, $date->duration);
                $data1[] = $line1;
                if ($count == 3) {
                    break;
                }
            }
        }
    } // end of foreach
    if ($currentdayformat) {
        $context = [
            'obj' => $data1,
        ];
        echo $OUTPUT->render_from_template('block_todays_coursesessions/todays_sessions', $context);
        return $data1;
    }
} // end of function block_instructors_session_get_tablecontent
function block_instructors_todays_sessions_data_for_add($sessionsinfo, $today = false, $previoustime = '', $filter) {
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
function block_todayssession_instructor_sessions_view() {
    global $USER, $DB, $CFG, $PAGE, $OUTPUT;
    // Tab.
    $ttab = get_string('todays_sessions', 'block_todays_coursesessions');
    // $ptab = get_string('previous_sessions', 'block_todays_coursesessions');
    // $atab = get_string('addnew_sessions', 'block_todays_coursesessions');
    $tabs = [
        'ttab' => $ttab,
        // 'ptab' => $ptab,
        // 'atab' => $atab
    ];
    return $OUTPUT->render_from_template('block_todays_coursesessions/tab', $tabs);

} // end of block_todayssession_instructor_sessions_view function
function block_instructors_session_addnewsession_table() {
    global $DB, $CFG, $USER, $PAGE, $OUTPUT;
    $PAGE->requires->js_init_call('include_addnewsession_js', array('id' => 'block_todays_coursesessions_addnewsessions'), true);
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
            $sessionsql = "SELECT cm.id, c.fullname
                             FROM {course_modules} cm
                             JOIN {modules} m ON cm.module = m.id
                             JOIN {course} c ON cm.course = c.id
                            WHERE m.name = :attendance
                       AND cm.course IN ($classid)";
            $session = $DB->get_records_sql($sessionsql, $params);
            $line = array();
            foreach ($session as $attendance) {
                $newsession = $attendance->id;
                $line['course'] = $attendance->fullname;
                $line['addsession'] = $newsession;
                $data[] = $line;
                $addsessioncount++;
                if ($addsessioncount == 3) {
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
    echo $OUTPUT->render_from_template('block_todays_coursesessions/add_newsession', $context);
    return $data;
} // end of function
