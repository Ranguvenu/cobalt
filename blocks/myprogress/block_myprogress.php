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
 * @package   block_myprogress
 * @copyright 2022 eAbyas Info Solutions Pvt. Ltd.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_myprogress extends block_base {
    public function init() {
        $this->title = get_string('pluginname', 'block_myprogress');
    }

    public function get_content() {
        global $OUTPUT, $DB, $USER, $COURSE;
        require_login();
        if ($this->content !== null) {
            return $this->content;
        }
        $enrolled = "SELECT COUNT(c.id) AS course
                      FROM {user} u
                      JOIN {role_assignments} ra ON ra.userid = u.id
                      JOIN {role} r ON r.id=ra.roleid AND r.shortname = 'student'
                      JOIN {context} AS ctx ON ctx.id = ra.contextid
                      JOIN {course} c ON c.id = ctx.instanceid
                     WHERE u.id = {$USER->id}";

        $enrolledcount = $DB->get_records_sql($enrolled);

        $completions = "SELECT COUNT(cc.id) AS Completions FROM {course_completions} AS cc
                         JOIN {course} AS c ON c.id = cc.course
                         JOIN {enrol} AS e ON e.courseid = c.id
                         JOIN {user_enrolments} AS ue ON ue.enrolid = e.id
                         JOIN {user} AS u ON ue.userid = u.id AND u.id = cc.userid
                         JOIN {role_assignments} ra ON u.id = ra.userid
                         JOIN {role} r ON r.id = ra.roleid
                         JOIN {context} cxt ON cxt.id = ra.contextid AND c.id = cxt.instanceid
                          AND  r.shortname ='student' AND u.id = {$USER->id}";

        $completionscount = $DB->get_record_sql($completions);

        // To get the marked sessions in active semester.
        $userattendedsql = "SELECT COUNT(stat.id) as statuscount
                              FROM {attendance_log} al
                              JOIN {attendance_sessions} ats ON al.sessionid = ats.id
                              JOIN {attendance} a ON ats.attendanceid = a.id
                              JOIN {attendance_statuses} stat ON al.statusid = stat.id
                              JOIN {local_program_level_courses} lplc ON a.course = lplc.courseid
                              JOIN {local_program_levels} pl ON lplc.programid = pl.programid
                              AND lplc.levelid = pl.id
                             WHERE al.studentid = {$USER->id} AND stat.acronym IN ('P','L')
                              AND pl.active = 1";

        $userattended = $DB->count_records_sql($userattendedsql);

        $role = $DB->get_record_sql("SELECT id, shortname FROM {role} WHERE shortname = 'student'");
        $programcourses = $DB->get_record_sql("SELECT lplc.programid,lplc.levelid
                                        FROM {local_program_level_courses} lplc
                                        JOIN {local_program_levels} pl ON lplc.programid = pl.programid
                                        AND lplc.levelid = pl.id
                                        JOIN {local_program_users} pu ON lplc.programid = pu.programid
                                       WHERE pl.active = 1 AND pu.userid = {$USER->id}");

        if ($programcourses == null) {
            $programcourses = new \stdClass;
            $programcourses->programid = 0;
            $programcourses->levelid = 0;
        }

        // To get the all sessions in active semester.
        $totalattdencesql = "SELECT COUNT(DISTINCT(ats.id))
                              FROM {attendance_sessions} ats
                              JOIN {attendance} a ON a.id = ats.attendanceid
                              JOIN {local_program_level_courses} lplc ON a.course = lplc.courseid
                              JOIN {local_program_levels} pl ON lplc.programid = pl.programid
                              AND lplc.levelid = pl.id
                              JOIN {role_assignments} rl ON rl.userid = {$USER->id}
                             WHERE pl.active = 1 AND pl.programid = {$programcourses->programid}
                              AND lplc.levelid = {$programcourses->levelid} AND rl.roleid = {$role->id}";

        $totalattdence = $DB->count_records_sql($totalattdencesql);

        if ($userattended && $totalattdence > 0) {
            $percentage = round(($userattended / $totalattdence) * 100);
        } else {
            $percentage = 0;
        }

        foreach ($enrolledcount as $enrolled) {
            $line = array();
            $line['enrolled'] = $enrolled;
            $line['completionscount'] = $completionscount;
            $line['percentage'] = $percentage;
        }
// Propgram progress starts//////
        $total = ("SELECT COUNT(programid) FROM {local_program_levels} c WHERE programid = '$programcourses->programid'");
        $totalusers   = $DB->count_records_sql($total);
        $pldata = $DB->get_records_sql("SELECT enddate AS enddate ,level  FROM {local_program_levels} WHERE programid = '$programcourses->programid' AND enddate != 0 ");
        $cdate = time();

        $i= 0;
        foreach($pldata as $t)
        {
        if($cdate >$t->enddate)
        { $i++;
            
        }
        }
         $semnumber = $i;

         $tt = $semnumber*100;
         if ($tt && $totalusers > 0) {
              $per = floor($tt/$totalusers);
        } else {
            $per = 0;
        }
        
// Propgram progress ends//////

// Semester Progress starts////
$programid = $programcourses->programid;
    $params = array();
    $params['programid'] = $programid;
    $program = $DB->get_record('local_program', array('id' => $programid));
        //$PAGE->set_url('/local/users/profile.php');
        $programsdetails = $DB->get_records('local_program_levels', $params);
        $todaydate = time();
        $count = 0;
        //$line = array();
        foreach ($programsdetails as $key => $value) {
            $params['levelid'] = $value->id;
            $semestercourse = "SELECT c.id,c.fullname FROM
            {course} c JOIN {local_program_level_courses} lplc ON c.id = lplc.courseid WHERE levelid = :levelid";
        
            $semestercourses = $DB->get_records_sql($semestercourse, $params);
            $semcourse = array_keys($semestercourses);
            $semcourseids = implode(',', $semcourse);

            if($semcourseids){
                $coursecompleted = "SELECT COUNT(id) FROM {course_completions} WHERE timecompleted >1 AND userid = $USER->id
                AND course IN ($semcourseids)";

                $coursecompletedcount = $DB->count_records_sql($coursecompleted);
            }else{
                $coursecompletedcount = 0;
            }
            $value->count = count($semestercourses);
            if($coursecompletedcount == 0){
                $value->semprogress = 0;
            } else {
                $value->semprogress = $coursecompletedcount/$value->count*100;
                $totalsemprogress += $value->semprogress;

            }
            if($todaydate > $value->enddate)
            {
                $count++;
            }
            if($value->startdate == 0){
                $value->startdate = 'N/A';
            } else {
                $value->startdate = date('d-M-Y', $value->startdate);
            }
            if($value->startdate == 0){
                $value->enddate = 'N/A';
            } else {
                $value->enddate = date('d-M-Y', $value->enddate);
            }
        }
        $countsem = count($programsdetails);
        $var = $countsem*100;
        //$sempro = floor(($totalsemprogress/$var)*100);
        if ($totalsemprogress && $var > 0) {
              $sempro = floor(($totalsemprogress/$var)*100);
        } else {
            $sempro = 0;
        }
// Semester Progress Ends////
        $data = [
            'obj' => $line,
            'proprogress'=>$per,
            'semprogress'=>$sempro,
        ];

        $users = $DB->get_records_sql("SELECT DISTINCT(r.id),r.shortname FROM {role} r
                                        JOIN {role_assignments} ra ON ra.roleid = r.id
                                        JOIN {user} u ON u.id = ra.userid
                                       WHERE u.id = {$USER->id} AND r.shortname = 'student'");

        if ($users) {
            $this->content = new \stdClass();
            $this->content->text = $OUTPUT->render_from_template('block_myprogress/index', $data);
        }
        return $this->content;
    }
}
