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
 * Handle ajax requests in curriculum
 *
 * @package    local_curriculums
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define('AJAX_SCRIPT', true);
require_once(dirname(__FILE__) . '/../../config.php');
require_once('lib.php');
require_once($CFG->dirroot.'/local/curriculum/lib.php');
require_login();
global $DB, $CFG, $USER, $PAGE;
$context = context_system::instance();

$PAGE->set_context($context);
$renderer = $PAGE->get_renderer('local_curriculum');

$action = required_param('action', PARAM_ACTION);
$curriculumid = optional_param('curriculumid', '', PARAM_INT);
$semesterid = optional_param('semesterid', '', PARAM_INT);
$courseid = optional_param('courseid', 0, PARAM_INT);
$yearid = optional_param('yearid', 0, PARAM_INT);
$costcenter = optional_param('costcenter', 0, PARAM_INT);
$context = context_system::instance();
$departmentid = optional_param('departmentid', 0, PARAM_INT);
$course = optional_param('course', 0, PARAM_INT);
$switchtype = optional_param('switch_type', '', PARAM_TEXT);
$userlib = new local_users\functions\userlibfunctions();

if ($switchtype == 0) {
    $id = $DB->get_record('local_cc_semester_courses', array('courseid' => $course,
                                                            'curriculumid' => $curriculumid,
                                                            'semesterid' => $semesterid));
    $sql = "UPDATE {local_cc_semester_courses} SET coursetype = 0 WHERE id=:id";
    $DB->execute($sql, array('id' => $id->id));

} else if ($switchtype == 1) {
     $id = $DB->get_record('local_cc_semester_courses', array('courseid' => $course,
                                                            'curriculumid' => $curriculumid,
                                                            'semesterid' => $semesterid));
     $sql = "UPDATE {local_cc_semester_courses} SET coursetype = 1 WHERE id=:id";
    $DB->execute($sql, array('id' => $id->id));
}

switch ($action) {
    case 'curriculumyearsemesters':
         $return = $renderer->viewcurriculumsemesteryear($curriculumid, $yearid);
    break;
    case 'departmentlist':
        $department = find_departments($costcenter);

        echo json_encode($department);
    exit;
    break;
    case 'courseslist':
        $courseslist = find_courses($departmentid, $semesterid, $yearid, $curriculumid);
        echo json_encode(['data' => $courseslist]);
    exit;
    break;
}
echo json_encode($return);
