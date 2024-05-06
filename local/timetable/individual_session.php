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
 * @package    local
 * @subpackage  Timetable
 * @copyright  2012 Hemalatha arun <Hemaltha@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
global $CFG, $DB, $USER, $OUTPUT;
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/lib.php');
require_once($CFG->dirroot . '/local/timetable/lib.php');
require_once($CFG->dirroot . '/local/courses/filters_form.php');
require_once($CFG->dirroot.'/mod/attendance/locallib.php');
$semid = required_param('tlid', PARAM_INT);
$sessionaction = optional_param('sessionaction','',PARAM_RAW);
$dataexcel = optional_param('dataexcel','',PARAM_RAW);
$exceldata = json_decode($dataexcel);
$PAGE->set_url('/local/timetable/individual_session.php?tlid='.$semid);
if($sessionaction){    
    foreach ($exceldata as $key => $value) {
        $courseid = $DB->get_field('course','id', array('shortname'=>$value->coursecode));
        $sesstarttime = $value->sestime->starthour * HOURSECS + $value->sestime->startminute * MINSECS;
        $sesendtime = $value->sestime->endhour * HOURSECS + $value->sestime->endminute * MINSECS;
        $sessiondate = strtotime($value->date) + $sesstarttime;
        $sessenddate = strtotime($value->date) + $sesendtime;
        $duration = $sesendtime - $sesstarttime;
        if($value->semid){
            $timeintervalid = $DB->get_field('local_timeintervals','id', array('semesterid' => $value->semid));
        }
        $attendanceid = $DB->get_field('attendance','id',array('course' => $courseid));
        $teacherid = $DB->get_field('user','id',array('email'=>$value->teacher_email));
        $totaltime = explode('-', $value->slottime);
        $starttime = '';
        $endtime = '';
        foreach ($totaltime as $key => $val) {
            $starttime = $totaltime[0];            
            $endtime = $totaltime[1];
        }
        if ($totaltime) {
            $sql = "SELECT id  FROM {local_timeintervals_slots} WHERE starttime LIKE '%$starttime%' AND endtime LIKE '%$endtime%' AND timeintervalid = $timeintervalid";
            $slotid = $DB->get_record_sql($sql);
        }
        if ($slotid) {
            $sessionexists = $DB->get_record_sql("SELECT id FROM {attendance_sessions} WHERE sessdate = $sessiondate AND slotid = $slotid->id");
        }
        if (strlen($value->building) > 0) {
            $buildingid = $DB->get_field('local_location_institutes', 'id', ['fullname' => $value->building]);
        } else {
            $buildingid = 0;
        }
        if (strlen($value->room) > 0) {
            $roomid = $DB->get_field('local_location_room', 'id', ['name' => $value->room]);
        } else {
            $roomid = 0;
        }
        if (strlen($value->group) > 0) {
            $groupid = $DB->get_field('local_location_room', 'id', ['name' => $value->group]);
        } else {
            $groupid = 0;
        }
        $user->attendanceid = $attendanceid;
        $user->calendarevent = 1;
        $user = new \stdclass();
        $user->courseid = $courseid;
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
        $user->sessionname = $value->session_name;
        $user->sessdate = $sessiondate;
        $user->sessedate = $sessenddate;
        $user->duration = $duration;
        $user->slotid = $slotid->id;
        $user->description = $value->description;
        $user->building = $buildingid;
        $user->room = $roomid;
        $user->batch_group = $groupid;
        $user->timemodified = time();
        if (!$sessionexists && $sessionaction === 'singleno') {
            $user->id = $sessionexists;
            // $DB->insert_record('attendance_sessions', $user);
        }
        if ($sessionexists && $sessionaction === 'singleyes'){
            $user->id = $sessionexists->id;
            $currdate = time();
            if($currdate < $sessiondate){
                $DB->update_record('attendance_sessions', $user);  
            }
        }
        if (!$sessionexists && $sessionaction === 'singleyes') {
            $user->id = $sessionexists->id;
            // $DB->insert_record('attendance_sessions', $user);
            
        }
    }
}
$systemcontext =  context_system::instance();
$PAGE->set_pagelayout('standard');
$PAGE->set_context($systemcontext);
require_login();
$PAGE->set_title(get_string('individualsession', 'local_timetable'));
$PAGE->requires->js_call_amd('local_timetable/ajaxforms', 'load', array());
if (is_siteadmin() || 
    has_capability('local/costcenter:manage_multiorganizations', $systemcontext) || 
    has_capability('local/costcenter:manage_ownorganization', $systemcontext) || 
    has_capability('local/costcenter:manage_owndepartments', $systemcontext)) {
    $sql = "SELECT lp.id, CONCAT(lp.name,'-', lpl.level) as fullname
              FROM {local_program} lp
              JOIN {local_program_levels} lpl ON lpl.programid = lp.id
            WHERE lpl.id = ? ";
    $mergenames = $DB->get_record_sql($sql, [$semid]);
    $PAGE->set_heading(get_string('admin_individualsession', 'local_timetable', $mergenames));

    $PAGE->navbar->add(get_string('timetablelayout', 'local_timetable'), new moodle_url('/local/timetable/timelayoutview.php'));
    $PAGE->navbar->add(get_string('individualsession', 'local_timetable'));
} else {
    $PAGE->set_heading(get_string('individualsession', 'local_timetable'));
}

