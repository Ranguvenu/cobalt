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
 * program Capabilities
 *
 * program - A Moodle plugin for managing ILT's
 * @package
 * @author     eAbyas Info Solutions
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once($CFG->libdir.'/formslib.php');
class filters_form extends moodleform {

    function definition() {
        global $CFG;

        $systemcontext = \context_system::instance();

        $mform    = $this->_form;
        $filterlist        = $this->_customdata['filterlist'];// this contains the data of this form
        $filterparams      = $this->_customdata['filterparams'] ?? null;
        $submitidcust      = $this->_customdata['submitid'] ?? null;

        $submitid = $submitidcust ? $submitidcust : 'filteringform';

        $this->_form->_attributes['id'] = $submitid;

        foreach ($filterlist as $key => $value) {
            if($value === 'organizations' || $value === 'departments' || $value == 'subdepartment'){
                $filter = 'costcenter';
            }else if($value == 'status'){
                $filter = 'courses';
            }else if($value === 'learningplan'){
                $filter = 'learningplan';
            }else if($value === 'program'){
                $filter = 'program';
            } else if($value === 'dependent_fields'){
                require_once($CFG->dirroot.'/local/costcenter/lib.php');
                local_costcenter_set_costcenter_path($this->_customdata);
                if(is_siteadmin() || has_capability('local/costcenter:manage_multiorganizations', $systemcontext)){
                    local_costcenter_get_dependent_fields($mform, $this->_ajaxformdata, $this->_customdata,null, false, 'local_program', $categorycontext, $multiple = true);
                } else if(!is_siteadmin() && !has_capability('local/costcenter:manage_multiorganizations', $systemcontext) && has_capability('local/costcenter:manage_ownorganization', $systemcontext)){
                    local_costcenter_get_dependent_fields($mform, $this->_ajaxformdata, $this->_customdata, range(2,3), false, 'local_program', $categorycontext, $multiple = true);
                } else if(!is_siteadmin() && !has_capability('local/costcenter:manage_multiorganizations', $systemcontext) && !has_capability('local/costcenter:manage_ownorganization', $systemcontext) && has_capability('local/costcenter:manage_owndepartments', $systemcontext)){
                    local_costcenter_get_dependent_fields($mform, $this->_ajaxformdata, $this->_customdata, range(3,3), false, 'local_program', $categorycontext, $multiple = true);
                }
                continue;
            } else{
                $filter = $value;
            }
            $core_component = new core_component();
            $courses_plugin_exist = $core_component::get_plugin_directory('local', $filter);
            if ($courses_plugin_exist) {
                require_once($CFG->dirroot . '/local/' . $filter . '/lib.php');
                $functionname = $value.'_filter';
                $functionname($mform);
            }
        }

        $buttonarray = array();

        $buttonarray[] =& $mform->createElement('submit', 'add', get_string('apply', 'local_program'));
        $buttonarray[] =& $mform->createElement('cancel', 'remove', get_string('reset', 'local_program'));
        $grp =& $mform->addElement('group', 'buttonsgrp', '', $buttonarray, null, false);
        
        $mform->disable_form_change_checker();

    }
     /**
     * Validation.
     *
     * @param array $data
     * @param array $files
     * @return array the errors that were found
     */
    function validation($data, $files) {
        global $DB;

        $errors = parent::validation($data, $files);
        return $errors;
    }
}
