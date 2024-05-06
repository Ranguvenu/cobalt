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
 namespace local_program;
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
			case 'program_enrol':
                $strings = "[program_name], [program_organization], [program_stream], [program_creater], [program_enroluserfulname], [program_link], [program_enroluseremail]";
                break;
            case 'program_unenroll':
                $strings = "[program_name], [program_organization], [program_stream], [program_creater], [program_enroluserfulname], [program_link], [program_enroluseremail]";
                break;
            case 'program_completion':
                $strings = "[program_name], [program_organization], [program_stream], [program_creater], [program_enroluserfulname], [program_link], [program_enroluseremail], [program_completiondate]";
                break;
            case 'program_semester_completion':
                $strings = "[program_name], [program_level], [program_enroluserfulname], [program_link], [program_enroluseremail],[program_level_creater],[program_semester_completiondate]";
                break;
            case 'program_session_enrol':
                $strings = "[program_name], [program_level], [program_course], [program_session_name], [program_session_username]";
                break;
            case 'program_session_reschedule':
                $strings = "[program_name], [program_level], [program_course], [program_session_name], [program_session_username]";
                break;
            case 'program_session_attendance':
                $strings = "[program_name], [program_level], [program_course], [program_session_name], [program_session_username], [program_session_link], [program_session_useremail], [program_session_trainername], [program_session_attendance], [program_session_startdate], [program_session_enddate]";
                break;
            case 'program_session_reminder':
                $strings = "[program_name], [program_level], [program_course], [program_session_name], [program_session_username]";
                break;
            case 'program_session_completion':
                $strings = "[program_name], [program_level], [program_course], [program_session_name], [program_session_username], [program_session_link], [program_session_useremail], [program_session_trainername], [program_session_attendance], [program_session_startdate], [program_session_enddate], [program_session_completiondate]";
                break;
            case 'program_session_cancel':
                $strings = "[program_name], [program_level], [program_course], [program_session_name], [program_session_username]";
                break;
            case 'course_enrol';
                $string = "[course_title], [course_enrolstartdate], [course_enrolenddate], [course_department], [course_description], [course_url], [enroluser_fullname], [enroluser_email]";
                break;
            case 'program_semester_enrollment';
            $strings = "[program_name], [program_level], [program_enroluserfulname], [program_link], [program_enroluseremail],[program_level_creater]";
                break;
                // program_course, program_lc_course_link, program_lc_course_creater, program_enroluserfulname, program_enroluseremail
            case 'program_course_enrollment':
            $strings = "[program_course], [program_lc_course_link], [program_lc_course_creater], [program_name], [program_level], [program_enroluserfulname], [program_enroluseremail]";
                break;
            case 'program_course_completion':
            $strings = "[program_course], [program_lc_course_link], [program_lc_course_creater], [program_name], [program_level], [program_enroluserfulname], [program_enroluseremail], [program_lc_course_completiondate]";
                break;
		}
	}

	public function program_notification($emailtype, $touser, $fromuser, $programinstance){

        if($notification = $this->get_existing_notification($programinstance, $emailtype)){
            $this->send_program_notification($programinstance, $touser, $fromuser, $emailtype, $notification);
        }
    }
    public function get_existing_notification($programinstance, $emailtype){
    	$corecomponent = new \core_component();
        $costcenterexist = $corecomponent::get_plugin_directory('local','costcenter');
        $params = array();
        $notification_typesql = "SELECT lni.* FROM {local_notification_info} AS lni
            JOIN {local_notification_type} AS lnt ON lnt.id=lni.notificationid
            WHERE concat(',',lni.moduleid,',') LIKE concat('%,',:moduleid,',%') AND lnt.shortname LIKE :emailtype AND lni.active=1 ";
        $params['moduleid'] = $programinstance->id;
        $params['emailtype'] = $emailtype;
        if($costcenterexist){
            $notification_typesql .= " AND lni.costcenterid=:costcenter";
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
    
    public function send_program_notification($programinstance, $touser, $fromuser, $emailtype, $notification){
        global $CFG, $DB;
    	$datamailobject = new \stdClass();
        $datamailobject->notification_infoid = $notification->id;
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
            	// programcourse completion

        if($emailtype == 'program_course_completion') {
            $datamailobject->program_course = $programinstance->fullname;
            $datamailobject->program_lc_course_creater = date('d-m-Y', $programinstance->timeenrolled);
            $datamailobject->program_level = $programinstance->level;
            $course_url = new \moodle_url($CFG->wwwroot.'/course/view.php', array('id' => $programinstance->cid));
            // $course_url = new \moodle_url($CFG->wwwroot.'/local/program/view.php?bcid='.$programinstance->id);
            $datamailobject->program_lc_course_link = \html_writer::link($course_url, $course_url);
            $datamailobject->program_enroluserfulname = fullname($touser);
            $datamailobject->program_enroluseremail = $touser->email;
            $datamailobject->program_lc_course_completiondate = $programinstance->timecompleted;
        }

        if($emailtype == 'program_semester_completion'){
            $datamailobject->program_level = $programinstance->level;
            $datamailobject->program_level_creater = $programinstance->usercreated;
            $datamailobject->program_semester_completiondate = $programinstance->timecompleted;
        }

        if($emailtype == 'program_course_enrollment'){

            $sql = "SELECT plc.courseid as cid, p.*, c.fullname, pl.id as levelid, pl.level, pl.active, plc.mandatory, pl.startdate, pl.enddate, plc.parentid 

                                FROM {local_program} p 
                                JOIN {local_program_users} pu ON pu.programid = p.id 
                                JOIN {local_program_levels} pl ON pl.programid = pu.programid 
                                JOIN {local_program_level_courses} plc ON plc.levelid = pl.id 
                                JOIN {course} c ON c.id = plc.courseid 
                                JOIN {enrol} e ON e.courseid = c.id 
                                JOIN {user_enrolments} ue ON ue.enrolid = e.id 
                                JOIN {role_assignments} ra ON ra.userid = ue.userid 
                                JOIN {role} r ON r.id = ra.roleid 
                                JOIN {user} u ON u.id = ue.userid 
                                -- JOIN {user_lastaccess} ul ON ul.courseid = c.id 
                                WHERE ra.userid = {$programinstance->userid} AND pl.active = 1 
                                GROUP BY plc.courseid ORDER BY plc.courseid, plc.mandatory ASC";
            $course_enrol = $DB->get_records_sql($sql);

            $sql1 = "SELECT plc.courseid, c.fullname

                    FROM {local_program} p 
                    JOIN {local_program_users} pu ON pu.programid = p.id 
                    JOIN {local_program_levels} pl ON pl.programid = pu.programid 
                    JOIN {local_program_level_courses} plc ON plc.levelid = pl.id 
                    JOIN {course} c ON c.id = plc.courseid 
                    JOIN {enrol} e ON e.courseid = c.id 
                    JOIN {user_enrolments} ue ON ue.enrolid = e.id 
                    JOIN {role_assignments} ra ON ra.userid = ue.userid 
                    JOIN {role} r ON r.id = ra.roleid 
                    JOIN {user} u ON u.id = ue.userid 
                    -- JOIN {user_lastaccess} ul ON ul.courseid = c.id 
                    WHERE ra.userid = {$programinstance->userid} AND pl.active = 1 
                    GROUP BY plc.courseid ORDER BY plc.courseid, plc.mandatory ASC";
            $coursefullname = $DB->get_records_sql_menu($sql1);

            $datamailobject->program_course = implode(", ", array_values($coursefullname));
            $course_keys = array_keys($coursefullname);
            $ckeys = implode(", ", array_values($course_keys));

            foreach($course_enrol as $course_enrols) {
                $course_url = new \moodle_url($CFG->wwwroot.'/course/view.php', array('id' => $course_enrols->cid));
                $program_lc_course_link = \html_writer::link($course_url, $course_url);
                $arr[] = $program_lc_course_link;
            }

            $link = implode(", ", array_values($arr));
            $datamailobject->program_lc_course_link = $link;
            foreach($course_enrol as $course_enrols) {

                $datamailobject->program_lc_course_creater = date('d-m-Y', $course_enrols->course_enrolstartdate);
                $datamailobject->program_level = $course_enrols->level;
                $datamailobject->program_enroluserfulname = fullname($touser);
                $datamailobject->program_enroluseremail = $touser->email;

            }
        }
        if($emailtype == 'program_semester_enrollment'){
            $datamailobject->program_level = $programinstance->level;
            $datamailobject->program_level_creater = $programinstance->usercreated;
            $datamailobject->program_semester_completiondate = $programinstance->timecompleted;
        }
        
        $non_coursenamestring = array('program_semester_completion', 'program_completion', 'program_unenroll', 'program_enrol', 'program_semester_enrollment', 'program_course_enrollment', 'program_course_completion');
        if(!in_array($emailtype, $non_coursenamestring)){
            $programcourses_sql = "SELECT c.id,c.fullname FROM {course} AS c
                JOIN {local_program_level_courses} AS lplc ON lplc.courseid=c.id
                JOIN {local_program_levels} AS lpl ON lpl.id=lplc.levelid
                WHERE lpl.programid=:programid AND lpl.id=:levelid ";
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
        }else{
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
