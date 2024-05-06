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
define('ADwebservice', 4);
class cronfunctionality{
    
    private $data;
    
    //-------To hold error messages
    private $errors = array();
    
    //----To hold error field name       
    private  $mfields = array();
    
    //-----To hold warning messages----
    private $warnings = array();
    
    //-----To hold warning field names-----
    private $wmfields = array();
    
    private $errormessage;
    
    //-----It hold user field cost center id
    private $costcenterid;
    
    //-----It will hold the Deparment id
    private $leve1_departmentid;
    
    // ----It will hold the Sub_department id
    private $leve2_departmentid;
    
    //---It will holds the status(active or inactive) of the user
    private $activestatus;
    
    //----It will holds the count of inserted record
    private $insertedcount=0;
    
    //----It will holds the count of updated record
    private $updatedcount=0;
    
    
    public $costcenterobj;

    private $errorcount=0;

    private $warningscount=0;

    private $updatesupervisor_warningscount =0;
    
    //---It will holds the costcenter shortname    
    private $costcenter_shortname;

    //-----It holds the unique username    
    private $username;
    
    //----It holds the unique employee id
    private $employee_id;

    private $department_shortname;

    private $excel_line_number;

    private $timezones;

    private $formdata;
    
    
    function __construct($data=null){   
        global $CFG;
        $this->data = $data;
        $this->costcenterobj = new costcenter();
        $this->timezones = \core_date::get_list_of_timezones($CFG->forcetimezone);
    }// end of constructor
    
