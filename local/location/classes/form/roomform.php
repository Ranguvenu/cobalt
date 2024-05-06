<?php
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

namespace local_location\form;
use core;
use moodleform;
use context_system;

if (!defined('MOODLE_INTERNAL')) {
	die('Direct access to this script is forbidden.'); ///  It must be included from a Moodle page
}

require_once "{$CFG->dirroot}/lib/formslib.php";

class roomform extends moodleform {

	/**
	 * Definition of the room form
	 */
	function definition() {
		global $DB,$USER;
		$systemcontext = context_system::instance();
		$mform = &$this->_form;
		$roomid = $this->_customdata['id'];

		
		$mform->addElement('hidden', 'id', $roomid);
		$mform->setType('id', PARAM_INT);

		$params = array();
		$sql = "SELECT id, fullname FROM {local_location_institutes} WHERE 1=1 ";
     if (is_siteadmin() || has_capability('local/costcenter:manage_multiorganizations', $systemcontext) ) {
	    $params['visible'] = 1;
	    $sql .= " AND visible = :visible ";
	  } else if (has_capability('local/costcenter:manage_ownorganization', $systemcontext)) {
	    $sql .= " AND costcenter IN ($USER->open_costcenterid)";
	  } else if (has_capability('local/costcenter:manage_owndepartments', $systemcontext)) {
	    $sql .= " AND open_departmentid IN ($USER->open_departmentid)";
	  } else if (has_capability('local/costcenter:manage_ownsubdepartments', $systemcontext)) {
	    $sql .= " AND open_subdepartment IN ($USER->open_subdepartment)";
	  }
       
    $institutes = $DB->get_records_sql($sql,$params);
		$institutenames = array();
		$institutenames[null] = get_string('select', 'local_location');
		if ($institutes) {
			foreach ($institutes as $institute) {
				$institutenames[$institute->id] = $institute->fullname;
			}
		}

		$mform->addElement('select', 'instituteid', get_string('institute', 'local_location'), $institutenames, array());
		$mform->addRule('instituteid', null, 'required', null, 'client');

		$mform->addElement('text', 'name', get_string('room_name', 'local_location'), array('maxlength' => 45));
		$mform->setType('name', PARAM_TEXT);
		$mform->addRule('name', null, 'required', null, 'client');

		$mform->addElement('text', 'address', get_string('address', 'local_location'), array('maxlength' => 100));
		$mform->setType('address', PARAM_TEXT);
		$mform->addRule('address', null, 'required', null, 'client');

		$mform->addElement('hidden', 'mincapacity', 0);
      $mform->setType('mincapacity', PARAM_INT);
      $maxcapacity = 1500;//9223372036854775807
      $mform->addElement('hidden', 'maxcapacity', $maxcapacity);
      $mform->setType('maxcapacity', PARAM_INT);
		
		$mform->addElement('text', 'capacity', get_string('capacity', 'local_location'));
		$mform->setType('capacity', PARAM_RAW);
		$mform->addHelpButton('capacity','capacityofusers','local_location');

		$mform->disable_form_change_checker();
	}

  /**
   * Perform minimal validation on the settings form
   * @param array $data
   * @param array $files
   */
  public function validation($data, $files) {
  	global $DB;
  	$errors = parent::validation($data, $files);
  	if ($data['capacity'] > $data['maxcapacity']) {
  		$errors['capacity'] = get_string('notallowed', 'local_location');
  	}

  	if (!is_numeric($data['capacity']) && !empty($data['capacity'])) {
  		$errors['capacity'] = get_string('req_numeric', 'local_location');
  	}

  	if (!preg_match('/^[a-z0-9\s\_]+$/i', $data['capacity']) && !empty($data['capacity'])) {
  		$errors['capacity'] = get_string('specialcharactersnotallwoed','local_location');
  	}

  	if (strlen(trim($data['name'])) == 0 && !empty($data['name'])) {
  		$errors['name'] = get_string('blankspaces','local_location');
  	}

  	if (strlen(trim($data['address'])) == 0 && !empty($data['address'])) {
  		$errors['address'] = get_string('blankspaces','local_location');
  	}

      return $errors;
  }
}

