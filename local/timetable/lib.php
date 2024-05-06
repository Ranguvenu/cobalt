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
 * List the tool provided in a course
 *
 * @package    manage_departments
 * @subpackage  list of all functions which is used in departments plugin
 * @copyright  2012 Hemalatha arun <Hemaltha@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use \local_timetable\form\session_form as session_form;
use \local_timetable\form\update_session as update_session;
use \mod_attendance\output\password_icon as password_icon;
use \mod_attendance\output\filter_controls;

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/local/lib.php');
require_once($CFG->dirroot . '/local/timetable/lib.php');
require_once($CFG->dirroot . '/message/lib.php');
$hier = new hierarchy();

//  class manage_dept contsins list of functions....which is used by department plugin

class manage_timetable {

    private static $_singleton;

    //----constructor not called by outside of the class...only possible with inside the class
    private function __construct() {

    }

    //----used to crate a object---when the first time of usage of this function ..its create object
    //--by the next time its link to the same object(single tone object)instead of creating new object...
    public static function getInstance() {
        if (!self::$_singleton) {
            self::$_singleton = new manage_timetable();
        }
        return self::$_singleton;
    }

    /**
     * @method dept_tabs
     * @todo it provides the tab view(particularly for this plugin)
     * @param string $currentab by default it hold the first tab name
     * @param string $dynamictab by default its null ,if passes the parameter it creates dynamic tab
     * @return--it displays the tab
     */
    function timetable_tabs($currenttab = 'addnew', $dynamictab = null, $edit_label = null) {
        global $OUTPUT;
        $systemcontext = context_system::instance();
        $toprow = array();

        $toprow[] = new tabobject('view_timings', new moodle_url('/local/timetable/index.php'), get_string('setstandardview_timings', 'local_timetable'));
        $toprow[] = new tabobject('set_timings', new moodle_url('/local/timetable/settimings.php'), get_string('setstandard_timings', 'local_timetable'));

        $toprow[] = new tabobject('set_classtype', new moodle_url('/local/timetable/classtype.php'), get_string('setclasstypes', 'local_timetable'));
        //$toprow[] = new tabobject('scheduleclassview', new moodle_url('/local/timetable/scheduleclassview.php'), get_string('scheduleclass_timetable', 'local_timetable'));

        if ($currenttab == 'schedule_class')
            $toprow[] = new tabobject('scheduleclassview', new moodle_url('/local/timetable/scheduleclassview.php'), get_string('scheduleclassview', 'local_timetable'));

        //$toprow[] = new tabobject('calendar_view', new moodle_url('/local/timetable/calendarview.php'), get_string('calendar_view', 'local_timetable'));

        echo $OUTPUT->tabtree($toprow, $currenttab);
    }

    /**
     * @method add_timeintervals
     * @todo used to set the time intervals for school and semesters
     * @param array object $data (form submitted data)
     * @return-- array object
     */
    function add_timeintervals($data) {
        global $DB, $CFG, $USER;
        
        $timeintervals_temp = new stdClass();
        $timeintervals_temp->schoolid = $data->open_costcenterid;
        $timeintervals_temp->open_departmentid = $data->open_departmentid;
        $timeintervals_temp->open_subdepartment = $data->open_subdepartment;
        $timeintervals_temp->programid = $data->program;
        $timeintervals_temp->semesterid = $data->semester;
        $timeintervals_temp->planid = 0;
        $batchid = $DB->get_field('local_program', 'batchid', ['id' => $data->program]);
        $timeintervals_temp->batchid = $batchid;
        $timeintervals_temp->visible = $data->visible;
        $timeintervals_temp->timecreated = time();
        $timeintervals_temp->timemodified = time();
        $timeintervals_temp->usermodified = $USER->id;
        $newid->id = $DB->insert_record('local_timeintervals', $timeintervals_temp);

        $temp = new stdClass();
        $temp->timeintervalid = $newid->id;
        $temp->usermodified = $USER->id;
        $temp->timecreated = time();

        for ($i = 0; $i < $data->section_repeats; $i++) {
            if (!empty($data->starthours[$i]) && !empty($data->endhours[$i])) {
                $starttime = sprintf("%02d", $data->starthours[$i]) . ':' . sprintf("%02d", $data->startminutes[$i]) . $data->start_td[$i];


                $temp->starttime = date('H:i:s', strtotime($starttime));
                $endtime = sprintf("%02d", $data->endhours[$i]) . ':' . sprintf("%02d", $data->endminutes[$i]) . $data->end_td[$i];
                $temp->endtime = date('H:i:s', strtotime($endtime));
                $insertedrecord = $DB->insert_record('local_timeintervals_slots', $temp);
            }
        }

        return $insertedrecord;
    }

    /**
     * @method timetable_converting_timeformat
     * @todo used to convert timeformat to suitable array format(which helpful to edit the timings in form)
     * @param object (it holds the edited value)
     * @return-- object (which suitable to form)
     */
    function timetable_converting_timeformat($tool) {
        global $CFG, $DB, $USER;

        // $timeinterval = $DB->get_record('local_timeintervals', array('id' => $tool->id));
        $slots = $DB->get_records('local_timeintervals_slots', array('timeintervalid' => $tool->id));
        $repeatedelement_count = sizeof($slots);
        foreach ($slots as $record) {
            $starttime = date('H:i', strtotime($record->starttime));
            $starttime_first = explode(' ', $starttime);
            // $start_td[] = $starttime_first[1];
            $starttime_second = explode(':', $starttime_first[0]);
            $starthours[] = $starttime_second[0];
            $startminutes[] = $starttime_second[1];

            $endtime = date('H:i', strtotime($record->endtime));
            $endtime_first = explode(' ', $endtime);
            // $end_td[] = $endtime_first[1];
            $endtime_second = explode(':', $endtime_first[0]);
            $endhours[] = $endtime_second[0];
            $endminutes[] = $endtime_second[1];
            $rid[] = $record->id;
        }

        $tool->starthours = $starthours;
        $tool->startminutes = $startminutes;
        // $tool->start_td = $start_td;
        $tool->endhours = $endhours;
        $tool->endminutes = $endminutes;
        // $tool->end_td = $end_td;
        $tool->rid = $rid;
        $tool->section_repeats = $repeatedelement_count;

        return $tool;
    }

    public function timetable_update_timeintervals($data) {
        global $CFG, $OUTPUT, $USER, $DB;
        $timeinterval_temp = new stdClass();
        $timeinterval_temp->id = $data->id;
        $timeinterval_temp->schoolid = $data->open_costcenterid;
        $timeinterval_temp->open_departmentid = $data->open_departmentid;
        $timeinterval_temp->open_subdepartment = $data->open_subdepartment;
        $timeinterval_temp->programid = $data->program;
        $timeinterval_temp->semesterid = $data->semester;
        $batchid = $DB->get_field('local_program', 'batchid', ['id' => $data->program]);
        $timeinterval_temp->batchid = $batchid;
        $timeinterval_temp->visible = $data->visible;
        $timeinterval_temp->timecreated = time();
        $timeinterval_temp->timemodified = time();
        $timeinterval_temp->usermodified = $USER->id;
        $updatedid = $DB->update_record('local_timeintervals', $timeinterval_temp);

        $temp = new stdClass();
        $temp->timeintervalid = $data->id;
        $temp->usermodified = $USER->id;
        $temp->timemodified = time();
        for ($i = 0; $i < $data->section_repeats; $i++) {
            if (($data->starthours[$i] > 0 ) && $data->endhours[$i] > 0) {
                $starttime = sprintf("%02d", $data->starthours[$i]) . ':' . sprintf("%02d", $data->startminutes[$i]) . $data->start_td[$i];
                $temp->starttime = date('H:i:s', strtotime($starttime));
                $endtime = sprintf("%02d", $data->endhours[$i]) . ':' . sprintf("%02d", $data->endminutes[$i]) . $data->end_td[$i];
                $temp->endtime = date('H:i:s', strtotime($endtime));
                if ($data->rid[$i] > 0) {
                    $temp->id = $data->rid[$i];
                    $recordid = $DB->update_record('local_timeintervals_slots', $temp);
                } else {
                    $recordid = $DB->insert_record('local_timeintervals_slots', $temp);
                }
            }
        }

        return $recordid;
    }

    /**
     * @method  success_error_msg
     * @todo providing valid success and error message based on condition
     * @param object $output resultent record
     * @param string $success Success message
     * @param string $error Error messgae
     * @param string $currenturl redirection url
     * @return printing valid message
     */
    function success_error_msg($output, $success, $error, $currenturl, $dynamic_name1 = false) {
        $hier = new hierarchy();
        if ($output) {
            if ($dynamic_name1)
                $confirm_msg = get_string($success, 'local_timetable', $dynamic_name1);
            else
                $confirm_msg = get_string($success, 'local_timetable');

            $options = array('style' => 'notifysuccess');
        }
        else {
            $confirm_msg = get_string($error, 'local_timetable');
            $options = array('style' => 'notifyproblem');
        }

        $hier->set_confirmation($confirm_msg, $currenturl, $options);
    }

    /**
     * @method  check_loginuser_registrar_admin
     * @todo to display school list based logged in user (registrar, admin)     *
     * @param boolean $schoolids_in_formofstring (used to get schoolids in the form of string)
     * @return based on condition it returns array of objects or string type of data
     */
    public function check_loginuser_registrar_admin($schoolids_in_formofstring = false) {
        global $DB, $USER, $CFG;
        $hier1 = new hierarchy();
        //  checking of login user is admin..
        if (is_siteadmin($USER->id)) {
            $schoolid = $DB->get_records('local_costcenter', array('visible' => 1));
        } else {
            //------------if registrar not assigned to any school it throws exception
            $users = $hier1->get_manager();
            $schoolid = $hier1->get_assignedschools();
        } // end of else

        if (empty($schoolid)) {
            throw new schoolnotfound_exception();
        }

        if ($schoolids_in_formofstring) {
            foreach ($schoolid as $sid) {
                $temp[] = $sid->id;
            }
            $school_id = implode(',', $temp);
            return $school_id;
        } else
            return $schoolid;
    }
}

//------------------ end of class---------------------------------------------------