    /**BULK UPLOAD FRONTEND METHOD
    * @param  $cir [<csv_import_reader Object >]
    * @param  $[filecolumns] [<colums fields in csv form>]
    * @param array $[formdata] [<data in the csv>]
    * for inserting record in local_userssyncdata.
     **/
    public function  main_hrms_frontendform_method($cir,$filecolumns, $formdata){
           
        global $DB,$USER, $CFG;
        $this->formdata = $formdata;
        $inserted = 0; $updated = 0; 
        $linenum = 1;    
        while($line=$cir->next()){

            $linenum++;
            $user = new \stdClass();
            foreach ($line as $keynum => $value) {
                if (!isset($filecolumns[$keynum])) {
                    // this should not happen
                    continue;
                    }
                $key = $filecolumns[$keynum];
                $user->$key = trim($value);
                 
                }
            $user->username=$user->email; 
            $user->excel_line_number=$linenum;  
            $this->data[]=$user;  
            $this->errors = array();
            $this->warnings = array();
            $this->mfields = array();
            $this->wmfields = array();
            $this->excel_line_number = $linenum;
            $stringhelpers = new stdClass();
            $stringhelpers->linenumber = $this->excel_line_number;
            // $user->department = 'all_'.$user->organization;
            // if(empty(trim($user->department))){
            //     $this->errors[] = get_string('client_upload_error', 'local_users', $stringhelpers);
            //     echo '<div class=local_users_sync_error>'.get_string('client_upload_error', 'local_users', $stringhelpers).'</div>';
            // }
            // if(empty(trim($user->password))){
            //     $this->errors[] = get_string('password_required', 'local_users', $stringhelpers); 
            //     echo '<div class=local_users_sync_error>'.get_string('password_required', 'local_users', $stringhelpers).'</div>';
            //     $this->errorcount++;

            // }
            if (strrpos($user->password, ' ') !== false) {
                echo '<div class=local_employees_sync_error>Spaces are not allowed in password for  student id "' . $user->employee_id . '" of uploaded excelsheet at line '.$this->excel_line_number.'.</div>';
                $this->errors[] = 'Provide password for  student id "' . $user->employee_id . '" of uploaded excelsheet at line '.$this->excel_line_number.'.';
                $this->mfields[] = 'password';
                $this->errorcount++;
            } else if(!empty($user->password) && !check_password_policy($user->password, $errmsg)){
                $stringhelpers->errormessage = $errmsg;
                $this->errors[] = get_string('password_upload_error', 'local_users', $stringhelpers);
                echo '<div class=local_users_sync_error>'.get_string('password_upload_error', 'local_users', $stringhelpers).'</div>';
                $this->errorcount++;
            }

            if(empty($user->password) && $formdata->createpassword == 0){
                $stringhelper = new stdClass();
                $stringhelper->linenumber = $this->excel_line_number;
                $stringhelper->password = $excel->password;
                $error_string = get_string('cannotcreateusepasswordadderror', 'local_users', $stringhelper);
                echo "<div class='local_users_sync_error'>".$error_string.".</div>";
                $this->errors[] = $error_string;
                $this->mfields[] = 'password';
                $this->errorcount++;
            }
            
            //---to get the costcenter shortname------
            // $this->to_get_the_costcentershortname($user);      
            //---to get the department shortname------
            $this->to_get_the_departmentshortname($user);
            
            //---It will set the username and employee id-----
            $this->to_get_the_username_employeeid($user,$formdata->option);

            //--username validation and also creating costcenter if not available
            $this->costcenter_validation($user);
            $this->secondlevel_validation($user);
             
            //-----It includes firstname and lastname, email fields validation
            $this->required_fields_validations($user,$formdata->option);              
            
            //-----It includes employee status validation , if find  other than the existing string,it will suspend the user
            $this->student_status_validation($user);

           
            // if(!empty($user->force_password_change)){
            //     $this->force_password_change_validation($user);
            // }
           
            //---It will set the  level1_departmentid-----
            if(!empty($this->open_costcenterid)){
                if(!empty($this->level1_departmentid)){
                //---It will set the  level2_departmentid-----
                    $this->get_departmentid('subdepartment', $this->level1_departmentid, $user, 'level2_departmentid');
                }else{
                    $this->level2_departmentid = null;
                }
            }else{
                $this->level1_departmentid = null;
            }
            if (count($this->errors) > 0) {
                // write error message to db and inform admin
            } else {
                //-----based on selected form option add and update operation will dones
                if($formdata->option==ONLY_ADD){
                    if($DB->record_exists('user',  array('username' => $user->username))){
                        $strings = new stdClass();
                        $strings->username = $user->username;
                        $strings->excel_line_number = $this->excel_line_number;
                        echo '<div class="local_users_sync_error">'.get_string('usernamealeadyexists', 'local_users', $strings).'</div>';
                        $this->errors[] = get_string('usernamealeadyexists', 'local_users', $strings);
                        $this->mfields[] = "username";  
                        $this->errorcount++;
                        $flag=1;
                        continue;
                    } else if($DB->record_exists('user',  array('open_employeeid' => $user->employee_id))){
                        $strings = new stdClass();
                        $strings->employee_id = $user->employee_id;
                        $strings->excel_line_number = $this->excel_line_number;
                        echo '<div class="local_users_sync_error">'.get_string('employeeid_alreadyexists','local_users', $strings).'</div>';
                        $this->errors[] = get_string('employeeid_alreadyexists', 'local_users', $stings);
                        $this->mfields[] = "useremployeeid";
                        $this->errorcount++;
                        $flag=1;
                        continue;
                    }      
                } 
                if($formdata->option==ONLY_ADD || $formdata->option==ADD_UPDATE){
                    $exists=$DB->record_exists('user',array('open_employeeid'=>$user->employee_id));
                    if(!$exists){ 
                        $err=$this->specific_costcenter_validation($user,$formdata->option);
                        if(!$err)
                        $this->add_rows($user, $formdata);
                    }else if($formdata->option==ONLY_ADD){
                        $strings = new stdClass();
                        $strings->employee_id = $user->employee_id;
                        $strings->excel_line_number = $this->excel_line_number;
                        echo '<div class="local_users_sync_error">'.get_string('employeeid_alreadyexists','local_users', $strings).'</div>';
                        $this->errors[] = get_string('employeeid_alreadyexists','local_users', $strings);
                        $this->mfields[] = "employee_id";
                        $this->errorcount++;
                        $flag=1;
                        continue;
                    }      
                }
                if($formdata->option==ONLY_UPDATE || $formdata->option==ADD_UPDATE){
                    $user_sql = "SELECT id  FROM {user} WHERE (username = :username OR open_employeeid = :employeeid) AND deleted = 0";
                    $user_exists = $DB->get_record_sql($user_sql,  array('username' => $user->username, 'employeeid' => $user->employee_id));
                    if ($user_exists) {                    
                        //-----Update functionality-----
                        $userobject=$this->preparing_user_object($user, $formdata);
                        $this->update_rows($user, $userobject);                               
                    }else if($formdata->option==ONLY_UPDATE) {
                        $strings = new stdClass();
                        $strings->employee_id = $user->employee_id;
                        $strings->excel_line_number = $this->excel_line_number;
                        echo '<div class="local_users_sync_error">'.get_string('empiddoesnotexists','local_users',$strings).'</div>';
                        $this->errors[] = get_string('empiddoesnotexists','local_users',$strings);
                        $this->mfields[] = "employee_id";
                        $this->errorcount++;
                        $flag=1;
                        continue;
                    }              
                }                
                // write warnings to db and inform admin
                if ( count($this->warnings) > 0) {
                    $this->warningscount = count($this->warnings);   
                }
            }                
            $data[]=$user;      
        }
         errorloop:
        //-----updating Reporting Manager (supervisor id )
        $this->update_supervisorid($this->data);
        if ( count($this->warnings) > 0 ) {
            $this->updatesupervisor_warningscount= count($this->warnings); 
        }
        if($this->data){
            $upload_info =  '<div class="critera_error1"><h3 style="text-decoration: underline;">'.get_string('empfile_syncstatus', 'local_users').'</h3>';
            $upload_info .= '<div class=local_users_sync_success>'.get_string('addedusers_msg', 'local_users', $this->insertedcount).'</div>';
            $upload_info .= '<div class=local_users_sync_success>'.get_string('updatedusers_msg', 'local_users', $this->updatedcount).'</div>';
            $upload_info .= '<div class=local_users_sync_error>'.get_string('errorscount_msg', 'local_users', $this->errorcount).'</div>
            </div>';
            $upload_info .= '<div class=local_users_sync_warning>'.get_string('warningscount_msg', 'local_users', $this->warningscount).'</div>';
            $upload_info .= '<div class=local_users_sync_warning>'.get_string('superwarnings_msg', 'local_users', $this->updatesupervisor_warningscount).'</div>';

            /*code added  for continue button*/
            $button=html_writer::tag('button',get_string('button','local_users'),array('class'=>'btn btn-primary'));
            $link= html_writer::tag('a',$button,array('href'=>$CFG->wwwroot. '/local/users/index.php'));
            $upload_info .='<div class="w-full pull-left text-xs-center">'.$link.'</div>';
            /*end of the code*/
            mtrace( $upload_info);        
        } else {
            $systemcontext = \context_system::instance();
            $labelstring = get_config('local_costcenter');
            if(is_siteadmin() || has_capability('local/costcenter', $systemcontext)){
                echo'<div class="critera_error">'.\core\notification::add(get_string('provideorganization','local_employees', $labelstring), \core\output\notification::NOTIFY_ERROR).'</div>';
            }
            echo'<div class="critera_error">'.\core\notification::add(get_string('providefirstname','local_employees'), \core\output\notification::NOTIFY_ERROR).'</div>';
            echo'<div class="critera_error">'.\core\notification::add(get_string('providelastname','local_employees'), \core\output\notification::NOTIFY_ERROR).'</div>';
            echo'<div class="critera_error">'.\core\notification::add(get_string('providepassword','local_employees'), \core\output\notification::NOTIFY_ERROR).'</div>';
            echo'<div class="critera_error">'.\core\notification::add(get_string('provideemail','local_employees'), \core\output\notification::NOTIFY_ERROR).'</div>';
            echo'<div class="critera_error">'.\core\notification::add(get_string('providestudentstatus','local_users'), \core\output\notification::NOTIFY_ERROR).'</div>';
            echo'<div class="critera_error">'.\core\notification::add(get_string('providerollno','local_employees'), \core\output\notification::NOTIFY_ERROR).'</div>';

            if(is_siteadmin() || has_capability('local/costcenter:manage_multiorganizations', $systemcontext) || has_capability('local/costcenter:manage_ownorganization', $systemcontext)){
                echo'<div class="critera_error">'.\core\notification::add(get_string('providecollege','local_employees', $labelstring), \core\output\notification::NOTIFY_ERROR).'</div>';
            }
            echo'<div class="critera_error">'.\core\notification::add(get_string('providestudentphonenumber','local_users'), \core\output\notification::NOTIFY_ERROR).'</div>';
            /*code added for continue button*/
            $button=html_writer::tag('button',get_string('button','local_users'),array('class'=>'btn btn-primary'));
            $link= html_writer::tag('a',$button,array('href'=>$CFG->wwwroot. '/local/users/sync/hrms_async.php'));
            $upload_info .='<div class="w-full pull-left text-xs-center">'.$link.'</div>';
            /*end of the code*/
            mtrace( $upload_info);
        }
        
    } // end of main_hrms_frontendform_method function
    /**
     * @param   $excel [<data in excel or csv uploaded>]
     */
    // private function to_get_the_costcentershortname($excel){        
    //     $costcenter_shortname= core_text::strtolower($excel->organization);
    //     // print_object($excel->employee_id);exit;
    //     if(empty($costcenter_shortname)){
    //         echo '<div class=local_users_sync_error>'.get_string('orgmissing_msg', 'local_users', $excel).'</div>';
    //         $this->errors[] = get_string('orgmissing_msg', 'local_users', $excel);
    //         $this->mfields[] = 'organization';
    //         $this->errorcount++;
    //     }
    //     else{            
    //         $this->costcenter_shortname = $costcenter_shortname;            
    //     }        
    // } // end of the to_get_the_costcentershortname  

    /**
     * @param   $excel [<data in excel or csv uploaded>]
     */
    private function to_get_the_departmentshortname($excel){ 
    global $DB;       
        $department_shortname= 'all_'.$excel->organization;
        if(empty($department_shortname)){
        }
        else{            
            $this->department_shortname = $department_shortname;
            $this->level1_departmentid = $DB->get_field('local_costcenter', 'id', array('shortname' => $department_shortname));           
        }        
    } // end of the to_get_the_departmentshortname 

