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
 * @package    block_assignments
 * @copyright  2023 Moodle India
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function courses_data_of_submissions() {
    global $DB, $USER;
    $assignmts = user_enrolled_courses_assignmts();
    $assignmtsids = implode(',', array_keys($assignmts));

    if ($assignmtsids) {
        $submissionssql = "SELECT c.id as courseid, c.fullname as coursename, asub.*,
                            concat(u.firstname, u.lastname) as studentname,
                            asg.name as assignmentname, asg.duedate, cm.id as coursemoduleid, asg.grade
                            FROM {assign_submission} as asub
                            JOIN {user} as u ON asub.userid = u.id
                            JOIN {course_modules} as cm ON cm.instance = asub.assignment
                            JOIN {course} as c ON c.id = cm.course
                            JOIN {assign} as asg ON asg.course = cm.course AND asub.assignment = asg.id
                            WHERE asub.status = 'submitted'
                            AND asub.timecreated < asub.timemodified AND cm.module = 1
                            AND asg.id IN($assignmtsids) 
                            AND (asub.userid, asub.assignment)
                            NOT IN(SELECT userid, assignment FROM {assign_grades} 
                                WHERE assignment IN($assignmtsids) AND grade > 0 AND grader > 0)
                            ORDER BY asub.id DESC";

        $submissions = $DB->get_records_sql_menu($submissionssql);
    }
    if (count($submissions) > 0) {
        return $submissions;
    } else {
        return null;
    }
}

function assignments_data_of_submissions() {
    global $DB, $USER;
    $assignmts = user_enrolled_courses_assignmts();
    $assignmtsids = implode(',', array_keys($assignmts));

    if ($assignmtsids) {
        $submissionssql = "SELECT asg.id as assignmentid, asg.name as assignmentname,
                            c.id as courseid, c.fullname as coursename, asub.*,
                            concat(u.firstname, u.lastname) as studentname,
                            asg.duedate, cm.id as coursemoduleid, asg.grade
                            FROM {assign_submission} as asub
                            JOIN {user} as u ON asub.userid = u.id
                            JOIN {course_modules} as cm ON cm.instance = asub.assignment
                            JOIN {course} as c ON c.id = cm.course
                            JOIN {assign} as asg ON asg.course = cm.course AND asub.assignment = asg.id
                            WHERE asub.status = 'submitted'
                            AND asub.timecreated < asub.timemodified AND cm.module = 1
                            AND asg.id IN($assignmtsids) 
                            AND (asub.userid, asub.assignment)
                            NOT IN(SELECT userid, assignment FROM {assign_grades} 
                                WHERE assignment IN($assignmtsids) AND grade > 0 AND grader > 0)
                            ORDER BY asub.id DESC";

        $submissionsasg = $DB->get_records_sql_menu($submissionssql);
    }
    if (count($submissionsasg) > 0) {
        return $submissionsasg;
    } else {
        return null;
    }
}

function students_data_of_submissions() {
    global $DB, $USER;
    $assignmts = user_enrolled_courses_assignmts();
    $assignmtsids = implode(',', array_keys($assignmts));

    if ($assignmtsids) {
        $submissionssql = "SELECT u.id as studentid, u.username as username,
                            asg.id as assignmentid, asg.name as assignmentname,
                            c.id as courseid, c.fullname as coursename, asub.*,
                            asg.duedate, cm.id as coursemoduleid, asg.grade
                            FROM {assign_submission} as asub
                            JOIN {user} as u ON asub.userid = u.id
                            JOIN {course_modules} as cm ON cm.instance = asub.assignment
                            JOIN {course} as c ON c.id = cm.course
                            JOIN {assign} as asg ON asg.course = cm.course AND asub.assignment = asg.id
                            WHERE asub.status = 'submitted'
                            AND asub.timecreated < asub.timemodified AND cm.module = 1
                            AND asg.id IN($assignmtsids) 
                            AND (asub.userid, asub.assignment)
                            NOT IN(SELECT userid, assignment FROM {assign_grades} 
                                WHERE assignment IN($assignmtsids) AND grade > 0 AND grader > 0)
                            ORDER BY asub.id DESC";

        $submissionstdnts = $DB->get_records_sql_menu($submissionssql);
    }
    if (count($submissionstdnts) > 0) {
        return $submissionstdnts;
    } else {
        return null;
    }
}

