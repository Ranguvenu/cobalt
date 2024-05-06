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
 * curriculum View
 *
 * @package    local_curriculum
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
use context_system;
use \local_courses\action\insert as insert;
require_once($CFG->dirroot . '/local/curriculum/lib.php');
if (file_exists($CFG->dirroot . '/local/lib.php')) {
    require_once($CFG->dirroot . '/local/lib.php');
}
use \local_curriculum\notifications_emails as curriculumnotifications_emails;
// Curriculum.
define('CURRICULUM_NEW', 0);
define('CURRICULUM_COMPLETED', 2);
// Session Attendance.
define('SESSION_PRESENT', 1);
define('SESSION_ABSENT', 2);
// Types.
define('CURRICULUM', 1);

class program {
    /**
     * Manage curriculum (Create or Update the curriculum)
     * @method manage_curriculum
     * @param  Object $data Clasroom Data
     * @return Integer curriculum ID
     */
    public function manage_curriculum($curriculum, $copy=false) {
        global $DB, $USER;
        $curriculum->shortname = $curriculum->name;

 
        if (empty($curriculum->trainers)) {
            $curriculum->trainers = null;
        }
        if (empty($curriculum->capacity) || $curriculum->capacity == 0) {
            $curriculum->capacity = 0;
        }
        // if ($curriculum->open_univdept_status == 1) {
        //     $curriculum->departmentid = $curriculum->open_collegeid;
        // } else {
        //     $curriculum->departmentid = $curriculum->open_departmentid;
        // }

        // $curriculum->costcenter = $curriculum->costcenter;
        //  $curriculum->open_departmentid = $curriculum->open_departmentid;
        //   $curriculum->open_subdepartment = $curriculum->open_subdepartment;

        $curriculum->startdate = 0;
        $curriculum->enddate = 0;
        $curriculum->description = preg_replace("/&nbsp;/", " ", strip_tags($curriculum->cr_description['text']));
      //  $curriculum->department = $curriculum->departmentid;

        try {
            if ($curriculum->id > 0) {
         
                $curriculum->timemodified = time();
                $curriculum->usermodified = $USER->id;
                if ($DB->update_record('local_curriculum', $curriculum)) {
                    $semesteryearsdata = new stdClass();
                    $semesteryearsdata->id = $curriculum->id;
                    $semesteryearsdata->action = 'curriculum_form_data';
                }
                $params = array(
                    'context' => context_system::instance(),
                    'objectid' => $curriculum->id
                );
                // Trigger curriculum updated event.
                $event = \local_curriculum\event\curriculum_updated::create($params);
                $event->add_record_snapshot('local_curriculum', $curriculum);
                $event->trigger();
            } else {

                $curriculum->status = 0;
                $curriculum->timecreated = time();
                $curriculum->usercreated = $USER->id;
 
                $curriculum->id = $DB->insert_record('local_curriculum', $curriculum);
                $params = array(
                    'context' => context_system::instance(),
                    'objectid' => $curriculum->id
                );

                $event = \local_curriculum\event\curriculum_created::create($params);
                $event->add_record_snapshot('local_curriculum', $curriculum);
                $event->trigger();

                $curriculum->shortname = 'curriculum' . $curriculum->id;
                $DB->update_record('local_curriculum', $curriculum);
                if ($curriculum->id && $copy == false) {
                    $semesteryearsdata = new stdClass();
                    $semesteryearsdata->programid = $curriculum->program;
                    $semesteryearsdata->curriculumid = $curriculum->id;
                }
            }
            $curriculum->totalsemesters = $DB->count_records('local_curriculum_semesters',
                                                    array('curriculumid' => $curriculum->id)
                                                );
            $DB->update_record('local_curriculum', $curriculum);
        } catch (dml_exception $ex) {
            throw new moodle_exception($ex);
        }
        return $curriculum->id;
    }