    /**
     * @param   $excel [<data in excel or csv uploaded>] for validation
     */
    private function costcenter_validation($excel){
         global $DB, $USER;
        $systemcontext = \context_system::instance();
         //------username validation------
            $labelstring = get_config('local_costcenter');
            $firstlevel = strtolower($labelstring->firstlevel);
            $secondlevel = strtolower($labelstring->secondlevel);
            $thirdlevel = strtolower($labelstring->thirdlevel);
            if ( $this->costcenter_shortname) {
                $costcenter_shortname=$this->costcenter_shortname;
                // checking cost center available if not inserting new costcenter
                $costcenterinfo = $DB->get_record_sql("SELECT * FROM {local_costcenter} WHERE lower(shortname)='$costcenter_shortname'");                
                $strings = new stdClass;
                $strings->org_shortname = $costcenter_shortname;
                $strings->excel_line_number = $this->excel_line_number;
                $strings->firstlevel = $firstlevel;
                if(empty($costcenterinfo)){
                    $this->errors[] = get_string('invalidorg_msg', 'local_users',$strings);
                    $this->mfields[] = $firstlevel;
                    $this->errorcount++;
                // }
                //     else if($costcenterinfo->shortname != $costcenter_shortname){
                //     $this->errors[] = get_string('aaaaa', 'local_users',$strings);
                //     $this->mfields[] = 'Organisation';
                //     $this->errorcount++;

                } else if ((!$DB->record_exists('user', array('id'=> $USER->id, 'open_costcenterid'=>$costcenterinfo->id))) && (!is_siteadmin()) && (!has_capability('local/costcenter:manage_multiorganizations', $systemcontext))){
                    $this->errors[] = get_string('otherorg_msg', 'local_users', $strings);
                    $this->mfields[] = $firstlevel;
                    $this->errorcount++;
                }else {
                    $this->open_costcenterid = $costcenterinfo->id;
                }
            }  
    } // end of costcenter_validation function
    
