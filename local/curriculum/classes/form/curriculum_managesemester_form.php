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

namespace local_curriculum\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/completionlib.php');
use context_system;
use local_program\local\querylib;
use moodleform;
use core_component;
use local_users\functions\userlibfunctions as userlib;

class curriculum_managesemester_form extends moodleform {
    public $formstatus;
    public function __construct($action = null, $customdata = null, $method = 'post',
        $target = '', $attributes = null, $editable = true, $formdata = null) {
        parent::__construct($action, $customdata, $method, $target, $attributes, $editable,
            $formdata);
    }

    public function definition() {
        global $CFG, $USER, $PAGE, $DB;
        $querieslib = new querylib();
        $mform = &$this->_form;
        $systemcontext = context_system::instance();
        $renderer = $PAGE->get_renderer('local_curriculum');
        $context = context_system::instance();
        $formstatus = $this->_customdata['form_status'];
        $formdata = $this->_customdata['formdata'];

        $id = $this->_customdata['id'] > 0 ? $this->_customdata['id'] : 0;
        $programid = $this->_customdata['programid'];
        $curriculumid = $this->_customdata['curriculumid'];
        $yearid = $this->_customdata['yearid'];
        $semesterid = $this->_customdata['semesterid'];

        $context = context_system::instance();
        $mform->addElement('hidden', 'programid', $programid);
        $mform->setType('programid', PARAM_INT);

        $semposition = 0;
        $semposition = $DB->get_field('local_curriculum_semesters', 'position', ['curriculumid' => $curriculumid]);
        if (!empty($semposition) && $semposition > 0) {
            $semposition = $semposition+1;
        }else{
            $semposition = 1;
        }
        $mform->addElement('hidden', 'position', $semposition);
        $mform->setType('programid', PARAM_INT);

        $mform->addElement('hidden', 'curriculumid', $curriculumid);
        $mform->setType('curriculumid', PARAM_INT);

        $mform->addElement('hidden', 'yearid', $yearid);
        $mform->setType('yearid', PARAM_INT);

        $mform->addElement('hidden', 'semesterid', $semesterid);
        $mform->setType('semesterid', PARAM_INT);

        $formheaders = array_keys($this->formstatus);
        $formheader = $formheaders[$formstatus];

        $mform->addElement('hidden', 'id', $id);
        $mform->setType('id', PARAM_INT);

        $mform->addElement('text', 'semester', get_string('semester', 'local_curriculum'), array());
        $mform->addRule('semester', get_string('semesternamerequired', 'local_curriculum'), 'required', null, 'client');
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('semester', PARAM_TEXT);
        } else {
            $mform->setType('semester', PARAM_CLEANHTML);
        }
        $editoroptions = array(
            'noclean' => false,
            'autosave' => false
        );
        $mform->addElement('editor', 'description',
                get_string('description', 'local_curriculum'), null, $editoroptions);
        $mform->setType('description', PARAM_RAW);
        $mform->addHelpButton('description', 'description', 'local_curriculum');

        $mform->disable_form_change_checker();
    }
    public function validation($data, $files) {
        global $CFG, $DB, $USER;
        $errors = array();

        if (isset($data['semester']) && empty(trim($data['semester']))) {
            $errors['semester'] = get_string('semesternamerequired', 'local_curriculum');
        }
        $semnameexistssql = "SELECT semester FROM {local_curriculum_semesters} WHERE curriculumid = :curriculumid AND semester = :semester AND id != :semesterid";
        $semnameexists = $DB->record_exists_sql($semnameexistssql, array('curriculumid' => $data['curriculumid'], 'semester' => $data['semester'], 'semesterid' => $data['id']));
        if ($semnameexists) {
            $errors['semester'] = get_string('nameexists', 'local_curriculum');
        }
        return $errors;
    }

    public function set_data($components) {
        global $DB;
        $context = context_system::instance();
        $data = $DB->get_record('local_curriculum_semesters', array('id' => $components->id));
        $data->id = $data->id;
        $description = $data->description;
        $data->description = array();
        $data->description['text'] = $description;
        $data->semester = $data->semester;
        parent::set_data($data);
    }
}
