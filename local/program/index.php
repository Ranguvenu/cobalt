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
require_once($CFG->dirroot . '/local/program/filters_form.php');
require_once($CFG->dirroot . '/local/groups/lib.php');

$systemcontext = context_system::instance();
require_login();
$id = optional_param('id', 0, PARAM_INT); // Program id.
$show = optional_param('show', 0, PARAM_INT);
$groups = optional_param('groups', '', PARAM_RAW);
$status = optional_param('status', '', PARAM_RAW);
$costcenterid = optional_param('costcenterid', '', PARAM_INT);
$departmentid = optional_param('departmentid', '', PARAM_INT);
$subdepartmentid = optional_param('subdepartmentid', '', PARAM_INT);
$PAGE->set_url($CFG->wwwroot . '/local/program/index.php');
$PAGE->set_pagelayout("standard");
$PAGE->set_context($systemcontext);
if (!is_siteadmin() &&
    (!has_capability('local/program:manage_multiorganizations',
        context_system::instance()) &&
    !has_capability('local/costcenter:manage_multiorganizations',
        context_system::instance())) &&
    !(has_capability('local/program:manageprogram', context_system::instance()))) {
    $PAGE->set_title(get_string('my_programs', 'local_program'));
    $PAGE->set_heading(get_string('my_programs', 'local_program'));
} else {
    $PAGE->set_title(get_string('browse_programs', 'local_program'));
    $PAGE->set_heading(get_string('browse_programs', 'local_program'));
}
$PAGE->requires->jquery_plugin('ui-css');
$PAGE->requires->css('/local/program/css/jquery.dataTables.min.css', true);
$PAGE->requires->js_call_amd('local_program/ajaxforms', 'load');
$PAGE->requires->js_call_amd('local_costcenter/newcostcenter', 'load', array());
$PAGE->requires->js_call_amd('local_program/program', 'getstream');

$corecomponent = new core_component();
$bloompluginexist = $corecomponent::get_plugin_directory('theme', 'bloom');
if (!empty($bloompluginexist)) {
    $PAGE->requires->js_call_amd('theme_bloom/quickactions', 'quickactionsCall');
}
$renderer = $PAGE->get_renderer('local_program');
$PAGE->navbar->add(get_string("pluginname", 'local_program'));
echo $OUTPUT->header();


$labelstring = get_config('local_costcenter');
$firstlevel = $labelstring->firstlevel;
$secondlevel = $labelstring->secondlevel;
$thirdlevel = $labelstring->thirdlevel;

$thisfilters = array('dependent_fields','program', 'groups');

$formdata = new stdClass();
$formdata->$firstlevel = $costcenterid;
$formdata->$secondlevel = $departmentid;
$formdata->$thirdlevel = $subdepartmentid;
// $formdata->filteropen_level4department = $l4department;
// $formdata->filteropen_level5department = $l5department;
$formdata->status = $status;

$datasubmitted = data_submitted() ? data_submitted() : $formdata;


if ($datasubmitted->$firstlevel == '_qf__force_multiselect_submission') {
    $datasubmitted->$firstlevel=array();
}

if ($datasubmitted->$secondlevel == '_qf__force_multiselect_submission') {
    $datasubmitted->$secondlevel=array();
}

if ($datasubmitted->$thirdlevel == '_qf__force_multiselect_submission') {
    $datasubmitted->$thirdlevel=array();
}

$mform = new filters_form(new moodle_url('/local/program/index.php',array('formattype'=>$formattype)), array('filterlist'=> $thisfilters)+(array)$datasubmitted);
$filterdata = null;     
if ($mform->is_cancelled()) {
    redirect($CFG->wwwroot . '/local/program/index.php');
} else{
    $filterdata =  $mform->get_data();
    if($filterdata){
        $collapse = false;
        $show = 'show';
    } else{
        $collapse = true;
        $show = '';
    }
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
echo        '</div>
        </div>';
$selectedgroups = '';
if ($filterdata) {
    $selectedsubdepts = !empty($filterdata->$thirdlevel)
        ? implode(',', array_filter($filterdata->$thirdlevel)) : null;
        $selectedcostcenterid = !empty($filterdata->$firstlevel)
        ? implode(',', array_filter($filterdata->$firstlevel)) : null;
        $selecteddepartmentid = !empty($filterdata->$secondlevel)
        ? implode(',', array_filter($filterdata->$secondlevel)) : null;
        $selectedprogram = !empty($filterdata->program)
        ? implode(',', $filterdata->program) : null;
        $selectedstatus = !empty($filterdata->status)
        ? implode(',', $filterdata->status) : null;
        $selectedgroups = !empty($filterdata->groups)
        ? implode(',', $filterdata->groups) : null;
} else {
    $selectedsubdepts = $selectedcostcenterid = $selecteddepartmentid
    = $selectedprogram = $selectedstatus = null;
}
if (!empty($datasubmitted->$firstlevel)) {
    $selectedcostcenterid = $datasubmitted->$firstlevel;
}
if (!empty($datasubmitted->$secondlevel)) {
    $selecteddepartmentid = $datasubmitted->$secondlevel;
}
if (!empty($datasubmitted->$thirdlevel)) {
    $selectedsubdepts = $datasubmitted->$thirdlevel;
}
if (!empty($status)) {
    $selectedstatus = $status;
}
if ($selectedgroups == 0) {
    unset($selectedgroups);
}

echo $renderer->get_program_tabs($selectedsubdepts, $selectedcostcenterid,
    $selecteddepartmentid, $selectedprogram, $selectedstatus, $selectedgroups);

$PAGE->requires->js_call_amd('local_program/program', 'programDatatable',
                    array(array('programstatus' => -1, 'selected_subdepts' => $selectedsubdepts,
                                'selectedcostcenterid' => $selectedcostcenterid,
                                'selecteddepartmentid' => $selecteddepartmentid,
                                'selectedprogram' => $selectedprogram,
                                'selectedstatus' => $selectedstatus, 'selectedgroups' => $selectedgroups)));
echo $OUTPUT->footer();
