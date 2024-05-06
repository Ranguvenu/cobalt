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
 * @package    block_assignments_graph
 * @copyright  Moodle India
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_assignments_graph extends block_base {
    function init() {
        $this->title = get_string('pluginname', 'block_assignments_graph');
    }

    function get_content() {
        global $CFG, $OUTPUT, $DB, $USER;
        $systemcontext = context_system::instance();
        require_login();
        if ($this->content !== null) {
            return $this->content;
        }
        $userid = $USER->id;
        $params = array();
        $params['userid'] = $userid;
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
        $coursesenrolled = $DB->get_records_sql($coursesenrolledsql, $params);
        $courseskeys =  implode(',', array_keys($coursesenrolled));
        if ($courseskeys) {
            $assignmtssql = "SELECT id, name FROM {assign} WHERE course IN($courseskeys)";
            $assignmts = $DB->get_records_sql($assignmtssql);
        }
        if ($assignmts) {
            $assignmtnames = array();
            $avgvalues = array();
            foreach ($assignmts as $agmtid => $assignmt) {
                $assignmtnames[] = $assignmt->name;
                $asmgtsubsql = "SELECT count(id) as submissioncount 
                                FROM {assign_submission} 
                                WHERE assignment = {$assignmt->id}
                                AND status = 'submitted' AND timemodified > timecreated";
                $asmgtsub = $DB->count_records_sql($asmgtsubsql);
                // $avggrade = number_format($asmgtsub, 2, ".", ".");
                $avgvalues[] = $asmgtsub;
            }
            arsort($avgvalues);
            $sample = array();
            $line = array();
            $count = 0;
            foreach ($avgvalues as $key => $avg) {
                $sample[] = $avg;
                $line[] = $assignmtnames[$key];
                $count++;
                if ($count >= 10) {
                    break;
                } else {
                    continue;
                }
            }
            $sales = new \core\chart_series(get_string('assignmentsgraph', 'block_assignments_graph'), $sample);
            $labels = $line;
            $chart = new \core\chart_bar();
            $chart->add_series($sales);
            $chart->set_labels($labels);
        }
        $users = $DB->get_records_sql("SELECT DISTINCT(r.id),r.shortname 
                                    FROM {role} r
                                    JOIN {role_assignments} ra ON ra.roleid = r.id
                                    JOIN {user} u ON u.id = ra.userid
                                    WHERE u.id = {$userid} 
                                    AND r.shortname = 'editingteacher'");
        $this->content =  new stdClass();
        if ($users) {
            if ($sample) {
                $this->content->text = $OUTPUT->render($chart);
            } else {
                $this->content->text = '<div class="alert alert-info w-100 text-center">'.get_string('nodataavailable', 'block_assignments_graph').'';
            }
        }
        return $this->content;
    }
}

