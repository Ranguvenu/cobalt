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

namespace local_admissions\local;
use \local_program\program as program;
require_once($CFG->dirroot.'/user/lib.php');

require_once($CFG->dirroot.'/course/lib.php');
require_once($CFG->dirroot.'/course/externallib.php');
require_once($CFG->dirroot . '/course/modlib.php');

use html_writer;
use moodle_url;
use context_system;
use tabobject;
use user_create_user;
use context_user;
use core_user;
use stdClass;

class lib {

    private static $_lib;
    private $dbHandle;
    public static function getInstance() {
        if (!self::$_lib) {
            self::$_lib = new lib();
        }
        return self::$_lib;
    }

    /**
     * get Uploaded Docs(zip file name) local users
     *
     * @param int $id id of the table local_users
     * @return array having list of all file names of the uploaded docs
     */
    public function get_uploadeddocslist($id){
        global $DB;

        $sql = "SELECT lu.id, f.filename
                FROM {local_users} lu 
                JOIN {files} f ON lu.uploaddocs = f.itemid
                WHERE f.contextid = :contextid AND f.component = :component 
                AND f.filearea = :filearea AND f.filename != :filename AND lu.id = {$id}
                ORDER BY f.filename ASC ";
        $context = context_system::instance();
        $docslist = $DB->get_records_sql_menu($sql, array('contextid'=>1, 'component'=>'local_admissions', 'filearea'=>'uploaddocs', 'filename'=>'.'));
            return $docslist;
    }

      /**
     * get Uploaded Docs(zip file path) Path
     *
     * @param int $id id of the table local_users
     * @return array having list of all file path, component, filearea, filename and itemid of the uploaded docs
     */
    public function get_uploadeddocument_src($id){
        global $DB;

        $badgeitemid = $DB->get_field('local_users','uploaddocs',array('id'=>$id));
        if($badgeitemid){
            $context = context_system::instance();

            $fs = get_file_storage();
            $files = $fs->get_area_files($context->id, 'local_admissions', 'uploaddocs', $badgeitemid, 'sortorder', false);
            if ($files) {
                foreach ($files as $file) {
                    $fileurl = \moodle_url::make_pluginfile_url
                                (
                                    $file->get_contextid(),
                                    $file->get_component(),
                                    $file->get_filearea(),
                                    $file->get_itemid(),
                                    $file->get_filepath(),
                                    $file->get_filename()
                                );
                        }
                return $fileurl->out();
            }else{
                return null;
            }
        }else{
            return null;
        }
    }

    public function admission_userinfo () {
        global $DB;
        $sql = "SELECT u.id as local_usersid, u.firstname, u.lastname, u.email, u.uploaddocs, u.revisecnt, la.admissionid, p.id as programid, p.name, u.status, u.registrationid 
                FROM {local_users} u 
                JOIN {local_admissions} la ON la.admissionid = u.id 
                JOIN {local_program} p ON u.programid = p.id 
                WHERE u.status = 0 ORDER BY u.id DESC";

        $admissionuserinfo = $DB->get_records_sql($sql);
        $userdocs = array();
        foreach($admissionuserinfo as $admissionuserinfor) {
            $docs = new stdClass();
            $docs->admissionid = $admissionuserinfor->admissionid;
            $docs->programid = $admissionuserinfor->programid;
            $docs->firstname = $admissionuserinfor->firstname;
            $docs->lastname = $admissionuserinfor->lastname;
            $docs->email = $admissionuserinfor->email;
            $docs->name = $admissionuserinfor->name;
            $docs->registrationid = $admissionuserinfor->registrationid;
            $docs->uploaddocs = $this->get_uploadeddocument_src($admissionuserinfor->local_usersid);

            if($admissionuserinfor->revisecnt >= 1) {
                $docs->revisecnt = 1;
            } else {
                $docs->revisecnt  = 0;
            }

            $userdocs[] = $docs;
        }
        return $userdocs;
    }

    public function count_admission_userinfo () {
        global $DB;

        $sql = "SELECT u.id as local_usersid, COUNT(DISTINCT u.id) as countuserinfo, u.firstname, u.lastname, u.email, u.uploaddocs, la.admissionid, p.id as programid, p.name, u.status 
                FROM {local_users} u 
                JOIN {local_admissions} la ON la.admissionid = u.id 
                JOIN {local_program} p ON u.programid = p.id 
                WHERE u.status = 0 ";

        $admissionuserinfo = $DB->get_records_sql($sql);
        return $admissionuserinfo;
    }

