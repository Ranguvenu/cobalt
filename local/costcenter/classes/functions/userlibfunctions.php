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
 * @package    local_costcenter
 * @copyright  2022 eAbyas Info Solutions Pvt. Ltd.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_costcenter\functions;
defined('MOODLE_INTERNAL') || die;
require_once($CFG->dirroot . '/local/costcenter/lib.php');

class userlibfunctions {
    /* find department list
    @param need to pass costcenter value*/
    public function find_departments_list($costcenter) {
        global $DB;
        if ($costcenter) {
            $sql = "SELECT id, fullname FROM {local_costcenter} ";

            $costcenters = explode(',', $costcenter);
            list($relatedparentidsql, $relatedparentidparams) = $DB->get_in_or_equal($costcenters, SQL_PARAMS_NAMED, 'parentid');
            $sql .= " where parentid $relatedparentidsql";

            $subdep = $DB->get_records_sql($sql, $relatedparentidparams);

            return $subdep;
        } else {
            return $costcenter;
        }
    }
    /* find sub department list
    @param need to pass department value*/
    public function find_subdepartments_list($department) {
        global $DB;
        $sql = "SELECT id, fullname FROM {local_costcenter} ";

        $departments = explode(',', $department);
        list($relatedparentidsql, $relatedparentidparams) = $DB->get_in_or_equal($departments, SQL_PARAMS_NAMED, 'parentid');
        $sql .= " where parentid $relatedparentidsql";

        $subdep = $DB->get_records_sql($sql, $relatedparentidparams);

        return $subdep;
    }

    /* find supervisors list
    @param need to pass supervisor and userid optional value*/
    public function find_supervisor_list($supervisor, $userid=0) {
        global $DB;
        if ($supervisor) {
            $sql = "SELECT u.id,Concat(u.firstname,' ',u.lastname) as username
                     FROM {user} u
                    WHERE u.suspended = :suspended AND u.deleted = :deleted
                      AND u.open_costcenterid = :costcenterid  AND u.id > 2";
            if ($userid) {
                $sql .= " AND u.id != :userid";
            }
            $subdep = $DB->get_records_sql($sql, array('suspended' => 0, 'deleted' => 0,
                                                        'costcenterid' => $supervisor, 'userid' => $userid)
                                            );
            return $subdep;
        }
    }

    /* find department supervisors list
    @param need to pass supervisor and userid optional value*/
    public function find_dept_supervisor_list($supervisor, $userid=0) {
        if ($supervisor) {
            global $DB;
            $sql = "SELECT u.id,Concat(u.firstname,' ',u.lastname) as username
                     FROM {user} u
                    WHERE u.suspended!=1 AND u.deleted!=1 AND u.open_departmentid = $supervisor
                     AND u.id!= 1 AND u.id!=2";
            if ($userid) {
                $sql .= " AND u.id != {$userid} AND u.id IN (SELECT open_supervisorid FROM {user} WHERE id = {$userid})";
            }
            $subdep = $DB->get_records_sql($sql);
            return $subdep;
        }
    }