echo $OUTPUT->header();

$renderer = $PAGE->get_renderer('local_timetable');
echo $renderer->get_timetableadd_btns();
$filterparams = $renderer->timetable_individual_session_content(true);

$thisfilters = array('month', 'teacher');

$filterparams['submitid'] = 'formfilteringform';

$mform = new filters_form(null, array('filterlist' => $thisfilters, 'filterparams' => $filterparams , 'submitid'=>'formfilteringform'));

if ($filterdata) {
    $collapse = false;
    $show = 'show';
} else {
    $collapse = true;
    $show = '';
}
$role = identify_teacher_role($USER->id);
if(is_siteadmin()
  || has_capability('local/costcenter:manage_multiorganizations', $systemcontext)
  || has_capability('local/costcenter:manage_ownorganization', $systemcontext)
  || has_capability('local/costcenter:manage_owndepartments', $systemcontext)
  || has_capability('local/costcenter:manage_ownsubdepartments', $systemcontext)
  || $role->shortname == 'editingteacher') {

    echo '<a id="tmtblfltrbtn" class="btn-link btn-sm d-flex align-items-center filter_btn" 
            href="javascript:void(0);"
            data-toggle="collapse" data-target="#local_courses-filter_collapse"
            aria-expanded="false" aria-controls="local_courses-filter_collapse">
                <span class="filter mr-2">Filters</span>
            <i class="m-0 fa fa-sliders fa-2x" aria-hidden="true"></i>
        </a>';

    echo  '<div class="collapse '.$show.'" id="local_courses-filter_collapse">
            <div id="filters_form" class="card card-body p-2">';
                $mform->display();
    echo    '</div>
           </div><br>';

    echo $OUTPUT->render_from_template('local_timetable/global_filter', $filterparams);

    if (is_siteadmin()
        || has_capability('local/costcenter:manage_ownorganization', $systemcontext)
        || has_capability('local/costcenter:manage_owndepartments', $systemcontext)) {

        echo html_writer::link(new moodle_url('/local/timetable/timelayoutview.php'),''.get_string('back', 'local_timetable').'',array('id'=>'local_timetable_batchwisebu', 'class' => 'btn btn-primary'));
    }

    echo html_writer::link(
        new moodle_url('/local/timetable/timetable_view.php?text=default&semid='.$semid),
        ''.get_string('calendar_view', 'local_timetable').'',
        array(
            'id'=>'local_timetable_batchwisebu',
            'class' => 'btn btn-primary float-right mr-2'
        )
    );

    echo $renderer->timetable_individual_session_content();

    echo $OUTPUT->footer();
} else {
    throw new Exception('You dont have permission to access this page');
}
