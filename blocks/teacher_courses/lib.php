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
require_once($CFG->dirroot.'/local/includes.php');

/**
 * Description: Course/Subject filter code
 * @param  [mform object]  $mform[the form object where the form is initiated]
 */
function teacher_courses_filter($mform){
    global $DB, $USER;
    $systemcontext = context_system::instance();
    $params = array('studentid' => $USER->id);
    $sql = "SELECT c.id,c.fullname 
             FROM {user} u
             JOIN {role_assignments} ra ON ra.userid = u.id
             JOIN {role} r ON r.id = ra.roleid AND r.shortname = 'editingteacher'
             JOIN {context} ctx ON ctx.id = ra.contextid
             JOIN {course} c ON c.id = ctx.instanceid
            WHERE u.id = :studentid";

    $courselist = $DB->get_records_sql_menu($sql, $params);

    $select = $mform->addElement('autocomplete', 'course', '', $courselist,
                                    array(
                                        'placeholder' => get_string('course','block_teacher_courses')
                                    )
                                );
    $mform->setType('course', PARAM_RAW);
    $select->setMultiple(true);
}

function get_openteacher_courses($stable, $filterdata){

     global $DB, $USER, $CFG;
    require_login();
    require_once($CFG->dirroot.'/local/includes.php');
    $currenttime = time();
    $params = array();
      $params['studentid'] = $stable->studentid;

      $countsql = "SELECT count(c.id) ";

       // $selectsql = " SELECT c.id,c.fullname ";
        $selectsql = " SELECT c.id , c.fullname,c.open_identifiedas ";

        /* $fromsql = " FROM {user} u
                   JOIN {role_assignments} ra ON ra.userid = u.id
                   JOIN {role} r ON r.id = ra.roleid AND r.shortname = 'editingteacher'
                   JOIN {context} ctx ON ctx.id = ra.contextid
                   JOIN {course} c ON c.id = ctx.instanceid
                   JOIN {attendance} a ON a.course = c.id  ";*/

        $fromsql = "FROM mdl_course AS c JOIN mdl_context ct ON ct.instanceid = c.ID AND ct.contextlevel = 50 JOIN mdl_role_assignments ra ON ra.CONTEXTID = ct.ID JOIN mdl_user u ON u.ID = ra.userid  AND c.open_identifiedas = 6";

       $fromsql .= " WHERE u.id = $USER->id";

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


        $students = "SELECT u.id as sid,u.firstname,u.lastname
                      FROM {user} u
                      JOIN {role_assignments} ra ON ra.userid = u.id
                      JOIN {role} r ON r.id=ra.roleid AND r.shortname = 'student'
                      JOIN {context} ctx ON ctx.id = ra.contextid
                      JOIN {course} c ON c.id = ctx.instanceid
                       
                     WHERE c.id = $course->id ";
        $sturec = $DB->get_records_sql($students);
        $sturecords = count($sturec);
         
        $userattendedsql = "SELECT COUNT(stat.id) as statuscount
                                FROM {attendance_log} al
                                JOIN {attendance_sessions} ats ON al.sessionid = ats.id
                                JOIN {attendance} a ON ats.attendanceid = a.id
                                JOIN {attendance_statuses} stat ON al.statusid = stat.id
                                 
                                AND stat.acronym IN ('P','L') 
                                AND ats.teacherid=$USER->id /*AND ats.lasttakenby = $USER->id */
                                AND (ats.sessdate+ats.duration) < ($currenttime)";

        $userattended = $DB->count_records_sql($userattendedsql);

        $totalattdencesql = "SELECT COUNT(DISTINCT(alog.id)) 
                                FROM {attendance_log} alog 
                                JOIN {attendance_sessions} ases ON ases.id = alog.sessionid 
                                JOIN {attendance} att ON att.id = ases.attendanceid 
                                WHERE att.course = $course->id AND ases.teacherid=$USER->id /*AND ases.lasttakenby = $USER->id*/ AND (ases.sessdate+ases.duration) < ($currenttime)";
        $totalattdence = $DB->count_records_sql($totalattdencesql);

        if ($userattended && $totalattdence > 0) {
            $attendancepercentage = ($userattended / $totalattdence) * 100;
        } else {
            $attendancepercentage = 0;
        }

$courseprogress = 0;

        $students = $DB->get_records_sql("SELECT u.id as userid
                      FROM {user} u
                      JOIN {role_assignments} ra ON ra.userid = u.id
                      JOIN {role} r ON r.id=ra.roleid AND r.shortname = 'student'
                      JOIN {context} ctx ON ctx.id = ra.contextid
                      JOIN {course} c ON c.id = ctx.instanceid
                       
                     WHERE c.id = $course->id");

            $participant = 0;
            $participantcount = 0;
            foreach($students as $student){

                $params['id'] = $course->id;
                $params['studentsid'] = $studentsid;
                $totalmodules = "SELECT COUNT(*) FROM {course_modules} cm 
                                WHERE cm.course = :id";
                
                $totalmodules = "SELECT COUNT(cm.id) FROM {course_modules} cm 
                                        WHERE cm.course = $course->id AND cm.completion = 1";

                $completedmodules = "SELECT COUNT(cmc.id) FROM {user} u  
                                JOIN {course_modules_completion} cmc ON u.id = cmc.userid
                                JOIN {course_modules} cm ON cm.id = cmc.coursemoduleid 
                                WHERE cm.course = $course->id AND u.id= $student->userid";

                $completedmodulescount = $DB->count_records_sql($completedmodules);
                $totalmodulescount = $DB->count_records_sql($totalmodules);

                $courseprogress = $completedmodulescount/$totalmodulescount*100;
                $participant +=($courseprogress);
                $participantcount +=count($courseprogress);

            }
            if($students){
                $participantaverage = ($participant / $participantcount);
            } else{
                $participantaverage = 0;   
            }

            if ($participantaverage == 100 || $participantaverage == 0) {
                    $participantsaverages = intval($participantaverage );
            } else {
                $participantsaverages = number_format($participantaverage, 2, '.', '.');
            }


              if (file_exists($CFG->dirroot.'/local/includes.php')) {
                require_once($CFG->dirroot.'/local/includes.php');
                $includes = new user_course_details();

                  $parentcourseid = $DB->get_field('local_program_level_courses','parentid',array('courseid'=> $course->id));
                 
                $parentcourseexists = $DB->record_exists('course', array('id' => $parentcourseid));
                if($parentcourseexists){
                    $image_url = $DB->get_record('course',array('id'=> $parentcourseid));
                    if($image_url) {
                        $courseimage = $includes->course_summary_files($image_url);

                        if (is_object($courseimage)) {
                            $line['courseimage'] = $courseimage->out();                    
                        } else {
                            $line['courseimage'] = $courseimage;
                        }
                    }            
                } else {
                    $programcourseimageurl = $DB->get_record('course',array('id'=> $course->id));
                    if($programcourseimageurl){
                        $programcourseimage = $includes->course_summary_files($programcourseimageurl);
                        if (is_object($programcourseimage)) {
                            $line['courseimage'] = $programcourseimage->out();                    
                        }else{
                            $line['courseimage'] = $programcourseimage;
                        }
                    }
                }
            }

$coursefullname = $class->fullname;
                if (strlen($class->fullname) > 25) {
                    $class->fullname = substr($class->fullname, 0, 25).'...';
                }

                $line['cfullname'] = $coursefullname;
                $line['cid'] = $course->id;
                $line['fullname'] = $class->fullname;
                 
            $attendanceid = $DB->get_field('attendance', 'id', array('course' => $course->id));
           

 
            $datesofprevious =("SELECT count(id)
                                FROM {attendance_sessions}
                                WHERE   teacherid = $USER->id
                                
                                AND (sessdate+duration) < ($currenttime)");
            $totalsessions =$DB->count_records_sql($datesofprevious);

            if ($attendancepercentage == 100 || $attendancepercentage == 0) {
                $participantsaverage = intval($attendancepercentage );
            } else {
            $participantsaverage = number_format($attendancepercentage, 2, '.', '.');

            }
            $line['open_identifiedas'] = $class->open_identifiedas;
            $line['sessiondelivered'] = $totalsessions;
            $line['studentcount'] = $sturecords;
            $line['teacherid'] = $USER->id;
            $line['attendancepercentage'] = $participantsaverage;
            // $line['courseparticipants'] = $participantsaverages;
            $line['cfg_url'] = $CFG->wwwroot;
            $data[] = $line;
        }

 
 
        $coursedata = [
            'hascourses' => $data,
            'length' => count($data),
            'count' => $count,
        ];
 
    return $coursedata; 
   
}
function get_teacher_courses($stable, $filterdata) {
    global $DB, $USER, $CFG;
    require_login();
    require_once($CFG->dirroot.'/local/includes.php');
    $currenttime = time();
    $params = array();
    $params['studentid'] = $stable->studentid;

    $countsql = "SELECT count(c.id) ";

    $selectsql = " SELECT c.id,c.fullname ";

    $fromsql = " FROM {user} u
                   JOIN {role_assignments} ra ON ra.userid = u.id
                   JOIN {role} r ON r.id = ra.roleid AND r.shortname = 'editingteacher'
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
        if($course->id){

            $programname = $DB->get_field_sql("SELECT lp.name FROM {local_program} lp JOIN {local_program_level_courses} lplc ON lp.id = lplc.programid WHERE lplc.courseid = $course->id");
            }

        $students = "SELECT u.id as sid,u.firstname,u.lastname
                      FROM {user} u
                      JOIN {role_assignments} ra ON ra.userid = u.id
                      JOIN {role} r ON r.id=ra.roleid AND r.shortname = 'student'
                      JOIN {context} ctx ON ctx.id = ra.contextid
                      JOIN {course} c ON c.id = ctx.instanceid
                      JOIN {local_program_level_courses} plc ON plc.courseid=c.id
                      JOIN {local_program} p ON p.id = plc.programid
                     WHERE c.id = $course->id ";
        $sturec = $DB->get_records_sql($students);
        $sturecords = count($sturec);
        // $userattendedsql = "SELECT COUNT(stat.id) as statuscount
        //                     FROM {attendance_log} al
        //                     JOIN {attendance_sessions} ats ON al.sessionid = ats.id
        //                     JOIN {attendance} a ON ats.attendanceid = a.id
        //                     JOIN {attendance_statuses} stat ON al.statusid = stat.id
        //                     JOIN {local_program_level_courses} lplc ON a.course = lplc.courseid
        //                     JOIN {local_program_levels} pl ON lplc.programid = pl.programid
        //                     AND lplc.levelid = pl.id
        //                     WHERE lplc.courseid = $course->id
        //                     AND stat.acronym IN ('P','L') 
        //                     AND ats.teacherid=$USER->id AND ats.lasttakenby = $USER->id
        //                     AND DATE(FROM_UNIXTIME(ats.sessdate, '%Y-%m-%d')) < DATE(NOW())";
        $userattendedsql = "SELECT COUNT(stat.id) as statuscount
                                FROM {attendance_log} al
                                JOIN {attendance_sessions} ats ON al.sessionid = ats.id
                                JOIN {attendance} a ON ats.attendanceid = a.id
                                JOIN {attendance_statuses} stat ON al.statusid = stat.id
                                JOIN {local_program_level_courses} lplc ON a.course = lplc.courseid
                                JOIN {local_program_levels} pl ON lplc.programid = pl.programid
                                AND lplc.levelid = pl.id
                                WHERE lplc.courseid = $course->id
                                AND stat.acronym IN ('P','L') 
                                AND ats.teacherid=$USER->id /*AND ats.lasttakenby = $USER->id */
                                AND (ats.sessdate+ats.duration) < ($currenttime)";

        $userattended = $DB->count_records_sql($userattendedsql);

        $totalattdencesql = "SELECT COUNT(DISTINCT(alog.id)) 
                                FROM {attendance_log} alog 
                                JOIN {attendance_sessions} ases ON ases.id = alog.sessionid 
                                JOIN {attendance} att ON att.id = ases.attendanceid 
                                WHERE att.course = $course->id AND ases.teacherid=$USER->id /*AND ases.lasttakenby = $USER->id*/ AND (ases.sessdate+ases.duration) < ($currenttime)";
        $totalattdence = $DB->count_records_sql($totalattdencesql);

        if ($userattended && $totalattdence > 0) {
            $attendancepercentage = ($userattended / $totalattdence) * 100;
        } else {
            $attendancepercentage = 0;
        }

        $courseprogress = 0;

        $students = $DB->get_records_sql("SELECT u.id as userid
                      FROM {user} u
                      JOIN {role_assignments} ra ON ra.userid = u.id
                      JOIN {role} r ON r.id=ra.roleid AND r.shortname = 'student'
                      JOIN {context} ctx ON ctx.id = ra.contextid
                      JOIN {course} c ON c.id = ctx.instanceid
                      JOIN {local_program_level_courses} plc ON plc.courseid=c.id
                      JOIN {local_program} p ON p.id = plc.programid
                     WHERE c.id = $course->id");

            $participant = 0;
            $participantcount = 0;
            foreach($students as $student){

                $params['id'] = $course->id;
                $params['studentsid'] = $studentsid;
                $totalmodules = "SELECT COUNT(*) FROM {course_modules} cm 
                                WHERE cm.course = :id";
                
                $totalmodules = "SELECT COUNT(cm.id) FROM {course_modules} cm 
                                        WHERE cm.course = $course->id AND cm.completion = 1";

                $completedmodules = "SELECT COUNT(cmc.id) FROM {user} u  
                                JOIN {course_modules_completion} cmc ON u.id = cmc.userid
                                JOIN {course_modules} cm ON cm.id = cmc.coursemoduleid 
                                WHERE cm.course = $course->id AND u.id= $student->userid";

                $completedmodulescount = $DB->count_records_sql($completedmodules);
                $totalmodulescount = $DB->count_records_sql($totalmodules);

                $courseprogress = $completedmodulescount/$totalmodulescount*100;
                $participant +=($courseprogress);
                $participantcount +=count($courseprogress);

            }
            if($students){
                $participantaverage = ($participant / $participantcount);
            } else{
                $participantaverage = 0;   
            }

            if ($participantaverage == 100 || $participantaverage == 0) {
                    $participantsaverages = intval($participantaverage );
            } else {
                $participantsaverages = number_format($participantaverage, 2, '.', '.');
            }
                
            /*Course image code starts here*/
            if (file_exists($CFG->dirroot.'/local/includes.php')) {
                require_once($CFG->dirroot.'/local/includes.php');
                $includes = new user_course_details();

                $parentcourseid = $DB->get_field('local_program_level_courses','parentid',array('courseid'=> $course->id));
                
                $parentcourseexists = $DB->record_exists('course', array('id' => $parentcourseid));
                if($parentcourseexists){
                    $image_url = $DB->get_record('course',array('id'=> $parentcourseid));
                    if($image_url) {
                        $courseimage = $includes->course_summary_files($image_url);

                        if (is_object($courseimage)) {
                            $line['courseimage'] = $courseimage->out();                    
                        } else {
                            $line['courseimage'] = $courseimage;
                        }
                    }            
                } else {
                    $programcourseimageurl = $DB->get_record('course',array('id'=> $course->id));
                    if($programcourseimageurl){
                        $programcourseimage = $includes->course_summary_files($programcourseimageurl);
                        if (is_object($programcourseimage)) {
                            $line['courseimage'] = $programcourseimage->out();                    
                        }else{
                            $line['courseimage'] = $programcourseimage;
                        }
                    }
                }
            }
            /*Course image code ends here*/

                $coursefullname = $class->fullname;
                if (strlen($class->fullname) > 25) {
                    $class->fullname = substr($class->fullname, 0, 25).'...';
                }

                $line['cfullname'] = $coursefullname;
                $line['cid'] = $course->id;
                $line['fullname'] = $class->fullname;
                $line['programname'] = $programname;
            $attendanceid = $DB->get_field('attendance', 'id', array('course' => $course->id));
            // $datesofprevious = $DB->count_records_sql("SELECT count(*)
            //                                        FROM {attendance_sessions} ass
            //                                       WHERE attendanceid = $attendanceid
            //                                             AND DATE(FROM_UNIXTIME(ass.sessdate, '%Y-%m-%d')) < DATE(NOW())");
            // $datesofprevious =("SELECT count(ass.id) as total
            //                         FROM {attendance_sessions} ass
            //                         WHERE ass.lasttakenby = $USER->id 
            //                         and ass.attendanceid = $attendanceid
            //                         AND ass.teacherid=$USER->id 
            //                         AND ass.lasttakenby = $USER->id
            //                         AND DATE(FROM_UNIXTIME(ass.sessdate, '%Y-%m-%d')) < DATE(NOW())");

            $datesofprevious =("SELECT count(id)
                                FROM {attendance_sessions}
                                WHERE /*ass.lasttakenby = $USER->id 
                                and*/ teacherid = $USER->id
                                AND attendanceid = $attendanceid
                                AND (sessdate+duration) < ($currenttime)");
            $totalsessions =$DB->count_records_sql($datesofprevious);

            if ($attendancepercentage == 100 || $attendancepercentage == 0) {
                $participantsaverage = intval($attendancepercentage );
            } else {
            $participantsaverage = number_format($attendancepercentage, 2, '.', '.');

            }
            $line['sessiondelivered'] = $totalsessions;
            $line['studentcount'] = $sturecords;
            $line['teacherid'] = $USER->id;
            $line['open_identifiedas'] = $class->open_identifiedas;
            $line['attendancepercentage'] = $participantsaverage;
            // $line['courseparticipants'] = $participantsaverages;
            $line['cfg_url'] = $CFG->wwwroot;
            $data[] = $line;
        }

        $coursedata = [
            'hascourses' => $data,
            'length' => count($data),
            'count' => $count,
        ];
    return $coursedata; 
}

