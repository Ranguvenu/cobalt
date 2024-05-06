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

namespace local_timetable\cron;
require_once($CFG->dirroot.'/mod/attendance/locallib.php');
require_once($CFG->dirroot.'/user/lib.php');
require_once($CFG->dirroot.'/local/costcenter/lib.php');
use costcenter;
use core_text;
use core_user;
use DateTime;
use html_writer;
use stdClass;
use mod_attendance_structure;
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
    private $sessiondata = array();
    private $errorcount = 0;
    private $sessioncount = 0;
    private $warningscount = 0;
    private $updatesupervisor_warningscount = 0;
    private $errormessage;
    private $insertedcount = 0;
    private $updatedcount = 0;
    private $formdata;
    private $existing_session;
    private $employeeid_exist;
    private $semid;

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
        $formdata->sessioncount = 0;
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
            $user->semid = $formdata->semid;
            $user->sessioncount = $formdata->sessioncount;
            if(empty($user->semid)){
                $semcourseid = $DB->get_field('course','id',array('shortname' => $user->coursecode));
                $user->semid = $DB->get_field('local_program_level_courses','levelid',array('courseid' => $semcourseid));
            }
            $this->data[] = $user;
            $this->errors = array();
            $this->warnings = array();
            $this->mfields = array();
            $this->wmfields = array();
            $this->excel_line_number = $linenum;

 
            // building validation
            if (!empty($user->building)) {
                $this->building_room_validation($user);
                $this->building_room_exists($user);
            }          

            if (!empty($user->building)) {
                $this->batch_group_validation($user);
            }  

            // Course name validation.
            if (!empty($user->course_name)) {
                $this->coursename_validation($user);
            } else {
                $strings = new StdClass();
                $strings->excel_line_number = $this->excel_line_number;
                echo '<div class="alert alert-danger w-full">'.get_string('missing_coursename', 'local_timetable', $strings).'</div>';
                $this->errors[] = get_string('missing_coursename', 'local_timetable', $strings);
                $this->mfields[] = 'coursename';
                $this->errorcount++;
            }

            // Course code validation.
            if (!empty($user->coursecode)) {
                $this->coursecode_validation($user);
            } else {
                $strings = new StdClass();
                $strings->excel_line_number = $this->excel_line_number;
                echo '<div class="alert alert-danger w-full">'.get_string('missing_coursecode', 'local_timetable', $strings).'</div>';
                $this->errors[] = get_string('missing_coursecode', 'local_timetable', $strings);
                $this->mfields[] = 'coursecode';
                $this->errorcount++;
            }

            // Teacher email id validation.
            if (!empty($user->teacher_email)) {
                $this->emailid_validation($user);
                if (!empty($user->coursecode)) {
                    $this->teacher_exists($user);
                    if (!is_siteadmin() && !has_capability('local/costcenter:manage_multiorganizations', $systemcontext) && !has_capability('local/costcenter:manage_ownorganization', $systemcontext) && !has_capability('local/costcenter:manage_owndepartments', $systemcontext) && !has_capability('local/costcenter:manage_ownsubdepartments', $systemcontext)) {
                        $this->own_teacher_exists($user);
                    }
                }
            } else {
                $strings = new StdClass();
                $strings->excel_line_number = $this->excel_line_number;
                echo '<div class="alert alert-danger w-full">'.get_string('missing_teacher_email', 'local_timetable', $strings).'</div>';
                $this->errors[] = get_string('missing_teacher_email', 'local_timetable', $strings);
                $this->mfields[] = 'teacher_email';
                $this->errorcount++;
            }

            // Session date validation.
            if (!empty($user->date)) {
                $this->date_validation($user);
            } else {
                $strings = new StdClass();
                $strings->excel_line_number = $this->excel_line_number;
                echo '<div class="alert alert-danger w-full">'.get_string('missing_date', 'local_timetable', $strings).'</div>';
                $this->errors[] = get_string('missing_date', 'local_timetable', $strings);
                $this->mfields[] = 'date';
                $this->errorcount++;
            }

            // Session slottime validation.
            if (!empty($user->slottime)) {
                $this->slottime_validation($user);
            } else {
                $strings = new StdClass();
                $strings->excel_line_number = $this->excel_line_number;
                echo '<div class="alert alert-danger w-full">'.get_string('missing_slottime', 'local_timetable', $strings).'</div>';
                $this->errors[] = get_string('missing_slottime', 'local_timetable', $strings);
                $this->mfields[] = 'slottime';
                $this->errorcount++;
            }

            // Session name validation.
            if (empty($user->session_name)) {
                $strings = new StdClass();
                $strings->excel_line_number = $this->excel_line_number;
                echo '<div class="alert alert-danger w-full">'.get_string('missing_session_name', 'local_timetable', $strings).'</div>';
                $this->errors[] = get_string('missing_session_name', 'local_timetable', $strings);
                $this->mfields[] = 'session_name';
                $this->errorcount++;
            }

            $userobject = $this->preparing_users_object($user);
            if (count($this->errors) > 0) {
            
            }

            if (!empty($user->course_name) && !empty($user->coursecode) &&
                !empty($user->date) && !empty($user->teacher_email) && !empty($user->slottime) &&
                !empty($user->session_name)) {
                $this->session_exists($user);
            }
            $exceldata[] = $user;
        }
        if (count($this->warnings) > 0 ) {
            $this->updatesupervisor_warningscount = count($this->warnings);     
        }
        if ($this->data) {
            if($this->sessioncount > 1 && $this->errorcount == 0){
                $exceldata = json_encode($exceldata);
                $semid = $this->semid;
                echo '<div class="alert alert-warning w-full">'.get_string('multiplesessions', 'local_timetable').'</div>';
                $strings = new StdClass();
                foreach ($this->sessiondata as $key => $value) {
                    $date = date("d-M-Y",$value->sessdate);
                    $strings->date =  $date;
                    $slottime = $DB->get_record('local_timeintervals_slots', array('id' => $value->slotid));
                    $stime = substr_replace($slottime->starttime, '', 5);
                    $etime = substr_replace($slottime->endtime, '', 5);

                    $strings->slottime =  $stime. ' - ' .$etime;
                    echo '<div class="alert alert-warning w-full">'.get_string('eachsessions', 'local_timetable', $strings).'</div>';
                }
                $upload_info .='<div class="alert alert-success w-full">'.get_string('displaymessage', 'local_timetable').'</div>';
                $button = html_writer::tag('button', get_string('yes','local_timetable'), array('class' => 'btn btn-primary'));
                $link = html_writer::tag('a', $button, array('href' => $CFG->wwwroot. '/local/timetable/individual_session.php?tlid='.$formdata->semid.'&sessionaction=singleyes'.'&dataexcel='.$exceldata));

                $button1 = html_writer::tag('button', get_string('no','local_timetable'), array('class' => 'btn btn-primary'));
                $link1 = html_writer::tag('a', $button1, array('href' => $CFG->wwwroot. '/local/timetable/individual_session.php?tlid='.$formdata->semid.'&sessionaction=singleno'.'&dataexcel='.$exceldata));

                $upload_info .='<div class="w-full pull-left text-center">'.$link.' '.$link1.'</div>';
                mtrace($upload_info);
            }
            if($this->sessioncount == 1 && $this->errorcount == 0){
                $semid = $this->semid;
                $strings = new StdClass();
                foreach ($this->sessiondata as $key => $value) {
                    $date = date("d-M-Y",$value->sessdate);
                    $strings->date = $date;
                    $strings->teacherid = $excel->teacher_email;
                    $strings->slottime =  $excel->slottime;
                    $strings->excel_line_number = $this->excel_line_number;
                    echo '<div class="alert alert-warning w-full">'.get_string('singlesession', 'local_timetable', $strings).'</div>';
                }
                $exceldata = json_encode($exceldata);
                $upload_info .='<div class="alert alert-success w-full">'.get_string('displaymessage', 'local_timetable').'</div>';
                $button = html_writer::tag('button', get_string('yes','local_timetable'), array('class' => 'btn btn-primary'));
                $link = html_writer::tag('a', $button, array('href' => $CFG->wwwroot. '/local/timetable/individual_session.php?tlid='.$formdata->semid.'&sessionaction=singleyes'.'&dataexcel='.$exceldata));

                $button1 = html_writer::tag('button', get_string('no','local_timetable'), array('class' => 'btn btn-primary'));
                $link1 = html_writer::tag('a', $button1, array('href' => $CFG->wwwroot. '/local/timetable/individual_session.php?tlid='.$formdata->semid.'&sessionaction=singleno'.'&dataexcel='.$exceldata));

                $upload_info .='<div class="w-full pull-left text-center">'.$link.' '.$link1.'</div>';
                mtrace($upload_info);
            }
            if($this->sessioncount == 0 && $this->errorcount == 0){
                $sessionid = implode(',', $this->sessiondata);
                $semid = $this->semid;
                if($sessionid){
                    $sessiontabledata = $DB->get_records_sql("SELECT * FROM {attendance_sessions} WHERE id IN ($sessionid)");
                    $strings = new StdClass();
                    foreach ($sessiontabledata as $key => $value) {
                        $date = date("d-M-Y",$value->sessdate);
                        $strings->date = $date;
                        echo '<div class="alert alert-success w-full">'.get_string('addedtimetable_msg', 'local_timetable', $strings).'</div>';
                    }
                }
                $button = html_writer::tag('button', get_string('continue','local_timetable'), array('class' => 'btn btn-primary'));
                $link = html_writer::tag('a', $button, array('href' => $CFG->wwwroot. '/local/timetable/individual_session.php?tlid='.$formdata->semid));
                $upload_info .='<div class="w-full pull-left text-center">'.$link.'</div>';
                mtrace($upload_info);
            }
            if($this->sessioncount == 0 && $this->errorcount >= 1){
                $semid = $this->semid;
                if ($this->insertedcount > 0) {
                    echo '<div class="alert alert-success w-full">'.get_string('insertedtimetable_msg', 'local_timetable', $this->insertedcount).'</div>';
                }
                $button = html_writer::tag('button', get_string('continue','local_timetable'), array('class' => 'btn btn-primary'));
                $link = html_writer::tag('a', $button, array('href' => $CFG->wwwroot. '/local/timetable/sync/hrms_async.php?semid='.$formdata->semid.'&action=1'));
                $upload_info .='<div class="w-full pull-left text-center">'.$link.'</div>';
                mtrace($upload_info);   
            }
            if($this->sessioncount >= 1 && $this->errorcount >= 1){
                $semid = $this->semid;
                $button = html_writer::tag('button', get_string('continue','local_timetable'), array('class' => 'btn btn-primary'));
                $link = html_writer::tag('a', $button, array('href' => $CFG->wwwroot. '/local/timetable/sync/hrms_async.php?semid='.$formdata->semid.'&action=1'));
                $upload_info .='<div class="w-full pull-left text-center">'.$link.'</div>';
                mtrace($upload_info);   
            }
        } else {
            echo '<div class="alert alert-danger w-full">'.get_string('providecoursename','local_timetable').'</div>';
            echo '<div class="alert alert-danger w-full">'.get_string('providecoursecode','local_timetable').'</div>';
            echo '<div class="alert alert-danger w-full">'.get_string('provideteachermailid','local_timetable').'</div>';
            echo '<div class="alert alert-danger w-full">'.get_string('providesessiondate','local_timetable').'</div>';
            echo '<div class="alert alert-danger w-full">'.get_string('providesessionslot','local_timetable').'</div>';
            echo '<div class="alert alert-danger w-full">'.get_string('providesessionname','local_timetable').'</div>';
            echo '<div class="alert alert-danger w-full">'.get_string('providesessiondescription','local_timetable').'</div>';
            $button = html_writer::tag('button',get_string('button','local_users'),array('class'=>'btn btn-primary'));
            $link = html_writer::tag('a', $button, array('href' => $CFG->wwwroot. '/local/timetable/sync/hrms_async.php?semid='.$formdata->semid.'&action=1'));
            $upload_info .='<div class="w-full pull-left text-xs-center">'.$link.'</div>';
            mtrace( $upload_info);
        }
    }//end of main_hrms_frontendform_method

    public function preparing_users_object($excel, $formdata=null) {
        global $USER, $DB, $CFG;
        $date = strtotime($excel->date);
        if($excel->semid){
            $timeintervalid = $DB->get_field('local_timeintervals','id', array('semesterid' => $excel->semid));
        }
        $courseid = $DB->get_field('course','id', array('shortname'=>$excel->coursecode));
        $attendanceid = $DB->get_field('attendance','id',array('course' => $courseid));
        $teacherid = $DB->get_field('user','id',array('email'=>$excel->teacher_email));
        $totaltime = explode('-', $excel->slottime);
        $starttime = '';
        $endtime = '';
        foreach ($totaltime as $key => $value) {
            $starttime = $totaltime[0];            
            $endtime = $totaltime[1];
        }
        $stime = explode(':', $starttime);
        $etime = explode(':', $endtime);
        
        $excel->sestime = array(
            'starthour' => $stime[0],
            'startminute' => $stime[1],
            'endhour' => $etime[0],
            'endminute' => $etime[1]
        );
        $array = array();
        $array['timeintervalid'] = $timeintervalid;
        if($totaltime){
            $sql = "SELECT id  FROM {local_timeintervals_slots} WHERE starttime LIKE '%$starttime%' AND endtime LIKE '%$endtime%' AND timeintervalid = :timeintervalid";
            $slotid = $DB->get_record_sql($sql, $array);
        }

        $user = new \stdclass();
        $user->coursesid = $courseid;
        if (strlen($excel->building) > 0) {
            $buildingid = $DB->get_field('local_location_institutes', 'id', ['fullname' => $excel->building]);
        } else {
            $buildingid = 0;
        }
        if (strlen($excel->room) > 0) {
            $roomid = $DB->get_field('local_location_room', 'id', ['name' => $excel->room]);
        } else {
            $roomid = 0;
        }
        if (strlen($excel->group) > 0) {
            $groupid = $DB->get_field('cohort', 'id', ['idnumber' => $excel->group, 'visible' => 0]);
        } else {
            $groupid = 0;
        }
        $user->attendanceid = $attendanceid;
        $user->calendarevent = 1;
        $user->studentscanmark = 1;
        $user->includeqrcode = 1;
        $user->rotateqrcode = 1;
        $user->rotateqrcodesecret = local_attendance_random_string();
        $user->autoassignstatus = 1;
        $user->usedefaultsubnet = 1;
        $user->automarkcompleted = 0;
        $user->preventsharedip = 0;
        $user->preventsharediptime = 0;
        $user->teacherid = $teacherid;
        $user->sessionname = $excel->session_name;
        $user->sessiondate = $date;
        $user->sestime = $excel->sestime;
        $user->slotid = $slotid->id;
        $user->description = $excel->description;
        $user->building = $buildingid;
        $user->room = $roomid;
        $user->batch_group = $groupid;
        $user->timemodified = time();
        return $user;
    } // end of  preparing_users_object method

    public function emailid_validation($excel) {
        global $DB;
        $strings = new StdClass();
        $strings->teacher_email = $excel->teacher_email;
        $strings->excel_line_number = $this->excel_line_number;
        $this->teacher_email = $excel->teacher_email;
        if (!validate_email($excel->teacher_email)) {
            echo '<div class="alert alert-danger w-full">'.get_string('invalidemail_msg', 'local_timetable', $strings).'</div>';
            $this->errors[] = get_string('invalidemail_msg', 'local_timetable', $strings);
            $this->mfields[] = 'teacher_email';           
            $this->errorcount++;
        }
    }
    // Batch_group_validation
    public function batch_group_validation($excel) {
        global $DB;
        $strings = new StdClass();
        $strings->group =  $excel->group;
        $strings->excel_line_number = $this->excel_line_number;
        $groupid = $DB->get_field('cohort', 'id', ['idnumber' => $excel->group]);
        if($groupid){
            $parentgroupid = $DB->get_field('local_sub_groups', 'parentid', ['groupid' => $groupid]);
            $groupprogramid = $DB->get_field('local_program', 'id', ['batchid' => $parentgroupid]);
            $programid = $DB->get_field('local_program_levels', 'programid', ['id' => $excel->semid]);
            if($groupprogramid != $programid){
                echo '<div class="alert alert-danger w-full">'.get_string('groupidnotexists', 'local_timetable', $strings).'</div>';
                $this->errors[] = get_string('groupidnotexists', 'local_timetable', $strings);
                $this->mfields[] = 'group';
                $this->errorcount++; 
            }
        }
    }

    // Teacher_exists_validation.
    public function teacher_exists($excel){
        global $DB;
        $userdata = $DB->get_record('user', array('email' => $excel->teacher_email));

        $slottimes = explode('-', $excel->slottime);
        $stime = explode(':', $slottimes[0]);
        $etime = explode(':', $slottimes[1]);
        
        $sesstarttime = $stime[0] * HOURSECS + $stime[1] * MINSECS;
        $endtime = $etime[0] * HOURSECS + $etime[1] * MINSECS;

        $sessstart = strtotime($excel->date) + $sesstarttime;
        $sessend = strtotime($excel->date) + $endtime;

        $teacher_existssql = "SELECT *
                                FROM {attendance_sessions}
                               WHERE teacherid = ? AND (('{$sessstart}' BETWEEN sessdate AND sessedate) OR ('{$sessend}' BETWEEN sessdate AND sessedate))";
        $teacher_exists = $DB->get_record_sql($teacher_existssql, [$userdata->id]);
        $strings = new StdClass();
        $teacherid = $DB->get_field('user', 'id', ['username' => $excel->teacher_email]);
        if ($teacher_exists->teacherid != $teacherid && $teacher_exists->sessdate == $sessstart && $teacher_exists->sessedate == $sessend) {
                $strings->teacher_fullname = fullname($userdata);
                $strings->date = date('d-m-Y H:i', $teacher_exists->sessdate).'-'.date('H:i', $teacher_exists->sessedate);
                echo '<div class="alert alert-danger w-full">'.get_string('teacher_already_exists', 'local_timetable', $strings).'</div>';
                $this->errors[] = get_string('teacher_already_exists', 'local_timetable', $strings);
                $this->mfields[] = 'teacher_email';           
                $this->errorcount++;
        }

        $courseid = $DB->get_field('course','id',array('shortname' => $excel->coursecode));
        if ($courseid) {
            $contexcourse = \context_course::instance($courseid);
            $strings->excel_line_number = $this->excel_line_number;
            $strings->course = $excel->course_name;

            if (!is_enrolled($contexcourse, $userdata)) { 
                echo '<div class="alert alert-danger w-full">'.get_string('invalidteahcer_msg', 'local_timetable', $strings).'</div>';
                $this->errors[] = get_string('invalidteahcer_msg', 'local_timetable', $strings);
                $this->mfields[] = 'teacher_email';           
                $this->errorcount++;
            }
        }
    }

    // Own_Teacher_exists_validation.
    public function own_teacher_exists($excel){
        global $DB, $USER;
        $courseid = $DB->get_field('course','id',array('shortname' => $excel->coursecode));
        if ($courseid) {
            $contexcourse = \context_course::instance($courseid);
            $userid = $DB->get_field('user', 'id', array('email' => $excel->teacher_email));
            $strings = new StdClass();
            $strings->excel_line_number = $this->excel_line_number;
            $strings->userid = $userid;
            if ($USER->id != $userid) {
                echo '<div class="alert alert-danger w-full">'.get_string('invalidownteacher_msg', 'local_timetable', $strings).'</div>';
                $this->errors[] = get_string('invalidownteacher_msg', 'local_timetable', $strings);
                $this->mfields[] = 'own_teacher_email';           
                $this->errorcount++;
            }
        }
    }

    // slottime_validation().
    public function slottime_validation($excel) {
        global $DB;
        $strings = new StdClass();
        $array = array();
        $strings->excel_line_number = $this->excel_line_number;
        if ($excel->semid) {
            $timeintervalslot = $DB->get_field_sql("SELECT id FROM {local_timeintervals} lti WHERE semesterid = $excel->semid");
            $totaltime = explode('-', $excel->slottime);
            $starttime = '';
            $endtime = '';
            foreach ($totaltime as $key => $value) {
                $starttime = $totaltime[0];            
                $endtime = $totaltime[1];
            }
            $strings->starttime = $starttime; 
            $strings->endtime = $endtime;
            $array['timeintervalslot'] = $timeintervalslot;
            $sql = "SELECT * FROM {local_timeintervals_slots} WHERE timeintervalid = :timeintervalslot  AND starttime LIKE '%$starttime%' AND endtime LIKE '%$endtime%'";
            $slot_timings_exists = $DB->get_record_sql($sql, $array);
            if(empty($slot_timings_exists)){
                echo '<div class="alert alert-danger w-full">'.get_string('slot_timenotexists', 'local_timetable', $strings).'</div>';
                $this->errors[] = get_string('slot_timenotexists', 'local_timetable', $strings);
                $this->mfields[] = 'slottime';
                $this->errorcount++;
            }
        }
    }

    public function coursecode_validation($excel) {
        global $DB;
        $strings = new StdClass();
        $strings->coursecode = $excel->coursecode;
        $strings->excel_line_number = $this->excel_line_number;
        $this->coursecode = $excel->coursecode;
        if(!$DB->record_exists('course',array('shortname' => $excel->coursecode))){
            echo '<div class="alert alert-danger w-full">'.get_string('invalidcoursecode_msg', 'local_timetable', $strings).'</div>';
            $this->errors[] = get_string('invalidcoursecode_msg', 'local_timetable', $strings);
            $this->mfields[] = 'coursecode';
            $this->errorcount++;
        }
    }

    public function coursename_validation($excel) {
        global $DB;
        $strings = new StdClass();
        $strings->course_name =  $excel->course_name;
        $strings->excel_line_number = $this->excel_line_number;
        $this->courseconame = $excel->course_name;
        if($excel->coursecode){
            if(!$DB->record_exists('course',array('fullname' => $excel->course_name , 'shortname' => $excel->coursecode))){
                echo '<div class="alert alert-danger w-full">'.get_string('invalidcoursename_msg', 'local_timetable', $strings).'</div>';
                $this->errors[] = get_string('invalidcoursename_msg', 'local_timetable', $strings);
                $this->mfields[] = 'course_name';
                $this->errorcount++;
            }
        }
    }
    // building_room_validation
    public function building_room_validation($excel) {
        global $DB;
        $strings = new StdClass();
        $strings->building =  $excel->building;
        $strings->room =  $excel->room;
        $strings->excel_line_number = $this->excel_line_number;
        $costcenterid = $DB->get_field('course', 'open_costcenterid', ['shortname' => $excel->coursecode]);
        
        if($excel->building){
            $sql = "SELECT id FROM {local_location_institutes} WHERE costcenter = $costcenterid AND fullname LIKE '%$excel->building%' ";
            $buildingid = $DB->get_field_sql($sql);
            if(!empty($buildingid)){
                $roomsql = "SELECT * FROM {local_location_room} WHERE name LIKE  '%$excel->room%'";
                $roomdata = $DB->get_record_sql($roomsql);        
                if($roomdata->instituteid != $buildingid){
                   echo '<div class="alert alert-danger w-full">'.get_string('invalidroombuilding', 'local_timetable', $strings).'</div>';
                    $this->errors[] = get_string('invalidroombuilding', 'local_timetable', $strings);
                    $this->mfields[] = 'buildingroom';
                    $this->errorcount++; 
                }
            } else {
                    echo '<div class="alert alert-danger w-full">'.get_string('buildingnotexists', 'local_timetable', $strings).'</div>';
                    $this->errors[] = get_string('buildingnotexists', 'local_timetable', $strings);
                    $this->mfields[] = 'buildingroom';
                    $this->errorcount++;
            }
        }
    }
    // building_room_exists
    public function building_room_exists($excel){
        global $DB;
        $buildingdata = $DB->get_record('local_location_institutes', array('fullname' => $excel->building));
        $roomdata = $DB->get_record('local_location_room', array('instituteid' => $buildingdata->id));

        $slottimes = explode('-', $excel->slottime);
        $stime = explode(':', $slottimes[0]);
        $etime = explode(':', $slottimes[1]);
        
        $sesstarttime = $stime[0] * HOURSECS + $stime[1] * MINSECS;
        $endtime = $etime[0] * HOURSECS + $etime[1] * MINSECS;

        $sessstart = strtotime($excel->date) + $sesstarttime;
        $sessend = strtotime($excel->date) + $endtime;

        $building_room_existssql = "SELECT *
                                FROM {attendance_sessions}
                               WHERE building = ? AND room = ? AND (('{$sessstart}' BETWEEN sessdate AND sessedate) OR ('{$sessend}' BETWEEN sessdate AND sessedate))";
        $building_room_exists = $DB->get_record_sql($building_room_existssql, [$buildingdata->id, $roomdata->id]);
        $strings = new StdClass();        
        if ($building_room_exists && $building_room_exists->sessdate == $sessstart && $building_room_exists->sessedate == $sessend) {
                $strings->buildingname = ucfirst($buildingdata->fullname);
                $strings->roomname = ucfirst($roomdata->name);
                $strings->date = date('d-m-Y H:i', $building_room_exists->sessdate).'-'.date('H:i', $building_room_exists->sessedate);
                echo '<div class="alert alert-danger w-full">'.get_string('building_already_exists', 'local_timetable', $strings).'</div>';
                $this->errors[] = get_string('building_already_exists', 'local_timetable', $strings);
                $this->mfields[] = 'building_room';           
                $this->errorcount++;
        }
    }

    public function date_validation($excel){
        global $DB;
        $slottimes = explode('-', $excel->slottime);
        $stime = explode(':', $slottimes[0]);
        $sesstarttime = $stime[0] * HOURSECS + $stime[1] * MINSECS;

        /*$date = explode('-', $excel->date);
        $newdate = $date[1].'-'.$date[0].'-'.$date[2];*/

        $sessstart = strtotime($excel->date) + $sesstarttime;
        $userdata = $DB->get_record('user', array('email' => $excel->teacher_email));
        $semstartdate = $DB->get_field('local_program_levels','startdate' ,array('id' => $excel->semid));
        $levelname = $DB->get_field('local_program_levels','level' ,array('id' => $excel->semid));
        $courseid = $DB->get_field('course','id' ,array('shortname' => $excel->coursecode));
        
        $semcourse = $DB->get_field('local_program_level_courses','courseid' ,array('courseid' => $courseid, 'levelid' => $excel->semid));
        $semenddate = $DB->get_field('local_program_levels','enddate' ,array('id' => $excel->semid));
        $exceldate = strtotime($excel->date);

        $teacher_times_exists = $DB->get_record('attendance_sessions', ['teacherid' => $userdata->id, 'sessdate' => $sessstart]);
        $strings  = new \StdClass();
        $strings->excel_line_number = $this->excel_line_number;
        $strings->coursename = $excel->course_name;
        $strings->levelname = $levelname;
        if(($exceldate < $semstartdate || $exceldate > $semenddate)) {
            echo '<div class="alert alert-danger w-full">'.get_string('invaliddate_msg', 'local_timetable', $strings).'</div>';
            $this->errors[] = get_string('invaliddate_msg', 'local_timetable', $strings);
            $this->mfields[] = 'date';
            $this->errorcount++;
        }
        if(empty($semcourse)) {
            echo '<div class="alert alert-danger w-full">'.get_string('coursenotexists', 'local_timetable', $strings).'</div>';
            $this->errors[] = get_string('coursenotexists', 'local_timetable', $strings);
            $this->mfields[] = 'date';
            $this->errorcount++;
        }
    }

    public function session_exists($excel) {
        global $DB,$CFG;
        $semid = $this->semid;
        if($excel->semid){
            $timeintervalid = $DB->get_field('local_timeintervals','id', array('semesterid' => $excel->semid));
        }
        $userobject = $this->preparing_users_object($excel);
        $totaltime = explode('-', $excel->slottime);
        $starttime = '';
        $endtime = '';
        foreach ($totaltime as $key => $value) {
            $starttime = $totaltime[0];            
            $endtime = $totaltime[1];
        }
        $courseid = $DB->get_field('course','id', array('shortname'=>$excel->coursecode));
        if ($courseid) {
            $pageparams->action = 1;
            $cmidsql = "SELECT cm.id
                         FROM {course_modules} cm
                         JOIN {modules} m ON cm.module = m.id
                        WHERE cm.course = {$courseid} AND m.name = 'attendance' "; 
            $id = $DB->get_field_sql($cmidsql);
            $cm = get_coursemodule_from_id('attendance', $id, 0, false, MUST_EXIST);
            $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
            $attdata = $DB->get_record('attendance', array('id' => $cm->instance), '*', MUST_EXIST);
            $context = \context_module::instance($cm->id);
            $att = new mod_attendance_structure($attdata, $cm, $course, $context, $pageparams);
                
            $sessions = sessions_data_for_add($userobject, $att);
            // foreach ($sessions as $key => $sess) {
            //     $sessions[$key] = $sess->duration;
            //     $sesstarttime = $sess->sesstarttime;
            // }
            // $exceldate = strtotime($excel->date);
            // $exceldate += $sesstarttime;
            $array = array();
            $array['timeintervalid'] = $timeintervalid;
            $sql = "SELECT id  FROM {local_timeintervals_slots} WHERE starttime LIKE '%$starttime%' AND endtime LIKE '%$endtime%' AND timeintervalid = :timeintervalid";
            $slotid = $DB->get_record_sql($sql, $array);
            if($slotid){
                $sessionexists_count = $DB->record_exists_sql("SELECT id FROM {attendance_sessions} WHERE sessdate = '{$sessions[0]->sessdate}' AND slotid = $slotid->id");
            }
            if($sessionexists_count){
                $this->sessiondata[] = $DB->get_record_sql("SELECT * FROM {attendance_sessions} WHERE sessdate = '{$sessions[0]->sessdate}' AND slotid = $slotid->id");
                $this->sessioncount++;
            }
            if($this->sessioncount == 0 && $this->errorcount == 0) {
                $this->insertedcount++;
                // sessions_data_for_add
                // $userobject->duration = $sessions[0];
                // $userobject->sessdate += $sesstarttime;
                // print_object($sessions[0]);die;
                $this->sessiondata[] = $DB->insert_record('attendance_sessions', $sessions[0]);   
            }
            $this->semid = $excel->semid;
        }
    } // session_exists
} //end of class