    public function programinfo() {
        global $DB;
        $programinfo = $DB->get_records_sql("SELECT p.id, p.name, c.name as cohortname FROM {local_program} p
                       JOIN {cohort} c ON p.batchid = c.id
                        ");
        return $programinfo;
    }

    public function count_programinfo() {
        global $DB;
        $countprograminfo = $DB->get_records_sql("SELECT p.id, p.name, c.name as cohortname, COUNT(c.id) as countprograminfo FROM {local_program} p
                       JOIN {cohort} c ON p.batchid = c.id
                        ");
        return $countprograminfo;
    }

    public function approved_userinfo () {
        global $DB;

        $sql =  "SELECT u.id as local_usersid, u.firstname, u.lastname, u.email, u.uploaddocs, la.admissionid, p.id as programid, p.name, u.status 
                FROM {local_users} u 
                JOIN {local_admissions} la ON la.admissionid = u.id 
                JOIN {local_program} p ON u.programid = p.id 
                WHERE u.status = 1 ORDER BY u.id DESC";
        
        $admissionuserinf = $DB->get_records_sql($sql);
        $admissionuserinfo = array();
        foreach($admissionuserinf as $admissionuserinfos) {
            $docs = new stdClass();
            $docs->admissionid = $admissionuserinfos->local_usersid;
            $docs->programid = $admissionuserinfos->programid;
            $docs->firstname = $admissionuserinfos->firstname;
            $docs->lastname = $admissionuserinfos->lastname;
            $docs->email = $admissionuserinfos->email;
            $docs->name = $admissionuserinfos->name;
            $docs->uploaddocs = $this->get_uploadeddocument_src($admissionuserinfos->local_usersid);
            // $docs->uploaddocs = $reviseuserinfor->local_usersid > 0 ? $this->get_uploadeddocument_src($reviseuserinfor->local_usersid) : 'NA';

            $admissionuserinfo[] = $docs;
        }
        return $admissionuserinfo;
    }

    public function count_approved_userinfo () {
        global $DB;

        $sql =  "SELECT u.id as local_usersid, COUNT(DISTINCT u.id) as approvedcount, u.firstname, u.lastname, u.email, u.uploaddocs, la.admissionid, p.id as programid, p.name, u.status 
                FROM {local_users} u 
                JOIN {local_admissions} la ON la.admissionid = u.id 
                JOIN {local_program} p ON u.programid = p.id 
                WHERE u.status = 1";

        $admissionuserinfo = $DB->get_records_sql($sql);
        return $admissionuserinfo;
    }

    public function rejected_userinfo () {
        global $DB;

        $sql =  "SELECT u.id as local_usersid, u.firstname, u.lastname, u.email, u.uploaddocs, la.admissionid, p.id as programid, p.name, u.status 
                FROM {local_users} u 
                JOIN {local_admissions} la ON la.admissionid = u.id 
                JOIN {local_program} p ON u.programid = p.id 
                WHERE u.status = 2 ORDER BY u.id DESC";

        $admissionuserinf = $DB->get_records_sql($sql);
        $admissionuserinfo = array();
        foreach($admissionuserinf as $admissionuserinfos) {
            $docs = new stdClass();
            $docs->id = $admissionuserinfos->local_usersid;
            $docs->programid = $admissionuserinfos->programid;
            $docs->firstname = $admissionuserinfos->firstname;
            $docs->lastname = $admissionuserinfos->lastname;
            $docs->email = $admissionuserinfos->email;
            $docs->name = $admissionuserinfos->name;
            $docs->uploaddocs = $this->get_uploadeddocument_src($admissionuserinfos->local_usersid);
            // $docs->uploaddocs = $reviseuserinfor->local_usersid > 0 ? $this->get_uploadeddocument_src($reviseuserinfor->local_usersid) : 'NA';

            $admissionuserinfo[] = $docs;
        }
        return $admissionuserinfo;
    }

    public function count_rejected_userinfo () {
        global $DB;

        $sql =  "SELECT u.id as local_usersid, COUNT(DISTINCT u.id) as rejectedcount, u.firstname, u.lastname, u.email, u.uploaddocs, la.admissionid, p.id as programid, p.name, u.status 
                FROM {local_users} u 
                JOIN {local_admissions} la ON la.admissionid = u.id 
                JOIN {local_program} p ON u.programid = p.id 
                WHERE u.status = 2";

        $countrejecteduserinfo = $DB->get_records_sql($sql);
       
        return $countrejecteduserinfo;
    }

    public function revise_userinfo () {
        global $DB;

        $sql =  "SELECT u.id as local_usersid, u.firstname, u.lastname, u.email, u.uploaddocs, u.reason,
                la.admissionid, p.id as programid, p.name, u.status, u.revisecnt 
                FROM {local_users} u 
                JOIN {local_admissions} la ON la.admissionid = u.id 
                JOIN {local_program} p ON u.programid = p.id 
                WHERE u.status = 3 GROUP BY u.id ORDER BY u.id DESC";

        $reviseuserinfo = $DB->get_records_sql($sql);
        foreach($reviseuserinfo as $reviseuserinfor) {
            $docs = new stdClass();
            $docs->admissionid = $reviseuserinfor->local_usersid;
            $docs->programid = $reviseuserinfor->programid;
            $docs->firstname = $reviseuserinfor->firstname;
            $docs->lastname = $reviseuserinfor->lastname;
            $docs->email = $reviseuserinfor->email;
            $docs->name = $reviseuserinfor->name;
            $docs->reason = $reviseuserinfor->reason;
            $docs->uploaddocs = $this->get_uploadeddocument_src($reviseuserinfor->local_usersid);
            if ($reviseuserinfor->revisecnt <= 1) {
                $docs->revisecnt = $reviseuserinfor->revisecnt;
            }else{
                $docs->cannotedit = 'N/A';
            }           
            // $docs->uploaddocs = $reviseuserinfor->local_usersid > 0 ? $this->get_uploadeddocument_src($reviseuserinfor->local_usersid) : 'NA';
            $userdocs[] = $docs;
        }
        return $userdocs;
    }

    public function count_revise_userinfo () {
        global $DB;
        $sql =  "SELECT u.id as local_usersid, COUNT(DISTINCT u.id) as revisedcount, u.firstname, u.lastname, u.email, u.uploaddocs, la.admissionid, p.id as programid, p.name, u.status 
                FROM {local_users} u 
                JOIN {local_admissions} la ON la.admissionid = u.id 
                JOIN {local_program} p ON u.programid = p.id 
                WHERE u.status = 3";

        $countreviseuserinfo = $DB->get_records_sql($sql);
        return $countreviseuserinfo;
    }


      /**
     * Insert form submitted data.
     *
     * @param int $dataobj inserted data of the table local_users
     * @return int having id of the local_users
     */
    public function save_admissions($dataobj){
        global $DB, $USER;

        $batchid = $DB->get_field('local_program', 'batchid', array('id'=>$dataobj->programid));
      
        $insertdata->userid = 0;
        $insertdata->programid = $dataobj->programid;
        $insertdata->batchid = $batchid;
        $insertdata->status = 0;
        $pid = array('id'=>$dataobj->id, 'programid'=>$insertdata->programid, 'batchid'=>$batchid);
        $acceptinfo = $DB->get_record('local_users', $pid, '*');
        
        $revisecnt = $DB->get_field('local_users', 'revisecnt', array('id' => $dataobj->id));
        
        if(!$revisecnt) {
            $cnt = 0;
        } else {
            $cnt = $revisecnt;
        }
    
        if($dataobj->id){
            $dataobj->revisecnt = $cnt;
            $dataobj->status = 0;
            $dataobj->usermodified = $USER->id;
            $dataobj->timemodified = time();
            $DB->update_record('local_users', $dataobj);
            $id = $dataobj->id;
        }else{
            // file_save_draft_area_files($dataobj->uploaddocs, 1, 'local_admissions', 'uploaddocs', $dataobj->uploaddocs);
            $dataobj->revisecnt = $cnt;
            $dataobj->usercreated = $USER->id;
            $dataobj->timecreated = time();
         
            $id = $DB->insert_record('local_users', $dataobj);
        }
        return $id;
    }

      /**
     * Insert form submitted data.
     *
     * @param int $dataobj inserted data of the table local_users
     * @return int having id of the local_users
     */
    public function save_educationaldetails($dataobj) {
        global $DB, $USER, $CFG;

        $context =  \context_system::instance();

        $dataobj->id = $this->save_admissions($dataobj);

        $localusers = $DB->get_record('local_users', array('id'=>$dataobj->id));

        $emaillogs = new \local_admissions\notification();
        $allow = true;
        $type = 'admission_create';
        $fromuserid = $USER->id;
    
        // $local_program = $DB->get_record('local_program', array('id' => $programid));
        $sql = "SELECT lu.id as admissionid, lu.registrationid, lu.email, lu.firstname, lu.lastname,  p.* 
        FROM {local_users} lu 
        JOIN {local_program} p ON p.id = lu.programid 
        WHERE lu.id = :admissionid AND lu.programid = :programid";

        $local_program = $DB->get_record_sql($sql, array('admissionid'=>$localusers->id, 'programid'=>$localusers->programid));
      
        // delete all previous levels which is not available in current submission
        $currentlevels = $dataobj->coursename;

        list($insql, $inparams) = $DB->get_in_or_equal($currentlevels, SQL_PARAMS_NAMED, 'coursename');
        $sql = "SELECT id 
                FROM {local_admissions} 
                WHERE coursename $insql AND admissionid =:admissionid ";

        $inparams['admissionid'] = $dataobj->id;
        $unwatedbages = $DB->get_fieldset_sql($sql, $inparams);
        // if($unwatedbages){
        //     foreach ($unwatedbages as $unwatedbage) {
        //         $DB->delete_records('local_admissions', array('id'=>$unwatedbage));
        //     }
        // }
        //     // insert/update levels for this user educational Details
   
        for($i = 0;($i < $dataobj->option_repeats); $i++){
            $insertdata = new \stdClass();
            $insertdata->admissionid = $dataobj->id;
            $insertdata->level = $dataobj->courseseq[$i];
            $insertdata->coursename = $dataobj->coursename[$i];
            $insertdata->university = $dataobj->university[$i];
            $insertdata->yearofpassing = $dataobj->yearofpassing[$i];
            $insertdata->percentage = $dataobj->percentage[$i];
           
            $params = array('admissionid'=>$dataobj->id, 'level'=>$insertdata->level);

            $prbadge = $DB->get_record('local_admissions', $params, '*');
            if($prbadge->id){
                $insertdata->id = $prbadge->id;
                $insertdata->timemodified = time();
                $insertdata->usermodified = $USER->id;
                $DB->update_record('local_admissions', $insertdata);
            }else{
                $insertdata->timecreated = time();
                $insertdata->usercreated = $USER->id;
                
                $insertdata->id = $DB->insert_record('local_admissions', $insertdata);

            }
        }

        // Local Admission Event for Application Receive.
        $params = array(
            'context' => context_system::instance(),
            'objectid' => $localusers->id,
            'userid' => $USER->id,
            'other' => array('programid' => $localusers->programid, 'email'=>$localusers->email)
        );

        $event = \local_admissions\event\status_create::create($params);
        $event->add_record_snapshot('local_users', $localusers);
        $event->trigger();

        $reject_record = $DB->get_record('local_users', array('id'=>$dataobj->id));

        $reject_records->firstname = $reject_record->firstname;
        $reject_records->lastname = $reject_record->lastname;
        $reject_records->registrationid = $reject_record->registrationid;
        $reject_records->program_name = $DB->get_field('local_program', 'name', array('id'=>$reject_record->programid));

        // Local Admission Event for Application Reject.

        $params = array(
                'context' => context_system::instance(),
                'objectid' => $reject_records->id,
                'userid' => $USER->id,
                'other' => array('programid' => $reject_records->programid, 'email'=>$reject_records->email)
            );

            $event = \local_admissions\event\status_reject::create($params);
            $event->add_record_snapshot('local_users', $reject_records);
            $event->trigger();

            // Sending an email to the user to check the application status.

            $datamailobj->body = "Dear <b>$reject_records->firstname  $reject_records->lastname</b>,
                                We have successfully received your application for entry into <b> $reject_records->program_name </b>
                                your apllication is now setting with our admission team for approval.<br>
                                We shall be in contact again with you shortly to approve your application to the program or to request further information relating to your application.<br>
                                check the status of your application clicking on link: <a>$CFG->wwwroot/local/admissions/status.php</a><br>
                                
                                Thanks<br>
                                Admission Team.";

             email_to_user($reject_record, $USER, 'Application Received', $datamailobj->body);

            if ($allow) {
                $touser = $local_program;
                $email_logs = $emaillogs->admission_notification($type, $touser, $fromuser = get_admin(), $local_program);
            }

        $clgname = $DB->get_record_sql("SELECT lc.shortname
                                         FROM {local_costcenter} lc
                                         JOIN {local_program} lp ON lc.id = lp.costcenter
                                        WHERE lp.id = $dataobj->programid");
        $collegename = strtoupper(substr($clgname->shortname, 0, 3));
        $year = substr(date("Y"), - 2);
        $idvaluelength = strlen($dataobj->id);
        if ($idvaluelength == 1) {
            $idvaluelength = "000".$dataobj->id;
        } else if ($idvaluelength == 2) {
            $idvaluelength = "00".$dataobj->id;
        } else if ($idvaluelength == 3) {
            $idvaluelength = "0".$dataobj->id;
        } else {
            $idvaluelength = $dataobj->id;
        }
        $registrationid = $year.$collegename.$idvaluelength;
        $dataobj->id = $dataobj->id;
        $dataobj->registrationid = $registrationid;
        $DB->update_record('local_users', $dataobj);
        
        return true;
    }

    public function user_info ($record) {
        global $DB;
        $params = array();
        $params['registrationid'] = $record->registrationid;
        $sql = "SELECT la.id, la.admissionid, u.dob, p.id as programid,
                CONCAT(u.firstname, ' ',u.lastname) as fullname, u.email, p.name,
                u.status, u.reason, u.revisecnt
                 FROM {local_admissions} la 
                 JOIN {local_users} u ON la.admissionid = u.id
                 JOIN {local_program} p ON u.programid = p.id 
                WHERE u.registrationid = :registrationid
                 GROUP BY la.id";
        $userinfo = $DB->get_record_sql($sql, $params);
        return $userinfo;
    }
# Admin Approvels.
public function admin_enroluser_to_program($id, $programid) {
    global $DB, $USER, $CFG;
    require_once($CFG->dirroot.'/course/lib.php');
    require_once($CFG->dirroot.'/course/externallib.php');
    require_once($CFG->dirroot . '/course/modlib.php');

    $emaillogs = new \local_admissions\notification();
    $pemaillogs = new \local_program\notification();
    $enroled_users = new program();

    $allow = true;
    $type = 'admission_approve';
    $ptype = 'program_enrol';
    $dataobj = $programid;
    $fromuserid = $USER->id;
    $pid = array('programid'=>$programid);

    $acceptinfo = $DB->get_record('local_users', $pid, '*');
    $sql = "SELECT lu.id as admissionid, lu.registrationid, lu.email, lu.firstname, lu.lastname, p.* 
    FROM {local_users} lu 
    JOIN {local_program} p ON p.id = lu.programid 
    WHERE lu.id = :admissionid AND lu.programid = :programid";

    $local_program = $DB->get_record_sql($sql, array('admissionid'=>$id, 'programid'=>$programid));

    $programenrolusers = "SELECT plc.courseid, lu.id, pl.id as levelid, lu.email, pl.programid, pl.level, pl.active, plc.mandatory, plc.parentid, c.fullname , plc.parentid 
            FROM {local_users} lu 
            JOIN {local_program_levels} pl ON pl.programid = lu.programid 
            JOIN {local_program_level_courses} plc ON plc.levelid = pl.id
            JOIN {course} c ON c.id = plc.courseid WHERE lu.id = :admissionid AND pl.programid = :programid AND plc.mandatory = :mandatory AND pl.active = 1";

    $enrolusers = $DB->get_records_sql($programenrolusers, array('admissionid'=>$id, 'programid'=>$programid, 'mandatory'=>1, 'active'=>1));
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%&*_";
    $password = substr(str_shuffle( $chars ), 0, 15 );
    $local_program->password =  $password;

    $aid = $DB->get_field('local_users', 'id', array('id'=>$id));
    if ($programid) {
        $acceptdata->id = $aid;
        $acceptdata->status = 1;
        $acceptdata->approvedby = $USER->id;
        $acceptdata->timemodified = time();
        $acceptdata->usermodified = $USER->id;

        $DB->update_record('local_users', $acceptdata);
    }
    $opendepsub = $DB->get_record('local_groups', array('cohortid'=> $acceptinfo->batchid));
    
    $localusersdata = $DB->get_record('local_users', array('id' => $id));
       if ($id) {
           $localusersdata->confirmed = 1;
           $localusersdata->policyagreed = 0;
           $localusersdata->deleted = 0;
           $localusersdata->suspended = 0;
           $localusersdata->mnethostid = 1;
           $passwordencrypt = md5($password);
           $localusersdata->password = $passwordencrypt;
           $localusersdata->username = $localusersdata->email;
           $localusersdata->phone1 = $localusersdata->mobile;
           $localusersdata->phone2 = $localusersdata->altermobileno;
           $localusersdata->address = $localusersdata->addressline1.''.$localusersdata->addressline2;
           $localusersdata->open_costcenterid = $opendepsub->costcenterid;
           $localusersdata->admissionid = $id;
           $localusersdata->open_departmentid = $opendepsub->departmentid;
           $localusersdata->open_subdepartment = $opendepsub->subdepartmentid;
           $localusersdata->open_type = 1;
           $localusersdata->timecreated = time();
           $localusersdata->usercreated = $USER->id;

           // Local Admission Event for Application Approve.

           try {
               $dataid->id = $DB->insert_record('user', $localusersdata);              
               $params = array(
                   'context' => context_system::instance(),
                   'objectid' => $dataid->id,
                   'relateduserid' => $dataid->id,
                   'other' => array('programid' => $programid)
               );

               $event = \local_admissions\event\status_accept::create($params);
               $event->add_record_snapshot('user', $dataid);
               $event->trigger();

               $localuserdata = $DB->get_record('local_users', array('id' => $id));
               $reject_records = new \stdClass();
               $reject_records->id = $localuserdata->id;
               $reject_records->firstname = $localuserdata->firstname;
               $reject_records->lastname = $localuserdata->lastname;
               $reject_records->registrationid = $localuserdata->registrationid;
               $reject_records->email = $localuserdata->email;
               $reject_records->password = $local_program->password;

               $applicatinuser = $DB->get_record('user', array('id' => $dataid->id));

               $reject_records->program_name = $DB->get_field('local_program', 'name', array('id'=>$id));
               
               // Sending an email to the user with login credentials.

               $url = new \moodle_url('/my/index.php');
               $finalurl = html_entity_decode($url, ENT_QUOTES, 'UTF-8');
               $datamailobj->body =  "Congratulations !! <b>$reject_records->firstname $reject_records->lastname</b>
                                     <br> Your application with the Registration No: <b> $reject_records->registrationid </b> has been approved for the program <b> $reject_records->program_name </b><br>
                                     Login with your credentials to access your Program courses.<br>
                                     Username : <b>$reject_records->email</b><br>
                                     Password : <b>$reject_records->password</b><br>
                                     <a href=".$finalurl.">$finalurl</a><br><br>
   
                                     Thanks<br>
                                     Admissions Team.";

                email_to_user($applicatinuser, $USER, 'Application Approved', $datamailobj->body);

               if ($allow) {
                   $touser = \core_user::get_user($dataid->id);
                   $email_logs = $emaillogs->admission_notification($type, $touser, $USER, $local_program);
               }

               if ($allow) {
                   $touser = \core_user::get_user($dataid->id);
                   $pemail_logs = $pemaillogs->program_notification($ptype, $touser, $USER, $local_program);
               }

               $getuserid = $DB->get_record('user', array('id' => $dataid->id));
               if ($getuserid) {
                   $getlocalusers = $DB->get_record('local_users', array('id' => $id));
                    if ($getlocalusers->id) {
                       $getlocalusers->id = $getlocalusers->id;
                       $getlocalusers->userid = $getuserid->id;
                       $getlocalusers->status = 1;
                       $DB->update_record('local_users', $getlocalusers);
                    }
                   $getlocaladmissions = $DB->get_record('local_admissions', array('admissionid' => $getuserid->admissionid));
                   if ($getlocaladmissions) {
                       $getlocaladmissions->userid = $getuserid->id;
                    //    $getlocaladmissions->status = 1;
                       $data->id = $DB->update_record('local_admissions', $getlocaladmissions);
                   
                       $getcohortuser = $DB->get_record('user', array('id' => $data->id));
                       
                       $getcohortuser->cohortid = $opendepsub->cohortid;
                       $getcohortuser->userid = $getuserid->id;;
                       $getcohortuser->timeadded = time();
                       $DB->insert_record('cohort_members', $getcohortuser);
                       $getcohortuser->programid = $programid;
                       $getcohortuser->typeid = 0;
                       $getcohortuser->userid = $getuserid->id;
                       $getcohortuser->supervisorid = 0;
                       $getcohortuser->hours = 0;
                       $getcohortuser->usercreated = $USER->id;
                       $getcohortuser->timecreated = time();

                       $a = $DB->insert_record('local_program_users', $getcohortuser);

                        foreach($enrolusers as $enroluser) {
                            $params = new stdClass();
                            $params->programid = $enroluser->programid;
                            $params->levelid = $enroluser->levelid;
                            $params->userid = $getcohortuser->userid;
                            $params->courseid = $enroluser->courseid;
                            $params->mandatory = $enroluser->mandatory;
                            $params->timecreated = time();
                            // $enroled_users->enrol_batch_to_program_courses($params->programid, $params->levelid, $params->mandatory, $params->courseid);
                            $enroled_users->manage_admissions_course_enrolments($params->courseid, $params->userid, 'student', 'enrol', 'self');
                            // $enroled_users->manage_program_courses($params->courseid);
                            $method_exists = $DB->record_exists('enrol', ['enrol' => 'program', 'courseid' => $params->courseid]);
                            if ($method_exists) {
                                continue;
                            } else {
                                $enrol = $DB->insert_record('enrol', ['enrol' => 'self', 'status' => 0, 'courseid' => $params->courseid, 'sortorder' => 3, 'roleid' => 5, 'timecreated' => time(), 'timemodified' => time()]);
                            }
                            $enroled_users->enrol_user_to_program_courses($params->programid, $params->userid, $params->courseid, $params->levelid);

                            $restorecourse = $DB->get_record('course',array('id'=>$params->courseid));
                            // $mastercourseid = $restorecourse->id;
                            $category_id = $restorecourse->category;
                            $open_departmentid = (int)$restorecourse->open_departmentid;
                            $open_subdepartment = (int)$restorecourse->open_subdepartment;
                            $restorecourse->shortname = $restorecourse->shortname.'_'.$courses->programid.'_'.$courses->levelid;
                            $restorecourse->id = 0;
                            $restorecourse->idnumber = '';
                            $restorecourse->open_identifiedas = 5;
                            $restorecourse->category = $category_id;
                            // $newcourse = create_course($restorecourse);
                            $eusers->id = $DB->insert_record('local_program_enrolments', $params);
                        }
                   }
               }
           } catch (dml_exception $ex) {
               throw new moodle_exception(get_string('duplicate_entry', 'local_admissions'));
               print_error($ex);
           }
       }
}


      /**
     * Insert form submitted data.
     *
     * @param int $dataobj inserted data of the table local_users
     * @return int having id of the local_users
     */
    public function save_adminapprovels($dataobj){
        global $DB, $USER;

        $batchid = $DB->get_field('local_program', 'batchid', array('id'=>$dataobj->programid));
      
        $insertdata->userid = 0;
        $insertdata->programid = $dataobj->programid;
        $insertdata->batchid = $batchid;
        $insertdata->status = 0;
        $pid = array('id'=>$dataobj->id, 'programid'=>$insertdata->programid, 'batchid'=>$batchid);
        $acceptinfo = $DB->get_record('local_users', $pid, '*');

        $revisecnt = $DB->get_field('local_users', 'revisecnt', array('id' => $dataobj->id));

        $cnt = $revisecnt;
        
        if($dataobj->id){
            $dataobj->revisecnt = $cnt;
            $dataobj->status = 1;
            $dataobj->usermodified = $USER->id;
            $dataobj->timemodified = time();
            $DB->update_record('local_users', $dataobj);
            $id = $dataobj->id;
        }else{
            // file_save_draft_area_files($dataobj->uploaddocs, 1, 'local_admissions', 'uploaddocs', $dataobj->uploaddocs);
            $dataobj->revisecnt = $cnt;
            $dataobj->usercreated = $USER->id;
            $dataobj->timecreated = time();
         
            $id = $DB->insert_record('local_users', $dataobj);
        }
        return $id;
    }

      /**
     * Insert form submitted data.
     *
     * @param int $dataobj inserted data of the table local_users
     * @return int having id of the local_users
     */
    public function save_adminapprovels_educationaldetails($dataobj) {
        global $DB, $USER;
    
        $dataobj->id = $this->save_adminapprovels($dataobj);
        $localusers = $DB->get_record('local_users', array('id'=>$dataobj->id));
        $emaillogs = new \local_admissions\notification();
        $allow = true;
        $type = 'admission_create';
        $fromuserid = $USER->id;
        // $local_program = $DB->get_record('local_program', array('id' => $programid));
        $sql = "SELECT lu.id as admissionid, lu.registrationid, lu.email, lu.firstname, lu.lastname,  p.* 
        FROM {local_users} lu 
        JOIN {local_program} p ON p.id = lu.programid 
        WHERE lu.id = :admissionid AND lu.programid = :programid";

        $local_program = $DB->get_record_sql($sql, array('admissionid'=>$localusers->id, 'programid'=>$localusers->programid));
        
        // delete all previous levels which is not available in current submission
        $currentlevels = $dataobj->coursename;

        list($insql, $inparams) = $DB->get_in_or_equal($currentlevels, SQL_PARAMS_NAMED, 'coursename');
        $sql = "SELECT id 
                FROM {local_admissions} 
                WHERE coursename $insql AND admissionid =:admissionid ";

        $inparams['admissionid'] = $dataobj->id;
        $unwatedbages = $DB->get_fieldset_sql($sql, $inparams);
        // if($unwatedbages){
        //     foreach ($unwatedbages as $unwatedbage) {
        //         $DB->delete_records('local_admissions', array('id'=>$unwatedbage));
        //     }
        // }
        //     // insert/update levels for this user educational Details
        for($i = 0;($i < $dataobj->option_repeats); $i++){
            $insertdata = new \stdClass();
            $insertdata->admissionid = $dataobj->id;
            $insertdata->level = $dataobj->courseseq[$i];
            $insertdata->coursename = $dataobj->coursename[$i];
            $insertdata->university = $dataobj->university[$i];
            $insertdata->yearofpassing = $dataobj->yearofpassing[$i];
            $insertdata->percentage = $dataobj->percentage[$i];
           
            $params = array('admissionid'=>$dataobj->id, 'level'=>$insertdata->level);

            $prbadge = $DB->get_record('local_admissions', $params, '*');
            if($prbadge->id){
                $insertdata->id = $prbadge->id;
                $insertdata->timemodified = time();
                $insertdata->usermodified = $USER->id;
                $DB->update_record('local_admissions', $insertdata);
            }else{
                $insertdata->timecreated = time();
                $insertdata->usercreated = $USER->id;

                $insertdata->id = $DB->insert_record('local_admissions', $insertdata);

            }
        }

        // Local Admission Event for Application Receive.

        $params = array(
            'context' => context_system::instance(),
            'objectid' => $localusers->id,
            'userid' => $localusers->userid,
            'other' => array('programid' => $localusers->programid)
        );

        $event = \local_admissions\event\status_create::create($params);
        $event->add_record_snapshot('local_users', $localusers);
        $event->trigger();

        $reject_record = $DB->get_record('local_users', array('id'=>$dataobj->id));

        $reject_records->firstname = $reject_record->firstname;
        $reject_records->lastname = $reject_record->lastname;
        $reject_records->registrationid = $reject_record->registrationid;
        $reject_records->program_name = $DB->get_field('local_program', 'name', array('id'=>$reject_record->programid));

        // Local Admission Event for Application Reject.

        $params = array(
                'context' => context_system::instance(),
                'objectid' => $reject_records->id,
                'userid' => $USER->id,
                'other' => array('programid' => $reject_records->programid, 'email'=>$reject_records->email)
            );

            $event = \local_admissions\event\status_reject::create($params);
            $event->add_record_snapshot('local_users', $reject_records);
            $event->trigger();

            // Sending an email to the user to check the application status.

            $datamailobj->body = "Dear <b>$reject_records->firstname  $reject_records->lastname</b>,
                                We have successfully received your application for entry into <b> $reject_records->program_name </b>
                                your apllication is now setting with our admission team for approval.<br>
                                We shall be in contact again with you shortly to approve your application to the program or to request further information relating to your application.<br>
                                check the status of your application clicking on link: <a>$CFG->wwwroot/local/admissions/status.php</a><br>
                                
                                Thanks<br>
                                Admission Team.";

             email_to_user($reject_record, $USER, 'Application Received', $datamailobj->body);

        if ($allow) {
            $touser = $local_program;
            $email_logs = $emaillogs->admission_notification($type, $touser, $fromuser = get_admin(), $local_program);
        }
    
        $clgname = $DB->get_record_sql("SELECT lc.shortname
                                         FROM {local_costcenter} lc
                                         JOIN {local_program} lp ON lc.id = lp.costcenter
                                        WHERE lp.id = $dataobj->programid");
        $collegename = strtoupper(substr($clgname->shortname, 0, 3));
        $year = substr(date("Y"), - 2);
        $idvaluelength = strlen($dataobj->id);
        if ($idvaluelength == 1) {
            $idvaluelength = "000".$dataobj->id;
        } else if ($idvaluelength == 2) {
            $idvaluelength = "00".$dataobj->id;
        } else if ($idvaluelength == 3) {
            $idvaluelength = "0".$dataobj->id;
        } else {
            $idvaluelength = $dataobj->id;
        }
        $registrationid = $year.$collegename.$idvaluelength;
        $dataobj->id = $dataobj->id;
        $dataobj->registrationid = $registrationid;
        $DB->update_record('local_users', $dataobj);

        if ($dataobj->id) {

            $admissiondata = $DB->get_record('local_users', array('id'=>$dataobj->id));
             /**
             * Insert form submitted data.
             *
             * @param int $admissionid id inserted data of the table local_users
             * @param int $programid id the table local_program
             * @return int having id of the local_users
             */
            $adminenrol = $this->admin_enroluser_to_program($admissiondata->id, $admissiondata->programid);
        }
        return true;
    }

    public function accept_student_admission($id, $programid, $context){
        global $DB, $USER, $CFG, $PAGE;
        
        $systemcontext = context_system::instance();
        $PAGE->set_context($systemcontext);
        $emaillogs = new \local_admissions\notification();
        $pemaillogs = new \local_program\notification();
        $enroled_users = new program();

        $allow = true;
        $ptype = 'program_enrol';
        $type = 'admission_approve';
        $dataobj = $programid;
        $fromuserid = $USER->id;
        $pid = array('programid'=>$programid);

        $acceptinfo = $DB->get_record('local_users', $pid, '*');
        $sql = "SELECT lu.id as admissionid, lu.registrationid, lu.email, lu.firstname, lu.lastname, p.* 
        FROM {local_users} lu 
        JOIN {local_program} p ON p.id = lu.programid 
        WHERE lu.id = :admissionid AND lu.programid = :programid";

        $local_program = $DB->get_record_sql($sql, array('admissionid'=>$id, 'programid'=>$programid));

        $programenrolusers = "SELECT plc.courseid, lu.id, pl.id as levelid, lu.email, pl.programid, pl.level, pl.active, plc.mandatory, plc.parentid, c.fullname , plc.parentid 
                FROM {local_users} lu 
                JOIN {local_program_levels} pl ON pl.programid = lu.programid 
                JOIN {local_program_level_courses} plc ON plc.levelid = pl.id
                JOIN {course} c ON c.id = plc.courseid WHERE lu.id = :admissionid AND pl.programid = :programid AND plc.mandatory = :mandatory AND pl.active = 1";

        $enrolusers = $DB->get_records_sql($programenrolusers, array('admissionid'=>$id, 'programid'=>$programid, 'mandatory'=>1, 'active'=>1));

        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%&*_";
        $password = substr(str_shuffle( $chars ), 0, 15 );
        $local_program->password =  $password;

        $aid = $DB->get_field('local_users', 'id', array('id'=>$id));
        if ($programid) {
            $acceptdata->id = $aid;
            $acceptdata->status = 1;
            $acceptdata->approvedby = $USER->id;
            $acceptdata->timemodified = time();
            $acceptdata->usermodified = $USER->id;
            $DB->update_record('local_users', $acceptdata);
        }
        $opendepsub = $DB->get_record('local_groups', array('cohortid'=> $acceptinfo->batchid));
        $localusersdata = $DB->get_record('local_users', array('id' => $id));
           if ($id) {
               $localusersdata->confirmed = 1;
               $localusersdata->policyagreed = 0;
               $localusersdata->deleted = 0;
               $localusersdata->suspended = 0;
               $localusersdata->mnethostid = 1;
               $passwordencrypt = md5($password);
               $localusersdata->password = $passwordencrypt;
               $localusersdata->username = $localusersdata->email;
               $localusersdata->phone1 = $localusersdata->mobile;
               $localusersdata->phone2 = $localusersdata->altermobileno;
               $localusersdata->address = $localusersdata->addressline1.''.$localusersdata->addressline2;
               $localusersdata->open_costcenterid = $opendepsub->costcenterid;
               $localusersdata->admissionid = $id;
               $localusersdata->open_departmentid = $opendepsub->departmentid;
               $localusersdata->open_subdepartment = $opendepsub->subdepartmentid;
               $localusersdata->open_type = 1;
               $localusersdata->timecreated = time();
               $localusersdata->usercreated = $USER->id;

               // Local Admission Event for Application Approve.
               try {
                   $dataid->id = $DB->insert_record('user', $localusersdata);              
                   $params = array(
                       'context' => context_system::instance(),
                       'objectid' => $dataid->id,
                       'relateduserid' => $dataid->id,
                       'other' => array('programid' => $programid)
                   );

                   $event = \local_admissions\event\status_accept::create($params);
                   $event->add_record_snapshot('user', $dataid);
                   $event->trigger();

                   $localuserdata = $DB->get_record('local_users', array('id' => $id));
                   $reject_records = new \stdClass();
                   $reject_records->id = $localuserdata->id;
                   $reject_records->firstname = $localuserdata->firstname;
                   $reject_records->lastname = $localuserdata->lastname;
                   $reject_records->registrationid = $localuserdata->registrationid;
                   $reject_records->email = $localuserdata->email;
                   $reject_records->password = $local_program->password;

                   $applicatinuser = $DB->get_record('user', array('id' => $dataid->id));
    
                   $reject_records->program_name = $DB->get_field('local_program', 'name', array('id'=>$id));
                   
                    // Sending an email to the user with login credentials.

                   $url = new \moodle_url('/my/index.php');
                   $finalurl = html_entity_decode($url, ENT_QUOTES, 'UTF-8');
                   $datamailobj->body =  "Congratulations !! <b>$reject_records->firstname $reject_records->lastname</b>
                                         <br> Your application with the Registration No: <b> $reject_records->registrationid </b> has been approved for the program <b> $reject_records->program_name </b><br>
                                         Login with your credentials to access your Program courses.<br>
                                         Username : <b>$reject_records->email</b><br>
                                         Password : <b>$reject_records->password</b><br>
                                         <a href=".$finalurl.">$finalurl</a><br><br>
       
                                         Thanks<br>
                                         Admissions Team.";

                    email_to_user($applicatinuser, $USER, 'Application Approved', $datamailobj->body);
   
                   if ($allow) {
                       $touser = \core_user::get_user($dataid->id);
                       $email_logs = $emaillogs->admission_notification($type, $touser, $USER, $local_program);
                   }
                   if ($allow) {
                       $touser = \core_user::get_user($dataid->id);
                       $pemail_logs = $pemaillogs->program_notification($ptype, $touser, $USER, $local_program);
                   }
                   $getuserid = $DB->get_record('user', array('id' => $dataid->id));
                   if ($getuserid) {
                       $getlocalusers = $DB->get_record('local_users', array('id' => $getuserid->admissionid));
                        if ($getlocalusers) {
                           $getlocalusers->userid = $getuserid->id;
                           $DB->update_record('local_users', $getlocalusers);
                        }
                       $getlocaladmissions = $DB->get_record('local_admissions', array('admissionid' => $getuserid->admissionid));
                       if ($getlocaladmissions) {
                           $getlocaladmissions->userid = $getuserid->id;
                           // $getlocaladmissions->status = 1;
                           $data->id = $DB->update_record('local_admissions', $getlocaladmissions);
                       
                           $getcohortuser = $DB->get_record('user', array('id' => $data->id));
                           
                           $getcohortuser->cohortid = $opendepsub->cohortid;
                           $getcohortuser->userid = $getuserid->id;;
                           $getcohortuser->timeadded = time();
                           $DB->insert_record('cohort_members', $getcohortuser);
                           $getcohortuser->programid = $programid;
                           $getcohortuser->typeid = 0;
                           $getcohortuser->userid = $getuserid->id;
                           $getcohortuser->supervisorid = 0;
                           $getcohortuser->hours = 0;
                           $getcohortuser->usercreated = $USER->id;
                           $getcohortuser->timecreated = time();

                           $a = $DB->insert_record('local_program_users', $getcohortuser);

                            foreach($enrolusers as $enroluser) {
                                $params = new stdClass();
                                $params->programid = $enroluser->programid;
                                $params->levelid = $enroluser->levelid;
                                $params->userid = $getcohortuser->userid;
                                $params->courseid = $enroluser->courseid;
                                $params->mandatory = $enroluser->mandatory;
                                $params->timecreated = time();
                                // $enroled_users->enrol_batch_to_program_courses($params->programid, $params->levelid, $params->mandatory, $params->courseid);
                                $enroled_users->manage_admissions_course_enrolments($params->courseid, $params->userid, 'student', 'enrol', 'self');
                                // $enroled_users->manage_program_courses($params->courseid);
                                $method_exists = $DB->record_exists('enrol', ['enrol' => 'program', 'courseid' => $params->courseid]);
                                if ($method_exists) {
                                    continue;
                                } else {
                                    $enrol = $DB->insert_record('enrol', ['enrol' => 'self', 'status' => 0, 'courseid' => $params->courseid, 'sortorder' => 3, 'roleid' => 5, 'timecreated' => time(), 'timemodified' => time()]);
                                }
                                $enroled_users->enrol_user_to_program_courses($params->programid, $params->userid, $params->courseid, $params->levelid);

                                $restorecourse = $DB->get_record('course',array('id'=>$params->courseid));
                                // $mastercourseid = $restorecourse->id;
                                $category_id = $restorecourse->category;
                                $open_departmentid = (int)$restorecourse->open_departmentid;
                                $open_subdepartment = (int)$restorecourse->open_subdepartment;
                                $restorecourse->shortname = $restorecourse->shortname.'_'.$courses->programid.'_'.$courses->levelid;
                                $restorecourse->id = 0;
                                $restorecourse->idnumber = '';
                                $restorecourse->open_identifiedas = 5;
                                $restorecourse->category = $category_id;
                                // $newcourse = create_course($restorecourse);
                                $eusers->id = $DB->insert_record('local_program_enrolments', $params);
                            }
                       }
                   }
               } catch (dml_exception $ex) {
                   throw new moodle_exception(get_string('duplicate_entry', 'local_admissions'));
                   print_error($ex);
               }
           }
    }

    public function downloadpdf_file($id){
        global $DB;
        $lablestring = get_config('local_costcenter');
        $sql = "SELECT  lu.*, cc.fullname, la.level,la.id as addid, la.coursename, la.university, la.yearofpassing, la.percentage, p.name as programname, c.name as batchname 
        FROM {local_admissions} la 
        JOIN {local_users} lu ON lu.id = la.admissionid 
        JOIN {local_program} p ON p.id = lu.programid 
        JOIN {cohort} c ON c.id = p.batchid 
        JOIN {local_costcenter} cc ON cc.id = p.costcenter 
        WHERE admissionid = :id 
                ";
        $useradmission = $DB->get_records_sql($sql, array('id'=>$id));
            $userdocs = array();
            $setdata = array();
            foreach($useradmission as $useradmissiondata) {
                $docs = new stdClass();
                $docs->firstname = $useradmissiondata->firstname;
                $docs->lastname = $useradmissiondata->lastname;
                $docs->registrationid = $useradmissiondata->registrationid;
                $docs->fathername = $useradmissiondata->fathername;
                $docs->mothername = $useradmissiondata->mothername;
                $docs->birthplace = $useradmissiondata->birthplace;
                $docs->nationality = $useradmissiondata->nationality;
                $docs->religion = $useradmissiondata->religion;
                $docs->email = $useradmissiondata->email;

                $docs->batchname = $useradmissiondata->batchname;
                $docs->maritalstatus = $useradmissiondata->maritalstatus;
                $docs->programname = $useradmissiondata->programname;
                $docs->percentage = $useradmissiondata->percentage;
                $docs->yearofpassing = $useradmissiondata->yearofpassing;
                $docs->university = $useradmissiondata->university;
                $docs->collegename = $useradmissiondata->fullname;
                $docs->status = $useradmissiondata->status;
          
                $docs->addressline1 = $useradmissiondata->addressline1;
                $docs->addressline2 = $useradmissiondata->addressline2;
                $docs->paddressline1 = $useradmissiondata->paddressline1;
                $docs->paddressline2 = $useradmissiondata->paddressline2;
                $docs->pcity = $useradmissiondata->pcity;
                $docs->pstate = $useradmissiondata->pstate;
                $ucountry = get_string($useradmissiondata->pcountry, 'countries');
                $docs->pcountry = $ucountry;
                $usrcountry = get_string($useradmissiondata->country, 'countries');
                $docs->country = $usrcountry;
                $docs->addressline2 = $useradmissiondata->addressline2;
                $docs->paddressline1 = $useradmissiondata->paddressline1;
                $docs->ppincode = $useradmissiondata->ppincode;
                $docs->reason = $useradmissiondata->reason;
                $docs->timecreated = $useradmissiondata->timecreated;
     
                $docs->occupation = $useradmissiondata->occupation;
                $docs->city = $useradmissiondata->city;
                $docs->mobile = $useradmissiondata->mobile;
                $docs->altermobileno = $useradmissiondata->altermobileno;
                if ($useradmissiondata->gender == 0) {
                    $docs->gender = "Male";
                } else if ($useradmissiondata->gender == 1){
                    $docs->gender = "Female";
                } else {
                    $docs->gender = 'Others';
                }

                $docs->maritalstatus = $useradmissiondata->maritalstatus;

                if ($useradmissiondata->maritalstatus == 0) {
                    $docs->maritalstatus = "Married";
                } else if ($useradmissiondata->maritalstatus == 1){
                    $docs->maritalstatus = "Unmarried";
                } else {
                    $docs->maritalstatus = 'NA';
                }

                $docs->dob = date('d-M-Y', $useradmissiondata->dob);
                $docs->pincode = $useradmissiondata->pincode;
          
                $docs->state = $useradmissiondata->state;
                $uploaddocs = $this->get_uploadeddocslist($useradmissiondata->id);
                $docs->uploaddocs = $uploaddocs[$useradmissiondata->id];
                
                $docs->imgsrc = $this->get_uploadeddocument_src($useradmissiondata->id);
                // $docs->uploaddocs = $reviseuserinfor->local_usersid != null ? $this->get_uploadeddocument_src($reviseuserinfor->local_usersid) : 'NA';
                $docs->firstlevel = $lablestring->firstlevel;
                $userdocs[] = $docs;
                
            }

        return $userdocs;
    }
    public function educational_details($admissionid) {
        global $DB;
        $check = $DB->get_records('local_admissions', array('admissionid'=>$admissionid));
        $userdoc = array();
        foreach($check as $checks) {
            $collegedetails = new stdClass();
            $collegedetails->coursename = $checks->coursename;
            $collegedetails->university = $checks->university;
            $collegedetails->yearofpassing = $checks->yearofpassing;
            $collegedetails->percentage = $checks->percentage;
            $collegedetails->level = $checks->level;
            $userdoc[] = $collegedetails;
        }
        return $userdoc;
    }
}
