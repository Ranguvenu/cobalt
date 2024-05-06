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
// namespace local_groups\form;
use moodleform;
use context_instance;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/lib/formslib.php');
class batchgroup extends moodleform {

    /**
     * Define the group batchgroup form
     */
    public function definition() {
        global $DB, $USER;
        $mform = $this->_form;
        $editoroptions = $this->_customdata['editoroptions'];
        $id = $this->_customdata['id'];
        $batchid = $this->_customdata['batchid'];
        $mformajax    =& $this->_ajaxformdata;
        $context = \context_system::instance();

        $sql = $DB->get_field('cohort', 'name', ['id' => $batchid]);
        $mform->addElement('static', 'cohortid', get_string('batch_name', 'local_groups'), $sql);
        $mform->setType('batchid', PARAM_INT);

        $mform->addElement('hidden', 'id', $id);
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'batchid');
        $mform->setType('batchid', PARAM_INT);
        $mform->setDefault('batchid', $batchid);

        $mform->addElement('hidden', 'contextid', $context->id);
        $mform->setType('contextid', PARAM_INT);
        $mform->setConstant('contextid', $context->id);

        $mform->addElement('text', 'name', get_string('groupname', 'local_groups'));
        $mform->addRule('name', get_string('groupname', 'local_groups'), 'required', null, 'client');
        $mform->setType('name', PARAM_RAW);

        $mform->addElement('text', 'idnumber', get_string('group_id', 'local_groups'));
        $mform->addRule('idnumber', get_string('group_id', 'local_groups'), 'required', null, 'client');
        $mform->setType('idnumber', PARAM_RAW);

        $mform->addElement('hidden', 'visible', get_string('visible', 'local_groups'));
        $mform->setDefault('visible', 0);

        $this->add_action_buttons();

        if ($id > 0) {
            $subdata = $DB->get_record('local_sub_groups', ['id' => $id]);
            $data = $DB->get_record('cohort', ['id' => $subdata->groupid] );
            $type = array(
                'name' => $data->name,
                'idnumber' => $data->idnumber
            );
            $mform->setDefaults($type);
        }
    }

    public function validation($data, $files) {
        global $DB;
        $errors = parent::validation($data, $files);
        if (!empty($data['name'])) {
            if (strlen(trim($data['name'])) == 0) {
                $errors['name'] = get_string('blankspaces', 'local_groups');
            }
        }

        if (!empty($data['idnumber'])) {
            if (strrpos($data['idnumber'], ' ') !== false) {
                $errors['idnumber'] = get_string('blankspaces', 'local_groups');
            }
        }

        if ($data['id'] > 0) {
            $subdata = $DB->get_record('local_sub_groups', ['id' => $data['id']]);
            $currentsql = "SELECT idnumber
                            FROM {cohort}
                           WHERE id != {$subdata->groupid} AND idnumber LIKE '%".$data['idnumber']."%'";
            $current = $DB->get_record_sql($currentsql);
            if ($current && $data['id'] != $current->id) {
                $errors['idnumber'] = get_string('duplicategroupidnumber','local_groups');
            }
        } else {
            $currentsql = "SELECT idnumber
                            FROM {cohort}
                           WHERE idnumber LIKE '%".$data['idnumber']."%'";
            $current = $DB->get_record_sql($currentsql);
            if ($current) {
                $errors['idnumber'] = get_string('duplicategroupidnumber','local_groups');
            }

        }

        return $errors;
    }
}
