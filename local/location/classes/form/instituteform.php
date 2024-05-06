<?php
namespace local_location\form;
use core;
use moodleform;
use context_system;
/*
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package Local
 * @subpackage classroom
 */

if (!defined('MOODLE_INTERNAL')) {
	die('Direct access to this script is forbidden.'); ///  It must be included from a Moodle page
}
require_once($CFG->dirroot.'/lib/formslib.php');
require_once($CFG->dirroot . '/local/costcenter/lib.php');
use local_costcenter\functions\userlibfunctions as costcenterlib;

class instituteform extends moodleform {

	/**
	 * Definition of the room form
	 */
	function definition() {
		global $DB,$USER;

		$mform = &$this->_form;
		
		$instituteid = $this->_customdata['id'];
		$mformajax    =& $this->_ajaxformdata;
		$context = \context_system::instance();

		$selected_ins_name = $DB->get_records('local_costcenter');
		$mform->setType('id', PARAM_INT);

		costcenterlib::org_hierarchy($mform, $instituteid, $context, $mformajax, 'location_institutes', 'locationclass'); 

		$mform->addElement('hidden', 'id', $instituteid);
		$mform->setType('instituteid', PARAM_INT);

		$allow_multi_session = array();
		$allow_multi_session[] = $mform->createElement('radio', 'institute_type', '', get_string('internal', 'local_location'), 1);
		$allow_multi_session[] = $mform->createElement('radio', 'institute_type', '', get_string('external', 'local_location'), 2);
		$mform->addGroup($allow_multi_session, 'radioar', get_string('institutetype', 'local_location'), array(' '), false);
		$mform->setDefault('institute_type',1);
		$mform->addHelpButton('radioar', 'locationtype', 'local_location');   

		$mform->addElement('text', 'fullname', get_string('institute_name', 'local_location'), array('maxlength' => 50));
		$mform->setType('fullname', PARAM_TEXT);
		$mform->addRule('fullname', null, 'required', null, 'client');

		$mform->addElement('textarea', 'address', get_string('address', 'local_location'), array('maxlength' => 100));
		$mform->setType('address', PARAM_TEXT);
		$mform->addRule('address', null, 'required', null, 'client');



		// $this->add_action_buttons();
		$mform->disable_form_change_checker();
	}

	public function validation($data, $files) {
		global $DB;
		$errors = parent::validation($data, $files);
		if(strlen($data['address'])> 500){
			$errors['address'] = get_string('addresstoolong', 'local_location');
		}

		$fullnameexists = $DB->get_record('local_location_institutes', array('fullname' => trim($data['fullname']), 'costcenter' => $data['open_costcenterid']));

		if ($fullnameexists) {
	        if ($fullnameexists->fullname == $data['fullname'] && $data['id'] != $fullnameexists->id) {
	            $errors['fullname'] = get_string('fullnametakenlp', 'local_location', $fullnameexists->fullname);
	        }
	    }

	    if (strlen(trim($data['fullname'])) == 0 && !empty($data['fullname'])) {
	    	$errors['fullname'] = get_string('blankspaces','local_location');
	  	}

	  	if (strlen(trim($data['address'])) == 0 && !empty($data['address'])) {
	  		$errors['address'] = get_string('blankspaces','local_location');
	  	}

		return $errors; 
	}

}
