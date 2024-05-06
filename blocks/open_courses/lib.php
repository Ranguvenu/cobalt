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
require_once($CFG->dirroot . '/mod/attendance/locallib.php');
/*
*From here the student functions starts
*you can easily identify by looking at function names which is prefixed with block_todays_timetable
*/

 
 function semester_filter1($mform)
{
     global $DB, $USER;
    $systemcontext = context_system::instance();

    $params = array('studentid' => $USER->id, 'active' => 1);
    

            $sql = "SELECT cr.id , cr.fullname FROM {course} AS cr JOIN {context} ct ON ct.instanceid = cr.ID AND ct.contextlevel = 50 JOIN {role_assignments} ra ON ra.CONTEXTID = ct.ID JOIN {user} u ON u.ID = ra.userid  AND cr.open_identifiedas = 6 AND u.ID=$USER->id ";

    $courselist = $DB->get_records_sql_menu($sql, $params);

    $select = $mform->addElement(
        'autocomplete',
        'coursename',
        '',
        $courselist,
        array(
            'placeholder' => get_string('coursename', 'block_semester_progress')
        )
    );
    $mform->setType('coursename', PARAM_RAW);
    $select->setMultiple(true);
    
     
}  


function get_listof_current_semester_courses1($stable, $filterdata)
{
    global $DB, $USER, $CFG;
 
    $params = array();
    $params['studentid'] = $stable->studentid;

    $countsql = "SELECT count(DISTINCT cr.id) FROM {course} AS cr JOIN {context} ct ON ct.instanceid = cr.ID AND ct.contextlevel = 50 JOIN {role_assignments} ra ON ra.CONTEXTID = ct.ID JOIN {user} u ON u.ID = ra.userid  AND cr.open_identifiedas = 6 AND u.ID=$USER->id ";

   // $selectsql = " SELECT plc.courseid, pu.userid, c.fullname, pl.programid, pl.startdate, pl.enddate, plc.parentid   ";

$selectsql = "SELECT cr.id , cr.fullname FROM {course} AS cr JOIN {context} ct ON ct.instanceid = cr.ID AND ct.contextlevel = 50 JOIN {role_assignments} ra ON ra.CONTEXTID = ct.ID JOIN {user} u ON u.ID = ra.userid  AND cr.open_identifiedas = 6 AND u.ID=$USER->id ";

 


   /* $fromsql = " FROM {local_program_users} pu 
                JOIN {local_program_levels} pl ON pl.programid = pu.programid 
                JOIN {local_program_level_courses} plc ON plc.levelid = pl.id 
                JOIN {course} c ON c.id = plc.courseid 
                JOIN {context} AS ctx ON c.id = ctx.instanceid 
                JOIN {role_assignments} AS ra ON ra.contextid = ctx.id 
                JOIN {user} AS u ON u.id = ra.userid  
                 ";

    $fromsql .= " WHERE ra.userid = :studentid AND pl.active = 1 ";*/


//$fromsql = "";

    // For "Global (search box)" filter.
   

    if (!empty($filterdata->coursename)) {
        $filteredquizzes = array_filter(explode(',', $filterdata->coursename), 'is_numeric');
        if (!empty($filteredquizzes)) {
            $quizzesarray = array();
            foreach ($filteredquizzes as $key => $value) {
                $quizzesarray[] = " cr.id = $value";
            }
            $quizzesimplode = implode(' OR ', $quizzesarray);
            $fromsql .= " AND ($quizzesimplode) ";
        }
    }

     if (isset($filterdata->search_query) && trim($filterdata->search_query) != '') {
 
        $fromsql .= " AND cr.fullname LIKE :search";
        $params = array('search' => '%'.trim($filterdata->search_query).'%');
    } else {
        $params = array();
    }
 
    $count = $DB->count_records_sql($countsql , $params);

    $orderby = "GROUP BY plc.courseid ORDER BY plc.courseid ASC";

    $allquizs = $DB->get_records_sql($selectsql.$fromsql , $params, $stable->start, $stable->length);

    $alldata = array();

    $electivesql = "SELECT plc.id, plc.courseid, pl.programid, pl.id as levelid, pl.level, pl.active, plc.mandatory, pl.has_course_elective, pl.startdate
    FROM {local_program_users} pu 
    JOIN {local_program_levels} pl ON pl.programid = pu.programid 
    JOIN {local_program_level_courses} plc ON plc.levelid = pl.id
    WHERE pu.userid = :userid AND pl.active = 1";

    $semstartdate = $DB->get_record_sql($electivesql, array('userid' => $USER->id));

    $today_date = date('d-m-Y');

    if ($semstartdate->startdate == 0) {
        $semester_startdate = 'N/A';
    } else {
       $semester_startdate = date('d-m-Y', $semstartdate->startdate);
    }

    if (strtotime($today_date) >= $semstartdate->startdate) {
        $can_access = 1;
        $datemessage = '';
    } else {
        $can_access = 0;
        $datemessage = '"' . $semstartdate->level . '" not started yet...!!!';
    }

    $line = array();
    $params = array();

    $ele = get_elective_courses1($USER->id);
    $elective = $ele[0];

    foreach ($allquizs as $key => $quizvalues) {
        $doc = array();
 

        $params['id'] = $quizvalues->courseid;
            $cid = $quizvalues->id;
 
        $totalmodules = "SELECT COUNT(*) FROM {course_modules} cm WHERE cm.course = $cid  AND cm.completion = 1 AND cm.visible = 1 AND cm.deletioninprogress = 0";

        $totalmodulescount = $DB->count_records_sql($totalmodules, $params);

        $completedmodules = "SELECT COUNT(cmc.id) FROM {course_modules_completion} cmc LEFT JOIN {course_modules} cm ON cmc.coursemoduleid = cm.id WHERE cm.course = $cid AND cmc.userid = $USER->id AND cm.visible = 1 AND cm.deletioninprogress = 0";
        $completedmodulescount = $DB->count_records_sql($completedmodules, $params);

        $courseprogress = ($completedmodulescount/$totalmodulescount)*100;
        
        $coursecontext = $DB->get_field('context','id', array('instanceid' => $quizvalues->courseid, 'contextlevel' =>50));
        $semesterdate = $DB->get_record('local_program_levels', array('id'=>$quizvalues->levelid));
        
        // Topics count.
        $countoftopics = "SELECT COUNT(id) FROM {course_sections} WHERE course = $cid AND section >= 1";
        $nooftopics = $DB->count_records_sql($countoftopics, $params);

        // Assignments count.
        $assignments = "SELECT COUNT(cm.id)
                         FROM {course_modules} cm
                         JOIN {modules} as m ON cm.module = m.id
                        WHERE cm.course = $cid AND cm.visible = 1 AND m.name = 'assign' AND cm.deletioninprogress = 0";
          $assignmentscount = $DB->count_records_sql($assignments, $params);
 

        // Quizzes count.
        $test = "SELECT COUNT(cm.id)
                  FROM {course_modules} cm
                  JOIN {modules} as m ON cm.module = m.id
                 WHERE cm.course = $cid AND cm.visible = 1 AND m.name = 'quiz' AND cm.deletioninprogress = 0";
        $testcount = $DB->count_records_sql($test, $params);



        if ($totalmodulescount > 1) {
            $completedmodules = "SELECT COUNT(cmc.id) FROM {course_modules_completion} cmc LEFT JOIN {course_modules} cm ON cmc.coursemoduleid = cm.id WHERE cm.course = $cid AND cmc.userid = $USER->id AND cmc.completionstate = 1 AND cm.visible = 1 AND cm.deletioninprogress = 0";
            $completedmodulescount = $DB->count_records_sql($completedmodules, $params);

            $courseprogress = round($completedmodulescount / $totalmodulescount * 100);
            $course_completion_exists = $DB->record_exists_sql("SELECT id FROM {course_completions} cc WHERE cc.course = {$quizvalues->id} AND cc.userid = {$USER->id} AND cc.timecompleted > 0");
            if ($course_completion_exists) {
                $courseprogress = 100;
                $coursecompleted = 'Completed';
            } else {
                $courseprogress = $courseprogress;
                // $coursecompleted = 'To be completed';
                // $coursecompleted_criteria = 'To be completed based on completion criteria';
            }

            $coursecontext = $DB->get_field('context', 'id', array('instanceid' => $quizvalues->courseid, 'contextlevel' => 50));
            $params['id'] = $quizvalues->courseid;
            // $countoftopics = "SELECT COUNT(id) FROM {course_sections} WHERE course = :id AND section >= 1";
            // $nooftopics = $DB->count_records_sql($countoftopics, $params);
            $doc['id'] = $cid;//$quizvalues->courseid;
            $doc['fullname'] = $quizvalues->fullname;
            $doc['assignmentscount'] = $assignmentscount;
            $doc['testcount'] = $testcount;


 

            $semstartdate = $DB->get_field('local_program_levels', 'startdate', array('id' => $quizvalues->levelid));
            $sementdate = $DB->get_field('local_program_levels', 'enddate', array('id' => $quizvalues->levelid));

            if ($semstartdate == 0) {
                $doc['startdate'] = 'N/A';
            } else {
                $doc['startdate'] = (date('d-M-Y', $semstartdate));
            }
            if ($sementdate == 0) {
                $doc['enddate'] = 'N/A';
            } else {
                $doc['enddate'] = date('d-M-Y', $sementdate);
            }
            $doc['courseprogress'] = round($courseprogress) . '%';
            $doc['nooftopics'] = $nooftopics;
            /*$instructor = $DB->get_records_sql("SELECT u.id,u.firstname FROM {user} u JOIN {role_assignments} ra ON ra.userid = u.id WHERE ra.roleid = 3 AND ra.contextid = $coursecontext");
            $facultycount = count($instructor);
            $doc['facultycount'] = $facultycount;

            if ($quizvalues->mandatory == 1) {
                $doc['mandatory'] = 'Core';
            } else {
                $doc['mandatory'] = 'Elective';
            }*/
            $doc['criteria'] = $criteria;
            $data['criteriaselected'][] = $doc;
        } else {



            $criteria = true;
            $coursecontext = $DB->get_field('context', 'id', array('instanceid' => $quizvalues->courseid, 'contextlevel' => 50));
            // $countoftopics = "SELECT COUNT(id) FROM {course_sections} WHERE course = :id AND section >= 1";
            // $nooftopics = $DB->count_records_sql($countoftopics, $params);
            $doc['id'] = $cid;//$quizvalues->courseid;
            $doc['fullname'] = $quizvalues->fullname;

            $semstartdate = $DB->get_field('local_program_levels', 'startdate', array('id' => $quizvalues->levelid));
            $sementdate = $DB->get_field('local_program_levels', 'enddate', array('id' => $quizvalues->levelid));

            if ($semstartdate == 0) {
                $doc['startdate'] = 'N/A';
            } else {
                $doc['startdate'] = (date('d-M-Y', $semstartdate));
            }
            if ($sementdate == 0) {
                $doc['enddate'] = 'N/A';
            } else {
                $doc['enddate'] = date('d-M-Y', $sementdate);
            }

            $courseprogress = 0;
            $doc['courseprogress'] = round($courseprogress) . '%';
            $doc['nooftopics'] = $nooftopics;
            $doc['testcount'] = $testcount;
            $doc['assignmentscount'] = $assignmentscount;

 
          /*  $instructor = $DB->get_records_sql("SELECT u.id,u.firstname FROM {user} u JOIN {role_assignments} ra ON ra.userid = u.id WHERE ra.roleid = 3 AND ra.contextid = $coursecontext");
            $facultycount = count($instructor);
            $doc['facultycount'] = $facultycount;

            if ($quizvalues->mandatory == 1) {
                $doc['mandatory'] = 'Core';
            } else {
                $doc['mandatory'] = 'Elective';
            }*/
            $doc['criteria'] = $criteria;
           /* $course_completion_exists = $DB->record_exists_sql("SELECT id FROM {course_completions} cc WHERE cc.course = $quizvalues->courseid AND cc.userid = $USER->id AND cc.timecompleted > 0");
            if ($course_completion_exists) {
                $courseprogress = 'Completed';
                $coursecompleted = 'Completed';
                $doc['courseprogress'] = $courseprogress;
            } else {
                $courseprogress = 'In progress';
                $coursecompleted = 'To be completed';
                $coursecompleted_criteria = 'To be completed based on completion criteria';
                $doc['courseprogress'] = $courseprogress;
                $doc['assignmentscount'] = $assignmentscount;
                $doc['testcount'] = $testcount;
                $data['criterianotselected'][] = $doc;
            }*/
        }
 

        $doc['id'] = $cid;
        $doc['electivecnt'] = $elective->show;
        $doc['message'] = $elective->message;
        $doc['can_access'] = $can_access;
        $doc['dateaccess_message'] = $datemessage;
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

function get_elective_courses1($userid) {
    global $DB, $USER;

    $electivesql = "SELECT plc.id, plc.courseid, pl.programid, pl.id as levelid, pl.level, pl.active, plc.mandatory, pl.has_course_elective, pl.course_elective 
                        FROM {local_program_users} pu 
                        JOIN {local_program_levels} pl ON pl.programid = pu.programid 
                        JOIN {local_program_level_courses} plc ON plc.levelid = pl.id
                        WHERE pu.userid = :userid AND pl.active = 1";
    $elective = $DB->get_record_sql($electivesql, array('userid' => $userid));

    $sql = "SELECT plc.id, plc.courseid, pl.programid, c.fullname, pl.id as levelid, pl.level, pl.active, plc.mandatory, c.fullname, pl.startdate, pl.enddate, plc.parentid 
            FROM {local_program_users} pu 
            JOIN {local_program_levels} pl ON pl.programid = pu.programid 
            JOIN {local_program_level_courses} plc ON plc.levelid = pl.id 
            JOIN {course} c ON c.id = plc.courseid 
            JOIN {enrol} e ON e.courseid = c.id 
            JOIN {user_enrolments} ue ON ue.enrolid = e.id 
            JOIN {role_assignments} ra ON ra.userid = ue.userid 
            JOIN {role} r ON r.id = ra.roleid 
            JOIN {user} u ON u.id = ue.userid 
            -- JOIN {user_lastaccess} ul ON ul.courseid = c.id 
            WHERE ra.userid = :userid AND pl.active = 1 AND plc.mandatory = 0
            GROUP BY plc.courseid ORDER BY plc.courseid DESC
    ";

$elective_courses = $DB->get_records_sql($sql, array('userid'=>$userid));
$cnt = count($elective_courses);

    foreach($elective_courses as $elective_course) {
        $docs = new stdClass();
        
    }

    // $docs->course_elective = $DB->get_field('local_program_levels', 'course_elective', array('programid'=>$elective_course->programid, 'id'=>$elective_course->levelid));
    
   // $message = 'Complete electives selection to start accessing the semester courses...!!!';


    if($elective->course_elective == $cnt){
        $docs->show = 1;
    }else{
        $docs->show = 0;
    }
   // $docs->message = $message;

    $userdocs[] = $docs;
    
    return $userdocs;
}
