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
 * @package   block_teachers_tests_summary
 * @copyright 2022 eAbyas Info Solutions Pvt. Ltd.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_teachers_tests_summary extends block_base {
    public function init() {
        $this->title = get_string('pluginname', 'block_teachers_tests_summary');
    }

    public function get_content() {
        global $CFG, $OUTPUT, $DB, $USER, $COURSE;
        require_login();
        
        if ($this->content !== null) {
            return $this->content;
        }

        $systemcontext = context_system::instance();
        $this->page->requires->jquery();
        // $this->page->requires->css('/blocks/teachers_tests_summary/style.css');
        $this->page->requires->js_call_amd('block_teachers_tests_summary/quiz', 'Datatable', array());
        $this->page->requires->js_call_amd('block_teachers_tests_summary/quiz', 'init',
                                            array(
                                                array(
                                                    'contextid' => $systemcontext->id,
                                                    'selector' => '.quizpopup'
                                                )
                                            )
                                        );
        $this->page->requires->js_call_amd('block_teachers_tests_summary/inprogressquiz', 'init',
                                            array(
                                                array(
                                                    'contextid' => $systemcontext->id,
                                                    'selector' => '.inprogressquizpopup'
                                                )
                                            )
                                        );
        $this->page->requires->js_call_amd('block_teachers_tests_summary/notattemptedquiz', 'init',
                                            array(
                                                array(
                                                    'contextid' => $systemcontext->id,
                                                    'selector' => '.notattemptedquizpopup'
                                                )
                                            )
                                        );

        require_once($CFG->dirroot . '/blocks/teachers_tests_summary/lib.php');

        $allquizs = all_quizs($USER->id);

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

            // Inprocess quiz student count.
            $inprogressstudents = inprogress_quizs ($quizid);

            // Completed quiz student count.
            $completedstudents = completed_quizs ($quizid);

            // Not yet quiz completed students.
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
            
            $alldata[] = $doc;
        }

        if (count($alldata) >= 5) {
            $return = 1;
        } else {
            $return = null;
        }

        $viewmoreurl = $CFG->wwwroot .'/blocks/teachers_tests_summary/summary.php';
        $quizpage = $CFG->wwwroot .'/mod/quiz/view.php?id=';
        $records = [
            'data' => array_values($alldata),
            'visible' => $return,
            'viewmoreurl' => $viewmoreurl,
            'quizpage' => $quizpage,
        ];

        $teachers = $DB->get_records_sql("SELECT DISTINCT(r.id),r.shortname 
                                    FROM {role} r
                                    JOIN {role_assignments} ra ON ra.roleid = r.id
                                    JOIN {user} u ON u.id = ra.userid
                                    WHERE u.id = {$USER->id} 
                                    AND r.shortname = 'editingteacher'");
        
        if ($teachers) {
            $this->content =  new \stdClass();
            $this->content->text = $OUTPUT->render_from_template('block_teachers_tests_summary/testsummary', $records);
        }

        return $this->content;

    }
}
