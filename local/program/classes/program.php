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

/**
 * program View
 *
 * @package    local_program
 * @copyright  2018 Arun Kumar M <arun@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_program;
defined('MOODLE_INTERNAL') || die();
use context_system;
use stdClass;
use moodle_url;
use completion_completion;
use html_table;
use html_writer;
use core_component;
use Exception;
require_once($CFG->dirroot . '/local/program/lib.php');
require_once($CFG->dirroot . '/enrol/cohort/lib.php');
if (file_exists($CFG->dirroot . '/local/lib.php')) {
  require_once($CFG->dirroot . '/local/lib.php');
}
require_once($CFG->dirroot . '/course/externallib.php');

// Program
define('program_NEW', 0);
define('program_COMPLETED', 2);
// Session Attendance
define('SESSION_PRESENT', 1);
define('SESSION_ABSENT', 2);
// Types
define('program', 1);

class program {
    /**
     * Manage program (Create or Update the program)
     * @method manage_program
     * @param  Object           $data Clasroom Data
     * @return Integer               program ID
     */
    public function manage_program($program) {
        global $DB, $USER;
        $program->shortname = $program->name;
        $systemcontext = context_system::instance();
        // $batchsql = "SELECT *
        //               FROM {local_groups}
        //              WHERE cohortid = :batchid";
        // $programbatch = $DB->get_records_sql($batchsql, array('batchid' => $program->batchid));
        // $batch = array_values($programbatch);

        if(!$program->selfenrol){
          $program->approvalreqd = 0;
        }
        if (empty($program->trainers)) {
            $program->trainers = null;
        }
        if (empty($program->capacity) || $program->capacity == 0) {
            $program->capacity = 0;
        }

        file_save_draft_area_files($program->programlogo, 1, 'local_program', 'programlogo', $program->programlogo);
        //$program->startdate = 0;
        //$program->enddate = 0;
        $program->description = $program->cr_description_editor['text'];
        try {
            if ($program->id > 0) {
                $program->timemodified = time();
                $program->usermodified = $USER->id;

                if($program->map_certificate == 1){
                  $program->certificateid = $program->certificateid;
                }else{
                  $program->certificateid = null;
                }
                $program->costcenter = $program->open_costcenterid;
                $program->department = $program->open_departmentid;
                $program->subdepartment = $program->open_subdepartment;
                
                // To check semester start-date exists or not.
                $existingsemstdatesql = "SELECT MIN(startdate) as startdate
                                   FROM {local_program_levels}
                                  WHERE programid = {$program->id} AND startdate <> 0";
                $existingsemstdate = $DB->get_field_sql($existingsemstdatesql);

                // Automated updation semesters start-date and end-date.
                if (!empty($existingsemstdate)) {
                    if ($program->program_startdate) {
                        $startdateexist = $DB->get_field('local_program', 'program_startdate', array('id'=>$program->id));
                        $programdata = $DB->get_record('local_program', array('id' => $program->id));

                        $pdiffdays = date('d-m-Y', $program->program_startdate);
                        $odiffdays = date('d-m-Y', $startdateexist);
                        $newpdate = date_create("$pdiffdays");
                        $existingpdate = date_create("$odiffdays");
                        $diffdays = date_diff($newpdate, $existingpdate);

                        if($startdateexist < $program->program_startdate && $startdateexist != $program->program_startdate) {
                            $levelids = $DB->get_records('local_program_levels', array('programid'=>$program->id));
                                ;
                            foreach($levelids as $levelidvalue) {
                                for ($i=0; $i < count($levelids) ; $i++) {
                                    if ($diffdays->days) {
                                        $sdate = $levelidvalue->startdate;
                                        $edate = $levelidvalue->enddate;
                                        if ($sdate != 0 && $edate != 0) {
                                            $newsdate = strtotime('+ '.$diffdays->days.' days', $sdate);
                                            $newedate = strtotime('+ '.$diffdays->days.' days', $edate);
                                        } else {
                                            $newsdate = 0;
                                            $newedate = 0;
                                        }
                                    } else {
                                        $newsdate = 0;
                                        $newedate = 0;
                                    }
                                    $sem_startdate = new stdClass();
                                    $sem_startdate->id = $levelidvalue->id;
                                    $sem_startdate->programid = $program->id;
                                    $sem_startdate->startdate = $newsdate;
                                    $sem_startdate->enddate = $newedate;
                                    $DB->update_record('local_program_levels', $sem_startdate);
                                }
                            }
                        } else if ($startdateexist > $program->program_startdate && $startdateexist != $program->program_startdate) {
                            $levelids = $DB->get_records('local_program_levels', array('programid'=>$program->id));
                                ;
                            foreach($levelids as $levelidvalue) {
                                for ($i=0; $i < count($levelids) ; $i++) {
                                    if ($diffdays->days) {
                                        $sdate = $levelidvalue->startdate;
                                        $edate = $levelidvalue->enddate;
                                        if ($sdate != 0 && $edate != 0) {
                                            $newsdate = strtotime('- '.$diffdays->days.' days', $sdate);
                                            $newedate = strtotime('- '.$diffdays->days.' days', $edate);
                                        } else {
                                            $newsdate = 0;
                                            $newedate = 0;
                                        }
                                    } else {
                                        $newsdate = 0;
                                        $newedate = 0;
                                    }
                                    $sem_startdate = new stdClass();
                                    $sem_startdate->id = $levelidvalue->id;
                                    $sem_startdate->programid = $program->id;
                                    $sem_startdate->startdate = $newsdate;
                                    $sem_startdate->enddate = $newedate;
                                    $DB->update_record('local_program_levels', $sem_startdate);
                                }
                            }
                        }
                    }
                } // End of if.
                $DB->update_record('local_program', $program);
                $params = array(
                    'context' => $systemcontext,
                    'objectid' => $program->id
                );
                // Trigger program updated event.

                $event = \local_program\event\program_updated::create($params);
                $event->add_record_snapshot('local_program', $program);
                $event->trigger();


            } else {
                $program->status = 0;
                $program->timecreated = time();
                $program->usercreated = $USER->id;
                $program->costcenter = $program->open_costcenterid;
                $program->department = $program->open_departmentid;
                $program->subdepartment = $program->open_subdepartment;
                if (has_capability('local/program:manageprogram', $systemcontext)) {
                    $program->department = $program->open_departmentid;
                    $program->subdepartment = $program->open_subdepartment;
                    if (!is_siteadmin() && (has_capability('local/program:manage_owndepartments', $systemcontext)
                      || has_capability('local/costcenter:manage_owndepartments', $systemcontext))) {
                        $program->department = $USER->open_departmentid;
                    }
                }

                $program->id = $DB->insert_record('local_program', $program);

                $cmembers = $DB->get_records_sql("SELECT id, cohortid, userid FROM {cohort_members} WHERE cohortid = {$program->batchid}");
                
                foreach($cmembers as $cmember){
                    if($cmember->cohortid == $program->batchid){
                        $insertdata = new stdClass();
                        $insertdata->programid = $program->id;
                        $insertdata->userid = $cmember->userid;
                        $insertdata->usercreated = $USER->id;
                        $insertdata->usermodified = $USER->id;
                        $insertdata->supervisorid = 0;
                        $insertdata->hours = 0;
                        $insertdata->timecreated = time();
                        $insertdata->timemodified = time();
                        $DB->insert_record('local_program_users', $insertdata);
                    }
                }
      
                $params = array(
                    'context' => context_system::instance(),
                    'objectid' => $program->id
                );

                $event = \local_program\event\program_created::create($params);
                $event->add_record_snapshot('local_program', $program);
                $event->trigger();

                $program->shortname = 'program' . $program->id;
                $DB->update_record('local_program', $program);
                if ($program->id) {
                    $leveldata = new stdClass();
                    $leveldata->programid = $program->id;
                    $this->manage_program_stream_levels($leveldata, true);
                }
            }
            $program->totallevels = $DB->count_records('local_program_levels', array('programid' => $program->id));
            $DB->update_record('local_program', $program);
        } catch (dml_exception $ex) {
            print_error($ex);
        }
        return $program->id;
    }

    /**
     * @param int $programid Id of the program for which users need to be counted
     * @return number of users in a program.
     */
    public function count_enrolled_users($programid) {
        global $DB;
        $enrolcount = $DB->count_records_sql(" SELECT count(cm.id) FROM {cohort_members} cm
            JOIN {cohort} c on c.id=cm.cohortid
            JOIN {local_program} p on p.batchid=c.id
            JOIN {user} u ON cm.userid = u.id
            WHERE p.id=$programid AND u.deleted = 0 AND u.suspended = 0
            ");
        return $enrolcount;
    }
    /**
     * programs
     * @method programs
     * @param  Object     $stable Datatable fields
     * @return Array  programs and totalprogramcount
     */
    public function programs($stable, $request = false,$subdepts = null, $costcenterid= null, $departmentid = null,$program = null,$status = null,$groups = null) {
        global $DB, $USER;
        $params = array();
        $programs = array();
        $programscount = 0;
        $concatsql = '';
        $systemcontext = context_system::instance();
        if (!empty($stable->search)) {
            $fields = array("bc.name");
            $fields .= " LIKE :search2 ";
            $params['search2'] = '%' . $stable->search . '%';
            $concatsql .= " AND ".$DB->sql_like('bc.name', ':search2', false);

        }

        if (has_capability('local/program:manageprogram', $systemcontext) &&
            (!is_siteadmin() && (!has_capability('local/program:manage_multiorganizations', $systemcontext) && !has_capability('local/costcenter:manage_multiorganizations', $systemcontext))
                && has_capability('local/costcenter:manage_ownorganization', $systemcontext))) {
                $condition = " AND (cc.id = :costcenter)";
                $params['costcenter'] = $USER->open_costcenterid;
            $concatsql .= $condition;
        } else if (!is_siteadmin() /*&& has_capability('local/program:manage_owndepartments', $systemcontext)*/ && has_capability('local/costcenter:manage_owndepartments', $systemcontext)) {
                $condition .= " AND (bc.costcenter = :costcenter ) AND (bc.department = :department )";
                $params['costcenter'] = $USER->open_costcenterid;
                $params['department'] = $USER->open_departmentid;
                $concatsql .= $condition;
        } else if (!is_siteadmin() /*&& has_capability('local/program:manage_owndepartments', $systemcontext)*/ && has_capability('local/costcenter:manage_ownsubdepartments', $systemcontext)) {
                $condition .= " AND (bc.costcenter = :costcenter ) AND (bc.subdepartment = :subdepartment ) AND (bc.department = :department )";
                $params['costcenter'] = $USER->open_costcenterid;
                $params['department'] = $USER->open_departmentid;
                $params['subdepartment'] = $USER->open_subdepartment;
                $concatsql .= $condition;
        }
        else if (!is_siteadmin() && (!has_capability('local/program:manage_multiorganizations', $systemcontext) && !has_capability('local/costcenter:manage_multiorganizations', $systemcontext)) && !has_capability('local/costcenter:manage_owndepartments', $systemcontext)) {
            $myprograms = $DB->get_records_menu('local_program_users',
                array('userid' => $USER->id), 'id', 'id, programid');
            if (isset($stable->programid) && !empty($stable->programid)) {
                $userenrolstatus = $DB->record_exists('local_program_users',
                    array('programid' => $stable->programid, 'userid' => $USER->id));
                $status = $DB->get_field('local_program', 'status',
                    array('id' => $stable->programid));
                 $program_costcenter = $DB->get_field('local_program', 'costcenter',
                    array('id' => $stable->programid));
                if ($status == 1 && !$userenrolstatus &&
                    $program_costcenter == $USER->open_costcenterid) {
                } else {
                    if (!empty($myprograms)) {
                        $myprograms = implode(', ', $myprograms);
                        $concatsql .= " AND bc.id IN ( $myprograms ) and bc.visible=1";
                    } else {
                        return compact('programs', 'programscount');
                    }
                }
            } else {
                if (!empty($myprograms)) {
                    $myprograms = implode(', ', $myprograms);
                    $concatsql .= " AND bc.id IN ( $myprograms ) and bc.visible=1";
                } else {
                    return compact('programs', 'programscount');
                }
            }
        }
        if (isset($stable->programid) && $stable->programid > 0) {
            $concatsql .= " AND bc.id = :programid";
            $params['programid'] = $stable->programid;
        }
        //added revathi
        if($subdepts != NULL && $subdepts != 'null' ) {
            if(is_array($subdepts)){
                $subdepts = implode(',', $subdepts);
            } else {
                $subdepts = $subdepts;
            }
          $concatsql .= " AND bc.subdepartment IN ($subdepts) ";
        }
        // if($costcenterid != NULL && $costcenterid != 'null' ) {
        //   $concatsql .= " AND bc.costcenter IN ($costcenterid) ";
        // }

         if($costcenterid != NULL && $costcenterid != 'null' ) {
            if(is_array($costcenterid)){
                $costcenterid = implode(',', $costcenterid);
            } else {
                $costcenterid = $costcenterid;
            }
          $concatsql .= " AND bc.costcenter IN ($costcenterid) ";
        }


        if($departmentid != NULL && $departmentid != 'null' ) {
            if(is_array($departmentid)){
                $departmentid = implode(',', $departmentid);
            } else {
                $departmentid = $departmentid;
            }
          $concatsql .= " AND bc.department IN ($departmentid) ";
        }
        if($program != NULL && $program != 'null' ) {
          $concatsql .= " AND bc.id IN ($program) ";
        }
        if($groups != NULL && $groups != 'null' ) {
          $concatsql .= " AND bc.batchid IN ($groups) ";
        }
        if(!empty($status)){
          $filterstatus = explode(',',$status);
          if(!(in_array('active',$filterstatus) && in_array('inactive',$filterstatus))){
              if(in_array('active' ,$filterstatus)){
                  $concatsql .= " AND bc.visible = 1 ";
              }else if(in_array('inactive' ,$filterstatus)){
                  $concatsql .= " AND bc.visible = 0 ";
              }
          }
        }
        //end
        $countsql = "SELECT COUNT(bc.id) ";

            $fromsql = "SELECT bc.*, (SELECT COUNT(DISTINCT cu.userid)
                                  FROM {local_program_users} AS cu
                                  JOIN {user} u ON cu.userid = u.id
                                  WHERE cu.programid = bc.id AND u.deleted = 0 AND u.suspended = 0
                              ) AS enrolled_users, (SELECT COUNT(DISTINCT bu.userid)
                                  FROM {local_program_users} AS bu
                                  WHERE bu.programid = bc.id AND bu.completion_status = 1 AND bu.completiondate > 0
                              ) AS completed_users";

        if (has_capability('local/program:manageprogram', $systemcontext)
            && !is_siteadmin() && !has_capability('local/program:manage_multiorganizations', $systemcontext)
            && !has_capability('local/costcenter:manage_multiorganizations', $systemcontext)
            && has_capability('local/costcenter:manage_ownorganization', $systemcontext)) {
                $joinon = "cc.id = bc.costcenter";
            
        } else if (/*(has_capability('local/program:manage_owndepartments', $systemcontext) || */!is_siteadmin() && has_capability('local/costcenter:manage_owndepartments', $systemcontext)) {
                $joinon = "cc.id = bc.department";
        } else {
            $joinon = "cc.id = bc.costcenter";
        }
        $sql = " FROM {local_program} AS bc
                 JOIN {local_costcenter} AS cc ON $joinon
                WHERE 1 = 1 ";
        $sql .= $concatsql;

        if (isset($stable->programid) && $stable->programid > 0) {
            $programs = $DB->get_record_sql($fromsql . $sql, $params);
        } else {
            try {
                $programscount = $DB->count_records_sql($countsql . $sql, $params);
                if ($stable->thead == false) {
                    $sql .= " ORDER BY bc.id DESC";
                    if ($request == true) {
                        $programs = $DB->get_record_sql($fromsql . $sql, $params, $stable->start, $stable->length);
                    } else {
                        $programs = $DB->get_records_sql($fromsql . $sql, $params, $stable->start, $stable->length);
                    }
                }
            } catch (dml_exception $ex) {
                $programscount = 0;
            }
        }
        if (isset($stable->programid) && $stable->programid > 0) {
            return $programs;
        } else {
            return compact('programs', 'programscount');
        }
    }
    /**
     * [program_add_assignusers description]
     * @method program_add_assignusers
     * @param  [type]                    $programid   [description]
     * @param  [type]                    $userstoassign [description]
     * @return [type]                                   [description]
     */
    public function program_add_assignusers($programid, $userstoassign) {
        global $DB, $USER,$CFG;
        if (file_exists($CFG->dirroot . '/local/lib.php')) {
            require_once($CFG->dirroot . '/local/lib.php');
        }

        $emaillogs = new \local_program\notification();
        $allow = true;
        $type = 'program_enrol';
        $dataobj = $programid;
        $fromuserid = $USER->id;
        if ($allow) {
           $progress       = 0;
           $local_program = $DB->get_record('local_program', array('id' => $programid));
            foreach ($userstoassign as $key => $adduser) {

                    $progress++;

                    $programuser = new stdClass();
                    $programuser->programid = $programid;
                    $programuser->courseid = 0;
                    $programuser->userid = $adduser;
                    $programuser->supervisorid = 0;
                    $programuser->prefeedback = 0;
                    $programuser->postfeedback = 0;
                    $programuser->trainingfeedback = 0;
                    $programuser->confirmation = 0;
                    $programuser->attended_sessions = 0;
                    $programuser->hours = 0;
                    $programuser->completion_status = 0;
                    $programuser->completiondate = 0;
                    $programuser->usercreated = $USER->id;
                    $programuser->timecreated = time();
                    $programuser->usermodified = $USER->id;
                    $programuser->timemodified = time();
                    try {
                    $programuser->id = $DB->insert_record('local_program_users',
                            $programuser);

                    $sqls = "select plc.courseid, p.*, pl.*, pu.userid, pu.id as programuser from 
                            {local_program} p 
                            join {local_program_users} pu  on pu.programid = p.id
                            join {local_program_levels} pl on pl.programid = pu.programid 
                            join {local_program_level_courses} plc ON  plc.levelid = pl.id where pu.id =  {$programuser->id} AND pl.active = 1 AND mandatory = 1 GROUP BY plc.courseid";

                    $sem_enroluser = $DB->get_records_sql($sqls);

                    foreach($sem_enroluser as $sem_enrolusers) {
                        // $sem_enrolusers = $DB->get_record('local_program_users', array('id' => $programuser->id));
                        $touser = \core_user::get_user($sem_enrolusers->userid);
                        // enrol users and online admission users directly if the program semester is active. (local_program_enrolments) .
                            // $this->enrol_user_to_admission_courses($sem_enrolusers->programid, $sem_enrolusers->userid, $sem_enrolusers->courseid, $sem_enrolusers->id);
                            $this->enrol_user_to_program_courses($sem_enrolusers->programid, $sem_enrolusers->userid, $sem_enrolusers->courseid, $sem_enrolusers->id);
                    }
                        $params = array(
                            'context' => context_system::instance(),
                            'objectid' => $programuser->id,
                            'relateduserid' => $programuser->id,
                            'other' => array('programid' => $programid)
                        );

                        $event = \local_program\event\program_users_enrol::create($params);
                        $event->add_record_snapshot('local_program_users', $programuser);
                        $event->trigger();

                        if ($local_program->status == 0) {
                            $touser = \core_user::get_user($programuser->userid);
                            $email_logs = $emaillogs->program_notification($type, $touser, $USER, $local_program);
                        }
                    } catch (dml_exception $ex) {
                        print_error($ex);
                    }

            }

            $sqls = "select plc.courseid, p.*, pl.*, pu.userid, pu.id as programuser from 
                    {local_program} p 
                    join {local_program_users} pu  on pu.programid = p.id
                    join {local_program_levels} pl on pl.programid = pu.programid 
                    join {local_program_level_courses} plc ON  plc.levelid = pl.id where pu.id =  {$programuser->id} AND pl.active = 1 AND mandatory = 1 GROUP BY plc.courseid";

            $sem_enroluser = $DB->get_records_sql($sqls);

            foreach($sem_enroluser as $sem_enrolusers) {
                // $sem_enrolusers = $DB->get_record('local_program_users', array('id' => $programuser->id));
                $touser = \core_user::get_user($sem_enrolusers->userid);
                // enrol users and online admission users directly if the program semester is active. (local_program_enrolments) .
                    // $this->enrol_user_to_admission_courses($sem_enrolusers->programid, $sem_enrolusers->userid, $sem_enrolusers->courseid, $sem_enrolusers->id);
                    $this->enrol_user_to_program_courses($sem_enrolusers->programid, $sem_enrolusers->userid, $sem_enrolusers->courseid, $sem_enrolusers->id);
            }

            $program = new stdClass();
            $program->id = $programid;
            $program->totalusers = $DB->count_records('local_program_users',
                array('programid' => $programid));
            $DB->update_record('local_program', $program);

            $result              = new stdClass();
            $result->changecount = $progress;
            $result->program   = $local_program->name;
        }
        return $result;
    }
    /**
     * [program_remove_assignusers description]
     * @method program_remove_assignusers
     * @param  [type]                       $programid     [description]
     * @param  [type]                       $userstounassign [description]
     * @return [type]                                        [description]
     */
    public function program_remove_assignusers($programid, $userstounassign) {
        global $DB, $USER,$CFG;
        if (file_exists($CFG->dirroot . '/local/lib.php')) {
            require_once($CFG->dirroot . '/local/lib.php');
        }

        $emaillogs = new \local_program\notification();
        $programenrol = enrol_get_plugin('program');
        $courses = $DB->get_records_menu('local_program_level_courses',
            array('programid' => $programid), 'id', 'id, courseid');
        $type = 'program_unenroll';
        $dataobj = $programid;
        $fromuserid = $USER->id;
        try {
          $local_program = $DB->get_record('local_program', array('id' => $programid));
            $progress= 0;
            foreach ($userstounassign as $key=>$removeuser) {
                    $progress++;
                    if ($local_program->status != 0) {
                        if (!empty($courses)) {
                            foreach ($courses as $course) {
                                if ($course > 0) {

                                    $unenrolprogramuser = $this->manage_program_course_enrolments(
                                        $course, $removeuser, 'employee', 'unenrol');
                                }
                            }
                        }
                    }
                $params = array(
                   'context' => context_system::instance(),
                   'objectid' => $programid,
                   'relateduserid' => $removeuser,
                );

                $event = \local_program\event\program_users_unenrol::create($params);
                $event->add_record_snapshot('local_program_users', $programid);
                $event->trigger();
                $DB->delete_records('local_program_users',  array('programid' => $programid,
                    'userid' => $removeuser));
                if ($local_program->status == 0) {
                    $touser = \core_user::get_user($removeuser);
                    $email_logs = $emaillogs->program_notification($type , $touser, $USER, $local_program);
                }
            }
            $program = new stdClass();
            $program->id = $programid;
            $program->totalusers = $DB->count_records('local_program_users',
                array('programid' => $programid));
            $DB->update_record('local_program', $program);

            $result              = new stdClass();
            $result->changecount = $progress;
            $result->program   = $local_program->name;
        } catch (dml_exception $ex) {
            print_error($ex);
        }
        return $result;
    }
    /**
     * [program_logo description]
     * @method program_logo
     * @param  integer        $programlogo [description]
     * @return [type]                        [description]
     */
    public function program_logo($programlogo = 0) {
        global $DB;
        $programlogourl = false;
        if ($programlogo > 0){
            $sql = "SELECT * FROM {files} WHERE itemid = $programlogo AND filename != '.'
            ORDER BY id DESC ";//LIMIT 1
            $programlogorecord = $DB->get_record_sql($sql);
        }
        if (!empty($programlogorecord)) {
          if ($programlogorecord->filearea == "programlogo") {
            $programlogourl = moodle_url::make_pluginfile_url($programlogorecord->contextid,
                $programlogorecord->component, $programlogorecord->filearea,
                $programlogorecord->itemid, $programlogorecord->filepath,
                $programlogorecord->filename);
          }
        }
        return $programlogourl;
    }
    /**
     * [manage_program_courses description]
     * @method manage_program_courses
     * @param  [type]                   $courses [description]
     * @return [type]                            [description]
     */
    public function manage_program_courses($courses) {
        global $DB, $USER, $CFG;
        require_once($CFG->dirroot.'/course/lib.php');
        require_once($CFG->dirroot.'/course/externallib.php');
        require_once($CFG->dirroot . '/course/modlib.php');
        $courselist = array_filter($courses->course);

        $emaillogs = new \local_program\notification();
        $allow = true;
        $type = 'course_enrol';
        /**
         * ODL-866: Ikram: Code starts here.
         * Adding some comments to understand it better. 
         */
        /**
         * Creating duplicate courses of the selected parent courses
         * and enroll batch users in the new course created for the respective programs.
         * @param StdClass object $courselist 
         */
        $course_created = [];
        foreach ($courselist as $course) {
            // Preventing execution of the loop if $course is having value 0 or null.
            if (!$course) {
                continue;
            }
            $restorecourse = $DB->get_record('course',array('id'=>$course));
            $mastercourseid = $restorecourse->id;
            $category_id = $restorecourse->category;
            $open_departmentid = (int)$restorecourse->open_departmentid;
            $open_subdepartment = (int)$restorecourse->open_subdepartment;
            $restorecourse->shortname = $restorecourse->shortname.'_'.$courses->programid.'_'.$courses->levelid;
            $restorecourse->id = 0;
            $restorecourse->idnumber = '';
            $restorecourse->open_identifiedas = 5;
            $restorecourse->category = $category_id;
            $newcourse = create_course($restorecourse);
            $course_created[] = $newcourse->id;
            if ($restorecourse) {
                \core_course_external::import_course($mastercourseid, $newcourse->id, 0);
            }
            // Attendance Module addition to the courses.
            $instance_type = 'attendance';
            $moduleinfo = new \stdClass();
            $moduleinfo->timemodified = time();
            $moduleinfo->module=$DB->get_field('modules','id',array('name'=>$instance_type));
            $moduleinfo->modulename="$instance_type";
            $moduleinfo->section=1;
            $moduleinfo->visible=1;
            $moduleinfo = add_moduleinfo($moduleinfo, $newcourse, null);
            $programcourseexists = $DB->record_exists('local_program_level_courses',
                array('programid' => $courses->programid, 'levelid' => $courses->levelid, 'courseid' => $newcourse->id));
            if (!empty($programcourseexists)) {
                continue;
            }
            $programcourse = new stdClass();
            $programcourse->parentid = $mastercourseid;
            $programcourse->programid = $courses->programid;
            $programcourse->levelid = $courses->levelid;
            $programcourse->courseid = $newcourse->id;
            $programcourse->timecreated = time();
            $programcourse->usercreated = $USER->id;
            $programcourse->id = $DB->insert_record('local_program_level_courses',
                $programcourse);
            $params = array(
                'context' => context_system::instance(),
                'objectid' => $programcourse->id,
                'other' => array('programid' => $courses->programid,
                                 'levelid' => $courses->levelid)
            );

            $event = \local_program\event\bclevelcourse_created::create($params);
            $event->add_record_snapshot('local_program_level_courses', $programcourse);
            $event->trigger();
        }
        // Set mandatory courses.
        if (!empty($courses->mandatory_courseids)) {
            $m_courseids = $DB->execute("UPDATE {local_program_level_courses} SET mandatory = 1 WHERE parentid IN($courses->mandatory_courseids) AND programid = $courses->programid AND levelid = $courses->levelid");
        }
        /**
         * Add (program) enrolment method for the elective courses as users will need to 
         * enroll themselves in those programs.
         */
        $elective_courses = $DB->get_records_sql("SELECT courseid FROM {local_program_level_courses} WHERE programid = $courses->programid AND levelid = $courses->levelid AND mandatory <> 1 ");
        if ($elective_courses) {
            foreach ($elective_courses as $elec_crs) {
                try {
                    $method_exists = $DB->record_exists('enrol', ['enrol' => 'program', 'courseid' => $elec_crs->courseid]);
                    if ($method_exists) {
                        continue;
                    }else{
                        $enrol = $DB->insert_record('enrol', ['enrol' => 'program', 'status' => 0, 'courseid' => $elec_crs->courseid, 'sortorder' => 3, 'roleid' => 5, 'timecreated' => time(), 'timemodified' => time()]);
                        $user_enrol = $DB->get_field('user_enrolments', 'userid', array('id'=>$enrol));

                        if ($allow) {
                           $enrol_course = $DB->get_field('enrol', 'courseid', array('id'=>$enrol));
                           $course_data = $DB->get_record('course', array('id'=>$enrol_course));
                           $touser = \core_user::get_user($dataid->id);
                           $email_logs = $emaillogs->program_notification($type, $touser, $USER, $course_data);
                        }
                    }
                } catch (dml_exception $e) {
                    print_error($e);
                }
            }
        }
        // Check if the level is active for this program.
        $is_level_active = $DB->get_field('local_program_levels', 'active', ['id' => $courses->levelid]);
        if ($is_level_active) {
            for ($i=0; $i < count($course_created) ; $i++) {
                $is_mandatory = $DB->get_field('local_program_level_courses', 'mandatory', ['courseid' => $course_created[$i]]);
                if ($is_mandatory) {
                    $this->enrol_batch_to_program_courses($courses->programid, $courses->levelid, $is_mandatory = 1, $course_created[$i]);
                }
            }
        }
         /** ODL-866: Ikram: Code Ends here. **/
        $totalcourses = $DB->count_records('local_program_level_courses',
            array('programid' => $courses->programid, 'levelid' => $courses->levelid));
        $leveldata = new stdClass();
        $leveldata->id = $courses->levelid;
        $leveldata->programid = $courses->programid;
        $leveldata->totalcourses = $totalcourses;
        $leveldata->timemodified = time();
        $leveldata->usermodified = $USER->id;
        $DB->update_record('local_program_levels', $leveldata);
        $totalbccourses = $DB->count_records('local_program_level_courses',
            array('programid' => $courses->programid));
        $programdata = new stdClass();
        $programdata->id = $courses->programid;
        $programdata->totalcourses = $totalbccourses;
        $programdata->timemodified = time();
        $programdata->usermodified = $USER->id;
        $DB->update_record('local_program', $programdata);
        return true;
    }
    /**
     * [manage_program_course_enrolments description]
     * @method manage_program_course_enrolments
     * @param  [type]                             $cousre        [description]
     * @param  [type]                             $user          [description]
     * @param  string                             $roleshortname [description]
     * @param  string                             $type          [description]
     * @param  string                             $pluginname    [description]
     * @return [type]                                            [description]
     */
    public function manage_program_course_enrolments($course, $user, $roleshortname = 'student',
        $type = 'enrol', $pluginname = 'manual') {
        global $DB;
        $instance=$DB->get_record('enrol', array('courseid' => $course, 'enrol' => $pluginname));
        if($instance){
            $enrolmethod = enrol_get_plugin($pluginname);
            $roleid = $DB->get_field('role', 'id', array('shortname' => $roleshortname));
            if ($type == 'enrol') {
                $enrolmethod->enrol_user($instance, $user, $roleid, time());
            } else if ($type == 'unenrol'){
                $enrolmethod->unenrol_user($instance, $user, $roleid, time());
            }
        }else{
            throw new Exception("Enrolment method (program) is not available in this course. So enrolment cannot happen. Sorry..!", 1); 
        }
        return true;
    }
    public function program_levels($programid) {
        global $DB, $USER;
        $programlevelssql = "SELECT bcl.id, bcl.level, bcl.position
                                FROM {local_program_levels} bcl
                                JOIN {local_program} bc ON bc.id = bcl.programid
                                WHERE bc.id = :programid";
        $programlevels = $DB->get_records_sql($programlevelssql, array('programid' => $programid));
        return $programlevels;
    }
    public function programlevels($programid) {
        global $OUTPUT, $CFG, $DB, $USER;
        $levels = $DB->get_records('local_program_levels', array('programid' => $programid));
        return $levels;
    }
    public function levelcourses($programid, $levelid) {
        global $CFG, $DB, $USER;
        $levelcoursesssql = "SELECT bclc.id AS bclevelcourseid, bclc.programid,
                                    bclc.levelid, c.*
                                      FROM {local_program_level_courses} bclc
                                      JOIN {course} c ON c.id = bclc.courseid
                                     WHERE bclc.programid = :programid ";

        if ($levelid) {
          $levelcoursesssql .= " AND bclc.levelid = {$levelid}";
        }
        $programlevelcourses = $DB->get_records_sql($levelcoursesssql,
                array('programid' => $programid));

        return $programlevelcourses;
    }
    /**
     * [program_courses description]
     * @method program_courses
     * @param  [type]            $programid [description]
     * @return [type]                         [description]
     */
    public function program_level_courses($programid, $levelid, $userview = false) {
        global $DB, $USER;
        $context = context_system::instance();
        if ($levelid > 0) {
            $params = array();
            $programcourses = array();
            $programcoursesssql = "SELECT bclc.id AS bclevelcourseid, bclc.programid,bclc.levelid,c.fullname AS course, bclc.mandatory, c.*
                FROM {local_program_level_courses} bclc
                JOIN {course} c ON c.id = bclc.courseid
                WHERE bclc.programid = :programid
                AND bclc.levelid = {$levelid} ";
            if ($userview && !is_siteadmin() && !has_capability('local/program:createprogram', $context)) {
                $programcoursesssql .= " ORDER BY bclevelcourseid";
            }
            $programlevelcourses = $DB->get_records_sql($programcoursesssql,
                array('programid' => $programid));
        }
        return $programlevelcourses;
    }
    /**
     * [programusers description]
     * @method programusers
     * @param  [type]         $programid [description]
     * @param  [type]         $stable      [description]
     * @return [type]                      [description]
     */
    public function programusers($programid, $stable) {
        global $DB, $USER;
        $params = array();
        $programusers = array();
        $concatsql = '';
        if (!empty($stable->search)) {

            $fields = array(0 => $DB->sql_like('u.firstname', ':ufirstname',  false),
                            1 => $DB->sql_like('u.lastname', ':ulastname',  false),
                            2 => $DB->sql_like('u.email', ':uemail',  false),
                            3 => $DB->sql_like('u.idnumber', ':uidnumber',  false)
                            );
            $fields = implode(" OR ", $fields);
            $concatsql .= " AND ($fields) ";
            $params['ufirstname'] = '%' .$stable->search. '%';
            $params['ulastname'] = '%' .$stable->search. '%';
            $params['uemail'] = '%' .$stable->search. '%';
            $params['uidnumber'] = '%' .$stable->search. '%';
        }
        $countsql = "SELECT COUNT(cu.id) ";
        $fromsql = "SELECT u.*, cu.attended_sessions, cu.hours, cu.completion_status, c.totalsessions,
        c.activesessions";
        $sql = " FROM {user} AS u
                 JOIN {local_program_users} AS cu ON cu.userid = u.id
                 JOIN {local_program} AS c ON c.id = cu.programid
                WHERE c.id = {$programid} AND u.confirmed = 1 AND u.suspended = 0 AND u.deleted = 0 AND u.id > 2";
        $sql .= $concatsql;
        try {
            $programuserscount = $DB->count_records_sql($countsql . $sql, $params);
            if ($stable->thead == false) {
                $sql .= " ORDER BY id ASC";
                $programusers = $DB->get_records_sql($fromsql . $sql, $params, $stable->start, $stable->length);
            }
        } catch (dml_exception $ex) {
            $programuserscount = 0;
        }
        return compact('programusers', 'programuserscount');
    }


   	/**
     * [programusers description]
     * @method programusers
     * @param  [type]         $programid [description]
     * @param  [type]         $stable      [description]
     * @return [type]                      [description]
     */
    public function groupusers($cohortid, $stable) {
        global $DB, $USER;
        $params = array();
        $programusers = array();
        $concatsql = '';
        if (!empty($stable->search)) {

            $fields = array(0 => $DB->sql_like('u.firstname', ':ufirstname',  false),
                            1 => $DB->sql_like('u.lastname', ':ulastname',  false),
                            2 => $DB->sql_like('u.email', ':uemail',  false),
                            3 => $DB->sql_like('u.idnumber', ':uidnumber',  false)
                            );
            $fields = implode(" OR ", $fields);
            $concatsql .= " AND ($fields) ";
            $params['ufirstname'] = '%' .$stable->search. '%';
            $params['ulastname'] = '%' .$stable->search. '%';
            $params['uemail'] = '%' .$stable->search. '%';
            $params['uidnumber'] = '%' .$stable->search. '%';
        }
        $countsql = "SELECT COUNT(cu.id) ";
        $fromsql = "SELECT u.*, cu.cohortid, cu.userid ";
      
        $sql = " FROM {user} AS u
                 JOIN {cohort_members} AS cu ON cu.userid = u.id
                WHERE cu.cohortid = {$cohortid} AND u.confirmed = 1 AND u.suspended = 0 AND u.deleted = 0 AND u.id > 2";
        $sql .= $concatsql;
        try {
            $groupuserscount = $DB->count_records_sql($countsql . $sql, $params);
            if ($stable->thead == false) {
                $sql .= " ORDER BY id ASC";
                $groupusers = $DB->get_records_sql($fromsql . $sql, $params, $stable->start, $stable->length);
            }
        } catch (dml_exception $ex) {
            $groupuserscount = 0;
        }
        return compact('groupusers', 'groupuserscount');
    }



    /**
     * [function to get user enrolled programs ]
     * @param  [int] $userid [id of the user]
     * @return [object]         [programs object]
     */
    public function enrol_get_users_programs($userid) {
        global $DB;
        $program_sql = "SELECT lc.id, lc.name, lc.description
                           FROM {local_program} AS lc
                           JOIN {local_program_users} AS lcu ON lcu.programid = lc.id
                          WHERE userid = :userid";
        $programs = $DB->get_records_sql($program_sql, array('userid' => $userid));
        return $programs;
    }
    /**
     * Manage level/Semester's courses
     * @param int $level id
     * 
     */
    public function manage_program_stream_levels($level, $autocreate = false) {
        global $DB, $USER;

        $level->description = $level->level_description['text'];
        $position = $DB->count_records('local_program_levels', array('programid' => $level->programid));

        $curriculumsql = "SELECT lc.id
            FROM {local_curriculum} lc
            JOIN {local_program} lp ON lc.id = lp.curriculumid
            WHERE lp.id = $level->programid";
        $curriculumlist = $DB->get_records_sql($curriculumsql);
        $curriculumlistarray = array();
        foreach($curriculumlist as $curriculum){
            $curriculumlistarray[] = $curriculum->id;
        }
        $allcurriculumid = implode(', ', $curriculumlistarray);
        $curriculumcount = $DB->get_records_sql("SELECT COUNT(id) as id
                                                FROM {local_curriculum_semesters}
                                               WHERE curriculumid IN ($allcurriculumid)");
        foreach ($curriculumcount as $curriculumvalue) {
            $value = $curriculumvalue;
        }
        $level->position = $position;
        $level->active = $level->active;

        try {
            if ($level->id > 0) {
                $level->usermodified = $USER->id;
                $level->timemodified = time();
                if($level->active == 1) {
                    $act = $DB->execute("UPDATE {local_program_levels} SET active = 0 WHERE programid = {$level->programid} AND active <> 0");
                }
                $level->active = $level->active;
                $level->course_elective = isset($level->has_course_elective) ? $level->course_elective : null;
                $DB->update_record('local_program_levels', $level);
                $scheduledid = $DB->get_field('local_schedulesessions', 'id', ['semesterid' => $level->id]);
                if (!empty($scheduledid)) {
                    $scheduledata = new \stdClass();
                    $scheduledata->id = $scheduledid;
                    $scheduledata->startdate = $level->startdate;
                    $scheduledata->enddate = $level->enddate;
                    $scheduledata->usermodified = $USER->id;
                    $scheduledata->timemodified = time();
                    $DB->update_record('local_schedulesessions', $scheduledata);
                }
                /** ODL-866: Ikram: Code starts here. **/
                /**
                 * If semester was marked as active then fetch the program batch and 
                 * enroll them into the courses available in the semester.
                 * 
                 */
                // ($programid, $levelid, $coursetype, int $courseid = null) 
                if ($level->enrolmethod == 1) {
                    $this->enrol_batch_to_program_courses($level->programid, $level->id, 1);
                }
                $params = array(
                    'context' => context_system::instance(),
                    'objectid' => $level->id,
                    'other' =>array('programid' => $level->programid)
                );

                $event = \local_program\event\level_updated::create($params);
                $event->add_record_snapshot('local_program_levels', $level);
                $event->trigger();
            }else {
                if ($autocreate) {
                    $records = array();
                    for ($i = 1; $i <= $value->id; $i++) {
                        ${'record' . $i} = new stdClass();
                        ${'record' . $i}->programid = $level->programid;
                        ${'record' . $i}->level = 'Semester ' . $i;
                        ${'record' . $i}->description = '';
                        ${'record' . $i}->position = $i;
                        ${'record' . $i}->is_mandatory = 1;
                        ${'record' . $i}->usercreated = $USER->id;
                        ${'record' . $i}->timecreated = time();
                        $records[$i] = ${'record' . $i};
                    }
                    $DB->insert_records('local_program_levels', $records);
                    return true;
                } else {
                    $level->usercreated = $USER->id;
                    $level->timecreated = time();
                    if($level->active == 1) {
                        $act = $DB->execute("UPDATE {local_program_levels} SET active = 0 WHERE programid = {$level->programid} AND active <> 0");
                    }
                    $level->active = $level->active;

                    $level->course_elective = isset($level->has_course_elective) ? $level->course_elective : null;
                    $level->id = $DB->insert_record('local_program_levels', $level);

                    if ($level->active == 1) {
                        $batchid = $DB->get_field('local_program', 'batchid', array('id' => $level->programid));
                        $roleid = $DB->get_field('role', 'id', array('shortname' => 'student'));

                        $levelcourses = $DB->get_records('local_program_level_courses', array('levelid' => $level->id, 'programid' => $level->programid));

                        foreach ($levelcourses as $levelcourse) {
                             $course = get_course($levelcourse->courseid);

                             $obj = new \stdClass();
                             $obj->status = 0;
                             $obj->customint1 = $batchid;
                             $obj->roleid = $roleid;
                             $obj->customint2 = 0;
                             $obj->id = 0;
                             $obj->courseid = $levelcourse->courseid;
                             $obj->type = 'cohort';
                             $plugin = enrol_get_plugin('cohort');

                             $plugin->add_instance($course, (array) $obj);
                        }
                    }

                    $params = array(
                        'context' => context_system::instance(),
                        'objectid' => $level->id,
                        'other' => array('programid' => $level->programid)
                    );

                    $event = \local_program\event\level_created::create($params);
                    $event->add_record_snapshot('local_program_levels', $level);
                    $event->trigger();
                }
            }
        } catch (dml_exception $ex) {
            print_error($ex);
        }
        return $level->id;
    }
    /**
     * This function will check for the program batches if exists
     * and enrol the batch students to all the courses available in the program.
     * @param int $coursetype Boolian value for the course type
     * for mandatory courese $coursetype = 1 and for elective courese $coursetype = 0
     * @param int $progrmaid is ID of the program.
     * @param int $courseid ID of the course to enrol the batch.
     * @return boolian
     */
    public function enrol_batch_to_program_courses($programid, $levelid, $coursetype, int $courseid = null)  {
        global $DB, $USER, $CFG;

        $allow = true;
        $emaillogs = new \local_program\notification();
        $semenrol_type = 'program_semester_enrollment';
        $course_enrol = 'program_course_enrollment';

        $c_id = [];
        if ($courseid) {
            $c_id = ['courseid' => $courseid];
        }
        $c_type = [];
        if ($coursetype) {
            $c_type = ['mandatory' => $coursetype];
        }
        $batchid = $DB->get_field('local_program', 'batchid', array('id' => $programid));
        $roleid = $DB->get_field('role', 'id', array('shortname' => 'student'));
        $levelcourses = $DB->get_records('local_program_level_courses', array('levelid' => $levelid, 'programid' => $programid) + $c_type + $c_id);
        if (!$levelcourses) {
            return false;
        }
        $participants = $DB->get_records('local_program_users', ['programid' => $programid]);

        foreach ($levelcourses as $levelcourse) {
            if ($levelcourse->mandatory == 1) {
                $course = get_course($levelcourse->courseid);
                $instancedata = $DB->get_record('enrol', array('courseid' =>$levelcourse->courseid));
                $obj = new \stdClass();
                $obj->status = 0;
                $obj->customint1 = $batchid;
                $obj->roleid = $roleid;
                $obj->customint2 = 0;
                $obj->id = 0;
                $obj->courseid = $levelcourse->courseid;
                $obj->type = 'cohort';
                $plugin = enrol_get_plugin('cohort');
                $exists = $DB->record_exists('enrol', array('courseid' =>$levelcourse->courseid, 'enrol' => 'cohort'));
                // Creating object data to insert the batch enrolments inro local_program_enrolments;
                $params = new stdClass();
                $params->programid = $programid;
                $params->levelid = $levelid;
                $params->courseid = $levelcourse->courseid;

                if ($exists) {
                    if($plugin->update_instance($instancedata, (array) $obj)){
                        $params->mandatory = $levelcourse->mandatory;
                        if ($participants) {
                            
                            foreach ($participants as $participant) {
                                $params->userid = $participant->userid;

                                if (!$programenrolid = $DB->get_field('local_program_enrolments', 'id', ['programid' => $programid, 'levelid' => $levelid, 'userid' => $participant->userid, 'courseid' => $levelcourse->courseid])) {
                                    $params->timecreated = time();
                                    $data->id = $DB->insert_record('local_program_enrolments', $params);

                                    $get_userenrols = $DB->get_record('local_program_enrolments', array('id'=>$data->id));

                                    $sqli = "SELECT p.*, pe.userid, pl.level FROM {local_program} p 
                                            JOIN {local_program_enrolments} pe ON pe.programid = p.id 
                                            JOIN {local_program_levels} pl ON pl.id = pe.levelid 
                                            WHERE pe.id = {$data->id} and pe.userid = {$get_userenrols->userid} 
                                    ";

                                    $enrol_users = $DB->get_record_sql($sqli); 
                                    if ($allow) {
                                        $touser = \core_user::get_user($enrol_users->userid);
                                        $email_logs = $emaillogs->program_notification($semenrol_type, $touser, $USER, $enrol_users);
                                    }

                                    $sql = "SELECT p.*, c.id as cid, pe.userid, pl.level, c.fullname,  pe.timecreated as course_enrolstartdate FROM {local_program} p 
                                            JOIN {local_program_enrolments} pe ON pe.programid = p.id 
                                            JOIN {local_program_levels} pl ON pl.id = pe.levelid 
                                            JOIN {local_program_level_courses} plc ON plc.levelid = pl.id 
                                            JOIN {course} c ON c.id = plc.courseid 
                                            JOIN {enrol} e ON e.courseid = c.id 
                                            JOIN {user_enrolments} ue ON ue.enrolid = e.id 
                                            JOIN {role_assignments} ra ON ra.userid = ue.userid 
                                            JOIN {role} r ON r.id = ra.roleid 
                                            JOIN {user} u ON u.id = ue.userid 
                                            WHERE pe.id = {$data->id} and c.id = {$get_userenrols->courseid} and pe.userid = {$get_userenrols->userid} GROUP BY pe.userid, pe.courseid";

                                    $course_enrols = $DB->get_record_sql($sql);
                                    if($course_enrols) {
                                        $touser = \core_user::get_user($course_enrols->userid);
                                        $email_logs = $emaillogs->program_notification($course_enrol, $touser, $USER, $course_enrols);
                                    }
                                }else{
                                    $params->id = $programenrolid;
                                    $params->timemodified = time();
                                    $DB->update_record('local_program_enrolments', $params);
                                }
                            }
                        }
                    }
                }else{

                    $plugin->add_instance($course, (array) $obj);
                    $params->mandatory = $levelcourse->mandatory;
                    foreach ($participants as $participant) {
                        $params->userid = $participant->userid;
                        $params->timecreated = time();
                        $data->id = $DB->insert_record('local_program_enrolments', $params);

                        // $enrol_users = $DB->get_record('local_program_enrolments', array('id'=>$data->id));
                        $sqli = "SELECT p.*, pe.userid, pl.level FROM {local_program} p 
                                 JOIN {local_program_enrolments} pe ON pe.programid = p.id 
                                 JOIN {local_program_levels} pl ON pl.id = pe.levelid 
                                 WHERE pe.id = {$data->id};
                                 ";
                        $enrol_users = $DB->get_record_sql($sqli);
                        
                        if ($allow) {
                            $touser = \core_user::get_user($enrol_users->userid);
                            $email_logs = $emaillogs->program_notification($semenrol_type, $touser, $USER, $enrol_users);
                            
                        }
                    }
                }
            }
        }
        return true;
    }
    /**
     * [unassign_courses_to_bclevel description]
     * @method unassign_courses_to_bclevel
     * @param  [type]                      $programid [description]
     * @param  [type]                      $levelid    [description]
     * @param  [type]                      $bclcid     [description]
     * @return [type]                                  [description]
     */
    public function unassign_courses_to_bclevel($programid, $levelid, $bclcid) {
        global $DB;
        $cou=$DB->get_field('local_program_level_courses','courseid',array('id' => $bclcid));
        $DB->delete_records('local_program_level_courses', array('id' => $bclcid,
            'levelid' => $levelid));
        $DB->delete_records('course', array('id' => $cou));

        $participants = $DB->get_records('local_program_users', ['programid' => $programid]);
        foreach ($participants as $participant) {
            $DB->delete_records('local_program_enrolments', array('courseid' => $cou, 'userid' => $participant->userid, 'programid' => $programid, 'levelid' => $levelid));
        }
        $totalcourses = $DB->count_records('local_program_level_courses',
            array('programid' => $programid, 'levelid' => $levelid));
        $leveldata = new stdClass();
        $leveldata->id = $levelid;
        $leveldata->programid = $programid;
        $leveldata->totalcourses = $totalcourses;
        $leveldata->timemodified = time();
        $leveldata->usermodified = $USER->id;
        $DB->update_record('local_program_levels', $leveldata);
        $totalbccourses = $DB->count_records('local_program_level_courses',
            array('programid' => $programid));
        $programdata = new stdClass();
        $programdata->id = $programid;
        $programdata->totalcourses = $totalbccourses;
        $programdata->timemodified = time();
        $programdata->usermodified = $USER->id;
        $DB->update_record('local_program', $programdata);

        return true;
    }
    /**
     * [manage_bclevel_course_enrolments description]
     * @method manage_bclevel_course_enrolments
     * @param  [type]                           $course     [description]
     * @param  [type]                           $user       [description]
     * @param  string                           $role       [description]
     * @param  string                           $type       [description]
     * @param  string                           $pluginname [description]
     * @return [type]                                       [description]
     */
    public function manage_bclevel_course_enrolments($course, $user, $role = 'employee',
        $type = 'enrol', $pluginname = 'program') {
        global $DB;
        $courseexist=$DB->record_exists('enrol', array('courseid' => $course, 'enrol' => $pluginname));
        if($courseexist){
          $enrolmethod = enrol_get_plugin($pluginname);
          $roleid = $DB->get_field('role', 'id', array('shortname' => $role));
          $instance = $DB->get_record('enrol', array('courseid' => $course, 'enrol' => $pluginname), '*', MUST_EXIST);
          if (!empty($instance)) {
              if ($type == 'enrol') {
                  $enrolmethod->enrol_user($instance, $user, $roleid, time());
              } else if ($type == 'unenrol') {
                  $enrolmethod->unenrol_user($instance, $user, $roleid, time());
              }
          }
        }
        return true;
    }
    /**
     * [mycompletedlevels description]
     * @method mycompletedlevels
     * @param  [type]            $programid [description]
     * @param  [type]            $userid     [description]
     * @return [type]                        [description]
     */
    public function mycompletedlevels($programid, $userid) {
        global $DB;
        $mycompletedlevels = array();
        $mycompletedlevelssql = "SELECT levelids
                                   FROM {local_program_users}
                                  WHERE programid = :programid AND userid = :userid ";
        $mycompletedlevelslist = $DB->get_field_sql($mycompletedlevelssql,
            array('programid' => $programid, 'userid' => $userid));
        if (!empty($mycompletedlevelslist)) {
            $mycompletedlevels = explode(',', $mycompletedlevelslist);
        }
        return $mycompletedlevels;
    }

    public function myprogramlist($programid, $userid) {
        global $DB;
        $myprogramlist = array();
        $time=time();
        $myprogramlistsql = "SELECT id FROM {local_program_levels}
                        WHERE programid = $programid  AND ((startdate < $time AND startdate != 0) OR  active=1)" ; 
        $myprogramlists = $DB->get_fieldset_sql($myprogramlistsql);
        return $myprogramlists;
    }
    /**
     * [mycompletedveles description]
     * @method mycompletedveles
     * @param  [type]           $programid [description]
     * @return [type]                       [description]
     */
    public function mylevelsandcompletedlevels($programid) {
        global $DB, $USER;
        $levels = $DB->get_fieldset_select('local_program_levels', 'id', 'programid = :programid ORDER BY id ASC',
            array('programid' => $programid));
        $mylevelcomptllist = $DB->get_field('local_program_users', 'levelids',
            array('programid' => $programid, 'userid' => $USER->id));
        $mylevelcomptl = explode(',', $mylevelcomptllist);
        return array($levels, $mylevelcomptl);

    }
    /**
     * [mynextlevels description]
     * @method mynextlevels
     * @param  [type]       $programid [description]
     * @return [type]                   [description]
     */
    public function mynextlevels($programid) {
        global $DB, $USER;
        list($levels, $mylevelcomptl) = $this->mylevelsandcompletedlevels($programid, $USER->id);
        $notcmptllevels = array_values(array_diff($levels, $mylevelcomptl));
        return $notcmptllevels;
    }
    public function levels_completion_status($programid){
      global $DB;
      $levels_completion_sql = "SELECT id FROM {local_program_users} WHERE programid = :programid AND (levelids != '' AND levelids IS NOT NULL) ";
      $status = $DB->get_field_sql($levels_completion_sql,  array('programid' => $programid));
      return !$status;
    }
    /** ODL 866: Ikram Starts Here.. **/
    /**
     * [enrol_user_to_program_courses description]
     * @method enrol_user_to_program_courses: This method will enrol user to course.
     * @param int $programid id of the program in which the course is available
     * @param int $userid userid to be enrolled to course
     * @param int $courseid courseid in which the user requested to get enrolled.
     * @return Boolian
     */
    public function enrol_user_to_program_courses($programid, $userid, $courseid, $levelid) {
        global $DB, $USER,$CFG;
        
        if (file_exists($CFG->dirroot . '/local/lib.php')) {
            require_once($CFG->dirroot . '/local/lib.php');
        }

        $allow = true;
        $emaillogs = new \local_program\notification();
        $type = 'program_enrol';
        $semenrol_type = 'program_semester_enrollment';
        $course_enrol = 'program_course_enrollment';

        // $emaillogs = new \local_program\notification();
        $programenrol = enrol_get_plugin('program');
        try {
            $enrol_user = $this->manage_program_course_enrolments($courseid, $userid, 'student', 'enrol');
            $mandatory = $DB->get_field('local_program_level_courses', 'mandatory', ['levelid' => $levelid, 'courseid' => $courseid, 'programid' => $programid]);
            $data->id = $DB->insert_record('local_program_enrolments', ['programid' => $programid, 'userid' => $userid, 'levelid' => $levelid, 'courseid' => $courseid, 'mandatory' => $mandatory, 'timecreated' => time(), 'timemodified' => null]);

            $params = array(
               'context' => context_system::instance(),
               'objectid' => $courseid,
               'relateduserid' => $userid,
               'other' => ['programid' => $programid, 'levelid' => $levelid]
            );
            $event = \local_program\event\program_users_enrol::create($params);
            $event->add_record_snapshot('local_program_users', $programid);
            $event->trigger();
            if ($local_program->status == 0) {
                $touser = \core_user::get_user($userid);
                $email_logs = $emaillogs->program_notification($type , $touser, $USER, $local_program);
            }
            $sqli = "SELECT p.*, pe.userid, pl.level FROM {local_program} p 
                JOIN {local_program_enrolments} pe ON pe.programid = p.id 
                JOIN {local_program_levels} pl ON pl.id = pe.levelid 
                WHERE pe.id = {$data->id}
                ";
            $enrol_users = $DB->get_record_sql($sqli);

            if ($allow) {
                $touser = \core_user::get_user($enrol_users->userid);
                $email_logs = $emaillogs->program_notification($semenrol_type, $touser, $USER, $enrol_users);
                
            }

            $sql = "SELECT p.*, pe.userid, pl.level, c.fullname, c.id as cid, pe.timecreated as course_enrolstartdate FROM {local_program} p 
            JOIN {local_program_enrolments} pe ON pe.programid = p.id 
            JOIN {local_program_levels} pl ON pl.id = pe.levelid 
            JOIN {local_program_level_courses} plc ON plc.levelid = pl.id 
            JOIN {course} c ON c.id = plc.courseid 
            JOIN {enrol} e ON e.courseid = c.id 
            JOIN {user_enrolments} ue ON ue.enrolid = e.id 
            JOIN {role_assignments} ra ON ra.userid = ue.userid 
            JOIN {role} r ON r.id = ra.roleid 
            JOIN {user} u ON u.id = ue.userid 
            WHERE pe.id = {$data->id} GROUP BY pe.userid, pe.courseid";

            $course_enrols = $DB->get_record_sql($sql);
            if($course_enrols) {
                $touser = \core_user::get_user($course_enrols->userid);
                $email_logs = $emaillogs->program_notification($course_enrol, $touser, $USER, $course_enrols);
            }

            
        } catch (dml_exception $e) {
            print_error($e);
        }
        return true;
    }
    /** ODL 866: Ikram ENDS Here.. **/

      public function manage_admissions_course_enrolments($course, $user, $roleshortname = 'student',
        $type = 'enrol', $pluginname = 'self') {
        global $DB;
        $instance = $DB->get_record('enrol', array('courseid' => $course, 'enrol' => $pluginname));
        if($instance){
            $enrolmethod = enrol_get_plugin($pluginname);
            $roleid = $DB->get_field('role', 'id', array('shortname' => $roleshortname));
            if ($type == 'enrol') {
                $enrolmethod->enrol_user($instance, $user, $roleid, time());
            } else if ($type == 'unenrol'){
                $enrolmethod->unenrol_user($instance, $user, $roleid, time());
            }
        }else{
            throw new Exception("Enrolment method (program) is not available in this course. So enrolment cannot happen. Sorry..!", 1); 
        }
        return true;
    }

     public function enrol_user_to_admission_courses($programid, $userid, $courseid, $levelid) {
        global $DB, $USER,$CFG;
        if (file_exists($CFG->dirroot . '/local/lib.php')) {
            require_once($CFG->dirroot . '/local/lib.php');
        }

        $allow = true;
        $emaillogs = new \local_program\notification();
        $semenrol_type = 'program_semester_enrollment';

        $type = 'program_enrol';
        // $emaillogs = new \local_program\notification();
        $programenrol = enrol_get_plugin('program');
        try {
            $enrol_user = $this->manage_admissions_course_enrolments($courseid, $userid, 'student', 'enrol');
            $mandatory = $DB->get_field('local_program_level_courses', 'mandatory', ['levelid' => $levelid, 'courseid' => $courseid, 'programid' => $programid]);
            $data->id = $DB->insert_record('local_program_enrolments', ['programid' => $programid, 'userid' => $userid, 'levelid' => $levelid, 'courseid' => $courseid, 'mandatory' => $mandatory, 'timecreated' => time(), 'timemodified' => null]);

            $sql = "SELECT p.*, pe.userid, pl.level, c.fullname,c.id as cid, pe.timecreated as course_enrolstartdate FROM {local_program} p 
                    JOIN {local_program_enrolments} pe ON pe.programid = p.id 
                    JOIN {local_program_levels} pl ON pl.id = pe.levelid 
                    JOIN {local_program_level_courses} plc ON plc.levelid = pl.id 
                    JOIN {course} c ON c.id = plc.courseid 
                    JOIN {enrol} e ON e.courseid = c.id 
                    JOIN {user_enrolments} ue ON ue.enrolid = e.id 
                    JOIN {role_assignments} ra ON ra.userid = ue.userid 
                    JOIN {role} r ON r.id = ra.roleid 
                    JOIN {user} u ON u.id = ue.userid 
                    WHERE pe.id = {$data->id} GROUP BY pe.userid, pe.courseid";

            $course_enrols = $DB->get_record_sql($sql);

            if($course_enrols) {
                $touser = \core_user::get_user($course_enrols->userid);
                $email_logs = $emaillogs->program_notification($course_enrol, $touser, $USER, $course_enrols);
            }
            $sqli = "SELECT p.*, pe.userid, pl.level FROM {local_program} p 
                JOIN {local_program_enrolments} pe ON pe.programid = p.id 
                JOIN {local_program_levels} pl ON pl.id = pe.levelid 
                WHERE pe.id = {$data->id}
                ";
            $enrol_users = $DB->get_record_sql($sqli);

            if ($allow) {
                $touser = \core_user::get_user($enrol_users->userid);
                $email_logs = $emaillogs->program_notification($semenrol_type, $touser, $USER, $enrol_users);
                
            }
            $params = array(
               'context' => context_system::instance(),
               'objectid' => $courseid,
               'relateduserid' => $userid,
               'other' => ['programid' => $programid, 'levelid' => $levelid]
            );
            $event = \local_program\event\program_users_enrol::create($params);
            $event->add_record_snapshot('local_program_users', $programid);
            $event->trigger();
            if ($local_program->status == 0) {
                $touser = \core_user::get_user($userid);
                $email_logs = $emaillogs->program_notification($type , $touser, $USER, $local_program);
            }
        } catch (dml_exception $e) {
            print_error($e);
        }
        return true;
    }
        /**
     * [program_add_assignusers description]
     * @method program_add_assignusers
     * @param  [type]                    $programid   [description]
     * @param  [type]                    $userstoassign [description]
     * @return [type]                                   [description]
     */
    public function program_addassign_assignusers($programid, $userstoassign, $levelid) {
        global $DB, $USER,$CFG;
        if (file_exists($CFG->dirroot . '/local/lib.php')) {
            require_once($CFG->dirroot . '/local/lib.php');
        }
        $emaillogs = new \local_program\notification();
        $allow = true;
        $type = 'program_enrol';
        $dataobj = $programid;
        $fromuserid = $USER->id;
        if ($allow) {
           $progress       = 0;
           $local_program = $DB->get_record('local_program', array('id' => $programid));
            foreach ($userstoassign as $key => $adduser) {
                    $progress++;
                    $programuser = new stdClass();
                    $programuser->programid = $programid;
                    $programuser->courseid = 0;
                    $programuser->userid = $adduser;
                    $programuser->supervisorid = 0;
                    $programuser->prefeedback = 0;
                    $programuser->postfeedback = 0;
                    $programuser->trainingfeedback = 0;
                    $programuser->confirmation = 0;
                    $programuser->attended_sessions = 0;
                    $programuser->hours = 0;
                    $programuser->completion_status = 0;
                    $programuser->completiondate = 0;
                    $programuser->usercreated = $USER->id;
                    $programuser->timecreated = time();
                    $programuser->usermodified = $USER->id;
                    $programuser->timemodified = time();
                    $programuser->levelids = $levelid;
                    try {
                        $var = $DB->record_exists('local_program_users',array('levelids' => $levelid, 'userid' =>$adduser));
                        if(!$var){
                            $programuser->id = $DB->insert_record('local_program_users',
                                $programuser);
                            $sqlquery = "SELECT lplc.courseid,lpu.programid,lpu.levelids,lpu.userid FROM {local_program_users} lpu JOIN {local_program_levels} lpl ON lpu.levelids = lpl.id JOIN {local_program_level_courses} lplc ON lpl.id = lplc.levelid WHERE lpu.levelids = $levelid AND lpu.programid = $programid AND lpu.userid = $adduser AND lplc.mandatory = 1";
                            $tabledata = $DB->get_records_sql($sqlquery);
                            foreach ($tabledata as $value) {
                                $this->enrol_semwiseusers_to_program_courses($value->programid, $value->levelids, 1,$value->courseid, $value->userid);
                            }
                            $params = array(
                                'context' => context_system::instance(),
                                'objectid' => $programuser->id,
                                'relateduserid' => $programuser->id,
                                'other' => array('programid' => $programid)
                            );

                            $event = \local_program\event\program_users_enrol::create($params);
                            $event->add_record_snapshot('local_program_users', $programuser);
                            $event->trigger();

                            if ($local_program->status == 0) {
                                $touser = \core_user::get_user($programuser->userid);
                                $email_logs = $emaillogs->program_notification($type, $touser, $USER, $local_program);
                            }
                        }
                    } catch (dml_exception $ex) {
                        print_error($ex);
                    }

            }
            $program = new stdClass();
            $program->id = $programid;
            $program->totalusers = $DB->count_records('local_program_users',
                array('programid' => $programid));
            $DB->update_record('local_program', $program);

            $result              = new stdClass();
            $result->changecount = $progress;
            $result->program   = $local_program->name;
        }
        return $result;
    }
        /**
     * [program_remove_assignusers description]
     * @method program_remove_assignusers
     * @param  [type]                       $programid     [description]
     * @param  [type]                       $userstounassign [description]
     * @return [type]                                        [description]
     */
    public function program_rem_assignusers($programid, $userstounassign, $levelid) {
        global $DB, $USER,$CFG;
        if (file_exists($CFG->dirroot . '/local/lib.php')) {
            require_once($CFG->dirroot . '/local/lib.php');
        }

        $emaillogs = new \local_program\notification();
        $programenrol = enrol_get_plugin('program');
        $courses = $DB->get_records_menu('local_program_level_courses',
            array('programid' => $programid,'levelid' => $levelid,'mandatory' => 1), 'id', 'id, courseid');
        $type = 'program_unenroll';
        $dataobj = $programid;
        $fromuserid = $USER->id;
        try {
          $local_program = $DB->get_record('local_program', array('id' => $programid));
            $progress= 0;
            foreach ($userstounassign as $key=>$removeuser) {
                    $progress++;
                    if ($local_program->status == 0) {
                        if (!empty($courses)) {
                            foreach ($courses as $course) {
                                if ($course > 0) {
                                    $unenrolsemwiseuser = $this->manage_program_course_enrolments(
                                        $course, $removeuser, 'student', 'unenrol');
                                }
                            }
                        }
                    }
                $params = array(
                   'context' => context_system::instance(),
                   'objectid' => $programid,
                   'relateduserid' => $removeuser,
                );

                $event = \local_program\event\program_users_unenrol::create($params);
                $event->add_record_snapshot('local_program_users', $programid);
                $event->trigger();
                $DB->delete_records('local_program_users',  array('programid' => $programid,
                    'userid' => $removeuser, 'levelids' => $levelid));
                if ($local_program->status == 0) {
                    $touser = \core_user::get_user($removeuser);
                    $email_logs = $emaillogs->program_notification($type , $touser, $USER, $local_program);
                }
            }
            $program = new stdClass();
            $program->id = $programid;
            $program->totalusers = $DB->count_records('local_program_users',
                array('programid' => $programid));
            $DB->update_record('local_program', $program);

            $result              = new stdClass();
            $result->changecount = $progress;
            $result->program   = $local_program->name;
        } catch (dml_exception $ex) {
            print_error($ex);
        }
        return $result;
    }



    /**
     * This function will check for the program batches if exists
     * and enrol the batch students to all the courses available in the program.
     * @param int $coursetype Boolian value for the course type
     * for mandatory courese $coursetype = 1 and for elective courese $coursetype = 0
     * @param int $progrmaid is ID of the program.
     * @param int $courseid ID of the course to enrol the batch.
     * @return boolian
     */
    public function enrol_semwiseusers_to_program_courses($programid, $levelid, $coursetype, int $courseid = null, $userid)  {
        global $DB;
        $c_id = [];
        if ($courseid) {
            $c_id = ['courseid' => $courseid];
        }
        $c_type = [];
        if ($coursetype) {
            $c_type = ['mandatory' => $coursetype];
        }
        $batchid = $DB->get_field('local_program', 'batchid', array('id' => $programid));
        $roleid = $DB->get_field('role', 'id', array('shortname' => 'student'));
        $levelcourses = $DB->get_records('local_program_level_courses', array('levelid' => $levelid, 'programid' => $programid) + $c_type + $c_id);
        if (!$levelcourses) {
            return false;
        }
            $participants = $DB->get_records('local_program_users', ['programid' => $programid, 'levelids' => $levelid, 'userid' =>$userid]);
        foreach ($levelcourses as $levelcourse) {
            if ($levelcourse->mandatory == 1) {
                $course = get_course($levelcourse->courseid);

                $course->courseid = $course->id;
                $instancedata = $DB->get_record('enrol', array('courseid' =>$levelcourse->courseid));
                $instancedata->userid = $userid;
                $instancedata->levelid = $levelid;
                $obj = new \stdClass();
                $obj->status = 0;
                $obj->customint1 = $batchid;
                $obj->roleid = $roleid;
                $obj->customint2 = 0;
                $obj->id = 0;
                $obj->courseid = $levelcourse->courseid;
                $obj->type = 'cohort';
                $plugin = enrol_get_plugin('cohort');
                $exists = $DB->record_exists('enrol', array('courseid' =>$levelcourse->courseid, 'enrol' => 'cohort'));
                // Creating object data to insert the batch enrolments inro local_program_enrolments;
                $params = new stdClass();
                $params->programid = $programid;
                $params->levelid = $levelid;
                $params->courseid = $levelcourse->courseid;
                $course->userid = $userid;
                $course->levelid = $levelid;
                if ($exists) {
                    if($plugin->update_instance($instancedata, (array) $obj)){
                        $params->mandatory = $levelcourse->mandatory;
                        if ($participants) {
                            foreach ($participants as $participant) {
                                $params->userid = $participant->userid;
                                if (!$programenrolid = $DB->get_field('local_program_enrolments', 'id', ['programid' => $programid, 'levelid' => $levelid, 'userid' => $participant->userid, 'courseid' => $levelcourse->courseid])) {
                                    $params->timecreated = time();
                                    $DB->insert_record('local_program_enrolments', $params);
                                }else{
                                    $params->id = $programenrolid;
                                    $params->timemodified = time();
                                    $DB->update_record('local_program_enrolments', $params);
                                }
                            }
                        }
                    }
                }else{
                    $plugin->add_instance($course, (array) $obj);
                    $params->mandatory = $levelcourse->mandatory;
                    foreach ($participants as $participant) {
                        $params->userid = $participant->userid;
                        $params->timecreated = time();
                        $DB->insert_record('local_program_enrolments', $params);
                    }
                }
            }
        }
        return true;
    }
}
