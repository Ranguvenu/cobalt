<?php
/**
 * This file is part of eAbyas
 *
 * Copyright eAbyas Info Solutons Pvt Ltd, India
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author eabyas  <info@eabyas.in>
 * @package
 * @subpackage local_users
 */
namespace local_users\forms;

defined('MOODLE_INTERNAL') || die;
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/completionlib.php');
require_once('lib.php');
use moodleform;
use core_user;
use local_users\functions\userlibfunctions as userlib;
class extsignup_form extends moodleform {
	public $formstatus;
	public function __construct($action = null, $customdata = null, $method = 'post', $target = '', $attributes = null, $editable = true, $formdata = null) {

	 	$this->formstatus = array(
	 		'generaldetails' => get_string('generaldetails', 'local_users'),
			'otherdetails' => get_string('otherdetails', 'local_users'),
			'contactdetails' => get_string('contactdetails', 'local_users'),
			);
	 	parent::__construct($action, $customdata, $method, $target, $attributes, $editable, $formdata);
	}
    public function definition() {
        global $USER, $CFG, $DB, $PAGE;
        $mform = $this->_form;
        $mform->addElement('html', '<div class="card-title">
                        <h3 class="signup-title">Sign Up</h3>
                        <p class="signup-title-content">Enter your details to register yourself.</p>
                    </div> ');
        $namefields = useredit_get_required_name_fields();

        foreach ($namefields as $field) {
            $mform->addElement('text', $field, get_string($field,'local_users'), 'maxlength="100" size="30"');           
            $stringid = 'missing' . $field;
            if (!get_string_manager()->string_exists($stringid, 'moodle')) {
                $stringid = 'required';
            }
            $mform->addRule($field, get_string($stringid,'local_users'), 'required', null, 'client');
        }

        $mform->addElement('text', 'email', get_string('email'), 'maxlength="100" size="25"');
        $mform->addRule('email', get_string('missingemail'), 'required', null, 'client');
        $mform->setForceLtr('email');

        
        $mform->addElement('password', 'password', get_string('password'), 'maxlength="32" size="12"');
        $mform->setType('password', core_user::get_property_type('password'));
        $mform->addRule('password', get_string('missingpassword'), 'required', null, 'client');
        if (!empty($CFG->passwordpolicy)){
            $mform->addElement('static', 'passwordpolicyinfo', '', print_password_policy());
        }

        $this->add_action_buttons(true, get_string('createaccount', 'local_users'));
    }

   public function validation($data, $files) {
        $errors = array();
        global $DB, $CFG;
		$sub_data=data_submitted();
		$errors = parent::validation($data, $files);
        if ($user = $DB->get_record('user', array('email' => $data['email']), '*', IGNORE_MULTIPLE)) {
            if (empty($data['id']) || $user->open_employeeid != $data['id']) {
                $errors['email'] = get_string('emailexists', 'local_users');
            }
        }
        $email = $data['email'];
        $domain = substr(strrchr($email, "@"), 1);

        $domainsql = "SELECT id,shortname FROM {local_costcenter} WHERE (SUBSTRING_INDEX(SUBSTR(open_domains, INSTR(open_domains, '@') + 1),'.',2)) = '$domain'";

        $result = $DB->get_record_sql($domainsql ,array()); 
        $dept = $DB->get_field('local_costcenter','id',array('shortname'=>'all_'.$result->shortname));
        if(empty($result) || empty($dept)){
            $errors['email'] = get_string('domainnotexists', 'local_users');
        }
    if (!check_password_policy($data['password'], $errmsg, '')) {
        $errors['password'] = $errmsg;
    }
        return $errors;
    }
}

