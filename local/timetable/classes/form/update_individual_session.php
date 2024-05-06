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


use moodleform;
use context_system;
use mod_attendance_structure;
use DateTime;
use DateInterval;
use DatePeriod;
use core_component;
/**
 * class for displaying update session form.
 *
 * @copyright  2011 Artem Andreev <andreev.artem@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/local/timetable/lib.php');
require_once($CFG->dirroot . '/mod/attendance/locallib.php');
class update_individual_session extends \moodleform {

    /**
     * Called to define this moodle form
     *
     * @return void
     */
    public function definition() {
        global $CFG, $DB, $COURSE, $USER, $PAGE;
        $systemcontext = context_system::instance(); 
        $mform    =& $this->_form;
        $class = 'timetableclass';

        $id            = $this->_customdata['id'];
        $action        = $this->_customdata['action'];
        $modcontext    = $this->_customdata['modcontext'];
        $course        = $this->_customdata['courseid'];
        $sessionid     = $this->_customdata['sessionid'];
        $semesterid    = $this->_customdata['semesterid'];
        $cm            = $this->_customdata['cm'];
        $building      = $this->_customdata['building'];
        $systemcontext = context_system::instance();
        $calendardate  = $_REQUEST['sessiondate'];
        if (is_siteadmin() || 
                has_capability('local/costcenter:manage_multiorganizations', $systemcontext) || 
                has_capability('local/costcenter:manage_ownorganization', $systemcontext) || 
                has_capability('local/costcenter:manage_owndepartments', $systemcontext)) {
            $semesterid    = $this->_customdata['semesterid'];
        } else {
            $semesterid    = $DB->get_field('local_program_level_courses', 'levelid', ['courseid' => $course]);
        }

        $calendarday = strtotime($calendardate['day'].'-'.$calendardate['month'].'-'.$calendardate['year']);

        $PAGE->requires->yui_module('moodle-local_timetable-timetablelayout', 'M.local_timetable.init_timetablelayout', array(array('formid' => $mform->getAttribute('id'))));

        $mform->addElement('hidden', 'id', $id);
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'semid', $semesterid);
        $mform->setType('semid', PARAM_INT);

        if (!$sess = $DB->get_record('attendance_sessions', array('id' => $sessionid) )) {
            error('No such session in this course');
        }
        // $attendancesubnet = $DB->get_field('attendance', 'subnet', array('id' => $sess->attendanceid));
        // $defopts = array('maxfiles' => EDITOR_UNLIMITED_FILES, 'noclean' => true, 'context' => $modcontext);
        // $sess = file_prepare_standard_editor($sess, 'description', $defopts, $modcontext, 'mod_attendance', 'session', $sess->id);

        $starttime = $sess->sessdate - usergetmidnight($sess->sessdate);
        $starthour = floor($starttime / HOURSECS);
        $startminute = floor(($starttime - $starthour * HOURSECS) / MINSECS);

        $enddate = $sess->sessdate + $sess->duration;
        $endtime = $enddate - usergetmidnight($enddate);
        $endhour = floor($endtime / HOURSECS);
        $endminute = floor(($endtime - $endhour * HOURSECS) / MINSECS);

        $coursename = get_course($sess->courseid);

        if ($sess->subnet == $attendancesubnet) {
            $data['usedefaultsubnet'] = 1;
        } else {
            $data['usedefaultsubnet'] = 0;
        }

        $mform->addElement('static', 'course', get_string('course', 'attendance'), $coursename->fullname);
        $mform->addElement('hidden', 'courseid', $sess->courseid);

        if ($sess->batch_group == 0) {
            $strtype = get_string('commonsession', 'attendance');
        } else {
            $groupname = $DB->get_field('cohort', 'name', array('id' => $sess->batch_group));
            $strtype = $groupname;
        }
        $mform->addElement('static', 'sessiontypedescription', get_string('group', 'local_timetable'), $strtype);

        $mform->addElement('hidden', 'batch_group', $sess->batch_group);
        $mform->setType('batch_group', PARAM_INT);

        $olddate = construct_session_full_date_time($sess->sessdate, $sess->duration);

        $mform->addElement('static', 'olddate', get_string('olddate', 'attendance'), $olddate);

        if (is_siteadmin()) {
            $pid = $DB->get_field('local_program_levels', 'programid', ['id' => $semesterid]);
            $costid = $DB->get_field('local_program', 'costcenter', ['id' => $pid]);
            $select = [null => 'Select'];
            $sessiontype = $select + $DB->get_records_menu('local_session_type', array('organization' => $costid), '', $fields = 'id, session_type');
            $mform->addElement('select', 'session_type', get_string('session_type', 'local_timetable'), $sessiontype);
            $mform->setType('session_type', PARAM_INT);
        } else {
            $pid = $DB->get_field('local_program_level_courses', 'programid', ['courseid' => $course]);
            $costid = $DB->get_field('local_program', 'costcenter', ['id' => $pid]);
            $select = [null => 'Select'];
            $sessiontype = $select + $DB->get_records_menu('local_session_type', array('organization' => $costid), '', $fields = 'id, session_type');
            $mform->addElement('select', 'session_type', get_string('session_type', 'local_timetable'), $sessiontype);
            $mform->setType('session_type', PARAM_INT);
        }

        $mform->addElement('date_selector', 'sessiondate', get_string('sessiondate', 'attendance'));

        $mform->registerNoSubmitButton('updatecourseformat');
        $mform->addElement('submit', 'updatecourseformat', get_string('updatedata', 'local_timetable'));

        $caldate = !empty($calendarday) ? $calendarday : strtotime(date('d-m-Y' ,$sess->sessdate));

        // Fetching timeinterval slots.
        $intervalslots = get_listof_availabe_slots($semesterid, $caldate);

        $slottime = $DB->get_record('local_timeintervals_slots', array('id' => $sess->slotid));
        $realstime = substr_replace($slottime->starttime, '', 5);
        $realetime = substr_replace($slottime->endtime, '', 5);       
        $realtime = $realstime.' - '.$realetime;
        $arr = array($slottime->id => $realtime);
        $arr = array_unique($arr+$intervalslots);

        $select = $mform->addElement('autocomplete', 'timeslots', get_string('duration', 'local_timetable'), $arr);
        $mform->addRule('timeslots', get_string('timeslots', 'local_timetable'), 'required', null, 'client');
        $mform->setType('timeslots', PARAM_INT);

        $role = array();
        $role[null] = get_string('selectrole','attendance');

        $systemroles = $DB->get_records_sql("SELECT u.* FROM {user} u
                JOIN {user_enrolments} ue ON ue.userid = u.id
                JOIN {enrol} e ON e.id = ue.enrolid
                WHERE (open_type = 0 AND deleted = 0) AND e.courseid = $sess->courseid");
        foreach($systemroles as $key =>$systemrole){
            $role[$key] = $systemrole->firstname.' '.$systemrole->lastname;
        }

        if (array_key_exists($sess->teacherid, $role)) {
            $existskey[$sess->teacherid] = $role[$sess->teacherid];
        }

        if(is_siteadmin() || 
                has_capability('local/costcenter:manage_multiorganizations', $systemcontext) || 
                has_capability('local/costcenter:manage_ownorganization', $systemcontext) || 
                has_capability('local/costcenter:manage_owndepartments', $systemcontext)){
            if(empty($existskey)){
                $mform->addElement('autocomplete', 'teacherid', get_string('employee', 'attendance'), $role);
                $mform->setType('teacherid', PARAM_INT);
                $mform->addRule('teacherid', get_string('select_teacher', 'attendance'), 'required', null, 'client');
            } else{
                $mform->addElement('autocomplete', 'teacherid', get_string('employee', 'attendance'), $existskey+$role);
                $mform->setType('teacherid', PARAM_INT);
                $mform->addRule('teacherid', get_string('select_teacher', 'attendance'), 'required', null, 'client');
            }
        } else{
            if(empty($existskey)){
                $mform->addElement('hidden', 'teacherid', get_string('employee', 'attendance'), $role);
                $mform->setType('teacherid', PARAM_INT);
                $mform->addRule('teacherid', get_string('select_teacher', 'attendance'), 'required', null, 'client');
                $mform->setDefault('teacherid',$USER->id);
            } else{
                $mform->addElement('hidden', 'teacherid', get_string('employee', 'attendance'), $existskey+$role);
                $mform->setType('teacherid', PARAM_INT);
                $mform->addRule('teacherid', get_string('select_teacher', 'attendance'), 'required', null, 'client');
                $mform->setDefault('teacherid',$USER->id);
            }
        }

        // Show which status set is in use.
        $maxstatusset = attendance_get_max_statusset($this->_customdata['att']->id);
        if ($maxstatusset > 0) {
            $mform->addElement('static', 'statussetstring', get_string('usestatusset', 'mod_attendance'),
                attendance_get_setname($this->_customdata['att']->id, $sess->statusset));
        }
        $mform->addElement('hidden', 'statusset', $sess->statusset);
        $mform->setType('statusset', PARAM_INT);

         $mform->addElement('text', 'sessionname', get_string('sessionname', 'local_timetable'), array('maxlength' => '50'));
        $mform->addRule('sessionname', get_string('sessionname', 'attendance'), 'required', null, 'client');

        if ($building) {
            $building = $building;
        } else {
            $building = $sess->building;
        }

        $locationselect = [0 => get_string('select_building', 'local_timetable')];
        $location = $locationselect + $DB->get_records_menu('local_location_institutes', array('id' => $building), '', $fields = 'id, fullname');
        $locationoptions = array(
            'ajax' => 'local_costcenter/form-options-selector',
            'data-selectstring' => get_string('select_building', 'local_timetable'),
            'data-contextid' => $systemcontext->id,
            'data-action' => 'costcenter_building_selector',
            'data-options' => json_encode(array('id' => 0, 'costcenterid' => $costid)),
            'class' => 'locationselect',
            'data-class' => 'locationselect',
            'multiple' => false,
            'data-pluginclass' => $class,
            'onchange' => '(function(e){ require("local_costcenter/newcostcenter").changeElement(event) })(event)',
        );

        $mform->addElement('autocomplete', 'building', get_string('building', 'local_timetable'), $location, $locationoptions);

        $roomselect = [0 => get_string('select_room', 'local_timetable')];
        $room = $roomselect + $DB->get_records_menu('local_location_room', array('id' => $sess->room), '', $fields = 'id, name');
        $roomoptions = array(
            'ajax' => 'local_costcenter/form-options-selector',
            'data-selectstring' => get_string('select_room', 'local_timetable'),
            'data-contextid' => $systemcontext->id,
            'data-action' => 'costcenter_room_selector',
            'data-options' => json_encode(array('id' => 0)),
            'class' => 'roomselect',
            'data-parentclass' => 'locationselect',
            'data-class' => 'roomselect',
            'multiple' => false,
            'data-pluginclass' => $class,
            'onchange' => '(function(e){ require("local_costcenter/newcostcenter").changeElement(event) })(event)',
        );

        $mform->addElement('autocomplete', 'room', get_string('room', 'local_timetable'), $room, $roomoptions);

        $mform->addElement('textarea', 'description', get_string('description', 'attendance'), array('maxlength' => 50) );
        $mform->setType('description', PARAM_RAW);

        if(is_siteadmin() || 
                has_capability('local/costcenter:manage_multiorganizations', $systemcontext) || 
                has_capability('local/costcenter:manage_ownorganization', $systemcontext) || 
                has_capability('local/costcenter:manage_owndepartments', $systemcontext)){

            if (!empty(get_config('attendance', 'enablecalendar'))) {
                $mform->addElement('checkbox', 'calendarevent', '', get_string('calendarevent', 'attendance'));
                $mform->addHelpButton('calendarevent', 'calendarevent', 'attendance');
            } else {
                $mform->addElement('hidden', 'calendarevent', 0);
                $mform->setType('calendarevent', PARAM_INT);
            }

            // If warnings allow selector for reporting.
            if (!empty(get_config('attendance', 'enablewarnings'))) {
                $mform->addElement('checkbox', 'absenteereport', '', get_string('includeabsentee', 'attendance'));
                $mform->addHelpButton('absenteereport', 'includeabsentee', 'attendance');
            }

            // Students can mark own attendance.
            $studentscanmark = get_config('attendance', 'studentscanmark');
            if (!empty($studentscanmark)) {

                $mform->addElement('hidden', 'studentscanmark');
                $mform->setDefault('studentscanmark', $studentscanmark);

                $mform->addElement('checkbox', 'includeqrcode', '', get_string('includeqrcode', 'attendance'));
                $mform->setDefault('includeqrcode', $studentscanmark);

                $mform->addElement('checkbox', 'rotateqrcode', '', get_string('rotateqrcode', 'attendance'));
                $mform->setDefault('rotateqrcode', $studentscanmark);

                $mform->addElement('checkbox', 'autoassignstatus', '', get_string('autoassignstatus', 'attendance'));
                $mform->addHelpButton('autoassignstatus', 'autoassignstatus', 'attendance');
                $mform->setDefault('autoassignstatus', $studentscanmark);
            }
            $mgroup = array();
            $mgroup[] = & $mform->createElement('text', 'subnet', get_string('requiresubnet', 'attendance'));
            $mform->setDefault('subnet', $this->_customdata['att']->subnet);
            $mgroup[] = & $mform->createElement('checkbox', 'usedefaultsubnet', get_string('usedefaultsubnet', 'attendance'));
            $mform->setDefault('usedefaultsubnet', 1);
            $mform->setType('subnet', PARAM_TEXT);

            $mform->addGroup($mgroup, 'subnetgrp', get_string('requiresubnet', 'attendance'), array(' '), false);
            $mform->setAdvanced('subnetgrp');
            $mform->addHelpButton('subnetgrp', 'requiresubnet', 'attendance');
            $mform->hideif('subnet', 'usedefaultsubnet', 'checked');

            $mform->addElement('hidden', 'automarkcompleted', '0');
            $mform->settype('automarkcompleted', PARAM_INT);

            $mgroup3 = array();
            $options = attendance_get_sharedipoptions();
            $mgroup3[] = & $mform->createElement('select', 'preventsharedip',
                get_string('preventsharedip', 'attendance'), $options);
            $mgroup3[] = & $mform->createElement('text', 'preventsharediptime',
                get_string('preventsharediptime', 'attendance'), '', 'test');
            $mform->addGroup($mgroup3, 'preventsharedgroup',
                get_string('preventsharedip', 'attendance'), array(' '), false);
            $mform->addHelpButton('preventsharedgroup', 'preventsharedip', 'attendance');
            $mform->setAdvanced('preventsharedgroup');
            $mform->setType('preventsharediptime', PARAM_INT);
        }

        $mform->addElement('hidden', 'action', 2);
        $mform->settype('action', PARAM_INT);

        $mform->addElement('hidden', 'semesterid', $semesterid);
        $mform->settype('semesterid', PARAM_INT);

        $mform->addElement('hidden', 'courseid', $courseid);
        $mform->settype('courseid', PARAM_INT);

        $mform->addElement('hidden', 'sessionid', $sessionid);
        $mform->settype('sessionid', PARAM_INT);

        $role = identify_teacher_role($USER->id);
        if ($role->shortname == 'editingteacher') {
            $semesterid = 0;
        }

        $cancel = '<a id="id_cancel" class="btn btn-secondary" name="cancel" href='.$CFG->wwwroot .'/local/timetable/individual_session.php?tlid='.$semesterid.'>Cancel</a>';
        $buttonarray[] = &$mform->createElement('submit', 'submitbutton', 'Add');
        $buttonarray[] = &$mform->createElement('button', 'cancelbutton',$cancel);
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');
    }

    /**
     * Perform minimal validation on the settings form
     * @param array $data
     * @param array $files
     */
    public function validation($data, $files) {
        global $DB, $USER;

        $sessionid     = $this->_customdata['sessionid'];
        $modcontext    = $this->_customdata['modcontext'];
        $course        = $this->_customdata['course'];
        $cm            = $this->_customdata['cm'];
        $errors = parent::validation($data, $files);

        if ($data['id'] > 0 && $data['timeslots'] > 0) {
            $slotstimes = $DB->get_record('local_timeintervals_slots', ['id' => $data['timeslots']]);
        }
        $stime = explode(':', $slotstimes->starttime);
        $etime = explode(':', $slotstimes->endtime);
        $data['sestime'] = array(
            'starthour' => $stime[0],
            'startminute' => $stime[1],
            'endhour' => $etime[0],
            'endminute' => $etime[1]
        );

        $sesstarttime = $data['sestime']['starthour'] * HOURSECS + $data['sestime']['startminute'] * MINSECS;
        $sesendtime = $data['sestime']['endhour'] * HOURSECS + $data['sestime']['endminute'] * MINSECS;
        if ($sesendtime < $sesstarttime) {
            $errors['sestime'] = get_string('invalidsessionendtime', 'attendance');
        }

        $sessstart = $data['sessiondate'] + $sesstarttime;
        $sessend = $data['sessiondate'] + $sesendtime;
        $existedsessionsql = "SELECT ats.attendanceid, ats.sessdate, ats.duration
                                FROM {attendance_sessions} ats
                                JOIN {attendance} att ON ats.attendanceid = att.id
                                WHERE att.course = :course AND ats.attendanceid = :attendance
                                AND ats.sessdate = :sessionstart AND ats.id != :sessid";
        $existedsession = $DB->record_exists_sql($existedsessionsql, array('course' => $cm->course, 'attendance' => $cm->instance, 'sessionstart' => $sessstart, 'sessid' => $sessionid));

        if ($existedsession) {
            $errors['sestime'] = get_string('alreadyexisted', 'attendance');
        }
        $courseexists = $DB->record_exists('local_program_level_courses', array('courseid' => $data['courseid']));
        if ($courseexists) {
            $courselevel = $DB->get_field('local_program_level_courses', 'levelid', array('courseid' => $data['courseid']));
            $semrecord = $DB->get_record('local_program_levels', array('id' => $courselevel));
            if ($semrecord->startdate > 0 && $semrecord->startdate != null) {
                if ($data['sessiondate'] < $semrecord->startdate) {
                    $semstartdate = date('d-M-Y', $semrecord->startdate);
                    $errors['sessiondate'] = get_string('semesterstartson', 'attendance', $semstartdate);
                }
            }
            if ($semrecord->enddate > 0 && $semrecord->enddate != null) {
                if ($data['sessiondate'] > $semrecord->enddate) {
                    $semenddate = date('d-M-Y', $semrecord->enddate);
                    $errors['sessiondate'] = get_string('semesterendson', 'attendance', $semenddate);
                }
            }
        }

        // Teacher perticular slot exists.
        $teacher_slot_exists = $DB->get_record('attendance_sessions', ['teacherid' => $data['teacherid'], 'slotid' => $data['timeslots'], 'sessdate' => $sessstart]);
        if ($teacher_slot_exists) {
            $teacherdata = $DB->get_record('user', ['id' => $teacher_slot_exists->teacherid]);

            $alreadyused = new \stdClass();
            $alreadyused->time = date('d-m-Y H:i', $teacher_slot_exists->sessdate).'-'.date('H:i', $teacher_slot_exists->sessedate);
            $alreadyused->course = $DB->get_field('course', 'fullname', ['id' => $teacher_slot_exists->courseid]);
            $alreadyused->teacher = fullname($teacherdata);
        }

        $role = identify_teacher_role($USER->id);
        if ($role->shortname == 'editingteacher') {
            $string = get_string('slot_already_used_teacher', 'local_timetable', $alreadyused);
        } else {
            $string = get_string('slot_already_used', 'local_timetable', $alreadyused);
        }

        if ($teacher_slot_exists && $teacher_slot_exists->sessdate == $sessstart && $teacher_slot_exists->id != $data['id']) {
            $errors['timeslots'] = $string;
        }

        // Slot exists.
        $dparams = array();
        $attsql = "SELECT * ";
        $fromsql = "FROM {attendance_sessions} ";
        $fromsql .= "WHERE 1=1 ";
        $fromsql .= "AND (('{$sessstart}' BETWEEN sessdate AND sessedate) OR ('{$sessend}' BETWEEN sessdate AND sessedate )) ";
        $slotsql = $DB->get_records_sql($attsql.$fromsql, $dparams);
        foreach ($slotsql as $value) {
            $exists_course_p_id = $DB->get_record('local_program_level_courses', ['courseid' => $value->courseid]);
            $course_p_id = $DB->get_record('local_program_level_courses', ['courseid' => $data['courseid']]);

            if ($value->id != $data['id']) {
                if ($exists_course_p_id->programid == $course_p_id->programid && ($sessstart <= $value->sessdate || $sessend <= $value->sessedate)) {
                    $time = date('d-m-Y H:i', $sessstart).'-'.date('H:i', $sessend);
                    $coursename = get_course($value->courseid);
                    $alreadyused->course = $coursename->fullname;
                    $alreadyused->date = $time;
                    $errors['timeslots'] = get_string('already_used', 'local_timetable', $alreadyused);
                }
            }
        }

        // Teacher exists for perticular duration.
        $dparams['teacherid'] = $data['teacherid'];
        $fromsql .= "AND teacherid = :teacherid ";
        $teacher_exists = $DB->get_records_sql($attsql.$fromsql, $dparams);
        foreach ($teacher_exists as $key => $tvalue) {
            $teacherdetails = $DB->get_record('user', ['id' => $data['teacherid']]);
            $alreadyused->teacher_fullname = fullname($teacherdetails);
            $alreadyused->date = date('d-m-Y H:i', $tvalue->sessdate).'-'.date('H:i', $tvalue->sessedate);

            if ($tvalue->id != $data['id']) {
                if (($sessend > $tvalue->sessdate || $sessend > $tvalue->sessedate) || ($tvalue->sessdate == $sessstart && $tvalue->sessedate == $sessend)) {
                    $errors['teacherid'] = get_string('teacher_already_exists', 'local_timetable', $alreadyused);
                }
            }
        }

        if ($data['room'][0]) {
            $data['room'] = $data['room'][0];
        } else {
            $data['room'] = $data['room'];
        }

        // Building and room exists for perticular duration.
        $brsql = "SELECT *
                    FROM {attendance_sessions}
                   WHERE building = ? AND room = ? AND (('{$sessstart}' BETWEEN sessdate AND sessedate) OR ('{$sessend}' BETWEEN sessdate AND sessedate ))";
        $brexists = $DB->get_record_sql($brsql, [ $data['building'], $data['room'] ]);
        if ($brexists && $brexists->id != $data['id']) {
            $alreadyused->date = date('d-m-Y H:i', $brexists->sessdate).'-'.date('H:i', $brexists->sessedate);
            if ($data['building']) {
                $alreadyused->buildingname = $DB->get_field('local_location_institutes', 'fullname', ['id' => $data['building']]);
                $errors['building'] = get_string('building_exists', 'local_timetable', $alreadyused);
            }
            if ($data['room']) {
                $alreadyused->roomname = $DB->get_field('local_location_room', 'name', ['id' => $data['room']]);
                $errors['room'] = get_string('room_exists', 'local_timetable', $alreadyused);
            }
        }

        // Examination is exists for perticular duration.
        $examsql = "SELECT ats.id, ats.slotid, ats.sessdate, ats.sessedate
                     FROM {local_session_type} lst
                     JOIN {attendance_sessions} ats ON lst.id = ats.session_type
                    WHERE lst.session_type = ? AND (ats.sessdate BETWEEN '{$sessstart}' AND '{$sessend}') ";
        $exam_schedule = $DB->get_record_sql($examsql, ['Examination']);
        $time = date('d-m-Y H:i', $exam_schedule->sessdate).'-'.date('H:i', $exam_schedule->sessedate);

        if (!empty($exam_schedule) && $exam_schedule->id != $data['id']) {
            $errors['timeslots'] = get_string('occupied_for_exam', 'local_timetable', $time);
        }

        $br_sql = $DB->record_exists('local_location_room', ['instituteid' => $data['building'], 'id' => $data['room']]);
        if (!$br_sql && $data['building'] > 0 && $data['room'] > 0) {
            $errors['room'] = get_string('invalidroom', 'local_timetable');
        }

        if (strlen(trim($data['sessionname'])) == 0 && !empty($data['sessionname'])) {
            $errors['sessionname'] = get_string('blankspaces','local_location');
        }

        return $errors;
    }
}
