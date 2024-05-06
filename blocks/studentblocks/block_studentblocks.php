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
 * @package   block_studentblocks
 * @copyright 2023 eAbyas Info Solution Pvt Ltd.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_studentblocks extends block_base {

    function init() {
        $this->title = get_string('pluginname', 'block_studentblocks');
    }

    public function get_content() {
        global $OUTPUT,$CFG,$PAGE,$DB, $USER;
        // $this->page->requires->css('/blocks/studentblocks/style.css');
        $id = $USER->id;
        $systemcontext = context_system::instance();
        $programid = $DB->get_field('local_program_users', 'programid', array('userid' => $id));
        $params = array('programid' => $programid);

        // All semesters.
        $semsql = "SELECT DISTINCT(pl.id), pl.level
                     FROM {local_program_levels} pl
                     JOIN {local_program_level_courses} lplc ON pl.id = lplc.levelid
                    WHERE pl.programid = :programid";
        $semesters = $DB->get_records_sql($semsql, $params);

        $labels = [];
        foreach ($semesters as $semvalue) {
            $labels[]  = $semvalue->level;
        }

        // Completed course users(top 5) in program semesters.
        $userquerysql = "SELECT cc.userid
                          FROM {local_program_users} pu
                          JOIN {user} u ON pu.userid = u.id
                          JOIN {course_completions} cc ON u.id = cc.userid
                          JOIN {local_program_level_courses} lplc ON cc.course = lplc.courseid
                          JOIN {local_program_levels} lpl ON lplc.levelid = lpl.id
                         WHERE pu.programid = :programid AND u.deleted = 0 AND cc.timecompleted > 0
                         GROUP BY userid
                         ORDER BY COUNT(cc.id) DESC
                         LIMIT 5";
        $userquery = $DB->get_records_sql($userquerysql, $params);

        // If loggedin user not exists in top 5 list.
        if (!array_key_exists($id, $userquery)) {
            $loggedinuser = new \stdClass();
            $loggedinuser = array_push($userquery, (object)['userid' => $id ]);
            $userquery->userid[] = $loggedinuser;
        }

        foreach ($userquery as $userids) {
            $userid = $userids->userid ;
            $params['userid'] = $userid;
            $selectsql = "SELECT DISTINCT(bclc.courseid) AS programcourseid, bclc.levelid
                           FROM {local_program_level_courses} bclc
                           JOIN {user} u
                           JOIN {user_enrolments} ue ON ue.userid=u.id
                           JOIN {enrol} e ON e.id=ue.enrolid
                           JOIN {course} c ON c.id = e.courseid and c.id = bclc.courseid
                           JOIN {local_cc_semester_courses} cc ON cc.open_parentcourseid =  bclc.parentid
                           JOIN {local_program_levels} lpl ON lpl.id = bclc.levelid and lpl.programid = bclc.programid
                          WHERE u.id = :userid AND u.deleted = 0 AND lpl.programid = :programid
                          ORDER BY bclc.levelid ASC";
            $semesterinfo = $DB->get_records_sql($selectsql, $params);

            $products = array();
            $productarray = array();
            $cids = array();
            
            foreach ($semesterinfo as $current) {
                $levelid = $current->levelid;
                $products[$levelid][] = $current;
            }

            $arr = array();
            $productarray = array_values($products);

            foreach ($productarray as $sd) {
                $cids = array();
                
                foreach ($sd as $vb) {
                    $cids[] = $vb->programcourseid;
                }
                
                $courseids = implode(', ', $cids);
                $coursescount = count($cids);

                // Semesters course completetion data.
                if (!empty($courseids)) {
                    $coursecompleted = "SELECT COUNT(id)
                                         FROM {course_completions} 
                                        WHERE timecompleted > 0 AND userid = $userid
                                         AND course IN ($courseids)";
                    $coursecompletedcount = $DB->count_records_sql($coursecompleted);
                } else {
                    $coursecompletedcount = 0;
                }

                if ($coursecompletedcount == 0) {
                    $semprogress = 0;
                } else {
                    $semprogress= floor($coursecompletedcount / $coursescount * 100);
                }

                $semtotal = $semprogress;
                $arr[] = $semtotal;
            }
            $output = $arr;
            $val = [];
            foreach ($labels as $keys => $lableval) {
                if ($output[$keys]) {
                    $val[$keys] = $output[$keys];
                } else {
                    $val[$keys] = 0;
                }
            }
            $udetails = $DB->get_field('user', 'firstname', array('id' => $userid));
            $stud[] = new \core\chart_series($udetails, $val);
        }

        $chart4 = new \core\chart_line();
        $chart4->set_smooth(true);

        foreach ($stud as $c) {
            $chart4->add_series($c);
        }

        // If semesters not created.
        if (!$labels) {
            $labels = ['2004', '2005', '2006', '2007'];
        }

        $chart4->set_labels($labels);
        $chart4->get_yaxis(0, true)->set_label(get_string('course_completions', 'block_studentblocks'));

        $users = $DB->get_records_sql("SELECT DISTINCT(r.id), r.shortname
                                        FROM {role} r
                                        JOIN {role_assignments} ra ON ra.roleid = r.id
                                        JOIN {user} u ON u.id = ra.userid
                                       WHERE u.id = {$USER->id}
                                        AND r.shortname = 'student'");

        if ($users) {
            $this->content = new stdClass();
            $this->content->text = $OUTPUT->render($chart4);
        }

        return $this->content;
    }
}
