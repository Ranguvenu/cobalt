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

class contact_informationform extends moodleform
{

    public function definition()
    {
        global $USER, $CFG, $DB, $PAGE;

        $mform = $this->_form;
        $context = context_system::instance();
        $id = $this->_customdata['id'];
        $mform->addElement('html', '<div class="row"><div class="col-md-8">');
        $mform->addElement('hidden', 'id', $id);
        $mform->setType('id', PARAM_INT);

        // Contact Info.

        $mform->addElement('html', '<div class= "form">');
        $mform->addElement('html', '<div class="heading">' . get_string('contactdetails', 'local_admissions') . '</div>');

        $mform->addElement('html', '<div class= "fields">');

        $contactDetails = $DB->get_record('local_users', array('id' => $id));
        if ($id > 0 && $contactDetails->email !== null) {
            $mform->disabledIf('email', 'id', 'req', 0);
            $mform->addElement('text', 'email', get_string('email', 'local_admissions'));
        } else {
            $mform->addElement('text', 'email', get_string('email', 'local_admissions'));
            $mform->addRule('email', get_string('erroremail', 'local_admissions'), 'required', null, 'client');
            $mform->addRule('email', get_string('emailerror', 'local_admissions'), 'email', null, 'client');
            $mform->setType('email', PARAM_RAW);
        }

        $mform->addElement('text', 'mobile', get_string('mobile', 'local_admissions'), ['maxlength' => 10, 'placeholder' => 'ex.1234567890']);
        $mform->addRule('mobile', get_string('errormobile', 'local_admissions'), 'required', null, 'client');
        $mform->setType('mobile', PARAM_RAW);

        $mform->addElement('text', 'altermobileno', get_string('altermobileno', 'local_admissions'), ['maxlength' => 10, 'placeholder' => 'ex.1234567890']);
        $mform->addRule('altermobileno', get_string('erroraltermobileno', 'local_admissions'), 'required', null, 'client');
        $mform->setType('altermobileno', PARAM_RAW);

        $mform->addElement('html', '<div class="addressheading">' . get_string('current_address', 'local_admissions') . '</div>');

        $mform->addElement('text', 'addressline1', get_string('addressline1', 'local_admissions'));
        $mform->addRule('addressline1', get_string('erroraddressline1', 'local_admissions'), 'required', null, 'client');
        $mform->setType('addressline1', PARAM_RAW);

        $mform->addElement('text', 'addressline2', get_string('addressline2', 'local_admissions'));
        // $mform->addRule('addressline2', get_string('erroraddressline1','local_admissions'), 'required', null, 'client');
        $mform->setType('addressline2', PARAM_RAW);

        $mform->addElement('text', 'city', get_string('city', 'local_admissions'), 'maxlength="120" size="21"');
        $mform->addRule('city', get_string('errorcity', 'local_admissions'), 'required', null, 'client');
        $mform->setType('city', PARAM_TEXT);
        if (!empty($CFG->defaultcity)) {
            $mform->setDefault('city', $CFG->defaultcity);
        }

        $mform->addElement('text', 'state', get_string('state', 'local_admissions'));
        $mform->addRule('state', get_string('errorstate', 'local_admissions'), 'required', null, 'client');
        $mform->setType('state', PARAM_RAW);

        $purpose = user_edit_map_field_purpose($userid, 'country', 'local_admissions');
        $choices = get_string_manager()->get_list_of_countries();
        $choices = array('' => get_string('selectacountry', 'local_admissions')) + $choices;
        $mform->addElement('select', 'country', get_string('selectacountry', 'local_admissions'), $choices, $purpose);
        $mform->addRule('country', get_string('errorcountry', 'local_admissions'), 'required', null, 'client');
        if (!empty($CFG->country)) {
            $mform->setDefault('country', core_user::get_property_default('country'));
        }

        $mform->addElement('text', 'pincode', get_string('pincode', 'local_admissions'), ['maxlength' => 6, 'placeholder' => 'ex.123456']);
        $mform->addRule('pincode', get_string('errorpincode', 'local_admissions'), 'required', null, 'client');
        $mform->setType('pincode', PARAM_RAW);

        // Present Address.

        $mform->addElement('html', '<div class="d-flex per_address justify-content-between"><div class="addressheading">' . get_string('permanent_address', 'local_admissions') . '</div>');

        $mform->addElement('advcheckbox', 'copytext', get_string('copytext', 'local_admissions'), '', array('group' => 1), array(0, 1));
        $mform->addElement('html', '</div>');
        $mform->addElement('text', 'paddressline1', get_string('addressline1', 'local_admissions'));
        $mform->addRule('paddressline1', get_string('erroraddressline1', 'local_admissions'), 'required', null, 'client');
        $mform->setType('paddressline1', PARAM_RAW);

        $mform->addElement('text', 'paddressline2', get_string('addressline2', 'local_admissions'));
        // $mform->addRule('paddressline2', get_string('erroraddressline1','local_admissions'), 'required', null, 'client');
        $mform->setType('paddressline2', PARAM_RAW);

        $mform->addElement('text', 'pcity', get_string('city', 'local_admissions'), 'maxlength="120" size="21"');
        $mform->addRule('pcity', get_string('errorcity', 'local_admissions'), 'required', null, 'client');
        $mform->setType('pcity', PARAM_TEXT);
        if (!empty($CFG->defaultcity)) {
            $mform->setDefault('pcity', $CFG->defaultcity);
        }

        $mform->addElement('text', 'pstate', get_string('state', 'local_admissions'));
        $mform->addRule('pstate', get_string('errorstate', 'local_admissions'), 'required', null, 'client');
        $mform->setType('pstate', PARAM_RAW);

        $purpose = user_edit_map_field_purpose($userid, 'pcountry', 'local_admissions');
        $choices = get_string_manager()->get_list_of_countries();
        $choices = array('' => get_string('selectacountry', 'local_admissions')) + $choices;
        $mform->addElement('select', 'pcountry', get_string('selectacountry', 'local_admissions'), $choices, $purpose);
        $mform->addRule('pcountry', get_string('errorcountry', 'local_admissions'), 'required', null, 'client');
        if (!empty($CFG->country)) {
            $mform->setDefault('pcountry', core_user::get_property_default('pcountry'));
        }

        $mform->addElement('text', 'ppincode', get_string('pincode', 'local_admissions'), ['maxlength' => 6, 'placeholder' => 'ex.123456']);
        $mform->addRule('ppincode', get_string('errorpincode', 'local_admissions'), 'required', null, 'client');
        $mform->setType('ppincode', PARAM_RAW);

        $mform->addElement('html', '</div>');
        $mform->addElement('html', '</div>');

        $mform->addElement('html', '<div class="row mb-2 mt-4">');
        $mform->addElement('html', '<div class="col-md-3"> </div>');

        if ($id > 0 && $contactDetails->email !== null) {
            $mform->addElement('html', '<div class="buttons-container col-md-9 btncontainer">');
            $mform->addElement('html', '<input type="submit" class="btn btn-primary mr-4" name="submitbutton" id="id_submitbutton" value="Update" data-initial-value="Update">');
            $mform->addElement('html', '<a class="btn btn-primary prev-per-btn mr-4" href=' . $CFG->wwwroot . '/local/admissions/personal_information.php?id=' . $id . '>Previous</a>');
            $mform->addElement('html', '<a class="btn btn-primary next-edu-btn mr-4 " href=' . $CFG->wwwroot . '/local/admissions/educational_information.php?id=' . $id . '>Next</a>');
            $mform->addElement('html', '<a class="btn btn-primary cont-nxt-btn mr-4" href=' . $CFG->wwwroot . '/local/admissions/view.php>Cancel</a>');
            $mform->addElement('html', '</div>');
        } else {
            $mform->addElement('html', '<div class="buttons-container col-md-9 con-mar">');
            $mform->addElement('html', '<input type="submit" class="btn btn-primary mr-4" name="submitbutton" id="id_submitbutton" value="Save and continue" data-initial-value="Save and continue">');
            $mform->addElement('html', '<a class="btn btn-primary prev-per-btn mr-4" href=' . $CFG->wwwroot . '/local/admissions/personal_information.php?id=' . $id . '>Previous</a>');
            $mform->addElement('html', '<a class="btn btn-primary cont-nxt-btn" href=' . $CFG->wwwroot . '/local/admissions/view.php>Cancel</a>');
            $mform->addElement('html', '</div>');
        }

        $mform->addElement('html', '</div>');
        $mform->addElement('html', '</div>');
        $mform->addElement('html', '</div></div>');
    }
    public function validation($data, $files)
    {
        $errors = array();
        global $DB, $CFG;
        $sub_data = data_submitted();
        $errors = parent::validation($data, $files);
        $email = $data['email'];
        // $employeeid = $data['open_employeeid'];
        $id = $data['id'];
        if (!empty($data['mobile'])) {
            if (strlen($data['mobile']) !== 10) {
                $errors['mobile'] = get_string('errormobiledigits', 'local_admissions');
            }
            if (!is_numeric($data['mobile'])) {
                $errors['mobile'] = get_string('acceptedtype', 'local_admissions');
            }
        }
        if (!empty($data['altermobileno'])) {
            if (strlen($data['altermobileno']) !== 10) {
                $errors['altermobileno'] = get_string('errormobiledigits', 'local_admissions');
            }
            if (!is_numeric($data['altermobileno'])) {
                $errors['altermobileno'] = get_string('acceptedtype', 'local_admissions');
            }
        }
        if (!empty($data['pincode'])) {
            if (strlen($data['pincode']) !== 6) {
                $errors['pincode'] = get_string('errorpincodelen', 'local_admissions');
            }
            if (!is_numeric($data['pincode'])) {
                $errors['pincode'] = get_string('acceptedtype', 'local_admissions');
            }
            if (preg_match("/([%\$#\*\,\.]+)/", $data['pincode'])) {
                $errors['pincode'] = get_string('nospecialtype', 'local_admissions');
            }
            if (preg_match("/^[a-zA-Z\s]+$/", $data['pincode'])) {
                $errors['pincode'] = get_string('alphabettype', 'local_admissions');
            }
        }
        if (!empty($data['ppincode'])) {
            if (strlen($data['ppincode']) !== 6) {
                $errors['ppincode'] = get_string('errorpincodelen', 'local_admissions');
            }
            if (!is_numeric($data['ppincode'])) {
                $errors['ppincode'] = get_string('acceptedtype', 'local_admissions');
            }
            if (preg_match("/([%\$#\*\,\.]+)/", $data['ppincode'])) {
                $errors['ppincode'] = get_string('nospecialtype', 'local_admissions');
            }
            if (preg_match("/^[a-zA-Z\s]+$/", $data['ppincode'])) {
                $errors['ppincode'] = get_string('alphabettype', 'local_admissions');
            }
        }
        if (get_config('core', 'allowaccountssameemail') == 0) {
            if (!empty($data['email']) && ($user = $DB->get_record('user', array('email' => $data['email']), '*', IGNORE_MULTIPLE))) {
                if (empty($data['id']) || $user->id != $data['id']) {
                    $errors['email'] = get_string('emailexists', 'local_admissions');
                }
            }
        }
        if (get_config('core', 'allowaccountssameemail') == 0) {
            if (!empty($data['email']) && ($user = $DB->get_record('local_users', array('email' => $data['email']), '*', IGNORE_MULTIPLE))) {
                if (empty($data['id']) || $user->id != $data['id']) {
                    $errors['email'] = get_string('emailexists', 'local_admissions');
                }
            }
        }
        if ($id > 0) {
            // echo 'N/A';
        } else {
            if (!empty($data['email']) && !validate_email($data['email'])) {
                $errors['email'] = get_string('emailerror', 'local_admissions');
            }
            if (($data['email']) && !validate_email($data['email'])) {
            }
        }
        if (!empty($data['email'])) {
            $existsarray = array();
            $userexistingemailsql = "SELECT id, username, email FROM {user}";
            $userexistingemails = $DB->get_records_sql($userexistingemailsql);
            foreach ($userexistingemails as $userexistingemail) {
                if ($data['email'] == $userexistingemail->email) {
                    $existsarray[] = 1;
                }
                if ($data['email'] == $userexistingemail->username) {
                    $existsarray[] = 1;
                }
            }
            $lclusremailexistssql = "SELECT id, email FROM {local_users}";
            $lclusremails = $DB->get_records_sql($lclusremailexistssql);
            foreach ($lclusremails as $lclusremail) {
                if ($data['email'] == $lclusremail->email) {
                    $existsarray[] = 1;
                }
            }
            if (in_array(1, $existsarray)) {
                $errors['email'] = get_string('emailexists', 'local_admissions');
            }
        }
        return $errors;
    }
}