    /**
     * Description: [department_elements code]
     * @param  [mform]  $mform          [form where the filetr is initiated]
     * @return [void]                  [description]
     */
    public function department_elements($mform, $id, $context, $mformajax, $plugin) {
        global $DB, $USER;
        $existdata = $DB->get_record('local_'.$plugin, array('id' => $id));

        if ($plugin == 'evaluations') {
            $pluginname = 'local_evaluation';
        } else {
            $pluginname = 'local_course';
        }
        $systemcontext = \context_system::instance();
        if (is_siteadmin($USER->id) || has_capability('local/costcenter:manage_multiorganizations',
                $systemcontext)) {
            $organisationselect = [null => get_string('selectorg', 'local_courses')];
            if ($id || $mformajax['costcenterid']) {
                $opencostcenter = (int) $mformajax['costcenterid'] ? (int)$mformajax['costcenterid'] : $existdata->costcenterid;
                $organisations = $organisationselect + $DB->get_records_menu('local_costcenter',
                                                            array('id' => $opencostcenter), '',
                                                            $fields = 'id, fullname'
                                                        );
            } else {
                $opencostcenter = 0;
                $organisations = $organisationselect;
            }
            $costcenteroptions = array(
                'ajax' => 'local_costcenter/form-options-selector',
                'data-contextid' => $systemcontext->id,
                'data-action' => 'costcenter_organisation_selector',
                'data-options' => json_encode(array('id' => $opencostcenter)),
                'class' => 'organisationnameselect',
                'data-class' => 'organisationselect',
                'multiple' => false,
            );

            $mform->addElement('autocomplete', 'costcenterid',
                                get_string('organization', 'local_courses'),
                                $organisations, $costcenteroptions
                            );
            $mform->addHelpButton('costcenterid', 'costcenteridcourse', $pluginname);
            $mform->setType('costcenterid', PARAM_INT);
            $mform->addRule('costcenterid', get_string('pleaseselectorganization', 'local_courses'), 'required', null, 'client');

        } else if (has_capability('local/costcenter:manage_ownorganization', $systemcontext)) {

            $mform->addElement('hidden', 'costcenterid', null,
                               array('id' => 'id_costcenterid',
                                    'data-class' => 'organisationselect'
                               )
                           );
            $mform->setType('costcenterid', PARAM_INT);
            $mform->setConstant('costcenterid', $USER->open_costcenterid);

        } else if (has_capability('local/costcenter:manage_owndepartments', $systemcontext)) {

            $mform->addElement('hidden', 'costcenterid', null,
                                array('id' => 'id_costcenterid',
                                    'data-class' => 'organisationselect'
                                )
                            );
            $mform->setType('costcenterid', PARAM_INT);
            $mform->setConstant('costcenterid', $USER->open_costcenterid);

            $mform->addElement('hidden', 'departmentid', $USER->open_departmentid,
                                array('id' => 'id_departmentid',
                                    'data-class' => 'departmentselect'
                                )
                            );
            $mform->setType('departmentid', PARAM_INT);
            $mform->setConstant('departmentid', $USER->open_departmentid);

        } else {

            $mform->addElement('hidden', 'costcenterid', null,
                                array('id' => 'id_costcenterid',
                                    'data-class' => 'organisationselect'
                                )
                            );
            $mform->setType('costcenterid', PARAM_INT);
            $mform->setConstant('costcenterid', $USER->open_costcenterid);

            $mform->addElement('hidden', 'departmentid', $USER->open_departmentid,
                                array('id' => 'id_departmentid',
                                   'data-class' => 'departmentselect'
                                )
                            );
            $mform->setType('departmentid', PARAM_INT);
            $mform->setConstant('departmentid', $USER->open_departmentid);

        }
        if (is_siteadmin($USER->id) || has_capability('local/costcenter:manage_multiorganizations',
            $systemcontext) || has_capability('local/costcenter:manage_ownorganization', $systemcontext)) {
            $departmentselect = [0 => get_string('selectdept', 'local_courses')];
            if ($id || $mformajax['departmentid']) {
                $opendepartment = (int)$mformajax['departmentid'] ? (int)$mformajax['departmentid'] : $existdata->departmentid;
                $departments = $departmentselect + $DB->get_records_menu('local_costcenter',
                                                        array('id' => $opendepartment), '',
                                                        $fields = 'id, fullname'
                                                    );
            } else {
                $opendepartment = 0;
                $departments = $departmentselect;
            }
            $departmentoptions = array(
                'ajax' => 'local_costcenter/form-options-selector',
                'data-contextid' => $systemcontext->id,
                'data-action' => 'costcenter_department_selector',
                'data-options' => json_encode(array('id' => $opendepartment)),
                'class' => 'departmentselect',
                'data-parentclass' => 'organisationselect',
                'data-class' => 'departmentselect',
                'multiple' => false,
            );

            $mform->addElement('autocomplete', 'departmentid',
                                get_string('department', 'local_evaluation'),
                                $departments, $departmentoptions
                            );
            $mform->addHelpButton('departmentid', 'departmentidcourse', $pluginname);
            $mform->setType('departmentid', PARAM_INT);
        }
    }
        /**
    * Description: [org_hierarchy code]
    * @param  [mform]  $mform          [form where the filetr is initiated]
    * @return [void]                  [description]
    */
    public function org_hierarchy($mform, $id, $context, $mformajax, $plugin, $class){
        global $DB, $USER;
        $labelstring =  get_config('local_costcenter');
        if($plugin === 'employee'){
            $plugin = 'user';
            $flag = 'employee';
        }
        if ($plugin === 'user' || $plugin === 'course') {
            $local = '';
        } else if ($plugin === 'program' || $plugin === 'groups' || $plugin === 'timeintervals' || $plugin === 'curriculum' || $plugin === 'location_institutes') {
            $local = 'local_';
        }

        if($plugin == 'groups'){
            $existdata = $DB->get_record($local.$plugin, array('cohortid' => $id));
        } else{
            $existdata = $DB->get_record($local.$plugin, array('id' => $id));
        }

        // Batch_form
        if($existdata->costcenterid){
            $existdata->open_costcenterid = $existdata->costcenterid; 
        }
        if($existdata->departmentid){
            $existdata->open_departmentid = $existdata->departmentid; 
        }
        if($existdata->subdepartmentid){
            $existdata->open_subdepartment = $existdata->subdepartmentid; 
        }
        // program_form
        if($plugin !== 'user'){
            if($existdata->costcenter){
                $existdata->open_costcenterid = $existdata->costcenter;
                unset($existdata->costcenter);
            }
            if($existdata->department){
                $existdata->open_departmentid = $existdata->department; 
                unset($existdata->department);
            }
            if($existdata->subdepartment){
                $existdata->open_subdepartment = $existdata->subdepartment; 
                unset($existdata->subdepartment);
            }
        }
        if($existdata->schoolid){
            $existdata->open_costcenterid = $existdata->schoolid;
        }
        if($plugin == 'location_institutes'){
            if($existdata->costcenter){
                $existdata->open_costcenterid = $existdata->costcenter;
            }
        }
        
        // Role field to the employee form code Starts 
        if($flag === 'employee'){
            $role_select = [null => get_string('select_role', 'local_costcenter')];
            if($id || $mformajax['roleid']){
                    $roleid = (int) $mformajax['roleid'] ? (int)$mformajax['roleid'] : $existdata->roleid;
                    $roles = $role_select + $DB->get_records_menu('role', 
                                        array('id' => $roleid), '',  $fields='id, name'); 
                }
                else{
                    $roleid = 0;
                    $roles = $role_select;
                }
            $roleoptions = array(
                'ajax' => 'local_costcenter/form-options-selector',
                // 'data-depth' => 1,
                'data-selectstring' => get_string('select_role', 'local_costcenter'),
                'data-contextid' => $context->id,
                'data-action' => 'costcenter_role_selector',
                'data-options' => json_encode(array('id' => $roleid)),
                'class' => 'roleselect',
                'data-class' => 'roleselect',
                'multiple' => false,
                'data-pluginclass' => $class,
                'onchange' => '(function(e){ require("local_costcenter/newcostcenter").changeElement(event) })(event)',
            );
             $var = $DB->get_record('role', ['id' => $roleid]);
                if($var->shortname === 'orgadmin'){
                    $orgadminedit = $var->id;
                    $mform->hideif('open_departmentid', 'roleid', 'eq', $orgadminedit);
                    $mform->hideif('open_subdepartment', 'roleid', 'eq', $orgadminedit);
                }

                $mform->addElement('autocomplete', 'roleid', get_string('role', 'local_costcenter'), 
                                    $roles, $roleoptions);
                $mform->setType('roleid', PARAM_INT);
                $mform->addRule('roleid', get_string('errorrole', 'local_costcenter'), 
                                'required', null, 'client');
        }
        // Role field to the employee form code Ends

        if (is_siteadmin($USER->id) || has_capability('local/costcenter:manage_multiorganizations', $context)) {
                $organisation_select = [null => get_string('selectopen_costcenterid', 'local_costcenter', $labelstring)];
                $varorg = $_REQUEST['open_costcenterid'];
                if($id || $mformajax['open_costcenterid'] || $varorg){
                    if($varorg){
                        $open_costcenter = (int) $mformajax['open_costcenterid'] ? (int)$mformajax['open_costcenterid'] : $varorg;
                    } else{
                        $open_costcenter = (int) $mformajax['open_costcenterid'] ? (int)$mformajax['open_costcenterid'] : $existdata->open_costcenterid;
                    }
                    $organisations = $organisation_select + $DB->get_records_menu('local_costcenter', 
                                        array('id' => $open_costcenter), '',  $fields='id, fullname'); 
                }
                else{
                    $open_costcenter = 0;
                    $organisations = $organisation_select;
                }
            $costcenteroptions = array(
                'ajax' => 'local_costcenter/form-options-selector',
                'data-depth' => 1,
                'data-selectstring' => get_string('selectopen_costcenterid', 'local_costcenter', $labelstring),
                'data-contextid' => $context->id,
                'data-action' => 'costcenter_organisation_selector',
                'data-options' => json_encode(array('id' => $open_costcenter)),
                'class' => 'organisationselect',
                'data-class' => 'organisationselect',
                'multiple' => false,
                'data-pluginclass' => $class,
                'onchange' => '(function(e){ require("local_costcenter/newcostcenter").changeElement(event) })(event)',
            );

                $mform->addElement('autocomplete', 'open_costcenterid', get_string('organization', 'local_costcenter', $labelstring->firstlevel), 
                                    $organisations, $costcenteroptions);
                // $mform->addHelpButton('open_costcenterid', 'open_costcenteriduser', 'local_users',$labelstring->firstlevel);
                $mform->setType('open_costcenterid', PARAM_INT);
                $mform->addRule('open_costcenterid', get_string('errororganization', 'local_users',$labelstring->firstlevel), 
                                'required', null, 'client');

            } else if (has_capability('local/costcenter:manage_ownorganization', $context)){

                $mform->addElement('hidden', 'open_costcenterid', null, array('id' => 'id_open_costcenterid', 
                                   'data-class' => 'organisationselect'));
                $mform->setType('open_costcenterid', PARAM_INT);
                $mform->setConstant('open_costcenterid', $USER->open_costcenterid);
            
            } else if (has_capability('local/costcenter:manage_owndepartments', $context)){
            
                $mform->addElement('hidden', 'open_costcenterid', null, array('id' => 'id_open_costcenterid', 
                                   'data-class' => 'organisationselect'));
                $mform->setType('open_costcenterid', PARAM_INT);
                $mform->setConstant('open_costcenterid', $USER->open_costcenterid);

                $mform->addElement('hidden', 'open_departmentid', $USER->open_departmentid, 
                        array('id' => 'id_open_departmentid', 'data-class' => 'departmentselect'));
                $mform->setType('open_departmentid', PARAM_INT);
                $mform->setConstant('open_departmentid', $USER->open_departmentid);
            } else {
                $mform->addElement('hidden', 'open_costcenterid', null, 
                                    array('id' => 'id_open_costcenterid', 'data-class' => 'organisationselect'));
                $mform->setType('open_costcenterid', PARAM_INT);
                $mform->setConstant('open_costcenterid', $USER->open_costcenterid);

                $mform->addElement('hidden', 'open_departmentid', $USER->open_departmentid, 
                        array('id' => 'id_open_departmentid', 'data-class' => 'departmentselect'));
                $mform->setType('open_departmentid', PARAM_INT);
                $mform->setConstant('open_departmentid', $USER->open_departmentid);

                if($USER->open_subdepartment){
                    $mform->addElement('hidden', 'open_subdepartment', null, array('id' => 'id_open_subdepartment'));
                    $mform->setType('open_subdepartment', PARAM_INT);
                    $mform->setConstant('open_subdepartment', $USER->open_subdepartment);
                }
            }
            if(is_siteadmin() || has_capability('local/costcenter:manage_multiorganizations', $context) || has_capability('local/costcenter:manage_ownorganization', $context)){
                // $department_select = [0 => get_string('all')];
                if($class !== 'userclass' || $class !== 'groupclass' || $class !== 'programclass' || $class == 'locationclass'){
                    // $subdepartment_select = [0 => get_string('all')];
                    $department_select = [0 => get_string('all')];
                } 
                if($class === 'userclass' || $class === 'groupclass' || $class === 'programclass'){
                    $department_select = [null => get_string('selectdept', 'local_courses', $labelstring)];
                }
                $vardept = $_REQUEST['open_departmentid'];
                if($id || $mformajax['open_departmentid'] || $vardept){
                    if($vardept){
                        $open_department = (int)$mformajax['open_departmentid'] ? (int)$mformajax['open_departmentid'] : $vardept;
                    } else{

                    $open_department = (int)$mformajax['open_departmentid'] ? (int)$mformajax['open_departmentid'] : $existdata->open_departmentid;
                    }
                    $departments = $department_select + $DB->get_records_menu('local_costcenter', array('id' => $open_department), '',  $fields='id, fullname'); 
                }else{
                    $open_department = 0;
                    $departments = $department_select;
                }

            $departmentoptions = array(
                'ajax' => 'local_costcenter/form-options-selector',
                'data-depth' => 2,
                'data-selectstring' => get_string('selectdept', 'local_courses', $labelstring),
                'data-contextid' => $context->id,
                'data-action' => 'costcenter_department_selector',
                'data-options' => json_encode(array('id' => $open_department)),
                'class' => 'departmentselect',
                'data-parentclass' => 'organisationselect',
                'data-class' => 'departmentselect',
                'multiple' => false,
                'data-pluginclass' => $class,
                'onchange' => '(function(e){ require("local_costcenter/newcostcenter").changeElement(event) })(event)',
            );
                // if($class !== 'userclass' || $class !== 'groupclass'){
                //     // $subdepartment_select = [0 => get_string('all')];
                //     $department_select = [0 => get_string('all')];
                // } 
                // if($class === 'userclass' || $class === 'groupclass'){
                //     $department_select = [null => get_string('selectdept', 'local_courses', $labelstring)];
                // }
                $mform->addElement('autocomplete', 'open_departmentid'/*, get_string('department', 'local_users'),*/ ,$labelstring->secondlevel,$departments, $departmentoptions);
                if($class === 'userclass' || $class === 'groupclass' || $class === 'programclass'){
                    $department_select = [null => get_string('selectdept', 'local_courses', $labelstring)];
                    $mform->addRule('open_departmentid', get_string('errordept', 'local_users',$labelstring->secondlevel), 'required', null, 'client');
                }
                // $mform->addHelpButton('open_departmentid', 'open_departmentiduser', 'local_users');
                $mform->setType('open_departmentid', PARAM_INT);
            }
            if(is_siteadmin($USER->id) || 
                has_capability('local/costcenter:manage_multiorganizations', $context) || 
                has_capability('local/costcenter:manage_ownorganization', $context) || 
                has_capability('local/costcenter:manage_owndepartments', $context)){
                // if($class !== 'userclass' || $class !== 'groupclass'){
                    $subdepartment_select = [0 => get_string('all')];
                // } 
                /*if($class === 'userclass' || $class === 'groupclass'){
                    $subdepartment_select = [null => get_string('selectsubdept', 'local_courses', $labelstring)];
                }*/
                $varsub = $_REQUEST['open_subdepartment'];
                if($id || $mformajax['open_subdepartment'] || $varsub){
                    if($varsub){
                        $open_subdepartment = (int)$mformajax['open_subdepartment'] ? (int)$mformajax['open_subdepartment'] : $varsub;
                    } else {
                        $open_subdepartment = (int)$mformajax['open_subdepartment'] ? (int)$mformajax['open_subdepartment'] : $existdata->open_subdepartment;
                    }
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
                    'data-contextid' => $context->id,
                    'data-action' => 'costcenter_subdepartment_selector',
                    'data-options' => json_encode(array('id' => $open_subdepartment)),
                    'class' => 'subdepartmentselect',
                    'data-parentclass' => 'departmentselect',
                    'data-class' => 'subdepartmentselect',
                    'multiple' => false,
                    'data-pluginclass' => $class,
                    'onchange' => '(function(e){ require("local_costcenter/newcostcenter").changeElement(event) })(event)',
                );
                if($USER->open_departmentid) {
                    $dept_or_col = $DB->get_field('local_costcenter','dept_or_col',['id'=> $USER->open_departmentid]);
                }
                $mform->addElement('autocomplete', 'open_subdepartment', /*get_string('sub_departments', 'local_courses'),*/
                $labelstring->thirdlevel, $subdepartments, $subdepartmentoptions);
                // $mform->addHelpButton('open_subdepartment', 'open_subdepartmentuser', 'local_users');
                $mform->setType('open_subdepartment', PARAM_INT);
            }
    }
}
