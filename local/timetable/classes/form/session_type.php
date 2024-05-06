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

// namespace local_timetable\form;
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');
use moodleform;

class session_type extends moodleform{

	function definition() {
        global $CFG, $USER, $DB, $PAGE, $OUTPUT;
        $systemcontext = context_system::instance();
        $lablestring = get_config('local_costcenter');
		$mform = $this->_form;
		$id = $this->_customdata['id'];

    	$mform->addElement('hidden', 'id', $id);
        $mform->setType('id', PARAM_INT);

        $params = array();
        $params['visible'] = 1;
        $params['depth'] = 1;

        $sql = "SELECT * ";
        $sql .= "FROM {local_costcenter} ";
        $sql .= "WHERE 1=1 ";
        if (is_siteadmin()
            || has_capability('local/costcenter:manage_multiorganizations', $systemcontext)
            ) {
            $sql .= "AND depth = :depth ";
        } else if (has_capability('local/costcenter:manage_ownorganization', $systemcontext)) {
            $params['costcenterid'] = $USER->open_costcenterid;
            $sql .= "AND id = :costcenterid";
        }

		$costdata = $DB->get_records_sql($sql, $params);
        $costcenter = [null => get_string('selectorganization', 'local_timetable', $lablestring->firstlevel)];
        foreach ($costdata as $key => $value) {
            $costcenter[$key] = $value->fullname;
        }

        if (is_siteadmin()
            || has_capability('local/costcenter:manage_multiorganizations', $systemcontext)
            ) {
            $mform->addElement('select', 'organization', get_string('organisations', 'local_timetable', $lablestring->firstlevel), $costcenter);
            $mform->addRule('organization', get_string('missingorganisation', 'local_timetable', $lablestring->firstlevel), 'required', null, 'client');
            $mform->setType('organization', PARAM_INT);
        } else if (has_capability('local/costcenter:manage_ownorganization', $systemcontext)) {
            $mform->addElement('hidden', 'new_organization', $USER->open_costcenterid);
            $mform->setType('organization', PARAM_INT);
        }
        

        $repeatarray = array();

        $repeatarray[] = $mform->createElement('hidden', 'sesstype', 'sesstype');
        $repeatarray[] = $mform->createElement('text', 'session_type', get_string('session_type', 'local_timetable'), array('maxlength' => 100));

         $repeateloptions = array();
        $repeateloptions['sesstype']['default'] = '{no}';
        $repeateloptions['sesstype']['rule'] = 'numeric';
        $repeateloptions['sesstype']['type'] = PARAM_INT;

        $repeateloptions['session_type']['rule'] = 'required';
        $repeateloptions['session_type']['type'] = PARAM_RAW;
        $repeatno = 1;

        $this->repeat_elements($repeatarray, $repeatno, $repeateloptions, 'option_repeats', 'option_add_fields', 1, get_string('addmore', 'local_timetable'), true);

    	$this->add_action_buttons(true, get_string('save'));

        if ($id > 0) {
            $data = $DB->get_record('local_session_type', ['id' => $id]);

            $type = array(
                'organization' => $data->organization,
                'session_type[0]' => $data->session_type
            );
            $mform->setDefaults($type);
        }
	}

    /**
     * Perform minimal validation on the settings form
     * @param array $data
     * @param array $files
     */
    public function validation($data, $files) {
        global $DB;
        $lablestring = get_config('local_costcenter');
        $errors = parent::validation($data, $files);
        if ($data['new_organization']) {
            $data['organization'] = $data['new_organization'];
        }
        if ($data['organization'] == 0) {
            $errors['organization'] = get_string('missingorganisation', 'local_timetable', $lablestring->firstlevel);
        }

        for($i = 0; $i < count($data['session_type']); $i++){
            $session_type = $data['session_type'][$i];
            if ($DB->record_exists('local_session_type', ['organization' => $data['organization'], 'session_type' => ucfirst($data['session_type'][$i])])) {
                if (ucfirst($data['session_type'][$i]) == get_string('exam', 'local_timetable')) {
                    $errors['session_type['.$i.']'] = get_string('alreadyexisted', 'local_timetable');
                }
            }

            if (strlen(trim($data['session_type'][$i])) == 0 ) {
                $errors['session_type['.$i.']'] = get_string('blankspaces','local_location');
            }
        }

        return $errors;
    }
}

