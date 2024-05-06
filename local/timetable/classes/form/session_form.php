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
namespace local_timetable\form;

use moodleform;
use context_system;
use local_attendance_structure;
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
class session_form extends moodleform {
    public $formstatus;
    public function __construct($action = null, $customdata = null, $method = 'post', $target = '', $attributes = null, $editable = true, $formdata = null) {

        $this->formstatus = array(
            'generaldetails' => get_string('generaldetails', 'local_users'), 
            // 'contactdetails' => get_string('contactdetails', 'local_users'),
            );
        parent::__construct($action, $customdata, $method, $target, $attributes, $editable, $formdata);
    }
    /**
     * Called to define this moodle form
     *
     * @return void
     */
    public function definition() {

        global $CFG, $USER, $DB;
        $systemcontext =  context_system::instance();
        $mform    =& $this->_form;
        $class = 'timetableclass';
        $cm          = $this->_customdata['coursesid'];
        $id          = $this->_customdata['id'];
        $semesterid  = $this->_customdata['semesterid'];
        $form_status = $this->_customdata['form_status'];
        $dayname     = $this->_customdata['dayname'];
        $slotid      = $this->_customdata['slotid'];

        if ($form_status == 0) {

            $batchsql = "SELECT c.id
                          FROM {cohort} c
                          JOIN {local_program} lp ON c.id = lp.batchid
                          JOIN {local_program_levels} lpl ON lp.id = lpl.programid
                         WHERE lpl.id = :semid";
            $batchid = $DB->get_field_sql($batchsql, ['semid' => $semesterid]);
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
            
            $select = [null => "Select Course"];
            if ($id || $this->_ajaxformdata['coursesid']) {
                $selectedcourse = (int) $this->_ajaxformdata['coursesid']
                                    ? (int)$this->_ajaxformdata['coursesid']
                                    : $coursenames;
                $defaultcourse = $select + $DB->get_records_menu('course', array('id' => $selectedcourse), '', $fields = 'id, fullname');
            } else {
                $selectedcourse = 0;
                $defaultcourse = $select;
            }
            $costcenteroptions = array(
                'ajax' => 'local_costcenter/form-options-selector',
                'data-contextid' => $systemcontext->id,
                'data-action' => 'costcenter_courseid_selector',
                'data-options' => json_encode(array('id' => $semesterid)),
                'class' => 'timetablecourseselect',
                'data-class' => 'timetablecourse',
                'multiple' => false,
                'data-pluginclass' => $class,
                'onchange' => '(function(e){ require("local_costcenter/newcostcenter").changeElement(event) })(event)',
            );

            $mform->addElement('autocomplete', 'coursesid', get_string('courses', 'local_timetable'), $defaultcourse, $costcenteroptions);
            $mform->addRule('coursesid', get_string('courses', 'local_timetable'), 'required', null, 'client');
            $mform->setType('coursesid', PARAM_INT);
            $teacherselect = [null => get_string('selectteacher', 'local_timetable')];
            if ($id || $this->_ajaxformdata['teacherid']) {
                $openteacher = (int) $this->_ajaxformdata['teacherid']
                                    ? (int)$this->_ajaxformdata['teacherid']
                                    : $dept;
                $sql = "SELECT id, CONCAT(firstname, ' ', lastname) as fullname
                          FROM {user}
                        WHERE id = ?";
                $teachers = $teacherselect + $DB->get_records_sql_menu($sql, [$openteacher]);
            } else {
                $openteacher = 0;
                $teachers = $teacherselect;
            }
            $teacheroptions = array(
                'ajax' => 'local_costcenter/form-options-selector',
                'data-selectstring' => get_string('selectteacher', 'local_timetable'),
                'data-contextid' => $systemcontext->id,
                'data-action' => 'costcenter_teacherid_selector',
                'data-options' => json_encode(array('id' => $openteacher)),
                'class' => 'timetableteacherselect',
                'data-parentclass' => 'timetablecourse',
                'data-class' => 'timetableteacherselect',
                'multiple' => false,
                'data-pluginclass' => $class,
                'onchange' => '(function(e){ require("local_costcenter/newcostcenter").changeElement(event) })(event)',
            );

            $mform->addElement('autocomplete', 'teacherid', get_string('employee', 'attendance'), $teachers, $teacheroptions);
            $mform->setType('teacherid', PARAM_INT);
            $mform->addRule('teacherid', get_string('select_teacher', 'attendance'), 'required', null, 'client');

            $pid = $DB->get_field('local_program_levels', 'programid', ['id' => $semesterid]);
            $costid = $DB->get_field('local_program', 'costcenter', ['id' => $pid]);
            $select = [null => 'Select'];
            $sessiontype = $select + $DB->get_records_menu('local_session_type', array('organization' => $costid), '', $fields = 'id, session_type');
            $mform->addElement('select', 'session_type', get_string('session_type', 'local_timetable'), $sessiontype);
            $mform->setType('session_type', PARAM_INT);

            // $customename[] = $mform->createElement('advcheckbox', 'customename', get_string('customename', 'local_timetable'));
            // $mform->addGroup($customename,  'advcheckbox', '', array(' '), false);
            $mform->addElement('text', 'sessionname', get_string('sessionname', 'local_timetable'), array('maxlength' => '50'));
            $mform->addRule('sessionname', get_string('sessionname', 'attendance'), 'required', null, 'client');
            // $mform->hideIf('sessionname', 'customename', 'neq', 1);

            $c_sql = "SELECT lp.costcenter
                     FROM {local_program} lp
                     JOIN {local_program_levels} lpl ON lpl.programid = lp.id
                    WHERE lpl.id = ?";
            $costid = $DB->get_field_sql($c_sql, [$semesterid]);

            $locationselect = [null => get_string('select_building', 'local_timetable')];
            if ($id || $this->_ajaxformdata['building']) {
                $openlocation = (int) $this->_ajaxformdata['building']
                                    ? (int)$this->_ajaxformdata['building']
                                    : $buildingid;
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

            $roomselect = [null => get_string('select_room', 'local_timetable')];
            if ($id || $this->_ajaxformdata['room']) {
                $openroom = (int) $this->_ajaxformdata['room']
                                    ? (int)$this->_ajaxformdata['room']
                                    : $roomid;
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
            
            $pluginconfig = get_config('attendance');

            // attendance_form_sessiondate_selector($mform);

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

            /*$mform->addElement('editor', 'sdescription', get_string('description', 'attendance'), array('rows' => 1, 'columns' => 80),
                                array('maxfiles' => EDITOR_UNLIMITED_FILES, 'noclean' => true, 'context' => $modcontext));
            $mform->setType('sdescription', PARAM_RAW);*/
            $mform->addElement('textarea', 'description', get_string('description', 'attendance'), array('maxlength' => 30) );
            $mform->setType('description', PARAM_RAW);

            // Warnings.
            $mform->addElement('static', 'warnings', get_string('warnings', 'local_timetable'));

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

            // For multiple sessions.
            // $mform->addElement('header', 'headeraddmultiplesessions', get_string('addmultiplesessions', 'attendance'));
            if (!empty($pluginconfig->multisessionexpanded)) {
                $mform->setExpanded('headeraddmultiplesessions');
            }
            $mform->addElement('checkbox', 'addmultiply', '', get_string('repeatasfollows', 'local_timetable'));
            $mform->addHelpButton('addmultiply', 'createmultiplesessions', 'attendance');

            /*$sdays = array();
            if ($CFG->calendar_startwday === '0') { // Week start from sunday.
                $sdays[] =& $mform->createElement('checkbox', 'Sun', '', get_string('sunday', 'calendar'));
            }
            $sdays[] =& $mform->createElement('checkbox', 'Mon', '', get_string('monday', 'calendar'));
            $sdays[] =& $mform->createElement('checkbox', 'Tue', '', get_string('tuesday', 'calendar'));
            $sdays[] =& $mform->createElement('checkbox', 'Wed', '', get_string('wednesday', 'calendar'));
            $sdays[] =& $mform->createElement('checkbox', 'Thu', '', get_string('thursday', 'calendar'));
            $sdays[] =& $mform->createElement('checkbox', 'Fri', '', get_string('friday', 'calendar'));
            $sdays[] =& $mform->createElement('checkbox', 'Sat', '', get_string('saturday', 'calendar'));
            if ($CFG->calendar_startwday !== '0') { // Week start from sunday.
                $sdays[] =& $mform->createElement('checkbox', 'Sun', '', get_string('sunday', 'calendar'));
            }
            $mform->addGroup($sdays, 'sdays', get_string('repeaton', 'attendance'), array('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'), true);
            $mform->disabledIf('sdays', 'addmultiply', 'notchecked');*/


            $leveldates = $DB->get_record('local_program_levels', ['id' => $semesterid]);
            $pdiffdays = date('d-m-Y', $leveldates->startdate);
            $odiffdays = date('d-m-Y', $leveldates->enddate);
            $newpdate = date_create("$pdiffdays");
            $existingpdate = date_create("$odiffdays");
            $diffdays = date_diff($newpdate, $existingpdate);
            $weekcount = round($diffdays->days / 7);
            for ($i = 1; $i <= $weekcount; $i++) {
                $allweeks[$i] = $i;
            }

            /*$period = array(1 => 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20,
                21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36);*/
            $periodgroup = array();
            $periodgroup[] =& $mform->createElement('select', 'period', '', $allweeks, false, true);
            $mform->disabledIf('period', 'addmultiply', 'notchecked');
            $periodgroup[] =& $mform->createElement('static', 'perioddesc', '', get_string('week', 'attendance'));
            $mform->addGroup($periodgroup, 'periodgroup', get_string('repeatevery', 'attendance'), array(' '), false);

            $mform->addHelpButton('periodgroup', 'avaliableweeks', 'local_timetable');

            $mform->addElement('date_selector', 'sessionenddate', get_string('repeatuntil', 'attendance'));
            $mform->disabledIf('sessionenddate', 'addmultiply', 'notchecked');

            $mform->addElement('hidden', 'coursestartdate', $course->startdate);
            $mform->setType('coursestartdate', PARAM_INT);

            $mform->addElement('hidden', 'previoussessiondate', 0);
            $mform->setType('previoussessiondate', PARAM_INT);

                        // Students can mark own attendance.
            $studentscanmark = get_config('attendance', 'studentscanmark');
            if (!empty($studentscanmark)) {
                // $mgroup = array();
                /*$mform->addElement('checkbox', 'studentscanmark', '', get_string('studentscanmark', 'attendance'));
                    $mform->setDefault('studentscanmark', $studentscanmark);*/
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

            if (isset($pluginconfig->preventsharedip)) {
                $mform->setDefault('preventsharedip', $pluginconfig->preventsharedip);
            }
            if (isset($pluginconfig->preventsharediptime)) {
                $mform->setDefault('preventsharediptime', $pluginconfig->preventsharediptime);
            }
        } // end form status 0.

        if ($form_status == 1) {

            if ($cm) {
                // $mform->addElement('header', 'general', get_string('addsession', 'attendance'));
                $cm = $DB->get_record('course_modules', ['course' => 114, 'module' => 27]);
                $groupmode = groups_get_activity_groupmode($cm);
                switch ($groupmode) {
                    case NOGROUPS:
                    $mform->addElement('static', 'sessiontypedescription', get_string('sessiontype', 'attendance'),
                                          get_string('commonsession', 'attendance'));
                        $mform->addHelpButton('sessiontypedescription', 'sessiontype', 'attendance');
                        $mform->addElement('hidden', 'sessiontype', local_attendance_structure::SESSION_COMMON);
                        $mform->setType('sessiontype', PARAM_INT);

                    $role = array();
                    $role[null] = get_string('selectrole','attendance');
                    $systemroles = $DB->get_records_sql("SELECT u.* FROM {user} u 
                        JOIN {user_enrolments} ue ON ue.userid = u.id 
                        JOIN {enrol} e ON e.id = ue.enrolid
                        WHERE (open_type = 0 AND deleted = 0) AND e.courseid = 114");

                    foreach($systemroles as $key =>$systemrole){
                        
                        $role[$key] = $systemrole->firstname. ' '. $systemrole->lastname;
                    }
                    $mform->addElement('autocomplete', 'teacherid', get_string('employee', 'attendance'),$role);
                    // $mform->addHelpButton('userid', 'useriduser', 'local_employees');
                    $mform->setType('teacherid', PARAM_INT);
                    $mform->addRule('teacherid', get_string('select_teacher', 'attendance'), 'required', null, 'client');

                        break;
                    case SEPARATEGROUPS:
                        $mform->addElement('static', 'sessiontypedescription', get_string('sessiontype', 'attendance'),
                                          get_string('groupsession', 'attendance'));
                        $mform->addHelpButton('sessiontypedescription', 'sessiontype', 'attendance');
                        $mform->addElement('hidden', 'sessiontype', local_attendance_structure::SESSION_GROUP);
                        $mform->setType('sessiontype', PARAM_INT);
                    //     $mform->addElement('autocomplete', 'open_costcenterid', get_string('organization', 'local_employees'));
                    // $mform->addHelpButton('open_costcenterid', 'open_costcenteriduser', 'local_employees');
                    // $mform->setType('open_costcenterid', PARAM_INT);
                    // $mform->addRule('open_costcenterid', get_string('pleaseselectorganization', 'local_employees'), 'required', null, 'client');
                        break;
                    case VISIBLEGROUPS:
                        $radio = array();
                        $radio[] = &$mform->createElement('radio', 'sessiontype', '', get_string('commonsession', 'attendance'),
                                                          local_attendance_structure::SESSION_COMMON);
                        $radio[] = &$mform->createElement('radio', 'sessiontype', '', get_string('groupsession', 'attendance'),
                                                          local_attendance_structure::SESSION_GROUP);
                        $mform->addGroup($radio, 'sessiontype', get_string('sessiontype', 'attendance'), ' ', false);
                        $mform->setType('sessiontype', PARAM_INT);
                        $mform->addHelpButton('sessiontype', 'sessiontype', 'attendance');
                        $mform->setDefault('sessiontype', local_attendance_structure::SESSION_COMMON);
                    //     $mform->addElement('autocomplete', 'open_costcenterid', get_string('organization', 'local_employees'));
                    // $mform->addHelpButton('open_costcenterid', 'open_costcenteriduser', 'local_employees');
                    // $mform->setType('open_costcenterid', PARAM_INT);
                    // $mform->addRule('open_costcenterid', get_string('pleaseselectorganization', 'local_employees'), 'required', null, 'client');
                        break;
                }
                if ($groupmode == SEPARATEGROUPS or $groupmode == VISIBLEGROUPS) {
                    if ($groupmode == SEPARATEGROUPS and !has_capability('moodle/site:accessallgroups', $modcontext)) {
                        $groups = groups_get_all_groups ($course->id, $USER->id, $cm->groupingid);
                    } else {
                        $groups = groups_get_all_groups($course->id, 0, $cm->groupingid);
                    }
                    if ($groups) {
                        $selectgroups = array();
                        foreach ($groups as $group) {
                            $selectgroups[$group->id] = $group->name;
                        }
                        $select = &$mform->addElement('select', 'groups', get_string('groups', 'group'), $selectgroups);
                        $select->setMultiple(true);
                        $mform->disabledIf('groups', 'sessiontype', 'eq', local_attendance_structure::SESSION_COMMON);
                    } else {
                        if ($groupmode == VISIBLEGROUPS) {
                            $mform->updateElementAttr($radio, array('disabled' => 'disabled'));
                        }
                        $mform->addElement('static', 'groups', get_string('groups', 'group'),
                                          get_string('nogroups', 'attendance'));
                        if ($groupmode == SEPARATEGROUPS) {
                            return;
                        }
                    }
                }
            }
        } // end of form status 1

        if ($id > 0 && $form_status > 0) {
            $mform->addElement('hidden', 'id', $id);
            $mform->setType('id', PARAM_INT);

            $mform->addElement('hidden', 'form_status', $form_status);
            $mform->setType('form_status', PARAM_INT);
            
            $mform->addElement('hidden', 'coursesid', $cm);
            $mform->setType('coursesid', PARAM_INT);

            $mform->addElement('hidden', 'slotid', $slotid);
            $mform->setType('slotid', PARAM_INT);

            $mform->addElement('hidden', 'dayname', $dayname);
            $mform->setType('dayname', PARAM_RAW);
            
        } else {
            $mform->addElement('hidden', 'id');
            $mform->setType('id', PARAM_INT);

            $mform->addElement('hidden', 'form_status');
            $mform->setType('form_status', PARAM_INT);

            $mform->addElement('hidden', 'semid', $semesterid);
            $mform->setType('semid', PARAM_INT);

            $mform->addElement('hidden', 'sessiondate', $leveldates->startdate);
            $mform->setType('sessiondate', PARAM_INT);

            $mform->addElement('hidden', 'slotid', $slotid);
            $mform->setType('slotid', PARAM_INT);

            $mform->addElement('hidden', 'dayname', $dayname);
            $mform->setType('dayname', PARAM_RAW);
        }


        $mform->disable_form_change_checker();

        // $this->add_action_buttons(true, get_string('add', 'attendance'));
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
        $cm = $DB->get_record('course_modules', ['course' => $courseid, 'module' => 27]);

        $errors = parent::validation($data, $files);

        $leveldatacheck = $DB->get_record('local_program_levels', ['id' => $data['semid']]);
        if (isset($data['addmultiply'])) {
            if (isset($data['sessionenddate'])) {
                if ($data['sessionenddate'] > $leveldatacheck->enddate && $data['form_status'] == 0) {
                    $errors['sessionenddate'] = get_string('invalidenddata', 'local_timetable');
                }
                if ($data['sessionenddate'] < $leveldatacheck->startdate && $data['form_status'] == 0) {
                    $errors['sessionenddate'] = get_string('invalidsdata', 'local_timetable');
                }
            }
        }

        /*$sesstarttime = $data['sestime']['starthour'] * HOURSECS + $data['sestime']['startminute'] * MINSECS;
        
        $sesendtime = $data['sestime']['endhour'] * HOURSECS + $data['sestime']['endminute'] * MINSECS;
        
        if ($sesendtime < $sesstarttime && $data['form_status'] == 0) {
            $errors['sestime'] = get_string('invalidsessionendtime', 'attendance');
        }

        if (!empty($data['addmultiply']) && $data['sessiondate'] != 0 && $data['sessionenddate'] != 0 &&
                $data['sessionenddate'] < $data['sessiondate']) {
            $errors['sessionenddate'] = get_string('invalidsessionenddate', 'attendance');
        }

        if ($data['sessiontype'] == local_attendance_structure::SESSION_GROUP and empty($data['groups'])) {
            $errors['groups'] = get_string('errorgroupsnotselected', 'attendance');
        }

        $addmulti = isset($data['addmultiply']) ? (int)$data['addmultiply'] : 0;
        if (($addmulti != 0) && (!array_key_exists('sdays', $data) || empty($data['sdays']))) {
            $data['sdays'] = array();
            $errors['sdays'] = get_string('required', 'attendance');
        }
        if (isset($data['sdays'])) {
            if (!$this->checkweekdays($data['sessiondate'], $data['sessionenddate'], $data['sdays']) ) {
                $errors['sdays'] = get_string('checkweekdays', 'attendance');
            }
        }
        if ($addmulti && ceil(($data['sessionenddate'] - $data['sessiondate']) / YEARSECS) > 1) {
            $errors['sessionenddate'] = get_string('timeahead', 'attendance');
        }
        $sessstart = $data['sessiondate'] + $sesstarttime;
        if ($sessstart < $data['coursestartdate'] && $sessstart != $data['previoussessiondate'] && $data['form_status'] == 0) {
            $errors['sessiondate'] = get_string('priorto', 'attendance',
                userdate($data['coursestartdate'], get_string('strftimedmyhm', 'attendance')));
            $this->_form->setConstant('previoussessiondate', $sessstart);
        }*/

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
        /*$existedsessionsql = "SELECT ats.attendanceid, ats.sessdate, ats.duration
                                FROM {attendance_sessions} ats
                                JOIN {attendance} att ON ats.attendanceid = att.id
                                WHERE att.course = :course AND ats.attendanceid = :attendance
                                AND ats.sessdate = :sessionstart AND ats.id != :sessid";
        $existedsession = $DB->record_exists_sql($existedsessionsql, array('course' => $cm->course, 'attendance' => $cm->instance, 'sessionstart' => $sessstart, 'sessid' => $semesterid));
        if ($existedsession && $data['form_status'] == 0) {
            $errors['sestime'] = get_string('alreadyexisted', 'attendance');
        }
        $courseexists = $DB->record_exists('local_program_level_courses', array('levelid' => $semesterid));
        if ($courseexists) {
            $semrecord = $DB->get_record('local_program_levels', array('id' => $semesterid));
            if ($semrecord->startdate > 0 && $semrecord->startdate != null && $data['form_status'] == 0) {
                if ($data['sessiondate'] < $semrecord->startdate && $data['form_status'] == 0) {
                    $semstartdate = date('d-M-Y', $semrecord->startdate);
                    $errors['sessiondate'] = get_string('semesterstartson', 'attendance', $semstartdate);
                }
            }
            if ($semrecord->enddate > 0 && $semrecord->enddate != null && $data['form_status'] == 0) {
                if ($data['sessiondate'] > $semrecord->enddate && $data['form_status'] == 0) {
                    $semenddate = date('d-M-Y', $semrecord->enddate);
                    $errors['sessiondate'] = get_string('semesterendson', 'attendance', $semenddate);
                }
            }
        }*/
        if (empty($data['teacherid']) && $data['form_status'] == 0) {
            $errors['teacherid'] = get_string('teachererror', 'local_timetable');
        }
        if (empty($data['coursesid']) && $data['form_status'] == 0) {
            $errors['coursesid'] = get_string('courseerror', 'local_timetable');
        }
        $formdata = new \stdClass();
        $formdata->dayname = $data['dayname'];
        $formdata->sessiondate = $data['sessiondate'];
        $formdata->slotid = $data['slotid'];
        $check_semdate = semester_timetable_date_validation($formdata);

        $obj = new \stdClass();
        $obj->nextday = ucfirst($check_semdate->nextday);
        $obj->date = date('d-m-Y', $check_semdate->sessdate);

        if ($check_semdate->sessdate > $leveldatacheck->enddate && $data['form_status'] == 0) {
            $errors['warnings'] = get_string('semester_date_error', 'local_timetable', $obj);
        }

        // Teacher exists for perticular duration.
        $teacher_existssql = "SELECT *
                                FROM {attendance_sessions}
                               WHERE teacherid = ? AND (('{$check_semdate->sessdate}' BETWEEN sessdate AND sessedate ) OR ('{$check_semdate->enddate}' BETWEEN sessdate AND sessedate ))";
        $teacher_exists = $DB->get_records_sql($teacher_existssql, [$data['teacherid']]);
        $alreadyused = new \stdClass();
        foreach ($teacher_exists as $key => $tvalue) {
            $teacherdetails = $DB->get_record('user', ['id' => $data['teacherid']]);
            $alreadyused->teacher_fullname = fullname($teacherdetails);
            $alreadyused->date = date('d-m-Y H:i', $tvalue->sessdate).'-'.date('H:i', $tvalue->sessedate);

            if (($check_semdate->enddate > $tvalue->sessdate || $check_semdate->enddate > $tvalue->sessedate) || ($tvalue->sessdate == $check_semdate->sessdate && $tvalue->sessedate == $check_semdate->enddate) && $data['form_status'] == 0) {
                $errors['teacherid'] = get_string('teacher_already_exists', 'local_timetable', $alreadyused);
            }
        }

        // Slot exists.
        $slots = "SELECT *
                   FROM {attendance_sessions}
                  WHERE 1=1 AND (('{$check_semdate->sessdate}' BETWEEN sessdate AND sessedate) OR ('{$check_semdate->enddate}' BETWEEN sessdate AND sessedate))";
        $slotsql = $DB->get_records_sql($slots);
        foreach ($slotsql as $value) {
            $exists_course_p_id = $DB->get_record('local_program_level_courses', ['courseid' => $value->courseid]);
            $course_p_id = $DB->get_record('local_program_level_courses', ['courseid' => $data['coursesid']]);

            $condition = $exists_course_p_id->programid == $course_p_id->programid && ($check_semdate->sessdate < $value->sessedate || $check_semdate->sessdate < $value->sessdate) && ($check_semdate->enddate > $value->sessdate || $check_semdate->enddate > $value->sessedate) && $data['form_status'] == 0;

            if ($condition == true) {
                $time = date('d-m-Y H:i', $value->sessdate).'-'.date('H:i', $value->sessedate);
                $coursename = get_course($value->courseid);
                $alreadyused->course = $coursename->fullname;
                $alreadyused->date = $time;
                if ($value->courseid == $data['coursesid']) {
                    $errors['warnings'] = get_string('already_used', 'local_timetable', $alreadyused);
                } else {
                    $errors['warnings'] = get_string('already_used', 'local_timetable', $alreadyused);
                }
            }
        }

        // Building and room exists for perticular duration.
        $brsql = "SELECT *
                    FROM {attendance_sessions}
                   WHERE building = ? AND room = ? AND (('{$check_semdate->sessdate}' BETWEEN sessdate AND sessedate) OR ('{$check_semdate->enddate}' BETWEEN sessdate AND sessedate ))";
        $brexists = $DB->get_record_sql($brsql, [ $data['building'], $data['room'] ]);
        if ($brexists && $data['form_status'] == 0) {
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
                    WHERE lst.session_type = ? AND (ats.sessdate BETWEEN '{$check_semdate->sessdate}' AND '{$check_semdate->enddate}') ";
        $exam_schedule = $DB->get_record_sql($examsql, ['Examination']);
        $time = date('d-m-Y H:i', $exam_schedule->sessdate).'-'.date('H:i', $exam_schedule->sessedate);

        if (!empty($exam_schedule)) {
            $errors['warnings'] = get_string('occupied_for_exam', 'local_timetable', $time);
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