    public function manage_curriculum_semester_completions($curriculumid, $semesterid, $yearid = 0) {
        global $DB, $USER;

        $courses = $DB->get_records_menu('local_cc_semester_courses',
            array('curriculumid' => $curriculumid, 'semesterid' => $semesterid), '', 'id, courseid');
        $bclcomptlcheck = $DB->record_exists('local_ccs_cmplt_criteria',
            array('curriculumid' => $curriculumid, 'semesterid' => $semesterid));
        if ($bclcomptlcheck) {
            $completions = $DB->get_record('local_ccs_cmplt_criteria',
                array('curriculumid' => $curriculumid, 'semesterid' => $semesterid));
        } else {
            $completions = new stdClass();
            $completions->curriculumid = $curriculumid;
            $completions->semesterid = $semesterid;
        }
        $completions->sessionids = null;

        if (!empty($courses) && is_array($courses)) {
            $completions->courseids = implode(', ', array_values($courses));

        } else {
            $completions->courseids = null;
        }

        $completions->sessiontracking = null;
        $completions->coursetracking = 'AND';
        try {
            if ($completions->id > 0) {
                $completions->timemodified = time();
                $completions->usermodified = $USER->id;
                $DB->update_record('local_ccs_cmplt_criteria', $completions);
                $params = array(
                    'context' => context_system::instance(),
                    'objectid' => $completions->id,
                    'other' => array('curriculumid' => $completions->curriculumid,
                                             'semesterid' => $completions->semesterid)
                );
                $event = \local_curriculum\event\program_completions_settings_updated::create($params);
                $event->add_record_snapshot('local_ccs_cmplt_criteria', $completions->curriculumid);
                $event->trigger();
            } else {
                $completions->timecreated = time();
                $completions->usercreated = $USER->id;
                $completions->yearid = $yearid;

                $completions->id = $DB->insert_record('local_ccs_cmplt_criteria', $completions);
                $params = array(
                    'context' => context_system::instance(),
                    'objectid' => $completions->id,
                    'other' => array('curriculumid' => $completions->curriculumid,
                                             'semesterid' => $completions->semesterid)
                );
                $event = \local_curriculum\event\program_completions_settings_created::create($params);
                $event->add_record_snapshot('local_ccs_cmplt_criteria', $completions);
                $event->trigger();
            }
        } catch (dml_exception $ex) {
            throw new moodle_exception($ex);
        }
        return $completions->id;
    }

    /**
     * curriculums
     * @method curriculums
     * @param  Object     $stable Datatable fields
     * @return Array  curriculums and totalcurriculumcount
     */
    public function curriculums($stable, $request = false) {
        global $DB, $USER;
        $params = array();
        $curriculums = array();
        $curriculumscount = 0;
        $concatsql = '';

        if (!empty($stable->search)) {
            $fields = array("bc.name");
            $fields = implode(" LIKE :search1 OR ", $fields);
            $fields .= " LIKE :search2 ";
            $params['search1'] = '%' . $stable->search . '%';
            $params['search2'] = '%' . $stable->search . '%';
            $concatsql .= " AND ($fields) ";
        }

        if (isset($stable->curriculumid) && $stable->curriculumid > 0) {
            $concatsql .= " AND bc.id = :curriculumid";
            $params['curriculumid'] = $stable->curriculumid;
        }

        $countsql = "SELECT COUNT(bc.id) ";
        if ($request == true) {
            $fromsql = "SELECT group_concat(bc.id) AS curriculumids";
        } else {
            $fromsql = "SELECT distinct(bc.id),bc.* ";
        }

        $sql = " FROM {local_curriculum} bc
                 JOIN {local_program} ps ON ps.curriculumid = bc.id
                 JOIN {local_costcenter} cc ON cc.id=ps.costcenter
                WHERE 1 = 1 ";

        if (isset($stable->curriculumid) && $stable->curriculumid > 0) {
            $curriculums = $DB->get_records_sql($fromsql . $sql, $params);
        } else {
            try {
                $curriculumscount = $DB->count_records_sql($countsql . $sql, $params);
                if ($stable->thead == false) {
                    $sql .= " ORDER BY bc.id DESC";
                    if ($request == true) {
                        $curriculums = $DB->get_record_sql($fromsql . $sql, $params, $stable->start, $stable->length);
                    } else {
                        $curriculums = $DB->get_records_sql($fromsql . $sql, $params, $stable->start, $stable->length);
                    }
                }
            } catch (dml_exception $ex) {
                $curriculumscount = 0;
            }
        }
        if (isset($stable->curriculumid) && $stable->curriculumid > 0) {
            return $curriculums;
        } else {
            return compact('curriculums', 'curriculumscount');
        }
    }
    /**
     * [manage_curriculum_courses description]
     * @method manage_curriculum_courses
     * @param  [type]                   $courses [description]
     * @return [type]                            [description]
     */
    public function manage_curriculum_courses($courses) {
        global $DB, $USER, $CFG;
        require_once($CFG->dirroot. '/course/lib.php');

 

            $curriculumcourseexists = $DB->record_exists('local_cc_semester_courses',
                                                array(
                                                    'curriculumid' => $courses->curriculumid,
                                                    'semesterid' => $courses->semesterid,
                                                    'open_parentcourseid' => $courses->course
                                                )
                                            );
            $mastercourse = $DB->get_record('local_cc_semester_courses',  array('id' => $courses->course));

                $mastercourse->shortname = $mastercourse->shortname .'_'.$courses->semesterid . '_' .$courses->curriculumid;
                $mastercourse->open_parentcourseid = $mastercourse->id;
                $mastercourse->id = 0;

                $curriculumcourse = new stdClass();
                $curriculumcourse->programid = $courses->programid;
                $curriculumcourse->curriculumid = $courses->curriculumid;
                $curriculumcourse->yearid = 1;
                $curriculumcourse->semesterid = $courses->semesterid;
                $curriculumcourse->open_parentcourseid = $courses->course;
                $curriculumcourse->courseid = $courses->course;
                $curriculumcourse->coursetype = $courses->coursetype;
                 $curriculumcourse->open_departmentid = $courses->open_departmentid;
                $curriculumcourse->timecreated = time();
                $curriculumcourse->usercreated = $USER->id;
                $curriculumcourse->id = $DB->insert_record('local_cc_semester_courses',
                    $curriculumcourse);
                $params = array(
                    'context' => context_system::instance(),
                    'objectid' => $curriculumcourse->id,
                    'other' => array('programid' => $courses->programid,
                                     'curriculumid' => $courses->curriculumid,
                                     'semesterid' => $courses->semesterid,
                                     'yearid' => $courses->yearid)
                );

                $this->manage_curriculum_semester_completions($courses->curriculumid, $courses->semesterid, $courses->yearid);

                $totalcourses = $DB->count_records('local_cc_semester_courses',
                                                array(
                                                    'curriculumid' => $courses->curriculumid,
                                                    'semesterid' => $courses->semesterid,
                                                    'yearid' => $courses->yearid
                                                )
                                            );
            $semesterdata = new stdClass();
            $semesterdata->id = $courses->semesterid;
            $semesterdata->programid = $courses->programid;
            $semesterdata->curriculumid = $courses->curriculumid;
            $semesterdata->totalcourses = $totalcourses;
            $semesterdata->timemodified = time();
            $semesterdata->usermodified = $USER->id;
            $DB->update_record('local_curriculum_semesters', $semesterdata);
            $totalbccourses = $DB->count_records('local_cc_semester_courses',
                array('curriculumid' => $courses->curriculumid));
            $curriculumdata = new stdClass();
            $curriculumdata->programid = $courses->programid;
            $curriculumdata->id = $courses->curriculumid;
            $curriculumdata->totalcourses = $totalbccourses;
            $curriculumdata->timemodified = time();
            $curriculumdata->usermodified = $USER->id;
            $DB->update_record('local_curriculum', $curriculumdata);

        return true;
    }
    public function curriculum_semesteryears($curriculumid, $yearid = 0) {
        global $DB, $USER;
        $params = array();
    }
    public function curriculum_semesters($curriculumid) {
        global $DB, $USER;
        $curriculumsemesterssql = "SELECT bcl.id, bcl.semester, bcl.position
                                FROM {local_curriculum_semesters} bcl
                                JOIN {local_curriculum} bc ON bc.id = bcl.curriculumid
                                WHERE bc.id = :curriculumid";
        $curriculumsemesters = $DB->get_records_sql($curriculumsemesterssql,
            array('curriculumid' => $curriculumid));
        return $curriculumsemesters;
    }

