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
 * @package ODL
 * @subpackage local_courses
 */

ini_set('memory_limit', '-1');
define('NO_OUTPUT_BUFFERING', true);
require('../../config.php');
require_once($CFG->dirroot . '/local/opencourses/lib.php');
require_once($CFG->dirroot . '/local/opencourses/moocfilters_form.php');
require_once($CFG->dirroot.'/local/lib.php');
//require_once($CFG->dirroot.'/local/courses/lib.php');

global $CFG, $DB, $USER, $PAGE, $OUTPUT, $SESSION;

$view = optional_param('view', 'page', PARAM_RAW);
$type = optional_param('type', '', PARAM_RAW);
$lastitem = optional_param('lastitem', 0, PARAM_INT);

$enrolid = required_param('enrolid', PARAM_INT);
$programid = 0;//optional_param('programid',0,PARAM_INT);
$batchid = 0;//optional_param('batchid',0,PARAM_INT);
$course_id = optional_param('id', 0, PARAM_INT);
$roleid = optional_param('roleid', -1, PARAM_INT);
  $costcenteridstd = optional_param('costcenterid', -1, PARAM_INT);
 
    $organizationid = optional_param('costcenterid', 0, PARAM_INT);
       $departmentid = optional_param('departmentid', 0, PARAM_INT);
   
     $subdepartmentid = optional_param('subdepartmentid', 0, PARAM_INT);
 
$instance = $DB->get_record('enrol', array('id' => $enrolid, 'enrol' => 'manual'), '*', MUST_EXIST);
$course = $DB->get_record('course', array('id' => $instance->courseid), '*', MUST_EXIST);
$submit_value = optional_param('submit_value', '', PARAM_RAW);
$add = optional_param('add', array(), PARAM_RAW);
$remove = optional_param('remove', array(), PARAM_RAW);
$sesskey = sesskey();
$context = context_course::instance($course->id, MUST_EXIST);
require_login();

if ($view == 'ajax') {

  $options = (array)json_decode($_GET["options"], false);
  $select_from_users = course_enrolled_users2($type, $course_id, $options, false, $offset1 =- 1, $perpage = 500, $lastitem);
  echo json_encode($select_from_users);
  exit;
}

$canenrol = has_capability('local/courses:enrol', $context);
    // No need to invent new error strings here.
    require_capability('local/courses:enrol', $context);
    require_capability('enrol/manual:enrol', $context);
    require_capability('enrol/manual:unenrol', $context);

 if (!(is_siteadmin() || has_capability('local/costcenter:manage_multiorganizations', $context))) {

        $user_costcenter = $DB->get_record('user', array('id' => $USER->id), $fields = 'id, open_costcenterid, open_departmentid');
        $course_costcenter = $DB->get_record('course', array('id' => $course->id), 
                              $fields = 'id, open_costcenterid, open_departmentid');
        // if (has_capability('local/costcenter:manage_ownorganization', $context)) {
         
        //   if (($user_costcenter->open_costcenterid != $course_costcenter->open_costcenterid)) {
        //      redirect($CFG->wwwroot.'/local/opencourses/opencourses.php');
        //      die;
        //   }
         
        // } elseif (has_capability('local/costcenter:manage_owndepartments', $context)) {
         
        //   if (($user_costcenter->open_costcenterid != $course_costcenter->open_costcenterid) 
        //   || ($user_costcenter->open_departmentid != $course_costcenter->open_departmentid)) {
        //       redirect($CFG->wwwroot.'/local/courses/courses.php');
        //       die;
        //   }
          
        // }
  }
/*Department level restrictions */
require_once($CFG->dirroot.'/local/includes.php');
$userlist = new has_user_permission();
$haveaccess = $userlist->access_courses_permission($course_id);
if(!$haveaccess) {
   redirect($CFG->wwwroot . '/local/courses/error.php?id=2');
}
if ($roleid < 0) {
    $roleid = $instance->roleid;
}
$roles = get_assignable_roles($context);
$roles = array('0' => get_string('none')) + $roles;