function local_timetable_timelayout_dialogform($timeslotobject, $dayname) {
    global $DB, $USER, $PAGE, $hier;
    $PAGE->requires->js('/local/timetable/js/timelayout_custom.js');
    $timel = $timeslotobject->timelayout;
    $timelayoutid = $timel->id;
    $schoolid = $timel->schoolid;
    $programid = $timel->programid;
    $semesterid = $timel->semesterid;
    $timeintervalslotid = $timeslotobject->slotid;
    $timeslotid = $timeslotobject->slotid;

    $sql = "SELECT c.id, c.fullname, c.shortname
             FROM {course} c
             JOIN {local_program_level_courses} lplc ON c.id = lplc.courseid
             JOIN {local_program_levels} lpl ON lplc.programid = lpl.programid
            WHERE lpl.programid = $programid AND lpl.id = $semesterid";
    $planclasses = $DB->get_records_sql($sql);

    $id = $timeintervalslotid . $dayname . 'dialog-form';
    $formid = $timeintervalslotid . $dayname . 'form';
    echo '<div class=' . $id . ' title="Schedule Class" style="display:none;">
       <p class="validateTips">All form fields are required.</p>

    <form class=' . $formid . '>
    <fieldset>';

    //---------------hidden values------------------------------
    echo "<input type='hidden' name='programid' value='$programid'   />";
    echo "<input type='hidden' name='semesterid' value='$semesterid'  />";
    echo "<input type='hidden' name='timeintervalslotid' value='$timeintervalslotid'   />";
    echo "<input type='hidden' name='timelayoutid' value='$timelayoutid'  />";
    echo "<input type='hidden' name='availableweekdays' value='$dayname'   />";
    echo "<input type='hidden' name='schoolid' value='$schoolid'  />";
    echo "<input type='hidden' name='timeintervalid' value='$timel->timeintervalid' />";

    $uniqueid = $timeintervalslotid . $dayname;
    //-------selecting time intervals-----------------------------------
    for ($i = 0; $i <= 12; $i++) {
        $hours[$i] = sprintf("%02d", $i);
    }
    for ($i = 0; $i < 60; $i+=5) {
        $minutes[$i] = sprintf("%02d", $i);
    }
    $timefomat = array('am' => 'AM', 'pm' => 'PM');

    //-------displaying classintervals-------------------------------------

    $classintervals = array();
    $timeintervalslist = $DB->get_records_sql("select id,starttime,endtime from {local_timeintervals_slots} where timeintervalid=$timel->id and visible=1 order by starttime");
    if (($timeintervalslist)) {
        $i = 1;
        foreach ($timeintervalslist as $timeintervals) {
            // $timings[$timeintervals->id]=  $timeintervals
            $timings[$timeintervals->id] = date('h:i A', strtotime($timeintervals->starttime)) . ' To ' . date('h:i A', strtotime($timeintervals->endtime)) . ' (Interval' . $i . ')';
            $i++;
        }
    }

    echo '<label class="local_timetable_dialog_label" for=timeinterval>Time intervals</label>';
    echo ' <select name="timeintervals_dialog" class="timeintervals_dialog"  multiple>';
    foreach ($timings as $key => $value) {
        if ($key == $timeintervalslotid)
            echo'  <option value=' . $key . ' selected >' . $value . '</option>';
        else
            echo'  <option value=' . $key . ' >' . $value . '</option>';
    }
    echo'</select>';
    echo "</br><input type=submit  value=submit>";
    echo '</fieldset>
         </form>
         </div>';
}


function local_timetable_insertscheduled_fromdailogform($formdata) {
    global $DB, $CFG, $USER;

    $batch_timelayoutinfo = $DB->get_record('local_batch_timetablelayout', array('id' => $formdata->timelayoutid));
    $semester_offerings = $DB->get_record('local_semester', array('id' => $batch_timelayoutinfo->semesterid));
    $classinfo = $DB->get_record('local_clclasses', array('id' => $formdata->classid));
    // $timeinterval_info = $DB->get_record('local_timeintervals', array('id' => $formdata->timeintervalid));
    //$schedule_data= $formdata;

    //---------instructor validation---------------------------
    if(isset($formdata->instructorid)){
       if (is_array($formdata->instructorid)) {
        $instructorids = implode(',', $formdata->instructorid);
       } else {
        $instructorids = $formdata->instructorid;
       }
    }
    else
    $instructorids =0;
    //--------------------inserting instructor----------------


    $schedule_data = new stdclass();
    $schedule_data->classid = $formdata->classid;
    $schedule_data->instructorid = $instructorids;
    $schedule_data->classtypeid = $formdata->classtype;
    $schedule_data->planid = $formdata->planid;
    $schedule_data->curriculumid = $formdata->curriculumid;
    if(isset($formdata->classroomid))
    $schedule_data->classroomid = $formdata->classroomid;
    $schedule_data->timelayoutid = $formdata->timelayoutid;
    $schedule_data->semesterid = $semester_offerings->id;
    $schedule_data->availableweekdays = $formdata->availableweekdays;
    if(isset( $classinfo->schoolid))
    $schedule_data->schoolid = $classinfo->schoolid;
    if($batch_timelayoutinfo->startdate && $batch_timelayoutinfo->enddate){
        $schedule_data->startdate = $batch_timelayoutinfo->startdate;
        $schedule_data->enddate = $batch_timelayoutinfo->enddate;
    }else{
        $schedule_data->startdate = $semester_offerings->startdate;
        $schedule_data->enddate = $semester_offerings->enddate;
    }
    $schedule_data->timecreated = time();
    $schedule_data->timemodified = time();
    $schedule_data->usermodified = $USER->id;
    if(isset($classinfo->cobaltcourseid))
    $schedule_data->courseid = $classinfo->cobaltcourseid;
    if(isset( $classinfo->departmentid))
    $schedule_data->departmentinid = $classinfo->departmentid;
    $schedule_data->color = $formdata->color;
    // $schedule_data->starttime = $timeinterval_info->starttime;
    // $schedule_data->endtime = $timeinterval_info->endtime;
    $schedule_data->batchid = $batch_timelayoutinfo->batchid;
    // $insertedrecord = $DB->insert_record('local_scheduleclass', $schedule_data

    if(isset($formdata->customfield) && $formdata->customfield ){
        $schedule_data->classid = 0;
        $schedule_data->classtypeid =0;
        $schedule_data->customtext=$formdata->customtext;
        $schedule_data->schoolid=$formdata->schoolid;
        $schedule_data->classroomid=0;
    }


    return $schedule_data;
}

function local_timetable_fromajax_cellcontent($scheduleid, $stu_classlist = null, $cellinfo=null) {
    global $DB, $CFG, $USER, $DB, $OUTPUT, $PAGE;
    $var = $DB->get_field('local_schedulesessions','sessionid',array('id' => $scheduleid));
    $crrdate = time();
    $var = explode(',', $var);
    $count= 0;
    foreach ($var as $key => $value) {
        $datacount = $DB->get_field_sql("SELECT id FROM {attendance_sessions} WHERE id IN ($value) AND sessdate > $crrdate ");
        if($datacount){
            $count++;
        }
    }
    $context = context_system::instance();
    $scheduleinfo = $DB->get_record('local_schedulesessions', array('id' => $scheduleid));
    $sessid = explode(', ', $scheduleinfo->sessionid);
    $coursename = $DB->get_field('course', 'fullname', ['id' => $scheduleinfo->courseid]);
    $sessiondata = $DB->get_record('attendance_sessions', array('id' =>$sessid[0]));
    $teachername = $DB->get_record('user',['id' => $sessiondata->teacherid]);
    $usercontext = context_user::instance($USER->id);
    if (isset($sessiondata->sessionname)) {
        $sessionname = '<span style="font-size: 15px;color: #000;">'.$sessiondata->sessionname.'</span>';
    } else {
        $sessionname = '';
    }

    // Displaying crud operation button.
    $delete_cap = array('local/timetable:manage', 'local/timetable:delete');
    if ($sessiondata->lasttaken == 0) {
        if ($count >= 1) {
            $editicon = '<i class="icon fa fa-pencil-square-o edit_icon" aria-hidden="true"></i>';
            $actions = '<ul  class="local_timetable_crudlink">
                        <li>'.
                        html_writer::link('javascript:void(0)', $editicon, array('title' => get_string('updatesession', 'local_program'), 'onclick' => '(function(e){ require("local_timetable/ajaxforms").init({contextid: '.$context->id.', component:"local_timetable", callback:"update_session", plugintype: "local", pluginname: "timetable", id:'.$sessid[0].' , semesterid: '.$scheduleinfo->semesterid.', action:2, slotid: '.$cellinfo->slotid.', dayname: '."'$scheduleinfo->availableweekdays'".',form_status:0, title: "updatesession" }) })(event)'))
                        .'</li></ul>';

            // for multiple session provide delete icon
            $deleteicon = '<i class="icon fa fa-trash delete_icon" aria-hidden="true"></i>';
            $actions .= '<ul  class="local_timetable_crudlink">
                        <li>'.
                        html_writer::link('javascript:void(0)', $deleteicon, array('title' => get_string('delete', 'local_timetable'), 'onclick' => '(function(e){ require("local_timetable/ajaxforms").sessdeleteconfirm({contextid: '.$context->id.', id:'.$scheduleid.', sessionname:'."'$sessiondata->sessionname'".' }) })(event)'))
                        .'</li></ul>';
        }
    }

    $divid = $scheduleinfo->timeintervalslotid . $scheduleinfo->availableweekdays . 'celldata';
    // ----used to display
    if(isset($scheduleinfo->customtext)){
        $ss = "<div id='$divid' ><ul class='local_timetable_celldata' style='color:#353535;'>";
        $ss .="<li><span id='timetable_customtext'>$scheduleinfo->customtext</span> $actions</li></ul><div>";
    }

    if (has_capability('local/clclasses:enrollclass', $usercontext) && !is_siteadmin()) {
        $class = 'local_timetable_studentcell';
    } else {
        $class = 'local_timetable_celldata';

        $ss = "<div id='$divid'><ul class='$class' style='color:#353535;'>";
        $ss .= "<li>$actions</li>";
        $ss .="<li>$sessionname</li>";
        $ss .="<li class='text-muted d-flex'><div class='d-flex'>".get_string('coursename','local_timetable').":</div><div>$coursename</div></li>";
        $ss .= "<li class='text-muted'>".get_string('teachername','local_timetable').": $teachername->firstname $teachername->lastname</li>
                </ul><div>";
    }
    return $ss;
}

