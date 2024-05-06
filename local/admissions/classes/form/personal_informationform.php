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

namespace local_admissions\form;
defined('MOODLE_INTERNAL') || die;
require_once($CFG->libdir . '/completionlib.php');
require_once($CFG->dirroot . '/local/costcenter/lib.php');
use moodleform;
use context_system;
use costcenter;
use events;
use context_user;
use core_user;
use local_admissions\local\lib as lib;

class personal_informationform extends moodleform {

    public function definition() {
        global $USER, $CFG, $DB, $PAGE;

        $mform = $this->_form;
        $context = context_system::instance();

        $id = $this->_customdata['id'];
        $userid = isset($this->_customdata['userid']);
        $programid = $this->_customdata['programid'];
        $status = isset($this->_customdata['status']);
        $batchid = $this->_customdata['batchid'];
        $format = $this->_customdata['format'];
        $admintoapply = $this->_customdata['admintoapply'];

        $mform->addElement('html', '<div class="row"><div class="col-md-8">');
        $mform->addElement('hidden', 'id', $id);
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'userid', $userid);
        $mform->setType('userid', PARAM_INT);

        $mform->addElement('hidden', 'programid', $programid);
        $mform->setType('programid', PARAM_INT);

        $mform->addElement('hidden', 'status', $status);
        $mform->setType('status', PARAM_INT);

        $mform->addElement('hidden', 'batchid', $batchid);
        $mform->setType('batchid', PARAM_INT);

            // Personal Info.

            $mform->addElement('html', '<div class= "form">');

            // $mform->addElement('html', '<div class="heading">'.get_string('personalinformation', 'local_admissions').'</div>');
            $mform->addElement('html', '<div class= "fields">');

            $mform->addElement('text', 'firstname', get_string('firstname', 'local_admissions'));
            $mform->addRule('firstname', get_string('errorfirstname', 'local_admissions'), 'required', null, 'client');
            $mform->setType('firstname', PARAM_RAW);

            $mform->addElement('text', 'lastname', get_string('lastname', 'local_admissions'));
            $mform->addRule('lastname', get_string('errorlastname', 'local_admissions'), 'required', null, 'client');
            $mform->setType('lastname', PARAM_RAW);

            $mform->addElement('text', 'fathername', get_string('fathername', 'local_admissions'));
            $mform->addRule('fathername', get_string('errorfathername', 'local_admissions'), 'required', null, 'client');
            $mform->setType('fathername', PARAM_RAW);

            $mform->addElement('text', 'mothername', get_string('mothername', 'local_admissions'));
            $mform->addRule('mothername', get_string('errormothername', 'local_admissions'), 'required', null, 'client');
            $mform->setType('mothername', PARAM_RAW);

            $genderlist = array('Male', 'Female', 'Others');
            $genderlist = array(null => get_string('selectgender', 'local_admissions')) + $genderlist;
            $mform->addElement('select', 'gender', get_string('gender', 'local_admissions'), $genderlist);
            $mform->addRule('gender', get_string('errorgender','local_admissions'), 'required', null);
            $mform->setType('gender', PARAM_TEXT);

            $mform->addElement('date_selector', 'dob', get_string('dob', 'local_admissions'));
            $mform->addRule('dob', get_string('missingstartdate','local_admissions'), 'required', null);
            $mform->setType('dob', PARAM_RAW);

            $mform->addElement('text', 'birthplace', get_string('placeofbirth', 'local_admissions'));
            $mform->addRule('birthplace', get_string('errorplaceofbirth', 'local_admissions'), 'required', null, 'client');
            $mform->setType('birthplace', PARAM_RAW);

            $maritalist = array('Married', 'Unmarried');
            $maritalist = array(null => get_string('selectstatus', 'local_admissions')) + $maritalist;
            $mform->addElement('select', 'maritalstatus', get_string('maritalstatus', 'local_admissions'), $maritalist);
            $mform->addRule('maritalstatus', get_string('errormaritalstatus','local_admissions'), 'required', null);
            $mform->setType('maritalstatus', PARAM_TEXT);

            $mform->addElement('text', 'nationality', get_string('nationality', 'local_admissions'));
            $mform->addRule('nationality', get_string('errornationality', 'local_admissions'), 'required', null, 'client');
            $mform->setType('nationality', PARAM_RAW);

