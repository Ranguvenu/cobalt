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
 * @package   block_quiz_graph
 * @copyright 2022 eAbyas Info Solutions Pvt. Ltd.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_quiz_graph extends block_base {
    public function init() {
        $this->title = get_string('pluginname', 'block_quiz_graph');
    }

    public function get_content() {
        global $CFG, $OUTPUT, $DB, $USER, $COURSE;
        require_login();
        if ($this->content !== null) {
            return $this->content;
        }

        // $this->page->requires->css('/blocks/quiz_graph/style.css');
        require_once($CFG->dirroot . '/blocks/teachers_tests_summary/lib.php');

        // All quizzes.
        $params = array();
        $params['teacherid'] = $USER->id;
        $sql = "SELECT q.id as quizid, q.name as quizname, c.fullname as coursename,
                 q.grade as maxgrade, q.course
                 FROM {user_enrolments} ue
                 JOIN {enrol} e ON ue.enrolid = e.id
                 JOIN {quiz} q ON e.courseid = q.course
                 JOIN {course} c ON q.course = c.id
                WHERE ue.userid = :teacherid
                 GROUP BY q.id, e.courseid, q.name, q.grade";
        $allquizs = $DB->get_records_sql($sql, $params);
        $avggrades = [];
        $quizname = [];
        foreach ($allquizs as $key => $quizvalues) {
            $quizid = $quizvalues->quizid;

            // Quizzes graph based on maximum attempted quiz.
            $completedquizsql = "SELECT COUNT(DISTINCT(qa.userid))
                                 FROM {quiz_attempts} qa
                                 JOIN {quiz} q ON qa.quiz = q.id
                                WHERE q.id = $quizid AND qa.timefinish > 0 AND qa.userid > 2
                                 AND qa.userid != {$USER->id} AND qa.state = 'finished'";
            $completedquizdata = $DB->count_records_sql($completedquizsql);

            // $avggrade = average_students_grade($quizid);
            // $avgstudentgrade = number_format($avggrade, 1, '.', '.');
            // if ($avgstudentgrade == 'nan') {
            //     $avgstudentgrade = '0.0';
            // }
            $quiznames = substr($quizvalues->quizname, 0, 15).'...';
            $quizname[]  = $quiznames;
            $avggrades[]  = $completedquizdata;
        }
        arsort($avggrades);

        $grades = [];
        $quiz = [];
        $count = 0;
        foreach ($avggrades as $key => $gradevalue) {
            $grades[] = $gradevalue;
            $quiz[] = $quizname[$key];
            $count++;
            if ($count >= 10) {
                break;
            } else {
                continue;
            }
        }

        $sales = new \core\chart_series(get_string('quiz', 'block_quiz_graph'), $grades);
        $labels = $quiz;

        $chart5 = new \core\chart_bar();
        $chart5->add_series($sales);
        $chart5->set_labels($labels);
        
        $teachers = $DB->get_records_sql("SELECT DISTINCT(r.id), r.shortname 
                                        FROM {role} r
                                        JOIN {role_assignments} ra ON ra.roleid = r.id
                                        JOIN {user} u ON u.id = ra.userid
                                       WHERE u.id = {$USER->id} 
                                        AND r.shortname = 'editingteacher'");

        if ($teachers) {
            $this->content =  new \stdClass();
            if ($grades) {
                $this->content->text = $OUTPUT->render($chart5);
            } else {
                $this->content->text = '<div class="alert alert-info w-100 text-center">'.get_string('nodataavailable', 'block_quiz_graph').'</div>';
            }
        }

        return $this->content;
    }
}
