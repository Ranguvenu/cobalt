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
 * Configurable Reports
 * A Moodle block for creating customizable reports
 * @package blocks
 * @author: Juan leyva <http://www.twitter.com/jleyvadelgado>
 * @date: 2009
 */

if (!defined('MOODLE_INTERNAL')) {
    //  It must be included from a Moodle page.
    die('Direct access to this script is forbidden.');
}

require_once($CFG->libdir.'/formslib.php');

class enrolmentsfield_form extends moodleform {
    public function definition() {
        global $DB, $USER, $CFG;

        $mform =& $this->_form;

        $mform->addElement('header',  'crformheader', get_string('enrolmentsfield', 'block_configurable_reports'), '');


// $filterslist=array('id','full_name','short_name');

//         $localusercolumns = array();
//         foreach ($filterslist as $flist) {
//             $localusercolumns[$flist] = $flist;
//         }
//         $columns = $DB->get_columns('course');
//         sort($columns);
//         foreach ($columns as $c) {
//             if(!in_array($c->name, $filterslist)){
//                 if($c->name == 'full_name') {
//                     $localusercolumns['fullname'] = $c->name;
//                     continue;
//                 }
//                 $localusercolumns[$c->name] = $c->name;
//             }
//         }


        // $columns = $DB->get_columns('user');
        $columns = array('username', 'coursename');
        $coursecolumns = array();
        foreach ($columns as $c) {
            $coursecolumns[$c] = $c;
        }

        $mform->addElement('select', 'column', get_string('column', 'block_configurable_reports'), $coursecolumns);

        $this->_customdata['compclass']->add_form_elements($mform, $this);

        // Buttons.
        $this->add_action_buttons(true, get_string('add'));

    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        $errors = $this->_customdata['compclass']->validate_form_elements($data, $errors);
        return $errors;
    }
}
