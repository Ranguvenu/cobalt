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
defined('MOODLE_INTERNAL') || die;
require_once($CFG->dirroot.'/local/curriculum/classes/local/querylib.php');
class managefaculties_form extends moodleform {

    public function definition() {
        global $CFG, $DB, $USER;
        $mform = &$this->_form;
        $programid = $this->_customdata['programid'];
        $curriculumid = $this->_customdata['curriculumid'];
        $yearid = $this->_customdata['yearid'];
        $semesterid = $this->_customdata['semesterid'];
        $courseid = $this->_customdata['courseid'];
        $context = context_system::instance();

        $mform->addElement('hidden', 'programid', $programid);
        $mform->setType('programid', PARAM_INT);

        $mform->addElement('hidden', 'curriculumid', $curriculumid);
        $mform->setType('curriculumid', PARAM_INT);

        $mform->addElement('hidden', 'yearid', $yearid);
        $mform->setType('yearid', PARAM_INT);

        $mform->addElement('hidden', 'semesterid', $semesterid);
        $mform->setType('semesterid', PARAM_INT);

        $mform->addElement('hidden', 'courseid', $courseid);
        $mform->setType('courseid', PARAM_INT);

        $faculties = array();
        $faculty = $this->_ajaxformdata['faculty'];
        if (!empty($faculty)) {
            $faculty = implode(',', $faculty);
            $facultysql = "SELECT u.id, CONCAT(u.firstname, ' ', u.lastname) AS fullname
                             FROM {user} AS u
                            WHERE u.id IN ($faculty) AND u.id > 2 AND u.confirmed = 1";
            $faculties = $DB->get_records_sql_menu($facultysql);
        }
        $options = array(
            'ajax' => 'local_curriculum/form-options-selector',
            'multiple' => true,
            'data-action' => 'program_course_faculty_selector',
            'data-contextid' => $context->id,
            'data-options' => json_encode(array(
                                            'courseid' => $courseid,
                                            'programid' => $programid,
                                            'yearid' => $yearid,
                                            'semesterid' => $semesterid,
                                            'curriculumid' => $curriculumid
                                        )
                                )
        );
        $mform->addElement('autocomplete', 'faculty', get_string('faculty', 'local_program'), $faculties, $options);
        $mform->addRule('faculty', null, 'required', null, 'client');

        $mform->disable_form_change_checker();
    }
    public function validation($data, $files) {
        $errors = array();
        global $DB, $CFG;
        $errors = parent::validation($data, $files);

        $instance = $DB->get_record('enrol', array('courseid' => $data['courseid'], 'enrol' => 'program'));

        if (empty($instance) || $instance->status != ENROL_INSTANCE_ENABLED) {
            $errors['faculty'] = get_string('canntenrol', 'enrol_program');
        }

        return $errors;
    }
}