if (!isset($roles[$roleid])) {
    // Weird - security always first!.
    $roleid = 0;
}

if (!$enrol_manual = enrol_get_plugin('manual')) {
    throw new coding_exception('Can not instantiate enrol_manual');
}

$instancename = $enrol_manual->get_instance_name($instance);
if($roleid == 3){

$PAGE->set_url('/local/opencourses/courseenrol.php', array('id' => $course_id, 'enrolid' => $instance->id,'programid'=>0,'roleid'=>$roleid,'costcenterid'=>$costcenteridstd, 'departmentid'=>$departmentid, 'subdepartmentid'=>$subdepartmentid));
} 
if($roleid == 5){
 
$PAGE->set_url('/local/opencourses/courseenrol.php', array('enrolid' => $instance->id,'roleid'=>$roleid,'id' => $course_id, 'programid'=>0,'costcenterid'=>$costcenteridstd, 'departmentid'=>$departmentid,'subdepartmentid'=>$subdepartmentid, 'batchid'=>0));
}
 
 

//$PAGE->navbar->add(get_string('manage_programs', 'local_program'), new moodle_url('/local/program/view.php?bcid='.$programid));

$PAGE->navbar->add(get_string('manage_courses','local_opencourses'),new moodle_url('/local/opencourses/opencourses.php'));


$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->js('/local/courses/js/jquery.bootstrap-duallistbox.js', true);
$PAGE->requires->css('/local/courses/css/bootstrap-duallistbox.css');
$PAGE->set_title($enrol_manual->get_instance_name($instance));
if($roleid == 3){
    $PAGE->navbar->add(get_string('facultyenrol', 'local_program'));
  if (!$add&&!$remove) {
    $PAGE->set_heading(get_string('EnrollFacultiesto','local_program', $course->fullname));
  }
}  if ($roleid == 5) {
    $PAGE->navbar->add(get_string('studentenrol', 'local_program'));
  if (!$add&&!$remove) {
    $PAGE->set_heading(get_string('Enrollstudentsto','local_program', $course->fullname));
  }
}


navigation_node::override_active_url(new moodle_url('/local/mass_enroll/mass_enroll.php', array('id' => $course->id)));
$systemcontext = context_system::instance();
 
if (is_siteadmin()) {
  $costcenter = "";
} else {
  $costcenter = $DB->get_field('course', 'open_costcenterid', array('id' => $course_id));
}

