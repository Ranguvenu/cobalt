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
// require_once($CFG->dirroot . '/local/timetable/timetable_hierform.php');
require_once($CFG->dirroot . '/local/timetable/classes/form/timetable_form.php');

$id = optional_param('id', -1, PARAM_INT);    // user id; -1 if creating new tool
$semid = optional_param('semesterid', '', PARAM_INT);
$delete = optional_param('delete', 0, PARAM_BOOL);
$confirm = optional_param('confirm', 0, PARAM_BOOL);
$visible = optional_param('visible', -1, PARAM_INT);
$scid = optional_param('scid', 0, PARAM_INT);
$view = optional_param('view', '', PARAM_TEXT);
$batchid = optional_param('bid', 0, PARAM_INT);

global $CFG, $DB, $USER, $OUTPUT;
$systemcontext =  context_system::instance();
$PAGE->set_pagelayout('standard');
$PAGE->set_context($systemcontext);

require_login();

$PAGE->set_url('/local/timetable/timelayout.php');
$PAGE->set_title(get_string('semester_timetable', 'local_timetable'));
$PAGE->set_heading(get_string('semester_timetable', 'local_timetable'));
$PAGE->navbar->add(get_string('timetablelayout', 'local_timetable'), new moodle_url('/local/timetable/timelayoutview.php'));
$PAGE->navbar->add(get_string('semester_timetable', 'local_timetable'), new moodle_url('/local/timetable/timelayout.php'));

//this is the return url
$currenturl = "{$CFG->wwwroot}/local/timetable/timelayoutview.php";

// calling manage_dept class instance.....
$timetable_ob = manage_timetable::getInstance();

$hier1 = new hierarchy();

echo $OUTPUT->header();

$PAGE->requires->js_call_amd('local_costcenter/newcostcenter', 'load', array());

if (empty($semid)) {
    $semesterid = $DB->get_field('local_timeintervals', 'semesterid', ['id' => $id]);
} else {
    $semesterid = $semid;
}

$timetablehier_ob =  new timetable_form(null, array('id' => $id,'semesterid' => $semesterid));

if ($id > 0) {
    $timelayoutinfo = $DB->get_record('local_timeintervals',array('id' => $id));
    $timetable_ob->timetable_converting_timeformat($timelayoutinfo);
    $timetablehier_ob->set_data($timelayoutinfo);
}

if ($timetablehier_ob->is_cancelled()) {
    redirect($currenturl);
} else if($data = $timetablehier_ob->get_data()){
    $timetable_ob = manage_timetable::getInstance();
    if ($data->id <= 0) {
        $timetable_ob->add_timeintervals($data);
        $timetable_ob->success_error_msg($res, 'success_msg_addingtimeintervals', 'error_msg_addingtimeintervals', $currenturl);
    } else {
        $timetable_ob->timetable_update_timeintervals($data);
        $timetable_ob->success_error_msg($res, 'success_msg_addingtimeintervals', 'error_msg_addingtimeintervals', $currenturl);
    }
}
$sql = "SELECT lp.costcenter, lp.department, lp.subdepartment
            FROM {local_program} lp
            JOIN {local_program_levels} lpl ON lp.id = lpl.programid
           WHERE lpl.id = ?";
$p_sem_data = $DB->get_record_sql($sql, [$semesterid]);
$role = identify_teacher_role($USER->id);
$stdrole = identify_role($USER->id);
$labelstring = get_config('local_costcenter');
if ($id > 0) {
    if (is_siteadmin()
      || has_capability('local/costcenter:manage_multiorganizations', $systemcontext)
      || (has_capability('local/costcenter:manage_ownorganization', $systemcontext) && $p_sem_data->costcenter == $USER->open_costcenterid)
      || (has_capability('local/costcenter:manage_owndepartments', $systemcontext) && $p_sem_data->department == $USER->open_departmentid) 
      || (has_capability('local/costcenter:manage_ownsubdepartments', $systemcontext) && $p_sem_data->subdepartment == $USER->open_subdepartment)) {
        $timetablehier_ob->display();
    } else {
        throw new Exception('You dont have permission to access this page');
    }
} else {
    if (is_siteadmin()
      || has_capability('local/costcenter:manage_multiorganizations', $systemcontext)
      || (has_capability('local/costcenter:manage_ownorganization', $systemcontext))
      || (has_capability('local/costcenter:manage_owndepartments', $systemcontext)) 
      || (has_capability('local/costcenter:manage_ownsubdepartments', $systemcontext))) {
            $timetablehier_ob->display();
    }  else {
            throw new Exception('You dont have permission to access this page');
    }
}
echo $OUTPUT->footer();
