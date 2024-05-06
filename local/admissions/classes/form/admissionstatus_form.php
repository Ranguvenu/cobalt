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
 * @package    local_hod
 * @copyright  moodleone
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
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
use html_writer;
class admissionstatus_form extends moodleform {
    public function definition() {
        global $CFG , $DB,$USER;
        $mform = $this->_form;

        $mform->addElement('html', '<br><br>');
        $mform->addElement('html', '<div class="form">');
        $mform->addElement('html', '<div class= "fields">');
        $mform->addElement('text', 'registrationid', 'Registration No');
        $mform->addRule('registrationid', get_string('enterregistration','local_admissions'), 'required', null);
        $mform->setType('registrationid', PARAM_NOTAGS);

        $mform->addElement('date_selector', 'dob', get_string('dob', 'local_admissions'));
        $mform->addRule('dob', get_string('missingdobdate','local_admissions'), 'required', null);
        $mform->setType('dob', PARAM_RAW);

        $buttonarray = array();
        $classarray = array('class' => 'form-submit');
        $buttonarray[] = &$mform->createElement('submit', 'saveanddisplay', get_string('submit','local_admissions'), $classarray);
        $buttonarray[] = &$mform->createElement('cancel');
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');

        $mform->addElement('html', '</div>');
        $mform->addElement('html', '</div>');
    }

    public function validation($data, $files) {
        global $DB;
        $errors = parent::validation($data, $files);
        if (!empty($data['registrationid']) && $data['registrationid'] != null || !empty($data['dob']) && $data['dob'] != null) {
            $registrationid = $DB->record_exists('local_users', array('registrationid' => $data['registrationid']));
            $dob = $DB->record_exists('local_users', array('dob' => $data['dob']));
            if ($dob != $data['dob'] || $registrationid != $data['registrationid']) {
                $errors['dob'] = get_string('invalidadmissionid','local_admissions');
            }
        }
        if (empty($data['registrationid'])) {
            $errors['registrationid'] = get_string('enterregistration','local_admissions');
        }

        return $errors;
    }
}
