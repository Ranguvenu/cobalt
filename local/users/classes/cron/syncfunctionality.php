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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author eabyas  <info@eabyas.in>
 * @package
 * @subpackage local_users
 */

namespace local_users\cron;
require_once($CFG->dirroot.'/user/lib.php');
require_once($CFG->dirroot.'/local/costcenter/lib.php');
use costcenter;
use core_text;
use core_user;
use DateTime;
use html_writer;
use stdClass;
define('MANUAL_ENROLL', 1);
define('LDAP_ENROLL', 2);
define('SAML2', 3);
define('ADWEBSERVICE', 4);
define('ADD_UPDATE', 3);
class syncfunctionality {
    private $data;
    private $errors = array();
    private $mfields = array();
    private $warnings = array();
    private $wmfields = array();
    private $errorcount = 0;
    private $warningscount = 0;
    private $updatesupervisor_warningscount = 0;
    private $errormessage;
    private $insertedcount = 0;
    private $updatedcount = 0;
    private $formdata;
    private $existing_user;
    private $employeeid_exist;

    public function __construct($data=null) {
        $this->data = $data;
        $this->timezones = \core_date::get_list_of_timezones($CFG->forcetimezone);
    }// end of constructor
    public function main_hrms_frontendform_method($cir, $filecolumns, $formdata) {
        global $DB, $USER, $CFG;
        $systemcontext = \context_system::instance();
        $inserted = 0;
        $updated = 0;
        $linenum = 1;
        $this->organizations = $this->get_organizations();
        $this->allusers = $this->get_allusers();

        while ($line = $cir->next()) {
            $linenum++;
            $user = new \stdClass();
            foreach ($line as $keynum => $value) {
                if (!isset($filecolumns[$keynum])) {
                    continue;
                }
                $key = $filecolumns[$keynum];
                $user->$key = trim($value);
            }
            $this->data[] = $user;
            $this->errors = array();
            $this->warnings = array();
            $this->mfields = array();
            $this->wmfields = array();
            $this->excel_line_number = $linenum;
            $mandatory_fields = ['first_name', 'last_name', 'username', 'organization', 'email','student_status'];
            foreach ($mandatory_fields as $field) {
                // Mandatory field validation.
                $this->mandatory_field_validation($user, $field);
            }
            // To check for existing user record.
            $sql = "SELECT u.username,u.id,u.open_costcenterid, u.email FROM {user} AS u WHERE (u.username LIKE :username OR u.open_employeeid LIKE :employeeid OR u.email LIKE :email) AND u.deleted = 0";
            $params = array();
            $params['username'] = $user->username;
            $params['employeeid'] = $user->employee_id;
            $params['email'] = $user->email;
            $existing_user = $DB->get_records_sql($sql, $params);
            if (count($existing_user == 1)) {
                $this->existing_user = array_values($existing_user)[0];
            } else if (count($existing_user > 1)) {
                $this->errors[] = get_string('multiple_user', 'local_users');
            } else {
                $this->existing_user = null;
            }
            // To hold costcenterid.
            $this->costcenterid = $this->get_org_hierarchyid($user->organization, $parent = 0);

            // Validation for employee status.
            $this->student_status_validation($user);
            //validation for mobile number
           
            if (!empty($user->email)) {
                $this->emailid_validation($user);
            }
            // if (!empty($user->force_password_change)) {
            //     $this->force_password_change_validation($user);
            // }
            // if (!empty($user->password) && !check_password_policy($user->password, $errmsg)) {
            //     $strings = new stdClass;
            //     $strings->errormessage = $errmsg;
            //     $strings->linenumber = $this->excel_line_number;
            //     $this->errors[] = get_string('password_upload_error', 'local_users', $strings);
            //     echo '<div class=local_users_sync_error>'.get_string('password_upload_error', 'local_users', $strings).'</div>';
            //     $this->errorcount++;
            // }
            $userobject = $this->preparing_users_object($user);
            // To display error messages.
            if (count($this->errors) > 0) {
            } else {
                if (is_null($this->existing_user)) {
                    $this->add_row($userobject);
                } else {
                    $this->update_row($user, $userobject);
                }
            }
        }
        if (count($this->warnings) > 0 ) {
            $this->updatesupervisor_warningscount = count($this->warnings);     
        }
        if ($this->data) {
            $upload_info = '<div class="critera_error1"><h3 style="text-decoration: underline;">'.get_string('empfile_syncstatus', 'local_users').'</h3>';
            $upload_info .= '<div class=local_users_sync_success>'.get_string('addedusers_msg', 'local_users', $this->insertedcount).'</div>';
            $upload_info .= '<div class=local_users_sync_success>'.get_string('updatedusers_msg', 'local_users', $this->updatedcount).'</div>';
            $upload_info .= '<div class=local_users_sync_error>'.get_string('errorscount_msg', 'local_users', $this->errorcount).'</div>
            </div>';
            $upload_info .= '<div class=local_users_sync_warning>'.get_string('warningscount_msg', 'local_users', $this->warningscount).'</div>';
            $upload_info .= '<div class=local_users_sync_warning>'.get_string('superwarnings_msg', 'local_users', $this->updatesupervisor_warningscount).'</div>';
            $button = html_writer::tag('button', get_string('button','local_users'), array('class' => 'btn btn-primary'));
            $link = html_writer::tag('a', $button, array('href' => $CFG->wwwroot. '/local/users/index.php'));
            $upload_info .='<div class="w-full pull-left text-xs-center">'.$link.'</div>';
            mtrace($upload_info);
        } else {
            echo'<div class="critera_error">'.get_string('filenotavailable', 'local_users').'</div>';
        }
    }//end of main_hrms_frontendform_method

