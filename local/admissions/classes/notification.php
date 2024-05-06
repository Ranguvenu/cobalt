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
 */
 namespace local_admissions;
 class notification{
 	public $db;
	public $user;
	public function __construct($db=null, $user=null){
		global $DB, $USER;
		$this->db = $db ? $db :$DB;
		$this->user = $user ? $user :$USER;
	}
	public function get_notification_strings($emailtype){
		switch($emailtype){
			case 'admission_approve':
                $strings = "[admission_program], [admission_email], [program_startdate], 
                            [program_enddate]";
            break;
            case 'admission_reject':
                $strings = "[admission_program], [admission_email], [program_startdate], 
                            [program_enddate]";
            break;
            case 'admission_revise':
                $strings = "[admission_program], [admission_email], [program_startdate], [program_enddate]";
            break;
            case 'admission_create':
                $strings = "[admission_program], [admission_email], [program_startdate], [program_enddate]";
            break;
		}
	}

	public function admission_notification($emailtype, $touser, $fromuser, $programinstance){
        if($notification = $this->get_existing_notification($programinstance, $emailtype)){
            $this->send_admission_notification($programinstance, $touser, $fromuser, $emailtype, $notification);
        }
    }
    public function get_existing_notification($programinstance, $emailtype){
    	$corecomponent = new \core_component();
        $costcenterexist = $corecomponent::get_plugin_directory('local','costcenter');
        $params = array();
        $notification_typesql = "SELECT lni.* FROM {local_notification_info} AS lni
            JOIN {local_notification_type} AS lnt ON lnt.id = lni.notificationid
            WHERE concat(',',lni.moduleid,',') LIKE concat('%,',:moduleid,',%') AND lnt.shortname LIKE :emailtype AND lni.active = 1 ";
        $params['moduleid'] = $programinstance->id;
        $params['emailtype'] = $emailtype;
        if($costcenterexist){
            $notification_typesql .= " AND lni.costcenterid = :costcenter";
            $params['costcenter'] = $programinstance->costcenter;
        }
        $notification = $this->db->get_record_sql($notification_typesql, $params);
        if(empty($notification)){ // sends the default notification for the type.
            $params = array();
            $notification_typesql = "SELECT lni.* FROM {local_notification_info} AS lni
                JOIN {local_notification_type} AS lnt ON lnt.id=lni.notificationid
                WHERE (lni.moduleid IS NULL OR lni.moduleid LIKE '0')
                AND lnt.shortname LIKE :emailtype AND lni.active=1 ";
            $params['emailtype'] = $emailtype;
            if($costcenterexist){
                $notification_typesql .= " AND lni.costcenterid=:costcenter";
                $params['costcenter'] = $programinstance->costcenter;
            }
            $notification = $this->db->get_record_sql($notification_typesql, $params);
        }

        $allnotification_typesql = "SELECT lni.* FROM {local_notification_info} AS lni
                JOIN {local_notification_type} AS lnt ON lnt.id=lni.notificationid
                WHERE (lni.moduleid = 'all' OR lni.moduleid LIKE '0')
                AND lnt.shortname LIKE :emailtype AND lni.active=1 ";
        $params['emailtype'] = $emailtype;

        $allnotification = $this->db->get_record_sql($allnotification_typesql, $params);

        if($allnotification){ // sends the default notification for the type.
            $params = array();
            $notification_typesql = "SELECT lni.* FROM {local_notification_info} AS lni
                JOIN {local_notification_type} AS lnt ON lnt.id=lni.notificationid
                WHERE (lni.moduleid = 'all' OR lni.moduleid LIKE '0')
                AND lnt.shortname LIKE :emailtype AND lni.active=1 ";
            $params['emailtype'] = $emailtype;
            if($costcenterexist){
                $notification_typesql .= " AND lni.costcenterid=:costcenter";
                $params['costcenter'] = $programinstance->costcenter;
            }
            $notification = $this->db->get_record_sql($notification_typesql, $params);
        }
        if(empty($notification)){
            return false;
        }else{
            return $notification;
        }
    }
    
    public function send_admission_notification($programinstance, $touser, $fromuser, $emailtype, $notification){
        global $CFG;
    	$datamailobject = new \stdClass();
        $datamailobject->notification_infoid = $notification->id;
        $datamailobject->admissionid = $programinstance->admissionid;
        $datamailobject->firstname = $programinstance->firstname;
        $datamailobject->email = $programinstance->email;
        $datamailobject->lastname = $programinstance->lastname;
        $datamailobject->password = $programinstance->password;
        $datamailobject->registrationid = $programinstance->registrationid;
        $datamailobject->reason = $programinstance->reason;
        $datamailobject->program_startdate = $programinstance->startdate ? \local_costcenter\lib::get_userdate("d/m/Y H:i",$programinstance->startdate) : 'N/A';
        $datamailobject->program_enddate = $programinstance->enddate ? \local_costcenter\lib::get_userdate("d/m/Y H:i",$programinstance->enddate) : 'N/A';
    	$datamailobject->program_name = $programinstance->name;
    	$datamailobject->program_organization = $this->db->get_field('local_costcenter', 'fullname',  array('id' => $programinstance->costcenter));
    	$datamailobject->program_stream = $this->db->get_field('local_program_stream', 'stream', array('id'=>$programinstance->stream));
        $creatornamesql = "SELECT concat(firstname,' ',lastname) FROM {user} WHERE id=:creatorid ";
    	$datamailobject->program_creater = $this->db->get_field_sql($creatornamesql, array('creatorid' => $programinstance->usercreated));
    	$datamailobject->program_enroluserfulname = fullname($touser);
        $url = new \moodle_url($CFG->wwwroot.'/local/program/view.php?bcid='.$programinstance->id);
    	$datamailobject->program_link = \html_writer::link($url, $url);
    	$datamailobject->program_enroluseremail = $touser->email;
        if($emailtype == 'program_completion'){
            $completiondate = $this->db->get_field('local_program_users', 'completiondate', array('programid'=>$programinstance->id, 'userid'=>$touser->id));
        	$datamailobject->program_completiondate  = $completiondate ? \local_costcenter\lib::get_userdate("d/m/Y H:i", $completiondate) : 'N/A';//for completion
        }
        if($emailtype == 'program_semester_completion'){

        }
    	// programcourse completion
        if($emailtype == 'program_course_completion'){
            $courserec_sql = "SELECT c.id, c.fullname, concat(u.firstname,' ',u.lastname) AS creatorname
                FROM {course} AS c
                JOIN {user} AS u ON u.id=c.open_coursecreator
                WHERE c.id=:courseid ";
            $courseobj = $this->db->get_record_sql($courserec_sql, array('courseid' => $programinstance->courseid));
            $datamailobject->program_course = $courseobj->fullname;
            $courselink = new \moodle_url('course/view.php', array('id' => $programinstance->courseid));
        	$datamailobject->program_lc_course_link = $courselink; //program_course_completion
            $datamailobject->program_lc_course_creater = $courseobj->creatorname;//program_course_completion
            $completiondate = $this->db->get_field('course_completions', 'timecompleted', array('course' => $programinstance->courseid, 'userid' => $touser->id));
            $datamailobject->program_lc_course_completiondate = $completiondate ? \local_costcenter\lib::get_userdate("d/m/Y H:i",$completiondate) : 'N/A';//program_course_completion
        }
        $non_coursenamestring = array('application_approved', 'application_rejected', 'application_revised', 'application_received');
        if(!in_array($emailtype, $non_coursenamestring)){
            $programcourses_sql = "SELECT c.id, c.fullname FROM {course} AS c
                JOIN {local_program_level_courses} AS lplc ON lplc.courseid = c.id
                JOIN {local_program_levels} AS lpl ON lpl.id = lplc.levelid
                WHERE lpl.programid = :programid AND lpl.id = :levelid ";
            $programscourses = $this->db->get_records_sql_menu($programcourses_sql, array('programid' => $programinstance->id, 'levelid' => $programinstance->levelid));
            $datamailobject->program_course = implode(',', $programscourses);
            $datamailobject->program_level = $this->db->get_field('local_program_levels', 'level',array('id' => $programinstance->levelid));
            $sessiondetails = $this->db->get_record('local_bc_course_sessions', array('id'=>$programinstance->sessionid), 'id, name, timestart, timefinish');
            $datamailobject->program_session_name = $sessiondetails->name;
            $datamailobject->program_session_startdate = $sessiondetails->timestart ? \local_costcenter\lib::get_userdate('d/m/Y H:i',$sessiondetails->timestart) : 'N/A';
            $datamailobject->program_session_enddate = $sessiondetails->timefinish ? \local_costcenter\lib::get_userdate('d/m/Y H:i',$sessiondetails->timefinish) : 'N/A';
            $url = new \moodle_url('local/program/sessions.php?bclcid='.$programinstance->courseid.'&levelid='.$programinstance->levelid.'&bcid='.$programinstance->id);
            $datamailobject->program_session_link = \html_writer::link($url, $url);
            $datamailobject->program_session_username = fullname($touser);
            $datamailobject->program_session_useremail = $touser->email;
            $trainerdetails_sql = "SELECT u.id, concat(u.firstname,' ',u.lastname) AS trainername
                FROM {user} AS u
                JOIN {local_bc_course_sessions} AS lbcs ON lbcs.trainerid=u.id
                WHERE lbcs.id=:sessionid ";
            $trainerdetails = $this->db->get_record_sql($trainerdetails_sql, array('sessionid' => $programinstance->sessionid));
            $datamailobject->program_session_trainername = $trainerdetails->trainername;
        }
        $attendancereq = array('program_session_completion', 'program_session_attendance');
        if(in_array($emailtype, $attendancereq)){
            $completion_status = $this->db->get_field('local_bc_session_signups', 'completion_status', array('programid'=>$programinstance->id, 'levelid'=>$programinstance->levelid, 'bclcid'=>$programinstance->courseid, 'sessionid'=>$programinstance->sessionid, 'userid'=>$touser->id));
            if($completion_status == SESSION_PRESENT) {
                $datamailobject->program_session_attendance = 'Present';
            } else {
                $datamailobject->program_session_attendance = 'Absent';
            }
        }
        if($emailtype == 'program_session_completion'){
            $program_completiondate=$this->db->get_field('local_bc_session_signups', 'timemodified', array('programid'=>$programinstance->id, 'levelid'=>$programinstance->levelid, 'bclcid'=>$programinstance->bclcid, 'sessionid'=>$programinstance->sessionid, 'userid'=>$touser->id));
            if(!empty($completion_date)){
                $datamailobject->program_session_completiondate  =  \local_costcenter\lib::get_userdate("d/m/Y H:i",$program_completiondate);
            } else {
                $datamailobject->program_session_completiondate  = 'NA';
            }
        }
        $datamailobject->type = $emailtype;
        $datamailobject->adminbody = NULL;
        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->programid = $programinstance->id;
        $datamailobject->touserid = $touser->id;
        $datamailobject->fromuserid = $fromuser->id;
        $datamailobject->teammemberid = 0;
        if(!empty($notification->adminbody) && !empty($touser->open_supervisorid)){
            $superuser = \core_user::get_user($touser->open_supervisorid);
        }else{
            $superuser = false;
        }
        $this->log_email_notification($touser, $fromuser, $datamailobject);
        if($superuser){
            $datamailobject->body = $notification->adminbody;
            $datamailobject->touserid = $superuser->id;
            $datamailobject->teammemberid = $touser->id;
            $this->log_email_notification($superuser, $fromuser, $datamailobject);
        }
    }
    public function log_email_notification($touser, $fromuser, $datamailobj){
        global $DB, $CFG;

        if ($datamailobj->type == 'admission_approve') {
            $url = new \moodle_url('/my/index.php');
            $finalurl = html_entity_decode($url, ENT_QUOTES, 'UTF-8');
            $datamailobj->body =  "Congratulations !! <b>$datamailobj->firstname $datamailobj->lastname</b>
                                  <br> Your application with the Registration No: <b> $datamailobj->registrationid </b> has been approved for the program <b> $datamailobj->program_name </b><br>
                                  Login with your credentials to access your Program courses.<br>
                                  Username : <b>$datamailobj->email</b><br>
                                  Password : <b>$datamailobj->password</b><br>
                                  <a href=".$finalurl.">$finalurl</a><br><br>

                                  Thanks<br>
                                  Admissions Team.";

        }else if ($datamailobj->type == 'admission_revise'){
            $datamailobj->body =  "Hi <b>$datamailobj->firstname  $datamailobj->lastname</b>,
                                  <br>Your application with Registration No: <b> $datamailobj->registrationid </b> to the program <b> $datamailobj->program_name </b> is requested for revision.<br> 
                                  Please access your submitted application through below link to review your information and re-submit.<br>
                                  Reason: $datamailobj->reason.<br>
                                  <a>$CFG->wwwroot/local/admissions/status.php</a><br>

                                  Thanks<br>
                                  Admissions Team.
                                  ";


        } else if ($datamailobj->type == 'admission_reject') {

            $datamailobj->body = "Hi <b>$datamailobj->firstname  $datamailobj->lastname</b>, 
                                 <br> We are sorry to inform you that your application with Registration No: <b> $datamailobj->registrationid </b> to the program <b> $datamailobj->program_name </b> is rejected.<br>
                                 As you have not meet the program Pre-requisites.<br>
                                 Reason: $datamailobj->reason.<br>

                                 Thanks<br>
                                 Admissions Team.
                                 ";


        } else if ($datamailobj->type == 'admission_create') {
            $datamailobj->body = "Dear <b>$datamailobj->firstname  $datamailobj->lastname</b>,
                                 We have successfully received your application for entry into <b> $datamailobj->program_name </b>
                                 your apllication is now setting with our admission team for approval.<br>
                                 We shall be in contact again with you shortly to approve your application to the program or to request further information relating to your application.<br>
                                 check the status of your application clicking on link: <a>$CFG->wwwroot/local/admissions/status.php</a><br>
                                 
                                 Thanks<br>
                                 Admission Team.";

        }
   
        $dataobject = clone $datamailobj;
        $dataobject->subject = $this->replace_strings($datamailobj, $datamailobj->subject);
        $dataobject->emailbody = $this->replace_strings($datamailobj, $datamailobj->body);
        $dataobject->from_emailid = $fromuser->email;
        $dataobject->from_userid = $fromuser->id;
        $dataobject->to_emailid = $touser->email;
        $dataobject->to_userid = $touser->id;
        $dataobject->ccto = 0;
        $dataobject->sentdate = 0;
        $dataobject->sent_by = $fromuser->id;
        $dataobject->moduleid = $datamailobj->programid;
        if($logid = $this->check_pending_mail_exists($touser, $fromuser, $datamailobj)){
            $dataobject->id = $logid;
            $dataobject->timemodified = time();
            $dataobject->usermodified = $this->user->id;
            $logid = $this->db->update_record('local_emaillogs', $dataobject);
        } else {
            $dataobject->timecreated = time();
            $dataobject->usercreated = $this->user->id;
            $this->db->insert_record('local_emaillogs', $dataobject);
        }
    }
    public function check_pending_mail_exists($user, $fromuser, $datamailobj){
        $sql =  " SELECT id FROM {local_emaillogs} WHERE to_userid = :userid AND notification_infoid = :infoid AND from_userid = :fromuserid AND subject = :subject AND status = 0";
        $params['userid'] = $datamailobj->touserid;
        $params['subject'] = $datamailobj->subject;
        $params['fromuserid'] = $datamailobj->fromuserid;
        $params['infoid'] = $datamailobj->notification_infoid;
        if($datamailobj->programid){
            $sql .= " AND moduleid=:programid";
            $params['programid'] = $datamailobj->programid;
        }
        if($datamailobj->teammemberid){
            $sql .= " AND teammemberid=:teammemberid";
            $params['teammemberid'] = $datamailobj->teammemberid;
        }
        return $this->db->get_field_sql($sql ,$params);
    }
    public function replace_strings($dataobject, $data){
        $strings = $this->db->get_records('local_notification_strings', array('module' => 'program'));
        if($strings){
            foreach($strings as $string){
                foreach($dataobject as $key => $dataval){
                    $key = '['.$key.']';
                    if("$string->name" == "$key"){
                        $data = str_replace("$string->name", "$dataval", $data);
                    }
                }
            }
        }
        return $data;
    }
}
