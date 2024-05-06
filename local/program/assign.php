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
 * @package    local_program
 * @copyright  2022 eAbyas Info Solutions Pvt. Ltd.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('NO_OUTPUT_BUFFERING', true);
require('../../config.php');

require_once($CFG->dirroot.'/local/program/classes/program.php');
require_once($CFG->dirroot.'/local/lib.php');
require_once($CFG->dirroot . '/local/courses/filters_form.php');
$levelid = optional_param('levelid','', PARAM_INT);
$costcenterid = optional_param('costcenterid','', PARAM_INT);
$id = optional_param('batchid','', PARAM_INT);
$returnurl = optional_param('returnurl', '', PARAM_LOCALURL);
$submitvalue = optional_param('submit_value', '', PARAM_RAW);
$add = optional_param('add', array(), PARAM_RAW);
$remove = optional_param('remove', array(), PARAM_RAW);
$view = optional_param('view', 'page', PARAM_RAW);
$type = optional_param('type', '', PARAM_RAW);
$lastitem = optional_param('lastitem', 0, PARAM_INT);
require_login();
$sesskey = sesskey();
if($id){
    $id = $id;
}else {
    if($add){
        foreach ($add as $key => $value) {
            $id = $DB->get_field_sql("SELECT cohortid FROM {cohort_members} WHERE userid = $value");
            $costcenterid = $DB->get_field('user','open_costcenterid',array('id' => $value));
        }
    }
     if($remove){
        foreach ($remove as $key => $value) {
            $id = $DB->get_field_sql("SELECT cohortid FROM {cohort_members} WHERE userid = $value");
            $costcenterid = $DB->get_field('user','open_costcenterid',array('id' => $value));
        }
    }
}
$programid = $DB->get_field('local_program','id',array('batchid' => $id));
if($levelid){
    $levelid = $levelid;
} else{
    $levelid = $DB->get_field('local_program_levels','id',array('programid'=>$programid,'active' => 1));
}
$sesskey = sesskey();
$level = $DB->get_record('local_program_levels', array('id' => $levelid), '*', MUST_EXIST);

$groups = $DB->get_record('cohort', array('id' => $id), '*', MUST_EXIST);
$context = context::instance_by_id($groups->contextid, MUST_EXIST);

require_capability('moodle/cohort:assign', $context);

$PAGE->set_context($context);
$PAGE->set_url('/local/program/assign.php', array('batchid' => $id, 'levelid' =>$levelid, 'costcenterid' => $costcenterid));
$PAGE->set_pagelayout('standard');
$url = new moodle_url('/local/program/assign.php', array('batchid' => $id, 'levelid' =>$levelid, 'costcenterid' => $costcenterid));

if ($returnurl) {
    $returnurl = new moodle_url($returnurl);
} else {
    $returnurl = new moodle_url('/local/program/index.php', array('contextid' => $groups->contextid));
}

if (!empty($groups->component)) {
    // We can not manually edit groupss that were created by external systems, sorry.
    redirect($returnurl);
}

$groupsdetails = $DB->get_record('local_groups', array('cohortid' => $id));

// I.e other than admin eg:Org.Head.
if (!(is_siteadmin()) && has_capability('local/costcenter:manage_ownorganization', context_system::instance())) {
    if ($groupsdetails->costcenterid != $USER->open_costcenterid) {
        throw new moodle_exception(get_string('pagecantaccess', 'local_groups'));
    }
}

// For Dept.Head.
if (!(is_siteadmin()) && has_capability('local/costcenter:manage_owndepartments', context_system::instance())) {
    if ($groupsdetails->costcenterid != $USER->open_costcenterid) {
        throw new moodle_exception(get_string('donthavepermissions', 'local_groups'));
    }
}

if (optional_param('cancel', false, PARAM_BOOL)) {
    redirect($url);
}

$PAGE->navbar->add(get_string('pluginname', 'local_program'),
                    new moodle_url('/local/program/view.php',
                        array('bcid' => $level->programid)
                    )
                );
$PAGE->navbar->add(get_string('userenrolment', 'local_program'));
$PAGE->set_title(get_string('assignusersemwise', 'local_program'));
$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->css('/local/classroom/css/bootstrap-duallistbox.css');
if ($view == 'ajax') {
    $options = (array)json_decode($_GET["options"], false);
    $selectfromusers = local_program_users($type, $id, $options, false, $offset1 = -1, $perpage = 50, $lastitem);
    echo json_encode($selectfromusers);
    exit;
}

