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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

defined('MOODLE_INTERNAL') || die();
/**
* Event observer for local_courses. Dont let other user to view unauthorized courses
*/
class local_program_observer extends \core\event\course_completed {
/**
* Triggered via course_completed event.
*
* @param \core\event\course_completed $event
*/
    public static function semester_completed(\core\event\course_completed $event) {
        global $DB, $CFG, $USER, $COURSE, $PAGE;
        $object = new \stdClass();
        $systemcontext = context_system::instance();
        $programid = $DB->get_field('local_program_users', 'programid', array('userid' => $event->data['relateduserid']));
        $semesterid = $DB->get_field('local_program_level_courses','levelid', array('courseid' =>$event->data['courseid']));

        $emaillogs = new \local_program\notification();
        $allow = true;

        // program course completion notification.

        $program_course_completiontype = 'program_course_completion';

        $sqli = "select pu.programid as programid, p.*, pl.level, c.fullname, c.id as cid, pu.userid, cc.timeenrolled, cc.timestarted, cc.timecompleted from 
                    {local_program} p 
                    join {local_program_users} pu on pu.programid = p.id  
                    join {local_program_levels} pl on pl.programid = pu.programid
                    join {local_program_level_courses} plc on plc.levelid = pl.id 
                    join {course} c on c.id = plc.courseid 
                    join {course_completions} cc on cc.course = c.id and cc.userid = pu.userid
                    where pu.userid = {$event->data['relateduserid']} and plc.courseid = {$event->data['courseid']} ";

        $program_course_completion = $DB->get_record_sql($sqli);
 
        // if($allow) {
        //     $touser = \core_user::get_user($program_course_completion->userid);
        //     $email_logs = $emaillogs->program_notification($program_course_completiontype, $touser, $fromuser = get_admin(), $program_course_completion);
        // }

        $programid = $DB->get_field('local_program_users', 'programid', array('userid' => $event->data['relateduserid']));
        $semesterid = $DB->get_field('local_program_level_courses','levelid', array('courseid' =>$event->data['courseid']));

        if($allow){
            $coursecompletion_type = 'course_complete';
            $notification = new \local_courses\notification();
            $course = $DB->get_record('course', array('id' => $event->data['courseid']));
            $user = core_user::get_user($event->data['relateduserid']);
            $notificationdata = $notification->get_existing_notification($course, $coursecompletion_type);
            if($notificationdata){
              $notification->send_course_email($course, $user, $coursecompletion_type, $notificationdata);
            }else if(!$notificationdata){
                $notification = new \local_courses\notification($DB);
                $notification->send_course_completion_notification($course, $user);
            }
        }
        $sem_type = 'program_semester_completion';
        $pro_type = 'program_completion';

        $dataobj = $programid;
        $fromuserid = $USER->id;

        $sql = "SELECT bclc.courseid AS courseid
                        FROM {local_program_level_courses} bclc
                        JOIN {user} u
                        JOIN {user_enrolments} ue ON ue.userid=u.id
                        JOIN {enrol} e ON e.id=ue.enrolid
                        JOIN {course} c ON c.id = e.courseid and c.id = bclc.courseid
                       JOIN {local_program_levels} lpl ON lpl.id = bclc.levelid and lpl.programid = bclc.programid WHERE u.id = {$event->data['relateduserid']} AND lpl.id = $semesterid";
        $semestercourses = $DB->get_records_sql($sql);
        $semcoursecount = count($semestercourses);
        $count = 0;
        $userid = $event->data['relateduserid'];
        foreach ($semestercourses as $value) {
            // $coursecompletionexists = $DB->record_exists('course_completions', array('course' => $value->courseid, 'userid' => $event->data['relateduserid']));
            $coursecompletionexistssql = "SELECT id
                                            FROM {course_completions}
                                           WHERE course = {$value->courseid}
                                            AND userid = {$userid} AND timecompleted > 0";
            $coursecompletionexists = $DB->record_exists_sql($coursecompletionexistssql);
            if($coursecompletionexists) {
                $count++;
            }
        }
        if($semcoursecount == $count) {
            $semcompletionexists = $DB->record_exists('local_semesters_completions',
                                                            array(
                                                                'programid' => $programid,
                                                                'userid' => $event->data['relateduserid'],
                                                                'levelid' => $semesterid
                                                            )
                                                        );
            if (!$semcompletionexists) {
                $object->programid = $programid;
                $object->levelid = $semesterid;
                $object->userid = $event->data['relateduserid'];
                $object->timecompleted = time();
                $object->id = $DB->insert_record('local_semesters_completions', $object);

                $semcom_record = $DB->get_record('local_semesters_completions', array('id' => $object->id));
                $local_program = $DB->get_record('local_program', array('id' => $semcom_record->programid));

                if($object->id) {
                    $touser = \core_user::get_user($semcom_record->userid);
                    $email_logs = $emaillogs->program_notification($sem_type, $touser, $fromuser = get_admin(), $local_program);
                }
            } else {
                return null;
            }
        } else {
            return null;
        }

        // program completion code starts
        $semesters = $DB->get_records_sql("SELECT * FROM {local_program_levels} WHERE programid = $programid");
        $programsemestercount = $DB->count_records_sql("SELECT count(id) FROM {local_program_levels} WHERE programid = $programid");
        $programsemcount = 0;
        foreach ($semesters as $value) {
            $semestercompletionsexists = $DB->record_exists('local_semesters_completions', array('levelid' => $value->id, 'userid' => $event->data['relateduserid']));
            if($semestercompletionsexists){
                $programsemcount++;
            }
        }
        $data = new \stdClass();
        if($programsemestercount == $programsemcount) {
            $programcompletionexists = $DB->record_exists('local_programcompletions',
                                                            array(
                                                                'programid' => $programid,
                                                                'userid' => $event->data['relateduserid'],
                                                            )
                                                        );
            if (!$programcompletionexists) {
                $data->programid = $programid;
                $data->userid = $event->data['relateduserid'];
                $data->completionstatus = time();
                $data->usercreated = $event->data['userid'];
                $data->timecreated = time();
                $insertdata->id = $DB->insert_record('local_programcompletions', $data);


                if ($insertdata->id) {
                    $procom_record = $DB->get_record('local_programcompletions', array('id' => $insertdata->id));
                    $local_programcom = $DB->get_record('local_program', array('id' => $procom_record->programid));

                    if($allow) {
                        $tousers = \core_user::get_user($procom_record->userid);
                        $email_logs = $emaillogs->program_notification($pro_type , $tousers, $fromuser = get_admin(), $local_programcom);
                    }
                }

                if($insertdata->id){
                    $templateid = $DB->get_field('local_program', 'certificateid', array('id'=> $programid));
                    $programname = $DB->get_field('local_program', 'shortname', array('id'=> $programid));
                    $cretificateexists = $DB->record_exists('tool_certificate_issues', array('userid' => $event->data['relateduserid'], 'moduleid' => $programid));
                    if (!$cretificateexists) {
                        $certificatedata = new \stdClass();
                        $certificatedata->userid = $event->data['relateduserid'];
                        $certificatedata->templateid = $templateid;
                        $certificatedata->moduleid = $programid;
                        $certificatedata->code = $programname.$event->data['relateduserid'];
                        $certificatedata->moduletype = 'program';
                        $certificatedata->timecreated = time();
                        $certificatedata->emailed = 1;
                        $object->id = $DB->insert_record('tool_certificate_issues', $certificatedata);

                    } else {
                        return null;
                    }
                }
            } else {
                return null;
            }
        } else {
            return null;
        }
    }
}
