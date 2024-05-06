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
 * local local_users
 *
 * @package    local_users
 * @copyright  2019 eAbyas <eAbyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_users\forms;
use moodleform;
require_once($CFG->dirroot . '/lib/formslib.php');
class contactus_form extends moodleform {

    public function definition() {
        global $DB;

        $mform = $this->_form;
        $id = $this->_customdata['id'];
        $touser = $DB->get_records_menu('user', array('id'=>2), '', 'id,email');

        $mform->addElement('static', 'description', null, get_string('contactus_desc', 'local_users'));

        $mform->addElement('select', 'mailto', get_string('mailto', 'local_users'), $touser);
        
        $mform->addElement('text', 'subject', get_string('subject', 'local_users'));
        $mform->addRule('subject', null, 'required', null, 'client');
        $mform->setType('subject', PARAM_RAW);
        
        $mform->addElement('editor', 'emailbody', get_string('body', 'local_users'));
        $mform->addRule('emailbody', null, 'required', null, 'client');
        $mform->setType('emailbody', PARAM_RAW);

        $mform->addElement('filepicker', 'userfile', get_string('file'), null,
                   array('maxbytes' => $maxbytes, 'accepted_types' => '*'));
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->setDefault('id',  $id);
        
        $this->add_action_buttons(true, get_string('send', 'local_users'));
    }

    /**
     * validate form
     *
     * @param [object] $data 
     * @param [object] $files 
     * @return costcenter validation errors
     */
    public function validation($data, $files) {
        global $DB;
        $errors = parent::validation($data, $files);
        return $errors;
    }
     
}
