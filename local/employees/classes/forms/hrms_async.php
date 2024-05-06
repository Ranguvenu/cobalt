<?php
namespace local_employees\forms;
defined('MOODLE_INTERNAL') || die();

require_once $CFG->libdir . '/formslib.php';
require_once($CFG->dirroot.'/local/employees/classes/cron/cronfunctionality.php');

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
        	$filepickeroptions['accepted_types'] = 'csv';
        	$filepickeroptions['maxfiles'] = 1;
		$mform->addElement('filepicker', 'userfile', get_string('file'), null, $filepickeroptions);
		$mform->addRule('userfile', null, 'required');

		$mform->addElement('hidden',  'delimiter_name');
		$mform->setType('delimiter_name', PARAM_TEXT);
		$mform->setDefault('delimiter_name',  'comma');

		$mform->addElement('hidden',  'encoding');
		$mform->setType('encoding', PARAM_RAW);
		$mform->setDefault('encoding',  'UTF-8');

		$mform->addElement('hidden', 'enrollmentmethod');
		$mform->setType('enrollmentmethod', PARAM_INT);
		$mform->setDefault('enrollmentmethod', MANUAL_ENROLL);
		$mform->addElement('advcheckbox', 'createpassword', get_string('createpassword', 'auth'));
        $options = array(null=>get_string('select_opt','local_employees'),ONLY_ADD=>get_string('only_add','local_employees'), ONLY_UPDATE=>get_string('only_update','local_employees'), ADD_UPDATE=>get_string('add_update','local_employees'));
		$mform->addElement('select', 'option', get_string('options', 'local_employees'), $options);
        $mform->addRule('option', null, 'required', null, 'client');
		$mform->setType('option', PARAM_INT);

		$this->add_action_buttons(true, get_string('upload'));
	}
}