    // secondlevel_validation
    /**
     * @param   $excel [<data in excel or csv uploaded>] for validation
     */
    private function secondlevel_validation($excel){
         global $DB, $USER;
        $systemcontext = \context_system::instance();
         //------secondlevel validation------
            $labelstring = get_config('local_costcenter');
            $firstlevel = strtolower($labelstring->firstlevel);
            $secondlevel = strtolower($labelstring->secondlevel);
            $thirdlevel = strtolower($labelstring->thirdlevel);
            if(is_siteadmin() || has_capability('local/costcenter:manage_multiorganizations', $systemcontext) || has_capability('local/costcenter:manage_ownorganization', $systemcontext)){
                if (empty($excel->$secondlevel)) {
                    $strings = new stdClass;
                    $strings->secondlevel = ucfirst($secondlevel);
                    $strings->excel_line_number = $this->excel_line_number;
                    echo '<div class=local_users_sync_error>'.get_string('department_emptymsg','local_users', $strings).'</div>';
                    $this->errors[] = get_string('department_emptymsg','local_users', $strings);
                    $this->mfields[] = 'Department';
                    $this->errorcount++;
                }  
            }
            // exit('hello');
    } // end of secondlevel_validation function
    /**
     * @param   $excel [<data in excel or csv uploaded>] for validation
     */
    private function required_fields_validations($excel,$option=0){
        global $DB, $USER;
        $labelstring = get_config('local_costcenter');
        $firstlevel = strtolower($labelstring->firstlevel);
        $secondlevel = strtolower($labelstring->secondlevel);
        $thirdlevel = strtolower($labelstring->thirdlevel);
        $systemcontext = \context_system::instance();
        $empid = $DB->get_record_sql('SELECT id,phone1 from {user} ORDER BY id DESC LIMIT 1');
        $empid->id++;
        $excel->employee_id = $excel->first_name.$excel->last_name.$empid->id;
        if(!empty($excel->employee_id)  && !empty($excel->username)){

            $exist_sql = "SELECT id,username FROM {user} 
                        WHERE (username = :username  
                        OR open_employeeid = :employeeid)
                        AND deleted = 0";
            $users_exist = $DB->get_records_sql_menu($exist_sql, array('username' => $excel->username ,'email' => $excel->email, 'employeeid' => $excel->employee_id, 'phone1' => $excel->phone));
            $cexist_users = count($users_exist);
        }
        //------employee code validation------- 
         if ( array_key_exists('employee_id', (array)$excel) ) {
                if (!empty($excel->employee_id)) {
                    $this->employee_id = $excel->employee_id;
                    $stringhelpers = new stdClass();
                    $stringhelpers->linenumber = $this->excel_line_number;
                    $stringhelpers->employee_id = $this->employee_id;   
                } else {
                    $strings = new stdClass;
                    $strings->username = $excel->username;
                    $strings->excel_line_number = $this->excel_line_number;
                    echo '<div class=local_users_sync_error>'.get_string('empidempty_msg','local_users', $strings).'</div>';
                    $this->errors[] = get_string('empidempty_msg','local_users', $strings);
                    $this->mfields[] = 'Employee_id';
                    $this->errorcount++;
                }
            } else {
                echo '<div class=local_users_sync_error>'.get_string('error_employeeidcolumn_heading', 'local_users').'</div>';
                $this->errormessage = get_string('error_employeeidcolumn_heading', 'local_users');
                $this->errorcount++;
            }
            //---------end of employee code validation-----
            //-----------check firstname-----
            if ( array_key_exists('first_name', (array)$excel) ) {
                if (empty($excel->first_name)) {
                    $strings = new stdClass;
                    $strings->employee_id = $excel->employee_id;
                    $strings->excel_line_number = $this->excel_line_number;
                    
                    echo '<div class="local_users_sync_error">'.get_string('firstname_emptymsg','local_users', $strings).'</div>'; 
                    $this->errors[] = get_string('firstname_emptymsg','local_users', $strings);
                    $this->mfields[] = 'firstname';
                    $this->errorcount++;
                } else if (strrpos($excel->first_name, ' ') !== false) {
                    echo '<div class=local_employees_sync_error>Spaces are not allowed in firstname for  student id "' . $user->employee_id . '" of uploaded excelsheet at line '.$this->excel_line_number.'.</div>';
                    $this->errors[] = 'Provide firstname for  student id "' . $user->employee_id . '" of uploaded excelsheet at line '.$this->excel_line_number.'.';
                    $this->mfields[] = 'firstname';
                    $this->errorcount++;
                }
            } else {
               echo '<div class="local_users_sync_error">'.get_string('error_firstnamecolumn_heading', 'local_users').'</div>'; 
               $this->errormessage = get_string('error_firstnamecolumn_heading', 'local_users');
               $this->errorcount++;
            }
            //check lastname
            if ( array_key_exists('last_name', (array)$excel) ) {
                if (empty($excel->last_name)) {
                    echo '<div class="local_users_sync_error">'.get_string('latname_emptymsg','local_users', $excel).'</div>'; 
                    $this->errors[] = get_string('latname_emptymsg','local_users', $excel);
                    $this->mfields[] = 'last_name';
                    $this->errorcount++;
                }
                else if (strrpos($excel->last_name, ' ') !== false) {
                    echo '<div class=local_employees_sync_error>Spaces are not allowed in last_name for  student id "' . $user->employee_id . '" of uploaded excelsheet at line '.$this->excel_line_number.'.</div>';
                    $this->errors[] = 'Provide last_name for  student id "' . $user->employee_id . '" of uploaded excelsheet at line '.$this->excel_line_number.'.';
                    $this->mfields[] = 'last_name';
                    $this->errorcount++;
                }
            } else {
                echo '<div class="local_users_sync_error">'.get_string('error_lastnamecolumn_heading', 'local_users').'</div>'; 
                $this->errormessage = get_string('error_lastnamecolumn_heading', 'local_users');
                $this->errorcount++;
            }     
            // check email id
            if ( array_key_exists('email', (array)$excel) ) {
                $strings = new stdClass;
                $strings->employee_id = $excel->employee_id;
                $strings->excel_line_number = $this->excel_line_number;
                if (empty($excel->email)) {
                    echo '<div class="local_users_sync_error">'.get_string('email_emptymsg', 'local_users', $strings).'</div>';
                    $this->errors[] = get_string('email_emptymsg', 'local_users', $strings);
                    $this->mfields[] = 'email';
                    $this->errorcount++;
                } else {
                    if (! validate_email($excel->email)) {
                        echo '<div class="local_users_sync_error">'.get_string('invalidemail_msg', 'local_users', $strings).'</div>';
                        $this->errors[] = get_string('invalidemail_msg', 'local_users', $strings);
                        $this->mfields[] = 'email';
                        $this->errorcount++;
                    }
                } 
            }
            // check phone number
            if ( array_key_exists('phone', (array)$excel) ) {
                if (empty($excel->phone)) {
                    echo '<div class="local_employees_sync_error">'.get_string('phone_emptymsg','local_employees', $excel).'</div>';
                    $this->errors[] = get_string('phone_emptymsg','local_employees', $excel);
                    $this->mfields[] = 'phone';
                    $this->errorcount++;
                }
                else if (!preg_match('/^[a-z0-9\s\_]+$/i', $excel->phone)) {
                    echo '<div class="local_users_sync_error">'.get_string('specialcharactersnotallwoed','local_users', $excel).'</div>'; 
                    $this->errors[] = get_string('specialcharactersnotallwoed','local_users', $excel);
                    $this->mfields[] = 'phone1';
                    $this->errorcount++;
                } 
                if(!empty($excel->phone)){
                    if (strlen($excel->phone) !== 10) {
                        echo '<div class="local_employees_sync_error">'.get_string('phonenumber_limit','local_employees', $excel).'</div>';
                        $this->errors[] = get_string('phonenumber_limit','local_employees', $excel);
                        $this->mfields[] = 'phone';
                        $this->errorcount++;
                    }
                    if (!is_numeric($excel->phone)) {
                        echo '<div class="local_employees_sync_error">'.get_string('phonenumber_numeric','local_employees', $excel).'</div>';
                        $this->errors[] = get_string('phonenumber_numeric','local_employees', $excel);
                        $this->mfields[] = 'phone';
                        $this->errorcount++;
                    }
                }
            } else {
                echo '<div class="local_employees_sync_error">'.get_string('error_phonecolumn_heading', 'local_employees').'</div>';
                $this->errormessage = get_string('error_phonecolumn_heading', 'local_employees');
                $this->errorcount++;
            }

            //check organization
            if(is_siteadmin() || has_capability('local/costcenter:manage_multiorganizations', $systemcontext)){
                $costcenter_shortname = core_text::strtolower($excel->$firstlevel);
                $costcenterinfo = $DB->get_record_sql("SELECT * FROM {local_costcenter} WHERE lower(shortname) = '$costcenter_shortname'");
                if ( array_key_exists($firstlevel, (array)$excel)) {
                        $excel->firstlevel = $firstlevel;
                    if (empty($excel->$firstlevel)) {
                        echo '<div class="local_employees_sync_error">'.get_string('organization_emptymsg','local_users', $excel).'</div>';
                        $this->errors[] = get_string('organization_emptymsg','local_users', $excel);
                        $this->mfields[] = $firstlevel;
                        $this->errorcount++;
                    } else if (strrpos($excel->$firstlevel, ' ') !== false) {
                        echo '<div class=local_employees_sync_error>Spaces are not allowed in "'.ucfirst($firstlevel).'" for  student id "' . $user->employee_id . '" of uploaded excelsheet at line '.$this->excel_line_number.'.</div>';
                        $this->errors[] = 'Provide "'.$firstlevel.'" for  student id "' . $user->employee_id . '" of uploaded excelsheet at line '.$this->excel_line_number.'.';
                        $this->mfields[] = "'$firstlevel'";
                        $this->errorcount++;
                    } 
                    else if (empty($costcenterinfo) && strtolower($costcenterinfo->shortname) != $costcenter_shortname) {
                        $excel->firstlevel = ucfirst($excel->firstlevel);
                        $excel->organization = $excel->$firstlevel;
                        echo '<div class="local_employees_sync_error">'.get_string('wrong_organization','local_users', $excel).'</div>';
                        $this->errors[] = get_string('wrong_organization','local_users', $excel);
                        $this->mfields[] = $firstlevel;
                        $this->errorcount++;
                    }

                } else {
                    $excel->firstlevel = ucfirst($firstlevel);
                    echo '<div class="local_employees_sync_error">'.get_string('error_organizationcolumn_heading', 'local_users', $excel).'</div>';
                    $this->errormessage = get_string('error_organizationcolumn_heading', 'local_users');
                    $this->errorcount++;
                }
            } /*else if(!is_siteadmin() && !has_capability('local/costcenter:manage_multiorganizations', $systemcontext) && has_capability('local/costcenter:manage_ownorganization', $systemcontext)){

            }*/

            //check department
            $excel->secondlevel = ucfirst($secondlevel);
            $excel->thirdlevel = ucfirst($thirdlevel);

            $excel->secondleveldata = $excel->$secondlevel;
            $excel->thirdleveldata = $excel->$thirdlevel;
            $exceldepartment =core_text::strtolower($excel->$secondlevel);
            if($exceldepartment != 'all'){
                $department_shortname = core_text::strtolower($excel->$secondlevel);
                $departmentinfo = $DB->get_record_sql("SELECT * FROM {local_costcenter} WHERE lower(shortname) = '$department_shortname'");
               }

            if ( array_key_exists('department', (array)$excel) ) {
                // if (empty($excel->department)) {
                //     echo '<div class="local_employees_sync_error">'.get_string('department_emptymsg','local_users', $excel).'</div>';
                //     $this->errors[] = get_string('fhngfd','local_users', $excel);
                //     $this->mfields[] = 'department';
                //     $this->errorcount++;
                // } else 
                // if (empty($departmentinfo) && strtolower($departmentinfo->shortname) != $department_shortname) {
                //     echo '<div class="local_employees_sync_error">'.get_string('wrong_department','local_users', $excel).'</div>';
                //     $this->errors[] = get_string('wrong_department','local_users', $excel);
                //     $this->mfields[] = $secondlevel;
                //     $this->errorcount++;
                // }

            // } else {
            //     echo '<div class="local_employees_sync_error">'.get_string('fvzdz', 'local_users').'</div>';
            //     $this->errormessage = get_string('hjnhnh', 'local_users');
            //     $this->errorcount++;
            }

            //check subdepartment
            $excelsubdepartment =core_text::strtolower($excel->$thirdlevel);
            if($excelsubdepartment != 'all'){
                $department_shortname = core_text::strtolower($excel->$thirdlevel);
                $departmentinfo = $DB->get_record_sql("SELECT * FROM {local_costcenter} WHERE lower(shortname) = '$department_shortname'");
               }

            if ( array_key_exists($thirdlevel, (array)$excel) ) {
                // if (empty($excel->subdepartment)) {
                //     echo '<div class="local_employees_sync_error">'.get_string('subdepartment_emptymsg','local_users', $excel).'</div>';
                //     $this->errors[] = get_string('fhngfd','local_users', $excel);
                //     $this->mfields[] = 'subdepartment';
                //     $this->errorcount++;
                // } else 
                // if (empty($departmentinfo) && strtolower($departmentinfo->shortname) != $department_shortname) {
                //     echo '<div class="local_employees_sync_error">'.get_string('wrong_subdepartment','local_users', $excel).'</div>';
                //     $this->errors[] = get_string('wrong_subdepartment','local_users', $excel);
                //     $this->mfields[] = $thirdlevel;
                //     $this->errorcount++;
                // }

            // } else {
            //     echo '<div class="local_employees_sync_error">'.get_string('fvzdz', 'local_users').'</div>';
            //     $this->errormessage = get_string('hjnhnh', 'local_users');
            //     $this->errorcount++;
            }
            // check department and subdepartment dependency's
            if(empty($costcenter_shortname)){
                $varorg = $USER->open_costcenterid;
            } else{
                $varorg = $DB->get_field('local_costcenter','id', array('shortname' => $costcenter_shortname));
            }
            $varorg1 = $DB->get_field('local_costcenter','parentid', array('shortname' => $exceldepartment));
            $vardepart = $DB->get_field('local_costcenter','id',array('shortname' => $exceldepartment, 'parentid' => $varorg));
            $varsubdepart = $DB->get_field('local_costcenter','id',array('shortname' => $excelsubdepartment, 'parentid' => $vardepart));
            if(is_siteadmin() || has_capability('local/costcenter:manage_multiorganizations', $systemcontext)){
                if($varorg != $varorg1) {
                    if(empty($vardepart) && $exceldepartment){
                        $stringhelper = new stdClass();
                        $stringhelper->linenumber = $this->excel_line_number;
                        $stringhelper->department = $exceldepartment;
                        $error_string = get_string('departmentisnotexists', 'local_employees', $excel);
                        echo "<div class='local_users_sync_error'>".$error_string.".</div>";
                        $this->errors[] = $error_string;
                        $this->mfields[] = $secondlevel;
                        $this->errorcount++;
                    }
                    if(empty($varsubdepart) && $excelsubdepartment){
                        $stringhelper = new stdClass();
                        $stringhelper->linenumber = $this->excel_line_number;
                        $stringhelper->subdepartment = $excelsubdepartment;
                        $error_string = get_string('subdepartmentisnotexists', 'local_employees', $excel);
                        echo "<div class='local_users_sync_error'>".$error_string.".</div>";
                        $this->errors[] = $error_string;
                        $this->mfields[] = $thirdlevel;
                        $this->errorcount++;
                    }
                } else {
                     if(empty($vardepart) && $exceldepartment){
                        if (empty($departmentinfo) && strtolower($departmentinfo->shortname) != $department_shortname) {
                            echo '<div class="local_employees_sync_error">'.get_string('wrong_department','local_users', $excel).'</div>';
                            $this->errors[] = get_string('wrong_department','local_users', $excel);
                            $this->mfields[] = $secondlevel;
                            $this->errorcount++;
                        }
                    }
                    if(empty($varsubdepart) && $excelsubdepartment){
                        if (empty($departmentinfo) && strtolower($departmentinfo->shortname) != $department_shortname) {
                            echo '<div class="local_employees_sync_error">'.get_string('wrong_subdepartment','local_users', $excel).'</div>';
                            $this->errors[] = get_string('wrong_subdepartment','local_users', $excel);
                            $this->mfields[] = $thirdlevel;
                            $this->errorcount++;
                        }
                    }
                }
            }
            if(!is_siteadmin() && !has_capability('local/costcenter:manage_multiorganizations', $systemcontext) && has_capability('local/costcenter:manage_ownorganization', $systemcontext)){   
                if($varorg != $varorg1) {
                    if(empty($vardepart) && $exceldepartment){
                        $stringhelper = new stdClass();
                        $stringhelper->linenumber = $this->excel_line_number;
                        $stringhelper->department = $exceldepartment;
                        $error_string = get_string('departmentisnotexists', 'local_employees', $excel);
                        echo "<div class='local_users_sync_error'>".$error_string.".</div>";
                        $this->errors[] = $error_string;
                        $this->mfields[] = $secondlevel;
                        $this->errorcount++;
                    }
                    if(empty($varsubdepart) && $excelsubdepartment){
                        $stringhelper = new stdClass();
                        $stringhelper->linenumber = $this->excel_line_number;
                        $stringhelper->subdepartment = $excelsubdepartment;
                        $error_string = get_string('subdepartmentisnotexists', 'local_employees', $excel);
                        echo "<div class='local_users_sync_error'>".$error_string.".</div>";
                        $this->errors[] = $error_string;
                        $this->mfields[] = $thirdlevel;
                        $this->errorcount++;
                    }
                }
                else {
                     if(empty($vardepart) && $exceldepartment){
                        if (empty($departmentinfo) && strtolower($departmentinfo->shortname) != $department_shortname) {
                            echo '<div class="local_employees_sync_error">'.get_string('wrong_department','local_users', $excel).'</div>';
                            $this->errors[] = get_string('wrong_department','local_users', $excel);
                            $this->mfields[] = $secondlevel;
                            $this->errorcount++;
                        }
                    }
                    if(empty($varsubdepart) && $excelsubdepartment){
                        if (empty($departmentinfo) && strtolower($departmentinfo->shortname) != $department_shortname) {
                            echo '<div class="local_employees_sync_error">'.get_string('wrong_subdepartment','local_users', $excel).'</div>';
                            $this->errors[] = get_string('wrong_subdepartment','local_users', $excel);
                            $this->mfields[] = $thirdlevel;
                            $this->errorcount++;
                        }
                    }
                }
            }
            if(!is_siteadmin() && !has_capability('local/costcenter:manage_multiorganizations', $systemcontext) && !has_capability('local/costcenter:manage_ownorganization', $systemcontext) && has_capability('local/costcenter:manage_owndepartments', $systemcontext)){   
                if($varorg != $varorg1) {
                    if(empty($varsubdepart) && $excelsubdepartment){
                        $stringhelper = new stdClass();
                        $stringhelper->linenumber = $this->excel_line_number;
                        $stringhelper->subdepartment = $excelsubdepartment;
                        $error_string = get_string('subdepartmentisnotexists', 'local_employees', $excel);
                        echo "<div class='local_users_sync_error'>".$error_string.".</div>";
                        $this->errors[] = $error_string;
                        $this->mfields[] = $thirdlevel;
                        $this->errorcount++;
                    }
                } else {
                    if(empty($varsubdepart) && $excelsubdepartment){
                        if (empty($departmentinfo) && strtolower($departmentinfo->shortname) != $department_shortname) {
                            echo '<div class="local_employees_sync_error">'.get_string('wrong_subdepartment','local_users', $excel).'</div>';
                            $this->errors[] = get_string('wrong_subdepartment','local_users', $excel);
                            $this->mfields[] = $thirdlevel;
                            $this->errorcount++;
                        }
                    }
                }
            }
            //check rollno 
             if ( array_key_exists('rollno', (array)$excel) ) {
                if (empty($excel->rollno)) {
                    echo '<div class="local_users_sync_error">'.get_string('rollno_emptymsg','local_users', $excel).'</div>'; 
                    $this->errors[] = get_string('rollno_emptymsg','local_users', $excel);
                    $this->mfields[] = 'rollno';
                    $this->errorcount++;
                } 
                else if (strrpos($excel->rollno, ' ') !== false) {
                    echo '<div class=local_employees_sync_error>Spaces are not allowed in rollno for  student id "' . $excel->employee_id . '" of uploaded excelsheet at line '.$this->excel_line_number.'.</div>';
                    $this->errors[] = 'Provide rollno for  student id "' . $excel->employee_id . '" of uploaded excelsheet at line '.$this->excel_line_number.'.';
                    $this->mfields[] = 'rollno';
                    $this->errorcount++;
                }
                else{
                    $var = $DB->get_field('user','id', array('email' => $excel->email));
                    $tableuserid = $DB->get_field('user','id', array('rollno' => $excel->rollno, 'deleted'=>0));
                    if($var != $tableuserid){
                        $stringhelper = new stdClass();
                        $stringhelper->linenumber = $this->excel_line_number;
                        $stringhelper->rollno = $excel->rollno;
                        $error_string = get_string('cannotcreateuserrollnoadderror', 'local_users', $stringhelper);
                        echo "<div class='local_users_sync_error'>".$error_string.".</div>";
                        $this->errors[] = $error_string;
                        $this->mfields[] = 'rollno';
                        $this->errorcount++;
                    }
                }
            }
    } // end of required_fields_validations function
    
