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

namespace local_users\local;    
use html_writer;
class user{
	public function user_profile_content($id,$return = false,$start =0,$limit=5){
        global $OUTPUT,$PAGE,$CFG,$DB;
        require_once($CFG->dirroot.'/course/renderer.php');
        require_once($CFG->libdir . '/badgeslib.php');

        $returnobj = new \stdClass();
        $returnobj->divid = 'user_profile';
        $returnobj->string = get_string('profile', 'local_users');
        $returnobj->moduletype = 'users';
        $returnobj->targetID = 'display_users';
        $returnobj->userid = $id;
        $returnobj->count = 1;
        $returnobj->usersexist = 1;
        
        $systemcontext = \context_system::instance();
        $userrecord = $DB->get_record('user', array('id' => $id));
    
        /*user roles*/
        $userroles = get_user_roles($systemcontext, $id);
        if(!empty($userroles)){
                $rolename  = array();
                foreach($userroles as $roles) {
                    $rolename[] = ucfirst($roles->name);
                }
                $roleinfo = implode(", ",$rolename);
        } else {
            $roleinfo = get_string('employee', 'local_users');
        }
        $sql3 = "SELECT cc.fullname, u.open_employeeid,
                u.open_costcenterid,u.open_supervisorid,
                u.department, u.open_subdepartment ,
                u.open_departmentid       
                FROM {local_costcenter} cc, {user} u
                WHERE u.id=:id AND u.open_costcenterid=cc.id";
        $userOrg = $DB->get_record_sql($sql3, array('id' => $id));
        $usercostcenter = $DB->get_field('local_costcenter', 'fullname',  array('id' => $userOrg->open_costcenterid));
        if(!empty($userrecord->phone1)){
                $contact = $userrecord->phone1;
        }else{
                $contact = 'N/A';
        }
        $get_reporting_username = '';
        $badgimg = '';
        $certimg = '';
        if(!empty($userOrg->open_supervisorid)){
            $get_reporting_username_sql = "SELECT u.id, u.firstname, u.lastname, u.open_employeeid FROM {user} as u WHERE  u.id= :open_supervisorid";
                $get_reporting_username = $DB->get_record_sql($get_reporting_username_sql , array('open_supervisorid' => $userOrg->open_supervisorid));
                $reporting_to_empid = $get_reporting_username->serviceid != NULL ? ' ('.$get_reporting_username->open_employeeid.')' : 'N/A';
                $reporting_username = $get_reporting_username->firstname.' '.$get_reporting_username->lastname;
        }else{
                $reporting_username = 'N/A';
        }
    if($get_reporting_username){
        $supervisorname = $get_reporting_username->firstname.' '.$get_reporting_username->lastname;
        
        $certiconimage = $OUTPUT->image_url('certicon','local_users');
        $certimg = $certiconimage->out_as_local_url();
    }
        $usersviewContext = [
            "userid" => $userrecord->id,
            "username" => fullname($userrecord),
            "rolename" => $roleinfo,
            "empid" => $userOrg->open_employeeid != NULL ? $userOrg->open_employeeid : 'N/A',
            "user_email" => $userrecord->email,
            "organisation" => $usercostcenter ? $usercostcenter : 'N/A', 
            "location" => $userrecord->city != NULL ? $userrecord->city : 'N/A',
            "address" => $userrecord->address != NULL ? $userrecord->address : 'N/A',
            "phnumber" => $contact,
            "certimg" => $certimg,
            "supervisorname" => $reporting_username,
        ];
        $data = array();
        $data[] = $usersviewContext;
        $returnobj->navdata = $data;  
        return $returnobj;
	}
}
