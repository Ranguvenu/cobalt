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
 * This file contains the forms to add session.
 *
 * @package   local_timetable
 * @copyright  2011 Artem Andreev <andreev.artem@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use moodleform;
use context_system;
use mod_attendance_structure;
use DateTime;
use DateInterval;
use DatePeriod;
use core_component;
/**
 * class for displaying add form.
 *
 * @copyright  2011 Artem Andreev <andreev.artem@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/local/timetable/lib.php');
require_once($CFG->dirroot . '/mod/attendance/locallib.php');
class individual_session extends moodleform {
    /**
     * Called to define this moodle form
     *
     * @return void
     */
    public function definition() {

        global $CFG, $USER, $DB, $PAGE;
        $systemcontext =  context_system::instance();
        $mform    =& $this->_form;
        $class = 'timetableclass';
        $cid = $_REQUEST['coursesid'];
        $tid = $_REQUEST['teacherid'];
        $buildingid = $_REQUEST['building'];
        $roomid = $_REQUEST['room'];
        $calendardate = $_REQUEST['sessiondate'];
        if (is_siteadmin()
            || has_capability('local/costcenter:manage_multiorganizations', $systemcontext)
            || has_capability('local/costcenter:manage_ownorganization', $systemcontext)
            || has_capability('local/costcenter:manage_owndepartments', $systemcontext)
            || has_capability('local/costcenter:manage_ownsubdepartments', $systemcontext)) {
            $semid    = $this->_customdata['semesterid'];
            $crsid    = $this->_customdata['coursid'];
        } else {
            $crsid  = $this->_customdata['coursid'];
            if ($cid) {
                $cid = $cid;
            } else {
                $cid = $crsid;
            }
            $semid    = $DB->get_field('local_program_level_courses', 'levelid', ['courseid' => $cid]);
        }

        $calendarday = strtotime($calendardate['day'].'-'.$calendardate['month'].'-'.$calendardate['year']);

        $PAGE->requires->yui_module('moodle-local_timetable-timetablelayout', 'M.local_timetable.init_timetablelayout', array(array('formid' => $mform->getAttribute('id'))));

        $batchsql = "SELECT c.id
                      FROM {cohort} c
                      JOIN {local_program} lp ON c.id = lp.batchid
                      JOIN {local_program_levels} lpl ON lp.id = lpl.programid
                     WHERE lpl.id = :semid";
        $batchid = $DB->get_field_sql($batchsql, ['semid' => $semid]);
        $batchgroups = $DB->get_records('local_sub_groups', ['parentid' => $batchid]);
        foreach ($batchgroups as $key => $grpvalue) {
            $grpdata[] = $DB->get_record('cohort', ['id' => $grpvalue->groupid]);
        }
        $grpname = [0 => get_string('commonsession', 'attendance')];
        foreach ($grpdata as $key => $value) {
            $grpname[$value->id] = $value->name;
        }

        $mform->addElement('autocomplete', 'batch_group', get_string('group', 'local_timetable'), $grpname);
        $mform->setType('batch_group', PARAM_INT);

        if (is_siteadmin()
            || has_capability('local/costcenter:manage_multiorganizations', $systemcontext)
            || has_capability('local/costcenter:manage_ownorganization', $systemcontext)
            || has_capability('local/costcenter:manage_owndepartments', $systemcontext)
            || has_capability('local/costcenter:manage_ownsubdepartments', $systemcontext)) {
            $select = [null => "Select Course"];
            $courses = $select + $DB->get_records_menu('course', array('id' => $cid), '', $fields = 'id, fullname');
            $courseoptions = array(
                'ajax' => 'local_costcenter/form-options-selector',
                'data-contextid' => $systemcontext->id,
                'data-action' => 'costcenter_courseid_selector',
                'data-options' => json_encode(array('id' => $semid)),
                'class' => 'timetablecourseselect',
                'data-class' => 'timetablecourse',
                'multiple' => false,
                'data-pluginclass' => $class,
                'onchange' => '(function(e){ require("local_costcenter/newcostcenter").changeElement(event) })(event)',
            );
            $mform->addElement('autocomplete', 'coursesid', get_string('courses', 'local_timetable'), $courses, $courseoptions);
            $mform->addRule('coursesid', get_string('courses', 'local_timetable'), 'required', null, 'client');
            $mform->setType('coursesid', PARAM_INT);
        } else {
            $select = [null => "Select Course"];
            $coursesql = "SELECT c.id, c.fullname
                            FROM {user} u
                            JOIN {role_assignments} ra ON ra.userid = u.id
                            JOIN {role} r ON r.id = ra.roleid 
                            AND r.shortname = 'editingteacher'
                            JOIN {context} ctx ON ctx.id = ra.contextid
                            JOIN {course} c ON c.id = ctx.instanceid
                            JOIN {local_program_level_courses} plc ON plc.courseid=c.id
                            JOIN {local_program_levels} pl ON pl.id = plc.levelid 
                            AND pl.programid = plc.programid
                            WHERE u.id = :userid";
            $allcourses = $select + $DB->get_records_sql_menu($coursesql, ['userid' => $USER->id]);
            if (!empty($crsid)) {
                $crse = get_course($crsid);
                $mform->addElement('static', 'crsid', get_string('courses', 'local_timetable'), $crse->fullname);
                $mform->addElement('hidden', 'coursesid', $crsid);
            } else {
                $mform->addElement('autocomplete', 'coursesid', get_string('courses', 'local_timetable'), $allcourses);
                $mform->addRule('coursesid', get_string('courses', 'local_timetable'), 'required', null, 'client');
                $mform->setType('coursesid', PARAM_INT);
            }
        }

        if (is_siteadmin()) {
            $pid = $DB->get_field('local_program_levels', 'programid', ['id' => $semid]);
            $costid = $DB->get_field('local_program', 'costcenter', ['id' => $pid]);
        } else {
            $pid = $DB->get_field('local_program_level_courses', 'programid', ['courseid' => $cid]);
            $costid = $DB->get_field('local_program', 'costcenter', ['id' => $pid]);
        }

        if (is_siteadmin()) {
            $select = [null => get_string('select', 'local_timetable')];
            $sessiontype = $select + $DB->get_records_menu('local_session_type', array('organization' => $costid), '', $fields = 'id, session_type');
            $mform->addElement('select', 'session_type', get_string('set_session_type', 'local_timetable'), $sessiontype);
            $mform->setType('session_type', PARAM_INT);
        } else {
            $select = [null => 'Select'];
            $sessiontype = $select + $DB->get_records_menu('local_session_type', array('organization' => $costid), '', $fields = 'id, session_type');
            $mform->addElement('select', 'session_type', get_string('set_session_type', 'local_timetable'), $sessiontype);
            $mform->setType('session_type', PARAM_INT);
        }

        $mform->addElement('date_selector', 'sessiondate', get_string('sessiondate', 'attendance'));

        $mform->registerNoSubmitButton('updatecourseformat');
        $mform->addElement('submit', 'updatecourseformat', get_string('updatedata', 'local_timetable'));

        // Fetching timeinterval slots.
        $intervalslots = get_listof_availabe_slots($semid, $calendarday);

        $select = $mform->addElement('autocomplete', 'timeslots', get_string('duration', 'local_timetable'), $intervalslots);
        $mform->addRule('timeslots', get_string('timeslots', 'local_timetable'), 'required', null, 'client');
        $mform->setType('timeslots', PARAM_INT);

        $teacherselect = [null => "Select Teacher"];
        $tsql = "SELECT id, CONCAT(firstname, ' ',lastname) as fullname
                  FROM {user}
                 WHERE id = :tid";
        $teacher = $teacherselect + $DB->get_records_sql_menu($tsql, ['tid' => $tid]);
        $teacheroptions = array(
            'ajax' => 'local_costcenter/form-options-selector',
            'data-selectstring' => get_string('selectteacher', 'local_timetable'),
            'data-contextid' => $systemcontext->id,
            'data-action' => 'costcenter_teacherid_selector',
            'data-options' => json_encode(array('courseid' => $cid)),
            'class' => 'timetableteacherselect',
            'data-parentclass' => 'timetablecourse',
            'data-class' => 'timetableteacherselect',
            'multiple' => false,
            'data-pluginclass' => $class,
            'onchange' => '(function(e){ require("local_costcenter/newcostcenter").changeElement(event) })(event)',
        );
        if(is_siteadmin() || 
                has_capability('local/costcenter:manage_multiorganizations', $systemcontext) || 
                has_capability('local/costcenter:manage_ownorganization', $systemcontext) || 
                has_capability('local/costcenter:manage_owndepartments', $systemcontext)){

            $mform->addElement('autocomplete', 'teacherid', get_string('employee', 'attendance'), $teacher, $teacheroptions);
            $mform->setType('teacherid', PARAM_INT);
            $mform->addRule('teacherid', get_string('select_teacher', 'attendance'), 'required', null, 'client');
        } else {
            $mform->addElement('hidden', 'teacherid', get_string('employee', 'attendance'));
            $mform->setType('teacherid', PARAM_INT);
            $mform->addRule('teacherid', get_string('select_teacher', 'attendance'), 'required', null, 'client');
            $mform->setDefault('teacherid', $USER->id);
        }

        $mform->addElement('text', 'sessionname', get_string('sessionname', 'local_timetable'), array('maxlength' => '50'));
        $mform->addRule('sessionname', get_string('reqsessionname', 'local_timetable'), 'required', null, 'client');

        $locationselect = [null => get_string('select_building', 'local_timetable')];
        $location = $locationselect + $DB->get_records_menu('local_location_institutes', array('id' => $buildingid), '', $fields = 'id, fullname');
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

        $roomselect = [null => get_string('select_room', 'local_timetable')];
        $room = $roomselect + $DB->get_records_menu('local_location_room', array('id' => $roomid), '', $fields = 'id, name');
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
    
        $pluginconfig = get_config('attendance');

        // Select which status set to use.
        $maxstatusset = attendance_get_max_statusset($this->_customdata['att']->id);
        if ($maxstatusset > 0) {
            $opts = array();
            for ($i = 0; $i <= $maxstatusset; $i++) {
                $opts[$i] = attendance_get_setname($this->_customdata['att']->id, $i);
            }
            $mform->addElement('select', 'statusset', get_string('usestatusset', 'mod_attendance'), $opts);
        } else {
            $mform->addElement('hidden', 'statusset', 0);
            $mform->setType('statusset', PARAM_INT);
        }

        $mform->addElement('textarea', 'description', get_string('description', 'attendance'), array('maxlength' => 50) );
        $mform->setType('description', PARAM_RAW);

        if(is_siteadmin() || 
                has_capability('local/costcenter:manage_multiorganizations', $systemcontext) || 
                has_capability('local/costcenter:manage_ownorganization', $systemcontext) || 
                has_capability('local/costcenter:manage_owndepartments', $systemcontext)){
            if (!empty($pluginconfig->enablecalendar)) {
                /*$mform->addElement('checkbox', 'calendarevent', '', get_string('calendarevent', 'attendance'));
                $mform->addHelpButton('calendarevent', 'calendarevent', 'attendance');
                if (isset($pluginconfig->calendarevent_default)) {
                    $mform->setDefault('calendarevent', $pluginconfig->calendarevent_default);
                }*/
                $mform->addElement('hidden', 'calendarevent');
                $mform->setType('calendarevent', PARAM_INT);
                $mform->setDefault('calendarevent', $pluginconfig->calendarevent_default);
            } else {
                $mform->addElement('hidden', 'calendarevent', 0);
                $mform->setType('calendarevent', PARAM_INT);
            }

            // If warnings allow selector for reporting.
            if (!empty(get_config('attendance', 'enablewarnings'))) {
                $mform->addElement('checkbox', 'absenteereport', '', get_string('includeabsentee', 'attendance'));
                $mform->addHelpButton('absenteereport', 'includeabsentee', 'attendance');
                if (isset($pluginconfig->absenteereport_default)) {
                    $mform->setDefault('absenteereport', $pluginconfig->absenteereport_default);
                }
            } else {
                $mform->addElement('hidden', 'absenteereport', 1);
                $mform->setType('absenteereport', PARAM_INT);
            }

            $mform->addElement('hidden', 'coursestartdate', $course->startdate);
            $mform->setType('coursestartdate', PARAM_INT);

            $mform->addElement('hidden', 'previoussessiondate', 0);
            $mform->setType('previoussessiondate', PARAM_INT);

            // Students can mark own attendance.
            $studentscanmark = get_config('attendance', 'studentscanmark');
            if (!empty($studentscanmark)) {
                // $mgroup = array();
                // $mform->addElement('checkbox', 'studentscanmark', '', get_string('studentscanmark', 'attendance'));
                $mform->addElement('hidden', 'studentscanmark');
                $mform->setDefault('studentscanmark', $studentscanmark);

                $mform->addElement('checkbox', 'includeqrcode', '', get_string('includeqrcode', 'attendance'));
                    $mform->setDefault('includeqrcode', $studentscanmark);
                $mform->addElement('checkbox', 'rotateqrcode', '', get_string('rotateqrcode', 'attendance'));
                    $mform->setDefault('rotateqrcode', $studentscanmark);
                $mform->addElement('checkbox', 'autoassignstatus', '', get_string('autoassignstatus', 'attendance'));
                    $mform->setDefault('autoassignstatus', $studentscanmark);
            }

            $mgroup2 = array();
            $mgroup2[] = & $mform->createElement('text', 'subnet', get_string('requiresubnet', 'attendance'));
            if (empty(get_config('attendance', 'subnetactivitylevel'))) {
                $mform->setDefault('subnet', get_config('attendance', 'subnet'));
            } else {
                $mform->setDefault('subnet', $this->_customdata['att']->subnet);
            }

            $mgroup2[] = & $mform->createElement('checkbox', 'usedefaultsubnet', get_string('usedefaultsubnet', 'attendance'));
            $mform->setDefault('usedefaultsubnet', 1);
            $mform->setType('subnet', PARAM_TEXT);

            $mform->addGroup($mgroup2, 'subnetgrp', get_string('requiresubnet', 'attendance'), array(' '), false);
            $mform->setAdvanced('subnetgrp');
            $mform->addHelpButton('subnetgrp', 'requiresubnet', 'attendance');
            $mform->hideif('subnet', 'usedefaultsubnet', 'checked');

            $mgroup3 = array();
            $options = attendance_get_sharedipoptions();
            $mgroup3[] = & $mform->createElement('select', 'preventsharedip',
                get_string('preventsharedip', 'attendance'), $options);
            $mgroup3[] = & $mform->createElement('text', 'preventsharediptime',
                get_string('preventsharediptime', 'attendance'), '', 'test');
            $mform->addGroup($mgroup3, 'preventsharedgroup', get_string('preventsharedip', 'attendance'), array(' '), false);
            $mform->addHelpButton('preventsharedgroup', 'preventsharedip', 'attendance');
            $mform->setAdvanced('preventsharedgroup');
            $mform->setType('preventsharedip', PARAM_INT);
            $mform->setType('preventsharediptime', PARAM_INT);
        }
        if (isset($pluginconfig->preventsharedip)) {
            $mform->setDefault('preventsharedip', $pluginconfig->preventsharedip);
        }
        if (isset($pluginconfig->preventsharediptime)) {
            $mform->setDefault('preventsharediptime', $pluginconfig->preventsharediptime);
        }

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'semid');
        $mform->setType('semid', PARAM_INT);
        $mform->setConstant('semid', $semid );

        $role = identify_teacher_role($USER->id);
        if ($role->shortname == 'editingteacher') {
            $semid = 0;
        } else {
            $semid = $semid;
        }

        $mform->disable_form_change_checker();

        $buttonarray = array();
        $cancel = '<a id="id_cancel" class="btn btn-secondary" name="cancel" href='.$CFG->wwwroot .'/local/timetable/individual_session.php?tlid='.$semid.'>Cancel</a>';
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
        global $DB;

        $courseids = $DB->get_records('local_program_level_courses', ['levelid' => $data['semid']]);
        if (array_key_exists($data['coursesid'], $courseids)) {
            $courseid = $courseids[$data['coursesid']];
        } else {
            $courseid = $data['coursesid'];
        }
        $moduleid = $DB->get_field('modules', 'id', ['name' => 'attendance']);
        $cm = $DB->get_record('course_modules', ['course' => $courseid, 'module' => $moduleid]);

        $errors = parent::validation($data, $files);

        $slotstimes = $DB->get_record('local_timeintervals_slots', ['id' => $data['timeslots']]);
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
            $errors['timeslots'] = get_string('invalidsessionendtime', 'attendance');
        }

        $sessstart = $data['sessiondate'] + $sesstarttime;
        $sessend = $data['sessiondate'] + $sesendtime;
        /*if ($sessstart < $data['coursestartdate'] && $sessstart != $data['previoussessiondate']) {
            $errors['sessiondate'] = get_string('priorto', 'attendance',
                userdate($data['coursestartdate'], get_string('strftimedmyhm', 'attendance')));
            $this->_form->setConstant('previoussessiondate', $sessstart);
        }*/

        /*if (!empty($data['studentscanmark']) && isset($data['automark'])
            && $data['automark'] == ATTENDANCE_AUTOMARK_CLOSE) {

            $cm            = $this->_customdata['cm'];
            // Check that the selected statusset has a status to use when unmarked.
            $sql = 'SELECT id
            FROM {attendance_statuses}
            WHERE deleted = 0 AND (attendanceid = 0 or attendanceid = ?)
            AND setnumber = ? AND setunmarked = 1';
            $params = array($cm->instance, $data['statusset']);
            if (!$DB->record_exists_sql($sql, $params)) {
                $errors['automark'] = get_string('noabsentstatusset', 'attendance');
            }
        }

        if (!empty($data['studentscanmark']) && !empty($data['preventsharedip']) &&
                empty($data['preventsharediptime'])) {
            $errors['preventsharedgroup'] = get_string('iptimemissing', 'attendance');

        }*/
        $existedsessionsql = "SELECT ats.attendanceid, ats.sessdate, ats.duration
                                FROM {attendance_sessions} ats
                                JOIN {local_schedulesessions} ls ON ats.id = ls.sessionid
                              WHERE ats.courseid = :course AND ats.attendanceid = :attendance
                                AND ats.sessdate = :sessionstart AND ls.semesterid = :sessid ";
        $existedsession = $DB->record_exists_sql($existedsessionsql, array('course' => $cm->course, 'attendance' => $cm->instance, 'sessionstart' => $sessstart, 'sessid' => $data['semid']));
        if ($existedsession) {
            $errors['timeslots'] = get_string('alreadyexisted', 'attendance');
        }
        $courseexists = $DB->record_exists('local_program_level_courses', array('levelid' => $data['semid']));
        if ($courseexists) {
            $semrecord = $DB->get_record('local_program_levels', array('id' => $data['semid']));
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
        if (empty($data['teacherid'])) {
            $errors['teacherid'] = get_string('teachererror', 'local_timetable');
        }
        if (empty($data['coursesid'])) {
            $errors['coursesid'] = get_string('courseerror', 'local_timetable');
        }
        if (empty($data['timeslots'])) {
            $errors['timeslots'] = get_string('timeslotserror', 'local_timetable');
        }
        
        $paramss = array();
        $paramss['sessiondate'] = $sessstart;
        $paramss['coursesid'] = $data['coursesid'];
        $paramss['timeslots'] = $data['timeslots'];
        $paramss['teacherid'] = $data['teacherid'];
        $sql = "SELECT id FROM {attendance_sessions} WHERE sessdate = :sessiondate AND courseid = :coursesid AND slotid = :timeslots AND teacherid = :teacherid";
        $session_exists = $DB->record_exists_sql($sql, $paramss);
        
        if($session_exists){
            $errors['timeslots'] = get_string('timeslotisexists', 'local_timetable');
        }

        $alreadyused = new \stdClass();

        // Teacher perticular slot exists.
        $teacher_slot_exists = $DB->get_record('attendance_sessions', ['teacherid' => $data['teacherid'], 'slotid' => $data['timeslots']]);
        if ($teacher_slot_exists) {
            $teacherdata = $DB->get_record('user', ['id' => $teacher_slot_exists->teacherid]);
            $alreadyused->time = date('d-m-Y H:i', $teacher_slot_exists->sessdate).'-'.date('H:i', $teacher_slot_exists->sessedate);
            $coursename = get_course($teacher_slot_exists->courseid);
            $alreadyused->course = $coursename->fullname;
            $alreadyused->teacher = fullname($teacherdata);
        }

        $role = identify_teacher_role($USER->id);
        if ($role->shortname == 'editingteacher') {
            $string = get_string('slot_already_used_teacher', 'local_timetable', $alreadyused);
        } else {
            $string = get_string('slot_already_used', 'local_timetable', $alreadyused);
        }

        if ($teacher_slot_exists && $teacher_slot_exists->sessdate == $sessstart) {
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
            $course_p_id = $DB->get_record('local_program_level_courses', ['courseid' => $data['coursesid']]);

            if ($exists_course_p_id->programid == $course_p_id->programid && ($sessstart <= $value->sessdate || $sessend <= $value->sessedate)) {
                $time = date('d-m-Y H:i', $sessstart).'-'.date('H:i', $sessend);
                $coursename = get_course($value->courseid);
                $alreadyused->course = $coursename->fullname;
                $alreadyused->date = $time;
                $errors['timeslots'] = get_string('already_used', 'local_timetable', $alreadyused);
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

            if (($sessend > $tvalue->sessdate || $sessend > $tvalue->sessedate) || ($tvalue->sessdate == $sessstart && $tvalue->sessedate == $sessend)) {
                $errors['teacherid'] = get_string('teacher_already_exists', 'local_timetable', $alreadyused);
            }
        }

        // Building and room exists for perticular duration.
        $brsql = "SELECT *
                    FROM {attendance_sessions}
                   WHERE building = ? AND room = ? AND (('{$sessstart}' BETWEEN sessdate AND sessedate) OR ('{$sessend}' BETWEEN sessdate AND sessedate ))";
        $brexists = $DB->get_record_sql($brsql, [ $data['building'], $data['room'] ]);
        if ($brexists) {
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
        $examsql = "SELECT lst.id, ats.slotid, ats.sessdate, ats.sessedate
                     FROM {local_session_type} lst
                     JOIN {attendance_sessions} ats ON lst.id = ats.session_type
                    WHERE lst.session_type = ? AND (ats.sessdate BETWEEN '{$sessstart}' AND '{$sessend}') ";
        $exam_schedule = $DB->get_record_sql($examsql, ['Examination']);
        $time = date('d-m-Y H:i', $exam_schedule->sessdate).'-'.date('H:i', $exam_schedule->sessedate);

        if (!empty($exam_schedule)) {
            $errors['timeslots'] = get_string('occupied_for_exam', 'local_timetable', $time);
        }

        if (strlen(trim($data['sessionname'])) == 0 && !empty($data['sessionname'])) {
            $errors['sessionname'] = get_string('blankspaces','local_location');
        }

        return $errors;
    }

    /**
     * Check weekdays function.
     * @param int $sessiondate
     * @param int $sessionenddate
     * @param int $sdays
     * @return bool
     */
    private function checkweekdays($sessiondate, $sessionenddate, $sdays) {

        $found = false;

        $daysofweek = array(0 => "Sun", 1 => "Mon", 2 => "Tue", 3 => "Wed", 4 => "Thu", 5 => "Fri", 6 => "Sat");
        $start = new DateTime( date("Y-m-d", $sessiondate) );
        $interval = new DateInterval('P1D');
        $end = new DateTime( date("Y-m-d", $sessionenddate) );
        $end->add( new DateInterval('P1D') );

        $period = new DatePeriod($start, $interval, $end);
        foreach ($period as $date) {
            if (!$found) {
                foreach ($sdays as $name => $value) {
                    $key = array_search($name, $daysofweek);
                    if ($date->format("w") == $key) {
                        $found = true;
                        break;
                    }
                }
            }
        }

        return $found;
    }
}
