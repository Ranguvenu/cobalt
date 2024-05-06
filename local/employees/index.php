<?php
/**
 * This file is part of eAbyas
 *
 * Copyright eAbyas Info Solutons Pvt Ltd, India
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author eabyas  <info@eabyas.in>
 * @package
 * @subpackage local_employees
 */


require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once($CFG->dirroot . '/local/employees/lib.php');

global $OUTPUT,$CFG,$PAGE;

require_login();

//for pluginchecking calling core_component
$corecomponent = new core_component();

//systemcontest defining
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);

//amd js calling
$PAGE->requires->js_call_amd('local_employees/newemployees', 'load', array());
$PAGE->requires->js_call_amd('theme_bloom/quickactions', 'quickactionsCall');
$PAGE->requires->js_call_amd('local_employees/newemployees', 'load', array());
// $PAGE->requires->js_call_amd('local_employees/datatablesamd', 'load', array());
$PAGE->requires->js_call_amd('local_employees/popup', 'Datatable', array());
$PAGE->requires->js_call_amd('local_employees/popup', 'init', array(array('contextid' => $systemcontext->id,
    'selector' => '.rolesuserpopup')));
$PAGE->requires->js_call_amd('local_employees/studentpopup', 'Datatable', array());
$PAGE->requires->js_call_amd('local_employees/studentpopup', 'init', array(array('contextid' => $systemcontext->id,
    'selector' => '.rolesstudentpopup')));
$PAGE->requires->js_call_amd('local_employees/coursepopup', 'Datatable', array());
$PAGE->requires->js_call_amd('local_employees/coursepopup', 'init', array(array('contextid' => $systemcontext->id,
    'selector' => '.rolescoursepopup')));
$PAGE->requires->js_call_amd('local_employees/courseview', 'Datatable', array());
$PAGE->requires->js_call_amd('local_employees/courseview', 'init', array(array('contextid' => $systemcontext->id,
    'selector' => '.courseviewpopup')));
$PAGE->requires->js_call_amd('local_employees/program', 'Datatable', array());
$PAGE->requires->js_call_amd('local_employees/program', 'init', array(array('contextid' => $systemcontext->id,
    'selector' => '.programpopup')));
$PAGE->requires->js_call_amd('local_costcenter/newcostcenter', 'load', array());
//set url and layout and title
$pageurl = new moodle_url('/local/employees/index.php');
$PAGE->set_url($pageurl);
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('manage_employees', 'local_employees'));

//Header and the navigation bar
$heading = get_string('manage_employees', 'local_employees');
$PAGE->set_heading($heading);
$PAGE->navbar->add($heading);
echo $OUTPUT->header();
$userrenderer = $PAGE->get_renderer('local_employees');

$collapse = true;
$show = '';

if(has_capability('local/employees:create',$systemcontext) || is_siteadmin()){
    //create user, uploademployees and sync errors icons
    echo $userrenderer->employees_page_top_action_buttons();
}

//pluginexist checking
$coursespluginexist = $corecomponent::get_plugin_directory('local','courses');

if(!empty($coursespluginexist)){

    //passing options and dataoptions in filter
    $filterparams = $userrenderer->manage_employees_content(true);

    //for filtering employees we are providing form
    $mform = employees_filters_form($filterparams);
    if($mform->is_cancelled()){
        redirect('index.php');
    }
    echo '<a class="btn-link btn-sm d-flex align-items-center filter_btn" href="javascript:void(0);" data-toggle="collapse" data-target="#local_employees-filter_collapse" aria-expanded="false" aria-controls="local_employees-filter_collapse">
        <span class="filter mr-2">Filters</span>
            <i class="m-0 fa fa-sliders fa-2x" aria-hidden="true"></i>
          </a>';
    echo  '<div class="mt-2 mb-2 collapse '.$show.'" id="local_employees-filter_collapse">
                <div id="filters_form" class="card card-body p-2">';
                    $mform->display();
    echo        '</div>
            </div>';
}
$filterparams['submitid'] = 'form#filteringform';
echo $OUTPUT->render_from_template('local_costcenter/global_filter', $filterparams);
echo $userrenderer->manage_employees_content();
echo $OUTPUT->footer();