    /**
     * @param   $excel [<data in excel or csv uploaded>] for validation
     */
    private function to_get_the_username_employeeid($excel,$option=0){                
        global $CFG, $DB;
        if($excel->username){
            if(($excel->username)){
                     $this->username = strtolower($excel->username);
            } else {
                $strings = new stdClass();
                $strings->employee_id = $excel->employee_id;
                $strings->excel_line_number = $this->excel_line_number;

                echo '<div class="local_users_sync_error">'.get_string('invalidusername_error', 'local_users', $strings).'</div>';
                $this->errors[] = get_string('invalidusername_error', 'local_users', $strings);
                $this->mfields[] = 'username';
                $this->errorcount++;
            }
            $sql = "SELECT id,open_employeeid FROM {user} WHERE username = :username AND deleted = 0";
            $user_object = $DB->get_record_sql($sql, array('username' => $this->username));
            if($option == ONLY_ADD){
                if($user_object){
                    $stringhelper = new stdClass();
                    $stringhelper->linenumber = $this->excel_line_number;
                    $stringhelper->username = $this->username;
                    $error_string = get_string('cannotcreateuserusernameadderror', 'local_users', $stringhelper);
                    echo "<div class='local_users_sync_error'>".$error_string.".</div>";
                    $this->errors[] = $error_string;
                    $this->mfields[] = 'username';
                    $this->errorcount++;
                }
            }else if($option == ONLY_UPDATE || $option == ADD_UPDATE){
                if(!empty($excel->employee_id) && !empty($excel->username)){
                    $exist_sql = "SELECT id FROM {user} 
                                WHERE (username = :username 
                                OR open_employeeid = :employeeid)
                                AND deleted = 0";
                    $users_exist = $DB->get_records_sql_menu($exist_sql, array('username' => strtolower($excel->username) ,'email' => strtolower($excel->email), 'employeeid' => $excel->employee_id));
                    $cexist_users = count($users_exist);
                }
                if($user_object){
                    if(!($user_object->open_employeeid == $excel->employee_id) && $cexist_users > 1){ 
                        $error_string = get_string('multipleedituserusernameediterror','local_users',$this->username);
                        $error_field = 'username';
                        echo "<div class='local_users_sync_error'>".$error_string.".</div>";
                        $this->errors[] = $error_string;
                        $this->mfields[] = $error_field;
                        $this->errorcount++;
                    }
                }
            }
            // if($option == ONLY_UPDATE){
            //     if(!$user_object){
            //         echo "<div class='local_users_sync_error'>".get_string('cannotedituserusernameediterror', 'local_users',$this->username).".</div>";
            //         $this->errors[] = get_string('cannotedituserusernameediterror', 'local_users',$this->username);
            //         $this->mfields[] = 'username';
            //         $this->errorcount++;
            //     }
            // }
        }else{
            $strings = new stdClass();
            $strings->employee_id = $excel->employee_id;
            $strings->excel_line_number = $this->excel_line_number;
            echo '<div class="local_users_sync_error">'.get_string('usernameempty_error', 'local_users', $strings).'</div>';
            $this->errors[] = get_string('usernameempty_error', 'local_users', $strings);
            $this->mfields[] = 'username_notexist';
            $this->errorcount++;
        }
    } // end of function to_ge_the_username_employeeid
    
