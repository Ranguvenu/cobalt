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
 * @package   block_employees_statistics
 * @copyright 2022 eAbyas Info Solutions Pvt. Ltd.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_employees_statistics extends block_base {
    public function init() {
        if (is_siteadmin()) {
            $this->title = get_string('pluginname', 'block_employees_statistics'); 
        } else {
            $this->title = get_string('mystatistics', 'block_employees_statistics'); 
        }
    }

    public function get_content() {
        global $OUTPUT, $DB, $USER, $COURSE, $CFG, $PAGE;
        require_login();
        if ($this->content !== null) {
            return $this->content;
        }

    $popularcoursesql = "SELECT p.id,p.name,count(c.id) as coursecounts
                      FROM {user} u
                      JOIN {role_assignments} ra ON ra.userid = u.id
                      JOIN {role} r ON r.id=ra.roleid 
                      AND r.shortname = 'editingteacher'
                      JOIN {context} ctx ON ctx.id = ra.contextid
                      JOIN {course} c ON c.id = ctx.instanceid
                      JOIN {local_program_level_courses} plc ON plc.courseid = ctx.instanceid JOIN {local_program} p ON p.id= plc.programid
                     WHERE u.id =  $USER->id GROUP BY p.id";

            $popularcourse=$DB->get_records_sql($popularcoursesql);
            $procount =count($popularcourse);

        $allcoursesql = "SELECT c.id,c.fullname,plc.programid as programid,
                        u.id as userid
                        FROM {user} u
                        JOIN {role_assignments} ra ON ra.userid = u.id
                        JOIN {role} r ON r.id = ra.roleid 
                        AND r.shortname = 'editingteacher'
                        JOIN {context} ctx ON ctx.id = ra.contextid
                        JOIN {course} c ON c.id = ctx.instanceid
                        JOIN {local_program_level_courses} plc ON plc.courseid=c.id
                        JOIN {local_program_levels} pl ON pl.id = plc.levelid 
                        AND pl.programid = plc.programid
                        WHERE u.id = $USER->id ";

        $allcourse = $DB->get_records_sql($allcoursesql);
        $allenrolledcou =count($allcourse);
            $percentage = array();
            $crprogressavg = array();
            $totalpercentage = 0;
            $avgattendance = 0;
            $quiz['count']= 0;
            $totalquiz['count']= 0;
            $filecount['count']=0;
            $totalfiles['count']=0;
            $questioncount['count']=0;
            $totalquestions['count']=0;
            $gradecount['count'] = 0;
            $totalgrades['count']= 0;
            $activelearner['count'] = 0;
            $participant = 0;
            $participantcount = 0;
            $cntpercentage = 0;
            $sessions = [];
            $currenttime = time();
        foreach($allcourse as $key ){
            $crprogress = array();
            $students = $DB->get_records_sql("SELECT u.id as sid,u.firstname,u.lastname,c.id as courseid
                      FROM {user} u
                      JOIN {role_assignments} ra ON ra.userid = u.id
                      JOIN {role} r ON r.id = ra.roleid AND r.shortname = 'student'
                      JOIN {context} ctx ON ctx.id = ra.contextid
                      JOIN {course} c ON c.id = ctx.instanceid
                      JOIN {local_program_level_courses} plc ON plc.courseid=c.id
                      JOIN {local_program} p ON p.id = plc.programid
                     WHERE c.id = $key->id");


                foreach($students as $student){

                    $activeusersql =("SELECT DISTINCT(ul.userid),ul.courseid 
                                    FROM {user_lastaccess} ul
                                    JOIN {course} c ON c.id = ul.courseid 
                                    WHERE (ul.courseid = $key->id 
                                    AND ul.userid = $student->sid) 
                                    AND FROM_UNIXTIME(ul.timeaccess, '%Y-%m-%d') >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
                    $activeusers =$DB->get_records_sql($activeusersql);
                    $activelearner['count'] +=count($activeusers);
                

                    $totalmodules = "SELECT COUNT(cm.id) FROM {course_modules} cm 
                                    WHERE cm.course = $key->id AND cm.completion = 1";

                    $completedmodules = "SELECT COUNT(cmc.id) FROM {user} u  
                            JOIN {course_modules_completion} cmc ON u.id = cmc.userid
                            JOIN {course_modules} cm ON cm.id = cmc.coursemoduleid 
                            WHERE cm.course = $key->id AND u.id=$student->sid AND cmc.completionstate = 1";

                    $completedmodulescount = $DB->count_records_sql($completedmodules);
                    $totalmodulescount = $DB->count_records_sql($totalmodules);

                    $crprogress[] = ($completedmodulescount/$totalmodulescount)*100;
                    // courseprogress
                    // $participant +=($courseprogress);
                    // $participantcount +=count($courseprogress);
                }
            $crprogressum = array_sum($crprogress);
            $crprogresscount = count($crprogress);
            $crprgsavg = $crprogressum/$crprogresscount;

            if ($crprgsavg == 0 || $crprgsavg == 100) {
                $avgerageparticipants = intval($crprgsavg );
            } else {
                $avgerageparticipants = number_format($crprgsavg, 2, '.', '.');
            }

            $crprogressavg[] = $avgerageparticipants;

            $userattendedsql = "SELECT COUNT(stat.id) as statuscount
                            FROM {attendance_log} al
                            JOIN {attendance_sessions} ats ON al.sessionid = ats.id
                            JOIN {attendance} a ON ats.attendanceid = a.id
                            JOIN {attendance_statuses} stat ON al.statusid = stat.id
                            JOIN {local_program_level_courses} lplc ON a.course = lplc.courseid
                            JOIN {local_program_levels} pl ON lplc.programid = pl.programid
                            AND lplc.levelid = pl.id
                            WHERE lplc.courseid = $key->id 
                            AND stat.acronym IN ('P','L')
                            AND lplc.programid = $key->programid 
                            AND ats.teacherid=$USER->id /*AND ats.lasttakenby = $USER->id*/ AND (ats.sessdate+ats.duration) < ($currenttime)";

            $userattended = $DB->count_records_sql($userattendedsql);

            $totalattdencesql = "SELECT COUNT(DISTINCT(alog.id)) 
                            FROM {attendance_log} alog 
                            JOIN {attendance_sessions} ases ON ases.id = alog.sessionid 
                            JOIN {attendance} att ON att.id = ases.attendanceid 
                            WHERE att.course = $key->id 
                            AND ases.teacherid=$USER->id /*AND 
                            ases.lasttakenby = $USER->id*/ AND (ases.sessdate+ases.duration) < ($currenttime)";
            $totalattdence = $DB->count_records_sql($totalattdencesql);

            if ($userattended > 0 && $totalattdence > 0) {
                $percentage[] = (($userattended / $totalattdence) * 100);
                // $percentagecount +=count($percentage);
                // $totalpercentage = $percentage;
            } else {
                $percentage[] = 0;
                // $percentagecount += 0;
                // $totalpercentage = 0;
            }

        }
        $participantaverage= (array_sum($crprogressavg)/count($crprogressavg));
        if (substr($participantaverage, 3) == 0) {
            $avgerageparticipants = intval($participantaverage );
        } else {
            $avgerageparticipants = number_format($participantaverage, 2, '.', '.');
        }

        $totalpercentage = array_sum($percentage);
        $cntpercentage = count($percentage);
        $avgattendance = ($totalpercentage/$cntpercentage);

        if (substr($avgattendance, 3) == 0) {
            $participantsaverage = intval($avgattendance );
        } else {
            $participantsaverage = number_format($avgattendance, 2, '.', '.');
        }

    $allcoursesql1 = "SELECT c.id,c.fullname,plc.programid as programid,
                        pl.id as levelid
                        FROM {user} u
                        JOIN {role_assignments} ra ON ra.userid = u.id
                        JOIN {role} r ON r.id = ra.roleid AND r.shortname = 'editingteacher'
                        JOIN {context} ctx ON ctx.id = ra.contextid
                        JOIN {course} c ON c.id = ctx.instanceid
                        JOIN {local_program_level_courses} plc ON plc.courseid=c.id
                        JOIN {local_program_levels} pl ON pl.id = plc.levelid 
                        AND pl.programid = plc.programid
                        WHERE u.id = $USER->id ";
    $allcourse1 = $DB->get_records_sql($allcoursesql1);

    foreach ($allcourse1 as $coursekey => $val) {
            $quiz['count']= 0;
            $filecount['count']=0;
            $questioncount['count']=0;
            $gradecount['count'] = 0;

            $attendanceid = $DB->get_field('attendance', 'id', array('course' => $val->id));
            // $datesofprevious =("SELECT count(ass.id) as total
            //                 FROM {attendance_sessions} ass
            //                 WHERE /*ass.lasttakenby = $USER->id and*/ ass.teacherid = $USER->id and ass.attendanceid = $attendanceid
            //                 AND (ass.sessdate+ass.duration) < ($currenttime)");
            // $totalsessions =$DB->count_records_sql($datesofprevious);
            $datesofprevious =("SELECT count(ass.id) as total
                            FROM {attendance_sessions} ass
                            WHERE /*ass.lasttakenby = $USER->id and*/ ass.teacherid = $USER->id AND ass.attendanceid = ?
                            AND (ass.sessdate+ass.duration) < ($currenttime)");
            $totalsessions =$DB->count_records_sql($datesofprevious, [$attendanceid]);

            $sessions['count'] += $totalsessions;
            $quiz['count'] += $DB->count_records('quiz', array ('course' =>$val->id));
            $totalquiz['count'] +=$quiz['count'];

            $grades = "SELECT ag.id,a.course FROM {assign_grades} ag
                JOIN {assign} a ON a.id = ag.assignment
                WHERE a.course = :courseid and ag.grader = :userid";
            $graderecords = $DB->get_records_sql($grades, array('userid' =>$USER->id,'courseid' =>$val->id));

            $gradecount['count'] += count($graderecords);
            $totalgrades['count'] +=$gradecount['count'];

            $files = "SELECT COUNT(f.id),cm.course FROM {files} f 
                        JOIN {context} c ON c.id = f.contextid 
                        JOIN {course_modules} cm ON cm.id = c.instanceid 
                        WHERE f.userid = :userid AND cm.course = :courseid 
                        AND f.filename != :filename";
            $filecount['count'] += $DB->count_records_sql($files, array('userid' =>$USER->id,'courseid' =>$val->id,'filename' =>'.'));
            $totalfiles['count'] +=$filecount['count'];

           $questions = "SELECT que.id
                        FROM {question} que 
                        
                        WHERE que.createdby = $USER->id";

            $questionrecords = $DB->get_records_sql($questions);
            $questioncount['count'] += count($questionrecords);
            // $totalquestions['count'] =$questioncount['count'];
        }

        if(empty($totalquiz)){
                $totalquiz['count'] = 0;
            }else{
                $totalquiz;
            }
            if(empty($totalgrades)){
                $totalgrades['count'] = 0;
            }else{
                $totalgrades;
            }
            if(empty($procount)){
                $procount = 0;
            }else{
                $procount;
            }
            if(empty($totalfiles)){
                $totalfiles['count'] = 0;
            }else{
                $totalfiles;
            }
            if(empty($totalquestions)){
                $totalquestions['count'] = 0;
            }else{
                $totalquestions;
            }
        
        $total=($questioncount['count']+$totalfiles['count']+$totalgrades['count']+$totalquiz['count']);

        $data = [
            'cfg_url' => $CFG->wwwroot,
            'programcount' => $procount,
            'coursecount' => $allenrolledcou,
            'sessions'=>$sessions,
            'questions'=>$questioncount['count'],
            'files'=>$totalfiles['count'],
            'grades'=>$totalgrades['count'],
            'quizes'=>$totalquiz['count'],
            'totalcount'=>$total,
            'activelearner'=>$activelearner,
            'avgattendance'=>$participantsaverage.'%',
            'participantaverage'=>$avgerageparticipants.'%',
        ];

       $users = $DB->get_records_sql("SELECT DISTINCT(r.id),r.shortname
                                    FROM {role} r
                                    JOIN {role_assignments} ra ON ra.roleid = r.id
                                    JOIN {user} u ON u.id = ra.userid
                                    JOIN {user_enrolments} ue on ue.userid = u.id 
                                    JOIN {enrol} e on e.id = ue.enrolid 
                                    WHERE u.id = {$USER->id}
                                    AND r.shortname = 'editingteacher'");
        if ($users) {
            $this->content = new \stdClass();
            $this->content->text = $OUTPUT->render_from_template('block_employees_statistics/index', $data);
        }
        return $this->content;
    }
}