if (!$add && !$remove) {
    // $PAGE->set_heading(get_string('EnrollFacultiesto','local_program', $course->fullname));
    // echo $OUTPUT->heading(get_string('semestername', 'local_program',$level->level));
     $PAGE->set_heading(get_string('Enrollstudentstosemester', 'local_program',$level->level));
}
echo $OUTPUT->header();
if ($groups) {
    $labelstring = get_config('local_costcenter');
    $firstlevel = $labelstring->$firstlevel;
    $secondlevel = $labelstring->$secondlevel;
    $thirdlevel = $labelstring->$thirdlevel;
    $organization = null;
    $department = null;
    $btchemail = null;
    $idnumber = null;
    $uname = null;
    $filterlist = array('dependent_fields','email');
    // $filterlist = get_filterslist();
    $formdata = new stdClass();
$formdata->$firstlevel = $costcenterid;
$formdata->$secondlevel = $departmentid;
$formdata->$thirdlevel = $subdepartmentid;

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
    // $mform = new filters_form($url, array(
    //                                     'filterlist' => $filterlist,
    //                                     'enrolid' => 0,
    //                                     'courseid' => $id,
    //                                     'action' => 'user_enrolment'
    //                                 )
    //                         );
    $mform = new filters_form($PAGE->url, array('filterlist' => $filterlist, 'enrolid' => 0, 'courseid' => $id, 'filterparams' => $filterparams, 'action' => 'user_enrolment'), 'post', '', null, true, (array)data_submitted());
    if ($mform->is_cancelled()) {
        redirect($PAGE->url);
    } else {
        $labelstring = get_config('local_costcenter');
        $firstlevel = $labelstring->firstlevel;
        $secondlevel = $labelstring->secondlevel;
        $thirdlevel = $labelstring->thirdlevel;
        $filterdata = $mform->get_data();
        if ($filterdata) {
            $collapse = false;
            $show = 'show';
        } else {
            $collapse = true;
            $show = '';
        }
        $organization = !empty($filterdata->$firstlevel) ? implode(',', $filterdata->$firstlevel) : null;
        $department = !empty($filterdata->$secondlevel) ? implode(',', $filterdata->$secondlevel) : null;
        $subdepartment = !empty($filterdata->$thirdlevel) ? implode(',', $filterdata->$thirdlevel) : null;
        $email = !empty($filterdata->email) ? implode(',', $filterdata->email) : null;
        $filtergroup = !empty($filterdata->groups) ? implode(',', $filterdata->groups) : null;
        $idnumber = !empty($filterdata->idnumber) ? implode(',', $filterdata->idnumber) : null;
        $uname = !empty($filterdata->users) ? implode(',', $filterdata->users) : null;
        
        $organizationid = optional_param('costcenterid', 0, PARAM_INT);
        $departmentid = optional_param('departmentid', 0, PARAM_INT);
        $subdepartmentid = optional_param('subdepartmentid', 0, PARAM_INT);
        
        // if (!empty($organizationid) || !empty($departmentid) || !empty($subdepartmentid)) {
        //     $organization = $organizationid;
        //     $department = $departmentid;
        //     $subdepartment = $subdepartmentid;
        //     if ($department == 0) {
        //         unset($department);
        //     }
        //     if ($subdepartment == 0) {
        //         unset($subdepartment);
        //     }
        //     $formdata = new stdClass();
        //     $formdata->$firstlevel = $organization;
        //     $formdata->$secondlevel = $department;
        //     $formdata->$thirdlevel = $subdepartment;
        //     $mform->set_data($formdata);
        // }
        if(is_array($datasubmitted->$firstlevel)){
            $datasubmitted->$firstlevel = implode(',', $datasubmitted->$firstlevel);
          } else{
            $datasubmitted->$firstlevel = $datasubmitted->$firstlevel;
          }
        
          if(is_array($datasubmitted->$secondlevel)){
            $datasubmitted->$secondlevel = implode(',', $datasubmitted->$secondlevel);
          } else{
            $datasubmitted->$secondlevel = $datasubmitted->$secondlevel;
          }
          if(is_array($datasubmitted->$thirdlevel)){
            $datasubmitted->$thirdlevel = implode(',', $datasubmitted->$thirdlevel);
          } else{
            $datasubmitted->$thirdlevel = $datasubmitted->$thirdlevel;
          }
        
          $mform->set_data($datasubmitted);

    }

    // Create the user selector objects.
    $options = array(
        'context' => $context->id,
        'groupsid' => $id,
        'organization' => $organization,
        'department' => $department,
        'subdepartment' => $subdepartment,
        'email' => $email,
        'groups' => $filtergroup,
        'idnumber' => $idnumber,
        'uname' => $uname,
        'levelid' => $levelid
    );

    if ( $add && confirm_sesskey()) {
        if ($submitvalue == "Add_All_Users") {
            $options = json_decode($_REQUEST["options"], false);
              $userstoassign = array_flip(local_program_users('add', $id, (array)$options, false, $offset1 = -1, $perpage = -1));
        } else {
            $userstoassign = $add;
        }
        if (!empty($userstoassign)) {
            $progress = 0;
            $progressbar = new \core\progress\display_if_slow(get_string('enrollusers', 'local_groups', $groups->name));
            $progressbar->start_html();
            $progressbar->start_progress('', count($userstoassign) - 1);
            foreach ($userstoassign as $key => $adduser) {
                $progressbar->progress($progress);
                $progress++;
                // local_program_add_member($levelid, $adduser, $level->programid);
            }
            $program = $DB->get_record_sql("SELECT id
                                             FROM {local_program}
                                            WHERE batchid = $groups->id
                                        ");

            $programclass = new \local_program\program();
            if ($program->id) {
                $programclass->program_addassign_assignusers($program->id, $userstoassign,$level->id);
            }
            $progressbar->end_html();
            $result = new stdClass();
            $result->changecount = $progress;
            $result->group = $groups->name;

            echo $OUTPUT->notification(get_string('enrolluserssuccess', 'local_groups', $result), 'success');
            $button = new single_button($url, get_string('click_continue', 'local_groups'), 'get', true);
            $button->class = 'continuebutton';
            echo $OUTPUT->render($button);
            echo $OUTPUT->footer();
            die();
        }
    }
    if ($remove && confirm_sesskey()) {
        if ($submitvalue == "Remove_All_Users") {
            $options = json_decode($_REQUEST["options"], false);
             $userstounassign = array_flip(local_program_users('remove', $id, (array)$options, false, $offset1 = -1, $perpage = -1));
        } else {
            $userstounassign = $remove;
        }
        if (!empty($userstounassign)) {
            $progress = 0;
            $progressbar = new \core\progress\display_if_slow(get_string('un_enrollusers', 'local_groups', $groups->name));
            $progressbar->start_html();
            $progressbar->start_progress('', count($userstounassign) - 1);
            foreach ($userstounassign as $key => $removeuser) {
                $progressbar->progress($progress);
                $progress++;
                // local_program_remove_member($levelid, $removeuser, $level->programid);
            }
            $programs = $DB->get_record_sql("SELECT id
                                             FROM {local_program}
                                            WHERE batchid = $groups->id
                                        ");
            $programclass = new \local_program\program();
            if ($programs->id) {
                $programclass->program_rem_assignusers($programs->id, $userstounassign, $level->id);
            }
            $progressbar->end_html();
            $result = new stdClass();
            $result->changecount = $progress;
            $result->group = $groups->name;
            echo $OUTPUT->notification(get_string('unenrolluserssuccess', 'local_groups', $result), 'success');
            $button = new single_button($PAGE->url, get_string('click_continue', 'local_groups'), 'get', true);
            $button->class = 'continuebutton';
            echo $OUTPUT->render($button);
            echo $OUTPUT->footer();
            die();
        }
    }
    $selecttousers = local_program_users('add', $id, $options, false, $offset = -1, $perpage = 50);
    $selecttouserstotal = local_program_users('add', $id, $options, true, $offset1 = -1, $perpage = -1);

    $selectfromusers = local_program_users('remove', $id, $options, false, $offset1 = -1, $perpage = 50);
    $selectfromuserstotal = local_program_users('remove', $id, $options, true, $offset1 = -1, $perpage = -1);

    $selectallenrolledusers = '&nbsp&nbsp<button type = "button" id = "select_add" name = "select_all"
                                            value = "Select All" title = "'
                                            .get_string('select_all', 'local_groups').'" class = "btn btn-default">'
                                            .get_string('select_all', 'local_groups').
                                        '</button>';
    $selectallenrolledusers .= '&nbsp&nbsp<button type = "button" id = "add_select" name = "remove_all"
                                            value = "Remove All" title = "'
                                            .get_string('remove_all', 'local_groups').
                                        '" class = "btn btn-default"/>'
                                            .get_string('unselect_all', 'local_groups').
                                        '</button>';

    $selectallnotenrolledusers = '&nbsp&nbsp<button type = "button" id = "select_remove" name = "select_all"
                                                value = "Select All" title = "'
                                            .get_string('select_all', 'local_groups').'" class = "btn btn-default"/>'
                                                .get_string('select_all', 'local_groups').
                                            '</button>';
    $selectallnotenrolledusers .= '&nbsp&nbsp<button type = "button" id = "remove_select" name = "remove_all"
                                                value = "Remove All" title = "'
                                            .get_string('remove_all', 'local_groups').
                                        '" class = "btn btn-default"/>'
                                                .get_string('unselect_all', 'local_groups').
                                            '</button>';

    $content = '<div class="bootstrap-duallistbox-container mb-3">';
    $encodedoptions = json_encode($options);
    $content .= '<form  method = "post" name = "form_name" id = "user_assign" class = "form_class" >
                    <div class="box2 col-12 col-md-5 pull-left">
                        <input type = "hidden" name = "id" value = "'.$id.'"/>
                        <input type = "hidden" name = "sesskey" value = "'.sesskey().'"/>
                        <input type = "hidden" name = "options"  value = '.$encodedoptions.' />
                            <label>'.get_string('enrolled_users', 'local_courses', $selectfromuserstotal).'</label>'
                            .$selectallnotenrolledusers;
    $content .= '<select multiple = "multiple" name = "remove[]"
                    id = "bootstrap-duallistbox-selected-list_duallistbox_groups_users" class = "dual_select">';
    $userkeyvalue = array_keys($selectfromusers);
    $uservalues = implode(', ', $userkeyvalue);
    $sql = $DB->get_field('local_program', 'id', array('batchid' => $id));
    if (!$sql) {
        foreach ($selectfromusers as $key => $selectfromuser) {
            $content .= "<option value = '$key'> $selectfromuser </option>";
        }
    } else {
        if (!empty($uservalues)) {
            $programsql = "SELECT lpu.userid, lpu.programid
                            FROM {local_program_users} lpu
                           WHERE lpu.userid IN ($uservalues)";
            $program = $DB->get_records_sql($programsql);
            $programarray = array();
            foreach ($program as $key => $value) {
                $programarray[] = $value->userid;
            }
            $prguserid = implode(', ', $programarray);

              $semstartdatesql = "SELECT DISTINCT(lpl.active), lpl.startdate
                                 FROM {local_program_levels} lpl
                                 JOIN {local_program_users} lpu ON lpl.programid = lpu.programid
                                WHERE lpu.userid IN ($prguserid) AND lpl.active = 1
                                 AND DATE(FROM_UNIXTIME(lpl.startdate, '%Y-%m-%d')) <= DATE(NOW())";
 
            $semstartdate = $DB->get_record_sql($semstartdatesql);

            foreach ($selectfromusers as $key => $selectfromuser) {
                if (!empty($semstartdate->startdate)) {
                    $content .= "<option value = '$key'> $selectfromuser </option>";
                } else {
                    $content .= "<option value = '$key'> $selectfromuser </option>";
                }
            }
        }
    }
    $content .= '</select>';
    $content .= '</div>
                    <div class = "box3 col-md-2 col-12 pull-left actions">
                        <button type = "submit" class = "custom_btn btn remove btn-default"
                            disabled = "disabled" title = "
                               '.get_string('removeallselectedusers', 'local_groups').'"
                               name = "submit_value" value = "Remove Selected Users"
                               id = "user_unassign_all"/>
                               '.get_string('remove_selected_users', 'local_groups').'
                        </button>
                </form>';
    $content .= '<form  method = "post" name = "form_name" id = "user_un_assign" class = "form_class" >
                    <button type = "submit" class = "custom_btn btn move btn-default" disabled = "disabled" title = "
                            '.get_string('addallselectedusers', 'local_groups').'"
                            name = "submit_value" value = "Add Selected Users"
                            id = "user_assign_all" />
                        '.get_string('add_selected_users', 'local_groups').'
                    </button>
                </div>
                <div class = "box1 col-md-5 col-12 pull-left">
                    <input type = "hidden" name = "id" value = "'.$id.'"/>
                    <input type = "hidden" name = "sesskey" value = "'.sesskey().'"/>
                    <input type = "hidden" name = "options"  value = '.$encodedoptions.' />
                        <label> '.get_string('not_enrolled_users', 'local_groups', $selecttouserstotal).'</label>'
                        .$selectallenrolledusers;
    $content .= '<select multiple = "multiple" name = "add[]"
                    id = "bootstrap-duallistbox-nonselected-list_duallistbox_groups_users" class = "dual_select">';
    foreach ($selecttousers as $key => $selecttouser) {
          $content .= "<option value = '$key'> $selecttouser </option>";
    }
    $content .= '</select>';
    $content .= '</div></form>';
    $content .= '</div>';
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

if ($id) {
    $selectdiv = '<div class="row d-block">
                    <div class="w-100 pull-left">
                        '.$content.'
                    </div>
                </div>';
    echo $selectdiv;
    $myjson = json_encode($options);
    echo "<script language='javascript'>
    $( document ).ready(function() {
        $('#select_remove').click(function() {
            $('#bootstrap-duallistbox-selected-list_duallistbox_groups_users option').prop('selected', true);
            $('.box3 .remove').prop('disabled', false);
            $('#user_unassign_all').val('Remove_All_Users');

            $('.box3 .move').prop('disabled', true);
            $('#bootstrap-duallistbox-nonselected-list_duallistbox_groups_users option').prop('selected', false);
            $('#user_assign_all').val('Add Selected Users');

        });
        $('#remove_select').click(function() {
            $('#bootstrap-duallistbox-selected-list_duallistbox_groups_users option').prop('selected', false);
            $('.box3 .remove').prop('disabled', true);
            $('#user_unassign_all').val('Remove Selected Users');
        });
        $('#select_add').click(function() {
            $('#bootstrap-duallistbox-nonselected-list_duallistbox_groups_users option').prop('selected', true);
            $('.box3 .move').prop('disabled', false);
            $('#user_assign_all').val('Add_All_Users');

            $('.box3 .remove').prop('disabled', true);
            $('#bootstrap-duallistbox-selected-list_duallistbox_groups_users option').prop('selected', false);
            $('#user_unassign_all').val('Remove Selected Users');

        });
        $('#add_select').click(function() {
           $('#bootstrap-duallistbox-nonselected-list_duallistbox_groups_users option').prop('selected', false);
            $('.box3 .move').prop('disabled', true);
            $('#user_assign_all').val('Add Selected Users');
        });
        $('#bootstrap-duallistbox-selected-list_duallistbox_groups_users').on('change', function() {
            if(this.value!=''){
                $('.box3 .remove').prop('disabled', false);
                $('.box3 .move').prop('disabled', true);
            }
        });
        $('#bootstrap-duallistbox-nonselected-list_duallistbox_groups_users').on('change', function() {
            if(this.value!=''){
                $('.box3 .move').prop('disabled', false);
                $('.box3 .remove').prop('disabled', true);
            }
        });
        jQuery(
            function($)
            {
              $('.dual_select').bind('scroll', function()
                {
                  if($(this).scrollTop() + $(this).innerHeight()>=$(this)[0].scrollHeight)
                  {
                    var get_id=$(this).attr('id');
                    if(get_id=='bootstrap-duallistbox-selected-list_duallistbox_groups_users'){
                        var type='remove';
                        var total_users=$selectfromuserstotal;
                    }
                    if(get_id=='bootstrap-duallistbox-nonselected-list_duallistbox_groups_users'){
                        var type='add';
                        var total_users=$selecttouserstotal;

                    }
                    var count_selected_list=$('#'+get_id+' option').length;

                    var lastValue = $('#'+get_id+' option:last-child').val();

                  if(count_selected_list<total_users){
                       //alert('end reached');
                        var selected_list_request = $.ajax({
                            method: 'GET',
                            url: M.cfg.wwwroot + '/local/groups/assign.php?options=$myjson',
                            data: {id:'$id',sesskey:'$sesskey', type:type,view:'ajax',lastitem:lastValue},
                            dataType: 'html'
                        });
                        var appending_selected_list = '';
                        selected_list_request.done(function(response){
                        //console.log(response);
                        response = jQuery.parseJSON(response);
                        //console.log(response);

                        $.each(response, function (index, data) {

                            appending_selected_list = appending_selected_list + '<option value=' + index + '>' + data + '</option>';
                        });
                        $('#'+get_id+'').append(appending_selected_list);
                        });
                    }
                  }
                })
            }
        );

    });
    </script>";
}
// $backurl = new moodle_url('/local/groups/index.php');
// $continue = '<div class = "col-lg-12 col-md-12 pull-right text-right p-0">';
// $continue .= $OUTPUT->single_button($backurl, get_string('continue'));
// $continue .= '</div>';
// echo $continue;
// echo $OUTPUT->footer();
$continue = '<div class="col-lg-12 col-md-12 pull-right text-right mt-15">';
$continue .= '<a href='.$CFG->wwwroot.'/local/program/view.php?bcid='.$level->programid.' class="singlebutton"><button class="btn">'.get_string('continue', 'local_program').'</button></a>';
$continue .= '';
echo $continue;
echo $OUTPUT->footer();
