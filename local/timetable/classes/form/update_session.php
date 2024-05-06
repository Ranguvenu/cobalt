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
 * Update form
 *
 * @package    local_timetable
 * @copyright  2011 Artem Andreev <andreev.artem@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_timetable\form;

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
class update_session extends \moodleform {

    /**
     * Called to define this moodle form
     *
     * @return void
     */
    public function definition() {

        global $CFG, $DB, $COURSE, $USER;
        $systemcontext = context_system::instance();
        $class = 'timetableclass';
        $mform    =& $this->_form;

        $teacherid     = $this->_customdata['teacherid'];
        $semesterid    = $this->_customdata['semesterid'];
        $modcontext    = $this->_customdata['modcontext'];
        $sessionid     = $this->_customdata['id'];
        $course        = $this->_customdata['course'];
        $cm            = $this->_customdata['cm'];

        if (!$sess = $DB->get_record('attendance_sessions', array('id' => $sessionid) )) {
            error('No such session in this course');
        }

        $attendancesubnet = $DB->get_field('attendance', 'subnet', array('id' => $sess->attendanceid));
        $defopts = array('maxfiles' => EDITOR_UNLIMITED_FILES, 'noclean' => true, 'context' => $modcontext);
        $sess = file_prepare_standard_editor($sess, 'description', $defopts, $modcontext, 'mod_attendance', 'session', $sess->id);

        $starttime = $sess->sessdate - usergetmidnight($sess->sessdate);
        $starthour = floor($starttime / HOURSECS);
        $startminute = floor(($starttime - $starthour * HOURSECS) / MINSECS);

        $enddate = $sess->sessdate + $sess->duration;
        $endtime = $enddate - usergetmidnight($enddate);
        $endhour = floor($endtime / HOURSECS);
        $endminute = floor(($endtime - $endhour * HOURSECS) / MINSECS);


        $data = array(
            'sessiondate' => $sess->sessdate,
            'sestime' => array('starthour' => $starthour, 'startminute' => $startminute,
            'endhour' => $endhour, 'endminute' => $endminute),
            'sdescription' => $sess->description_editor,
            'calendarevent' => $sess->calendarevent,
            'studentscanmark' => $sess->studentscanmark,
            'studentpassword' => $sess->studentpassword,
            'autoassignstatus' => $sess->autoassignstatus,
            'subnet' => $sess->subnet,
            'automark' => $sess->automark,
            'absenteereport' => $sess->absenteereport,
            'automarkcompleted' => 0,
            'preventsharedip' => $sess->preventsharedip,
            'preventsharediptime' => $sess->preventsharediptime,
            'includeqrcode' => $sess->includeqrcode,
            'rotateqrcode' => $sess->rotateqrcode,
            'automarkcmid' => $sess->automarkcmid,
            'coursesid' => $course,
            'teacherid' => $teacherid,
            'session_type' => $sess->session_type,
            'building' => $sess->building,
            'room' => $sess->room
        );

        if ($sess->subnet == $attendancesubnet) {
            $data['usedefaultsubnet'] = 1;
        } else {
            $data['usedefaultsubnet'] = 0;
        }

        if ($sess->batch_group == 0) {
            $strtype = get_string('commonsession', 'attendance');
        } else {
            $groupname = $DB->get_field('cohort', 'name', array('id' => $sess->batch_group));
            $strtype = $groupname;
        }
        $mform->addElement('static', 'sessiontypedescription', get_string('group', 'local_timetable'), $strtype);

        $olddate = construct_session_full_date_time($sess->sessdate, $sess->duration);

        $mform->addElement('static', 'olddate', get_string('olddate', 'attendance'), $olddate);

        $select = [null => "Select Course"];
        if ($course || $this->_ajaxformdata['course']) {
            $semcourse = (int) $this->_ajaxformdata['course']
                                ? (int)$this->_ajaxformdata['course']
                                : $course;
            $courses = $select + $DB->get_records_menu('course', array('id' => $semcourse), '', $fields = 'id, fullname');
        } else {
            $semcourse = 0;
            $courses = $select;
        }
        $courseoptions = array(
            'ajax' => 'local_costcenter/form-options-selector',
            'data-contextid' => $systemcontext->id,
            'data-action' => 'costcenter_courseid_selector',
            'data-options' => json_encode(array('id' => $semesterid, 'semcourse' => $semcourse)),
            'class' => 'timetablecourseselect',
            'data-class' => 'timetablecourse',
            'multiple' => false,
            'data-pluginclass' => $class,
            'onchange' => '(function(e){ require("local_costcenter/newcostcenter").changeElement(event) })(event)',
        );

        $mform->addElement('autocomplete', 'coursesid', 'Courses', $courses, $courseoptions);
        $mform->setType('coursesid', PARAM_INT);
        $mform->addRule('coursesid', get_string('courses', 'attendance'), 'required', null, 'client');

        $teacherselect = [null => "Select Teacher"];
        if ($teacherid || $this->_ajaxformdata['teacherid']) {
            $courseteacher = (int) $this->_ajaxformdata['teacherid']
                                ? (int)$this->_ajaxformdata['teacherid']
                                : $teacherid;
            $sql = "SELECT id, CONCAT(firstname, ' ', lastname) as fullname
                     FROM {user}
                    WHERE id = ?";
            $teacher = $teacherselect + $DB->get_records_sql_menu($sql, [$courseteacher]);
            // $teacher = $teacherselect + $DB->get_records_menu('user', array('id' => $courseteacher), '', $fields = 'id, firstname');
        } else {
            $courseteacher = 0;
            $teacher = $teacherselect;
        }
        $teacheroptions = array(
            'ajax' => 'local_costcenter/form-options-selector',
            'data-selectstring' => get_string('selectteacher', 'local_timetable'),
            'data-contextid' => $systemcontext->id,
            'data-action' => 'costcenter_teacherid_selector',
            'data-options' => json_encode(array('id' => $courseteacher)),
            'class' => 'timetableteacherselect',
            'data-parentclass' => 'timetablecourse',
            'data-class' => 'timetableteacherselect',
            'multiple' => false,
            'data-pluginclass' => $class,
            'onchange' => '(function(e){ require("local_costcenter/newcostcenter").changeElement(event) })(event)',
        );

        $mform->addElement('autocomplete', 'teacherid', get_string('employee', 'attendance'), $teacher, $teacheroptions);
        $mform->setType('teacherid', PARAM_INT);
        $mform->addRule('teacherid', get_string('select_teacher', 'attendance'), 'required', null, 'client');

        $pid = $DB->get_field('local_program_levels', 'programid', ['id' => $semesterid]);
        $costid = $DB->get_field('local_program', 'costcenter', ['id' => $pid]);
        $select = [null => 'Select'];
        $sessiontype = $select + $DB->get_records_menu('local_session_type', array('organization' => $costid), '', $fields = 'id, session_type');
        $mform->addElement('select', 'session_type', get_string('session_type', 'local_timetable'), $sessiontype);
        $mform->setType('session_type', PARAM_INT);

        /*$sessname = $DB->get_field('attendance_sessions', 'sessionname', ['id' => $id]);
        $customename = [];
        if ($sessname !== null) {
            $customename[] = $mform->createElement('advcheckbox', 'customename', get_string('customename', 'local_timetable'));
            $mform->addGroup($customename,  'advcheckbox', '', array(' '), false);
            $mform->addElement('text', 'sessionname', get_string('sessionname', 'local_timetable'), array('maxlength' => '20'));
            $mform->setDefault('customename', 1);

        } else {*/
            // $customename[] = $mform->createElement('advcheckbox', 'customename', get_string('customename', 'local_timetable'));
            // $mform->addGroup($customename,  'advcheckbox', '', array(' '), false);
            $mform->addElement('text', 'sessionname', get_string('sessionname', 'local_timetable'), array('maxlength' => '50'));
            $mform->addRule('sessionname', get_string('sessionname', 'attendance'), 'required', null, 'client');
            // $mform->hideIf('sessionname', 'customename', 'neq', 1);
        // }

        $c_sql = "SELECT lp.costcenter
                 FROM {local_program} lp
                 JOIN {local_program_levels} lpl ON lpl.programid = lp.id
                WHERE lpl.id = ?";
        $costid = $DB->get_field_sql($c_sql, [$semesterid]);

        $locationselect = [0 => get_string('select_building', 'local_timetable')];
        if ($id || $this->_ajaxformdata['building'] || $sess->building) {
            $openlocation = (int) $this->_ajaxformdata['building']
                                ? (int)$this->_ajaxformdata['building']
                                : $sess->building;
            $location = $locationselect + $DB->get_records_menu('local_location_institutes', array('id' => $openlocation), '', $fields = 'id, fullname');
        } else {
            $openlocation = 0;
            $location = $locationselect;
        }

        $locationoptions = array(
            'ajax' => 'local_costcenter/form-options-selector',
            'data-selectstring' => get_string('select_building', 'local_timetable'),
            'data-contextid' => $systemcontext->id,
            'data-action' => 'costcenter_building_selector',
            'data-options' => json_encode(array('id' => $openlocation, 'costcenterid' => $costid)),
            'class' => 'locationselect',
            'data-class' => 'locationselect',
            'multiple' => false,
            'data-pluginclass' => $class,
            'onchange' => '(function(e){ require("local_costcenter/newcostcenter").changeElement(event) })(event)',
        );

        $mform->addElement('autocomplete', 'building', get_string('building', 'local_timetable'), $location, $locationoptions);

        $roomselect = [0 => get_string('select_room', 'local_timetable')];
        if ($id || $this->_ajaxformdata['room'] || $sess->room) {
            $openroom = (int) $this->_ajaxformdata['room']
                                ? (int)$this->_ajaxformdata['room']
                                : $sess->room;
            $room = $roomselect + $DB->get_records_menu('local_location_room', array('id' => $openroom), '', $fields = 'id, name');
        } else {
            $openroom = 0;
            $room = $roomselect;
        }

        $roomoptions = array(
            'ajax' => 'local_costcenter/form-options-selector',
            'data-selectstring' => get_string('select_room', 'local_timetable'),
            'data-contextid' => $systemcontext->id,
            'data-action' => 'costcenter_room_selector',
            'data-options' => json_encode(array('id' => $openroom)),
            'class' => 'roomselect',
            'data-parentclass' => 'locationselect',
            'data-class' => 'roomselect',
            'multiple' => false,
            'data-pluginclass' => $class,
            'onchange' => '(function(e){ require("local_costcenter/newcostcenter").changeElement(event) })(event)',
        );

        $mform->addElement('autocomplete', 'room', get_string('room', 'local_timetable'), $room, $roomoptions);

        // attendance_form_sessiondate_selector($mform);

        // Show which status set is in use.
        $maxstatusset = attendance_get_max_statusset($this->_customdata['att']->id);
        if ($maxstatusset > 0) {
            $mform->addElement('static', 'statussetstring', get_string('usestatusset', 'mod_attendance'),
                attendance_get_setname($this->_customdata['att']->id, $sess->statusset));
        }
        $mform->addElement('hidden', 'statusset', $sess->statusset);
        $mform->setType('statusset', PARAM_INT);

        $mform->addElement('textarea', 'description', get_string('description', 'attendance'), array('maxlength' => 30) );
            $mform->setType('description', PARAM_RAW);

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

        // $mform->addElement('header', 'headerstudentmarking', get_string('studentmarking', 'attendance'), true);
        // $mform->setExpanded('headerstudentmarking');
        if (!empty($studentscanmark)) {
            $mform->addElement('checkbox', 'studentscanmark', '', get_string('studentscanmark', 'attendance'));
            $mform->addHelpButton('studentscanmark', 'studentscanmark', 'attendance');
            $mform->setDefault('studentscanmark', $studentscanmark);

            $mform->addElement('checkbox', 'includeqrcode', '', get_string('includeqrcode', 'attendance'));
            $mform->setDefault('includeqrcode', $studentscanmark);
            // $mform->hideif('includeqrcode', 'studentscanmark', 'notchecked');
            // $mform->disabledif('includeqrcode', 'rotateqrcode', 'checked');
            $mform->addElement('checkbox', 'rotateqrcode', '', get_string('rotateqrcode', 'attendance'));
            $mform->setDefault('rotateqrcode', $studentscanmark);

            // $mform->hideif('rotateqrcode', 'studentscanmark', 'notchecked');
            $mform->addElement('checkbox', 'autoassignstatus', '', get_string('autoassignstatus', 'attendance'));
            $mform->addHelpButton('autoassignstatus', 'autoassignstatus', 'attendance');
            $mform->setDefault('autoassignstatus', $studentscanmark);
            // $mform->hideif('autoassignstatus', 'studentscanmark', 'notchecked');
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

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->setDefault('id', PARAM_INT, $sessionid);

        $mform->setDefaults($data);
        // $this->add_action_buttons(true);
    }

    /**
     * Perform minimal validation on the settings form
     * @param array $data
     * @param array $files
     */
    public function validation($data, $files) {
        global $DB;

        $sessionid     = $this->_customdata['id'];
        $modcontext    = $this->_customdata['modcontext'];
        $course        = $this->_customdata['course'];
        $cm            = $this->_customdata['cm'];
        $errors = parent::validation($data, $files);

        if ($data['id'] > 0) {
            $atsdata = $DB->get_record('attendance_sessions', ['id' => $data['id']]);
            $slotstimes = $DB->get_record('local_timeintervals_slots', ['id' => $atsdata->slotid]);
            $date = explode('-', date('d-m-Y', $atsdata->sessdate));
            $atsdate = $date[0].'-'.$date[1].'-'.$date[2];
            $sdate = strtotime($atsdate);
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

        if (!empty($data['studentscanmark']) && isset($data['automark'])
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

        }
        $sessstart = $sdate + $sesstarttime;
        $sessend = $sdate + $sesendtime;
        $existedsessionsql = "SELECT ats.attendanceid, ats.sessdate, ats.duration
                                FROM {attendance_sessions} ats
                                JOIN {attendance} att ON ats.attendanceid = att.id
                                WHERE att.course = :course AND ats.attendanceid = :attendance
                                AND ats.sessdate = :sessionstart AND ats.id != :sessid";
        $existedsession = $DB->record_exists_sql($existedsessionsql, array('course' => $cm->course, 'attendance' => $cm->instance, 'sessionstart' => $sessstart, 'sessid' => $sessionid));

        if ($existedsession) {
            $errors['sestime'] = get_string('alreadyexisted', 'attendance');
        }
        $courseexists = $DB->record_exists('local_program_level_courses', array('courseid' => $course->id));
        if ($courseexists) {
            $courselevel = $DB->get_field('local_program_level_courses', 'levelid', array('courseid' => $course->id));
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
        if (empty($data['teacherid'])) {
            $errors['teacherid'] = get_string('teachererror', 'local_timetable');
        }
        if (empty($data['coursesid'])) {
            $errors['coursesid'] = get_string('courseerror', 'local_timetable');
        }

        // Teacher exists for perticular duration.
        $teacher_existssql = "SELECT *
                                FROM {attendance_sessions}
                               WHERE teacherid = ? AND (('{$sessstart}' BETWEEN sessdate AND sessedate ) OR ('{$sessend}' BETWEEN sessdate AND sessedate ))";
        $teacher_exists = $DB->get_records_sql($teacher_existssql, [$data['teacherid']]);
        $alreadyused = new \stdClass();
        foreach ($teacher_exists as $key => $tvalue) {
            $teacherdetails = $DB->get_record('user', ['id' => $data['teacherid']]);
            $alreadyused->teacher_fullname = fullname($teacherdetails);
            $alreadyused->date = date('d-m-Y H:i', $tvalue->sessdate).'-'.date('H:i', $tvalue->sessedate);

            if ($data['id'] != $tvalue->id) {
                if (($sessend > $tvalue->sessdate || $sessend > $tvalue->sessedate) || ($tvalue->sessdate == $sessstart && $tvalue->sessedate == $sessend) ) {
                    $errors['teacherid'] = get_string('teacher_already_exists', 'local_timetable', $alreadyused);
                }                
            }
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
        $examsql = "SELECT lst.id, ats.slotid, ats.sessdate, ats.sessedate
                     FROM {local_session_type} lst
                     JOIN {attendance_sessions} ats ON lst.id = ats.session_type
                    WHERE lst.session_type = ? AND (ats.sessdate BETWEEN '{$sessstart}' AND '{$sessend}') ";
        $exam_schedule = $DB->get_record_sql($examsql, ['Examination']);
        $time = date('d-m-Y H:i', $exam_schedule->sessdate).'-'.date('H:i', $exam_schedule->sessedate);

        if (!empty($exam_schedule) && $exam_schedule->id != $data['id']) {
            $errors['exam_occ'] = get_string('occupied_for_exam', 'local_timetable', $time);
        }

        if (strlen(trim($data['sessionname'])) == 0 && !empty($data['sessionname'])) {
            $errors['sessionname'] = get_string('blankspaces','local_location');
        }

        return $errors;
    }
}
