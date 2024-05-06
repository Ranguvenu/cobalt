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
 * Take Attendance
 *
 * @package    local_attendance
 * @copyright  2023 Dipanshu Kasera
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__).'/../../config.php');
require_once(dirname(__FILE__).'/locallib.php');
require_once($CFG->dirroot. '/local/timetable/lib.php');

$pageparams = new local_attendance_take_page_params();
$systemcontext = context_system::instance();

$id                     = required_param('id', PARAM_INT);
$pageparams->sessionid  = required_param('sessionid', PARAM_INT);
$pageparams->grouptype  = required_param('grouptype', PARAM_INT);
$pageparams->sort       = optional_param('sort', ATT_SORT_DEFAULT, PARAM_INT);
$pageparams->copyfrom   = optional_param('copyfrom', null, PARAM_INT);
$pageparams->viewmode   = optional_param('viewmode', null, PARAM_INT);
$pageparams->gridcols   = optional_param('gridcols', null, PARAM_INT);
$pageparams->page       = optional_param('page', 1, PARAM_INT);
$pageparams->perpage    = optional_param('perpage', get_config('attendance', 'resultsperpage'), PARAM_INT);

$batchgroup             = optional_param('batch_group', '', PARAM_INT);
if (is_siteadmin()
    || has_capability('local/costcenter:manage_multiorganizations', $systemcontext)
    || has_capability('local/costcenter:manage_ownorganization', $systemcontext)
    || has_capability('local/costcenter:manage_owndepartments', $systemcontext)) {
    $semesterid    = optional_param('semid', '', PARAM_INT);
} else {
    $semesterid = 0;
}

$cm             = get_coursemodule_from_id('attendance', $id, 0, false, MUST_EXIST);
$course         = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$att            = $DB->get_record('attendance', array('id' => $cm->instance), '*', MUST_EXIST);
// Check this is a valid session for this attendance.
$session        = $DB->get_record('attendance_sessions', array('id' => $pageparams->sessionid, 'attendanceid' => $att->id), '*', MUST_EXIST);

require_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/attendance:takeattendances', $context);

$pageparams->group = groups_get_activity_group($cm, true);

$pageparams->init($course->id);
$att = new local_attendance_structure($att, $cm, $course, $PAGE->context, $pageparams);

$allowedgroups = groups_get_activity_allowed_groups($cm);
if (!empty($pageparams->grouptype) && !array_key_exists($pageparams->grouptype, $allowedgroups)) {
     $group = groups_get_group($pageparams->grouptype);
     throw new moodle_exception('cannottakeforgroup', 'attendance', '', $group->name);
}

if (($formdata = data_submitted()) && confirm_sesskey()) {
    $att->take_from_form_data($formdata);

    $group = 0;
    if ($att->pageparams->grouptype != local_attendance_structure::SESSION_COMMON) {
        $group = $att->pageparams->grouptype;
    } else {
        if ($att->pageparams->group) {
            $group = $att->pageparams->group;
        }
    }

    // $totalusers = count_enrolled_users(context_module::instance($cm->id), 'mod/attendance:canbelisted', $group);
    $totalusers = count_enrolled_group_users(context_module::instance($cm->id), 'mod/attendance:canbelisted', $batchgroup);

    $usersperpage = $att->pageparams->perpage;
    if (!empty($att->pageparams->page) && $att->pageparams->page && $totalusers && $usersperpage) {
        $numberofpages = ceil($totalusers / $usersperpage);
        if ($att->pageparams->page < $numberofpages) {
            $params = array(
                'semid' => $semesterid,
                'sessionid' => $att->pageparams->sessionid,
                'grouptype' => $att->pageparams->grouptype,
                'batch_group' => $batchgroup);
            $params['page'] = $att->pageparams->page + 1;
            redirect($att->url_take($params), get_string('moreattendance', 'attendance'));
        }
    }

    // redirect($att->url_manage(), get_string('attendancesuccess', 'attendance'));
    $url = $CFG->wwwroot .'/local/timetable/individual_session.php?tlid='.$semesterid;
    redirect($url, get_string('attendancesuccess', 'attendance'));
}

$PAGE->set_url($att->url_take());
$PAGE->set_title($course->shortname. ": ".$att->name);
$PAGE->set_heading($course->fullname);
$PAGE->set_cacheable(true);
$PAGE->navbar->add($att->name);
$PAGE->navbar->ignore_active(true);
$PAGE->set_secondary_navigation(false);

$output = $PAGE->get_renderer('local_attendance');
$tabs = new local_attendance\output\tabs($att);
$sesstable = new local_attendance\output\take_data($att, $batchgroup, $semesterid);

// Output starts here.

echo $output->header();

echo html_writer::link(new moodle_url('/local/timetable/individual_session.php?tlid='.$semesterid),''.get_string('back', 'local_timetable').'',array('id'=>'local_timetable_batchwisebu', 'class' => 'btn btn-primary'));

echo $output->render($tabs);
echo $output->render($sesstable);

echo $output->footer();