function local_timetable_layoutcell_query($cellinfo, $dayname, $stu_enrolledlist = NULL) {
    global $DB, $CFG, $USER, $DB;
    $scheduled_ids = array();
    if ($cellinfo) {
        $timelay = $cellinfo->timelayout;
        $semoffering = $DB->get_record('local_program_levels', array('id' => $timelay->semesterid));
        if($timelay->startdate && $timelay->enddate ){
            $startdate = $timelay->startdate;
            $enddate = $timelay->enddate;
        }else{
            $startdate = $semoffering->startdate;
            $enddate = $semoffering->enddate;
        }

        $sql = "SELECT lss.id, lss.availableweekdays, lss.instructorid, lss.semesterid,
                 lss.timeintervalslotid
                 FROM  {local_schedulesessions} lss
                 -- JOIN  {local_timeintervals_slots} lts ON lss.timeintervalslotid = lts.id
                WHERE lss.schoolid = {$timelay->schoolid} AND lss.semesterid = {$timelay->semesterid}
                 AND lss.startdate = {$startdate} AND lss.enddate = {$enddate}
                 AND ( '{$cellinfo->starttime}' between lss.starttime AND lss.endtime)
                 AND ( '{$cellinfo->endtime}' between lss.starttime AND lss.endtime)
                 AND lss.timeintervalid = {$timelay->id} ";
        $cellscheduledinfo = $DB->get_records_sql($sql);

        foreach ($cellscheduledinfo as $record) {
            $daylist = explode('-', $record->availableweekdays);
            if (in_array($dayname, $daylist)) {
                $scheduled_ids[] = $record->id;
            }
        }
    } else {
        $scheduled_ids = array();
    }

    if (sizeof($scheduled_ids) > 0){
        $output = local_timetable_cellcontent_format($scheduled_ids, $dayname, $stu_enrolledlist, $cellinfo);
    } else {
        $output = local_timetable_emptycell_content($cellinfo, $dayname, $stu_enrolledlist);
    }

    return $output;
}

// end of function
function local_timetable_cellcontent_format($scheduled_ids, $dayname=null, $enrolledlist = NULL , $cellinfo = NULL) {
    global $DB, $CFG, $USER, $PAGE, $OUTPUT;
    $usercontext = context_user::instance($USER->id);
    $semid = $cellinfo->timelayout->semesterid;
    $response = '';
    $output = new stdClass();
    if (($scheduled_ids)) {
        $length = sizeof($scheduled_ids);
        foreach ($scheduled_ids as $key => $record) {

            $timeintervalslotid = $DB->get_field('local_schedulesessions', 'timeintervalslotid', ['id' => $record]);
            $scheduleinfo = $DB->get_record('local_timeintervals_slots', array('id' => $timeintervalslotid));
            $timeinterval = explode(',', $scheduleinfo->id);
            $colspan[] = sizeof($timeinterval);

            $response .= local_timetable_fromajax_cellcontent($record, $enrolledlist, $cellinfo);

            // ------ adding hr line  incase they scheduled multiple classes---------
            if (($length - 1) != $key)
                $response .='<hr id="local_timetable_cellhr"></hr>';

            //--------adding add button ,only one per cell------------
            if (empty($stu_enrolledlist)) {
                if (($length - 1) == $key) {
                    // Displaying crud operation button.
                    // $addicon = '<i class="icon fa fa-plus-circle float-right" aria-hidden="true"></i>';
                    $context = context_system::instance();
                    $delete_cap = array('local/timetable:manage', 'local/timetable:delete');
                    if (has_any_capability($delete_cap, $usercontext)) {
                        $response .= html_writer::link('javascript:void(0)', $addicon, array('class' => 'local_timetable_addbutton' ,'title' => get_string('addsession', 'local_timetable'), 'onclick' => '(function(e){ require("local_timetable/ajaxforms").init({contextid: '.$context->id.', component:"local_timetable", callback:"session_form", plugintype: "local", pluginname: "timetable", semesterid: '.$semid.', action:1, slotid: '.$cellinfo->slotid.', dayname: '."'$dayname'".',form_status:0,title: "addsession" }) })(event)'));
                    }
                }
            }
        }
        $output->content = $response;
        $output->colspan = max($colspan);
        $output->set = $timeinterval;
        $output->scheduleids = $scheduled_ids;
        return $output;
    } else {
        $output->content = 0;
        $output->colspan = 0;
        $output->scheduleids = 0;
        return $output = '';
    }
}

function local_timetable_emptycell_content($cellinfo, $dayname) {
    global $DB, $CFG, $USER, $OUTPUT, $PAGE;
    $usercontext = context_user::instance($USER->id);

    // displaying crud operation button.
    $semid = $cellinfo->timelayout->semesterid;
    $delete_cap = array('local/timetable:manage', 'local/timetable:delete');
    if (has_any_capability($delete_cap, $usercontext)) {
        $context = context_system::instance();
        // $url = new moodle_url('/local/program/index.php');
        $icon = '<i class="icon fa fa-plus-circle addsession_icon" aria-hidden="true"></i>';
        // $action = html_writer::link($url, $icon, array('title'=>get_string('download_certificate','tool_certificate'), 'target' => '_blank'));
        $action = html_writer::link('javascript:void(0)', $icon, array('title' => get_string('addsession', 'local_program'), 'onclick' => '(function(e){ require("local_timetable/ajaxforms").init({contextid: '.$context->id.', component:"local_timetable", callback:"session_form", plugintype: "local", pluginname: "timetable", semesterid: '.$semid.', action:1, slotid: '.$cellinfo->slotid.', dayname: '."'$dayname'".',form_status:0,title: "createsession" }) })(event)'));
    } else {
        $action = '';
    }
    $divid = $cellinfo->id . $dayname . 'celldata';
    $emptycell = "<div id=$divid >
                    <ul class='local_timetable_celldata'>
                    <li>      </li>
                    <li>       </li>
                    <li>       </li>
                    <li>$action</li>
                    </ul>
                  <div>";
    $output = new stdClass();
    $output->content = $emptycell;
    $output->colspan = 0;
    $output->scheduleids = 0;
    return $output;
}

