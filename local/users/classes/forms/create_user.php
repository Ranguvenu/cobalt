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

namespace local_users\forms;
defined('MOODLE_INTERNAL') || die;
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/completionlib.php');
require_once($CFG->dirroot . '/local/costcenter/lib.php');
use moodleform;
use context_system;
use costcenter;
use events;
use context_user;
use local_users\functions\userlibfunctions as userlib;
use local_costcenter\functions\userlibfunctions as costcenterlib;

class create_user extends moodleform {
    public $formstatus;
    public function __construct($action = null, $customdata = null, $method = 'post', $target = '', $attributes = null, $editable = true, $formdata = null) {

        $this->formstatus = array(
            'generaldetails' => get_string('generaldetails', 'local_users'), 
            'contactdetails' => get_string('contactdetails', 'local_users'),
            );
        parent::__construct($action, $customdata, $method, $target, $attributes, $editable, $formdata);
    }
    public function definition() {
        global $USER, $CFG, $DB, $PAGE;
        $systemcontext = context_system::instance();
        $costcenter = new costcenter();
        $mform = $this->_form;
        $labelstring = get_config('local_costcenter');
        $context = \context_system::instance();
        $mformajax    =& $this->_ajaxformdata;
        $form_status = $this->_customdata['form_status'];
        $id = $this->_customdata['id'];
        $editoroptions = $this->_customdata['editoroptions'];
        $filemanageroptions = $this->_customdata['filemanageroptions'];
        $admin = $this->_customdata['admin'];
        $usererc = $DB->get_record('user', array('id' => $id));

        if($form_status == 0){
            // if($id<=0){
            
            // }
            $user_exist = $DB->record_exists('local_program_users', ['userid' => $id]);
            if ($id > 0 && $user_exist) {
                if (is_siteadmin($USER->id)
                    || has_capability('local/costcenter:manage_multiorganizations', $context)
                    /*|| has_capability('local/costcenter:manage_ownorganization', $context)
                    || has_capability('local/costcenter:manage_owndepartments', $context)*/) {
                    $costcentersql = "SELECT lc.fullname FROM {local_costcenter} lc 
                                        JOIN {user} u ON lc.id = u.open_costcenterid
                                        WHERE u.id = :id";
                    $costcenter = $DB->get_field_sql($costcentersql, array('id' => $id));
                    $mform->addElement('static', 'costcentername', get_string('organization', 'local_costcenter', $labelstring->firstlevel), $costcenter);
                } 
                    $mform->addElement('hidden', 'open_costcenterid', $usererc->open_costcenterid);
                    $mform->setDefault('open_costcenterid', $usererc->open_costcenterid);

            // if ($id > 0) {
                // if($dept_or_col == 0){
                if(is_siteadmin() || has_capability('local/costcenter:manage_multiorganizations', $context) || has_capability('local/costcenter:manage_ownorganization', $context)){
                        $deptname = $DB->get_field('local_costcenter', 'fullname', array('id' => $usererc->open_departmentid, 'depth' => 2));
                        if ($deptname) {
                            $mform->addElement('static', 'departmentname', /*get_string('department', 'local_users', $labelstring)*/$labelstring->secondlevel, $deptname);
                        } else {
                            $mform->addElement('static', 'departmentname', /*get_string('department', 'local_users', $labelstring)*/$labelstring->secondlevel, get_string('allclasses', 'local_program'));
                        }
                        $mform->addElement('hidden', 'open_departmentid', $usererc->open_departmentid);
                        $mform->setDefault('open_departmentid', $usererc->open_departmentid);
                }

                // if ($id > 0) {
                if(is_siteadmin($USER->id) || 
                    has_capability('local/costcenter:manage_multiorganizations', $context) || 
                    has_capability('local/costcenter:manage_ownorganization', $context) || 
                    has_capability('local/costcenter:manage_owndepartments', $context)){
                    $subdeptname = $DB->get_field('local_costcenter', 'fullname', array('id' => $usererc->open_subdepartment, 'depth' => 3));
                    if ($subdeptname) {
                        $mform->addElement('static', 'subdepartmentname', /*get_string('subdepartment', 'local_users', $labelstring)*/$labelstring->thirdlevel, $subdeptname);
                    } else {
                        $mform->addElement('static', 'subdepartmentname', /*get_string('subdepartment', 'local_users', $labelstring)*/$labelstring->thirdlevel, get_string('allclasses', 'local_program'));
                    }
                    $mform->addElement('hidden', 'open_subdepartment', $usererc->open_subdepartment);
                    $mform->setDefault('open_subdepartment', $usererc->open_subdepartment);
                }
            } else{
                $getdepartmentelements = costcenterlib::org_hierarchy($mform, $id, $context, $mformajax, 'user', 'userclass');
            }

        if(is_siteadmin($USER->id) || 
                has_capability('local/costcenter:manage_multiorganizations', $systemcontext) || 
                has_capability('local/costcenter:manage_ownorganization', $systemcontext) || 
                has_capability('local/costcenter:manage_owndepartments', $systemcontext)){

            $mform->addElement('text', 'rollno', get_string('roll', 'local_users'));
            $mform->addRule('rollno', get_string('error_rollno','local_users'), 'required', null, 'client');
            // $mform->addRule('rollno', get_string('error_rollno', 'local_users'), 'rollno', null, 'client');
            $mform->setType('rollno', PARAM_RAW);    
        } else{
                $mform->addElement('hidden', 'rollno', null, array('id' => 'id_rollno', 
                                   'data-class' => 'organisationselect'));
                $mform->setType('rollno', PARAM_INT);
                $mform->setConstant('rollno', $USER->rollno);
        }
        $mform->addElement('text', 'email', get_string('email', 'local_users'), $readonly);
        $mform->addRule('email', get_string('erroremail','local_users'), 'required', null, 'client');
        $mform->addRule('email', get_string('emailerror', 'local_users'), 'email', null, 'client');
        $mform->setType('email', PARAM_RAW);

		$auths = \core_component::get_plugin_list('auth');
        $enabled = get_string('pluginenabled', 'core_plugin');
        $disabled = get_string('plugindisabled', 'core_plugin');
        $authoptions = array();
        $cannotchangepass = array();
        $cannotchangeusername = array();
        foreach ($auths as $auth => $unused) {
        	if($auth == 'nologin')
        		continue;
            $authinst = get_auth_plugin($auth);

            if (!$authinst->is_internal()) {
                $cannotchangeusername[] = $auth;
            }

            $passwordurl = $authinst->change_password_url();
            if (!($authinst->can_change_password() && empty($passwordurl))) {
                if ($userid < 1 and $authinst->is_internal()) {
                    // This is unlikely but we can not create account without password
                    // when plugin uses passwords, we need to set it initially at least.
                } else {
                    $cannotchangepass[] = $auth;
                }
            }
            if (is_enabled_auth($auth)) {

                $authoptions[$auth] = get_string('pluginname', "auth_{$auth}");
            }
        }
		$mform->addElement('passwordunmask', 'password', get_string('password'), 'size="20"');
		$mform->addHelpButton('password', 'newpassword');
		$mform->setType('password', PARAM_RAW);
		$mform->hideIf('password', 'createpassword', 'eq', 1);
		if(is_siteadmin($USER->id) || 
                has_capability('local/costcenter:manage_multiorganizations', $systemcontext) || 
                has_capability('local/costcenter:manage_ownorganization', $systemcontext) || 
                has_capability('local/costcenter:manage_owndepartments', $systemcontext)){

    		$gender=[];
    		$gender[] = $mform->createElement('advcheckbox', 'preference_auth_forcepasswordchange', get_string('forcepasswordchange'));
    		$gender[] = $mform->createElement('advcheckbox', 'createpassword', get_string('createpassword', 'auth'));
    			$mform->addGroup($gender,  'advcheckbox', '', array(' '), false);
        }	

			$mform->addElement('text', 'firstname', get_string('firstname', 'local_users'), $readonly);
	        $mform->addRule('firstname', get_string('errorfirstname', 'local_users'), 'required', null, 'client');
	        $mform->setType('firstname', PARAM_RAW);

	        $mform->addElement('text', 'lastname', get_string('lastname', 'local_users'), $readonly);
	        $mform->addRule('lastname', get_string('errorlastname', 'local_users'), 'required', null, 'client');
	        $mform->setType('lastname', PARAM_RAW);

		    $mform->addElement('text', 'city', get_string('city','local_users'),'maxlength="120" size="20"');
            $mform->addHelpButton('city', 'citydesc', 'local_employees');
	        $mform->setType('city', PARAM_RAW);

            $purpose = user_edit_map_field_purpose($user->id, 'country');
            $choices = get_string_manager()->get_list_of_countries();
            $choices = array('' => get_string('selectacountry') . '...') + $choices;
            $mform->addElement('select', 'country', get_string('selectacountry'),  $choices, $purpose);

            if (!empty($CFG->country)) {
                $mform->setDefault('country', core_user::get_property_default('country'));
            }

            $mform->addElement('text', 'phone1', get_string('phone1'), 'maxlength="10" size="10"');
            $mform->addRule('phone1', get_string('errorphone1', 'local_users'), 'required', null, 'client');
            $mform->setType('phone1', PARAM_RAW);
            $mform->setForceLtr('phone1');

            $mform->addElement('textarea', 'address', get_string('address','local_users'), 'maxlength="225" size="10"');
            $mform->addHelpButton('address', 'addressdesc', 'local_employees');
            $mform->setType('address', PARAM_RAW);
		    
		}//end of if($form_status = 0) condition.
		else if($form_status ==1){
	        if (isset($CFG->forcetimezone) and $CFG->forcetimezone != 99) {
		        $choices = \core_date::get_list_of_timezones($CFG->forcetimezone);
		        $mform->addElement('static', 'forcedtimezone', get_string('timezone'), $choices[$CFG->forcetimezone]);
		        $mform->addElement('hidden', 'timezone');
		        $mform->setType('timezone', \core_user::get_property_type('timezone'));
		    } else {
		    	$userrecord = \core_user::get_user($id);
		        $choices = \core_date::get_list_of_timezones($userrecord->timezone, true);
		        $mform->addElement('select', 'timezone', get_string('timezone'), $choices);
		    }
	       	
	        $mform->addElement('static', 'currentpicture', get_string('currentpicture'));
	        $mform->addElement('checkbox', 'deletepicture', get_string('delete'));
	        $mform->setDefault('deletepicture', 0);
	        $mform->addElement('filepicker', 'imagefile', get_string('profile_picture','local_users'), null, array('accepted_types' => array('.jpg', '.jpeg', '.png')));
	        $mform->addHelpButton('imagefile', 'profile_picture','local_users');

	        $mform->addElement('editor', 'description', get_string('aboutmyself', 'local_users'), null, $editoroptions);
	        $mform->setType('description', PARAM_RAW);
	        $mform->addHelpButton('description', 'descriptionuser', 'local_users');
		}
		// end of form status = 2 condition
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->setDefault('id',  $id);
        $mform->addElement('hidden', 'form_status');
        $mform->setType('form_status', PARAM_INT);
        $mform->setDefault('form_status',  $form_status);
        $mform->disable_form_change_checker();

    }

