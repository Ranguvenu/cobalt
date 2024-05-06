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
 * @package
 * @author     eAbyas Info Solutions
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
require_once(dirname(__FILE__) . '/../../config.php');
use local_program\program;
$programid = optional_param('bcid','', PARAM_INT);
$systemcontext = context_system::instance();
require_login();
global $DB, $CFG;
$PAGE->set_url('/local/program/view.php', array('bcid' => $programid));
$PAGE->set_context($systemcontext);
$PAGE->set_title(get_string('programs', 'local_program'));
// $PAGE->requires->js('/local/program/js/checkbox.js');
$PAGE->requires->js_call_amd('local_program', 'load', array());

$renderer = $PAGE->get_renderer('local_program');
$program=$renderer->programview_check($programid);
$baseurl = $CFG->wwwroot.'/local/program/view.php?bcid='.$programid;

if(empty($programid)){
    $PAGE->navbar->add(get_string("dashboard", 'local_program'), new moodle_url('/my/index.php')); 
    echo $OUTPUT->header();
    echo '<h4>you are not assigned to any program</h4>';
    echo '<h5><a class="btn pull-right" href="'.$CFG->wwwroot.'">back</a></h5>';
    echo $OUTPUT->footer();
}else{
    $PAGE->navbar->ignore_active();
    if ((has_capability('local/program:manageprogram', context_system::instance()) || is_siteadmin())) {
        $PAGE->navbar->add(get_string("pluginname", 'local_program'), new moodle_url('/local/program/index.php'));
$PAGE->navbar->add($program->name, new moodle_url('/local/program/view.php', array('bcid' => $programid)));
$PAGE->navbar->add(get_string("user_semesters_report", 'local_program'));
    }
    $PAGE->set_heading($program->name);
    $renderer = $PAGE->get_renderer('local_program');
    $content = $renderer->get_sem_wise_program_table($programid);
    echo $OUTPUT->header();
    // echo "<div class='coursebackup course_extended_menu_itemcontainer'>
    //         <a id='extended_menu_syncusers' title='{{# str}}bulkuploaduser, local_program{{/ str}}' class='course_extended_menu_itemlink' href='".$CFG->wwwroot."/local/program/sync/hrms_async.php?bcid=$programid'>
    //             <i class='icon fa fa-users fa-fw usergrp_icon' aria-hidden='true' aria-label=''></i>
    //         </a>
    //     </div>";
echo '<form action="' . $baseurl . '" method="POST">';
echo '<input type="hidden" name="sesskey" value="' . sesskey() . '" />';
// echo '<div class="d-flex justify-content-end">';
echo '<a style="float:right;" href = "'.$CFG->wwwroot.'/local/program/users.php?download=1&type=semesterwise&format=xls&bcid='.$programid.'"><i class="icon fa fa-download" aria-hidden="true" title="User-wise semesters-report"></i></a>';
echo html_writer::table($content);
echo '<div class="buttons">';
echo '<input type="submit" class="btn btn-primary" name="submit" value="' . get_string('savechanges') . '"/>';
echo '</div></form>';

    echo $OUTPUT->footer();
}


