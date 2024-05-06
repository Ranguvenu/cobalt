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

namespace local_timetable\form;
defined('MOODLE_INTERNAL') || die();

require_once $CFG->libdir . '/formslib.php';
require_once($CFG->dirroot.'/local/users/classes/cron/cronfunctionality.php');

use moodleform;
use csv_import_reader;
use core_text;
define('ONLY_ADD', 1);
define('ONLY_UPDATE', 2);
define('ADD_UPDATE', 3);
class hrms_async extends moodleform{

	function definition() {
		$mform = $this->_form;
		$filepickeroptions = array();
		$tlid = optional_param('semid','',PARAM_INT);
        	$filepickeroptions['accepted_types'] = 'csv';
        	$filepickeroptions['maxfiles'] = 1;
		$mform->addElement('filepicker', 'userfile', get_string('file'), null, $filepickeroptions);
		$mform->addRule('userfile', null, 'required');
		
		$mform->addElement('hidden','semid',$tlid);

		$mform->addElement('hidden',  'delimiter_name');
		$mform->setType('delimiter_name', PARAM_TEXT);
		$mform->setDefault('delimiter_name',  'comma');

		$mform->addElement('hidden',  'encoding');
		$mform->setType('encoding', PARAM_RAW);
		$mform->setDefault('encoding',  'UTF-8');

		$mform->addElement('hidden', 'enrollmentmethod');
		$mform->setType('enrollmentmethod', PARAM_INT);
		$mform->setDefault('enrollmentmethod', MANUAL_ENROLL);		
        // $mform->addElement('advcheckbox', 'createpassword', get_string('createpassword', 'auth'));
        // $options = array(null=>get_string('select_opt','local_users'),ONLY_ADD=>get_string('only_add','local_users'), ONLY_UPDATE=>get_string('only_update','local_users'), ADD_UPDATE=>get_string('add_update','local_users'));
		// $mform->addElement('select', 'option', get_string('options', 'local_users'), $options);
        // $mform->addRule('option', null, 'required', null, 'client');
		// $mform->setType('option', PARAM_INT);

		$this->add_action_buttons(true, get_string('upload'));
	}
}

