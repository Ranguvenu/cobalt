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

namespace local_admissions\form;
defined('MOODLE_INTERNAL') || die;
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/completionlib.php');
require_once($CFG->dirroot . '/local/costcenter/lib.php');
use moodleform;
use context_system;
use costcenter;
use events;
use context_user;
use core_user;
use local_admissions\local\lib as lib;

class rejectconfirmform extends moodleform {
	public $formstatus;
	public function __construct($action = null, $customdata = null, $method = 'post', $target = '', $attributes = null, $editable = true, $formdata = null) {

	 	parent::__construct($action, $customdata, $method, $target, $attributes, $editable, $formdata);
	}
    public function definition() {
        global $USER, $CFG, $DB, $PAGE;

        $costcenter = new costcenter();
        $mform = $this->_form;
        $context = \context_system::instance();
        $admissionid = $this->_customdata['admissionid'];
        $programid = $this->_customdata['programid'];

        $mform->addElement('textarea', 'reason', get_string('reason', 'local_admissions'));
        $mform->addRule('reason', get_string('errorreason', 'local_admissions'), 'required', null, 'client');
        $mform->setType('reason', PARAM_RAW);

        $mform->addElement('hidden', 'admissionid');
        $mform->setType('admissionid', PARAM_INT);
        $mform->setDefault('admissionid',  $admissionid);

        $mform->addElement('hidden', 'programid');
        $mform->setType('programid', PARAM_INT);
        $mform->setDefault('programid',  $programid);

        $mform->addElement('hidden', 'clickcount');
        $mform->setType('clickcount', PARAM_INT);
        $mform->setDefault('clickcount',  $clickcount);

        $mform->disable_form_change_checker();
    }

    public function definition_after_data() {
        global $USER, $CFG, $DB, $OUTPUT;
        $mform = & $this->_form;
    }

   public function validation($data, $files) {
        $errors = array();
        global $DB, $CFG;
		$sub_data = data_submitted();
		$errors = parent::validation($data, $files);
        $admissionid = $data['admissionid'];
        $programid = $data['programid'];

        return $errors;
    }
}
