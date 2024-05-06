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

class programcourses_form extends moodleform {

    public function definition() {

        global $CFG, $DB, $USER;
        $mform = &$this->_form;
        $programid = $this->_ajaxformdata['programid'];
        $yearid = $this->_ajaxformdata['yearid'];
        $curriculumid =$this->_ajaxformdata['curriculumid'];     
        $semesterid = $this->_ajaxformdata['semesterid'];
        $systemcontext = context_system::instance();
        $editoroptions = $this->_customdata['editoroptions'];

        $labelstring  = get_config('local_costcenter');

        $costid = $DB->get_field('local_curriculum','costcenter', array('id' =>$curriculumid));
        $dept = $DB->get_field('local_curriculum','open_departmentid', array('id' =>$curriculumid));
        $subdep = $DB->get_field('local_curriculum','open_subdepartment', array('id' =>$curriculumid));
        $class = 'curriculumclass';
        if(is_siteadmin() || has_capability('local/costcenter:manage_multiorganizations', $systemcontext) || has_capability('local/costcenter:manage_ownorganization', $systemcontext)){
            
            $department_select = [0 => get_string('all')];

            if($dept || $this->_ajaxformdata['open_departmentid']){
                $open_department = (int)$this->_ajaxformdata['open_departmentid'] ? (int)$this->_ajaxformdata['open_departmentid'] : $dept;
             
                $departments = $department_select + $DB->get_records_menu('local_costcenter', array('id' => $open_department), '',  $fields='id, fullname'); 
            }else{
                $open_department = 0;
                $departments = $department_select;
            }
            $departmentoptions = array(
                'ajax' => 'local_costcenter/form-options-selector',
                'data-depth' => 2,
                'data-contextid' => $systemcontext->id,
                'data-action' => 'costcenter_departments_selector',
                'data-options' => json_encode(array('id' => $costid)),
                'class' => 'departmentselect',
                'data-class' => 'departmentselect',
                'data-pluginclass' => $class,
                'multiple' => false,
                'onchange' => '(function(e){ require("local_costcenter/newcostcenter").changeElement(event) })(event)',
            );
            $department_select = [0 => get_string('all')];
            $mform->addElement('autocomplete', 'open_departmentid', get_string('department', 'local_curriculum', $labelstring->secondlevel), $departments, $departmentoptions);
            $mform->setDefault('open_departmentid', $open_department);
            $mform->addHelpButton('open_departmentid', 'open_departmentiduser', 'local_users');
            $mform->setType('open_departmentid', PARAM_INT);
        } else if (!is_siteadmin()
            && !has_capability('local/costcenter:manage_ownorganization', $systemcontext)
            && has_capability('local/costcenter:manage_owndepartments', $systemcontext)) {
            if ($USER->open_departmentid) {
                $mform->addElement('hidden', 'open_departmentid', $USER->open_departmentid, 
                            array('id' => 'id_open_departmentid', 'data-class' => 'departmentselect'));
                $mform->setType('open_departmentid', PARAM_INT);
                $mform->setConstant('open_departmentid', $USER->open_departmentid);
            }
        }

       if(is_siteadmin($USER->id) || 
            has_capability('local/costcenter:manage_multiorganizations', $systemcontext) || 
            has_capability('local/costcenter:manage_ownorganization', $systemcontext) || 
            has_capability('local/costcenter:manage_owndepartments', $systemcontext)){
            $subdepartment_select = [0 => get_string('all')];

            if($id || $this->_ajaxformdata['open_subdepartment']){
                $open_subdepartment = (int)$this->_ajaxformdata['open_subdepartment'] ? (int)$this->_ajaxformdata['open_subdepartment'] : $subdep;
                $subdepartments = $subdepartment_select + $DB->get_records_menu('local_costcenter', 
                                  array('id' => $open_subdepartment), '',  $fields='id, fullname');
            }else{
                $open_subdepartment = 0;
                $subdepartments = $subdepartment_select;
            }
            $subdepartmentoptions = array(
                'ajax' => 'local_costcenter/form-options-selector',
                'data-depth' => 3,
                'data-selectstring' => get_string('selectsubdept', 'local_courses', $labelstring),
                'data-contextid' => $systemcontext->id,
                'data-action' => 'costcenter_subdepartment_selector',
                'data-options' => json_encode(array('id' => $open_subdepartment)),
                'class' => 'subdepartmentselect',
                'data-parentclass' => 'departmentselect',
                'data-class' => 'subdepartmentselect',
                'data-pluginclass' => $class,
                'multiple' => false,
                'onchange' => '(function(e){ require("local_costcenter/newcostcenter").changeElement(event) })(event)',
            );

            $mform->addElement('autocomplete', 'open_subdepartment', get_string('sub_departments', 'local_curriculum', $labelstring->thirdlevel), $subdepartments, $subdepartmentoptions);
            $mform->setDefault('open_subdepartment', $open_subdepartment);
            $mform->addHelpButton('open_subdepartment', 'open_subdepartmentuser', 'local_users');
            $mform->setType('open_subdepartment', PARAM_INT);

        }

        $mform->addElement('hidden', 'programid', $programid);
        $mform->setType('programid', PARAM_INT);

        $mform->addElement('hidden', 'curriculumid', $curriculumid);
        $mform->setType('curriculumid', PARAM_INT);

        $mform->addElement('hidden', 'yearid', $yearid);
        $mform->setType('yearid', PARAM_INT);

        $mform->addElement('hidden', 'semesterid', $semesterid);
        $mform->setType('semesterid', PARAM_INT);

        if(is_siteadmin($USER->id) || 
            has_capability('local/costcenter:manage_multiorganizations', $systemcontext) || 
            has_capability('local/costcenter:manage_ownorganization', $systemcontext) || 
            has_capability('local/costcenter:manage_owndepartments', $systemcontext)){

            $coursesoptions = array(
                'ajax' => 'local_costcenter/form-options-selector',
                'data-selectstring' => get_string('selectcourse', 'local_curriculum'),
                'data-contextid' => $systemcontext->id,
                'data-action' => 'costcenter_courses_selector',
                'data-options' => json_encode(array('id' => $curriculumid)),
                'class' => 'courseselect',
                'data-parentclass' => 'departmentselect',
                'data-class' => 'courseselect',
                'data-pluginclass' => $class,
                'multiple' => false,
            );
            $courseid = $this->_ajaxformdata['course'];
            if($courseid){
                $courseselects = $DB->get_records_sql_menu("SELECT id, fullname FROM {course} WHERE id = $courseid");
            } if(empty($courseid)){
                $courseselects = [null => get_string('selectcourses', 'local_curriculum')];
            }
            $mform->addElement('autocomplete', 'course', get_string('course', 'local_curriculum'), 
                                $courseselects, $coursesoptions);
            $mform->addRule('course', get_string('missingcourse', 'local_curriculum'), 'required', null, 'client');
            $mform->setType('course', PARAM_INT);
        }

        $coursetype = array(null => get_string('selectcoursetype', 'local_curriculum'), '1' => 'Core', '0' => 'Elective');
        $mform->addElement('select', 'coursetype', get_string('coursetype', 'local_curriculum'), $coursetype);
        $mform->addRule('coursetype', get_string('missingcoursetype', 'local_curriculum'), 'required', null, 'client');
        $mform->disable_form_change_checker();
    }
    public function validation($data) {
        $errors = array();
        if ($data['course'] == 'null') {
            $errors['course'] = get_string('missingcourse', 'local_curriculum');
        }

        return $errors;
    }
}