    /**
     * @param   $excel [<data in excel or csv uploaded>] for validation
     */
    private function student_status_validation($excel){
        // check employeestatus
        $this->deletestatus = 0;
        if (array_key_exists('student_status', (array)$excel)) {
            if (empty($excel->student_status)) {
                echo '<div class=local_users_sync_error>Provide student status for  student id "' . $excel->employee_id . '" of uploaded excelsheet at line '.$this->excel_line_number.'.</div>';
                $this->errors[] = 'Provide student status for  employee id "' . $excel->employee_id . '" of uploaded excelsheet at line '.$this->excel_line_number.'.';
                $this->mfields[] = 'student_status';
                $this->errorcount++;
            } else if (strrpos($excel->student_status, ' ') !== false) {
                echo '<div class=local_employees_sync_error>Spaces are not allowed in student status for  student id "' . $excel->employee_id . '" of uploaded excelsheet at line '.$this->excel_line_number.'.</div>';
                $this->errors[] = 'Provide student status for  student id "' . $excel->employee_id . '" of uploaded excelsheet at line '.$this->excel_line_number.'.';
                $this->mfields[] = 'student_status';
                $this->errorcount++;
            } else {
                if (strtolower($excel->student_status) == 'active') {
                    $this->activestatus = 0;
                } elseif ( strtolower($excel->student_status) == 'inactive' ) {
                    $this->activestatus = 1;
                } elseif ( strtolower($excel->student_status) == 'delete' ) {
                    $this->deletestatus = 1;
                } else if(strtolower($excel->student_status) != 'active' || strtolower($excel->student_status) != 'inactive' || strtolower($excel->student_status) != 'delete'){
                    echo '<div class=local_users_sync_error>Provide student status for  student id "' . $excel->employee_id . '" of uploaded excelsheet at line '.$this->excel_line_number.'.</div>';
                    $this->errors[] = 'Provide student status for  employee id "' . $excel->employee_id . '" of uploaded excelsheet at line '.$this->excel_line_number.'.';
                    $this->mfields[] = 'student_status';
                    $this->errorcount++;
                    // $this->activestatus = 0;
                }
            }
        } else {
            echo '<div class="local_employees_sync_error">'.get_string('error_status_column_heading', 'local_employees').'</div>';
                $this->errormessage = get_string('error_status_column_heading', 'local_employees');
            echo '<div class=local_users_sync_error>Error in arrangement of columns in uploaded excelsheet at line '.$this->excel_line_number.'</div>';
            $this->errormessage = 'Error in arrangement of columns in uploaded excelsheet at line '.$this->excel_line_number.'.';
            $this->errorcount++;
        }        
    } // end of  student_status_validation method
    
    /**
     * [force_password_change_validation description]
     * @param  [type] $excel [description]
     */
    // private function force_password_change_validation($excel){
    //     $this->force_password_change = $excel->force_password_change;
    //     if(!is_numeric($this->force_password_change) || !(($this->force_password_change == 1) || ($this->force_password_change == 0))){
    //         echo '<div class=local_users_sync_error>force_password_change column should have value as 0 or 1 at line '.$this->excel_line_number.'</div>';
    //         $this->errors[] = 'force_password_change column should value as 0 or 1 at line '.$this->excel_line_number.'';
    //         $this->mfields[] = 'force_password_change';
    //         $this->errorcount++;
    //     }
    // }

