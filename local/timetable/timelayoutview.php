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
require_once($CFG->dirroot . '/local/courses/filters_form.php');

global $CFG, $DB, $USER, $OUTPUT;
$systemcontext =  context_system::instance();
$PAGE->set_pagelayout('standard');
$PAGE->set_context($systemcontext);
require_login();
$PAGE->set_url('/local/timetable/timelayoutview.php');
$PAGE->set_title('Timetable');
$PAGE->set_heading(get_string('pluginname', 'local_timetable'));
$PAGE->requires->js_call_amd('local_timetable/ajaxforms', 'load');
$PAGE->requires->js('/local/timetable/js/tmember_toggle.js');

$currenturl = "{$CFG->wwwroot}/local/timetable/timelayout.php";

$timetable_ob = manage_timetable::getInstance();
$role = identify_teacher_role($USER->id);
$stdrole = identify_role($USER->id);
if (is_siteadmin()
    || has_capability('local/costcenter:manage_multiorganizations', $systemcontext)
    || has_capability('local/costcenter:manage_ownorganization', $systemcontext)
    || has_capability('local/costcenter:manage_owndepartments', $systemcontext)
    || has_capability('local/costcenter:manage_ownsubdepartments', $systemcontext)) {

    echo $OUTPUT->header();

    $renderer = $PAGE->get_renderer('local_timetable');

    $filterparams = $renderer->timetable_view_content(true);

    echo $renderer->get_timetableadd_btns();

    // $thisfilters = array('organizations', 'program', 'batch');

    if (is_siteadmin() || has_capability('local/costcenter:manage_multiorganizations', $systemcontext)) {
        $thisfilters = array('organizations', 'program', 'batch');
    } else if (has_capability('local/costcenter:manage_owndepartments', $systemcontext)) {
        $thisfilters = array('program', 'batch');
    } else if (has_capability('local/costcenter:manage_ownsubdepartments', $systemcontext)) {
        $thisfilters = array('program', 'batch');
    }

    $filterparams['submitid'] = 'formfilteringform';

    $mform = new filters_form(null, array('filterlist' => $thisfilters, 'filterparams' => $filterparams , 'submitid'=>'formfilteringform'));

    if ($filterdata) {
        $collapse = false;
        $show = 'show';
    } else {
        $collapse = true;
        $show = '';
    }

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
    echo $renderer->timetable_view_content();

    echo $OUTPUT->footer();
} else {
    throw new Exception("You dont have permission to access");
    
}
