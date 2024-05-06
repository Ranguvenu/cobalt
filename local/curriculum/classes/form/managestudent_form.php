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
 * Manage curriculum Form.
 *
 * @package    local_curriculum
 * @copyright  2022 Eabyas Info Solutions <www.eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class managestudent_form extends moodleform {

    public function definition() {
        global $CFG, $DB, $USER;
        $mform = &$this->_form;
        $programid = $this->_customdata['programid'];
        $curriculumid = $this->_customdata['curriculumid'];
        $yearid = $this->_customdata['yearid'];

        $context = context_system::instance();

        $mform->addElement('hidden', 'programid', $programid);
        $mform->setType('programid', PARAM_INT);

        $mform->addElement('hidden', 'curriculumid', $curriculumid);
        $mform->setType('curriculumid', PARAM_INT);

        $mform->addElement('hidden', 'yearid', $yearid);
        $mform->setType('yearid', PARAM_INT);

        $students = array();
        $student = $this->_ajaxformdata['students'];
        if (!empty($student)) {
            $student = implode(',', $student);
            $studentssql = "SELECT u.id, CONCAT(u.firstname, ' ', u.lastname) AS fullname
                             FROM {user} AS u
                            WHERE u.id IN ($student) AND u.id > 2 AND u.confirmed = 1";
            $students = $DB->get_records_sql_menu($studentssql);
        }
        $options = array(
            'ajax' => 'local_curriculum/form-options-selector',
            'multiple' => true,
            'data-action' => 'program_course_student_selector',
            'data-contextid' => $context->id,
            'data-options' => json_encode(array('programid' => $programid, 'curriculumid' => $curriculumid, 'yearid' => $yearid))
        );
        $mform->addElement('autocomplete', 'students', get_string('students', 'local_program'), $students, $options);
        $mform->addRule('students', null, 'required', null, 'client');

        $mform->disable_form_change_checker();
    }

    public function validation($data, $files) {
        $errors = array();
        global $DB, $CFG;
        $errors = parent::validation($data, $files);
        $pluginname = 'program';
        $params = array();
        $semestercoursessql = 'SELECT c.id, c.id as courseid
                                   FROM {course} c
                                   JOIN {local_cc_semester_courses} ccsc ON ccsc.courseid = c.id
                                  WHERE ccsc.yearid = :yearid ';
        $params['yearid'] = $data['yearid'];
        $semestercourses = $DB->get_records_sql($semestercoursessql, $params);
        $enrolmethod = enrol_get_plugin($pluginname);
        foreach ($semestercourses as $semestercourse) {
            $instance = $DB->get_record('enrol', array('courseid' => $semestercourse->courseid, 'enrol' => $pluginname));
            if (empty($instance) || $instance->status != ENROL_INSTANCE_ENABLED) {
                $errors['students'] = get_string('canntenrol', 'enrol_program');
                break;
            }
        }
        return $errors;
    }

}