    /**
     * @param   $excel [<data in excel or csv uploaded>] for validation
     */
    private function specific_costcenter_validation($excel,$option = 0){
        global $DB; $flag=0;
               $costcenter_shortname= core_text::strtolower($excel->organization);
   
        if (!$DB->record_exists('user', array('open_employeeid'=> $excel->employee_id))) {
            if($DB->get_record('user', array('username'=>  $this->username))){
                if($option==0){
                   echo '<div class=local_users_sync_error>username for  employee id "' . $excel->employee_id . '" of uploaded excelsheet is already exists  in the system</div>';
                   $this->errors[] = 'username for  employee id "' . $excel->employee_id . '" of uploaded excelsheet is already exists in the system at line '.$this->excel_line_number.'.';
                   $this->mfields[] = 'username';
                   $this->errorcount++;
                  $flag=1;
                  return $flag;
                }
            }

        /******To Check Employee id already exist with costcenter a employee id can be there with other costcenter****/
        $sql="select u.id,u.open_costcenterid from {user} u where u.open_employeeid='".$excel->employee_id."'";
        $open_costcenterid='';
        $employecodevalidation=$DB->get_record_sql($sql);
        $excel_costcenter=$this->open_costcenterid;
        $id_costcenter='';
        if($employecodevalidation){
            $id_costcenter=$employecodevalidation->open_costcenterid;
        }
        if($id_costcenter==$excel_costcenter){
            if($option==0){
                /*****Here we check and throw the error of employee id****/
                echo '<div class=local_users_sync_error>Employee code for  employee id "' . $excel->employee_id . '" of uploaded excelsheet is already under this organization</div>';
                $this->errors[] = 'username for  employee id "' . $excel->employee_id . '" of uploaded excelsheet is already exists in the system at line '.$this->excel_line_number.'.';
                $this->mfields[] = 'username';
                $flag=1;
                $this->errorcount++;
            }
        }
    }
    return $flag;      
    } //end of specific_costcenter_validation
    
    /* method  get_departmentid
     * used to get the department(costcenter) id
     * @param : $field string (excel field name)
     * @param : $parentid int
     * @param : $excel object it holds single row
     * @param : $classmember 
     * @return : int department id  
    */
    private function get_departmentid($field, $parentid, $excel, $classmember){
        global $DB, $USER;
   
        if ( array_key_exists($field, $excel) ) {
            if ( !empty($excel->$field ) ) {
                $dep = trim($excel->$field);
                $dep =strtolower($dep);
                if($field == "department"){
                   $head = get_string('organisation', 'local_costcenter');
                   $parent_name = $excel->organization; 
                }
                else if($field == "subdepartment"){
                    $head = get_string('department', 'local_users');
                    $parent_name = $excel->department;
                }
                $dep=str_replace("\n", "", $dep);

                $departmentname = $DB->get_record_sql("SELECT * from {local_costcenter} where lower(shortname) = '$dep' AND parentid= $parentid");      
                  
                if (empty($departmentname)) {     
                    echo '<div class=local_users_sync_error>'.ucfirst($field).' "'.$dep.'"for employee id "'.$excel->employee_id.'" in uploaded excelsheet does not exist under '.$head.' '.$parent_name.' at line '.$this->excel_line_number.'.</div>';
                    $this->errors[] = ucfirst($field).' "'.$dep.'"for employee id "'.$excel->employee_id.'" in uploaded excelsheet does not exist under '.$head.' '.$parent_name.' at line '.$this->excel_line_number.'.';
                    $this->mfields[] = $field;
                    $this->errorcount++;
                    $this->$classmember = null;              
                } else {
                    $this->$classmember = $departmentname->id;
                }
            }  
        }

    } // end of  get_departmentid method
    
    private function preparing_user_object($excel, $formdata=null){
        global $USER, $DB, $CFG;
        $user = new \stdclass();
        $labelstring = get_config('local_costcenter');
        $firstlevel = strtolower($labelstring->firstlevel);
        $secondlevel = strtolower($labelstring->secondlevel);
        $thirdlevel = strtolower($labelstring->thirdlevel);

        $user->suspended = $this->activestatus;

        $user->idnumber = $this->employee_id;
        $user->open_employeeid = $excel->employee_id;
        $user->username = strtolower($this->username);
        
        $user->firstname = $excel->first_name;
        $user->lastname = $excel->last_name;
        $user->email = strtolower($excel->email);
        $user->country = 'IN';
        $user->student_status = $excel->student_status;
        //----costcenter and department info -----
        // $user->open_subdepartment = $this->level2_departmentid;
        $user->department = $excel->$secondlevel;
        $user->open_costcenterid = $DB->get_field('local_costcenter', 'id', array('shortname' => $excel->$firstlevel));
        $user->open_departmentid = $DB->get_field('local_costcenter', 'id', array('shortname' => $excel->$secondlevel));
        $user->open_subdepartment = $DB->get_field('local_costcenter', 'id', array('shortname' => $excel->$thirdlevel));
        $user->usermodified = $USER->id;
        $user->open_type = 1;
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
        $user->force_password_change = (empty($excel->force_password_change)) ? 0: $excel->force_password_change;
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
    } // end of function
     
    private function add_newuser_instance_fromhrmssync($excel, $user){
        global $DB, $USER;
        //--------Insertion part-------
        $systemcontext = \context_system::instance();
        $labelstring = get_config('local_costcenter');
        $firstlevel = strtolower($labelstring->firstlevel);
        $secondlevel = strtolower($labelstring->secondlevel);
        $thirdlevel = strtolower($labelstring->thirdlevel);
        $user->timecreated = time();
        $user->timemodified = time();
        $user->mnethostid = 1;
        $user->phone1 = $excel->phone;
        $user->city = $excel->city;
        $user->address = $excel->address;
        $user->open_departmentid = 0;
        $user->open_subdepartment = 0;
        $user->rollno = $excel->rollno;
        $user->confirmed = 1;
        $user->auth = 'email';
        $user->open_departmentid = $DB->get_field('local_costcenter', 'id', array('shortname' => $excel->$secondlevel));
        $user->open_subdepartment = $DB->get_field('local_costcenter', 'id', array('shortname' => $excel->thirdleveldata));
        if(empty(trim($user->password))){
            if($this->formdata->createpassword){
                $user->createpassword = $this->formdata->createpassword;
            }else{
                $user->password = hash_internal_user_password("Welcome#3");
            }
        }
        if(is_siteadmin() || has_capability('local/costcenter:manage_multiorganizations', $systemcontext)){
            $user->open_costcenterid = $DB->get_field('local_costcenter', 'id', array('shortname' => $excel->$firstlevel));
        } else if(!is_siteadmin() && !has_capability('local/costcenter:manage_multiorganizations', $systemcontext) && has_capability('local/costcenter:manage_ownorganization', $systemcontext)){
                $user->open_costcenterid = $USER->open_costcenterid;
                // $user->open_departmentid = $USER->open_departmentid;
        } else if(!is_siteadmin() && !has_capability('local/costcenter:manage_multiorganizations', $systemcontext) && !has_capability('local/costcenter:manage_ownorganization', $systemcontext) && has_capability('local/costcenter:manage_owndepartments', $systemcontext)){
                $user->open_costcenterid = $USER->open_costcenterid;
                $user->open_departmentid = $USER->open_departmentid;
                $departmentname = $DB->get_field('local_costcenter', 'shortname', ['id' => $user->open_departmentid]);
                $user->department = $departmentname;
        }
        $id  = user_create_user($user, false);
        if ($user->force_password_change == 1)
            set_user_preference('auth_forcepasswordchange', $user->force_password_change, $id);
         if ($this->formdata->createpassword){
            
           $usernew = $DB->get_record('user', array('id' => $id));
            setnew_password_and_mail($usernew);
            unset_user_preference('create_password', $usernew);
            set_user_preference('auth_forcepasswordchange', 1, $usernew);   
         }     
        $cuser = $DB->get_record('user', ['id'=>$id]);
        send_confirmation_email($cuser);
        $this->insertedcount++;
    } // end of add_newuser_instance
    
