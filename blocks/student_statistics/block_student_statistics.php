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
 * This file contains the news item block class, based upon block_base.
 *
 * @package    block_student_statistics
 * @copyright  Moodle India
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_student_statistics extends block_base {
    function init() {
        if (is_siteadmin()) {
            $this->title = get_string('pluginname', 'block_student_statistics');  
        } else if (!is_siteadmin()) {
            $this->title = get_string('titledisplay', 'block_student_statistics');
        } else {
            $this->title = get_string('pluginname', 'block_student_statistics');
        }
    }
    function get_content() {
        global $OUTPUT, $DB, $USER, $COURSE, $CFG;
        $systemcontext = context_system::instance();
        require_login();
        // $this->page->requires->css('/blocks/student_statistics/style.css');
        if ($this->content !== null) {
            return $this->content;
        }
        $userid = $USER->id;
        $params = array();
        $params['userid'] = $userid;

        $userrecord = $DB->get_record('user', array('id' => $userid));
        // user image
        $userimage = $OUTPUT->user_picture($userrecord, array('size' => 80, 'link' => false));
        $enrolledprogramid = $DB->get_field('local_program_users', 'programid', array('userid' => $userid));
        $enrolledprogramurl = $CFG->wwwroot.'/local/program/view.php?bcid='.$enrolledprogramid;
        $progdetails = $DB->get_record('local_program', array('id' => $enrolledprogramid));
        $batchname = $DB->get_field('cohort', 'name', array('id' => $progdetails->batchid));

        $enrolledcoursessql = "SELECT c.id,c.fullname,plc.programid as programid,
                                u.id as userid
                                FROM {user} u
                                JOIN {role_assignments} ra ON ra.userid = u.id
                                JOIN {role} r ON r.id = ra.roleid 
                                AND r.shortname = 'student'
                                JOIN {context} ctx ON ctx.id = ra.contextid
                                JOIN {course} c ON c.id = ctx.instanceid
                                JOIN {local_program_level_courses} plc ON plc.courseid=c.id
                                JOIN {local_program_levels} pl ON pl.id = plc.levelid 
                                AND pl.programid = plc.programid
                                WHERE u.id = :userid";
        $enrolledcourses = $DB->get_records_sql($enrolledcoursessql, $params);

        if (count($enrolledcourses) > 0) {
            $enrolledcoursescount = count($enrolledcourses);
            $enrolcsids = implode(',', array_keys($enrolledcourses));
            $completionssql = "SELECT count(id) FROM {course_completions} WHERE timecompleted > 0 AND userid = :userid AND course IN($enrolcsids)";
            $coursescompleted = $DB->count_records_sql($completionssql, $params);

            if ($coursescompleted > 0) {
                $completedcount = $coursescompleted;
            } else {
                $completedcount = 0;
            }
            
            $totallevels = $DB->get_records('local_program_levels', array('programid' => $enrolledprogramid));
            $totallevelscount = count($totallevels);
            $levelcompletedcount = 0;
            foreach ($totallevels as $level) {
                $levelcompletion = $DB->record_exists('local_semesters_completions', array('programid' => $level->programid, 'levelid' => $level->id, 'userid' => $userid));
                if ($levelcompletion) {
                    $levelcompletedcount++;
                }
            }
            if ($levelcompletedcount > 0) {
                $programprogrss = ($levelcompletedcount / $totallevelscount) * 100;
                $percentage = number_format($programprogrss, 2, '.', '.');
                $percentage = round($percentage);
            } else {
                $percentage = 0;
            }
        } else {
            $percentage = 0;
        }
        
        // To get the marked sessions in active semester.
        $userattendedsql = "SELECT COUNT(stat.id) as statuscount
                              FROM {attendance_log} al
                              JOIN {attendance_sessions} ats ON al.sessionid = ats.id
                              JOIN {attendance} a ON ats.attendanceid = a.id
                              JOIN {attendance_statuses} stat ON al.statusid = stat.id
                              JOIN {local_program_level_courses} lplc ON a.course = lplc.courseid
                              JOIN {local_program_levels} pl ON lplc.programid = pl.programid
                              AND lplc.levelid = pl.id
                             WHERE al.studentid = :userid AND stat.acronym IN ('P','L')
                              AND pl.active = 1";

        $userattended = $DB->count_records_sql($userattendedsql, $params);

        $programsems = $DB->get_record_sql("SELECT lplc.programid,lplc.levelid
                                        FROM {local_program_level_courses} lplc
                                        JOIN {local_program_levels} pl ON lplc.programid = pl.programid
                                        AND lplc.levelid = pl.id
                                        JOIN {local_program_users} pu ON lplc.programid = pu.programid
                                       WHERE pl.active = 1 AND pu.userid = {$userid}");

        if ($programsems == null) {
            $programsems = new \stdClass;
            $programsems->programid = 0;
            $programsems->levelid = 0;
        }
        $params['programid'] = $programsems->programid;
        $params['levelid'] = $programsems->levelid;
        $params['student'] = 'student';

        // To get the all sessions in active semester.
        $totalattdencesql = "SELECT COUNT(DISTINCT(ats.id))
                              FROM {attendance_sessions} ats
                              JOIN {attendance} a ON a.id = ats.attendanceid
                              JOIN {local_program_level_courses} lplc ON a.course = lplc.courseid
                              JOIN {local_program_levels} pl ON lplc.programid = pl.programid
                              AND lplc.levelid = pl.id
                              JOIN {role_assignments} rl ON rl.userid = :userid
                              JOIN {role} r ON r.id = rl.roleid
                             WHERE pl.active = 1 AND pl.programid = :programid
                              AND lplc.levelid = :levelid AND r.shortname = :student";

        $totalattdence = $DB->count_records_sql($totalattdencesql, $params);

        if ($userattended && $totalattdence > 0) {
            $currentsempercentage = round(($userattended / $totalattdence) * 100);
        } else {
            $currentsempercentage = 0;
        }

        $param = array();
        $param['userid'] = $userid;
        $param['student'] = 'student';
        $sql = "SELECT DISTINCT(u.id),r.shortname
                  FROM {user} u
                  JOIN {role_assignments} ra ON ra.userid = u.id
                  JOIN {role} r ON r.id = ra.roleid
                 WHERE r.shortname = :student AND u.id = :userid";
        $users = $DB->get_records_sql($sql, $param);
        $progfullname = $progdetails->name;
        $progbatchname = $batchname;
        $programname = strlen($progdetails->name) > 30 ? substr($progdetails->name, 0, 30).'...' : $progdetails->name;
        $batchname = strlen($batchname) > 30 ? substr($batchname, 0, 30).'...' : $batchname;
        $data = [
            'userimage' => $userimage,
            'studentname' => fullname($userrecord),
            'studentemail' => $userrecord->email,
            'studprogname' => $programname,
            'studbatchname' => $batchname,
            'progfullname' => $progfullname,
            'progbatchname' => $progbatchname,
            'enrolledcoursescount' => $enrolledcoursescount,
            'completedcount' => $completedcount,
            'enrolledprogramurl' => $enrolledprogramurl,
            'progressprogram' => $percentage,
            'csempercentage' => $currentsempercentage,
        ];

        $this->content = new stdClass();
        if ($users) {
            $this->content->text = $OUTPUT->render_from_template('block_student_statistics/index', $data);
        }
        return $this->content;
    }
}