    public function get_organizations() {
        global $DB;
        $sql = "SELECT shortname, id, parentid FROM {local_costcenter}";
        $costcenterslist = $DB->get_records_sql($sql);
        return $costcenterslist;
    }

    public function get_org_hierarchyid($fieldvalue, $parent) {
        global $DB;
        $datalist = $this->organizations;
        $datal = $datalist[$fieldvalue];
        if ($datal) {
            if ($parent == $datal->parentid) {
                return $datal->id;
            }
        } else {
            $strings = new stdClass;
            if ($parent == 0) {
                $identifier = 'organization';
                $strings->orgid = $fieldvalue;
            } else {
                $identifier = 'department';
                $strings->orgid = $fieldvalue;
            }
            $strings->identifier = $identifier;
            $strings->line = $this->excel_line_number;
            echo '<div class=local_users_sync_error>'.get_string('noorganizationidfound', 'local_users', $strings).'</div>';
            $this->errors[] = get_string('noorganizationidfound', 'local_users', $strings);
            $this->mfields[] = $fieldvalue;
            $this->errorcount++;
        }
    }//end of get_org_hierarchyid method

    public function mandatory_field_validation($user, $field) {
         //validation for mandatory missing fields
        if (empty(trim($user->$field))) {
              $strings = new stdClass;
              $strings->field = $field;
              $strings->linenumber = $this->excel_line_number;
            echo '<div class=local_users_sync_error>'.get_string('missing', 'local_users', $strings).'</div>';
            $this->errors[] = get_string('missing', 'local_users', $strings);
            $this->mfields[] = $field;
            $this->errorcount++;
        }
    }//end of mandatory_field_validation
    public function student_status_validation($excel) {
        //validation for employee status
        $strings = new stdClass;
        $strings->employee_id = $excel->employee_id;
        $strings->excel_line_number = $this->excel_line_number;
        $student_status = $excel->student_status;
        $this->deletestatus = 0;
        if (array_key_exists('student_status', $excel)) {
            if (strtolower($excel->student_status) == 'active') {
                $this->activestatus = 0;
            } else if ( strtolower($excel->student_status) == 'inactive' ) {
                $this->activestatus = 1;
            } else if ( strtolower($excel->student_status) == 'delete' ) {
                $this->deletestatus = 1;
            } else {
                $strings = new stdClass;
                $strings->line = $this->excel_line_number;
                echo '<div class=local_users_sync_error>'.get_string('statusvalidation', 'local_users', $strings).'</div>';
                $this->errors[] = get_string('statusvalidation','local_users', $strings);
                $this->mfields[] = $excel->student_status;
                $this->errorcount++;
            }
        } else {
            echo '<div class=local_users_sync_error>Error in arrangement of columns in uploaded excelsheet at line '.$this->excel_line_number.'</div>';
            $this->errormessage = get_string('columnsarragement_error','local_users', $excel);
            $this->errorcount++;
        }
    } // end of  student_status_validation method