    private function add_rows($excel,$formdata){
        global $DB, $USER, $CFG;
        $user=$this->preparing_user_object($excel,$formdata);
        
        $sql = "SELECT id FROM {user} WHERE (username = :username OR open_employeeid = :employeeid) AND deleted = 0";

        $userexist = $DB->get_record_sql($sql , array('username' => $user->username, 'employeeid'=>$user->open_employeeid));
        if($userexist){
            if($DB->record_exists('user',array('open_employeeid'=>$user->open_employeeid,'deleted' => 0))){
                $field = 'open_employeeid';
                $fieldvalue = $user->open_employeeid;
                echo "<div class='local_users_sync_error'>User with ".$field." ".$fieldvalue." already exist at line $this->excel_line_number.</div>";
                $this->errors[] = "User with ".$field." ".$fieldvalue." already exist at line $this->excel_line_number.";
                $this->mfields[] = $field;
                $this->errorcount++;
            }
        }//ended
        if(empty($userexist)){        
            $this->add_newuser_instance_fromhrmssync($excel, $user);
        }
    } // end of add_rows function   
    
    private function add_update_rows($excel){
        global $DB, $USER;
        // add or update information       
        $user=$this->preparing_user_object($excel);
        $user_sql = "SELECT id  FROM {user} WHERE (username = :username  OR open_employeeid = :employeeid) AND deleted = 0"; 
        $user_object = $DB->get_record_sql($user_sql,  array('username' => $user->username, 'employeeid' => $user->open_employeeid));
        if ($user_object) {
            //-----Update functionality--------
            $this->update_rows($excel, $user);                       
            } else{              
                $err=$this->specific_costcenter_validation($user);
                if(!$err)             
                    $this->add_newuser_instance_fromhrmssync($excel, $user);            
        } // end of else
        
    } // end of add_update_rows method

    public function update_rows($excel, $user){
        global $USER, $DB, $CFG;
        //---------Updation part-------
        $systemcontext = \context_system::instance();
        $labelstring = get_config('local_costcenter');
        $firstlevel = strtolower($labelstring->firstlevel);
        $secondlevel = strtolower($labelstring->secondlevel);
        $thirdlevel = strtolower($labelstring->thirdlevel);
        //-----if user exists updating user record 
        $user_sql = "SELECT username,id FROM {user} WHERE (username = :username OR open_employeeid = :employeeid) AND deleted = 0"; 
        $user_object = $DB->get_records_sql_menu($user_sql,  array('username' => $excel->username, 'employeeid' => $excel->employee_id));
        // $department =$DB->get_field('user','open_departmentid',array('username' => $user->username));
        $subdepartment =$DB->get_field('user','open_subdepartment',array('username' => $user->username));
        if(count($user_object) == 1){
            $userid=$user_object[$user->username];
            if($userid){ 
                $user->id = $userid;
                $user->timemodified = time();
                $user->suspended = $this->activestatus;
                $user->idnumber = $excel->employee_id;
            if(isset($user->open_costcenterid)){
              $existingcostcenter = $DB->get_field('user', 'open_costcenterid', array('id' => $user->id));
                if($user->open_costcenterid != $existingcostcenter){
                \core\session\manager::kill_user_sessions($user->id);
                }
            }
                // $user->open_departmentid = $department;
                // $user->open_subdepartment = $subdepartment;
                $user->department = $excel->$secondlevel;
                $user->institution = $DB->get_field('local_costcenter', 'fullname', array('id' => $this->level1_departmentid));
                $user->open_costcenterid = $DB->get_field('local_costcenter', 'id', array('shortname' =>$excel->$firstlevel));

                if(is_siteadmin() || has_capability('local/costcenter:manage_multiorganizations', $systemcontext)){
                    $user->open_costcenterid = $DB->get_field('local_costcenter', 'id', array('shortname' => $excel->$firstlevel));
                } else if(!is_siteadmin() && !has_capability('local/costcenter:manage_multiorganizations', $systemcontext) && has_capability('local/costcenter:manage_ownorganization', $systemcontext)){
                    $user->open_costcenterid = $USER->open_costcenterid;
                    // $user->open_departmentid = $USER->open_departmentid;
                } else if(!is_siteadmin() && !has_capability('local/costcenter:manage_multiorganizations', $systemcontext) && !has_capability('local/costcenter:manage_ownorganization', $systemcontext) && has_capability('local/costcenter:manage_owndepartments', $systemcontext)){
                    $user->open_costcenterid = $USER->open_costcenterid;
                    $user->open_departmentid = $USER->open_departmentid;
                    $departmentname = $DB->get_field('local_costcenter', 'shortname', ['id' => $user->open_departmentid]);
                    $user->department = $departmentname;
                }

                $user->usermodified = $USER->id;
                $user->open_type = 1;
                $user->confirmed = 1;
                $user->phone1 = $excel->phone;
                $user->rollno = $excel->rollno;
                $user->city = $excel->city;
                $user->address = $excel->address;
                if(!empty($excel->password)){
                    $user->password = hash_internal_user_password($excel->password);
                }else{
                    unset($user->password);
                }
                if($this->deletestatus == 1){
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
                if ($user->force_password_change == 1)
                set_user_preference('auth_forcepasswordchange', $user->force_password_change, $user->id);

                $this->updatedcount++;
            }
        }
    } // end of  update_rows method
    
    private function update_supervisorid($data){
        global $DB;      
       
            $this->warnings = array();
            $this->mfields = array();
            $this->wmfields = array();
            $linenum = 1;
         // supervisor id check after creating all users
        foreach($data as $excel){
            $linenum++;
            if(!is_object($excel))
                $excel = (object)$excel;
            
            //---to get the costcenter shortname------
            if(!empty($excel->organization)){
                $this->costcenter_shortname = $excel->organization;
            }
            
            $this->employee_id = $excel->employee_id;
            $excel->reportingmanager_empid='';
            if($excel->reportingmanager_empid!=''){
                $costcenter = $DB->get_field('user', 'open_costcenterid', array('username' => $excel->username));           
                $super_userid = $DB->get_record('user', array('open_employeeid' => $excel->reportingmanager_empid, 'open_costcenterid' => $costcenter));
                if($super_userid){
                    $user_exist = $DB->record_exists('user', array('open_employeeid'=> $this->employee_id));
                    if ($user_exist) {
                        $userid = $DB->get_field('user', 'id', array('open_employeeid'=>$this->employee_id));
                        $local_user = $DB->get_record('user', array('id'=>$userid));          
                        $local_user->open_supervisorempid = $super_userid->open_employeeid;
                        $local_user->open_supervisorid=$super_userid->id;
                       
                        if(!empty($local_user->id)){
                            $data=$DB->update_record('user', $local_user); 
                        }
                    }
                }else{
                    
                    $strings = new \stdClass();
                    $strings->empid = $excel->reportingmanager_empid;
                    $strings->line = $linenum;
                    $warningmessage = get_string('nosupervisorempidfound','local_users',$strings);
                    $this->errormessage = $warningmessage;
                    echo '<div class=local_users_sync_warning>'.$warningmessage.'</div>';
                    $this->warningscount++; 
                }
            }   
        }
    } // end of  update_supervisorid method
}  // end of class


