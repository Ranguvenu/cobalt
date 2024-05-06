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
 * Version information
 *
 * @package    local_groups
 * @copyright  2022 eAbyas Info Solutions Pvt. Ltd.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once("../../config.php");
require_once("lib.php");
require_once($CFG->libdir.'/adminlib.php');

global $PAGE,$USER;

$PAGE->requires->js_call_amd('theme_bloom/quickactions', 'quickactionsCall');
$PAGE->requires->js_call_amd('local_groups/newgroup', 'load', array());
$PAGE->requires->js_call_amd('local_groups/popup', 'Datatable', array());
$PAGE->requires->js_call_amd('local_costcenter/newcostcenter', 'load', array());
$PAGE->requires->css('/local/costcenter/css/jquery.dataTables.min.css');
$PAGE->requires->css(new moodle_url('https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css'));
$PAGE->requires->js(new moodle_url('https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js'),true);
$PAGE->requires->js(new moodle_url('https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js'),true);
$PAGE->requires->js(new moodle_url('https://canvasjs.com/assets/script/canvasjs.min.js'),true);

$contextid = optional_param('contextid', 0, PARAM_INT);
$page = optional_param('page', 0, PARAM_INT);
require_once($CFG->dirroot . '/local/courses/filters_form.php');
require_login();
if ($contextid) {
    $context = context::instance_by_id($contextid, MUST_EXIST);
} else {
    $context = context_system::instance();
}
$PAGE->requires->js_call_amd('local_groups/popup', 'init',array(array('contextid' => $context->id, 'selector' => '#batchuserpopup')));

if ($context->contextlevel != CONTEXT_COURSECAT && $context->contextlevel != CONTEXT_SYSTEM) {
    throw new moodle_exception('invalidcontext');
}
$manager = has_capability('moodle/cohort:manage', $context);
if (!$manager) {
    require_capability('moodle/cohort:view', $context);
}
$PAGE->set_context($context);
$PAGE->set_title(get_string('cohorts', 'local_groups'));
$PAGE->set_heading(get_string('cohorts', 'local_groups'));
$baseurl = new moodle_url('/local/groups/index.php');
$PAGE->set_url($baseurl);
$PAGE->navbar->add(get_string("pluginname", 'local_groups'));
$PAGE->requires->js_call_amd('local_groups/renderselections', 'init');
echo $OUTPUT->header();

$renderer = $PAGE->get_renderer('local_groups');
echo $renderer->get_group_btns();
$filterparams = $renderer->managegroups_content(true);

$filterparams['submitid'] = 'formfilteringform';
if (is_siteadmin() || has_capability('local/costcenter:manage_multiorganizations', $context)) {
    $thisfilters = array('dependent_fields', 'program', 'batch');
} else if (has_capability('local/costcenter:manage_ownorganization', $context)) {
    $thisfilters = array('dependent_fields', 'batch', 'program' /*'status'*/);
} else if (has_capability('local/costcenter:manage_owndepartments', $context)) {
    $thisfilters = array('dependent_fields', 'program', 'batch');
} else if (has_capability('local/costcenter:manage_ownsubdepartments', $context)) {
    $thisfilters = array('program', 'batch');
}

$mform = new filters_form(null, array('filterlist' => $thisfilters, 'filterparams' => $filterparams , 'submitid'=>'formfilteringform'));
$show = '';
if ($mform->is_cancelled()) {
    redirect('index.php');
} else {
    $filterdata =  $mform->get_data();

    if ($filterdata) {
        $collapse = false;
    } else {
        $collapse = true;
    }
}

if (empty($filterdata) && !empty($jsonparam)) {
    $filterdata = json_decode($jsonparam);
    foreach ($thisfilters AS $filter) {
        if (empty($filterdata->$filter)) {
            unset($filterdata->$filter);
        }
    }

    $mform->set_data($filterdata);
}
if ($filterdata) {
    $collapse = false;
    $show = 'show';
} else {
    $collapse = true;
    $show = '';
}

echo '<a class="btn-link btn-sm d-flex align-items-center filter_btn" href="javascript:void(0);"
        data-toggle="collapse" data-target="#local_courses-filter_collapse"
        aria-expanded="false" aria-controls="local_courses-filter_collapse">
            <span class="filter mr-2">Filters</span>
        <i class="m-0 fa fa-sliders fa-2x" aria-hidden="true"></i>
    </a>';

echo  '<div class="collapse '.$show.'" id="local_courses-filter_collapse">
        <div id="filters_form" class="card card-body p-2">';
            $mform->display();
echo    '</div>
       </div>';
       
echo $OUTPUT->render_from_template('local_groups/global_filter', $filterparams);
echo $renderer->managegroups_content();
echo $OUTPUT->footer();
