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
 * @package BizLMS
 * @subpackage local_location
 */

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/courses/filters_form.php');
global $CFG,$PAGE;
require_once($CFG->dirroot . '/local/location/lib.php');
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
$PAGE->set_url($CFG->wwwroot .'/local/location/index.php');
$PAGE->set_title(get_string('manage_building', 'local_location'));
$PAGE->set_heading(get_string('manage_building', 'local_location'));
$PAGE->navbar->ignore_active();
require_login();

$renderer = $PAGE->get_renderer('local_location');
$institute = new local_location\event\location();
$PAGE->requires->jquery();
$PAGE->requires->js('/local/location/js/delconfirm.js');
$PAGE->requires->js('/local/location/js/jquery.min.js',TRUE);
$PAGE->requires->js('/local/location/js/datatables.min.js', TRUE);
$PAGE->requires->css('/local/location/css/datatables.min.css');
$id = optional_param('id',0,PARAM_INT);
$delete = optional_param('delete', 0, PARAM_INT);
$PAGE->navbar->add(get_string('pluginname', 'local_timetable'), new moodle_url('/local/timetable/timelayoutview.php'));
$PAGE->navbar->add( get_string('manage_building', 'local_location'));

echo $OUTPUT->header();

if (has_capability('local/location:manageinstitute', $systemcontext)
    || has_capability('local/location:viewinstitute', $systemcontext)
    || has_capability('local/location:manageroom', $systemcontext)
    || has_capability('local/location:viewroom', $systemcontext)) {

    if ((has_capability('local/location:manageinstitute', $systemcontext))|| has_capability('local/location:viewinstitute', $systemcontext)) {
        $PAGE->requires->js_call_amd('local_location/newinstitute', 'load', array());
        echo "<ul class='course_extended_menu_list'>";
        if ((has_capability('local/location:manageroom', $systemcontext) || has_capability('local/location:viewroom', $systemcontext))) {
            echo "<li>
                <div class = 'coursebackup course_extended_menu_itemcontainer' >
                    <a href='".$CFG->wwwroot."/local/location/room.php' class='course_extended_menu_itemlink create_ilt' title='".get_string('room_title','local_location')."'><i class='icon fa fa-simplybuilt'></i></a>
                </div>
            </li>";
        }
        if(has_capability('local/location:manageinstitute', $systemcontext)){
    		echo "<li>
    			<div class = 'coursebackup course_extended_menu_itemcontainer'>
    				<a data-action='createinstitutemodal' data-value='0' class='course_extended_menu_itemlink' onclick ='(function(e){ require(\"local_location/newinstitute\").init({selector:\"createinstitutemodal\", contextid:$systemcontext->id, instituteid:$id}) })(event)' title='".get_string('createinstitute', 'local_location')."'><i class='icon fa fa-plus' aria-hidden='true'></i>
    				</a>
    			</div>
    		</li>";
        }
    	echo "</ul>";
    }

    $filterparams = $renderer->location_view_content(true);

    if (is_siteadmin() || has_capability('local/costcenter:manage_multiorganizations', $systemcontext)) {
        $thisfilters = array('dependent_fields', 'location_name', 'location_type');
    } else if (has_capability('local/costcenter:manage_ownorganization', $systemcontext)) {
        $thisfilters = array('dependent_fields', 'location_name', 'location_type');
    } else if (has_capability('local/costcenter:manage_owndepartments', $systemcontext)) {
        $thisfilters = array('dependent_fields', 'location_name', 'location_type');
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

    echo $OUTPUT->render_from_template('local_location/global_filter', $filterparams);
    echo $renderer->location_view_content();
} else {
    throw new Exception("You dont have permission to access this page");
    
}

echo $OUTPUT->footer();