    public function get_super_userid($reportinguserid, $orgid) {
        $userslist = $this->allusers;
        $user = $userslist[$reportinguserid];
        if ($user) {
            if ($orgid == $user->open_costcenterid) {
                return $user->id;
            }
        } else {
            $strings = new \stdClass();
            $strings->empid = $reportinguserid;
            $strings->line = $this->excel_line_number;
            $warningmessage = get_string('nosupervisorempidfound','local_users',$strings);
            $this->errormessage = $warningmessage;
            echo '<div class=local_users_sync_warning>'.$warningmessage.'</div>';
            $this->warningscount++;
            }  
    }

    public function get_subdepartmentid($subdepartmentid, $parentid) {
        global $DB;
        $datalist = $this->organizations;
        $datal = $datalist[$subdepartmentid];
        if ($datal) {
            if ($parentid == $datal->parentid) {
                return $datal->id;
            }
        } else {
            $strings = new \stdClass();
            $strings->subdepartmentid = $subdepartmentid;
            $strings->line = $this->excel_line_number;
            $warningmessage = get_string('noorsubdepartmentfound','local_users',$strings);
            $this->errormessage = $warningmessage;
             echo '<div class=local_users_sync_warning>'.$warningmessage.'</div>';
            $this->warningscount++; 
        }
    }

  public function preparing_users_object($excel, $formdata=null) {
        global $USER, $DB, $CFG;
        $user = new \stdclass();
        $user->mnethostid = 1;
        $user->confirmed = 1;
        $user->suspended = $this->activestatus;
        $user->idnumber = $excel->employee_id;
        $user->open_employeeid = $excel->employee_id;
        $user->username = strtolower($excel->username);

        $user->firstname =  $excel->first_name;
        $user->lastname = $excel->last_name;
        $user->email = strtolower($excel->email);
        $user->country = $excel->country ? $excel->country : 'IN';
        $user->student_status = $excel->student_status;
        $user->open_costcenterid = $this->costcenterid;
        $user->open_departmentid = $this->departmentid;
        $user->open_subdepartment =  $this->level2_departmentid;
        $user->department = $excel->department;
        if($excel->reportingmanager_empid){
           $super_user =  $this->get_super_userid($excel->reportingmanager_empid, $user->open_costcenterid);
           $user->open_supervisorid = $super_user;
        }
        $user->usermodified = $USER->id;
        if(!empty(trim($excel->password))){
            $user->password = hash_internal_user_password(trim($excel->password));
        }else{
            unset($user->password);
        }
        if($this->deletestatus == 1){
            $user->deleted = 0;
            $user->username = time().$user->username;
            $user->email = time().$user->email;
            $user->open_employeeid = time().$user->open_employeeid;
        }
        $user->force_password_change = (empty($excel->force_password_change)) ? 0 : $excel->force_password_change;
        if($formdata){
            switch($formdata->enrollmentmethod){
                case MANUAL_ENROLL:
                      $user->auth = "manual";
                      break;
                case LDAP_ENROLL:
                      $user->auth = "ldap";
                      break;
                case SAML2:
                      $user->auth = "saml2";
                      break;
                case ADwebservice:
                      $user->auth = "adwebservice";
                      break;                    
            }
        }
        return $user;
    } // end of  preparing_users_object method
   