    /**
     * [curriculumusers description]
     * @method curriculumusers
     * @param  [type]         $curriculumid [description]
     * @param  [type]         $stable      [description]
     * @return [type]                      [description]
     */
    public function curriculumusers($curriculumid, $stable) {
        global $DB, $USER;
        $params = array();
        $curriculumusers = array();
        $concatsql = '';
        if (!empty($stable->search)) {
            $fields = array(0 => 'u.firstname',
                            1 => 'u.lastname',
                            2 => 'u.email',
                            3 => 'u.idnumber'
                            );
                $fields = implode(" LIKE '%" .$stable->search. "%' OR ", $fields);
                $fields .= " LIKE '%" .$stable->search. "%' ";
                $concatsql .= " AND ($fields) ";
        }
        $countsql = "SELECT COUNT(cu.id) ";
        $fromsql = "SELECT u.*, cu.attended_sessions, cu.hours, cu.completion_status, c.totalsessions,
        c.activesessions";

        if ($stable->yearid > 0) {
            $sql .= " JOIN {local_cc_session_signups} ccss ON ccss.userid = cu.userid ";
            $concatsql .= " AND ccss.yearid = :yearid ";
            $params['yearid'] = $stable->yearid;
        }
        $sql .= " JOIN {local_curriculum} AS c ON c.id = cu.curriculumid
                  WHERE c.id = $curriculumid AND u.confirmed = 1 AND u.suspended = 0 AND u.deleted = 0 AND u.id > 2";
        $sql .= $concatsql;
        try {
            $curriculumuserscount = $DB->count_records_sql($countsql . $sql, $params);
            if ($stable->thead == false) {
                $sql .= " ORDER BY id ASC";
                $curriculumusers = $DB->get_records_sql($fromsql . $sql, $params, $stable->start, $stable->length);
            }
        } catch (dml_exception $ex) {
            $curriculumuserscount = 0;
        }
        return compact('curriculumusers', 'curriculumuserscount');
    }
    public function manage_curriculum_program_semesters($semester, $autocreate = false) {
        global $DB, $USER;

        $semester->id = $semester->id;
        $semester->semester = $semester->semester;
        $semester->description = $semester->description['text'];
        try {
            if ($semester->id > 0) {
                $semester->usermodified = $USER->id;
                $semester->timemodified = time();
                $DB->update_record('local_curriculum_semesters', $semester);
                $params = array(
                    'context' => context_system::instance(),
                    'objectid' => $semester->id,
                    'other' => array('curriculumid' => $semester->curriculumid)
                );

                $event = \local_curriculum\event\semester_updated::create($params);
                $event->add_record_snapshot('local_curriculum_semesters', $semester);
                $event->trigger();
            } else {
                if ($autocreate) {
                    $records = array();
                    for ($i = 1; $i <= 7; $i++) {
                        ${'record' . $i} = new stdClass();
                        ${'record' . $i}->id = $semester->id;
                        ${'record' . $i}->semester = 'Semester ' . $i;
                        ${'record' . $i}->position = $i;
                        ${'record' . $i}->description = '';
                        ${'record' . $i}->programid = $semester->programid;
                        ${'record' . $i}->usercreated = $USER->id;
                        ${'record' . $i}->timecreated = time();
                        $records[$i] = ${'record' . $i};
                    }
                    $DB->insert_records('local_curriculum_semesters', $records);
                    return true;
                } else {
                    $semester->usercreated = $USER->id;
                    $semester->position = $semester->position;
                    $semester->timecreated = time();
                    $semester->id = $DB->insert_record('local_curriculum_semesters', $semester);

                    $params = array(
                        'context' => context_system::instance(),
                        'objectid' => $semester->id,
                        'other' => array('curriculumid' => $semester->curriculumid)
                    );

                    $event = \local_curriculum\event\semester_created::create($params);
                    $event->add_record_snapshot('local_curriculum_semesters', $semester);
                    $event->trigger();
                }
            }
        } catch (dml_exception $ex) {
            throw new moodle_exception($ex);
        }
        return $semester->id;
    }
    public function bc_session_enrolments($enroldata) {
        global $DB, $CFG, $USER;
        $sessionenroldatasql = "SELECT bss.*, bcs.timestart, bcs.timefinish,
                                    bcs.attendance_status, bcs.totalusers, bcs.mincapacity,
                                    bcs.maxcapacity
                                  FROM {local_cc_course_sessions} bcs
                                  JOIN {local_cc_session_signups} bss ON bss.sessionid = bcs.id
                                 WHERE bss.curriculumid = :curriculumid AND bss.semesterid = :semesterid
                                 AND bss.bclcid = :bclcid
                                 AND bss.userid = :userid ";
        $sessionenroldata = $DB->get_record_sql($sessionenroldatasql,
            array('curriculumid' => $enroldata->curriculumid,
                'semesterid' => $enroldata->semesterid,
                'bclcid' => $enroldata->bclcid,
                'userid' => $enroldata->userid));
        if (!empty($sessionenroldata) && $enroldata->enrol == 3) {
            $params = array(
                'context' => context_system::instance(),
                'objectid' => $enroldata->bclcid
            );
            $event = \local_curriculum\event\session_users_unenrol::create($params);
            $event->add_record_snapshot('local_cc_session_signups', $enroldata);
            $event->trigger();

            $DB->delete_records('local_cc_session_signups',
                                    array(
                                        'bclcid' => $enroldata->bclcid,
                                        'userid' => $enroldata->userid,
                                        'completion_status' => 0
                                    )
                                );
            $totaluserssql = "SELECT COUNT(DISTINCT id) FROM {local_cc_session_signups} WHERE sessionid = $enroldata->sessionid";
            $totalusers = $DB->count_records_sql($totaluserssql);
            $sessiondata = new stdClass();
            $sessiondata->id = $enroldata->sessionid;
            $sessiondata->totalusers = $totalusers;
            $DB->update_record('local_cc_course_sessions', $sessiondata);
            if ($enroldata->signupid) {
                $courseid = $DB->get_field('local_cc_semester_courses', 'courseid',
                        array('id' => $enroldata->bclcid));
                $this->manage_bcsemester_course_enrolments($courseid, $USER->id, 'employee', 'unenrol');
            }
            $emaillogs = new programnotifications_emails();
            $email = $emaillogs->curriculum_emaillogs('curriculum_session_cancel', $enroldata, $enroldata->userid,
                                $USER->id);
        } else if (!empty($sessionenroldata) && $enroldata->enrol == 2) {
            $allsessions = $DB->get_records('local_cc_session_signups',
                                                array(
                                                    'bclcid' => $enroldata->bclcid,
                                                    'userid' => $USER->id,
                                                    'completion_status' => 0
                                                )
                                            );
            foreach ($allsessions as $res) {
                $DB->delete_records('local_cc_session_signups',
                                        array(
                                            'bclcid' => $enroldata->bclcid,
                                            'userid' => $USER->id,
                                            'sessionid' => $res->sessionid,
                                            'completion_status' => 0
                                        )
                                    );
                $totaluserssql = "SELECT COUNT(DISTINCT id) FROM {local_cc_session_signups} WHERE sessionid = $res->sessionid";
                $totalusers = $DB->count_records_sql($totaluserssql);
                $sessiondata = new stdClass();
                $sessiondata->id = $res->sessionid;
                $sessiondata->totalusers = $totalusers;
                $res = $DB->update_record('local_cc_course_sessions', $sessiondata);
            }
            $enroldata->userid = $USER->id;
            $enroldata->supervisorid = $USER->open_supervisorid;
            $enroldata->hours = 0;
            $enroldata->usercreated = $USER->id;
            $enroldata->timecreated = time();
            $signupid = $DB->insert_record('local_cc_session_signups', $enroldata);
            $params = array(
                            'context' => context_system::instance(),
                            'objectid' => $signupid->id,
                            'other' => array('curriculumid' => $curriculumid,
                              'semesterid' => $enroldata->semesterid,
                              'bclcid' => $enroldata->bclcid)
                        );

            $event = \local_curriculum\event\session_users_enrol::create($params);
            $event->add_record_snapshot('local_cc_session_signups', $signupid);
            $event->trigger();

            $totaluserssql = "SELECT COUNT(DISTINCT id) FROM {local_cc_session_signups} WHERE sessionid = $enroldata->sessionid";
            $totalusers = $DB->count_records_sql($totaluserssql);
            $sessiondata = new stdClass();
            $sessiondata->id = $enroldata->sessionid;
            $sessiondata->totalusers = $totalusers;
            $DB->update_record('local_cc_course_sessions', $sessiondata);
            // Reschedule session.
            $emaillogs = new programnotifications_emails();
            $email = $emaillogs->curriculum_emaillogs('curriculum_session_reschedule',
                                        $enroldata, $enroldata->userid, $USER->id);
        } else {
            if ($enroldata->enrol == 1) {
                $supervisorid = $DB->get_field('user', 'open_supervisorid', array('id' => $enroldata->userid));
                $enroldata->supervisorid = $supervisorid;
                $enroldata->hours = 0;
                $enroldata->usercreated = $USER->id;
                $enroldata->timecreated = time();
                $signupid = $DB->insert_record('local_cc_session_signups', $enroldata);

                $params = array(
                            'context' => context_system::instance(),
                            'objectid' => $signupid->id,
                            'other' => array('curriculumid' => $enroldata->curriculumid,
                              'semesterid' => $enroldata->semesterid,
                              'bclcid' => $enroldata->bclcid)
                        );

                $event = \local_curriculum\event\session_users_enrol::create($params);
                $event->add_record_snapshot('local_cc_session_signups', $signupid);
                $event->trigger();

                $totaluserssql = "SELECT COUNT(DISTINCT id)
                                    FROM {local_cc_session_signups}
                                  WHERE sessionid = $enroldata->sessionid";
                $totalusers = $DB->count_records_sql($totaluserssql);
                $sessiondata = new stdClass();
                $sessiondata->id = $enroldata->sessionid;
                $sessiondata->totalusers = $totalusers;
                $DB->update_record('local_cc_course_sessions', $sessiondata);

                // Enroll session.
                $emaillogs = new programnotifications_emails();
                $email = $emaillogs->curriculum_emaillogs('curriculum_session_enrol', $enroldata, $enroldata->userid,
                                $USER->id);
                if ($signupid) {
                    $courseid = $DB->get_field('local_cc_semester_courses', 'courseid',
                        array('id' => $enroldata->bclcid));
                    $this->manage_bcsemester_course_enrolments($courseid, $USER->id);
                }
                return $signupid;
            }
        }
        $totaluserssql = "SELECT COUNT(DISTINCT id) FROM {local_cc_session_signups} WHERE sessionid = $enroldata->sessionid";
        $totalusers = $DB->count_records_sql($totaluserssql);
        $sessiondata = new stdClass();
        $sessiondata->id = $enroldata->sessionid;
        $sessiondata->totalusers = $totalusers;
        $DB->update_record('local_cc_course_sessions', $sessiondata);
        return true;
    }
    /**
     * [unassign_courses_to_bcsemester description]
     * @method unassign_courses_to_bcsemester
     * @param  [type]                      $curriculumid [description]
     * @param  [type]                      $semesterid    [description]
     * @param  [type]                      $bclcid     [description]
     * @return [type]                                  [description]
     */
    public function unassign_courses_from_semester($curriculumid, $yearid, $semesterid, $courseid) {
        global $DB, $CFG;
        require_once($CFG->dirroot. '/course/lib.php');
        $signups = $DB->get_records('local_cc_session_signups', array('curriculumid' => $curriculumid, 'yearid' => $yearid));
        if (!empty($signups)) {
            throw new moodle_exception("please unassign students");
        }

        \core_php_time_limit::raise();
        fix_course_sortorder();

        $DB->delete_records('local_cc_semester_courses',
                                array(
                                    'curriculumid' => $curriculumid,
                                    'yearid' => $yearid,
                                    'courseid' => $courseid
                                )
                            );
        $totalcourses = $DB->count_records('local_cc_semester_courses',
                                                array(
                                                    'curriculumid' => $curriculumid,
                                                    'yearid' => $yearid,
                                                    'semesterid' => $semesterid
                                                )
                                            );
        $semesterdata = new stdClass();
        $semesterdata->id = $semesterid;
        $semesterdata->curriculumid = $curriculumid;
        $semesterdata->totalcourses = $totalcourses;
        $semesterdata->timemodified = time();
        $semesterdata->usermodified = $USER->id;
        $DB->update_record('local_curriculum_semesters', $semesterdata);
        $totalbccourses = $DB->count_records('local_cc_semester_courses',
            array('curriculumid' => $curriculumid));
        $curriculumdata = new stdClass();
        $curriculumdata->id = $curriculumid;
        $curriculumdata->totalcourses = $totalbccourses;
        $curriculumdata->timemodified = time();
        $curriculumdata->usermodified = $USER->id;
        $DB->update_record('local_curriculum', $curriculumdata);
        return true;
    }
    /**
     * [manage_bcsemester_course_enrolments description]
     * @method manage_bcsemester_course_enrolments
     * @param  [type]                           $course     [description]
     * @param  [type]                           $user       [description]
     * @param  string                           $role       [description]
     * @param  string                           $type       [description]
     * @param  string                           $pluginname [description]
     * @return [type]                                       [description]
     */
    public function manage_bcsemester_course_enrolments($course, $user, $role = 'employee',
        $type = 'enrol', $pluginname = 'curriculum') {
        global $DB;
        $enrolmethod = enrol_get_plugin($pluginname);
        $roleid = $DB->get_field('role', 'id', array('shortname' => $role));
        $instance = $DB->get_record('enrol', array('courseid' => $course, 'enrol' => $pluginname), '*', MUST_EXIST);
        if (!empty($instance)) {
            if ($type == 'enrol') {
                $enrolmethod->enrol_user($instance, $user, $roleid, time());
            } else if ($type == 'unenrol') {
                $enrolmethod->unenrol_user($instance, $user, $roleid, time());
            }
        }
        return true;
    }
    /**
     * [bccourse_sessions_completions description]
     * @method bccourse_sessions_completions
     * @param  [type]                        $bccourse [description]
     * @return [type]                                  [description]
     */
    public function bccourse_sessions_completions($bccourse) {
        global $DB, $USER;
        $bcsessionssql = "SELECT bccs.id as sessionid, bccs.curriculumid, bccs.semesterid,
                            bccs.bclcid,
                         (SELECT COUNT(bss.id) FROM {local_cc_session_signups} bss WHERE bss.bclcid = bccs.bclcid
                          AND bss.completion_status = 1 AND bss.userid = :userid) AS completedsessions
                         FROM {local_cc_course_sessions} bccs
                        WHERE bccs.semesterid = :semesterid AND bccs.bclcid = :bclcid";
        $bcsessions = $DB->get_records_sql($bcsessionssql,
            array('semesterid' => $bccourse->semesterid,
                    'userid' => $USER->id, 'bclcid' => $bccourse->bclcid));
        $completedsessions = false;
        foreach ($bcsessions as $bcsessions) {
            if ($bcsessions->completedsessions > 0) {
                $completedsessions = true;
            }
        }
    }
    public function curriculum_year_completions($programid, $curriculumid, $yearid, $userid) {
        global $DB, $USER;
        $ccyearcompletionstatussql = 'SELECT * FROM ((SELECT COUNT(cs.id) AS totalsemesters
                                   FROM {local_curriculum_semesters} cs
                                  WHERE cs.yearid = :yearid) AS totalsemesters,
                                (SELECT COUNT(csc.id) AS completedsemesters
                                   FROM {local_cc_semester_cmptl} csc
                                  WHERE csc.yearid = :yearid1 AND csc.userid = :userid ) AS completedsemesters  )';
        $ccyearcompletionstatus = $DB->get_record_sql($ccyearcompletionstatussql,
                                                            array(
                                                                'yearid' => $yearid,
                                                                'yearid1' => $yearid,
                                                                'userid' => $userid
                                                            )
                                                        );

        if ($ccyearcompletionstatus->totalsemesters == $ccyearcompletionstatus->completedsemesters) {
            $yearcompletionstatus = $DB->get_record('local_cc_session_signups',
                                                        array(
                                                            'programid' => $programid,
                                                            'curriculumid' => $curriculumid,
                                                            'yearid' => $yearid,
                                                            'userid' => $userid
                                                        )
                                                    );
            if (!empty($yearcompletionstatus)) {
                $yearcompletionstatus->completion_status = 1;
                $yearcompletionstatus->completiondate = time();
                $yearcompletionstatus->usermodified = $USER->id;
                $yearcompletionstatus->timemodified = time();
                $DB->update_record('local_cc_session_signups', $yearcompletionstatus);
                $notificationsexists = $DB->record_exists('local_notification_type',
                                                array('shortname' => 'program_year_completion')
                                            );
                if ($notificationsexists) {
                    $emaillogs = new programnotifications_emails();
                    $email = $emaillogs->curriculum_emaillogs('program_year_completion',
                                                    $yearcompletionstatus, $userid, $USER->id
                                                );
                }
            }
        }
        return true;
    }
    /**
     * [mycompletedveles description]
     * @method mycompletedveles
     * @param  [type]           $curriculumid [description]
     * @return [type]                       [description]
     */
    public function mysemestersandcompletedsemesters($curriculumid) {
        global $DB, $USER;
        $semesters = $DB->get_fieldset_select('local_curriculum_semesters', 'id', 'curriculumid = :curriculumid ORDER BY id ASC',
            array('curriculumid' => $curriculumid));
        return array($semesters);

    }
    /**
     * [mynextsemesters description]
     * @method mynextsemesters
     * @param  [type]       $curriculumid [description]
     * @return [type]                   [description]
     */
    public function mynextsemesters($curriculumid) {
        global $DB, $USER;
        list($semesters, $mysemestercomptl) = $this->mysemestersandcompletedsemesters($curriculumid, $USER->id);
        $notcmptlsemesters = array_values(array_diff($semesters, $mysemestercomptl));
        return $notcmptlsemesters;
    }
    /**
     * [mynextsemesters description]
     * @method mynextsemesters
     * @param  [type]       $curriculumid [description]
     * @return [type]                   [description]
     */
    public function mynextsemesteryears($curriculumid) {
        global $DB, $USER;
    }

    /**
     * [manage_curriculum_session_trainers description]
     * @method manage_curriculum_session_trainers
     * @param  [type]                           $bcsession [description]
     * @param  [type]                           $action    [description]
     * @return [type]                                      [description]
     */
    public function manage_curriculum_session_trainers($bcsession, $action) {
        global $DB, $USER, $CFG;
        if (file_exists($CFG->dirroot . '/local/lib.php')) {
            require_once($CFG->dirroot . '/local/lib.php');
        }
        $bcsemestercourse = $DB->get_field('local_cc_semester_courses', 'courseid',
            array('curriculumid' => $bcsession->curriculumid, 'semesterid' => $bcsession->semesterid, 'id' => $bcsession->bclcid));
        switch ($action) {
            case 'insert':
                $type = 'curriculum_enrol';
                $fromuserid = $USER->id;
                $string = 'trainer';
                $enrolbcsessionuser = $this->manage_bcsemester_course_enrolments(
                    $bcsemestercourse, $bcsession->trainerid,
                    'editingteacher', 'enrol');

            break;
            case 'delete';
                $enrolsamecoursessql = "SELECT *
                                          FROM {local_cc_course_sessions}
                                         WHERE trainerid = :oldtrainerid
                                         AND id != :sessionid";

                $enrolsamecourses = $DB->get_record_sql($enrolsamecoursessql,
                    array('oldtrainerid' => $session->oldtrainerid,
                        'sessionid' => $session->id));
                if (empty($enrolsamecourses) || $session->oldtrainerid != $session->trainerid) {
                    break;
                }
                $type = 'curriculum_unenroll';
                $fromuserid = $USER->id;
                $string = 'trainer';
                $enrolbcsessionuser = $this->manage_bcsemester_course_enrolments(
                    $bcsemestercourse, $bcsession->trainerid, 'editingteacher',
                    'unenrol');

            break;
            case 'all':
                $this->manage_curriculum_session_trainers($bcsession, 'insert');
                $this->manage_curriculum_session_trainers($bcsession, 'delete');
            break;
            case 'default':
            break;
        }
        return true;
    }
    /**
     * [curriculumsession_capacity_check description]
     * @param  [type] $classroomid [description]
     * @return [type]              [description]
     */
    public function session_capacity_check($curriculumid, $semesterid, $bclcid, $sessionid) {
        global $DB;
        $return = false;
        $sessioncapacity = $DB->get_field('local_cc_course_sessions', 'maxcapacity',
                                                array(
                                                    'curriculumid' => $curriculumid,
                                                    'semesterid' => $semesterid,
                                                    'bclcid' => $bclcid
                                                )
                                            );
        $sessionenrolledusers = $DB->count_records('local_cc_session_signups',
                                                        array(
                                                            'curriculumid' => $curriculumid,
                                                            'semesterid' => $semesterid,
                                                            'bclcid' => $bclcid,
                                                            'sessionid' => $sessionid
                                                        )
                                                    );

        if ($sessioncapacity <= $sessionenrolledusers && !empty($sessioncapacity) && $sessioncapacity != 0) {
            $return = true;
        }
        return $return;
    }

    /**
     * [curriculum_add_assignusers description]
     * @method curriculum_add_assignusers
     * @param  [type]                    $curriculumid   [description]
     * @param  [type]                    $userstoassign [description]
     * @return [type]                                   [description]
     */
    public function session_add_assignusers($curriculumid, $semesterid, $bclcid, $sessionid, $userstoassign) {
        global $DB, $USER, $CFG;
        if (file_exists($CFG->dirroot . '/local/lib.php')) {
            require_once($CFG->dirroot . '/local/lib.php');
        }
        $allow = true;

        if ($allow) {
            foreach ($userstoassign as $key => $adduser) {
                $sessioncapacitycheck = $this->session_capacity_check($curriculumid, $semesterid, $bclcid, $sessionid);
                if (!$sessioncapacitycheck) {
                    $curriculumuser = new stdClass();
                    $curriculumuser->curriculumid = $curriculumid;
                    $curriculumuser->semesterid = $semesterid;
                    $curriculumuser->bclcid = $bclcid;
                    $curriculumuser->sessionid = $sessionid;
                    $curriculumuser->enrol = 1;
                    $curriculumuser->userid = $adduser;
                    try {
                        $this->bc_session_enrolments($curriculumuser);
                    } catch (dml_exception $ex) {
                        throw new moodle_exception($ex);
                    }
                } else {
                    break;
                }
            }
        }
        return true;
    }
    public function curriculumsemesteryear($curriculumid) {
        global $DB, $USER;
        $semesters = $DB->get_records('local_curriculum_semesters',
                            array('curriculumid' => $curriculumid), '', '*, id as semesterid');
        return $semesters;

    }
    public function program_semesters_courses($programid) {
        global $DB, $USER;
        $levelcoursesssql = "SELECT bclc.id AS bclevelcourseid, bclc.programid,
                                    bclc.levelid, c.*
                                      FROM {local_program_level_courses} bclc
                                      JOIN {course} c ON c.id = bclc.courseid
                                     WHERE bclc.programid = :programid ";

        if ($levelid) {
          $levelcoursesssql .= " AND bclc.levelid = {$levelid}";
        }
        $programlevelcourses = $DB->get_records_sql($levelcoursesssql,
                array('programid' => $programid));
        if (count($programlevelcourses) > 0) {
            return true;
        } else {
            return false;
        }
    }
    public function programtemplatestatus($programid) {
        global $DB, $CFG, $USER;
        $program = $DB->get_record('local_program', array('id' => $programid));
        if ($program) {
            $costcenter = $program->costcenter;
            $checkcostcenter = $DB->get_record('local_costcenter', ['id' => $costcenter]);

            $checkparent = $DB->count_records_sql('SELECT COUNT(id)
                                                    FROM {local_program}
                                                   WHERE id = :id', array('id' => $program->id)
                                                );

            $copiedprograms = $DB->count_records_sql("SELECT COUNT(id)
                                                        FROM {local_program}
                                                      WHERE id = :id
                                                       AND costcenter = :costcenter",
                                                       array(
                                                            'id' => $program->id,
                                                            'costcenter' => $program->costcenter
                                                        )
                                                    );

            if ($checkcostcenter->parentid == 0) {
                return true;
            } else if ($program->parentid == 0 && $checkparent > 0 ) {
                return false;
            } else {
                return false;
            }
        }
        return true;

    }

    public function checkcopyprogram($programid) {
        global $DB;
        if ($programid) {
            $currentprogramsql = 'SELECT p.id, p.costcenter, p.id
                                   FROM {local_program} p
                                  WHERE p.id = :programid ';
            $currentprogram = $DB->get_record_sql($currentprogramsql, array('programid' => $programid));
            $programsql = 'SELECT pp.id, pp.costcenter, pp.id
                            FROM {local_program} pp
                           WHERE pp.id = :programid ';
            $program = $DB->get_record_sql($programsql, array('programid' => $currentprogram->id));

            if ($currentprogram->id > 0 &&($currentprogram->costcenter != $program->costcenter)) {
                return false;
            }
        }
        return true;
    }

    public function deletesemonlinecourses($semesterid = null, $curriculumid = null, $yearid = null) {
        global $DB, $CFG;
        require_once($CFG->dirroot. '/course/lib.php');
        $params = array();
        $onlinecoursessql = "SELECT courseid as id, courseid as courseid
                                FROM {local_cc_semester_courses}
                               WHERE 1 = 1";
        if ($semesterid) {
            $onlinecoursessql .= " AND semesterid = :semester";
            $params['semester'] = $semesterid;
        }
        if ($yearid) {
            $onlinecoursessql .= " AND yearid = :year";
            $params['year'] = $yearid;
        }
        if ($curriculumid) {
            $onlinecoursessql .= " AND curriculumid = :curriculum";
        }
            $params['curriculum'] = $curriculumid;

        $onlinecourseslist = $DB->get_records_sql_menu($onlinecoursessql, $params);
        if ($onlinecourseslist) {
            foreach ($onlinecourseslist as $key => $value) {
                \core_php_time_limit::raise();
                /* We do this here because it spits out feedback as it goes.
                   delete_course($value, false); commented for semester popup
                   Update course count in categories.*/
                fix_course_sortorder();
            }
        }
        return true;
    }
}
