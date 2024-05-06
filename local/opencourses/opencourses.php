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
 * @package odl
 * @subpackage local_courses
 */



require_once('../../config.php');
require_once($CFG->dirroot . '/local/courses/filters_form.php');
global $DB, $OUTPUT, $USER, $CFG;
$id        = optional_param('id', 0, PARAM_INT);
$deleteid = optional_param('delete', 0, PARAM_INT);
$confirm = optional_param('confirm', 0, PARAM_INT);
$jsonparam    = optional_param('jsonparam', '', PARAM_RAW);
$status = optional_param('status', '', PARAM_RAW);
$costcenterid = optional_param('costcenterid', '', PARAM_INT);
$departmentid = optional_param('departmentid', '', PARAM_INT);
$subdepartmentid = optional_param('subdepartmentid', '', PARAM_INT);
$formattype = optional_param('formattype', 'card', PARAM_TEXT);
if ($formattype == 'card') {
    $formattype_url = 'table';
    $display_text = get_string('listtype', 'local_opencourses');
    $display_texticon = get_string('listtypeicon', 'local_opencourses');
} else {
    $formattype_url = 'card';
    $display_text = get_string('cardtype', 'local_opencourses');
    $display_texticon = get_string('cardtypeicon', 'local_opencourses');
}



require_login();

$systemcontext = context_system::instance();
if(!has_capability('local/opencourses:view', $systemcontext)){
    print_error("You don't have permissions to view this page.");
}


$PAGE->set_pagelayout("standard");

$PAGE->set_context($systemcontext);
$PAGE->set_url('/local/opencourses/opencourses.php');
$PAGE->set_title(get_string('opencourses','local_opencourses'));
$PAGE->set_heading(get_string('manage_opencourses', 'local_opencourses'));
$PAGE->requires->jquery();
$PAGE->requires->js_call_amd('local_opencourses/courseAjaxform', 'load');
$PAGE->requires->js_call_amd('theme_bloom/quickactions', 'quickactionsCall');
$PAGE->requires->js_call_amd('local_costcenter/fragment', 'init', array());
$PAGE->requires->js_call_amd('local_opencourses/courses', 'load', array());
$PAGE->requires->js_call_amd('local_costcenter/newcostcenter', 'load', array());
$PAGE->navbar->ignore_active();
$PAGE->navbar->add(get_string('manage_courses', 'local_opencourses'));

 


if($deleteid && $confirm && confirm_sesskey()){
    $course = $DB->get_record('course', array('id'=>$deleteid));
    delete_course($course, false);
    if($course){
        $custom_delete = new local_courses\action\delete();
        $delete = $custom_delete->delete_coursedetails($deleteid);
     }

    $course_detail = new stdClass();
    $sql = $DB->get_field('user', 'firstname', array('id' =>$USER->id));
    $course_detail->userid = $sql;
    $course_detail->courseid = $deleteid;
    $description = get_string('descptn', 'local_courses', $course_detail);
    $logs = new local_courses\action\insert();
    $insert_logs = $logs->local_custom_logs('delete', 'course', $description, $deleteid);
    redirect($CFG->wwwroot . '/local/opencourses/opencourses.php'); 
}
$renderer = $PAGE->get_renderer('local_opencourses');

 
$extended_menu_links = '';  
$extended_menu_links = '<div class="course_contextmenu_extended">
            <ul class="course_extended_menu_list">';
            
if (((has_capability('local/costcenter:create', 
    $systemcontext)&&has_capability('local/courses:bulkupload', 
    $systemcontext)&&has_capability('local/courses:manage', 
    $systemcontext)&&has_capability('moodle/course:create', 
    $systemcontext)&&has_capability('moodle/course:update', 
    $systemcontext)))|| is_siteadmin()) {

    $extended_menu_links .= '<li><div class="courseedit course_extended_menu_itemcontainer">
                                <a id="extended_menu_createcourses" class="pull-right course_extended_menu_itemlink" title = "'.get_string('uploadcourses','local_opencourses').'" href = '.$CFG->wwwroot.'/local/opencourses/upload/index.php>
                                    <i class="icon fa fa-upload upload_icon" aria-hidden="true"></i>
                                </a>
                            </div></li>';
} 