function user_enrolled_courses($teacherid) {
    global $DB;
    $param = array();
    $param['teacherid'] = $teacherid;

    $coursesenrolledsql = "SELECT c.id,c.fullname,plc.programid as programid,
                            pl.id as levelid
                            FROM {user} u
                            JOIN {role_assignments} ra ON ra.userid = u.id
                            JOIN {role} r ON r.id = ra.roleid AND r.shortname = 'editingteacher'
                            JOIN {context} ctx ON ctx.id = ra.contextid
                            JOIN {course} c ON c.id = ctx.instanceid
                            JOIN {local_program_level_courses} plc ON plc.courseid=c.id
                            JOIN {local_program_levels} pl ON pl.id = plc.levelid 
                            AND pl.programid = plc.programid
                            WHERE u.id = :teacherid";

    $coursesenrolled = $DB->get_records_sql($coursesenrolledsql, $param);
    if (count($coursesenrolled) > 0) {
        return $coursesenrolled;
    } else {
        return null;
    }
}
function user_enrolled_courses_assignmts() {
    global $DB, $USER;
    $enrolledcourses = user_enrolled_courses($USER->id);
    if (count($enrolledcourses) > 0) {
        $courseskeys =  implode(',', array_keys($enrolledcourses));
        $assignmtssql = "SELECT id as assignmentid, name as assignmentname
                            FROM {assign} WHERE course IN($courseskeys)";
        $assignmts = $DB->get_records_sql_menu($assignmtssql);
        if (count($assignmts) > 0) {
            return $assignmts;
        } else {
            return null;
        }
    } else {
        return null;
    }
}
function get_listof_assignments($stable, $filterdata) {
    global $DB, $USER, $CFG;
    $assignmts = user_enrolled_courses_assignmts();
    $assignmtsids = implode(',', array_keys($assignmts));
    if ($assignmtsids) {
        $countsql = "SELECT count(asub.id) ";
        $submissionssql = "SELECT asub.*, c.id as courseid, c.fullname as coursename,
                            u.username as username,
                            asg.name as assignmentname, asg.duedate, cm.id as coursemoduleid, asg.grade ";
        $fromsql = "FROM {assign_submission} as asub
                            JOIN {user} as u ON asub.userid = u.id
                            JOIN {course_modules} as cm ON cm.instance = asub.assignment
                            JOIN {course} as c ON c.id = cm.course
                            JOIN {assign} as asg ON asg.course = cm.course AND asub.assignment = asg.id
                            WHERE asub.status = 'submitted'
                            AND asub.timecreated < asub.timemodified AND cm.module = 1
                            AND asg.id IN ($assignmtsids) 
                            AND (asub.userid, asub.assignment)
                            NOT IN(SELECT userid, assignment FROM {assign_grades} 
                    WHERE assignment IN($assignmtsids) AND grade > 0 AND grader > 0)";

         // For "Global (search box)" filter.
        if (isset($filterdata->search_query) && trim($filterdata->search_query) != '') {
            $filteredvalue = array_filter(explode(',', $filterdata->search_query));
            $valsarray = array();
            if (!empty($filteredvalue)) {
                foreach ($filteredvalue as $key => $value) {
                    $valsarray[] = "asg.name LIKE '%".trim($value)."%' OR c.fullname LIKE '%".trim($value)."%' OR u.username LIKE '%".trim($value)."%'";
                }
                $imploderequests = implode(' OR ', $valsarray);
                $fromsql .= " AND ($imploderequests)";
            }
        }

        // For "Assignment name" filter.
        if (!empty($filterdata->assignment)) {
            $filteredassignmts = array_filter(explode(',', $filterdata->assignment), 'is_numeric');
            if(!empty($filteredassignmts)) {
                $asgnmtsarray = array();
                foreach($filteredassignmts as $key => $value) {
                    $asgnmtsarray[] = "asg.id = $value"; 
                }
                $asgmntsimplode = implode(' OR ', $asgnmtsarray);
                $fromsql .= " AND ($asgmntsimplode) ";
            }
        }

        // For "Course name" filter.
        if (!empty($filterdata->courses)) {
            $filteredcourse = array_filter(explode(',', $filterdata->courses), 'is_numeric');
            if(!empty($filteredcourse)) {
                $coursearray = array();
                foreach($filteredcourse as $key => $value) {
                    $coursearray[] = "cm.course = $value"; 
                }
                $courseimplode = implode(' OR ', $coursearray);
                $fromsql .= " AND ($courseimplode) ";
            }
        }

        // For "Student username" filter.
        if (!empty($filterdata->username)) {
            $filteredusername = array_filter(explode(',', $filterdata->username), 'is_numeric');
            if(!empty($filteredusername)) {
                $usernamearray = array();
                foreach($filteredusername as $key => $value) {
                    $usernamearray[] = "asub.userid = $value"; 
                }
                $usernameimplode = implode(' OR ', $usernamearray);
                $fromsql .= " AND ($usernameimplode) ";
            }
        }

        $count = $DB->count_records_sql($countsql.$fromsql, []);
        
        $orderby = " ORDER BY asub.id DESC";

        $submissions = $DB->get_records_sql($submissionssql.$fromsql.$orderby, [], $stable->start, $stable->length);
    }

    foreach ($submissions as $subid => $submission) {

        $submissiondate = date('d-M-Y',$submission->timemodified);
        $submission->submissiondate = $submissiondate;
        
        if ($submission->timemodified < $submission->duedate) {
            $submission->delay = get_string('nodelay', 'block_assignments');
        } else {
            $delaycalculated = ($submission->timemodified - $submission->duedate);
            $delay = format_time($delaycalculated);
            $submission->delay = $delay;
        }

        $params = array();
        $params['id'] = $submission->coursemoduleid;
        $params['rownum'] = 0;
        $params['action'] = 'grader';
        $params['userid'] = $submission->userid;
        $gradeurl = new moodle_url('/mod/assign/view.php?', $params);
        $gradeurl = html_entity_decode($gradeurl, ENT_QUOTES, 'UTF-8');
        $submission->gradeurl = $gradeurl;

        $assignmtpage = new moodle_url('/mod/assign/view.php?', ['id' => $submission->coursemoduleid]);
        $assignmturl = html_entity_decode($assignmtpage, ENT_QUOTES, 'UTF-8');
        $submission->assignmturl = $assignmturl;

        $coursepage = new moodle_url('/course/view.php?',['id' => $submission->courseid]);
        $courseurl = html_entity_decode($coursepage, ENT_QUOTES, 'UTF-8');
        $submission->courseurl = $courseurl;
    }
    $submissionsres = [
        'submissions' => $submissions,
        'length' => count($submissions),
        'count' => $count,
    ];

    return $submissionsres; 
}

