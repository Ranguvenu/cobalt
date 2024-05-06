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
 * Manage curriculum Form.
 *
 * @package    local_curriculum
 * @copyright  2022 Eabyas Info Solutions <www.eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_curriculum\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/completionlib.php');
use context_system;
use local_program\local\querylib;
use moodleform;
use core_component;
use local_users\functions\userlibfunctions as userlib;
use local_costcenter\functions\userlibfunctions as costcenterlib;

class curriculum_form extends moodleform {
    public $formstatus;
    public function __construct($action = null, $customdata = null, $method = 'post',
        $target = '', $attributes = null, $editable = true, $formdata = null) {
        parent::__construct($action, $customdata, $method, $target, $attributes, $editable,
            $formdata);
    }

    public function definition() {
        global $CFG, $USER, $PAGE, $DB;
        $querieslib = new querylib();
        $mform = &$this->_form;
        $systemcontext = context_system::instance();
        $renderer = $PAGE->get_renderer('local_curriculum');
        $formstatus = $this->_customdata['form_status'];
        $formdata = $this->_customdata['formdata'];
        $id = $this->_customdata['id'] > 0 ? $this->_customdata['id'] : 0;
        $programid = $this->_customdata['program'] > 0 ? $this->_customdata['program'] : 0;
        $editoroptions = $this->_customdata['editoroptions'];

        // $costid = $DB->get_field('local_curriculum','costcenter', array('id' =>$id));
        // $dept = $DB->get_field('local_curriculum','open_departmentid', array('id' =>$id));
        // $subdep = $DB->get_field('local_curriculum','open_subdepartment', array('id' =>$id));

        $formheaders = array_keys($this->formstatus);
        $formheader = $formheaders[$formstatus];

        $mform->addElement('hidden', 'id', $id);
        $mform->setType('id', PARAM_INT);
        $mformajax    =& $this->_ajaxformdata;
        
        $getdepartmentelements = costcenterlib::org_hierarchy($mform, $id, $systemcontext, $mformajax, 'curriculum', 'curriculumclass');

        $mform->addElement('hidden', 'program', get_string('program', 'local_curriculum'));
        $mform->setType('program', PARAM_INT);
        $mform->setDefault('program', $programid);

        $mform->addElement('text', 'name', get_string('curriculum_name', 'local_curriculum'), array());
        // $mform->addRule('name', get_string('curriculum_name', 'local_curriculum'), 'required', null, 'client');

        $mform->addRule('name' , get_string('curriculumlengthis225words', 'local_curriculum'), 'maxlength' , 225 , 'client');
        $mform->addRule('name' , get_string('curriculum_name', 'local_curriculum'), 'required' , 225 , 'client');


        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $editoroptions = array(
            'noclean' => false,
            'autosave' => false
        );
        $mform->addElement('editor', 'cr_description',
                get_string('description', 'local_curriculum'), null, $editoroptions);
        $mform->setType('cr_description', PARAM_RAW);
        $mform->addHelpButton('cr_description', 'cr_description', 'local_curriculum');

        $mform->disable_form_change_checker();
    }
    public function validation($data, $files) {
        global $CFG, $DB, $USER;
        $errors = array();
        $labelstring = get_config('local_costcenter');
        $costcenter = $data['open_costcenterid'];
        $deprtid = $data['open_departmentid'];
        $subdeprtid = $data['open_subdepartment'];

        if (isset($data['open_costcenterid']) && ($data['open_costcenterid'] == 0)) {
            $errors['open_costcenterid'] = get_string('errororganization', 'local_users', $labelstring->firstlevel);
        }

        if (isset($data['name']) && empty(trim($data['name']))) {
            $errors['name'] = get_string('valnamerequired', 'local_curriculum');
        }
        if (!empty($deprtid) && !empty($costcenter) && $deprtid != 0 && $costcenter != 0) {
            $deptexists = $DB->record_exists('local_costcenter', array('id' => $deprtid, 'parentid' => $costcenter));
            if (!$deptexists) {
                $errors['open_departmentid'] = get_string('deptnotavail', 'local_curriculum', $labelstring);
                $errors['open_subdepartment'] = get_string('subdeptnotavail', 'local_curriculum', $labelstring);
            }
        }
        // $curmnameexistssql = "SELECT name FROM {local_curriculum} WHERE costcenter = :costcenter AND open_departmentid = :deptid AND open_subdepartment = :subdept AND id NOT IN (:crmid)";

        // $curmnameexists = $DB->record_exists_sql($curmnameexistssql, array('costcenter' => $data['costcenter'], 'deptid' => $data['open_departmentid'], 'subdept' => $data['open_subdepartment'], 'crmid' => $data['id']));

        // if ($curmnameexists) {
        //     $errors['name'] = get_string('curmnameexisted', 'local_curriculum');
        // }
        return $errors;
    }

    public function set_data($components) {
        global $DB;
        $context = context_system::instance();
        $data = $DB->get_record('local_curriculum', array('id' => $components->id));
        $data->cr_description = array();
        $data->cr_description['text'] = $data->description;
        $draftitemid = file_get_submitted_draft_itemid('curriculumlogo');
        file_prepare_draft_area($draftitemid, $context->id, 'local_curriculum', 'curriculumlogo',
            $data->curriculumlogo, null);
        $data->curriculumlogo = $draftitemid;
        $data->programid = $data->programid;
        $data->open_costcenterid = $data->costcenter;

        parent::set_data($data);
    }
}