if (is_siteadmin() ||(
        has_capability('moodle/course:create', 
        $systemcontext)&& has_capability('moodle/course:update', 
        $systemcontext)&&has_capability('local/courses:manage', 
        $systemcontext))) {
        $extended_menu_links .= '<li><div class="courseedit course_extended_menu_itemcontainer">
                                    <a id="extended_menu_createcourses" class="pull-right course_extended_menu_itemlink" title = "'.get_string('create_newcourse','local_opencourses').'" data-action="createcoursemodal" onclick="(function(e){ require(\'local_opencourses/courseAjaxform\').init({contextid:'.$systemcontext->id.', component:\'local_opencourses\', callback:\'custom_opencourse_form\', form_status:0, plugintype: \'local\', pluginname: \'opencourses\'}) })(event)">
                                        <span class="createicon">
                                        <i class="icon fa fa-book"></i>
                                        <i class="fa fa-plus createiconchild" aria-hidden="true"></i>
                                        </span>
                                    </a>
                                </div></li>';
}

 
$extended_menu_links .= '
        </ul>
    </div>';

  

echo $OUTPUT->header();
 
echo $extended_menu_links;

$filterparams = $renderer->get_catalog_opencourses(true, $formattype);
 

if (is_siteadmin()) {

    $thisfilters = array('dependent_fields','opencourses' /*'categories', 'status'*/);
} else if (has_capability('local/costcenter:manage_ownorganization', $systemcontext)) {
 
    $thisfilters = array('dependent_fields','opencourses' /*'categories', 'status' ,*/);
} else if (has_capability('local/costcenter:manage_owndepartments', $systemcontext)) {

    $thisfilters = array('dependent_fields','opencourses'  /*'categories', 'status',*/ );
} else {
    $thisfilters = array('organizations','opencourses' /*'categories', 'status' */);
}
$thisfilters[] = 'hrmsrole';

$mform = new filters_form(null, array('filterlist'=> $thisfilters, 'filterparams' => $filterparams));
     
if ($mform->is_cancelled()) {
    redirect($CFG->wwwroot . '/local/opencourses/opencourses.php');
} else {
    $filterdata =  $mform->get_data();
    if($filterdata){
        $collapse = false;
    } else{
        $collapse = true;
    }
}
if (empty($filterdata) && !empty($jsonparam)) {
    $filterdata = json_decode($jsonparam);
    foreach($thisfilters AS $filter){
        if(empty($filterdata->$filter)){
            unset($filterdata->$filter);
        }
    }
    $mform->set_data($filterdata);
}
if (!empty($costcenterid) || !empty($status) || !empty($departmentid) || !empty($subdepartmentid)) {   
        $formdata = new stdClass();
        $formdata->organizations = $costcenterid;
        $formdata->departments = $departmentid;
        $formdata->subdepartment = $subdepartmentid;
        $formdata->status = $status;
        $mform->set_data($formdata);
}
if ($filterdata) {
    $collapse = false;
    $show = 'show';
} else {
    $collapse = true;
    $show = '';
}

echo '<a class="btn-link btn-sm d-flex align-items-center filter_btn" href="javascript:void(0);" data-toggle="collapse" data-target="#local_courses-filter_collapse" aria-expanded="false" aria-controls="local_courses-filter_collapse">
           <span class="filter mr-2">Filters</span>
        <i class="m-0 fa fa-sliders fa-2x" aria-hidden="true"></i>

      </a>';
echo  '<div class="collapse '.$show.'" id="local_courses-filter_collapse">
            <div id="filters_form" class="card card-body p-2">';
                $mform->display();
echo        '</div>
        </div>';
$filterparams['submitid'] = 'form#filteringform';
echo $OUTPUT->render_from_template('local_costcenter/global_filter', $filterparams);
if (is_siteadmin() || (
        has_capability('moodle/course:create', $systemcontext) && has_capability('moodle/course:update', $systemcontext) && has_capability('local/courses:manage', $systemcontext))) {

    
    }
echo $renderer->get_catalog_opencourses(false, $formattype);

echo $OUTPUT->footer();