echo $OUTPUT->header();
if ($course) {
  $organization = null;
  $department = null;
  $email = null;
  $idnumber = null;
  $uname = null;
  $groups = null;
  // // roleid = 3 is for teacher
  // if($roleid == 3){
  //   $filterlist = get_filterslist();
  // }
  // // roleid = 5 is for student
  // if($roleid == 5){
  //   $filterlist = array('organizations','departments','subdepartment','email');
  // }
  if($roleid == 3){
// $filterlist = get_filterslist();
    if (is_siteadmin() || has_capability('local/costcenter:manage_multiorganizations', $context)) {
      $filterlist = array('dependent_fields','emailid');
    } else if (!is_siteadmin() && has_capability('local/costcenter:manage_ownorganization', $context )) {
      $filterlist = array('dependent_fields','emailid');
    } else if (!is_siteadmin() && has_capability('local/costcenter:manage_owndepartments', $context )) {
      $filterlist = array('dependent_fields','emailid');
    } else {
      $filterlist = array('emailid');
    }
  }
  // roleid = 5 is for student
  if($roleid == 5){
    if (is_siteadmin() || has_capability('local/costcenter:manage_multiorganizations', $context)) {
      $filterlist = array('dependent_fields','emailid');
    } else if (!is_siteadmin() && has_capability('local/costcenter:manage_ownorganization', $context )) {
      $filterlist = array('dependent_fields','emailid');
    } else if (!is_siteadmin() && has_capability('local/costcenter:manage_owndepartments', $context )) {
      $filterlist = array('dependent_fields','emailid');
    } else {
      $filterlist = array('emailid');
    }
  }
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
  $filterparams = array('options' => null, 'dataoptions' => null);
  $mform = new moocfilters_form($PAGE->url, array('filterlist' => $filterlist, 'enrolid' => $enrolid, 'courseid' => $course_id, 'filterparams' => $filterparams, 'action' => 'user_enrolment'), 'post', '', null, true, (array)data_submitted() );
 

  if ($mform->is_cancelled()) {
    redirect($PAGE->url);
  } else {
    $filterdata = $mform->get_data();
    if ($filterdata) {
        $collapse = false;
        $show = 'show';
    } else {
        $collapse = true;
        $show = '';
      }
      $labelstring = get_config('local_costcenter');
      $firstlevel = $labelstring->firstlevel;
      $secondlevel = $labelstring->secondlevel;
      $thirdlevel = $labelstring->thirdlevel;
      $organizations = !empty($filterdata->$firstlevel) ? implode(',', (array)$filterdata->$firstlevel) : null;
    $departments = !empty($filterdata->$secondlevel) ? implode(',', (array)$filterdata->$secondlevel) : null;
    $subdepartments = !empty($filterdata->$thirdlevel) ? implode(',', (array)$filterdata->$thirdlevel) : null;
     $email = !empty($filterdata->emailid) ? implode(',', $filterdata->emailid) : null;
    $idnumber = !empty($filterdata->idnumber) ? implode(',', $filterdata->idnumber) : null;
    $uname = !empty($filterdata->users) ? implode(',', $filterdata->users) : null;
    $groups = !empty($filterdata->groups) ? implode(',', $filterdata->groups) : null;
    $location = !empty($filterdata->location) ? implode(',', $filterdata->location) : null;
    $hrmsrole = !empty($filterdata->hrmsrole) ? implode(',', $filterdata->hrmsrole) : null;
    

    
    if (!empty($organizationid) || !empty($departmentid) || !empty($subdepartmentid)) {
        $organizationid = $organizationid;
        $departmentid = $departmentid;
        $subdepartmentid = $subdepartmentid;
        if ($department == 0) {
            unset($department);
        }
        if ($subdepartment == 0) {
            unset($subdepartment);
        }
        $formdata = new stdClass();
        $formdata->organizations = $organizationid;
        $formdata->departments = $departmentid;
        $formdata->subdepartment = $subdepartmentid;
        $mform->set_data($formdata);
    }
  }

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
  // Create the user selector objects.
  $options = array('context' => $context->id, 'courseid' => $course_id, 'organization' => $organizationid,'organizations' => $organizations,  'department' => $departmentid, 'departments' => $departments, 'subdepartment' => $subdepartmentid,  'subdepartments' => $subdepartments, 'email' => $email, 'idnumber' => $idnumber, 'uname' => $uname, 'groups' => $groups, 'hrmsrole' => $hrmsrole, 'roleid' => $roleid, 'location' => $location);
  
  $dataobj = $course_id;
  $fromuserid = $USER->id;
  if ( $add && confirm_sesskey()) {
    $type = 'course_enrol';
    if($submit_value == "Add_All_Users"){
      $options = json_decode($_REQUEST["options"], false);
      $userstoassign = array_flip(course_enrolled_users2('add', $course_id, (array)$options, false, $offset1=-1, $perpage=-1));
    }else{
        $userstoassign = $add;
    }
    if (!empty($userstoassign)) {
      $progress = 0;
      $progressbar = new \core\progress\display_if_slow(get_string('enrollusers', 'local_courses', $course->fullname));
      $progressbar->start_html();
      $progressbar->start_progress('', count($userstoassign)-1);
      $roleid = '';
      $userprogramid = '';
      $userbatchid = '';
      $usercostcenterid = '';
      foreach($userstoassign as $key => $adduser){
        $progressbar->progress($progress);
        $progress++;
        $timeend = 0;
        $timestart = 0;
        $type = $DB->get_field('user', 'open_type', array('id' => $adduser));
        $userprogramid = $DB->get_field('local_program_level_courses','programid', array('courseid' => $instance->courseid));
        $userbatchid = $DB->get_field('local_program', 'batchid', array('id' => $userprogramid));
        $usercostcenterid = $DB->get_field('user', 'open_costcenterid', array('id' => $adduser));
        $userlevelid = $DB->get_field('local_program_level_courses', 'levelid', array('courseid' => $instance->courseid));
        if($type == 0){
          $roleid = 3;
        } else{
          $roleid = 5;
        }
        // print_object($instance);exit;
        $enrol_manual->enrol_user($instance, $adduser, $roleid, $timestart, $timeend);
        
        $DB->insert_record('local_program_enrolments', ['programid' => $userprogramid, 'userid' => $adduser, 'levelid' => $userlevelid, 'courseid' => $instance->courseid, 'mandatory' => 0, 'timecreated' => time(), 'timemodified' => null]);

        $notification = new \local_courses\notification();
        $course = $DB->get_record('course', array('id' => $dataobj));
        $user = core_user::get_user($adduser);
        $notificationdata = $notification->get_existing_notification($course, $type);
        if($notificationdata)
          $notification->send_course_email($course, $user, $type, $notificationdata);
      }
      $progressbar->end_html();
      $result = new stdClass();
      $result->changecount = $progress;
      $result->course = $course->fullname; 

      echo $OUTPUT->notification(get_string('enrolluserssuccess', 'local_courses',$result),'success');
      if($roleid == 3){
        $continue = '<div class="col-lg-12 col-md-12 pull-center text-center mt-15">';

         $studentroleid = $DB->get_field('role', 'id', array('shortname' => 'student'));


       // $continue .= '<a href='.$CFG->wwwroot.'/local/opencourses/courseenrol.php?id='.$instance->courseid.'&enrolid='.$instance->id.'&roleid='.$roleid.'&programid='.$userprogramid.'&costcenterid='.$usercostcenterid.' class="singlebutton"><button class="btn">'.get_string('click_continue', 'local_program').'</button></a>';

$continue .= '<a href='.$CFG->wwwroot.'/local/opencourses/courseenrol.php?enrolid='.$enrolid.'&roleid='.$roleid.'&id='.$course->id.'&programid=0&costcenterid='.$course->open_costcenterid.'&departmentid='.$course->open_departmentid.'&subdepartmentid='.$course->open_subdepartment.'&batchid=0
 class="singlebutton"><button class="btn">'.get_string('click_continue', 'local_program').'</button></a>';

      }
      if($roleid == 5){
        $continue = '<div class="col-lg-12 col-md-12 pull-center text-center mt-15">';
       // $continue .= '<a href='.$CFG->wwwroot.'/local/opencourses/courseenrol.php?id='.$instance->courseid.'&enrolid='.$instance->id.'&roleid='.$roleid.'&programid='.$userprogramid.'&costcenterid='.$usercostcenterid.'&batchid='.$userbatchid.' class="singlebutton"><button class="btn">'.get_string('click_continue', 'local_program').'</button></a>';

         $continue .= '<a href='.$CFG->wwwroot.'/local/opencourses/courseenrol.php?enrolid='.$enrolid.'&roleid='.$roleid.'&id='.$course->id.'&programid=0&costcenterid='.$course->open_costcenterid.'&departmentid='.$course->open_departmentid.'&subdepartmentid='.$course->open_subdepartment.'&batchid=0 class="singlebutton"><button class="btn">'.get_string('click_continue', 'local_program').'</button></a>';
      }
      // $button = new single_button($url, get_string('click_continue','local_courses'), 'get', true, array('batchid'));

     $continue .= '';
echo $continue;
echo $OUTPUT->footer();
      die();
    }
  }
  if ($remove&& confirm_sesskey()) {
    $type = 'course_unenroll';
    if($submit_value == "Remove_All_Users"){

      $options =json_decode($_REQUEST["options"],false);
      $userstounassign = array_flip(course_enrolled_users2('remove',$course_id,(array)$options,false,$offset1=-1,$perpage=-1));
    } else {
      $userstounassign = $remove;
    }
    if (!empty($userstounassign)) {
      $progress = 0;
      $progressbar = new \core\progress\display_if_slow(get_string('un_enrollusers', 'local_courses',$course->fullname));
      $progressbar->start_html();
      $progressbar->start_progress('', count($userstounassign)-1);
      foreach ($userstounassign as $key=>$removeuser) {
          $progressbar->progress($progress);
          $progress++;
          if ($instance->enrol == 'manual') {
            $manual = $enrol_manual->unenrol_user($instance, $removeuser);
          }
          $data_self = $DB->get_record_sql("SELECT * FROM {user_enrolments} ue 
            JOIN {enrol} e ON ue.enrolid = e.id 
            WHERE e.courseid = {$course_id} and ue.userid = $removeuser");
          $enrol_self = enrol_get_plugin('self');
          if($data_self->enrol == 'self'){
            $self=$enrol_self->unenrol_user($data_self, $removeuser);
          }
          $userprogramid = $DB->get_field('local_program_level_courses','programid', array('courseid' => $instance->courseid));
        $userbatchid = $DB->get_field('local_program', 'batchid', array('id' => $userprogramid));
        $usercostcenterid = $DB->get_field('user', 'open_costcenterid', array('id' => $removeuser));
        $userlevelid = $DB->get_field('local_program_level_courses', 'levelid', array('courseid' => $instance->courseid));
          
          $DB->delete_records('local_program_enrolments', ['programid' => $userprogramid, 'userid' => $removeuser, 'levelid' => $userlevelid, 'courseid' => $instance->courseid, 'mandatory' => 0]);

          $notification = new \local_courses\notification();
          $course = $DB->get_record('course', array('id' => $dataobj));
          $user = core_user::get_user($removeuser);
          $notificationdata = $notification->get_existing_notification($course, $type);
          if($notificationdata)
            $notification->send_course_email($course, $user, $type, $notificationdata);
      }
      $progressbar->end_html();
      $result = new stdClass();
      $result->changecount = $progress;
      $result->course = $course->fullname; 
      

        $reurl = $CFG->wwwroot.'/local/opencourses/courseenrol.php?enrolid='.$enrolid.'&roleid='.$roleid.'&id='.$course->id.'&programid=0&costcenterid='.$course->open_costcenterid.'&departmentid='.$course->open_departmentid.'&subdepartmentid='.$course->open_subdepartment.'&batchid=0 ';

     
  
      echo $OUTPUT->notification(get_string('unenrolluserssuccess', 'local_courses', $result), 'success');
      //$button = new single_button($reurl, get_string('click_continue','local_courses'), 'get', true);
      $button = new single_button($PAGE->url, get_string('click_continue','local_courses'), 'get', true);

      
      $button->class = 'continuebutton';
      echo $OUTPUT->render($button);
      die();
    }
  }

  //$departmentid;

    //$options = array('context' => $context->id, 'courseid' => $course_id, 'organization' => $organizationid, 'department' => $departmentid, 'subdepartment' => $subdepartmentid, 'email' => $email, 'idnumber' => $idnumber, 'uname' => $uname, 'groups' => $groups, 'hrmsrole' => $hrmsrole, 'roleid' => $roleid, 'location' => $location);

 

 


  $select_to_users = course_enrolled_users2('add', $course_id, $options, false, $offset =- 1, $perpage = 500);
  $select_to_users_total = course_enrolled_users2('add', $course_id, $options, true, $offset1 =- 1, $perpage =- 1);

  $select_from_users = course_enrolled_users2('remove', $course_id, $options, false, $offset1=-1, $perpage = 500);
  $select_from_users_total = course_enrolled_users2('remove', $course_id, $options, true, $offset1=-1, $perpage =- 1);

  $select_all_enrolled_users = '&nbsp&nbsp<button type="button" id="select_add" name="select_all" value="Select All" title="'.get_string('select_all', 'local_courses').'" class="btn btn-default">'.get_string('select_all', 'local_courses').'</button>';
  $select_all_enrolled_users .= '&nbsp&nbsp<button type="button" id="add_select" name="remove_all" value="Remove All" title="'.get_string('remove_all', 'local_courses').'" class="btn btn-default"/>'.get_string('remove_all', 'local_courses').'</button>';
  
  $select_all_not_enrolled_users = '&nbsp&nbsp<button type="button" id="select_remove" name="select_all" value="Select All" title="'.get_string('select_all', 'local_courses').'" class="btn btn-default"/>'.get_string('select_all', 'local_courses').'</button>';
  $select_all_not_enrolled_users .= '&nbsp&nbsp<button type="button" id="remove_select" name="remove_all" value="Remove All" title="'.get_string('remove_all', 'local_courses').'" class="btn btn-default"/>'.get_string('remove_all', 'local_courses').'</button>';
    
    
  $content = '<div class="bootstrap-duallistbox-container">';
  $encoded_options = json_encode($options);
  $content .= '<form  method="post" name="form_name" id="user_assign" class="form_class" >
              <div class="box2 col-12 col-md-5 pull-left">
                <input type="hidden" name="id" value="'.$course_id.'"/>
                <input type="hidden" name="enrolid" value="'.$enrolid.'"/>
                <input type="hidden" name="sesskey" value="'.sesskey().'"/>
                <input type="hidden" name="options"  value=\''.$encoded_options.'\' />
                <label>'.get_string('enrolled_users', 'local_courses', $select_from_users_total).
                '</label>'.$select_all_not_enrolled_users;
  $content .= '<select multiple="multiple" name="remove[]" 
              id="bootstrap-duallistbox-selected-list_duallistbox_courses_users" class="dual_select">';
  foreach($select_from_users as $key=>$select_from_user){
        $content.="<option value='$key'>$select_from_user</option>";
  }
  $content .= '</select>
          </div>';

  $content .= '<div class="box3 col-12 col-md-2 actions pull-left">
              <button type="submit" class="custom_btn btn remove btn-default" disabled="disabled" 
              title="Remove Selected Users" id="user_unassign_all">
                '.get_string('remove_selected_users', 'local_courses').'
              </button>
            </form>';
  $content .= '<form  method="post" name="form_name" id="user_un_assign" class="form_class" >
              <button type="submit" class="custom_btn btn move btn-default" disabled="disabled" title="Add Selected Users" name="submit_value" value="Add Selected Users" id="user_assign_all" >
                '.get_string('add_selected_users', 'local_courses').'
              </button>
            </div>';
  $content .= '<div class="box1 col-12 col-md-5 pull-left">
              <input type="hidden" name="id" value="'.$course_id.'"/>
              <input type="hidden" name="enrolid" value="'.$enrolid.'"/>
              <input type="hidden" name="sesskey" value="'.sesskey().'"/>
              <input type="hidden" name="options"  value=\''.$encoded_options.'\' />
              <label> '.get_string('availablelist', 'local_courses', $select_to_users_total).'</label>'.$select_all_enrolled_users;
  $content .= '<select multiple="multiple" name="add[]" 
              id="bootstrap-duallistbox-nonselected-list_duallistbox_courses_users" class="dual_select">';
    foreach ($select_to_users as $key => $select_to_user) {
          $content .= "<option value='$key'>$select_to_user</option>";
    }
  $content .= '</select>';
  $content .= '</div>
            </form>';
  $content .= '</div>';
}

// echo '<a class="btn-link btn-sm" title="'.get_string('filter').'" href="javascript:void(0);" data-toggle="collapse" data-target="#local_coursesenrol-filter_collapse" aria-expanded="false" aria-controls="local_coursesenrol-filter_collapse">
//         <i class="m-0 fa fa-sliders fa-2x" aria-hidden="true"></i>
//       </a>';
// echo  '<div class="collapse '.$show.'" id="local_coursesenrol-filter_collapse">
//             <div id="filters_form" class="card card-body p-2">';
//                 $mform->display();
// echo        '</div>
//         </div>';

echo '<a class="btn-link btn-sm d-flex align-items-center filter_btn" href="javascript:void(0);" data-toggle="collapse" data-target="#local_courses-filter_collapse" aria-expanded="false" aria-controls="local_courses-filter_collapse">
           <span class="filter mr-2">Filters</span>
        <i class="m-0 fa fa-sliders fa-2x" aria-hidden="true"></i>

      </a>';
echo  '<div class="collapse '.$show.'" id="local_courses-filter_collapse">
            <div id="filters_form" class="card card-body p-2">';
                $mform->display();
echo        '</div>
        </div>';
if ($course) {
  $select_div = '<div class="row d-block">
                <div class="w-100 pull-left">'.$content.'</div>
              </div>';
  echo $select_div;
  $myJSON = json_encode($options);
  echo "<script language='javascript'>

  $( document ).ready(function() {
    $('#select_remove').click(function() {
        $('#bootstrap-duallistbox-selected-list_duallistbox_courses_users option').prop('selected', true);
        $('.box3 .remove').prop('disabled', false);
        $('#user_unassign_all').val('Remove_All_Users');

        $('.box3 .move').prop('disabled', true);
        $('#bootstrap-duallistbox-nonselected-list_duallistbox_courses_users option').prop('selected', false);
        $('#user_assign_all').val('Add Selected Users');

    });
    $('#remove_select').click(function() {
        $('#bootstrap-duallistbox-selected-list_duallistbox_courses_users option').prop('selected', false);
        $('.box3 .remove').prop('disabled', true);
        $('#user_unassign_all').val('Remove Selected Users');
    });
    $('#select_add').click(function() {
        $('#bootstrap-duallistbox-nonselected-list_duallistbox_courses_users option').prop('selected', true);
        $('.box3 .move').prop('disabled', false);
        $('#user_assign_all').val('Add_All_Users');

        $('.box3 .remove').prop('disabled', true);
        $('#bootstrap-duallistbox-selected-list_duallistbox_courses_users option').prop('selected', false);
        $('#user_unassign_all').val('Remove Selected Users');
        
    });
    $('#add_select').click(function() {
       $('#bootstrap-duallistbox-nonselected-list_duallistbox_courses_users option').prop('selected', false);
        $('.box3 .move').prop('disabled', true);
        $('#user_assign_all').val('Add Selected Users');
    });
    $('#bootstrap-duallistbox-selected-list_duallistbox_courses_users').on('change', function() {
        if(this.value != ''){
            $('.box3 .remove').prop('disabled', false);
            $('.box3 .move').prop('disabled', true);
        }
    });
    $('#bootstrap-duallistbox-nonselected-list_duallistbox_courses_users').on('change', function() {
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
                if(get_id=='bootstrap-duallistbox-selected-list_duallistbox_courses_users'){
                    var type='remove';
                    var total_users = $select_from_users_total;
                }
                if(get_id == 'bootstrap-duallistbox-nonselected-list_duallistbox_courses_users'){
                    var type = 'add';
                    var total_users = $select_to_users_total;
                   
                }
                var count_selected_list = $('#'+get_id+' option').length;
               
                var lastValue = $('#'+get_id+' option:last-child').val();
             
              if(count_selected_list<total_users){  
                   //alert('end reached');
                    var selected_list_request = $.ajax({
                        method: 'GET',
                        url: M.cfg.wwwroot + '/local/courses/courseenrol.php',
                        data: {id:'$course_id', sesskey:'$sesskey', type:type,view:'ajax', lastitem:lastValue, 
                          enrolid:'$enrolid', options: $myJSON},
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
// $continue = '<div class="col-lg-12 col-md-12 pull-right text-right mt-15">';
// $continue .= '<a href='.$CFG->wwwroot.'/local/program/view.php?bcid='.$programid.' class="singlebutton"><button class="btn">'.get_string('continue', 'local_program').'</button></a>';
// $continue .= '';
// echo $continue;

$backurl = new moodle_url('/local/opencourses/opencourses.php');
$continue='<div class="col-md-12 pull-right text-right mt-15px">';
$continue.=$OUTPUT->single_button($backurl,get_string('continue'));
$continue.='</div>';
echo $continue;

echo $OUTPUT->footer();