            $mform->addElement('text', 'religion', get_string('religion', 'local_admissions'));
            $mform->addRule('religion', get_string('errorreligion', 'local_admissions'), 'required', null, 'client');
            $mform->setType('religion', PARAM_RAW);

            $mform->addElement('text', 'occupation', get_string('occupation', 'local_admissions'));
            $mform->addRule('occupation', get_string('erroroccupation', 'local_admissions'), 'required', null, 'client');
            $mform->setType('occupation', PARAM_RAW);

            $auths = \core_component::get_plugin_list('auth');
            $enabled = get_string('pluginenabled', 'core_plugin');
            $disabled = get_string('plugindisabled', 'core_plugin');
            $authoptions = array();
            $cannotchangepass = array();
            $cannotchangeusername = array();
            $mform->addElement('html', '</div>');
            $mform->addElement('html', '</div>');

            $mform->addElement('html', '<div class="row mb-2 mt-4">');
            $mform->addElement('html', '<div class="col-md-3"> </div>');
 
            if ($id > 0) {
                $mform->addElement('html', '<div class="buttons-container col-md-9 btncontainer">');
                $mform->addElement('html', '<input type="submit" class="btn btn-primary mr-4" name="submitbutton" id="id_submitbutton" value="Update" data-initial-value="Update">');
                $mform->addElement('html', '<a class="btn btn-primary cont-nxt-btn mr-4" href='.$CFG->wwwroot.'/local/admissions/contact_information.php?id='.$id.'>Next</a>');
                $mform->addElement('html', '<a class="btn btn-primary cont-nxt-btn " href='.$CFG->wwwroot.'/local/admissions/view.php>Cancel</a>');
                $mform->addElement('html', '</div>');
            } else {
                // $mform->addElement('html', '<div class="buttons-container col-md-6">');
                    // $this->add_action_buttons(true, get_string('save_continue', 'local_admissions'));
                // $mform->addElement('html', '</div>');

                $buttonarray = array();
                if ($format == 'card') {
                    $returnurl = $CFG->wwwroot.'/local/admissions/programs.php?format='.$format;
                } else if ($format == 'list') {
                    $returnurl = $CFG->wwwroot.'/local/admissions/index.php?format='.$format;
                } else {
                    $returnurl = $CFG->wwwroot.'/local/admissions/programs.php?format='.$format;
                }
                if ($admintoapply > 0) {
                    $returnurl = $CFG->wwwroot.'/local/admissions/view.php';
                }
                $cancel = '<a id="id_cancel" name="cancel" href='.$returnurl.'>Cancel</a>';
                $buttonarray[] = &$mform->createElement('submit', 'save', get_string('saveandcontinue', 'local_admissions'));
                $buttonarray[] = &$mform->createElement('button', 'cancel', $cancel);
                $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
                $mform->closeHeaderBefore('buttonar');
            }
            $mform->addElement('html', '</div>');
            $mform->addElement('html', '</div>');
            $mform->addElement('html', '</div></div>');
        
    }
    public function validation($data, $files) {
        $errors = array();
        global $DB, $CFG;
        $sub_data = data_submitted();
        $errors = parent::validation($data, $files);
        $email = $data['email'];
        $employeeid = $data['open_employeeid'];
        $id = $data['id'];
        $form_status = $data['form_status'];
        $programid = $data['programid'];
        if($form_status == 0){ // as these fields are in only form part 1(form_status=0).
            $firstname = $data['firstname'];
            $lastname = $data['lastname'];
            if(empty(trim($firstname))){
                $errors['firstname'] = get_string('valfirstnamerequired','local_admissions');
            }
            if(empty(trim($lastname))){
                $errors['lastname'] = get_string('vallastnamerequired','local_admissions');
            }
            if(get_config('core', 'allowaccountssameemail') == 0){
                if (!empty($data['email']) && ($user = $DB->get_record('user', array('email' => $data['email']), '*', IGNORE_MULTIPLE))) {
                    if (empty($data['id']) || $user->id != $data['id']) {
                        $errors['email'] = get_string('emailexists', 'local_admissions');
                    }
                }
            }
            if ($id > 0) {
            } else {
                if (!empty($data['email']) && !validate_email($data['email'])) {
                    $errors['email'] = get_string('emailerror', 'local_admissions');
                }
                if (($data['email']) && !validate_email($data['email'])) {

                }
            }
        }
        return $errors;
    }
}