function local_timetable_timetablelayout_display($timelayoutid, $fromajax = null) {
    global $DB, $USER, $PAGE, $OUTPUT;

    // ------- getting timeintervals-----------------------------------
    $timetablelayoutinfo = $DB->get_record('local_timeintervals', array('semesterid' => $timelayoutid));
    $timeintervalslist = $DB->get_records_sql("SELECT ts.id as slotid ,ti.id, ts.starttime, ts.endtime  FROM {local_timeintervals_slots} as ts
                                                     JOIN {local_timeintervals} as ti on ti.id=ts.timeintervalid
             WHERE ts.visible=1 AND
             ts.timeintervalid = {$timetablelayoutinfo->id} order by ts.starttime ASC ");
    if (empty($timeintervalslist)) {
        throw new Exception('set the timeintervals to proceed further');
    }

    $days = array('M' => 'mon', 'TU' => 'tue', 'W' => 'wed', 'TH' => 'thur', 'F' => 'fri', 'SA' => 'sat'/*,'SU'=>'sun'*/);
    
    //----- adding timetable layout info------------------
    foreach ($timeintervalslist as $timeinterval) {
        $timeinterval->timelayout = $timetablelayoutinfo;
        $timeinterval->days = $days;
        $timelayout_intervalinfo[] = $timeinterval;
    }

    //---------creating two dimensional array ---to  make available info for each and every cell
    foreach ($days as $key => $value) {
        $daytimeslots[$key] = $timelayout_intervalinfo;
    }// end of foreach

    $table = new html_table();
    $table->head[] = "   ";

    foreach ($days as $key => $value) {
        $table->head[] = local_timetable_date_format($key);
    }

    $tabledata = array();
    $i = 0;
    foreach($timelayout_intervalinfo as $key => $value) {
        $tablerows = array();
        $colsspan_array = array();
        $cellcolspan = array();
        $tablerows[] = date("g:i A", strtotime($value->starttime)) . '-' . date("g:i A", strtotime($value->endtime));
        foreach($value->days as $daykeys => $dayname) {
            $cellcontent = local_timetable_layoutcell_query($value, $daykeys);

            // It contains timeintervalslotids , it may single slot or incase of merging two or three columns it holds more than one.
            if(isset($cellcontent->set)) {
                $set = $cellcontent->set;
            } else {
                $set = array();
            }
            $cellcolspans[$value->slotid] = array('set' => $set,'colspan' => $cellcontent->colspan);
            $colspans_array = array_values($cellcolspans);
            $optioncell = new html_table_cell($cellcontent->content);

            if ($colspans_array[$i] > 1) {
                //--------comparing(previous and present cell) each and every cell colspan--------------------------
                if( isset($colspans_array[$i]['set']) && isset($colspans_array[$i-1]['set'])
                    && $colspans_array[$i-1]['set'] && !empty($colspans_array[$i]['set'])) {
                    $setdiff = array_diff($colspans_array[$i]['set'],$colspans_array[$i-1]['set']);
                    $setdiffcount = sizeof($setdiff);
                } else {
                    $setdiffcount = 1;
                }

                if ($i > 0 && $colspans_array[$i - 1]['colspan'] > 1 && ($setdiffcount<=0) && $colspans_array[$i]['colspan'] > 1 ) {
                 //-----if colspan is > 1 then that cell content will be assigned to empty
                    $optioncell = '';
                } else {
                    $optioncell->colspan = $colspans_array[$i]['colspan'];
                }
            }

            // Checking columns merging timeintervalslots sets belongs to speciific timeintervalslot.
            if ($set) {
                $flag = 0;
                foreach ($cellcontent->set as $setrecord) {
                    if (trim($value->slotid) == trim($setrecord)) {
                      $flag = 1;
                      break;
                    }
                }
                if ($flag == 0) {
                    $optioncell = '';
                }
            }
            //-----------end of checking --------------------

            $cellcolspan[$value->slotid] = $cellcontent->colspan;

            if (!empty($optioncell)) {
                local_timetable_call_delete_conformation($cellcontent->scheduleids);
                $tablerows[] = $optioncell;
            }
            $i++;
        }
        $tabledata[] = $tablerows;
    }

    $table->id = "local_timetable_timelayout";
    $table->size = array('', '10%', '10%', '11%', '11%', '11%', '11%', '10%', '10%', '10%');
    $table->align = array('left', 'left', 'left', 'left', 'left', 'left', 'left', 'left', 'left');
    $table->width = '99%';
    $table->data = $tabledata;

    $response = html_writer::table($table);
    return $response;
}

function local_timetable_call_delete_conformation($scheduleids) {
    global $CFG, $DB, $PAGE, $USER;

    if (!empty($scheduleids)) {
        foreach ($scheduleids as $key => $record) {

            $PAGE->requires->event_handler('#celldeleteconfirm' . $record . '', 'click', 'M.util.tmahendra_show_confirm_dialog', array('message' => get_string('scheduledsessiondelconfirm', 'local_timetable'), 'callbackargs' => array('id' => $record)));
            //    $PAGE->requires->event_handler('#batchdeleteconfirm' . $cohort->id . '', 'click', 'M.util.tmahendra_show_confirm_dialog', array('message' => $message = get_string('batch_delconfirm', 'local_batches', format_string($cohort->name)), 'callbackargs' => array('id' => $cohort->id)));
        }
    }
}

// end of function

function local_timetable_date_format($day) {
    global $CFG, $DB, $PAGE, $USER;

    switch ($day) {
        case 'M': $res = 'Mon';
            break;

        case 'TU':$res = 'Tue';
            break;

        case 'W':$res = 'Wed';
            break;

        case 'TH':$res = 'Thu';
            break;
        case 'F':$res = 'Fri';
            break;

        case 'SA':$res = 'Sat';
            break;
        case 'SU':$res = 'Sun';
            break;
        default: '';
    }

    return $res;
}

/**
 * Serve the new group form as a fragment.
 *
 * @param array $args List of named arguments for the fragment loader.
 * @return string
 */
function local_timetable_output_fragment_session_form($args) {
    global $CFG, $PAGE, $DB;
    $args = (object) $args;
    $context = $args->context;
    $return = '';
    $formdata = [];
    if (!empty($args->jsonformdata)) {
        $serialiseddata = json_decode($args->jsonformdata);
        parse_str($serialiseddata, $formdata);
    }
    $courseid = $DB->get_field('local_schedulesessions', 'courseid', ['sessionid' => $args->id]);

    if ($args->id > 0) {
        $mform = new session_form(null, array('form_status' => $args->form_status, 'id' => $args->id, 'semesterid' => $args->semesterid, 'coursesid' => $courseid), 'post', '', null, true, $formdata);
    } else {
        $mform = new session_form(null, array('form_status' => $args->form_status, 'semesterid' => $args->semesterid, 'coursesid' => $courseid, 'dayname' => $args->dayname, 'slotid' => $args->slotid), 'post', '', null, true, $formdata);
    }

    if (!empty((array) $serialiseddata)) {
        // If we were passed non-empty form data we want the mform to call validation functions and show errors.
        $mform->is_validated();
    }
    $formheaders = array_keys($mform->formstatus);
    $nextform = array_key_exists($args->form_status, $formheaders);
    if ($nextform === false) {
        return false;
    }
    $output = $PAGE->get_renderer('local_timetable');
    ob_start();
    $formstatus = array();
    foreach (array_values($mform->formstatus) as $k => $mformstatus) {
        $activeclass = $k == $args->form_status ? 'active' : '';
        $formstatus[] = array('name' => $mformstatus, 'activeclass' => $activeclass);
    }
    $formstatusview = new \local_timetable\output\form_status($formstatus);
    $return .= $output->render($formstatusview);
    $mform->display();
    $return .= ob_get_contents();
    ob_end_clean();

    return $return;
}

/**
 * Serve the new group form as a fragment.
 *
 * @param array $args List of named arguments for the fragment loader.
 * @return string
 */
function local_timetable_output_fragment_update_session($args) {
    global $CFG, $PAGE, $DB;
    $args = (object) $args;
    $context = $args->context;
    $return = '';
    $formdata = [];
    if (!empty($args->jsonformdata)) {
        $serialiseddata = json_decode($args->jsonformdata);
        parse_str($serialiseddata, $formdata);
    }
    $courseid = $DB->get_field('attendance_sessions', 'courseid', ['id' => $args->id]);
    $cm = $DB->get_field('course_modules', 'id', ['course' => $courseid, 'module' => 27]);
    if ($args->id > 0) {
        $data = $DB->get_record('attendance_sessions', array('id' => $args->id));
        $mform = new update_session(null, array(/*'form_status' => $args->form_status,*/ 'id' => $args->id, 'semesterid' => $args->semesterid, 'course' => $courseid, 'cm' => $cm, 'teacherid' => $data->teacherid), 'post', '', null, true, $formdata);
        $mform->set_data($data);
    }

    if (!empty((array) $serialiseddata)) {
        // If we were passed non-empty form data we want the mform to call validation functions and show errors.
        $mform->is_validated();
    }
    ob_start();
    $mform->display();
    $return .= ob_get_contents();
    ob_end_clean();

    return $return;
}

function local_timetable_add_session ($data) {
    global $DB, $CFG, $USER;
    require_once($CFG->dirroot.'/local/attendance/locallib.php');
    require_once($CFG->dirroot.'/local/timetable/lib.php');
    require_once($CFG->dirroot.'/lib/datalib.php');

    if ($data->coursesid > 0) {
        $courseid = $data->coursesid;
    } else {
        $courseid = $DB->get_field('local_schedulesessions', 'courseid', ['timeintervalslotid' => $data->slotid, 'semesterid' => $data->semesterid, 'availableweekdays' => $data->dayname]);
    }

    $pageparams->action = 1;
    $cmidsql = "SELECT cm.id
                 FROM {course_modules} cm
                 JOIN {modules} m ON cm.module = m.id
                WHERE cm.course = {$courseid} AND m.name = 'attendance' ";
    $id = $DB->get_field_sql($cmidsql);
    $cm = get_coursemodule_from_id('attendance', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $attdata = $DB->get_record('attendance', array('id' => $cm->instance), '*', MUST_EXIST);
    $context = context_module::instance($cm->id);
    $att = new local_attendance_structure($attdata, $cm, $course, $context, $pageparams);

    $sdays = array();
    /*$currentdate = explode('-', date('d-m-Y'));
    $currentday =  getdate(mktime(0,0,0,$currentdate[1],$currentdate[0],$currentdate[2]));*/
    switch ($data->dayname) {
        case 'M':
            $sdays['Mon'] = 1;
            break;
        case 'TU':
            $sdays['Tue'] = 1;
            break;
        case 'W':
            $sdays['Wed'] = 1;
            break;
        case 'TH':
            $sdays['Thu'] = 1;
            break;
        case 'F':
            $sdays['Fri'] = 1;
            break;
        case 'SA':
            $sdays['Sat'] = 1;
            break;
        default:
            $sdays['Sun'] = 1;
            break;
    }
    if ($sdays){
        $data->sdays = $sdays;
    }

    $duration = $DB->get_record('local_timeintervals_slots', ['id' => $data->slotid]);
    $stime = explode(':', $duration->starttime);
    $etime = explode(':', $duration->endtime);

    $data->sestime = array(
        'starthour' => $stime[0],
        'startminute' => $stime[1],
        'endhour' => $etime[0],
        'endminute' => $etime[1]
    );

    $sessions = sessions_data_for_add($data, $att);

    $sessiondata = $att->add_sessions($sessions);

    foreach ($sessions as $key => $sessvalue) {
        $sessionids[] = $sessvalue->id;
    }
    $sessids = implode(', ', $sessionids);

    $timeintervalid = $DB->get_record('local_timeintervals_slots', ['id' => $data->slotid]);
    $semesterdata = $DB->get_record('local_program_levels', ['id' => $data->semesterid]);
    $programdata = $DB->get_record('local_program', ['id' => $semesterdata->programid]);

    $scheduledata = new stdClass();
    $scheduledata->attendanceid = $attdata->id;
    $scheduledata->instructorid = $data->teacherid;
    $scheduledata->schoolid = $programdata->costcenter;
    $scheduledata->semesterid = $data->semesterid;
    $scheduledata->courseid = $attdata->course;
    $scheduledata->timeintervalslotid = $data->slotid;
    $scheduledata->startdate = $semesterdata->startdate;
    $scheduledata->enddate = $semesterdata->enddate;
    $scheduledata->availableweekdays = $data->dayname;
    $scheduledata->starttime = $timeintervalid->starttime;
    $scheduledata->endtime = $timeintervalid->endtime;
    $scheduledata->batchid = $programdata->batchid;
    $scheduledata->timeintervalid = $timeintervalid->timeintervalid;
    if (isset($data->addmultiply)) {
        $scheduledata->addmultiply = $data->addmultiply;
    } else {
        $scheduledata->addmultiply = 0;
    }
    $scheduledata->sessionid = $sessids;
    $scheduledata->visible = 1;
    $scheduledata->usermodified = $USER->id;
    $scheduledata->timecreated = time();
    $scheduledata->timemodified = 0;

    $DB->insert_record('local_schedulesessions', $scheduledata);

    if($form_status == 0){
        $userdata = $sessions[0]->id;
    }

    return $userdata;
}

/**
 * Get session data for form.
 * @param stdClass $formdata moodleform - attendance form.
 * @param mod_attendance_structure $att - used to get attendance level subnet.
 * @return array.
 */
function sessions_data_for_add($formdata, local_attendance_structure $att) {
    global $CFG;

    require_once($CFG->dirroot.'/mod/attendance/locallib.php');
    require_once($CFG->dirroot.'/lib/datalib.php');

    $sesstarttime = $formdata->sestime['starthour'] * HOURSECS + $formdata->sestime['startminute'] * MINSECS;

    $sesendtime = $formdata->sestime['endhour'] * HOURSECS + $formdata->sestime['endminute'] * MINSECS;
    $sessiondate = $formdata->sessiondate + $sesstarttime;
    $duration = $sesendtime - $sesstarttime;

    if (empty($duration)) {
        $duration = '';
    }
    if (empty($sessiondate)) {
        $sessiondate = '';
    }
    if (empty(get_config('attendance', 'enablewarnings'))) {
        $absenteereport = get_config('attendance', 'absenteereport_default');
    } else {
        $absenteereport = empty($formdata->absenteereport) ? 0 : 1;
    }

    $now = time();

    if (empty(get_config('attendance', 'studentscanmark'))) {
        $formdata->studentscanmark = 0;
    }

    $calendarevent = 0;
    if (isset($formdata->calendarevent)) { // Calendar event should be created.
        $calendarevent = 1;
    }

    $sessions = array();
    if (isset($formdata->addmultiply)) {
        $startdate = $sessiondate;
        $enddate = $formdata->sessionenddate + DAYSECS; // Because enddate in 0:0am.
        if ($enddate < $startdate) {
            return null;
        }
        // Getting first day of week.
        $sdate = $startdate;
        $dinfo = usergetdate($sdate);
        $cday = '';

        switch ($formdata->dayname) {
            case 'M':
                $cday = 1;
                break;
            case 'TU':
                $cday = 2;
                break;
            case 'W':
                $cday = 3;
                break;
            case 'TH':
                $cday = 4;
                break;
            case 'F':
                $cday = 5;
                break;
            case 'SA':
                $cday = 6;
                break;
            default:
                $cday = 0;
            break;
        }

        if ($formdata->dayname === 'SU') { // Week start from sunday.
            $startweek = $startdate - $dinfo['wday'] * DAYSECS; // Call new variable.
        } else {
            $wday = $cday === 0 ? 7 : $cday;
            $startweek = $startdate - ($wday - 1) * 7;
        }

        $wdaydesc = array(0 => 'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat');

        while ($sdate < $enddate) {
            if ($sdate < $startweek + WEEKSECS) {
                $dinfo = usergetdate($sdate);
                if (isset($formdata->sdays) && array_key_exists($wdaydesc[$dinfo['wday']], $formdata->sdays)) {
                    if ($dinfo['wday'] == $wday) {
                        $sess = new stdClass();
                        $sess->sessdate = make_timestamp($dinfo['year'], $dinfo['mon'], $dinfo['mday'],
                            $formdata->sestime['starthour'], $formdata->sestime['startminute']);
                        $sess->sessedate = make_timestamp($dinfo['year'], $dinfo['mon'], $dinfo['mday'],
                            $formdata->sestime['endhour'], $formdata->sestime['endminute']);
                        $sess->duration = $duration;
                        $sess->sesstarttime = $sesstarttime;
                        if ($formdata->description == null) {
                            $sess->description = '';
                        } else {
                            $sess->description = $formdata->description;
                        }
                        $sess->calendarevent = $calendarevent;
                        $sess->timemodified = $now;
                        $sess->absenteereport = $absenteereport;
                        $sess->studentpassword = '';
                        $sess->includeqrcode = 0;
                        $sess->rotateqrcode = 0;
                        $sess->rotateqrcodesecret = '';
                        $sess->teacherid = $formdata->teacherid;
                        $sess->sessionname = $formdata->sessionname;
                        $sess->courseid = $formdata->coursesid;
                        $sess->slotid = $formdata->slotid;
                        $sess->session_type = $formdata->session_type;
                        $sess->building = $formdata->building;
                        $sess->room = $formdata->room;
                        $sess->batch_group = $formdata->batch_group;

                        if (!empty($formdata->usedefaultsubnet)) {
                            $sess->subnet = $att->subnet;
                        } else {
                            $sess->subnet = $formdata->subnet;
                        }
                        $sess->automark = $formdata->automark;
                        $sess->automarkcompleted = 0;

                        if (!empty($formdata->automarkcmid)) {
                            $sess->automarkcmid = $formdata->automarkcmid;
                        } else {
                            $sess->automarkcmid = 0;
                        }
                        if (!empty($formdata->preventsharedip)) {
                            $sess->preventsharedip = $formdata->preventsharedip;
                        }
                        if (!empty($formdata->preventsharediptime)) {
                            $sess->preventsharediptime = $formdata->preventsharediptime;
                        }

                        if (isset($formdata->studentscanmark)) { // Students will be able to mark their own attendance.
                            $sess->studentscanmark = 1;
                            if (isset($formdata->autoassignstatus)) {
                                $sess->autoassignstatus = 1;
                            }
                            if (!empty($formdata->includeqrcode)) {
                                $sess->includeqrcode = $formdata->includeqrcode;
                            }
                            if (!empty($formdata->rotateqrcode)) {
                                $sess->rotateqrcode = $formdata->rotateqrcode;
                                // $sess->studentpassword = local_attendance_random_string();
                                $sess->rotateqrcodesecret = local_attendance_random_string();
                            }
                            if (!empty($formdata->preventsharedip)) {
                                $sess->preventsharedip = $formdata->preventsharedip;
                            }
                            if (!empty($formdata->preventsharediptime)) {
                                $sess->preventsharediptime = $formdata->preventsharediptime;
                            }
                        } else {
                            $sess->subnet = '';
                            $sess->automark = 0;
                            $sess->automarkcompleted = 0;
                            $sess->preventsharedip = 0;
                            $sess->preventsharediptime = '';
                        }
                        $sess->statusset = $formdata->statusset;

                        attendance_fill_groupid($formdata, $sessions, $sess);
                    }
                }
                $sdate += DAYSECS;
            } else {
                $startweek += WEEKSECS * $formdata->period;
                $sdate = $startweek;
            }
        }
    } else {
        $sess = new stdClass();
        if (!empty($formdata->dayname)) {
            $dayto_add = '';
            if ($formdata->sessiondate <= strtotime(date('d-m-Y'))) {
                switch ($formdata->dayname) {
                    case 'M':
                        $dayto_add = strtotime('next monday', $formdata->sessiondate);
                        break;
                    case 'TU':
                        $dayto_add = strtotime('next tuesday', $formdata->sessiondate);
                        break;
                    case 'W':
                        $dayto_add = strtotime('next wednesday', $formdata->sessiondate);
                        break;
                    case 'TH':
                        $dayto_add = strtotime('next thursday', $formdata->sessiondate);
                        break;
                    case 'F':
                        $dayto_add = strtotime('next friday', $formdata->sessiondate);
                        break;
                    case 'SA':
                        $dayto_add = strtotime('next saturday', $formdata->sessiondate);
                        break;
                    default:
                        $dayto_add = strtotime('next sunday', $formdata->sessiondate);
                    break;
                }
                $dinfo = usergetdate($dayto_add);
            } else if ($formdata->sessiondate > strtotime(date('d-m-Y'))) {
                $days = ['SU', 'M', 'TU', 'W', 'TH', 'F', 'SA'];
                $nextdays = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
                foreach ($days as $key => $value) {
                        $sdate = usergetdate($formdata->sessiondate);
                    if ($formdata->dayname == $value) {
                        if ($sdate['wday'] != $key) {
                            $dayto_add = strtotime('next '.$nextdays[$key].'', $formdata->sessiondate);
                        } else {
                            $dayto_add = $formdata->sessiondate;
                        }
                        $dinfo = usergetdate($dayto_add);
                    }
                }
            }
            $sess->sessdate = make_timestamp(
                $dinfo['year'],
                $dinfo['mon'],
                $dinfo['mday'],
                $formdata->sestime['starthour'],
                $formdata->sestime['startminute']
            );
            $sess->sessedate = make_timestamp(
                $dinfo['year'],
                $dinfo['mon'],
                $dinfo['mday'],
                $formdata->sestime['endhour'],
                $formdata->sestime['endminute']
            );
        } else {
            $sess->sessdate = make_timestamp(
                date("Y", $formdata->sessiondate),
                date("m", $formdata->sessiondate),
                date("d", $formdata->sessiondate),
                $formdata->sestime['starthour'],
                $formdata->sestime['startminute']
            );
            $sess->sessedate = make_timestamp(
                date("Y", $formdata->sessiondate),
                date("m", $formdata->sessiondate),
                date("d", $formdata->sessiondate),
                $formdata->sestime['endhour'],
                $formdata->sestime['endminute']
            );
        }
        $sess->duration = $duration;
        $sess->sesstarttime = $sesstarttime;
        if ($formdata->description == null) {
            $sess->description = '';
        } else {
            $sess->description = $formdata->description;
        }
        $sess->calendarevent = $calendarevent;
        $sess->timemodified = $now;
        $sess->studentscanmark = 0;
        $sess->autoassignstatus = 0;
        $sess->subnet = '';
        $sess->studentpassword = '';
        $sess->automark = 0;
        $sess->automarkcompleted = 0;
        $sess->teacherid = $formdata->teacherid;
        $sess->sessionname = $formdata->sessionname;
        $sess->courseid = $formdata->coursesid;
        $sess->slotid = $formdata->slotid;
        $sess->session_type = $formdata->session_type;
        $sess->building = $formdata->building;
        $sess->room = $formdata->room;
        $sess->batch_group = $formdata->batch_group;

        if (!empty($formdata->automarkcmid)) {
            $sess->automarkcmid = $formdata->automarkcmid;
        } else {
            $sess->automarkcmid = 0;
        }

        $sess->absenteereport = $absenteereport;
        $sess->includeqrcode = 0;
        $sess->rotateqrcode = 0;
        $sess->rotateqrcodesecret = '';

        if (!empty($formdata->usedefaultsubnet)) {
            $sess->subnet = $att->subnet;
        } else {
            $sess->subnet = $formdata->subnet;
        }

        if (!empty($formdata->automark)) {
            $sess->automark = $formdata->automark;
        }
        if (!empty($formdata->preventsharedip)) {
            $sess->preventsharedip = $formdata->preventsharedip;
        }
        if (!empty($formdata->preventsharediptime)) {
            $sess->preventsharediptime = $formdata->preventsharediptime;
        }

        if (isset($formdata->studentscanmark) && !empty($formdata->studentscanmark)) {
            // Students will be able to mark their own attendance.
            $sess->studentscanmark = 1;
            if (isset($formdata->autoassignstatus) && !empty($formdata->autoassignstatus)) {
                $sess->autoassignstatus = 1;
            }
            if (!empty($formdata->includeqrcode)) {
                $sess->includeqrcode = $formdata->includeqrcode;
            }
            if (!empty($formdata->rotateqrcode)) {
                $sess->rotateqrcode = $formdata->rotateqrcode;
                // $sess->studentpassword = local_attendance_random_string();
                $sess->rotateqrcodesecret = local_attendance_random_string();
            }
            if (!empty($formdata->usedefaultsubnet)) {
                $sess->subnet = $att->subnet;
            } else {
                $sess->subnet = $formdata->subnet;
            }

            if (!empty($formdata->automark)) {
                $sess->automark = $formdata->automark;
            }
            if (!empty($formdata->preventsharedip)) {
                $sess->preventsharedip = $formdata->preventsharedip;
            }
            if (!empty($formdata->preventsharediptime)) {
                $sess->preventsharediptime = $formdata->preventsharediptime;
            }
        }
        if ($sess->statusset > 0) {
            $sess->statusset = $formdata->statusset;
        } else {
            $sess->statusset = 0;
        }

        local_attendance_fill_groupid($formdata, $sessions, $sess);
    }

    return $sessions;
}

function local_timetable_update_session ($data) {
    global $DB, $USER, $CFG;
    require_once($CFG->dirroot.'/local/attendance/locallib.php');
    require_once($CFG->dirroot.'/lib/datalib.php');

    if ($data->coursesid > 0) {
        $courseid = $data->coursesid;
    } else {
        $courseid = $DB->get_field('local_schedulesessions', 'courseid', ['timeintervalslotid' => $data->slotid, 'semesterid' => $data->semesterid, 'availableweekdays' => $data->dayname]);
    }

    $pageparams->action = 2;
    $cmidsql = "SELECT cm.id
                 FROM {course_modules} cm
                 JOIN {modules} m ON cm.module = m.id
                WHERE cm.course = {$courseid} AND m.name = 'attendance' ";
    $id = $DB->get_field_sql($cmidsql);
    $cm = get_coursemodule_from_id('attendance', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $attdata = $DB->get_record('attendance', array('id' => $cm->instance), '*', MUST_EXIST);
    $context = context_module::instance($cm->id);
    $att = new local_attendance_structure($attdata, $cm, $course, $context, $pageparams);

    if (empty($data->autoassignstatus)) {
        $data->autoassignstatus = 0;
    }

    $alldata = $DB->get_record('attendance_sessions', ['id' => $data->id]);

    if ($alldata) {
        $starttime = $alldata->sessdate - usergetmidnight($alldata->sessdate);
        $starthour = floor($starttime / HOURSECS);
        $startminute = floor(($starttime - $starthour * HOURSECS) / MINSECS);

        $enddate = $alldata->sessdate + $alldata->duration;
        $endtime = $enddate - usergetmidnight($enddate);
        $endhour = floor($endtime / HOURSECS);
        $endminute = floor(($endtime - $endhour * HOURSECS) / MINSECS);

        $sesstarttime = $starthour * HOURSECS + $startminute * MINSECS;
        $sessiondate = $alldata->sessdate - $sesstarttime;

        $sestime = array(
            'starthour' => $starthour,
            'startminute' => $startminute,
            'endhour' => $endhour,
            'endminute' => $endminute
        );

        $data->attendanceid = $alldata->attendanceid;
        $data->groupid = $alldata->groupid;
        if ($data->sessionname == null) {
            $data->sessionname = $alldata->sessionname;
        } else {
            $alldata->sessionname;
        }
        $data->sessiondate = $sessiondate;
        $data->sestime = $sestime;
        $data->lasttaken = $alldata->lasttaken;
        $data->lasttakenby = $alldata->lasttakenby;
        $data->timemodified = $alldata->timemodified;
        if ($data->description == null) {
            $data->description = '';
        }
        if ($data->teacherid == null) {
            $data->teacherid = $alldata->teacherid;
        }
        if ($alldata->calendarevent == null) {
            $data->calendarevent = $data->calendarevent;
        }
    }
    $sessids = $DB->get_field('local_schedulesessions', 'sessionid', ['timeintervalslotid' => $data->slotid]);
    $crrdate = time();
    $sessid = explode(', ', $sessids);
    $sqlid = array();
    foreach ($sessid as $ids => $value) {
                $sqlid[$ids] = $DB->get_field_sql("SELECT id FROM {attendance_sessions} WHERE id IN ($value) AND sessdate > $crrdate ");
            }
    $sqlid = array_flip($sqlid);
            //  to get the  ($sqlid) array values
    $sqlids = array_flip($sqlid);
    // to get the non empty values from the ($sqlid) array
    if(!empty($sqlid)){
        foreach ($sqlids as $ids) {
            $att->update_session_from_form_data($data, $ids);
        }
    }

    $scheduleid = $DB->get_field('local_schedulesessions', 'id', ['sessionid' => $data->id]);
    if ($scheduleid) {
        $scheduledata->id  = $scheduleid;
        $scheduledata->timemodified = time();
        $DB->update_record('local_schedulesessions', $scheduledata);
    }

    return $data->id;
}

function listof_semester_timetable ($stable, $filterdata) {
    global $DB, $USER, $CFG, $OUTPUT;
    $params = array();
    $lablestring = get_config('local_costcenter');
    $systemcontext = context_system::instance();
    $allcostcenter = $DB->get_records('local_costcenter', ['visible' => 1]);
    $costcenterids = implode(', ', array_keys($allcostcenter));

    $selectsql = "SELECT map.*, ba.*, p.id as programid ";
    $fromsql = " FROM {cohort} ba
                 JOIN {local_groups} map ON map.cohortid = ba.id
                 JOIN {local_program} p ON p.batchid = ba.id
                 JOIN {local_costcenter} lc ON p.costcenter = lc.id ";
    $concatsql = " WHERE ba.visible=1 ";
    if (is_siteadmin() || has_capability('local/costcenter:manage_multiorganizations', $systemcontext)) {
        if ($allcostcenter) {
            $concatsql .= " AND p.costcenter IN ($costcenterids) ";
        }
    } else if (has_capability('local/costcenter:manage_ownorganization', $systemcontext)) {
        $concatsql .= " AND p.costcenter = $USER->open_costcenterid ";
    } else if (has_capability('local/costcenter:manage_owndepartments', $systemcontext)) {
        $concatsql .= " AND p.costcenter = $USER->open_costcenterid AND p.department = $USER->open_departmentid ";
    } else if (has_capability('local/costcenter:manage_ownsubdepartments', $systemcontext)) {
        $concatsql .= " AND p.costcenter = $USER->open_costcenterid AND p.department = $USER->open_departmentid AND p.subdepartment = $USER->open_subdepartment";
    }

    // For "Global (search box)" filter.
    if (isset($filterdata->search_query) && trim($filterdata->search_query) != '') {
        $filtereddata = array_filter(explode(',', $filterdata->search_query));
        $searcharray = array();
        if (!empty($filtereddata)) {
            foreach ($filtereddata as $key => $value) {
                $searcharray[] = "ba.name LIKE '%".trim($value)."%' OR p.name LIKE '%".trim($value)."%' OR lc.fullname LIKE '%".trim($value)."%'";
            }
            $imploderequests = implode(' OR ', $searcharray);
            $concatsql .= " AND ($imploderequests)";
        }
    }

    // For "Organizations" filter.
    if (!empty($filterdata->organizations)) {
        $filteredcostcenter = array_filter(explode(',', $filterdata->organizations), 'is_numeric');
        if(!empty($filteredcostcenter)) {
            $costcenterarray = array();
            foreach($filteredcostcenter as $key => $value) {
                $costcenterarray[] = "lc.id = $value";
            }
            $costcenterimplode = implode(' OR ', $costcenterarray);
            $concatsql .= " AND ($costcenterimplode) ";
        }
    }

    // For "Program" filter.
    if (!empty($filterdata->program)) {
        $filteredprogram = array_filter(explode(',', $filterdata->program), 'is_numeric');
        if(!empty($filteredprogram)) {
            $programarray = array();
            foreach($filteredprogram as $key => $value) {
                $programarray[] = "p.id = $value";
            }
            $programimplode = implode(' OR ', $programarray);
            $concatsql .= " AND ($programimplode) ";
        }
    }

    // For "Batch" filter.
    if (!empty($filterdata->groups)) {
        $filteredbatch = array_filter(explode(',', $filterdata->groups), 'is_numeric');
        if(!empty($filteredbatch)) {
            $batcharray = array();
            foreach($filteredbatch as $key => $value) {
                $batcharray[] = "ba.id = $value";
            }
            $batchimplode = implode(' OR ', $batcharray);
            $concatsql .= " AND ($batchimplode) ";
        }
    }

    $orderby = " ORDER BY p.id DESC ";
    $countsql = $DB->get_records_sql($selectsql.$fromsql.$concatsql);
    $blist = $DB->get_records_sql($selectsql.$fromsql.$concatsql.$orderby, $params, $stable->start, $stable->length);

    $count = count($countsql);
    $alldata = array();
    $j = 0;
    foreach ($blist as $list) {
        $line = array();

        $schoolinfo = $DB->get_record('local_costcenter', array('id' => $list->costcenterid, 'visible' => 1));
        $programinfo = $DB->get_record('local_program', array('batchid' => $list->cohortid));

        if ($j > 0) {
            $displaynone = "display: none;";
            $dyclass = "fa-plus-square-o";
        } else {
            $displaynone = "";
            $dyclass = "fa-minus-square-o";
        }

        $line['id'] = $list->id;
        $line['organisations'] = $schoolinfo->fullname;
        $line['programname'] = $programinfo->name;
        $line['batchname'] = $list->name;
        $line['class'] = 'tmcell';
        $line['displaynone'] = $displaynone;
        $line['dyclass'] = $dyclass;
        $line['configwwwroot'] = $CFG->wwwroot;
        $line['firstlevel'] = $lablestring->firstlevel;
        $line['programid'] = $list->programid;
        $line['data'] = toggle_timelayoutview($list->id);
        if (empty($line['data'])) {
            $line['emptydata'] = 0;
        } else {
            $line['emptydata'] = 1;
        }
        $alldata[] = $line;
        $j++;
    }

    $timetablecontent = [
        'hastimetable' => $alldata,
        'length' => $count,
        'count' => $count,
    ];

    return $timetablecontent;
}

function toggle_timelayoutview($batchid) {
    global $DB, $CFG, $OUTPUT, $USER, $PAGE;

    $systemcontext =  context_system::instance();
    $params = array('batchid' => $batchid);
    $sql = "SELECT lpl.*, lt.id as tlid
             FROM {local_program_levels} lpl
             JOIN {local_program} p ON lpl.programid = p.id
             JOIN {local_timeintervals} lt ON lt.semesterid = lpl.id
            WHERE p.batchid = :batchid";
    $orderby = " ORDER BY lpl.id ASC";
    $timelayoutlist = $DB->get_records_sql($sql.$orderby, $params);

    $data = array();
    $i = 0;
    foreach ($timelayoutlist as $key => $list) {
        $line = array();
        $programinfo = $DB->get_record('local_program', array('id' => $list->programid));
        $curriculuminfo = $DB->get_record('local_curriculum', array('id' => $programinfo->curriculumid, 'visible' => 1));

        $startdate = ($list->startdate?$list->startdate:$seminfo->startdate);
        $enddate = ($list->enddate?$list->enddate:$seminfo->enddate);

        if (date('d M Y',$startdate) == '01 Jan 1970' || $list->startdate == 0) {
            $semstartdate = 'N/A';
        } else {
            $semstartdate = date('d M Y', $startdate);
        }

        if (date('d M Y',$enddate) == '01 Jan 1970' || $list->enddate == 0) {
            $semenddate = 'N/A';
        } else {
            $semenddate = date('d M Y', $enddate);
        }

        $sessionexists = $DB->get_records('local_program_level_courses', ['levelid' => $list->id]);
        
        $semsession = $DB->get_record('local_schedulesessions', ['semesterid' => $list->id]);
        $exists = array();
        foreach ($sessionexists as $coursesexists) {
            $exists[] = $DB->record_exists('attendance_sessions', ['courseid' => $coursesexists->courseid]);
        }
        if (in_array(1, $exists) || $semsession) {
            $recordexist = true;
        } else {
            $recordexist = false;
        }

        $active = $DB->get_records('local_program_levels', ['id' => $list->id, 'active' => 1]);
        if ($active) {
            $active = true;
        } else {
            $active = false;
        }

        $line['id'] = $list->tlid;
        $line['contextid'] = $systemcontext->id;
        $line['semesterid'] = $list->id;
        $line['startdaterange'] = $semstartdate;
        $line['enddaterange'] = $semenddate;
        $line['curriculumname'] = $curriculuminfo->name;
        $line['levelname'] = $list->level;
        $line['sessionexists'] = $recordexist;
        $line['active'] = $active;

        $data[] = $line;
        $i++;
    }

    return $data;
}

/**
 * Implementation of user image rendering.
 *
 * @param password_icon $helpicon A help icon instance
 * @return string HTML fragment
 */
function password_icon(password_icon $helpicon) {
    global $OUTPUT;
    return $OUTPUT->render_from_template('attendance/attendance_password_icon', $helpicon->export_for_template($OUTPUT));
}

/**
 * Individual session view.
 */
function listof_individual_semester_session_timetable ($stable, $filterdata) {
    global $DB, $PAGE, $CFG, $USER;
    require_once($CFG->dirroot. '/mod/attendance/locallib.php');
    require_once($CFG->dirroot. '/course/classes/management_renderer.php');

    $params = array();
    $date = usergetdate(strtotime(date('d-m-Y')));
    $year = $date['year'];
    if ($filterdata->month > 0 || $filterdata->year > 0) {
        if (!empty($filterdata->month) && empty($filterdata->year)) {
            $sdate = make_timestamp($year, $filterdata->month, 1);
            $newsdate = date('t',$sdate);
            $edate = make_timestamp($year, $filterdata->month, $newsdate);
        }
        if (!empty($filterdata->year) && empty($filterdata->month)) {
            $sdate = make_timestamp($filterdata->year, 1);
            $edate = make_timestamp($filterdata->year, 12);
        }
        if (!empty($filterdata->year) && !empty($filterdata->month)) {
            $sdate = make_timestamp($filterdata->year, $filterdata->month, 1);
            $newsdate = date('t',$sdate);
            $edate = make_timestamp($filterdata->year, $filterdata->month, $newsdate);
        }
    }

    if ($filterdata->check > 0) {
        $sday = 'start_date[day]';
        $smonth = 'start_date[month]';
        $syear = 'start_date[year]';
        $reqsdate = make_timestamp($filterdata->$syear, $filterdata->$smonth, $filterdata->$sday);
        $eday = 'end_date[day]';
        $emonth = 'end_date[month]';
        $eyear = 'end_date[year]';
        $reqedate = make_timestamp($filterdata->$eyear, $filterdata->$emonth, $filterdata->$eday);
    } else {
        if ($filterdata->month > 0 || $filterdata->year > 0) {
            $reqsdate = $sdate;
            $reqedate = $edate;
        } else {
            $reqsdate = null;
            $reqedate = null;
        }
    }

    $data = $PAGE->get_renderer('course');
    $sql = "SELECT ats.*, lplc.levelid as semid, lpl.programid ";
    $fromsql = " FROM {attendance_sessions} ats
                 JOIN {local_program_level_courses} lplc ON ats.courseid = lplc.courseid
                 JOIN {local_program_levels} lpl ON lplc.levelid = lpl.id
                 JOIN {local_program} lp ON lpl.programid = lp.id ";
    $fromsql .= " WHERE 1=1 ";
    if ($stable->semid > 0) {
        $fromsql .= " AND lpl.id = {$stable->semid} ";
    }
    
    $role = identify_teacher_role($USER->id);
    if ($role->shortname == 'editingteacher') {
        if ($USER->id > 2) {
            $fromsql .= " AND ats.teacherid = {$USER->id}";
        }
    }

    if ($role->shortname == 'orgadmin') {
        $fromsql .= " AND lp.costcenter = {$USER->open_costcenterid}";
    }

    if ($role->shortname == 'collegeadmin') {
        $fromsql .= " AND lp.costcenter = {$USER->open_costcenterid}
                      AND lp.department = {$USER->open_departmentid}";
    }

    if ($filterdata->month > 0 || $filterdata->year > 0 || $filterdata->check > 0) {
        $fromsql .= " AND ( ats.sessdate BETWEEN '{$reqsdate}' AND '{$reqedate}')";
    }

    // For "Global (search box)" filter.
    if (isset($filterdata->search_query) && trim($filterdata->search_query) != '') {
        $filtereddata = array_filter(explode(',', $filterdata->search_query));
        $searcharray = array();
        if (!empty($filtereddata)) {
            foreach ($filtereddata as $key => $value) {
                $searcharray[] = "ats.sessionname LIKE '%".trim($value)."%' OR
                                  ats.building LIKE '%".trim($value)."%' OR
                                  ats.room LIKE '%".trim($value)."%' OR
                                  lp.name LIKE '%".trim($value)."%'";
            }
            $imploderequests = implode(' OR ', $searcharray);
            $fromsql .= " AND ($imploderequests)";
        }
    }

    // For teacher filter.
    if (isset($filterdata->teacher) && trim($filterdata->teacher) != '') {
        $emailfiltereddata = array_filter(explode(',', $filterdata->teacher), 'is_numeric');
        $emailarray = array();
        if (!empty($emailfiltereddata)) {
            foreach ($emailfiltereddata as $key => $value) {
                $emailarray[] = " ats.teacherid = $value";
            }
            $imploderequests = implode(' OR ', $emailarray);
            $fromsql .= " AND ($imploderequests)";
        }
    }

    $counts = $DB->get_records_sql($sql.$fromsql);
    $count = count($counts);

    $orderby = " ORDER BY ats.id DESC ";
    $sessions = $DB->get_records_sql($sql.$fromsql.$orderby, $params, $stable->start, $stable->length);

    $i = 0;
    $alldata = array();
    foreach ($sessions as $key => $sess) {
        $line = array();
        $i++;
        $programname = $DB->get_field('local_program', 'name', ['id' => $sess->programid]);
        $courseid = $DB->get_field('attendance_sessions', 'courseid', ['id' => $sess->id]);
        $cmidsql = "SELECT cm.id
                     FROM {course_modules} cm
                     JOIN {modules} m ON cm.module = m.id
                    WHERE cm.course = {$courseid} AND m.name = 'attendance' ";
        $cmid = $DB->get_field_sql($cmidsql);

        // Take attendance action.
        $icon = new password_icon($sess->studentpassword, $sess->id);
        if ($sess->includeqrcode == 1||$sess->rotateqrcode == 1) {
            $icon->includeqrcode = 1;
        } else {
            $icon->includeqrcode = 0;
        }
        $passicon = password_icon($icon);

        $sess->viewmode = 2;
        $params = array(
            'id' => $cmid,
            'sessionid' => $sess->id,
            'grouptype' => $sess->groupid,
            'viewmode' => $sess->viewmode
        );
        $url = new moodle_url('/mod/attendance/take.php', $params);
        $title = get_string('takeattendance', 'attendance');
        $takeactions = $data->action_icon($url, new pix_icon('t/go', $title));

        $teacher_role = $DB->get_record('user', array('id' => $sess->teacherid));
        $get_sessioname = $DB->get_field('attendance_sessions', 'sessionname', array('id' => $sess->id));

        if (!empty($get_sessioname)) {
            $get_sessioname = $get_sessioname;
        } else {
            $get_sessioname = 'N/A';
        }

        $date = strip_tags(userdate($sess->sessdate, get_string('strftimedmyw', 'attendance')));
        $time = attendance_construct_session_time($sess->sessdate, $sess->duration);

        $fullname = $teacher_role->firstname.' '.$teacher_role->lastname;

        $grouptype = get_string('commonsession', 'attendance');

        $userratings = $DB->get_record_sql("SELECT avg(rating) as avgrating
                                             FROM {ratings}
                                            WHERE sessionid = $sess->id"
                                        );
        if ($userratings->avgrating) {
            $avgrating = $userratings->avgrating;
            if (substr($avgrating, 2) == 0) {
                $avgrating = intval($avgrating);
                $overallrating = $avgrating.' / 5';
            } else {
                $avgrating = $avgrating;
                $overallrating = number_format($avgrating,1,".",".").' / 5';
            }
        } else {
            $overallrating = 'NA';
        }
        if ($sess->lasttakenby > 0) {
            $lasttaken = true;
        } else {
            $lasttaken = false;
        }
        
        $group = $DB->get_field('cohort', 'name', ['id' => $sess->batch_group]);

        if (empty($group)) {
            $group = 'All Students';
        }

        $building = $DB->get_field('local_location_institutes', 'fullname', ['id' => $sess->building]);
        $room = $DB->get_field('local_location_room', 'name', ['id' => $sess->room]);

        $active = $DB->get_records('local_program_levels', ['id' => $sess->semid, 'active' => 1]);
        if ($active) {
            $active = true;
        } else {
            $active = false;
        }

        $line['sr'] = $i;
        $line['semid'] = $sess->semid;
        $line['courseid'] = $sess->courseid;
        $line['cmid'] = $cmid;
        $line['sessionid'] = $sess->id;
        $line['grouptype'] = $sess->groupid;
        $line['viewmode'] = $sess->viewmode;
        $line['sessioname'] = $get_sessioname;
        $line['date'] = $date;
        $line['time'] = $time;
        $line['teacher'] = $fullname;
        $line['type'] = $grouptype;
        $line['description'] = $sess->description;
        $line['overallrating'] = $overallrating;
        $line['configwwwroot'] = $CFG->wwwroot;
        $line['taken'] = $lasttaken;
        $line['lasttakenby'] = $sess->lasttakenby;
        $line['building'] = $building;
        $line['room'] = $room;
        $line['programname'] = $programname;
        $line['batch_group'] = $sess->batch_group;
        $line['group_name'] = $group;
        $line['active'] = $active;
        $alldata[] = $line;
    }

    $indsessioncontent = [
        'hassession' => $alldata,
        'count' => $count,
        'length' => $count,
    ];

    return $indsessioncontent;
}

/**
 * [timetable_filter form element function]
 * @param  [form] $mform [filter form]
 * @return
 */
function month_filter($mform, $query='', $searchanywhere=false, $page=0, $perpage=25) {
    global $DB, $USER;

    $month = array(0 => get_string('select_month', 'local_timetable'),
        "January",
        "February",
        "March",
        "April",
        "May",
        "June",
        "July",
        "August",
        "September",
        "October",
        "November",
        "December"
    );

    $asort = array_combine(range(2050, 1970), range(2050, 1970));
    $asort[0] = get_string('select_year', 'local_timetable');
    asort($asort);

    $mform->addElement('checkbox', 'check', '', 'Enable range');

    $mform->addElement('html', '<div id="enable_check">');
    $mform->addElement('date_selector', 'start_date', get_string('start_date', 'local_timetable'));
    $mform->setType('start_date', PARAM_INT);
    $mform->disabledIf('start_date', 'check', 'notchecked');

    $mform->addElement('date_selector', 'end_date', get_string('end_date', 'local_timetable'));
    $mform->setType('end_date', PARAM_INT);
    $mform->disabledIf('end_date', 'check', 'notchecked');
    $mform->addElement('html', '</div>');

    $mform->addElement('select', 'month', get_string('select_month', 'local_timetable'), $month);
    $mform->setType('month', PARAM_INT);
    $mform->disabledIf('month', 'check', 'checked');

    $mform->addElement('select', 'year', get_string('select_year', 'local_timetable'), $asort);
    $mform->setType('month', PARAM_INT);
    $mform->disabledIf('year', 'check', 'checked');
}

/**
 * Description: User email filter code
 * @param  [mform object]  $mform[the form object where the form is initiated]
 * @param  string  $query          [text inserted in filter]
 * @param  boolean $searchanywhere [description]
 * @param  integer $page           [page value]
 * @param  integer $perpage        [entities per page]
 */
function teacher_filter($mform, $query='', $searchanywhere=false, $page=0, $perpage=25) {
    global $DB, $USER;
    $systemcontext = context_system::instance();
    $userslist = array();
    $data = data_submitted();
    $userslistparams = array('adminuserid' => 2, 'deleted' => 0, 'userid' => $USER->id, 'open_type' => 0, 'suspended' => 0);
    if (is_siteadmin()
        || has_capability('local/costcenter:manage_multiorganizations', $systemcontext)
        || has_capability('local/costcenter:manage_ownorganization', $systemcontext)
        || has_capability('local/costcenter:manage_owndepartments', $systemcontext)) {
        $userslist_sql= "SELECT u.id, CONCAT(u.firstname, ' ', u.lastname) as fullname
                        FROM {user} u
                        JOIN {role} r ON u.roleid = r.id
                        WHERE u.id > :adminuserid
                        AND u.deleted = :deleted
                        AND u.id <> :userid
                        AND u.open_type = :open_type 
                        AND u.suspended = :suspended
                        AND r.shortname = 'editingteacher' ";
    }
    if(!empty($query)){
        if ($searchanywhere) {
            $likesql = " u.firstname LIKE '%".trim($query)."%' OR
                                  u.lastname LIKE '%".trim($query)."%' ";
            $userslist_sql .= " AND ($likesql) ";
        } else {
            $likesql = $DB->sql_like('u.firstname', "'$query%'", false);
            $userslist_sql .= " AND ($likesql) ";
        }
    }
    
    if (!empty($query) || empty($mform)) {
        $userslist = $DB->get_records_sql($userslist_sql, $userslistparams, $page, $perpage);
        return $userslist;
    }
    $options = array(
        'ajax' => 'local_courses/form-options-selector',
        'multiple' => true,
        'data-action' => 'teacher',
        'data-options' => json_encode(array('id' => 0)),
        'placeholder' => get_string('teacher', 'local_timetable')
    );
    if (is_siteadmin()
        || has_capability('local/costcenter:manage_multiorganizations', $systemcontext)
        || has_capability('local/costcenter:manage_ownorganization', $systemcontext)
        || has_capability('local/costcenter:manage_owndepartments', $systemcontext)) {
        $mform->addElement('autocomplete', 'teacher', '', $userslist, $options);
        $mform->setType('teacher', PARAM_RAW);
    }
}

function semester_timetable_date_validation($formdata) {
    global $DB;

    $duration = $DB->get_record('local_timeintervals_slots', ['id' => $formdata->slotid]);
    $stime = explode(':', $duration->starttime);
    $etime = explode(':', $duration->endtime);

    $formdata->sestime = array(
        'starthour' => $stime[0],
        'startminute' => $stime[1],
        'endhour' => $etime[0],
        'endminute' => $etime[1]
    );
    if (!empty($formdata->dayname)) {
        $dayto_add = '';
        if ($formdata->sessiondate <= strtotime(date('d-m-Y'))) {
            $days = ['SU', 'M', 'TU', 'W', 'TH', 'F', 'SA'];
            $nextdays = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
            foreach ($days as $key => $value) {
                $sdate = usergetdate($formdata->sessiondate);
                if ($formdata->dayname == $value) {
                    if ($sdate['wday'] != $key) {
                        $dayto_add = strtotime('next '.$nextdays[$key].'', $formdata->sessiondate);
                    } else {
                        $dayto_add = $formdata->sessiondate;
                    }
                    $dinfo = usergetdate($dayto_add);
                    $sess->nextday = $nextdays[$key];
                }
            }
        } else if ($formdata->sessiondate > strtotime(date('d-m-Y'))) {
            $days = ['SU', 'M', 'TU', 'W', 'TH', 'F', 'SA'];
            $nextdays = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
            foreach ($days as $key => $value) {
                $sdate = usergetdate($formdata->sessiondate);
                if ($formdata->dayname == $value) {
                    if ($sdate['wday'] != $key) {
                        $dayto_add = strtotime('next '.$nextdays[$key].'', $formdata->sessiondate);
                    } else {
                        $dayto_add = $formdata->sessiondate;
                    }
                    $dinfo = usergetdate($dayto_add);
                    $sess->nextday = $nextdays[$key];
                }
            }
        }
        $sess->sessdate = make_timestamp(
            $dinfo['year'],
            $dinfo['mon'],
            $dinfo['mday'],
            $formdata->sestime['starthour'],
            $formdata->sestime['startminute']
        );
        $sess->enddate = make_timestamp(
            $dinfo['year'],
            $dinfo['mon'],
            $dinfo['mday'],
            $formdata->sestime['endhour'],
            $formdata->sestime['endminute']
        );
    } else {
        $sess->sessdate = make_timestamp(
            date("Y", $formdata->sessiondate),
            date("m", $formdata->sessiondate),
            date("d", $formdata->sessiondate),
            $formdata->sestime['starthour'],
            $formdata->sestime['startminute']
        );
        $sess->enddate = make_timestamp(
            date("Y", $formdata->sessiondate),
            date("m", $formdata->sessiondate),
            date("d", $formdata->sessiondate),
            $formdata->sestime['endhour'],
            $formdata->sestime['endminute']
        );
    }

    return $sess;
}

/**
 * Counts list of users enrolled into course (as per above function)
 *
 * @param context $context
 * @param string $withcapability
 * @param int $groupid 0 means ignore groups, any other value limits the result by group id
 * @param bool $onlyactive consider only active enrolments in enabled plugins and time restrictions
 * @return int number of users enrolled into course
 */
function count_enrolled_group_users(context $context, $withcapability = '', $groupid = 0, $onlyactive = false) {
    global $DB;
    $capjoin = get_enrolled_with_capabilities_join(
            $context, '', $withcapability, $groupid, $onlyactive);
    if ($groupid > 0) {
        $join = str_replace('groups_members', 'cohort_members', $capjoin->joins);
        $joins = str_replace('.groupid', '.cohortid', $join);
        $sql = "SELECT COUNT(DISTINCT u.id)
                  FROM {user} u
                $joins
                 WHERE $capjoin->wheres AND u.deleted = 0";

        $count =  $DB->count_records_sql($sql, $capjoin->params);
    } else {
        $sql = "SELECT COUNT(DISTINCT u.id)
                  FROM {user} u
                $capjoin->joins
                 WHERE $capjoin->wheres AND u.deleted = 0";

        $count = $DB->count_records_sql($sql, $capjoin->params);
    }
    return $count;
}

/**
 * Returns list of users enrolled into course.
 *
 * @param context $context
 * @param string $withcapability
 * @param int $groupid 0 means ignore groups, USERSWITHOUTGROUP without any group and any other value limits the result by group id
 * @param string $userfields requested user record fields
 * @param string $orderby
 * @param int $limitfrom return a subset of records, starting at this point (optional, required if $limitnum is set).
 * @param int $limitnum return a subset comprising this many records (optional, required if $limitfrom is set).
 * @param bool $onlyactive consider only active enrolments in enabled plugins and time restrictions
 * @return array of user records
 */
function get_group_enrolled_users(context $context, $withcapability = '', $groupid = 0, $userfields = 'u.*', $orderby = null,
        $limitfrom = 0, $limitnum = 0, $onlyactive = false) {
    global $DB;

    list($esql, $params) = get_enrolled_sql($context, $withcapability, $groupid, $onlyactive);

    $join = str_replace('groups_members', 'cohort_members', $esql);
    $joins = str_replace('.groupid', '.cohortid', $join);

    $sql = "SELECT $userfields
              FROM {user} u
              JOIN ($joins) je ON je.id = u.id
             WHERE u.deleted = 0";

    if ($orderby) {
        $sql = "$sql ORDER BY $orderby";
    }
    $user = $DB->get_records_sql($sql, $params, $limitfrom, $limitnum);

    return $user;
}

/**
 * Returns list of availabe slots.
 *
 * @param int $semid
 * @param int $calendarday
 * @return array of availabe slots records
 */
function get_listof_availabe_slots($semid, $calendarday) {
    global $DB;

    // Fetching all the time slots for semester.
    $slotssql = "SELECT DISTINCT(lts.id), lts.starttime, lts.endtime
                   FROM {local_timeintervals_slots} lts
                   JOIN {local_timeintervals} lt ON lts.timeintervalid = lt.id ";
    $wheresql = " WHERE lt.semesterid = ? ";
    $orderby = " ORDER BY lts.starttime";
    $semslots = $DB->get_records_sql($slotssql.$wheresql.$orderby, [$semid]);

    // Defining $stime array.
    $stime = [];

    // Defining $etime array.
    $etime = [];

    // Sepreating slots start-time and end-time from $semslots array.
    foreach ($semslots as $key => $value) {
        $stime[] = $value->starttime;
        $etime[] = $value->endtime;
    }

    // Checking selected calendar date by default current date.
    $caldate = !empty($calendarday) ? $calendarday : strtotime(date('d-m-Y'));

    // Defining $params array.
    $params = [];
    $params['semid'] = $semid;

    // Adding string into array.
    $intervalslots = array(null => get_string('select_duration', 'local_timetable'));

    $allstots = [];

    // Generating start-date as per timeslots.
    foreach($stime as $sdate) {
        $stimeslots = explode(':', $sdate);
        $startdate = $caldate + $stimeslots[0] * HOURSECS + $stimeslots[1] * MINSECS;
        $allstots['s'][] = $startdate;
    }

    // Generating end-date as per timeslots.
    foreach($etime as $edate) {
        $etimeslots = explode(':', $edate);
        $enddate = $caldate + $etimeslots[0] * HOURSECS + $etimeslots[1] * MINSECS;
        $allstots['e'][] = $enddate;
    }

    $newArray = [];

    foreach ($allstots['s'] as $skey => $svalue) {
        $newArray[] = ['s' => $svalue, 'e' => $allstots['e'][$skey]];
    }

    // Identifying slots are booked in current date or not.
    foreach ($newArray as $slotkey => $slotvalue) {
        $intervalsql = " JOIN {attendance_sessions} ats ON lts.id = ats.slotid
                         WHERE lt.semesterid = :semid ";
        if (!empty($caldate)) {
            $params['calendardate'] = $sdate;
            $intervalsql .= " AND ats.sessdate = '{$slotvalue['s']}' AND ats.sessedate = '{$slotvalue['e']}'";
        }
        $timeintervals[] = $DB->get_record_sql($slotssql.$intervalsql, $params);

    }

    // Extract ids from timeintervals array.
    $idsArray1 = array_map(function ($item) {
        return $item->id;
    }, $timeintervals);

    // Filter semslots based on ids from timeintervals array.
    $resultArray = array_filter($semslots, function ($item) use ($idsArray1) {
        return !in_array($item->id, $idsArray1);
    });

    foreach ($resultArray as $key => $value) {
        $sslottime = substr_replace($value->starttime, '', 5);
        $eslotetime = substr_replace($value->endtime, '', 5);
        $intervalslots[$key] = $sslottime. ' - ' .$eslotetime;
    }

    return $intervalslots;
}
