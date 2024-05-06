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
 * @package   block_student_program_info
 * @copyright 2023 eAbyas Info Solutions Pvt Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */



class block_student_program_info extends block_base {

    function init() {
        $this->title = get_string('pluginname', 'block_student_program_info');
    }

    public function get_content() {
        global $OUTPUT, $CFG, $PAGE, $DB, $USER;

        // $this->page->requires->css('/blocks/student_program_info/style.css');
        $rowdata = array();
        $userid = $USER->id;
        $params = array('userid' => $userid);

        // Program name and counts of total courses and leves enrolled in program.
        $sql = "SELECT DISTINCT(c.id) as totalcourses, p.id, p.name, p.totallevels,
                 lc.name as curriculumname, p.program_startdate, plc.mandatory
                 FROM {local_program} p 
                 JOIN {local_curriculum} lc ON p.curriculumid = lc.id
                 JOIN {local_program_users} pu ON pu.programid = p.id
                 JOIN {local_program_levels} pl ON pl.programid = pu.programid 
                 JOIN {local_program_level_courses} plc ON plc.levelid = pl.id 
                 JOIN {course} c ON c.id = plc.courseid 
                 JOIN {enrol} e ON e.courseid = c.id 
                 JOIN {user_enrolments} ue ON ue.enrolid = e.id 
                 JOIN {role_assignments} ra ON ra.userid = ue.userid 
                 JOIN {role} r ON r.id = ra.roleid 
                 JOIN {user} u ON u.id = ue.userid 
                WHERE ra.userid = :userid
                 GROUP BY c.id, p.name, p.totallevels, lc.name, p.program_startdate";
        $programdata = $DB->get_records_sql($sql, $params);
        $totalenrolledcourses = count($programdata);
        foreach ($programdata as $key => $value) {
            $mandatory = $value->mandatory;
            if ($mandatory == 1) {
                $corecourses[]  = $mandatory;
            } else {
                $electivecourses[] = $mandatory;
            }
        }
        $programid = $value->id;
        $programname = $value->name;
        $startdate = $value->program_startdate;
        $curriculumname = $value->curriculumname;
        $totallevels = $value->totallevels;
        $core = count($corecourses);
        $elective = count($electivecourses);

        $pid = array('programid' => $programid);
        
        $totalsemesterssql = "SELECT COUNT(lpl.id)
                                FROM {local_program_levels} lpl
                                JOIN {local_program} p ON lpl.programid = p.id
                               WHERE p.id = :programid ";
        $totalsemesters = $DB->count_records_sql($totalsemesterssql, $pid); 

        // Program start date.
        $programstartdate = date('d-m-Y', $startdate);
        if ($programstartdate == "01-01-1970") {
            $programstartdate = 'N/A';
        }

        // Program end date.
        $duration = $DB->get_field('local_program', 'duration', array('id' => $programid));
        $durationformat = $DB->get_field('local_program', 'duration_format', array('id' => $programid));
        if ($durationformat == "M") {
            $sdate = $startdate;
            if ($sdate) {
                $newdate = strtotime('+ '.$duration.' months', $sdate);
                $enddate =  date('d-m-Y', $newdate);
            } else {
                $enddate = 'N/A';
            }
        } else {
            $sdate = $startdate;
            if ($sdate) {
                $date = strtotime($sdate);
                $newdate = strtotime('+ '.$duration.' year', $sdate);
                $enddate =  date('d-m-Y', $newdate);
            } else {
                $enddate = 'N/A';
            }

        }

        $rowdata['programname'] = $programname;
        $rowdata['totalcourses'] = $totalenrolledcourses;
        $rowdata['totalsemesters'] = $totalsemesters;
        $rowdata['curriculumname'] = $curriculumname;
        $rowdata['startdate'] = $programstartdate;
        $rowdata['enddate'] = $enddate;
        $rowdata['core'] = $core;
        $rowdata['elactive'] = $elective;
        $templatedata['rowdata'][] = $rowdata;

        $params['programid'] = $programid;

        // To get the inactive semesters records.
        $selectsql = "SELECT c.fullname AS course, bclc.courseid AS programcourseid,
                        bclc.programid, bclc.levelid,
                        lpl.level AS levelname,
                        (
                            SELECT COUNT(*) FROM {course_modules} cm
                            WHERE cm.course = bclc.courseid AND cm.completion = 1 AND cm.visible = 1 AND cm.deletioninprogress = 0
                        ) AS total_modules,
                        (
                            SELECT COUNT(cmc.id) FROM {course_modules_completion} cmc
                             LEFT JOIN {course_modules} cm ON cmc.coursemoduleid = cm.id
                            WHERE cm.course = bclc.courseid and  cmc.userid = u.id AND cmc.completionstate = 1 AND cm.visible = 1 AND cm.deletioninprogress = 0
                        ) AS modules_completed
                        FROM {local_program_level_courses} bclc
                        JOIN {user} u
                        JOIN {user_enrolments} ue ON ue.userid=u.id
                        JOIN {enrol} e ON e.id=ue.enrolid
                        JOIN {course} c ON c.id = e.courseid and c.id = bclc.courseid
                       JOIN {local_program_levels} lpl ON lpl.id = bclc.levelid and lpl.programid = bclc.programid
                       WHERE u.id =  :userid AND lpl.active = 0 AND lpl.programid = :programid
                       ORDER BY bclc.courseid ASC";
        $semesterinfo = $DB->get_records_sql($selectsql, $params);

        // $colour = array('danger','primary','success','warning','info','dark','secondary');
        $colour = array('#ECAC76','#3CD070','#9955BB','#FFC0CB','#0D6EFD','#DC3545','#00A59C');
        $c = 0;
        $list=array();
        $data = array();
        $products = array();

        foreach($semesterinfo as $current) {
            $levelid = $current->levelid;
            $semester = $current->levelname;
            $products[$levelid][] = $current;
            $arrt[] = $levelid;
            $semnamedata[] = $semester;
        }

        $semval = array_count_values($semnamedata);
        $aruniq = array_unique($arrt);
        $semnameunidata = array_unique($semnamedata);

        foreach ($aruniq as $x) {
            foreach ($semesterinfo as $sem_detail) {
                if($sem_detail->levelid == $x){
                    $inactivecriteriaexistssql = "SELECT COUNT(cm.id)
                           FROM {course_modules} as cm
                          WHERE cm.course = $sem_detail->programcourseid AND cm.completion = 1 AND cm.visible = 1 AND cm.deletioninprogress = 0";
                    $inactivecriteriaexists = $DB->count_records_sql($inactivecriteriaexistssql);
                    if ($inactivecriteriaexists > 1) {
                        $list['id'] = $sem_detail->programcourseid;
                        $list['inactive_identifier'] = 'inactive_identifier'.$sem_detail->programcourseid;
                        $list['fullname'] = $sem_detail->course;
                        $list['semester'] = $sem_detail->levelname;
                        $semester = $list['semester'];
                        $coursecompletedmodule = $sem_detail->modules_completed;
                        $coursetotalmodules = $sem_detail->total_modules;
                        $coursesprogress = ($coursecompletedmodule / $coursetotalmodules * 100);
                        if ($coursetotalmodules == 0 && $coursecompletedmodule == 0) {
                            $coursesprogress = 0;
                        }
                        $coursecompletionsexistssql = "SELECT id
                                                        FROM {course_completions}
                                                        WHERE course = {$sem_detail->programcourseid}
                                                            AND userid = {$userid} AND timecompleted > 0";
                        $coursecompletionsexists = $DB->record_exists_sql($coursecompletionsexistssql);
                        if ($coursecompletionsexists) {
                            $coursesprogress = 100;
                        }
                        $list['per'] = round($coursesprogress);
                        $list['colour'] =   $colour[$c];
                        if (array_key_exists($semester, $semval)) {
                            $coursekeycount = round(100 / $semval[$semester]);
                            $list['coursebarlength'] = round($coursesprogress * $coursekeycount / 100);
                        }
                        if ($list['coursebarlength'] == 0) {
                            $list['coursebarlength'] = '100';
                        }
                        $data['semcourses'][] = $list;
                        $data['semname'] = $list['semester'];
                        $c++;
                    } else {
                        $inactivecoursecompletionsexistssql = "SELECT id
                                                                 FROM {course_completions}
                                                                WHERE course = {$sem_detail->programcourseid}
                                                                  AND userid = {$userid} AND timecompleted > 0";
                        $inactivecoursecompletionsexists = $DB->record_exists_sql($inactivecoursecompletionsexistssql);
                        $criteria = true;
                        $records['id'] = $sem_detail->programcourseid;
                        $records['inactive_identifier'] = 'inactive_identifier'.$sem_detail->programcourseid;
                        $records['fullname'] = $sem_detail->course;
                        $records['semester'] = $sem_detail->levelname;

                        if ($inactivecoursecompletionsexists) {
                            $records['per'] = 100;
                            $records['progress'] = get_string('completed', 'local_groups');
                            $records['colour'] = '#AAFF00';
                        } else {
                            $records['per'] = 60;
                            $records['progress'] = get_string('inprogress', 'local_groups');
                            $records['colour'] = '#FFA500';
                        }
                        $data['criteria'][] = $records;
                        $data['semname'] = $records['semester'];
                    }
                }
            }

            $templatedata['glass'][]  = $data;
            $data = array();
        }

        // To get the active semester records.
        $selectsqldata = "SELECT c.fullname AS course, bclc.courseid AS programcourseid,
                        bclc.programid, bclc.levelid,
                        lpl.level AS levelname,
                        (
                            SELECT COUNT(*) FROM {course_modules} cm
                            WHERE cm.course = bclc.courseid AND cm.completion = 1 AND cm.visible = 1
                            AND cm.deletioninprogress = 0
                        ) AS total_modules,
                        (
                            SELECT COUNT(cmc.id) FROM {course_modules_completion} cmc
                             LEFT JOIN {course_modules} cm ON cmc.coursemoduleid = cm.id
                            WHERE cm.course = bclc.courseid and  cmc.userid = u.id AND cmc.completionstate = 1 AND cm.visible = 1 AND cm.deletioninprogress = 0
                        ) AS modules_completed
                        FROM {local_program_level_courses} bclc
                        JOIN {user} u
                        JOIN {user_enrolments} ue ON ue.userid=u.id
                        JOIN {enrol} e ON e.id=ue.enrolid
                        JOIN {course} c ON c.id = e.courseid and c.id = bclc.courseid
                       JOIN {local_program_levels} lpl ON lpl.id = bclc.levelid and lpl.programid = bclc.programid
                       WHERE u.id = :userid AND lpl.active = 1 AND lpl.programid = :programid
                       ORDER BY bclc.courseid ASC";
        $semesterdata = $DB->get_records_sql($selectsqldata, $params);

        // $colours = array('danger','primary','success','warning','info','dark','secondary');
        $colours = array('#ECAC76','#3CD070','#9955BB','#FFC0CB','#0D6EFD','#DC3545','#00A59C');
        $i = 0;
        $list = array();
        $levelrecords = array();
        $productval = array();

        foreach($semesterdata as $currentvalue) {
            $levelidvalue = $currentvalue->levelid;
            $semesters = $currentvalue->levelname;
            $productval[$levelidvalue][] = $currentvalue;
            $arrtt[] = $levelidvalue;
            $semnamevalue[] = $semesters;
        }

        $arunique = array_unique($arrtt);
        $semnameunivalue = array_unique($semnamevalue);

        foreach ($arunique as $levelids) {
            foreach ($semesterdata as $semesterdetail) {
                if($semesterdetail->levelid == $levelids){
                    $criteriaexistssql = "SELECT COUNT(cm.id)
                           FROM {course_modules} as cm
                          WHERE cm.course = $semesterdetail->programcourseid AND cm.completion = 1 AND cm.visible = 1 AND cm.deletioninprogress = 0";
                    $criteriaexists = $DB->count_records_sql($criteriaexistssql);
                    if ($criteriaexists > 1) {
                        $list['id'] = $semesterdetail->programcourseid;
                        $list['identifier'] = 'identifier'.$semesterdetail->programcourseid;
                        $list['fullname'] = $semesterdetail->course;
                        $list['semester'] = $semesterdetail->levelname;
                        $totalcompletedmodule = $semesterdetail->modules_completed;
                        $totalmodules = $semesterdetail->total_modules;
                        $courseprogress = ($totalcompletedmodule / $totalmodules * 100);
                        if ($totalmodules == 0 && $totalcompletedmodule == 0) {
                            $courseprogress = 0;
                        }
                        $coursecompletionsexistssql = "SELECT id
                                                        FROM {course_completions}
                                                        WHERE course = {$semesterdetail->programcourseid}
                                                            AND userid = {$userid} AND timecompleted > 0";
                        $coursecompletionsexists = $DB->record_exists_sql($coursecompletionsexistssql);
                        if ($coursecompletionsexists) {
                            $courseprogress = 100;
                        }
                        $list['per'] = round($courseprogress);
                        $list['colours'] = $colours[$i];
                        $coursecount = round(100 / COUNT($semesterdata));
                        $list['coursebarlength'] = round($courseprogress * $coursecount / 100);
                        if ($list['coursebarlength'] == 0) {
                            $list['coursebarlength'] = '100';
                        }
                        $levelrecords['sem'][] = $list;
                        $name = $levelrecords['sem'][0]['semester'];
                        $i++;
                    } else {
                        $coursecompletionsexistssql = "SELECT id
                                                      FROM {course_completions}
                                                      WHERE course = {$semesterdetail->programcourseid}
                                                      AND userid = {$userid} AND timecompleted > 0";
                        $coursecompletionsexists = $DB->record_exists_sql($coursecompletionsexistssql);
                        $criteria = true;
                        $record['id'] = $semesterdetail->programcourseid;
                        $record['identifier'] = 'identifier'.$semesterdetail->programcourseid;
                        $record['fullname'] = $semesterdetail->course;
                        $record['semester'] = $semesterdetail->levelname;

                        if ($coursecompletionsexists) {
                            $record['per'] = 100;
                            $record['progress'] = get_string('completed', 'local_groups');
                            $record['colours'] = '#AAFF00';
                        } else {
                            $record['per'] = 60;
                            $record['progress'] = get_string('inprogress', 'local_groups');
                            $record['colours'] = '#FFA500';
                        }
                        $completiondata['criteria'][] = $record;
                        $name = $record['semester'];
                    }
                }
            }
            $semnamevalue['semnamevalue'] = $name;
            $templatedata['leveldata'][]  = $levelrecords;
            $templatedata['criteriadata'][]  = $completiondata;
            $templatedata['semnamevalue1'][]  = $semnamevalue;
            $levelrecords = array();
            $record = array();
        }

        $users = $DB->get_records_sql("SELECT DISTINCT(r.id),r.shortname 
                                    FROM {role} r
                                    JOIN {role_assignments} ra ON ra.roleid = r.id
                                    JOIN {user} u ON u.id = ra.userid
                                    WHERE u.id = {$USER->id} 
                                    AND r.shortname = 'student'");
        if ($users) {
            $this->content = new \stdClass();
            $this->content->text = $OUTPUT->render_from_template('block_student_program_info/index', $templatedata);
        }

        return $this->content;
    }
}
