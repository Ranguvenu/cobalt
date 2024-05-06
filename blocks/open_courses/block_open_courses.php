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
 * @package   block_students_attendance
 * @copyright 2022 eAbyas Info Solutions Pvt. Ltd.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_open_courses extends block_base {
    public function init() {
        $this->title = get_string('pluginname', 'block_open_courses');
    }
     


public function get_content()
    {

        global $CFG, $USER, $PAGE, $OUTPUT, $DB;
        require_login();
        if ($this->content !== null) {
            return $this->content;
        }
$systemcontext = context_system::instance();
        
$programenrolusers = "SELECT cr.id , cr.fullname FROM mdl_course AS cr JOIN mdl_context ct ON ct.instanceid = cr.ID AND ct.contextlevel = 50 JOIN mdl_role_assignments ra ON ra.CONTEXTID = ct.ID JOIN mdl_user u ON u.ID = ra.userid  AND cr.open_identifiedas = 6 AND u.ID=$USER->id LIMIT 5";
       
 $course = $DB->get_records_sql($programenrolusers, array('userid' => $USER->id, 'active' => 1));
 
 

   foreach ($course as $key) {

            $params['id'] = $key->id;
                $cid = $key->id;
          
                $totalmodules = "SELECT COUNT(*) FROM {course_modules} cm WHERE cm.course = $cid  AND cm.completion = 1 AND cm.visible = 1 AND cm.deletioninprogress = 0";


           
              $totalmodulescount = $DB->count_records_sql($totalmodules, $params);

            // Assignments count.
            $assignments = "SELECT COUNT(cm.id)
                         FROM {course_modules} cm
                         JOIN {modules} as m ON cm.module = m.id
                        WHERE cm.course = $cid AND cm.visible = 1 AND m.name = 'assign' AND cm.deletioninprogress = 0  ";
            $assignmentscount = $DB->count_records_sql($assignments, $params);

                
              

            $line['assignmentscount'] = $assignmentscount;

            // Quizzes count.
            $test = "SELECT COUNT(cm.id)
                      FROM {course_modules} cm
                      JOIN {modules} as m ON cm.module = m.id
                     WHERE cm.course = $cid AND cm.visible = 1 AND m.name = 'quiz' AND cm.deletioninprogress = 0";
            $testcount = $DB->count_records_sql($test, $params);
            $line['testcount'] = $testcount;

            $countoftopics = "SELECT COUNT(id) FROM {course_sections} WHERE course = :id AND section >= 1";
            $nooftopics = $DB->count_records_sql($countoftopics, $params);
            $line['nooftopics'] = $nooftopics;

             $completedmodules = "SELECT COUNT(cmc.id) FROM {course_modules_completion} cmc LEFT JOIN {course_modules} cm ON cmc.coursemoduleid = cm.id WHERE cm.course = $cid AND cmc.userid = $USER->id AND cmc.completionstate = 1 AND cm.visible = 1 AND cm.deletioninprogress = 0";


                $completedmodulescount = $DB->count_records_sql($completedmodules, $params);
 
                 //$courseprogress = round($completedmodulescount / $totalmodulescount * 100);
                 $courseprogress = $totalmodulescount != 0 ? round($completedmodulescount/$totalmodulescount*100) : 0;
 

             //   $course_completion_exists = $DB->record_exists_sql("SELECT id FROM {course_completions} cc WHERE cc.course = $cid AND cc.userid = $USER->id");

                $course_completion_exists = $DB->record_exists_sql("SELECT id FROM {course_completions} cc WHERE cc.course = $cid AND cc.userid = $USER->id AND cc.timecompleted > 0");

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
                $line['id'] = $cid;//$key->courseid;
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


                // $instructor = $DB->get_records_sql("SELECT u.id,u.firstname FROM {user} u JOIN {role_assignments} ra ON ra.userid = u.id WHERE ra.roleid = 3 AND ra.contextid = $coursecontext");
                // $facultycount = count($instructor);
                // $line['facultycount'] = $facultycount;

                if ($key->mandatory == 1) {
                    $line['mandatory'] = 'Core';
                } else {
                    $line['mandatory'] = 'Elective';
                }

                $line['criteria'] = $criteria;
                $data['criteriaselected'][] = $line;

        }
       

        $criteriaselected = count($data['criteriaselected']);
        $criteriaselected = count($data['criterianotselected']);

        $totalactcri = $criteriaselected + $criteriaselected;

 
        if (count($data['criteriaselected']) >= 3 || count($data['criterianotselected']) >= 3) {
            $return = 1;
        } else {
            $return = null;
        }


        $semdate = $line;
        $usercourses = ['course' => $data, 'configwwwroot' => $CFG->wwwroot, 'semstartdate' => $semstartdate, 'semdate' => $semdate, 'visible' => $return, 'can_access' => $can_access, 'dateaccess_message'=>$datemessage];
        
        $users = $DB->get_records_sql("SELECT DISTINCT(r.id),r.shortname FROM {role} r JOIN {role_assignments} ra ON ra.roleid = r.id JOIN {user} u ON u.id = ra.userid WHERE u.id = {$USER->id} AND r.shortname = 'student'");
        
        if ($users) {
            $this->content = new \stdClass();
            $this->content->text = $OUTPUT->render_from_template('block_open_courses/index', $usercourses);
        }
        return $this->content;
    }
    

       
}
