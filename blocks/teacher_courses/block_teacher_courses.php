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
 * @package   block_teacher_courses
 * @copyright 2022 eAbyas Info Solutions Pvt. Ltd.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_teacher_courses extends block_base {
    public function init() {
        $this->title = get_string('teachercourse', 'block_teacher_courses');
    }
    public function get_required_javascript() {
        $systemcontext = context_system::instance();
        $this->page->requires->jquery();
        // $this->page->requires->js_call_amd('block_teacher_courses/table', 'load', array());
        $this->page->requires->js_call_amd('local_employees/studentpopup', 'init', array(array('contextid' => $systemcontext->id, 'selector' => '.rolesstudentpopup')));
        // teacher_courses_table
    }
    public function get_content() {
        global $OUTPUT, $DB, $USER, $COURSE, $CFG;
        require_login();
        if ($this->content !== null) {
            return $this->content;
        }
        require_once($CFG->dirroot.'/local/includes.php');
        $currenttime = time();
        // <img class="card-img-top" src="{{{courseimage}}}" alt="Card image" style="width:100%;">
        $params = array();
        $params['editingteacher'] = 'editingteacher';
        $params['userid'] = $USER->id;
        $coursesql = "SELECT   c.*
                   FROM {user} u
                   JOIN {role_assignments} ra ON ra.userid = u.id
                   JOIN {role} r ON r.id = ra.roleid AND r.shortname = :editingteacher
                   JOIN {context} ctx ON ctx.id = ra.contextid
                   JOIN {course} c ON c.id = ctx.instanceid
                   JOIN {attendance} a ON a.course = c.id
                  WHERE u.id = :userid";

        $courses = $DB->get_records_sql($coursesql, $params);
        $data = array();
        $count = 0;
            // $programname = $DB->get_field('local_program_level_courses','programid',array('courseid'=> $course->id));
        foreach ($courses as $course) {
            $line = array();
            if($course->id){

            $programname = $DB->get_field_sql("SELECT lp.name FROM {local_program} lp JOIN {local_program_level_courses} lplc ON lp.id = lplc.programid WHERE lplc.courseid = $course->id");
            }
            $class = $DB->get_record('course', array('id' => $course->id));
            // $students = "SELECT COUNT(u.id)
            //               FROM {user} u
            //               JOIN {role_assignments} ra ON ra.userid = u.id
            //               JOIN {role} r ON r.id = ra.roleid AND r.shortname = 'student'
            //               JOIN {context} AS ctx ON ctx.id = ra.contextid
            //               JOIN {course} c ON c.id = ctx.instanceid
            //               JOIN {local_program_level_courses} plc ON plc.courseid=c.id
            //               JOIN {local_program} p ON p.id = plc.programid
            //               JOIN {cohort} co ON co.id = p.batchid
            //              WHERE c.id = $course->id";
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
            // print_r($sturecords);
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
                                AND (ats.sessdate+ats.duration) < $currenttime";

            $userattended = $DB->count_records_sql($userattendedsql);

            $totalattdencesql = "SELECT COUNT(DISTINCT(alog.id)) 
                                FROM {attendance_log} alog 
                                JOIN {attendance_sessions} ases ON ases.id = alog.sessionid 
                                JOIN {attendance} att ON att.id = ases.attendanceid 
                                WHERE att.course = $course->id AND ases.teacherid=$USER->id /*AND ases.lasttakenby = $USER->id*/ AND (ases.sessdate+ases.duration) < $currenttime";
            $totalattdence = $DB->count_records_sql($totalattdencesql);

            if ($userattended > 0 && $totalattdence > 0) {
                $attendancepercentage = (($userattended / $totalattdence) * 100);
            } else {
                $attendancepercentage = 0;
              }

              $courseprogress = 0;
              $students =$DB->get_records_sql("SELECT u.id as userid
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
              $criteria_exists = $DB->count_records_sql("SELECT COUNT(ccc.id) FROM {course_completion_criteria} as ccc WHERE ccc.course = $course->id AND ccc.criteriatype = 4");
                foreach($students as $student) {
                    if($criteria_exists > 1) {
                        $totalmodules = "SELECT COUNT(cm.id) FROM {course_modules} cm 
                                            WHERE cm.course = $course->id AND cm.completion = 1";

                        $totalmodulescount = $DB->count_records_sql($totalmodules);
                        $completedmodules = "SELECT COUNT(cmc.id) FROM {user} u  
                                    JOIN {course_modules_completion} cmc ON u.id = cmc.userid
                                    JOIN {course_modules} cm ON cm.id = cmc.coursemoduleid 
                                    WHERE cm.course = $course->id AND u.id=$student->userid AND cmc.completionstate = 1";

                        $completedmodulescount = $DB->count_records_sql($completedmodules);

                        $courseprogress = $completedmodulescount/$totalmodulescount*100;
                        $participant +=($courseprogress);
                        $participantcount +=count($courseprogress);
                    } else {
                        $course_completions_exists = $DB->record_exists('course_completions',array('course' => $course->id, 'userid'=> $student->userid));
                        $criteria = true; 
                        if($course_completions_exists) {
                            $participantsaverages = 'Completed';
                        } else{
                            $participantsaverages = 'Inprogress';
                        }
                     }
                }
                if(!$criteria){
                    if($students){
                        $participantaverage = ($participant / $participantcount);
                    }
                    else{
                        $participantaverage = 0;   
                    }

                    if ($participantaverage == 100 || $participantaverage == 0) {
                        $participantsaverages = intval($participantaverage );
                    } else {
                    $participantsaverages = number_format($participantaverage, 2, '.', '.');

                    }
                } 
                $line['criteria'] = $criteria;
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


                // if (is_object($courseimage)) {
                //     $line['courseimage'] = $courseimage->out();                    
                // } else {
                //     $line['courseimage'] = $courseimage;
                // }  
                $coursefullname = $class->fullname;
                if (strlen($class->fullname) > 25) {
                    $class->fullname = substr($class->fullname, 0, 25).'...';
                }

                $line['cid'] = $course->id;
                $line['fullname'] = $class->fullname;
                $line['programname'] = $programname;
                $line['cfullname'] = $coursefullname;
                $line['open_identifiedas'] = $class->open_identifiedas;
                
 
                $attendanceid = $DB->get_field('attendance', 'id', array('course' => $course->id));
                // $datesofprevious = $DB->count_records_sql("SELECT count(*)
                //                                    FROM {attendance_sessions} ass
                //                                   WHERE attendanceid = $attendanceid
                //                                         AND DATE(FROM_UNIXTIME(ass.sessdate, '%Y-%m-%d')) < DATE(NOW())");
                
                $datesofprevious =("SELECT COUNT(id)
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
                $line['attendancepercentage'] = $participantsaverage;
                $line['courseimage'] = $courseimage;
                $data[] = $line;

                $count++;
                if($count >= 5){
                    break;
                }

        }


        $coursecounts =count($data);
        if($coursecounts  >= 5){
            $counts = $coursecounts;
        }
        $teacher = $DB->get_records_sql("SELECT DISTINCT(r.id),r.shortname
                                    FROM {role} r
                                    JOIN {role_assignments} ra ON ra.roleid = r.id
                                    JOIN {user} u ON u.id = ra.userid
                                    JOIN {user_enrolments} ue on ue.userid = u.id 
                                    JOIN {enrol} e on e.id = ue.enrolid 
                                    WHERE u.id = {$USER->id}
                                    AND r.shortname = 'editingteacher'");
 
  $openenrolusers = "SELECT cr.id , cr.fullname,cr.open_identifiedas FROM mdl_course AS cr JOIN mdl_context ct ON ct.instanceid = cr.ID AND ct.contextlevel = 50 JOIN mdl_role_assignments ra ON ra.CONTEXTID = ct.ID JOIN mdl_user u ON u.ID = ra.userid  AND cr.open_identifiedas = 6 AND u.ID=$USER->id LIMIT 5";
 

 $opencourse = $DB->get_records_sql($openenrolusers, array('userid' => $USER->id, 'active' => 1));
 
 

    foreach ($opencourse as $key) {

            
                $cid = $key->id;

                $line1['cid'] = $key->id;
                  $line1['fullname'] = $key->fullname;
                
                

$students =$DB->get_records_sql("SELECT u.id as userid
                      FROM {user} u
                      JOIN {role_assignments} ra ON ra.userid = u.id
                      JOIN {role} r ON r.id=ra.roleid AND r.shortname = 'student'
                      JOIN {context} ctx ON ctx.id = ra.contextid
                      JOIN {course} c ON c.id = ctx.instanceid
                      AND c.open_identifiedas = 6 
                      WHERE c.id = $cid
                      ");
 

   $studentcount = count($students);
 
  $line1['noofstudent'] = $studentcount;


     $attendanceid = $DB->get_field('attendance', 'id', array('course' => $cid));

if($attendanceid){
    $attendanceid = $attendanceid;
}else{
    $attendanceid = 0;
}
 
   $datesofprevious =("SELECT COUNT(id)
                                    FROM {attendance_sessions}
                                    WHERE   teacherid = $USER->id
                                    AND attendanceid = $attendanceid
                                    AND (sessdate+duration) < ($currenttime)");

   $totalsessions =$DB->count_records_sql($datesofprevious);

                if ($attendancepercentage == 100 || $attendancepercentage == 0) {
                 $participantsaverage = intval($attendancepercentage );
                } else {
                $participantsaverage = number_format($attendancepercentage, 2, '.', '.');

                }


       if (file_exists($CFG->dirroot.'/local/includes.php')) {
                    require_once($CFG->dirroot.'/local/includes.php');
                    $includes = new user_course_details();

                    
                    $parentcourseexists = $DB->record_exists('course', array('id' => $cid));
                    if($parentcourseexists){

                        $image_url = $DB->get_record('course',array('id'=> $cid));
                        if($image_url) {
                            $courseimage = $includes->course_summary_files($image_url);

                            if (is_object($courseimage)) {
                                $line1['courseimage'] = $courseimage->out();                    
                            } else {
                                $line1['courseimage'] = $courseimage;
                            }
                        }            
                    } else {
                        $programcourseimageurl = $DB->get_record('course',array('id'=> $cid));
                        if($programcourseimageurl){
                            
                            $programcourseimage = $includes->course_summary_files($programcourseimageurl);
                            if (is_object($programcourseimage)) {
                                $line1['courseimage'] = $programcourseimage->out();                    
                            }else{
                                $line1['courseimage'] = $programcourseimage;
                            }
                        }
                    }
                }

                $line1['sessiondelivered'] = $totalsessions;
                $line1['teacherid'] = $USER->id;
                $line1['attendancepercentage'] = $participantsaverage; 
                $line1['open_identifiedas'] = $key->open_identifiedas; 

                $data1[] = $line1;

    }

    $coursecounts1 =count($data1);
        if($coursecounts1  >= 5){
            $counts1 = $coursecounts1;
        }

        $coursedata = [
            'coursecounts' => $counts,
            'opencount' => $counts1,
            'courses' => $data,
            'opencourse'=>$data1,
        ];

        if ($teacher) {
            $this->content = new \stdClass();

            $this->content->text = $OUTPUT->render_from_template('block_teacher_courses/index', $coursedata);
        }
        return $this->content;
    }
}
