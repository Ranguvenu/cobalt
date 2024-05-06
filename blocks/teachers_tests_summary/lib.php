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
 * @package    block_teachers_tests_summary
 * @copyright  2022 eAbyas Info Solution Pvt Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function all_quizs ($id) {
    global $DB;
    $params = array();
    $params['teacherid'] = $id;
    $sql = "SELECT q.id as quizid, q.name as quizname, c.fullname as coursename,
            q.grade as maxgrade, q.course
            FROM {user_enrolments} ue
            JOIN {enrol} e ON ue.enrolid = e.id
            JOIN {quiz} q ON e.courseid = q.course
            JOIN {course} c ON q.course = c.id
           WHERE ue.userid = :teacherid
            GROUP BY q.id, e.courseid, q.name, q.grade
            LIMIT 5";
    $allquiz = $DB->get_records_sql($sql, $params);

    return $allquiz;
}


function not_yet_quizcompleted_students ($quizid) {
    global $DB;
    $params = array();
    $params['quizid'] = $quizid;
    $coursessql = "SELECT q.id, q.course
                    FROM {quiz} q
                   WHERE q.id = :quizid ";
    $courseids = $DB->get_record_sql($coursessql, $params);

    $params['courseid'] = $courseids->course;

    $stdrecordsql = "SELECT u.id
                       FROM {user} u
                       JOIN {role_assignments} ra ON ra.userid = u.id
                       JOIN {role} r ON r.id = ra.roleid AND r.shortname = 'student'
                       JOIN {context} ctx ON ctx.id = ra.contextid
                       JOIN {course} c ON c.id = ctx.instanceid
                      WHERE c.id = :courseid
                      GROUP BY u.id";
    $enrolledstudents = $DB->get_records_sql($stdrecordsql, $params);
    $enrolledstudentids = implode(', ', array_keys($enrolledstudents));
    if ($enrolledstudents) {
        $students = $DB->get_records_sql("SELECT qa.userid, qa.quiz
                                           FROM {quiz_attempts} qa
                                           JOIN {quiz} q ON qa.quiz = q.id
                                          WHERE q.id = $quizid AND qa.userid IN ($enrolledstudentids)");
        $enrolledstudentkey = array_flip(array_keys($enrolledstudents));
        $completedstudentkey = array_flip(array_keys($students));
        $notyetcompletedusers = array_diff_key($enrolledstudentkey, $completedstudentkey);
        $notyetcompleteduserscount = COUNT($notyetcompletedusers);
    }

    return $notyetcompleteduserscount;
}

function student_minpass_grade ($quizid) {
    global $DB;
    $params = array();
    $params['quizid'] = $quizid;
    $sql = "SELECT git.id, git.gradepass, q.id as quizid
             FROM {grade_items} git
             JOIN {course_modules} cm ON git.courseid = cm.course AND cm.module = 19
             JOIN {quiz} q ON cm.course = q.course AND cm.instance = q.id
            WHERE q.id = :quizid AND git.itemmodule = 'quiz' AND git.iteminstance = q.id";
    $minpassgrade = $DB->get_records_sql($sql, $params);

    return $minpassgrade;
}

function inprogress_quizs ($quizid) {
    global $DB, $USER;
    $params = array();
    $params['quizid'] = $quizid;
    $sql = "SELECT COUNT(DISTINCT(qa.userid)) as inprogress
                   FROM {quiz_attempts} qa
                   JOIN {quiz} q ON qa.quiz = q.id
                  WHERE q.id = :quizid AND qa.state = 'inprogress' AND qa.userid > 2
                   AND qa.userid != {$USER->id}";
    $inprogressquizs = $DB->count_records_sql($sql, $params);

    return $inprogressquizs;
}

function completed_quizs ($quizid) {
    global $DB, $USER;
    $params = array();
    $params['quizid'] = $quizid;
    $sql = "SELECT COUNT(DISTINCT(qa.userid)) as completed
                   FROM {quiz_attempts} qa
                   JOIN {quiz} q ON qa.quiz = q.id
                  WHERE q.id = :quizid AND qa.state = 'finished' AND qa.userid > 2
                   AND qa.userid != {$USER->id}";
    $completedquizs = $DB->count_records_sql($sql, $params);

    return $completedquizs;
}

function average_students_grade ($quizid) {
    global $DB, $USER;
    $params = array();
    $params['quizid'] = $quizid;
    $coursessql = "SELECT q.id, q.course
                    FROM {quiz} q
                   WHERE q.id = :quizid ";
    $courseids = $DB->get_record_sql($coursessql, $params);

    $params['courseid'] = $courseids->course;

    $stdrecordsql = "SELECT u.id
                       FROM {user} u
                       JOIN {role_assignments} ra ON ra.userid = u.id
                       JOIN {role} r ON r.id = ra.roleid AND r.shortname = 'student'
                       JOIN {context} ctx ON ctx.id = ra.contextid
                       JOIN {course} c ON c.id = ctx.instanceid
                      WHERE c.id = :courseid
                      GROUP BY u.id";
    $enrolledstudents = $DB->get_records_sql($stdrecordsql, $params);
    foreach ($enrolledstudents as $key => $value) {
        $attemptsql = "SELECT MAX(qa.attempt) as attempt
                     FROM {quiz_attempts} qa
                    WHERE qa.quiz = $quizid AND qa.timefinish > 0 AND qa.userid > 2
                     AND qa.userid != {$USER->id} AND qa.userid = $value->id";
        $maxattempts = $DB->get_records_sql($attemptsql);
        
        foreach ($maxattempts as $keys => $maxattemptsvalue) {
            $attempts = $maxattemptsvalue->attempt;
            if (!empty($attempts)) {
                $gradessql = "SELECT qa.sumgrades as grade
                               FROM {quiz_attempts} qa
                              WHERE qa.attempt = $maxattemptsvalue->attempt AND qa.quiz = $quizid
                               AND qa.timefinish > 0 AND qa.userid > 2
                               AND qa.userid != {$USER->id} AND qa.userid = $value->id";
                $grades[] = $DB->get_record_sql($gradessql);
            }
        }
    }
    $count = count($grades);
    $totalgrade = array_sum(array_column($grades, 'grade'));
    $avggrade = ($totalgrade / $count);

    return $avggrade;
}

function block_teachers_tests_summary_output_fragment_students_quiz_display($args) {
    global $OUTPUT, $DB, $USER;
    $id = $args['id'];
    $params = array();
    $params['quizid'] = $id;
    $completedquizsql = "SELECT DISTINCT(qa.userid), qa.id, qa.sumgrades as achivegrade,
                          q.grade as maxgrade, qa.attempt, qa.timefinish as completeddate
                         FROM {quiz_attempts} qa
                         JOIN {quiz} q ON qa.quiz = q.id
                         JOIN {quiz_grades} qg ON q.id = qg.quiz
                        WHERE q.id = :quizid AND qa.timefinish > 0 AND qa.userid > 2
                         AND qa.userid != {$USER->id}";
    $completedquizdata = $DB->get_records_sql($completedquizsql, $params);

    foreach ($completedquizdata as $keys) {

        $quizdetails = new stdClass();
        $param['userid'] = $keys->userid;
        $studentname = $DB->get_records_select('user', 'id = ?', $param);

        $firstname = $studentname[$keys->userid]->firstname;
        $lastname = $studentname[$keys->userid]->lastname;
        $studentfullname = ''.$firstname.' '.$lastname.'';

        $achivegrade = number_format($keys->achivegrade, 2, '.', '.');
        $maxgrade = number_format($keys->maxgrade, 2, '.', '.');
        $quizcomletiondate = date('d-M-Y', $keys->completeddate);

        $quizdetails->username = $studentfullname;
        $quizdetails->achivegrade = $achivegrade;
        $quizdetails->maxgrade = $maxgrade;
        $quizdetails->attempt = $keys->attempt;
        $quizdetails->completeddate = $quizcomletiondate;
        $quizarrays[] = $quizdetails;
    }

    $usersviewcontext = [
        'data' => $quizarrays,
    ];

    $output = $OUTPUT->render_from_template('block_teachers_tests_summary/studentdetails', $usersviewcontext);

    return $output;
}

function block_teachers_tests_summary_output_fragment_students_inprogressquiz_display($args) {
    global $OUTPUT, $DB, $USER;
    $id = $args['id'];
    $params = array();
    $params['quizid'] = $id;
    $inprogressquizsql = "SELECT qa.id, qa.userid, qa.attempt, qa.timestart as quizstartdate,
                         q.timeclose as duedate
                         FROM {quiz_attempts} qa
                         JOIN {quiz} q ON qa.quiz = q.id
                        WHERE q.id = :quizid AND qa.state = 'inprogress' AND qa.timestart > 0
                         AND qa.userid > 2 AND qa.userid != {$USER->id}";

    $inprogressquizdata = $DB->get_records_sql($inprogressquizsql, $params);
    
    foreach ($inprogressquizdata as $keys) {

        $inprogressquizdetails = new stdClass();
        $param['userid'] = $keys->userid;
        $studentname = $DB->get_records_select('user', 'id = ?', $param);

        $firstname = $studentname[$keys->userid]->firstname;
        $lastname = $studentname[$keys->userid]->lastname;
        $studentfullname = ''.$firstname.' '.$lastname.'';

        $quizstartdate = date('d-M-Y', $keys->quizstartdate);
        $quizduedate = date('d-M-Y', $keys->duedate);

        if ($quizduedate == '01-Jan-1970') {
            $duedate = 'N/A';
        } else {
            $duedate = $quizduedate;
        }
        
        $inprogressquizdetails->username = $studentfullname;
        $inprogressquizdetails->quizstartdate = $quizstartdate;
        $inprogressquizdetails->attempt = $keys->attempt;
        $inprogressquizdetails->duedate = $duedate;
        $inprogressquizarrays[] = $inprogressquizdetails;
    }
    
    $studeninprogressviewcontext = [
        'data' => $inprogressquizarrays,
    ];

    $quizinprogress = $OUTPUT->render_from_template('block_teachers_tests_summary/inprogressstudentsdetails', $studeninprogressviewcontext);
    
    return $quizinprogress;
}

function block_teachers_tests_summary_output_fragment_students_notattemptedquiz_display($args) {
    global $OUTPUT, $DB, $CFG;
    $id = $args['id'];
    $params = array();
    $params['quizid'] = $id;
    $coursessql = "SELECT q.id, q.course
                           FROM {quiz} q
                        WHERE q.id = :quizid ";
    $courseids = $DB->get_record_sql($coursessql, $params);

    $params['courseid'] = $courseids->course;

    $stdrecordsql = "SELECT u.id
                       FROM {user} u
                       JOIN {role_assignments} ra ON ra.userid = u.id
                       JOIN {role} r ON r.id = ra.roleid AND r.shortname = 'student'
                       JOIN {context} ctx ON ctx.id = ra.contextid
                       JOIN {course} c ON c.id = ctx.instanceid
                      WHERE c.id = :courseid
                      GROUP BY u.id";
    $enrolledstudents = $DB->get_records_sql($stdrecordsql, $params);
    $enrolledstudentids = implode(', ', array_keys($enrolledstudents));
    if ($enrolledstudents) {
        $students = $DB->get_records_sql("SELECT qa.userid, qa.quiz
                      FROM {quiz_attempts} qa
                      JOIN {quiz} q ON qa.quiz = q.id
                     WHERE q.id = $id AND qa.userid IN ($enrolledstudentids)");
        $enrolledkey = array_flip(array_keys($enrolledstudents));
        $completedkey = array_flip(array_keys($students));
        $notyetcompletedusers = array_diff_key($enrolledkey, $completedkey);

        foreach ($notyetcompletedusers as $keys => $values) {

            $incompletedquizdetails = new stdClass();
            $param['userid'] = $keys;
            $studentname = $DB->get_records_select('user', 'id = ?', $param);

            $firstname = $studentname[$keys]->firstname;
            $lastname = $studentname[$keys]->lastname;
            $studentfullname = ''.$firstname.' '.$lastname.'';
            $email = $studentname[$keys]->email;


            $incompletedquizdetails->username = $studentfullname;
            $incompletedquizdetails->email = $email;
            $inprogressquizarrays[] = $incompletedquizdetails;
        }
    }
    
    $studenincompletedviewcontext = [
        'data' => $inprogressquizarrays,
        'downloadurl' => $CFG->wwwroot . '/blocks/teachers_tests_summary/download.php?id='.$id,
    ];

    $notattemptedquiz = $OUTPUT->render_from_template('block_teachers_tests_summary/notattemptedstudents', $studenincompletedviewcontext);
    
    return $notattemptedquiz;
}

/**
 * Description: Quizname filter code
 * @param  [mform object]  $mform[the form object where the form is initiated]
 */
function quizname_filter($mform){
    global $DB, $USER;
    $systemcontext = context_system::instance();
    $params = array('teacherid' => $USER->id);
    $sql = "SELECT q.id as quizid, q.name as quizname
             FROM {user_enrolments} ue
             JOIN {enrol} e ON ue.enrolid = e.id
             JOIN {quiz} q ON e.courseid = q.course
             JOIN {course} c ON q.course = c.id
            WHERE ue.userid = :teacherid
             GROUP BY q.id, e.courseid, q.name, q.grade";
    $quizlist = $DB->get_records_sql_menu($sql, $params);

    $select = $mform->addElement('autocomplete', 'quizzes', '', $quizlist,
                                    array(
                                        'placeholder' => get_string('quizzes','block_teachers_tests_summary')
                                    )
                                );
    $mform->setType('quizzes', PARAM_RAW);
    $select->setMultiple(true);
}

/**
 * Description: Course filter code
 * @param  [mform object]  $mform[the form object where the form is initiated]
 */
function course_filter($mform){
    global $DB, $USER;
    $systemcontext = context_system::instance();
    $params = array('teacherid' => $USER->id);
    $sql = "SELECT c.id, c.fullname as coursename
             FROM {user_enrolments} ue
             JOIN {enrol} e ON ue.enrolid = e.id
             JOIN {quiz} q ON e.courseid = q.course
             JOIN {course} c ON q.course = c.id
            WHERE ue.userid = :teacherid
             GROUP BY q.id, e.courseid, q.name, q.grade";
    $courselist = $DB->get_records_sql_menu($sql, $params);
    
    $select = $mform->addElement('autocomplete', 'course', '', $courselist,
                                    array(
                                        'placeholder' => get_string('course','block_teachers_tests_summary')
                                    )
                                );
    $mform->setType('course', PARAM_RAW);
    $select->setMultiple(true);
}

function get_listof_quizzes ($stable, $filterdata) {
    global $DB, $USER, $CFG;

    $params = array();
    $params['teacherid'] = $stable->teacherid;
    $countsql = "SELECT count(q.id) ";

    $selectsql = "SELECT q.id as quizid, q.name as quizname, c.fullname as coursename,
                    q.grade as maxgrade, q.course";
    $fromsql = " FROM {user_enrolments} ue
                 JOIN {enrol} e ON ue.enrolid = e.id
                 JOIN {quiz} q ON e.courseid = q.course
                 JOIN {course} c ON q.course = c.id ";
    $fromsql .= " WHERE ue.userid = :teacherid";

    // For "Global (search box)" filter.
    if (isset($filterdata->search_query) && trim($filterdata->search_query) != '') {
        $filteredquizzes = array_filter(explode(',', $filterdata->search_query));
        $quizzesarray = array();
        if (!empty($filteredquizzes)) {
            foreach ($filteredquizzes as $key => $value) {
                $quizzesarray[] = "q.name LIKE '%".trim($value)."%' OR c.fullname LIKE '%".trim($value)."%'";
            }
            $imploderequests = implode(' OR ', $quizzesarray);
            $fromsql .= " AND ($imploderequests)";
        }
    }

    // For "Quiz name" filter.
    if (!empty($filterdata->quizzes)) {
        $filteredquizzes = array_filter(explode(',', $filterdata->quizzes), 'is_numeric');
        if(!empty($filteredquizzes)) {
            $quizzesarray = array();
            foreach($filteredquizzes as $key => $value) {
                $quizzesarray[] = "q.id = $value"; 
            }
            $quizzesimplode = implode(' OR ', $quizzesarray);
            $fromsql .= " AND ($quizzesimplode) ";
        }
    }

    // For "Course name" filter.
    if (!empty($filterdata->course)) {
        $filteredcourse = array_filter(explode(',', $filterdata->course), 'is_numeric');
        if(!empty($filteredcourse)) {
            $coursearray = array();
            foreach($filteredcourse as $key => $value) {
                $coursearray[] = "q.course = $value"; 
            }
            $courseimplode = implode(' OR ', $coursearray);
            $fromsql .= " AND ($courseimplode) ";
        }
    }

    $count = $DB->count_records_sql($countsql.$fromsql, $params);

    $gropuby = " GROUP BY q.id, e.courseid, q.name, q.grade";
    
    $orderby = " ORDER BY q.id ASC";
    
    $allquizs = $DB->get_records_sql($selectsql.$fromsql.$gropuby.$orderby, $params, $stable->start, $stable->length);

    $alldata = array();

    foreach ($allquizs as $key => $quizvalues) {
        $doc = array();
        $quizid = $quizvalues->quizid;
        $courseid = $quizvalues->course;
        $stdmaxgrade = $quizvalues->maxgrade;
        $stdmaxmimumgrade = number_format($stdmaxgrade, 2, '.', '.');

        // Student min required pass grade.
        $passgrade = student_minpass_grade($quizid);
        foreach ($passgrade as $key => $passgradevalue) {
            $attmptedquizid = $passgradevalue->quizid;
            if ($quizid == $attmptedquizid) {
                $grade = $passgradevalue->gradepass;
                $stdpassinggrade = number_format($grade, 2, '.', '.');
                $doc['minpassgrade'] = $stdpassinggrade;
            }
        }

        // Inprocess quizzes student count.
        $inprogressstudents = inprogress_quizs ($quizid);

        // Completed quizzes student count.
        $completedstudents = completed_quizs ($quizid);

        // Not yet quizzes completed students.
        $allstudents = not_yet_quizcompleted_students ($quizid);
        $stdcounts = $allstudents;
        if (empty($stdcounts)) {
            $stdcounts = 0;
        }

        // Quizzes average grade based on student achieved grade.
        $avggrade = average_students_grade($quizid);
        $avgstudentgrade = number_format($avggrade, 1, '.', '.');
        if ($avgstudentgrade == 'nan') {
            $avgstudentgrade = '0.0';
        }

        // Quizzes id based on course modules table.
        $quizmoduleid = $DB->get_field('course_modules', 'id', array('instance' => $quizid, 'course' => $courseid, 'module' => 19));

        $doc['id'] = $quizid;
        $doc['quizname'] = $quizvalues->quizname;
        $doc['coursename'] = $quizvalues->coursename;
        $doc['inprogressstudentcount'] = $inprogressstudents;
        $doc['completedstudentcount'] = $completedstudents;
        $doc['totalstdcounts'] = $stdcounts;
        $doc['maxgrade'] = $stdmaxmimumgrade;
        $doc['avggrade'] = $avgstudentgrade;
        $doc['quizmoduleid'] = $quizmoduleid;
        $doc['cfgwwwroot'] = $CFG->wwwroot;

        $alldata[] = $doc;
    }

    $coursesContext = [
        'hascourses' => $alldata,
        'length' => count($alldata),
        'count' => $count,
    ];

    return $coursesContext; 
}