/**
 * Description: Assignments filter code
 * @param  [mform object]  $mform[the form object where the form is initiated]
 */
function assignmentname_filter($mform) {
    global $DB, $USER;
    $systemcontext = context_system::instance();
    $assgnmts = assignments_data_of_submissions();
    $select = $mform->addElement('autocomplete', 'assignment', '', $assgnmts,
                                    array(
                                        'placeholder' => get_string('assgname', 'block_assignments')
                                    )
                                );
    $mform->setType('assignment', PARAM_RAW);
    $select->setMultiple(true);
}

/**
 * Description: Courses filter code
 * @param  [mform object]  $mform[the form object where the form is initiated]
 */
function coursename_filter($mform) {
    global $DB, $USER;
    
    $coursessubmitted = courses_data_of_submissions();
    $select = $mform->addElement('autocomplete', 'courses', '', $coursessubmitted,
                                    array(
                                        'placeholder' => get_string('cname','block_assignments')
                                    )
                                );
    $mform->setType('courses', PARAM_RAW);
    $select->setMultiple(true);
}

/**
 * Description: Students filter code
 * @param  [mform object]  $mform[the form object where the form is initiated]
 */
function studentname_filter($mform) {
    global $DB, $USER;
    $studentslist = students_data_of_submissions();
    $select = $mform->addElement('autocomplete', 'username', '', $studentslist,
                                    array(
                                        'placeholder' => get_string('email','block_assignments')
                                    )
                                );
    $mform->setType('username', PARAM_RAW);
    $select->setMultiple(true);
}