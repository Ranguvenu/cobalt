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
 * Class block_semester_progress
 *
 * @package    block_semester_progress
 * @copyright  2023 Dipanshu Kasera
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_semester_progress;
require_once($CFG->dirroot . '/blocks/semester_progress/lib.php');
class semester_progress_lib {
	public function cm_semester($pid, $semid) {
        global $DB, $USER, $CFG;
		if ($pid) {
            $alllevels = $DB->get_records('local_program_levels', ['programid' => $pid]);
            $levels = $DB->get_record('local_program_levels', ['programid' => $pid, 'active' => 1]);

            /*Unset Active semester*/
            if (array_key_exists($levels->id, $alllevels)) {
                unset($alllevels[$levels->id]);
            }
            if (count($alllevels) > 1) {
                $levels = array_keys($alllevels);
                foreach($alllevels as $value) {
                    $existslevel = $DB->get_field('local_semesters_completions', 'levelid', ['programid' => $pid, 'userid' => $USER->id, 'levelid' => $value->id]);
                    if ($existslevel) {
                        $previousssemid = "SELECT id, enddate, startdate, level
                                            FROM {local_program_levels}
                                           WHERE id = $value->id AND programid = $pid";
                        $semestersids[] = $DB->get_record_sql($previousssemid);
                    }
                }
                $levelcount = true;
            } else {
                if ($levels->id) {
                    $previousssemid = "SELECT id, enddate, startdate, level
                                    FROM {local_program_levels}
                                   WHERE id = (SELECT max(id)
                                                FROM {local_program_levels}
                                                WHERE id < $levels->id AND programid = $pid
                                            )";
                    $levelid = $DB->get_record_sql($previousssemid);
                    $existslevel = $DB->record_exists('local_semesters_completions', ['programid' => $pid, 'userid' => $USER->id, 'levelid' => $levelid->id]);
                    if ($existslevel) {
                        $semestersids[] = $levelid;
                    }
                    $levelcount = false;
                }
            }
        }
        if ($semestersids) {
            if ($semid > 0) {
                $levelcourse = $DB->get_records('local_program_level_courses', ['levelid' => $semid, 'programid' => $pid]);
                $leveldata = $DB->get_record('local_program_levels', ['id' => $semid]);
                $sdate = date('d-m-Y', $leveldata->startdate);
                $edate = date('d-m-Y', $leveldata->enddate);
            } else {
                $levelcourse = $DB->get_records('local_program_level_courses', ['levelid' => $semestersids[0]->id, 'programid' => $pid]);
                $sdate = date('d-m-Y', $semestersids[0]->startdate);
                $edate = date('d-m-Y', $semestersids[0]->enddate);
            }
            $core_courses = array();
            $elective_courses = array();
            $list = array();
            foreach($levelcourse as $key => $courseval) {
                $contexcourse = \context_course::instance($courseval->courseid);
                if (is_enrolled($contexcourse, $USER)) {
                    $cmpdata = array();
                    $activitycounts = activity_counts($courseval->courseid);
                    $id = $courseval->courseid;
                    if ($courseval->mandatory == 1) {
                        $core_courses[] = count($courseval);
                        $courses = 'Core';
                    } else {
                        $elective_courses[] = count($courseval);
                        $courses = 'Elective';
                    }
                    $cmpsql = "SELECT COUNT(id)
                                FROM {course_completions}
                               WHERE course = {$courseval->courseid} AND userid = {$USER->id}
                                AND timecompleted > 0";
                    $completedcourse[] = $DB->count_records_sql($cmpsql);
                    $cname = $DB->get_field('course', 'fullname', ['id' => $courseval->courseid]);
                    $cmpdata['id'] = $courseval->courseid;
                    $cmpdata['course_name'] = $cname;
                    $cmpdata['mandatory'] = $courses;
                    $cmpdata['totalmodulescount'] = $activitycounts['totalmodulescount'];
                    $cmpdata['assignmentscount'] = $activitycounts['assignmentscount'];
                    $cmpdata['testcount'] = $activitycounts['testcount'];
                    $cmpdata['nooftopics'] = $activitycounts['nooftopics'];
                    $list[] = $cmpdata;
                }
            }
            if (count($list) == count($completedcourse)) {
                $semprogress = 100;
                $courseprogress = 'Completed';
                $dyclass = 'course_completed';
            }
            $completedsem = array(
                'levelname' => array_values($semestersids),
                'sdate' => $sdate,
                'edate' => $edate,
                'progress' => $semprogress,
                'core_courses' => count($core_courses),
                'elective_courses' => count($elective_courses),
                'courseprogress' => $courseprogress,
                'dyclass' => $dyclass,
                'levelcount' => $levelcount,
                'list' => $list,
                'configwwwroot' => $CFG->wwwroot,
            );
        }
        if (empty($completedsem)) {
            $empty = '<div class="alert alert-info w-100 text-center">
                        {{#str}}nosemdataavailable, block_semester_progress{{/str}}
                      </div>';
            $record = array('empty' => $empty);
        } else {
            $record = $completedsem;
        }
    return $record;
	}

    public function current_semester() {
        global $CFG, $USER, $PAGE, $OUTPUT, $DB;
        $programenrolusers = "SELECT plc.courseid, pl.programid, c.fullname, pl.id as levelid, pl.level, pl.active, plc.mandatory, c.fullname, pl.startdate, pl.enddate, plc.parentid 
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
                                WHERE ra.userid = :userid AND pl.active = :active 
                                GROUP BY plc.courseid ORDER BY plc.courseid, plc.mandatory ASC LIMIT 5";

        $course = $DB->get_records_sql($programenrolusers, array('userid' => $USER->id, 'active' => 1));

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

    if(strtotime($today_date) >= $semstartdate->startdate) {
        $can_access = 1;
        $datemessage = '';
    }  else {
        $can_access = 0;
        $datemessage = '"'.$semstartdate->level.'" not started yet...!!!';
    }

        $line = array();
        $params = array();

        $ele = get_elective_courses($USER->id);
        $elective = $ele[0];


        foreach ($course as $key) {

            $params['id'] = $key->courseid;
            $totalmodules = "SELECT COUNT(*) FROM {course_modules} cm WHERE cm.course = :id  AND cm.completion = 1 AND cm.visible = 1 AND cm.deletioninprogress = 0";
            $totalmodulescount = $DB->count_records_sql($totalmodules, $params);

            // Assignments count.
            $assignments = "SELECT COUNT(cm.id)
                         FROM {course_modules} cm
                         JOIN {modules} as m ON cm.module = m.id
                        WHERE cm.course = :id AND cm.visible = 1 AND m.name = 'assign' AND cm.deletioninprogress = 0";
            $assignmentscount = $DB->count_records_sql($assignments, $params);
            $line['assignmentscount'] = $assignmentscount;

            // Quizzes count.
            $test = "SELECT COUNT(cm.id)
                      FROM {course_modules} cm
                      JOIN {modules} as m ON cm.module = m.id
                     WHERE cm.course = :id AND cm.visible = 1 AND m.name = 'quiz' AND cm.deletioninprogress = 0";
            $testcount = $DB->count_records_sql($test, $params);
            $line['testcount'] = $testcount;

            $countoftopics = "SELECT COUNT(id) FROM {course_sections} WHERE course = :id AND section >= 1";
            $nooftopics = $DB->count_records_sql($countoftopics, $params);
            $line['nooftopics'] = $nooftopics;

            if($totalmodulescount > 1){
                $completedmodules = "SELECT COUNT(cmc.id) FROM {course_modules_completion} cmc LEFT JOIN {course_modules} cm ON cmc.coursemoduleid = cm.id WHERE cm.course = :id AND cmc.userid = $USER->id AND cmc.completionstate = 1 AND cm.visible = 1 AND cm.deletioninprogress = 0";
                $completedmodulescount = $DB->count_records_sql($completedmodules, $params);

                $courseprogress = round($completedmodulescount / $totalmodulescount * 100);

                $course_completion_exists = $DB->record_exists_sql("SELECT id FROM {course_completions} cc WHERE cc.course = {$key->courseid} AND cc.userid = {$USER->id} AND cc.timecompleted > 0");
                if ($course_completion_exists) {
                    $courseprogress = 100;
                    $coursecompleted = 'Completed';
                } else {
                    $courseprogress = $courseprogress;
                    // $coursecompleted = 'To be completed';
                    // $coursecompleted_criteria = 'To be completed based on completion criteria';
                }

                $coursecontext = $DB->get_field('context', 'id', array('instanceid' => $key->courseid, 'contextlevel' => 50));
                $params['id'] = $key->courseid;
                // $countoftopics = "SELECT COUNT(id) FROM {course_sections} WHERE course = :id AND section >= 1";
                // $nooftopics = $DB->count_records_sql($countoftopics, $params);
                $line['id'] = $key->courseid;
                $line['fullname'] = $key->fullname;

                $semstartdate = $DB->get_field('local_program_levels', 'startdate', array('id' => $key->levelid));
                $sementdate = $DB->get_field('local_program_levels', 'enddate', array('id' => $key->levelid));

                if ($semstartdate == 0) {
                    $line['startdate'] = 'N/A';
                } else {
                    $line['startdate'] = (date('d-M-Y', $semstartdate));
                }
                if ($sementdate == 0) {
                    $line['enddate'] = 'N/A';
                } else {
                    $line['enddate'] = date('d-M-Y', $sementdate);
                }
                $line['courseprogress'] = round($courseprogress);
                $line['nooftopics'] = $nooftopics;
                $instructor = $DB->get_records_sql("SELECT u.id,u.firstname FROM {user} u JOIN {role_assignments} ra ON ra.userid = u.id WHERE ra.roleid = 3 AND ra.contextid = $coursecontext");
                $facultycount = count($instructor);
                $line['facultycount'] = $facultycount;

                if ($key->mandatory == 1) {
                    $line['mandatory'] = 'Core';
                } else {
                    $line['mandatory'] = 'Elective';
                }

                $line['criteria'] = $criteria;
                $data['criteriaselected'][] = $line;
            } else {
                $criteria = true;
                $coursecontext = $DB->get_field('context', 'id', array('instanceid' => $key->courseid, 'contextlevel' => 50));
                // $countoftopics = "SELECT COUNT(id) FROM {course_sections} WHERE course = :id AND section >= 1";
                // $nooftopics = $DB->count_records_sql($countoftopics, $params);
                $line['id'] = $key->courseid;
                $line['fullname'] = $key->fullname;

                $semstartdate = $DB->get_field('local_program_levels', 'startdate', array('id' => $key->levelid));
                $sementdate = $DB->get_field('local_program_levels', 'enddate', array('id' => $key->levelid));

                if ($semstartdate == 0) {
                    $line['startdate'] = 'N/A';
                } else {
                    $line['startdate'] = (date('d-M-Y', $semstartdate));
                }
                if ($sementdate == 0) {
                    $line['enddate'] = 'N/A';
                } else {
                    $line['enddate'] = date('d-M-Y', $sementdate);
                }
                $line['courseprogress'] = round($courseprogress);
                $line['nooftopics'] = $nooftopics;
                $instructor = $DB->get_records_sql("SELECT u.id,u.firstname FROM {user} u JOIN {role_assignments} ra ON ra.userid = u.id WHERE ra.roleid = 3 AND ra.contextid = $coursecontext");
                $facultycount = count($instructor);
                $line['facultycount'] = $facultycount;

                if ($key->mandatory == 1) {
                    $line['mandatory'] = 'Core';
                } else {
                    $line['mandatory'] = 'Elective';
                }
                $line['criteria'] = $criteria;
                $course_completion_exists = $DB->record_exists_sql("SELECT id FROM {course_completions} cc WHERE cc.course = $key->courseid AND cc.userid = $USER->id AND cc.timecompleted > 0");
                if ($course_completion_exists) {
                    $courseprogress = 'Completed';
                    $coursecompleted = 'Completed';
                    $dyclass = 'course_completed';
                    $line['courseprogress'] = $courseprogress;
                    $line['dyclass'] = $dyclass;
                } else {
                    $courseprogress = 'In progress';
                    $dyclass = 'course_inprogress';
                    $coursecompleted = 'To be completed';
                    $coursecompleted_criteria = 'To be completed based on completion criteria';
                    $line['courseprogress'] = $courseprogress;
                    $line['dyclass'] = $dyclass;
                }
                $data['criterianotselected'][] = $line;
            }
        }

        $criteriaselected = count($data['criteriaselected']);
        $criteriaselected = count($data['criterianotselected']);

        $totalactcri = $criteriaselected + $criteriaselected;

        if (count($data['criteriaselected']) >= 4 || count($data['criterianotselected']) >= 4 || $totalactcri >= 4) {
            $return = 1;
        } else {
            $return = null;
        }
        $semdate = $line;
        
        /* Completed semester code starts*/
        /*$pid = $DB->get_field('local_program_users', 'programid', ['userid' => $USER->id]);
        $cmdata = new lib();
        $completedsem = $cmdata->cm_semester($pid, $semid = false);*/
        
        $usercourses = [
            'course' => $data,
            'configwwwroot' => $CFG->wwwroot,
            'semstartdate' => $semstartdate,
            'semdate' => $semdate,
            'visible' => $return,
            'electivecnt'=> $elective->show,
            'message'=> $elective->message,
            'can_access' => $can_access,
            'dateaccess_message' => $datemessage,
        ];
        return $usercourses;
    }
}
