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

$timelayoutid = required_param('tlid',PARAM_INT);
$scheduledid = optional_param('scellid',0, PARAM_INT);

global $CFG, $DB, $USER, $OUTPUT;
$systemcontext =  context_system::instance();
$PAGE->set_pagelayout('base');
$PAGE->set_context($systemcontext);
require_login();
$PAGE->set_url('/local/timetable/timelayoutedit.php', array('tlid' => $timelayoutid));
$PAGE->set_title(get_string('semester_timetable', 'local_timetable'));
$PAGE->set_heading(get_string('semester_timetable', 'local_timetable'));
$PAGE->navbar->add(get_string('timetablelayout', 'local_timetable'), new moodle_url('/local/timetable/timelayoutview.php'));
$PAGE->navbar->add(get_string('individualsession', 'local_timetable'), new moodle_url('/local/timetable/individual_session.php?tlid='.$timelayoutid));
$PAGE->navbar->add(get_string('semester_timetable', 'local_timetable'), new moodle_url('/local/timetable/timelayoutedit.php'));
$PAGE->requires->js_call_amd('local_timetable/ajaxforms', 'load');
$PAGE->requires->js_call_amd('local_costcenter/newcostcenter', 'load', array());

$returnurl = new moodle_url('/local/timetable/timelayoutedit.php', array('tlid' => $timelayoutid));
$currenturl = "{$CFG->wwwroot}/local/timetable/timelayoutedit.php";

echo $OUTPUT->header();

$timelayoutinfo = $DB->get_record('local_timeintervals',array('semesterid' => $timelayoutid));
$seminfo = $DB->get_record('local_program_levels', array('id' => $timelayoutinfo->semesterid));


$layoutheading= '<ul class="local_timetable_layoutheading">';
$programname = $DB->get_field('local_program', 'name', array('id'=>$timelayoutinfo->programid));
$layoutheading .= "<li>$programname</li>";
$batchname = $DB->get_field('cohort', 'name', array('id'=>$timelayoutinfo->batchid));
$offereingperiod =$DB->get_record('local_program_levels',array('id'=>$timelayoutinfo->semesterid));
if($seminfo->startdate && $seminfo->enddate){
  $startdate = $seminfo->startdate;
  $enddate = $seminfo->enddate;
}
$sql = "SELECT lp.costcenter, lp.department, lp.subdepartment
          FROM {local_program} lp
          JOIN {local_program_levels} lpl ON lp.id = lpl.programid
         WHERE lpl.id = ?";
$p_sem_data = $DB->get_record_sql($sql, [$timelayoutid]);
$role = identify_teacher_role($USER->id);
$stdrole = identify_role($USER->id);
$labelstring = get_config('local_costcenter');

if(is_siteadmin()
  || has_capability('local/costcenter:manage_multiorganizations', $systemcontext)
  || (has_capability('local/costcenter:manage_ownorganization', $systemcontext) && $p_sem_data->costcenter == $USER->open_costcenterid)
  || (has_capability('local/costcenter:manage_owndepartments', $systemcontext) && $p_sem_data->department == $USER->open_departmentid)
  || (has_capability('local/costcenter:manage_ownsubdepartments', $systemcontext) && $p_sem_data->subdepartment == $USER->open_subdepartment)){

    $layoutheading .= "<li>$seminfo->level<span> (". date('d M Y',$startdate)." to ".date('d M Y',$enddate)."  )</span></li>";
    $layoutheading .= "</ul>";

    echo $layoutheading;

    echo html_writer::link(new moodle_url('/local/timetable/individual_session.php?tlid='.$timelayoutid),''.get_string('back', 'local_timetable').'',array('id'=>'local_timetable_batchwisebu', 'class' => 'btn btn-primary'));

    /*echo html_writer::link(new moodle_url('/local/timetable/timetable_view.php?text=default&semid='.$timelayoutid),''.get_string('student_view', 'local_timetable').'',array('id'=>'local_timetable_batchwisebu', 'class' => 'btn btn-primary float-right mr-2'));*/

    echo local_timetable_timetablelayout_display($timelayoutid);
    echo "<div>".get_string('repeatenote', 'local_timetable')."</div>";
    echo $OUTPUT->footer();
} else {  
  throw new Exception('You dont have permission to access this page');
}