    public function add_row($userobject) {
        global $DB, $USER, $CFG;
        $insertnewuserfromcsv = user_create_user($userobject, false);
        $userobject = (object)$userobject;
        $userobject->id = $insertnewuserfromcsv;
        $this->allusers[$userobject->open_employeeid] = $userobject;
        if ($userobject->force_password_change == 1) {
            set_user_preference('auth_forcepasswordchange', $userobject->force_password_change, $insertnewuserfromcsv);
        }
        if ($this->formdata->createpassword) {
            $usernew = $DB->get_record('user', array('id' => $insertnewuserfromcsv));
            setnew_password_and_mail($usernew);
            unset_user_preference('create_password', $usernew);
            set_user_preference('auth_forcepasswordchange', 1, $usernew);
        }
            $this->insertedcount++;
     } // end of add_row method

    public function update_row($excel, $user) {
        global $USER, $DB, $CFG;
        // Condition to get the userid to update the data.
        $userid = $this->existing_user->id;
        if ($userid) {
            $user->id = $userid;
            $user->timemodified = time();
            $user->suspended = $this->activestatus;
            $user->idnumber = $excel->employee_id;
            if(isset($user->open_costcenterid)) {
                $existingcostcenter = $this->existing_user->open_costcenterid;
                if($user->open_costcenterid != $existingcostcenter){
                    \core\session\manager::kill_user_sessions($user->id);
                }
            }
            $user->open_departmentid = $this->departmentid;
            $user->open_subdepartment = $this->level2_departmentid;
            $user->department = $excel->department;
            $user->usermodified = $USER->id;
            if (!empty($excel->password)) {
                $user->password = hash_internal_user_password($excel->password);
            } else {
                 unset($user->password);
            }
            if ($this->deletestatus == 1) {
                $user->deleted = 0;
                $user->username = time().$user->username;
                $user->email = time().$user->email;
                $user->open_employeeid = time().$user->open_employeeid;
            }
            user_update_user($user, false);
            if ($this->formdata->createpassword) {
                $usernew = $DB->get_record('user', array('id' => $user->id));
                setnew_password_and_mail($usernew);
                unset_user_preference('create_password', $usernew);
                set_user_preference('auth_forcepasswordchange', 1, $usernew);
            }
            if ($user->force_password_change == 1) {
                set_user_preference('auth_forcepasswordchange', $user->force_password_change, $user->id);
            }
            $this->updatedcount++;
        }
    } // end of  update_row method

    public function emailid_validation($excel) {
        global $DB;
        $strings = new StdClass();
        $strings->employee_id = $excel->employee_id;
        $strings->excel_line_number = $this->excel_line_number;
        $this->email = $excel->email;
        if (!validate_email($excel->email)) {
            echo '<div class="local_users_sync_error">'.get_string('invalidemail_msg', 'local_users', $strings).'</div>';
            $this->errors[] = get_string('invalidemail_msg', 'local_users', $strings);
            $this->mfields[] = 'email';
            $this->errorcount++;
        }
    }
    /**
     * [force_password_change_validation description]
     * @param  [type] $excel [description]
     */
    // private function force_password_change_validation($excel) {
    //     $this->force_password_change = $excel->force_password_change;
    //     if (!is_numeric($this->force_password_change) || !(($this->force_password_change == 1) || ($this->force_password_change == 0))){
    //         echo '<div class=local_users_sync_error>force_password_change column should have value as 0 or 1 at line '.$this->excel_line_number.'</div>';
    //         $this->errors[] = 'force_password_change column should value as 0 or 1 at line '.$this->excel_line_number.'';
    //         $this->mfields[] = 'force_password_change';
    //         $this->errorcount++;
    //     }
    // }
    public function get_allusers() {
        global $DB;
        $usersql = " SELECT open_employeeid, open_costcenterid, id FROM {user}";
        $users = $DB->get_records_sql($usersql);
        return $users;
    }
} //end of class