    public function definition_after_data() {
        global $USER, $CFG, $DB, $OUTPUT;
        $mform = & $this->_form;
        $form_status = $this->_customdata['form_status'];
        if ($userid = $mform->getElementValue('id')) {
            $user = $DB->get_record('user', array('id' => $userid));
        } else {
            $user = false;
        }
        // print picture
        if (empty($USER->newadminuser)) {
            if ($user) {
                $context = context_user::instance($user->id, MUST_EXIST);
                $fs = get_file_storage();
                $hasuploadedpicture = ($fs->file_exists($context->id, 'user', 'icon', 0, '/', 'f2.png') || $fs->file_exists($context->id, 'user', 'icon', 0, '/', 'f2.jpg'));

                if (!empty($user->picture) && $hasuploadedpicture) {
                    $imagevalue = $OUTPUT->user_picture($user, array('courseid' => SITEID, 'size' => 64,'link' => false));
                } else {
                    $imagevalue = get_string('none');
                }
            } else {
                $imagevalue = get_string('none');
            }
            if($form_status == 1){
	            $imageelement = $mform->getElement('currentpicture');
	            $imageelement->setValue($imagevalue);
			}
            if ($user && $mform->elementExists('deletepicture') && !$hasuploadedpicture) {
                $mform->removeElement('deletepicture');
            }
        }
    }

