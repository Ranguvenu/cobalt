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
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/lib.php');
require_once($CFG->dirroot . '/local/timetable/lib.php');
require_once($CFG->dirroot . '/local/timetable/classes/form/individual_session.php');
require_once($CFG->dirroot.'/local/attendance/locallib.php');
require_once($CFG->dirroot.'/local/timetable/lib.php');
require_once($CFG->dirroot.'/lib/datalib.php');

global $CFG, $DB, $USER, $OUTPUT;
$semid = required_param('semid', PARAM_INT);
$courseid = optional_param('courseid', '', PARAM_INT);
$systemcontext =  context_system::instance();
$PAGE->set_pagelayout('standard');
$PAGE->set_context($systemcontext);
require_login();
$PAGE->set_title('Timetable');
$PAGE->set_url('/local/timetable/individual_insert.php?semid='.$semid);
$PAGE->set_heading(get_string('individualsession', 'local_timetable'));

if (is_siteadmin()
    || has_capability('local/costcenter:manage_multiorganizations', $systemcontext)
    || (has_capability('local/costcenter:manage_ownorganization', $systemcontext))
    || (has_capability('local/costcenter:manage_owndepartments', $systemcontext))
    || (has_capability('local/costcenter:manage_ownsubdepartments', $systemcontext))) {
    $PAGE->navbar->add(get_string('timetablelayout', 'local_timetable'), new moodle_url('/local/timetable/timelayoutview.php'));
}
$PAGE->navbar->add(get_string('individualsession', 'local_timetable'), new moodle_url('/local/timetable/individual_session.php?tlid='.$semid));
$PAGE->navbar->add(get_string('createindividualsession', 'local_timetable'));
$PAGE->requires->js_call_amd('local_costcenter/newcostcenter', 'load', array());

$formparams['semesterid'] = $semid;
if (!empty($courseid)) {
    $formparams['coursid'] = $courseid;
}

echo $OUTPUT->header();
$mform = new individual_session(array('action' => 1), $formparams);

if ($mform->is_cancelled()) {
    // redirect($CFG->wwwroot .'/local/timetable/individual_session.php?tlid='.$semid);

} else if ($formdata = $mform->get_data()) {

    $slotstimes = $DB->get_record('local_timeintervals_slots', ['id' => $formdata->timeslots]);
    $stime = explode(':', $slotstimes->starttime);
    $etime = explode(':', $slotstimes->endtime);
    $formdata->sestime = array(
        'starthour' => $stime[0],
        'startminute' => $stime[1],
        'endhour' => $etime[0],
        'endminute' => $etime[1]
    );
    if(!is_siteadmin()){
        $formdata->calendarevent = 1;
        $formdata->studentscanmark = 1;
        $formdata->includeqrcode = 1;
        $formdata->rotateqrcode = 1;
        $formdata->autoassignstatus = 1;
        $formdata->usedefaultsubnet = 1;
        $formdata->automarkcompleted = 0;
        $formdata->preventsharedip = 0;
        $formdata->preventsharediptime = '';
        $formdata->rotateqrcodesecret = local_attendance_random_string();
    }
    $formdata->slotid = $formdata->timeslots;

    $pageparams->action = 1;
    $cmidsql = "SELECT cm.id
                 FROM {course_modules} cm
                 JOIN {modules} m ON cm.module = m.id
                WHERE cm.course = {$formdata->coursesid} AND m.name = 'attendance' "; 
    $id = $DB->get_field_sql($cmidsql);
    $cm = get_coursemodule_from_id('attendance', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $attdata = $DB->get_record('attendance', array('id' => $cm->instance), '*', MUST_EXIST);
    $context = context_module::instance($cm->id);
    $att = new local_attendance_structure($attdata, $cm, $course, $context, $pageparams);

    $sessions = sessions_data_for_add($formdata, $att);

    $sessiondata = $att->add_sessions($sessions);

    if($sessions[0]->id) {
        redirect($CFG->wwwroot .'/local/timetable/individual_session.php?tlid='.$formdata->semid);
    }

}
$renderer = $PAGE->get_renderer('local_timetable');

$sql = "SELECT lp.costcenter, lp.department,lp.subdepartment
            FROM {local_program} lp
            JOIN {local_program_levels} lpl ON lp.id = lpl.programid
           WHERE lpl.id = ?";
$p_sem_data = $DB->get_record_sql($sql, [$semid]);
$role = identify_teacher_role($USER->id);
$stdrole = identify_role($USER->id);
$labelstring = get_config('local_costcenter');
if(is_siteadmin()
  || has_capability('local/costcenter:manage_multiorganizations', $systemcontext)
  || (has_capability('local/costcenter:manage_ownorganization', $systemcontext) && $p_sem_data->costcenter == $USER->open_costcenterid)
  || (has_capability('local/costcenter:manage_owndepartments', $systemcontext) && $p_sem_data->department == $USER->open_departmentid)
  || (has_capability('local/costcenter:manage_ownsubdepartments', $systemcontext) && $p_sem_data->subdepartment == $USER->open_subdepartment)
  || ($role->shortname == 'editingteacher' || $semid == 0)){
    $mform->display();
} else {
    throw new Exception('You dont have permission to access this page');
}

echo $OUTPUT->footer();
