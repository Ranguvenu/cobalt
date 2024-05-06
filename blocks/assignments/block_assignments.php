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
 * @package    block_assignments
 * @copyright  Moodle India
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_assignments extends block_base {
    function init() {
        $this->title = get_string('pluginname', 'block_assignments');
    }
    function get_content() {
        global $OUTPUT, $DB, $USER, $COURSE, $CFG;
        $systemcontext = context_system::instance();
        require_login();
        if ($this->content !== null) {
            return $this->content;
        }
        $userid = $USER->id;
        $param = array();
        $param['userid'] = $userid;
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
                                WHERE u.id = :userid";
        $coursesenrolled = $DB->get_records_sql($coursesenrolledsql, $param);
        $courseskeys =  implode(',', array_keys($coursesenrolled));

        if ($courseskeys) {
            $assignmtssql = "SELECT * FROM {assign} WHERE course IN ($courseskeys)";
            $assignmts = $DB->get_records_sql($assignmtssql);
            $assignmtsids = implode(',', array_keys($assignmts));
        }
        if ($assignmtsids) {
            $submissionssql = "SELECT asub.*, c.id as courseid, c.fullname as coursename,
                                u.username as username,
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
                                ORDER BY asub.id DESC LIMIT 5";

            $submissions = $DB->get_records_sql($submissionssql);
        }

        $count = 0;
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
            $count ++;
            if ($count >= 5) {
                $return = 1;
            } else {
                $return = null;
            }
            $assignmtpage = new moodle_url('/mod/assign/view.php?', ['id' => $submission->coursemoduleid]);
            $assignmturl = html_entity_decode($assignmtpage, ENT_QUOTES, 'UTF-8');
            $submission->assignmturl = $assignmturl;

            $coursepage = new moodle_url('/course/view.php?',['id' => $submission->courseid]);
            $courseurl = html_entity_decode($coursepage, ENT_QUOTES, 'UTF-8');
            $submission->courseurl = $courseurl;    
        }
        $viewmoreurl = new moodle_url('/blocks/assignments/submissions.php');
        $teacher = $DB->get_records_sql("SELECT DISTINCT(r.id),r.shortname 
                                    FROM {role} r
                                    JOIN {role_assignments} ra ON ra.roleid = r.id
                                    JOIN {user} u ON u.id = ra.userid
                                    WHERE u.id = {$userid} 
                                    AND r.shortname = 'editingteacher'");        
        $contents = [
            'submissions' => array_values($submissions),
            'visible' => $return,
            'viewmoreurl' => $viewmoreurl
        ];
        $this->content = new stdClass();
        if ($teacher) {
            $this->content->text = $OUTPUT->render_from_template('block_assignments/index', $contents);
        }
        return $this->content;
    }
}