   public function validation($data, $files) {
        $errors = array();
        global $DB, $CFG;
        $labelstring = get_config('local_costcenter');
        $rollno = $data['rollno'];
		$sub_data=data_submitted();
		$errors = parent::validation($data, $files);
        $email = $data['email'];
        $employeeid = $data['open_employeeid'];
        $id = $data['id'];
        $form_status = $data['form_status'];
        if($form_status == 0){// as these fields are in only form part 1(form_status=0)
        	$firstname = $data['firstname'];
        	$lastname = $data['lastname'];
            if (empty($data['open_costcenterid'])) {
                $errors['open_costcenterid'] = get_string('errororganization', 'local_users', $labelstring->firstlevel);
            
            } 
        	if(empty(trim($firstname))){
        		$errors['firstname'] = get_string('valfirstnamerequired','local_users');
        	}
        	if(empty(trim($lastname))){
        		$errors['lastname'] = get_string('vallastnamerequired','local_users');
        	}
	        if(get_config('core', 'allowaccountssameemail') == 0){
			    if (!empty($data['email']) && ($user = $DB->get_record('user', array('email' => $data['email']), '*', IGNORE_MULTIPLE))) {
		            if (empty($data['id']) || $user->id != $data['id']) {
		                $errors['email'] = get_string('emailexists', 'local_users');
		            }
		        }
	    	}
            //  Roll number validation 
            // if(!empty($rollno)){
            //     $rollnoexists = $DB->record_exists('user',array('rollno' => $rollno));
            //     if($rollnoexists){
            //         $errors['rollno'] = get_string('rollnoexists', 'local_users');
            //     }
            // }
            if (!empty($data['rollno']) && ($user = $DB->get_record('user', array('rollno' => $data['rollno'], 'open_costcenterid' => $data['open_costcenterid']), '*', IGNORE_MULTIPLE))) {
                    if (empty($data['id']) || $user->id != $data['id']) {
                        $errors['rollno'] = get_string('rollnoexists', 'local_users');
                    }
                }

	    	if (!empty($data['email']) && !validate_email($data['email'])) {
	    		$errors['email'] = get_string('emailerror', 'local_users');
	    	}
	         
	        if(!$data['createpassword']){
		        if (!empty($data['password']) ) {
	                $errmsg = ''; // Prevent eclipse warning.
	                if (!check_password_policy($data['password'], $errmsg)) {
	                    $errors['password'] = $errmsg;
	                }
	            }else if(empty($data['id']) && $data['createpassword'] != 1 ){
	            	$errors['password'] = get_string('passwordrequired', 'local_users');
	            }
	        } 

            if (!empty($data['phone1'])) {
                if (strlen($data['phone1']) !== 10) {
                    $errors['phone1'] = get_string('errormobiledigits', 'local_admissions');
                }
                if (!is_numeric($data['phone1'])) {
                    $errors['phone1'] = get_string('acceptedtype', 'local_admissions');
                }
                if (!preg_match('/^[a-z0-9\s\_]+$/i', $data['phone1'])) {
                    $errors['phone1'] = get_string('specialcharactersnotallwoed','local_employees');
                }
            }
            if(empty(trim($rollno))){
                $errors['rollno'] = get_string('error_rollno','local_users');
            }


            if($id > 0){
                // $user_exist = $DB->record_exists('local_program_users', ['userid' => $id]);
                $user_exist = $DB->get_fieldset_sql("SELECT c.id as courseid
                FROM {user} u
                JOIN {role_assignments} ra ON ra.userid = u.id
                JOIN {role} r ON r.id=ra.roleid AND r.shortname = 'student'
                JOIN {context} ctx ON ctx.id = ra.contextid
                JOIN {course} c ON c.id = ctx.instanceid
                -- AND c.open_identifiedas IN (3,6) 
                WHERE u.id = $id");
            }
            if(!$user_exist){
                if(empty($data['open_departmentid'])){
                    $errors['open_departmentid'] = get_string('deptnameshouldselect', 'local_users', $labelstring);
                }
                if(!empty($data['open_costcenterid']) && !empty($data['open_departmentid'])){
                    $deptid = $data['open_departmentid'];
                    $costcenterid = $data['open_costcenterid'];
                    $deptid_subdeptid_exists = $DB->record_exists('local_costcenter', ['id' => $deptid, 'parentid' => $costcenterid]);
                    if(!$deptid_subdeptid_exists){
                        $errors['open_departmentid'] = get_string('deptnotexistsincostcenter', 'local_employees', $labelstring);
                        $errors['open_subdepartment'] = get_string('subdeptnotexistsincostcenter', 'local_employees', $labelstring);
                    }
                }

            }
	    }



        return $errors;
    }
}

