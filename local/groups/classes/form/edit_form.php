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
 * @package    local_groups
 * @copyright  2022 eAbyas Info Solution Pvt Ltd.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_groups\form;
use moodleform;
use context_instance;


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/lib/formslib.php');
require_once($CFG->dirroot . '/local/costcenter/lib.php');

use local_users\functions\userlibfunctions as userlib;
use local_costcenter\functions\userlibfunctions as costcenterlib;
class edit_form extends moodleform {

    /**
     * Define the group edit form
     */
    public function definition() {
        global $DB, $USER;
        $mform = $this->_form;
        $editoroptions = $this->_customdata['editoroptions'];
        $cohort = $this->_customdata['data'];
        $id = $this->_customdata['id'];
        $mformajax    =& $this->_ajaxformdata;
        $context = \context_system::instance();

        $getdepartmentelements = costcenterlib::org_hierarchy($mform, $id, $context, $mformajax, 'groups', 'groupclass');

        $mform->addElement('text', 'name', get_string('name', 'local_groups'), 'maxlength="50" size="50"');
        $mform->addRule('name', get_string('error_name', 'local_groups'), 'required', null, 'client');
        $mform->setType('name', PARAM_RAW);

        $mform->addElement('hidden', 'contextid', $context->id);
        $mform->setType('contextid', PARAM_INT);
        $mform->setConstant('contextid', $context->id);

        $mform->addElement('text', 'idnumber', get_string('idnumber', 'local_groups'), 'maxlength="50" size="50"');
        $mform->addRule('idnumber', get_string('groupidnumber', 'local_groups'), 'required', null, 'client');
        $mform->setType('idnumber', PARAM_RAW); // Idnumbers are plain text, must not be changed.

        $mform->addElement('hidden', 'visible', get_string('visible', 'local_groups'));
        $mform->setDefault('visible', 1);
        $mform->addHelpButton('visible', 'visible', 'cohort');

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        if (isset($this->_customdata['returnurl'])) {
            $mform->addElement('hidden', 'returnurl', $this->_customdata['returnurl']->out_as_local_url());
            $mform->setType('returnurl', PARAM_LOCALURL);
        }

        $this->add_action_buttons();

        $this->set_data($cohort);
    }

    public function validation($data, $files) {
        global $DB;
        $errors = parent::validation($data, $files);
        $labelstring = get_config('local_costcenter');
        $count = $DB->get_record('cohort', array('id' => $data['id']));
        if (!empty($data['idnumber']) && $count->idnumber != $data['idnumber']) {
            $currentsql = "SELECT idnumber
                            FROM {cohort}
                           WHERE id != {$data['id']} AND idnumber LIKE '%".$data['idnumber']."%'";
            $current = $DB->get_record_sql($currentsql);
            if ($current) {
                $errors['idnumber'] = get_string('duplicateidnumber','local_groups');
            }
        }

        if ($data['id'] > 0 ) {
            if ($data['open_costcenterid'] == 0 || empty($data['open_costcenterid'])) {
                $errors['open_costcenterid'] = get_string('pleaseselectorganization', 'local_groups', $labelstring->firstlevel);
            }
        } else {
            if ($data['open_costcenterid'] == 0) {
                $errors['open_costcenterid'] = get_string('pleaseselectorganization', 'local_groups', $labelstring->firstlevel);
            }
        }

        if (!empty($data['name'])) {
            if (strlen(trim($data['name'])) == 0) {
                $errors['name'] = get_string('blankspaces','local_groups');
            }/* else if (preg_match('/^[\s\<>~!@#$%^&*()]+$/u', $data['name'])) {
                $errors['name'] = get_string('specialcharactersnotallwoed','local_groups');
            }*/
        }

        if (!empty($data['idnumber'])) {
            if (strlen(trim($data['idnumber'])) == 0) {
                $errors['idnumber'] = get_string('blankspaces','local_groups');
            } /*else if (preg_match('/^[\s\<>~!@#$%^&*()]+$/u', $data['idnumber'])) {
                $errors['idnumber'] = get_string('specialcharactersnotallwoed','local_groups');
            }*/
        }

        if(empty($data['open_departmentid'])){
            $errors['open_departmentid'] = get_string('deptnameshouldselect', 'local_users', $labelstring);
        }

        if (empty($data['name'])) {
            $errors['name'] = get_string('error_name', 'local_groups');
        }

        return $errors;
    }
}
