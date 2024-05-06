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
 * Version information
 *
 * @package    blocks_queries
 * @copyright  2022 eAbyas Info Solutions Pvt. Ltd.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_queries\form;
use moodleform;
use plugin_renderer_base;
use context_system;
use html_table;
use html_writer;
use moodle_url;
use stdClass;
use single_button;
use costcenter;

require_once($CFG->dirroot.'/lib/formslib.php');
require_once($CFG->dirroot.'/blocks/queries/lib.php');
use block_queries\local\lib;

class queries_form extends moodleform {
    public function definition() {
         global $CFG, $USER, $DB;
         $manageroption=array();
         $mform = $this->_form;
         $logineditingteacherinstructors = array();
         $selectarray = array();
         $selectarray[null] = get_string('select', 'block_queries');
         $lib = new lib();
         $program = $lib->get_facultyrecords($USER->id);

        foreach ($program as $programs) {
            $lib = new lib();
            $re = $lib->get_facultycoursewise($programs->courseid);
            if ($re) {
                foreach ($re as $res) {
                    $firstname = $res->firstname;
                    $lastname = $res->lastname;
                    $fullname = $firstname.' '.$lastname;
                    $manageroption[$res->id.', editingteacher'] = ($fullname);
                }
            }
        }

        if ($res->id) {
            $record = $DB->get_record_sql("SELECT * FROM {user} WHERE id = $res->id");
        } else {
            $record = null;
        }
         $adminoption = array();
         $adminoption[$record->id.', editingteacher'] = $record->firstname;
         $options = array(null => $selectarray, get_string('instructor',
         'block_queries') => $logineditingteacherinstructors, $manageroption);
         $mform->addElement('html', html_writer::tag('span',
         get_string('usertype', 'block_queries').null, array()));
         $mform->addElement('html', html_writer::start_tag('div', array('class' => 'moodleform_div')));
         // For select dropdown.

         $mform->addElement('selectgroups', 'usertype', get_string('selectuser', 'block_queries'), $options);
         $mform->addRule('usertype', get_string('required'), 'required', null, 'client');
         $mform->addElement('html', html_writer::end_tag('div', array()));

         // For text subject.
         $adminoption[$record->id] = $record->firstname;
         $mform->addElement('text', 'subject', get_string('subject', 'block_queries'), array('maxlength' => 100));
         $mform->setType('subject', PARAM_RAW);
         $mform->addRule('subject', get_string('required'), 'required', null, 'client');

        // For textarea for description.
         $mform->addElement('textarea', 'description', get_string('description', 'block_queries'), array('maxlength' => 500, 'wrap = "virtual" rows = "3" cols = "25"'));
         $mform->addRule('description', get_string('required'), 'required', null, 'client');
         $this->add_action_buttons(false, get_string('postquery', 'block_queries'));
    }
}
