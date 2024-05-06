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
 * @package Dypatil Medical
 * @subpackage local_courses
 */

if (file_exists($CFG->dirroot.'/local/costcenter/lib.php')) {
    require_once($CFG->dirroot.'/local/costcenter/lib.php');                  
}
require_once($CFG->dirroot . '/user/selector/lib.php');
require_once($CFG->dirroot . '/local/lib.php');
require_once($CFG->libdir.'/completionlib.php');
require_once($CFG->dirroot.'/completion/completion_completion.php');
use \local_opencourses\form\custom_opencourse_form as custom_opencourse_form;
use \local_opemncourses\form\custom_courseevidence_form as custom_courseevidence_form;


defined('MOODLE_INTERNAL') || die();
 
 defined('MOODLE_INTERNAL') || die;
require_once($CFG->dirroot.'/user/selector/lib.php');
require_once($CFG->libdir . '/formslib.php');

 
    function local_opencourses_leftmenunode(){
    global $USER, $DB;

    $systemcontext = context_system::instance();
    $usersnode = '';

    if(is_siteadmin() || has_capability('local/program:manageprogram', context_system::instance()) || has_capability('local/courses:manage', context_system::instance())){
        $managemoochcourse_string=get_string('manage_mooc_courses','local_opencourses');
    }else{

        $managemoochcourse_string=get_string('viewmooccourse','local_opencourses');
    }

      // $managemoochcourse_string =  get_string('manage_mooc_courses','local_mooccourses');

     if(has_capability('local/costcenter:manage_multiorganizations',$systemcontext) || is_siteadmin() || has_capability('local/costcenter:manage_ownorganization', $systemcontext) || has_capability('local/program:viewprogram', $systemcontext) ||has_capability('local/costcenter:manage_owndepartments', $systemcontext) ){
        $usersnode .= html_writer::start_tag('li', array('id'=> 'id_leftmenu_mooccourses', 'class'=>'pull-left user_nav_div users dropdown-item'));
        if(!is_siteadmin() && !has_capability('local/costcenter:manage_multiorganizations',$systemcontext) &&  !has_capability('local/costcenter:manage_ownorganization', $systemcontext) && has_capability('local/costcenter:manage_owndepartments', $systemcontext)){
            $users_url = new moodle_url('/local/opencourses/opencourses.php');
        }else{
            $users_url = new moodle_url('/local/opencourses/opencourses.php');
        }
            $users = html_writer::link($users_url, '<span class="open_courses_icon dypatil_cmn_icon icon" aria-hidden="true"></span><span class="user_navigation_link_text">'.$managemoochcourse_string.'</span>',array('class'=>'user_navigation_link'));
            $usersnode .= $users;
        $usersnode .= html_writer::end_tag('li');
    }
    return array('8' => $usersnode);
}

function get_listof_opencourses($stable, $filterdata) {


 
    global $CFG, $DB, $OUTPUT, $USER;


    $core_component = new core_component();
    
    require_once($CFG->dirroot.'/course/renderer.php');
    require_once($CFG->dirroot . '/enrol/locallib.php');
    $autoenroll_plugin_exist = $core_component::get_plugin_directory('enrol', 'auto');
    if (!empty($autoenroll_plugin_exist)) {
      require_once($CFG->dirroot . '/enrol/auto/lib.php');
    }
    $labelstring = get_config('local_costcenter');
    $systemcontext = context_system::instance();
    $statustype = $stable->status;
    $totalcostcentercount = $stable->costcenterid;
    $totaldepartmentcount = $stable->departmentid;
    $departmentsparams = array();
    $subdepartmentsparams = array();
    $organizationsparams = array();
    $userorg = array();
    $userdep = array();
    $locationsparams = $hrmsrolessparams = [];
    $filtercategoriesparams = array();
    $filtercoursesparams = array();
    $chelper = new coursecat_helper();


    $selectsql = "SELECT c.id, c.fullname, c.shortname, c.category, 
                  c.open_points, c.open_costcenterid, c.open_identifiedas, 
                  c.visible, c.open_skill,c.open_costcenterid,c.open_departmentid ,c.open_subdepartment
                  FROM {course} AS c"; 

    $countsql  = "SELECT count(c.id) FROM {course} AS c ";
    if (is_siteadmin()) {
        $formsql = " JOIN {local_costcenter} AS co ON co.id = c.open_costcenterid
                     JOIN {course_categories} AS cc ON cc.id = c.category";

    } else if(has_capability('local/costcenter:manage_ownorganization',$systemcontext)) {
        $formsql = " JOIN {local_costcenter} AS co ON co.id = c.open_costcenterid
                   JOIN {course_categories} AS cc ON cc.id = c.category
                   WHERE c.open_costcenterid = $USER->open_costcenterid";
    } else if(has_capability('local/costcenter:manage_owndepartments',$systemcontext)) {
        if($USER->open_departmentid){

            $sql1 = "select id FROM {local_costcenter} WHERE parentid  = $USER->open_costcenterid";
                $result = $DB->get_records_sql($sql1);

                foreach($result as $rs){
                    $arr[] = $rs->id;
                }

  $arstring = implode(',',$arr);

        $formsql = " JOIN {local_costcenter} AS co ON co.id = c.open_costcenterid
                   JOIN {course_categories} AS cc ON cc.id = c.category
                   WHERE c.open_costcenterid = $USER->open_costcenterid 
                   AND c.open_departmentid = $USER->open_departmentid";
               }else{
                $sql1 = "select id FROM {local_costcenter} WHERE parentid  = $USER->open_costcenterid";
                $result = $DB->get_records_sql($sql1);

                foreach($result as $rs){
                    $arr[] = $rs->id;
                }


  $arstring = implode(',',$arr);
                  $formsql = " JOIN {local_costcenter} AS co ON co.id = c.open_costcenterid
                   JOIN {course_categories} AS cc ON cc.id = c.category
                   WHERE c.open_costcenterid = $USER->open_costcenterid 
                   ";
  //AND c.open_departmentid IN ($arstring);
               }
    } else {
        $formsql = " JOIN {local_costcenter} AS co ON co.id = c.open_costcenterid
                   JOIN {course_categories} AS cc ON cc.id = c.category
                   WHERE c.open_costcenterid = $USER->open_costcenterid 
                   AND c.open_departmentid = $USER->open_departmentid";
    }

    $formsql .= " AND c.id > 1 ";
    if (isset($filterdata->search_query) && trim($filterdata->search_query) != '') {
        $formsql .= " AND c.fullname LIKE :search";
        $searchparams = array('search' => '%'.trim($filterdata->search_query).'%');
    } else {
        $searchparams = array();
    }

    if (!empty($filterdata->categories)) {
        $filtercategories = explode(',', $filterdata->categories);
        $filtercategories = array_filter($filtercategories, function($value){
            if ($value != '_qf__force_multiselect_submission') {
                return $value;
            }
        });
        if($filtercategories != NULL) {
        list($filtercategoriessql, $filtercategoriesparams) = $DB->get_in_or_equal($filtercategories, 
                                                              SQL_PARAMS_NAMED, 'categories', true, false);
        $formsql .= " AND cc.id $filtercategoriessql";
        }
    }

    if (!empty($filterdata->opencourses)) {

        $filtercourses = explode(',', $filterdata->opencourses);

        $filtercourses = array_filter($filtercourses, function($value){

            if ($value != '_qf__force_multiselect_submission') {

                return $value;
            }
        });
        
        if ($filtercourses != NULL) {
        list($filtercoursessql, $filtercoursesparams) = $DB->get_in_or_equal($filtercourses, 
                                                        SQL_PARAMS_NAMED, 'courses', true, false);
        $formsql .= " AND c.id $filtercoursessql";
        }
    }
     $labelstring = get_config('local_costcenter');
    $firstlevel = $labelstring->firstlevel;
    $secondlevel = $labelstring->secondlevel;
    $thirdlevel = $labelstring->thirdlevel;  
    if (!empty($filterdata->$secondlevel)) {
        $departments = explode(',', $filterdata->$secondlevel);
        $departments = array_filter($departments, function($value){
            if ($value != '_qf__force_multiselect_submission') {
                return $value;
            }
        });
        if ($departments != NULL) {
        list($departmentssql, $departmentsparams) = $DB->get_in_or_equal($departments, 
                                                    SQL_PARAMS_NAMED, 'departments', true, false);
        $formsql .= " AND c.open_departmentid $departmentssql";
        }
    }
    if (!empty($filterdata->$thirdlevel)) {
        $subdepartments = explode(',', $filterdata->$thirdlevel);
        $subdepartments = array_filter($subdepartments, function($value){
            if ($value != '_qf__force_multiselect_submission') {
                return $value;
            }
        });
        if ($subdepartments != NULL) {
        list($subdepartmentssql, $subdepartmentsparams) = $DB->get_in_or_equal($subdepartments, 
                                                            SQL_PARAMS_NAMED, 'subdepartment', true, false);
        $formsql .= " AND c.open_subdepartment $subdepartmentssql";
        }
    }
    
     
    if (!empty($filterdata->$firstlevel)) {
        $organizations = explode(',', $filterdata->$firstlevel);
        $organizations = array_filter($organizations, function($value){
            if ($value != '_qf__force_multiselect_submission') {
                return $value;
            }
        });
        if ($organizations != NULL) {
        list($organizationssql, $organizationsparams) = $DB->get_in_or_equal($organizations, 
             SQL_PARAMS_NAMED, 'organizations', true, false);
        $formsql .= " AND c.open_costcenterid $organizationssql";
        }
    }
    if (!empty($filterdata->hrmsrole)) {
        $hrmsroles = explode(',', $filterdata->hrmsrole);
        $hrmsroles = array_filter($hrmsroles, function($value){
            if ($value != '_qf__force_multiselect_submission') {
                return $value;
            }
        });
        if ($hrmsroles != NULL) {
        list($hrmsrolessql, $hrmsrolessparams) = $DB->get_in_or_equal($hrmsroles, SQL_PARAMS_NAMED, 'hrmsrole', true, false);
        $formsql .= " AND c.open_hrmsrole {$hrmsrolessql} ";
        }
    }
    if (!empty($filterdata->location)) {
        $locations = explode(',', $filterdata->location);
        $locations = array_filter($locations, function($value){
            if ($value != '_qf__force_multiselect_submission') {
                return $value;
            }
        });
        if ($locations != NULL) {
        list($locationsql, $locationsparams) = $DB->get_in_or_equal($locations, SQL_PARAMS_NAMED, 'location', true, false);
        $formsql .= " AND c.open_location {$locationsql} ";
        }
    }

    if (!empty($filterdata->status)) {
        $status = explode(',', $filterdata->status);
        $status = array_filter($status, function($value){
            if ($value != '_qf__force_multiselect_submission') {
                return $value;
            }
        });
        if ($status != NULL) {
            if (!(in_array('active', $status) && in_array('inactive', $status))) {
                if (in_array('active', $status)) {
                    $formsql .= " AND c.visible = 1 ";           
                } else if (in_array('inactive' ,$status)) {
                    $formsql .= " AND c.visible = 0 ";
                }
            }
        }
    }



    if (!is_siteadmin()) {
        $userorg = array('usercostcenter'=>$USER->open_costcenterid);
        $userdep = array('userdepartment'=>$USER->open_departmentid);
    }
    if (!empty($statustype)) {
         $status = explode(',', $statustype);
        // 0 is inactive and 1 is active.
        if (!(in_array('active', $status) && in_array('inactive', $status))) {
            if(in_array('active', $status)){
                $formsql .= " AND c.visible = 1";           
            } else if (in_array('inactive', $status)) {
                $formsql .= " AND c.visible = 0";
            }
        }
    }
    if (!empty($totalcostcentercount)) {
         $formsql .= " AND c.open_costcenterid = $totalcostcentercount";
    }
    if (!empty($totaldepartmentcount)) {
         $formsql .= " AND c.open_departmentid = $totaldepartmentcount";
    }

    $ordersql = " ORDER BY c.id DESC";

    $params = array_merge($searchparams, $userorg, $userdep, $filtercategoriesparams, $filtercoursesparams, $departmentsparams, $subdepartmentsparams, $organizationsparams, $hrmsrolessparams, $locationsparams);

    if (is_siteadmin()) {
        $formsql .=" WHERE c.open_identifiedas = 6";
    }
   else if(has_capability('local/costcenter:manage_ownorganization',$systemcontext)) {
     $formsql .=" AND c.open_identifiedas = 6";
 }
else if(has_capability('local/costcenter:manage_owndepartments',$systemcontext)) {
     $formsql .=" AND c.open_identifiedas = 6";
 }else {
     $formsql .=" AND c.open_identifiedas = 6";
 }
     /* Ramanjaneyulu for showing all courses */
 


    $totalcourses = $DB->count_records_sql($countsql.$formsql.$ordersql, $params);


    $activesql =  " AND c.visible = :suspended ";
    $params['suspended'] = 1;
    $activeusers = 0;
    $totalactive = $DB->count_records_sql($countsql.$formsql.$activesql.$ordersql, $params);


    $params['suspended'] = 0;
    $inactiveusers = 0;
    $totalinactive =  $DB->count_records_sql($countsql.$formsql.$activesql.$ordersql,$params);    
  

    $courses = $DB->get_records_sql($selectsql.$formsql.$ordersql, $params, $stable->start, $stable->length);


    $ratings_plugin_exist = $core_component::get_plugin_directory('local', 'ratings');
    $courseslist = array();




    if (!empty($courses)) {
        $count = 0;
        foreach ($courses as $key => $course) {



            $course_in_list = new core_course_list_element($course);
            $context = context_course::instance($course->id);
            $category = $DB->get_record('course_categories', array('id'=>$course->category));

            $params = array('courseid'=>$course->id);
            
            $enrolledusersssql = " SELECT COUNT(DISTINCT(ue.id)) as ccount
                                FROM {course} c
                                JOIN {course_categories} cat ON cat.id = c.category
                                JOIN {enrol} e ON e.courseid = c.id AND 
                                            (e.enrol = 'manual' OR e.enrol = 'self') 
                                JOIN {user_enrolments} ue ON ue.enrolid = e.id
                                JOIN {user} u ON u.id = ue.userid AND u.confirmed = 1 
                                                AND u.deleted = 0 AND u.suspended = 0
                                JOIN {local_costcenter} lc ON lc.id = u.open_costcenterid
                                JOIN {role_assignments} as ra ON ra.userid = u.id
                                JOIN {role} as r ON r.id = ra.roleid AND r.shortname = 'employee'
                                WHERE c.id = :courseid";

            $enrolled_count =  $DB->count_records_sql($enrolledusersssql, $params);


            $completedusersssql = " SELECT COUNT(DISTINCT(cc.id)) as ccount
                                FROM {course} c
                                JOIN {course_categories} cat ON cat.id = c.category
                                JOIN {enrol} e ON e.courseid = c.id AND 
                                            (e.enrol = 'manual' OR e.enrol = 'self') 
                                JOIN {user_enrolments} ue ON ue.enrolid = e.id
                                JOIN {user} u ON u.id = ue.userid AND u.confirmed = 1 
                                                AND u.deleted = 0 AND u.suspended = 0
                                JOIN {local_costcenter} lc ON lc.id = u.open_costcenterid
                                JOIN {role_assignments} as ra ON ra.userid = u.id
                                JOIN {role} as r ON r.id = ra.roleid AND r.shortname = 'employee'
                                JOIN {course_completions} as cc 
                                        ON cc.course = c.id AND u.id = cc.userid
                                WHERE c.id = :courseid AND cc.timecompleted IS NOT NULL ";

            $completed_count = $DB->count_records_sql($completedusersssql,$params);

            $coursename = $course->fullname;
            $summarydescription = $course->summary;
            $shortname = $course->shortname;
            $catname = $category->name;
            $catnamestring = strlen($catname) > 12 ? substr($catname, 0, 12)."..." : $catname;
            $courestypes_names = array('2'=>get_string('classroom','local_courses'),'3'=>get_string('elearning', 'local_courses'), '4'=> get_string('learningplan', 'local_courses'), '5' => get_string('program', 'local_courses'), '6' => get_string('certification', 'local_courses'));
            $text_class = array('2'=>'classroom', '3'=>'elearning', '4'=> 'learningpath', '5' => 'program', '6' => 'certification');
            $courestypes = explode(',', $course->open_identifiedas);
            $displayed_names = array();
            foreach ($courestypes as $key => $courestype) {
                $displayed_names[] = '<span class="pl-10 '.$text_class[$courestype].'">'.$courestypes_names[$courestype].'</span>';
            }
            if ($ratings_plugin_exist) {
                require_once($CFG->dirroot.'/local/ratings/lib.php');
                $ratingenable = True;
                $avgratings = get_rating($course->id, 'local_courses');
                $rating_value = $avgratings->avg == 0 ? 'N/A' : $avgratings->avg;
            }else{
                $ratingenable = False;
                $rating_value = 'N/A';
            }
            $classname = '\local_tags\tags';
            if (class_exists($classname)) {
                $tags = new $classname;
                $tagstring = $tags->get_item_tags($component = 'local_courses', $itemtype = 'courses', $itemid = $course->id, $contextid = context_course::instance($course->id)->id, $arrayflag = 0, $more = 0);
                if ($tagstring == "") {
                    $tagstring = 'N/A';
                } else {
                    $tagstring = strlen($tagstring) > 35 ? substr($tagstring, 0, 35).'...' : $tagstring;
                }
                $tagenable = True;
            } else {
                $tagenable = False;
                $tagstring = '';
            }

            if ($course->open_skill > 0){
                $skill = $DB->get_field('local_skill', 'name', array('id' => $course->open_skill));
                if ($skill) {
                    $skillname = $skill;
                } else {
                    $skillname = 'N/A';
                }
            } else {
                $skillname = 'N/A';                
            }
            if($course->open_departmentid == 0){
                $coursedepartment = 'All';
                $shortcoursedepartment = 'All';
            } else{
                $coursedepartment = $DB->get_field('local_costcenter', 'fullname', array('id' => $course->open_departmentid));
                $shortcoursedepartment = strlen($coursedepartment) > 12 ? substr($coursedepartment, 0, 20)."..." : $coursedepartment;

            }
            $displayed_names = implode(',' ,$displayed_names);

 $colgfieldnotvisible = false;
            if (!is_siteadmin() && !has_capability('local/costcenter:manage_multiorganizations', $systemcontext) && !has_capability('local/costcenter:manage_ownorganization', $systemcontext) && has_capability('local/costcenter:manage_owndepartments', $systemcontext)) {
                $colgfieldnotvisible = true;
            }
          
            $courseslist[$count]["coursename"] = $coursename;
            $courseslist[$count]["coursedepartment"] = $coursedepartment;
            $courseslist[$count]["shortcoursedepartment"] = $shortcoursedepartment;
            $courseslist[$count]["secondlabelstring"] = $labelstring->secondlevel;
             $courseslist[$count]["colgfieldnotvisible"] =  $colgfieldnotvisible;
            $courseslist[$count]["shortname"] =  $shortname;
            $courseslist[$count]["skillname"] = $skillname;
            $courseslist[$count]["ratings_value"] = $rating_value;
            $courseslist[$count]["ratingenable"] = $ratingenable;
            $courseslist[$count]["tagstring"] = $tagstring;
            $courseslist[$count]["tagenable"] = $tagenable;
            $courseslist[$count]["catname"] = $catname;
            $courseslist[$count]["catnamestring"] = $catnamestring;
            $courseslist[$count]["enrolled_count"] = $enrolled_count;
            $courseslist[$count]["courseid"] = $course->id;
            $courseslist[$count]["completed_count"] = $completed_count;
            $courseslist[$count]["points"] = $course->open_points != NULL ? $course->open_points: 0;
            $courseslist[$count]["coursetype"] = $displayed_names;
            $courseslist[$count]["course_class"] = $course->visible ? 'active' : 'inactive';
            
            $coursesummary = \local_costcenter\lib::strip_tags_custom($chelper->get_course_formatted_summary($course_in_list,
                    array('overflowdiv' => false, 'noclean' => false, 'para' => false)));
            $summarydescription = $coursesummary;
            $summarystring = strlen($coursesummary) > 100 ? substr($coursesummary, 0, 100)."..." : $coursesummary;
            $courseslist[$count]["coursesummary"] = \local_costcenter\lib::strip_tags_custom($summarystring);
            $courseslist[$count]["summarydescription"] = \local_costcenter\lib::strip_tags_custom($summarydescription);
    
            // Course image.
            if (file_exists($CFG->dirroot.'/local/includes.php')) {
                require_once($CFG->dirroot.'/local/includes.php');
                $includes = new user_course_details();
                $courseimage = $includes->course_summary_files($course);

                if (is_object($courseimage)) {
                    $courseslist[$count]["courseimage"] = $courseimage->out();                    
                } else {
                    $courseslist[$count]["courseimage"] = $courseimage;
                }                
            }            

            $courseslist[$count]["courseurl"] = $CFG->wwwroot."/course/view.php?id=".$course->id;
            $enrolid = $DB->get_field('enrol', 'id', array('enrol'=>'manual', 'courseid'=>$course->id));
            
            if (has_capability('local/courses:enrol',$systemcontext)&&has_capability('local/courses:manage', $systemcontext)) {
                $courseslist[$count]["enrollusers"] = $CFG->wwwroot."/local/courses/courseenrol.php?id=".$course->id."&enrolid=".$enrolid;
            }
            
            $categorycontext = context_coursecat::instance($course->category);
            
            if (has_capability('local/courses:update',$systemcontext)&&has_capability('local/courses:manage', $systemcontext)&&has_capability('moodle/course:update', $systemcontext)) {

                $courseedit = html_writer::link('javascript:void(0)', html_writer::tag('i', '', array('class' => 'fa fa-pencil icon')), array('title' => get_string('edit'), 'alt' => get_string('edit'),'data-action' => 'createcoursemodal', 'class'=>'createcoursemodal', 'data-value'=>$course->id, 'onclick' =>'(function(e){ require("local_opencourses/courseAjaxform").init({contextid:'.$categorycontext->id.', component:"local_opencourses", callback:"custom_opencourse_form", form_status:0, plugintype: "local", pluginname: "opencourses", courseid: ' . $course->id . ' }) })(event)'));

                $courseslist[$count]["editcourse"] = $courseedit;
                if ($course->visible) {
                    $icon = 't/hide';
                    $string = get_string('le_active', 'local_courses');
                    $title = get_string('le_inactive', 'local_courses');
                } else {
                    $icon = 't/show';
                    $string = get_string('le_inactive', 'local_courses');
                    $title = get_string('le_active', 'local_courses');
                }
                $image = $OUTPUT->pix_icon($icon, $title, 'moodle', array('class' => 'iconsmall', 'title' => ''));
                $params = json_encode(array('coursename' => $coursename, 'coursestatus' => $course->visible));
                $course_exists = $DB->record_exists('local_cc_semester_courses', array('courseid' => $course->id));

                if($course_exists){
                    $courseslist[$count]["update_status"] .= html_writer::link('javascript:void(0)', $OUTPUT->pix_icon('t/hide', get_string('le_inactive','local_courses'), 'moodle', array('')), array('title' => get_string('le_inactive'), 'id' => "courses_delete_confirm_".$course->id,'onclick'=>'(function(e){ require(\'local_courses/courseAjaxform\').coursenothide({action:\'coursenothide\' , id: ' . $course->id . ', name:"'.$coursename.'" }) })(event)'));
                }else{
                    $courseslist[$count]["update_status"] .= html_writer::link("javascript:void(0)", $image, array('data-fg'=>"d", 'data-method' => 'course_update_status','data-plugin' => 'local_courses', 'data-params' => $params, 'data-id'=>$course->id));
                }

                if (!empty($autoenroll_plugin_exist)) {
                    $autoplugin = enrol_get_plugin('auto');
                    $instance = $autoplugin->get_instance_for_course($course->id);
                    if ($instance) {
                        if ($instance->status == ENROL_INSTANCE_DISABLED) {
                            
                        $courseslist[$count]["auto_enrol"] = $CFG->wwwroot."/enrol/auto/edit.php?courseid=".$course->id."&id=".$instance->id;
                        }
                    }
                }
            }
            
            if (has_capability('local/courses:delete', $systemcontext)&&has_capability('local/courses:manage', $systemcontext)) {
                $course_exists = $DB->record_exists('local_cc_semester_courses', array('courseid' => $course->id));


                $course_curriculum = $DB->get_field_sql("SELECT curriculumid FROM {local_cc_semester_courses} WHERE courseid = $course->id");

                $program_curriculum_id = $DB->get_field('local_program','id',array('curriculumid' => $course_curriculum));
                $programactive_exists = $DB->record_exists('local_program_levels', array('programid' =>$program_curriculum_id,'active' => 1));

                if ($programactive_exists) {
                    $deleteactionshtml = html_writer::link('javascript:void(0)', $OUTPUT->pix_icon('t/delete', get_string('delete'), 'moodle', array('')), array('title' => get_string('delete'), 'id' => "courses_delete_confirm_".$course->id,'onclick'=>'(function(e){ require(\'local_courses/courseAjaxform\').coursenotdelete({action:\'coursenotDelete\' , id: ' . $course->id . ', name:"'.$coursename.'" }) })(event)'));
                    $courseslist[$count]["deleteaction"] = $deleteactionshtml;
                } else{
                    $deleteactionshtml = html_writer::link('javascript:void(0)', $OUTPUT->pix_icon('t/delete', get_string('delete'), 'moodle', array('')), array('title' => get_string('delete'), 'id' => "courses_delete_confirm_".$course->id,'onclick'=>'(function(e){ require(\'local_courses/courseAjaxform\').deleteConfirm({action:\'deletecourse\' , id: ' . $course->id . ', name:"'.$coursename.'" }) })(event)'));
                    $courseslist[$count]["deleteaction"] = $deleteactionshtml;
                }

 

 // $student_enrolments = html_writer::link('javascript:void(0)', html_writer::tag('i', '', array('class' => 'fa fa-users icon')), array('title' => get_string('edit'), 'alt' => get_string('edit'),'data-action' => 'createcoursemodal', 'class'=>'createcoursemodal', 'data-value'=>$course->id, 'onclick' =>'(function(e){ require("local_courses/courseAjaxform").init({contextid:'.$categorycontext->id.', component:"local_courses", callback:"custom_course_form", form_status:0, plugintype: "local", pluginname: "courses", courseid: ' . $course->id . ' }) })(event)'));
              
                    




            }
            

                // if((is_siteadmin() && has_capability('local/costcenter:manage_ownorganization',$systemcontext) && has_capability('local/costcenter:manage_owndepartments',$systemcontext))){
                  $studentroleid = $DB->get_field('role', 'id', array('shortname' => 'student'));


//$CFG->wwwroot . '/local/courses/courseenrol.php?enrolid='.$enrolid.'&roleid='.$studentroleid.'&id='.$course->id.'&programid=0&costcenterid='.$course->open_costcenterid.'&departmentid='.$course->open_departmentid.'&subdepartmentid='.$course->open_subdepartment.'&batchid=0';
 

                $student_enrolments = html_writer::link(new moodle_url('/local/opencourses/courseenrol.php?enrolid='.$enrolid.'&roleid='.$studentroleid.'&id='.$course->id.'&programid=0&costcenterid='.$course->open_costcenterid.'&departmentid='.$course->open_departmentid.'&subdepartmentid='.$course->open_subdepartment.'&batchid=0'),$OUTPUT->pix_icon('i/enrolusers',get_string('enrolstudent', 'local_opencourses')));
                
    $courseslist[$count]["student_enrolments"] = $student_enrolments;


     $facultyroleid = $DB->get_field('role', 'id', array('shortname' => 'editingteacher'));


   //  '/local/courses/courseenrol.php?enrolid='.$enrolid.'&roleid='.$facultyroleid.'&id='.$course->id.'&programid=0&costcenterid='.$course->open_costcenterid.'&departmentid='.$course->open_departmentid.'&subdepartmentid='.$course->open_subdepartment

    $faculty_enrolments = html_writer::link(new moodle_url('/local/opencourses/courseenrol.php?enrolid='.$enrolid.'&roleid='.$facultyroleid.'&id='.$course->id.'&programid=0&costcenterid='.$course->open_costcenterid.'&departmentid='.$course->open_departmentid.'&subdepartmentid='.$course->open_subdepartment),html_writer::tag('i','',array('class'=>'fa fa-user-plus icon text-muted', 'title' => get_string('enrolfaculty','local_opencourses'), 'alt' => get_string('enrolfaculty', 'local_opencourses'))));

     $courseslist[$count]["faculty_enrolments"] = $faculty_enrolments;
         // }  
            if (has_capability('local/courses:grade_view', $systemcontext)&&has_capability('local/courses:manage', $systemcontext)) {
                $courseslist[$count]["grader"] = $CFG->wwwroot."/grade/report/grader/index.php?id=".$course->id;
            }
            if (has_capability('local/courses:report_view', $systemcontext)&&has_capability('local/courses:manage', $systemcontext)) {
                $courseslist[$count]["activity"] = $CFG->wwwroot."/report/outline/index.php?id=".$course->id;
            }
            if ((has_capability('local/request:approverecord', context_system::instance()) || is_siteadmin())) {
                $courseslist[$count]["requestlink"] = $CFG->wwwroot."/local/request/index.php?courseid=".$course->id;
            }
            $count++;
        }
        $nocourse = false;
        $pagination = false;
    } else {
        $nocourse = true;
        $pagination = false;
    }
    // Check the course instance is not used in any plugin.
    $candelete = true;
    $core_component = new core_component();
    $classroom_plugin_exist = $core_component::get_plugin_directory('local', 'classroom');
    if ($classroom_plugin_exist) {
        $exist_sql = "Select id from {local_classroom_courses} where courseid = ?";
        if ($DB->record_exists_sql($exist_sql, array($course->id)))
        $candelete = false;
    }
    
    $program_plugin_exist = $core_component::get_plugin_directory('local', 'program');
    if ($program_plugin_exist) {
        $exist_sql = "Select id from {local_program_level_courses} where courseid = ?";
        if ($DB->record_exists_sql($exist_sql, array($course->id)))
        $candelete = false;
    }
    $certification_plugin_exist = $core_component::get_plugin_directory('local', 'certification');
    if ($certification_plugin_exist) {
        $exist_sql = "Select id from {local_certification_courses} where courseid = ?";
        if ($DB->record_exists_sql($exist_sql, array($course->id)))
        $candelete = false;
    }

 
    $coursesContext = array(
        "hascourses" => $courseslist,
        "nocourses" => $nocourse,
        "totalcourses" => $totalcourses,
        "totalactive" => $totalactive,
        "totalinactive" => $totalinactive,

        "length" => count($courseslist),
        "actions"=>(((has_capability('local/courses:enrol',
        context_system::instance())|| has_capability('local/courses:update',
        context_system::instance())||has_capability('local/courses:delete',
        context_system::instance()) || has_capability('local/courses:grade_view',
        context_system::instance())|| has_capability('local/courses:report_view',
        context_system::instance())) || is_siteadmin())&&has_capability('local/courses:manage', $systemcontext)) ? true : false,
        "enrol"=>((has_capability('local/courses:enrol',
        context_system::instance())  || is_siteadmin())&&has_capability('local/courses:manage', $systemcontext)) ? true : false,
        "update"=>((has_capability('local/courses:update',
        context_system::instance()) || is_siteadmin())&&has_capability('local/courses:manage', $systemcontext)) ? true : false,
        "delete"=>((has_capability('local/courses:delete',
        context_system::instance()) || is_siteadmin())&&has_capability('local/courses:manage', $systemcontext)) ? true : false,
        "grade_view"=>((has_capability('local/courses:grade_view',
        context_system::instance()) || is_siteadmin())&&has_capability('local/courses:manage', $systemcontext)) ? true : false,
        "report_view"=>((has_capability('local/courses:report_view',
        context_system::instance()) || is_siteadmin())&&has_capability('local/courses:manage', $systemcontext)) ? true : false,
        "request_view"=>((has_capability('local/request:approverecord', $systemcontext)) || is_siteadmin()) ? true : false, 
    );

    return $coursesContext;

}



function local_opencourses_output_fragment_custom_opencourse_form($args){
    global $DB, $CFG, $PAGE;

  
    $args = (object) $args;
    $context = $args->context;
    $renderer = $PAGE->get_renderer('local_opencourses');
    $courseid = $args->courseid;
    $o = '';

    if ($courseid) {
        $course = get_course($courseid);
        $course = course_get_format($course)->get_course();
        $category = $DB->get_record('course_categories', array('id'=>$course->category), '*', MUST_EXIST);
        $coursecontext = context_course::instance($course->id);
        require_capability('moodle/course:update', $coursecontext);
    } else {
        $category = $CFG->defaultrequestcategory;
    }
    $formdata = [];
    if (!empty($args->jsonformdata)) {
        $serialiseddata = json_decode($args->jsonformdata);
        parse_str($serialiseddata, $formdata);
    }
    if (!empty($course) && empty($formdata)) {
        $formdata = (array)$course;
    }

    if ($courseid > 0) {
        $heading = get_string('updatecourse', 'local_courses');
        $collapse = false;
        $data = $DB->get_record('course', array('id'=>$courseid));
    }
    // Populate course tags.
    $editoroptions = array('maxfiles' => EDITOR_UNLIMITED_FILES, 'maxbytes' => $CFG->maxbytes, 'trusttext' => false, 
                     'noclean' => true,'autosave' => false);
    $overviewfilesoptions = course_overviewfiles_options($course);
    if ($courseid) {
        // Add context for editor.
        $editoroptions['context'] = $coursecontext;
        $editoroptions['subdirs'] = file_area_contains_subdirs($coursecontext, 'course', 'summary', 0);
        $course = file_prepare_standard_editor($course, 'summary', $editoroptions, $coursecontext, 'course', 'summary', 0);
        if ($overviewfilesoptions) {
            file_prepare_standard_filemanager($course, 'overviewfiles', $overviewfilesoptions, 
                $coursecontext, 'course', 'overviewfiles', 0);
        }
        $get_coursedetails=$DB->get_record('course', array('id'=>$course->id));
    } else {
        // Editor should respect category context if course context is not set.
        $editoroptions['context'] = $catcontext;
        $editoroptions['subdirs'] = 0;
        $course = file_prepare_standard_editor($course, 'summary', $editoroptions, null, 'course', 'summary', null);
        if ($overviewfilesoptions) {
            file_prepare_standard_filemanager($course, 'overviewfiles', $overviewfilesoptions, null, 'course', 'overviewfiles', 0);
        }
    }
    if ($formdata['open_points'] > 0) {
        $formdata['open_enablepoints'] = true;
    }

    $params = array(
        'course' => $course,
        'category' => $category,
        'editoroptions' => $editoroptions,
        'returnto' => $returnto,
        'get_coursedetails'=>$get_coursedetails,
        'form_status' => $args->form_status,
        'costcenterid' => $data->open_costcenterid
    );
    $mform = new custom_opencourse_form(null, $params, 'post', '', null, true, $formdata);

    // Used to set the courseid.
    $mform->set_data($formdata);

    if (!empty($args->jsonformdata) && strlen($args->jsonformdata)>2) {
        // If we were passed non-empty form data we want the mform to call validation functions and show errors.
        $mform->is_validated();
    }
    $formheaders = array_keys($mform->formstatus);
    $nextform = array_key_exists($args->form_status, $formheaders);
    if ($nextform === false) {
        return false;
    }
    ob_start();
    $formstatus = array();
    foreach (array_values($mform->formstatus) as $k => $mformstatus) {
        $activeclass = $k == $args->form_status ? 'active' : '';
        $formstatus[] = array('name' => $mformstatus, 'activeclass' => $activeclass);
    }
    $formstatusview = new \local_opencourses\output\form_status($formstatus);
    $o .= $renderer->render($formstatusview);
    $o = $PAGE->requires->js_call_amd('local_opencourses/courseAjaxform', 'getCatlist');
    $mform->display();
    $o .= ob_get_contents();
    ob_end_clean();
    return $o;
}

/**
 * Serve the delete category form as a fragment.
 *
 * @param array $args List of named arguments for the fragment loader.
 * @return string
 */
function local_opencourses_output_fragment_deletecategory_form($args){
 global $DB, $CFG, $PAGE;

    require_once($CFG->libdir . '/questionlib.php');

    $args = (object) $args;
    $context = $args->context;
    $categoryid = $args->categoryid;
    $o = '';

    $formdata = [];
    if (!empty($args->jsonformdata)) {
        $serialiseddata = json_decode($args->jsonformdata);
        parse_str($serialiseddata, $formdata);
    }

    if ($categoryid) {
        $category = core_course_category::get($categoryid);
        $context = context_coursecat::instance($category->id);
    }else {
        $category = core_course_category::get_default();
        $categoryid = $category->id;
        $context = context_coursecat::instance($category->id);
    }

    $mform = new local_courses\form\deletecategory_form(null, $category, 'post', '', null, true, $formdata);
    // Used to set the courseid.

    if (!empty($args->jsonformdata)) {
        // If we were passed non-empty form data we want the mform to call validation functions and show errors.
        $mform->is_validated();
    }

    ob_start();
    $mform->display();
    $o .= ob_get_contents();
    ob_end_clean();

    return $o;
}

/**
 * Serve the new course category form as a fragment.
 *
 * @param array $args List of named arguments for the fragment loader.
 * @return string
 */
function local_opencourses_output_fragment_coursecategory_form($args){
 global $DB, $CFG, $PAGE;

    $args = (object) $args;
    $context = $args->context;
    $categoryid = $args->categoryid;

    $o = '';

    $formdata = [];
    if (!empty($args->jsonformdata)) {
        $serialiseddata = json_decode($args->jsonformdata);
        parse_str($serialiseddata, $formdata);
    }
    if (empty($formdata) && $categoryid > 0) {

        $data = $DB->get_record('course_categories', array('id'=>$categoryid));
        $formdata = new stdClass();
        $formdata->id = $data->id;
        $formdata->parent = $data->parent;
        $formdata->name = $data->name;
        $formdata->idnumber = $data->idnumber;
        $formdata->cr_description_editor['text'] = $data->description;
    }

    if ($categoryid) {
        $coursecat = core_course_category::get($categoryid, MUST_EXIST, true);
        $category = $coursecat->get_db_record();
        $context = context_coursecat::instance($categoryid);

         $itemid = 0;
    } else {
        $parent = optional_param('parent', 0, PARAM_INT);

        if ($parent) {
            $DB->record_exists('course_categories', array('id' => $parent), '*', MUST_EXIST);
            $context = context_coursecat::instance($parent);
        } else {
            $context = context_system::instance();
        }
        $category = new stdClass();
        $category->id = 0;
        $category->parent = $parent;
    }

    $params = array(
    'categoryid' => $categoryid,
    'parent' => $category->parent,
    'context' => $context,
    'itemid' => $itemid
    );

    $mform = new local_courses\form\coursecategory_form(null, $params, 'post', '', null, true, $formdata);
    // Used to set the courseid.
    $mform->set_data($formdata);

    if (!empty($args->jsonformdata)) {
        // If we were passed non-empty form data we want the mform to call validation functions and show errors.
        $mform->is_validated();
    }

    ob_start();
    $mform->display();
    $o .= ob_get_contents();
    ob_end_clean();

    return $o;
}
/**
 * Serve the table for course categories
 *
 * @param array $args List of named arguments for the fragment loader.
 * @return string 
 */
function local_opencourses_output_fragment_coursecategory_display($args){
    global $DB, $CFG, $PAGE, $OUTPUT;

    $args = (object) $args;
    $context = $args->context;
    $categoryid = $args->categoryid;

    $formdata = [];
    if (!empty($args->jsonformdata)) {
        $serialiseddata = json_decode($args->jsonformdata);
        parse_str($serialiseddata, $formdata);
    }

    $table = new html_table();
    $table->id = 'popup_category';
    $table->align = ['left', 'center', 'center', 'center', 'center'];
    $table->head = array(get_string('course_name', 'local_courses'), get_string('enrolledusers', 'local_courses'),get_string('completed_users', 'local_courses'), get_string('type', 'local_courses'),get_string('actions', 'local_courses'));
    $courses = $DB->get_records_sql("SELECT c.id, c.category, c.fullname FROM {course} c WHERE c.id > 1
                                     AND c.category = ?", [$categoryid]);
    if ($courses) {
    $data=array();
    foreach($courses as $course){
        $row = array();
        $row[] = html_writer::link(new moodle_url('/course/view.php', array('id'=>$course->id)), $course->fullname);
        $course_sql = "SELECT count(ue.userid) as enrolled, count(cc.course) as completed
                            FROM {user_enrolments} as ue
                            JOIN {enrol} as e ON e.id = ue.enrolid
                            RIGHT JOIN {course} as c ON c.id = e.courseid
                            LEFT JOIN {course_completions} cc ON cc.course = e.courseid 
                            and ue.userid = cc.userid and cc.timecompleted IS NOT NULL
                            WHERE c.id = ?
                                group by e.courseid";
        $course_stats = $DB->get_record_sql($course_sql, [$course->id]);
       if ($course_stats->enrolled) {
            $row[] = $course_stats->enrolled;
        } else {
             $row[] = "N/A";
        }
        if ($course_stats->completed) {
            $row[] = $course_stats->completed;
        } else {
             $row[] = "N/A";
        }
        $ilt_sql = "SELECT open_identifiedas from {course}  WHERE id = ? " ;  
        $ilt_stats = $DB->get_record_sql($ilt_sql, [$course->id]);
        $types = explode(',', $ilt_stats->open_identifiedas);
        $classtype = array();
        foreach($types as $type){

            if ($type == 2) {
              $classtype[0]= get_string('classroom', 'local_courses');
            }
            if ($type == 3) {
             $classtype[2]= get_string('elearning', 'local_courses');
            }
            if ($type == 4) {
             $classtype[3]= get_string('learningplan', 'local_courses');
            }
            if ($type == 5) {
             $classtype[5]= get_string('program', 'local_courses');
            }
            if ($type == 6) {
             $classtype[6]= get_string('certification', 'local_courses');
            }
        }
        $ctype = implode(',' ,$classtype);

        if ($ctype) {

            $row[] = $ctype;
        } else {
             $row[] = "N/A";
        }



        $enrolid = $DB->get_field('enrol', 'id', array('courseid' => $course->id, 'enrol' => 'manual'));

        $enrolicon = html_writer::link(new moodle_url('/local/courses/courseenrol.php', array('id' => $course->id, 'enrolid' => $enrolid)), html_writer::tag('i', '', array('class' => 'fa fa-user-plus icon text-muted', 'title' => get_string('enrol', 'local_courses'), 'alt' => get_string('enrol'))));
        $actions = $enrolicon.' '.$editicon;
        $row[] = $actions;

        $data[] = $row;
    }
    $table->data = $data;
    $output = html_writer::table($table);
    $output .= html_writer::script("$('#popup_category').DataTable({
        'language': {
            paginate: {
            'previous': '<',
            'next': '>'
            }
        },
        'bInfo' : false,
        lengthMenu: [
            [5, 10, 25, 50, 100, -1],
            [5, 10, 25, 50, 100, ".get_string('all')."]
        ]
    });");
    } else {
        $output = get_string('nocourseavailiable', 'local_courses');
    }

    return $output;
}

/**
 * Serve the table for course status
 *
 * @param array $args List of named arguments for the fragment loader.
 * @return string
 */
function local_opencourses_output_fragment_coursestatus_display($args) {
    global $DB, $CFG, $PAGE, $OUTPUT, $USER;
    $args = (object) $args;
    $course = $DB->get_record('course', array('id' => $args->courseid));
    $info = new completion_info($course);
        
    // Is course complete?
    $coursecomplete = $info->is_course_complete($USER->id);

    // Has this user completed any criteria?
    $criteriacomplete = $info->count_course_user_data($USER->id);
    $params = array(
        'userid' => $USER->id,
        'course' => $course->id,
    );
    $completions = $info->get_completions($USER->id);
    $ccompletion = new completion_completion($params);

    $rows = array();
    // Loop through course criteria.
    foreach ($completions as $completion) {
        $criteria = $completion->get_criteria();
        $row = array();
            $row['type'] = $criteria->criteriatype;
            $row['title'] = $criteria->get_title();
            $row['complete'] = $completion->is_complete();
            $row['timecompleted'] = $completion->timecompleted;
            $row['details'] = $criteria->get_details($completion);
            $rows[] = $row;

        }
    // Print table.
    $last_type = '';
    $agg_type = false;

    $table = new html_table();
    $table->head = array(get_string('criteriagroup', 'format_tabtopics'),get_string('criteria', 'format_tabtopics'), get_string('requirement', 'format_tabtopics'), get_string('complete', 'format_tabtopics'), get_string('completiondate', 'format_tabtopics'));
    $table->size = array('20%', '20%', '25%', '5%', '30%');
    $table->align = array('left', 'left', 'left', 'center', 'center');
    $table->id = 'scrolltable';
    foreach ($rows as $row) {
        if ($last_type !== $row['details']['type']) {
        $last_type = $row['details']['type'];
        $agg_type = true;
        } else {
        // Display aggregation type.
            if ($agg_type) {
                $agg = $info->get_aggregation_method($row['type']);
                $last_type .= '('. html_writer::start_tag('i');
                if ($agg == COMPLETION_AGGREGATION_ALL) {
                    $last_type .= core_text::strtolower(get_string('all', 'completion'));
                } else {
                    $last_type .= core_text::strtolower(get_string('any', 'completion'));
                }
                $last_type .= html_writer::end_tag('i') .core_text::strtolower(get_string('required')).')';
                $agg_type = false;
            }
        }
        if ($row['timecompleted']) {
            $timecompleted = userdate($row['timecompleted'], get_string('strftimedate', 'langconfig'));
        } else {
            $timecompleted = '-';
        }
        $table->data[] = new html_table_row(array($last_type, $row['details']['criteria'], $row['details']['requirement'], $row['complete'] ? get_string('yes') : get_string('no'), $timecompleted));
    }
    $output = html_writer::table($table);
    $output .= html_writer::script("
         $(document).ready(function(){
            var table_rows = $('#scrolltable tr');
            // if(table_rows.length>6){
                $('#scrolltable').dataTable({
                    'searching': false,
                    'language': {
                        'paginate': {
                            'next': '>',
                            'previous': '<'
                        }
                    },
                    'pageLength': 5,
                });
            // }
        });
    ");
    return $output;
}

/*
* todo provides form element - courses
* @param $mform formobject
* return void
*/
 
 
/**
 * Returns onlinetests tagged with a specified tag.
 *
 * @param local_tags_tag $tag
 * @param bool $exclusivemode if set to true it means that no other entities tagged with this tag
 *             are displayed on the page and the per-page limit may be bigger
 * @param int $fromctx context id where the link was displayed, may be used by callbacks
 *            to display items in the same context first
 * @param int $ctx context id where to search for records
 * @param bool $rec search in subcontexts as well
 * @param int $page 0-based number of page being displayed
 * @return \local_tags\output\tagindex
 */
function local_opencourses_get_tagged_courses($tag, $exclusivemode = false, $fromctx = 0, $ctx = 0, $rec = 1, $page = 0, $sort = '') {
    global $CFG, $PAGE;
    // Prepare for display of tags related to tests.
    $perpage = $exclusivemode ? 10 : 5;
    $displayoptions = array(
        'limit' => $perpage,
        'offset' => $page * $perpage,
        'viewmoreurl' => null,
    );
    $renderer = $PAGE->get_renderer('local_courses');
    $totalcount = $renderer->tagged_courses($tag->id, $exclusivemode, $ctx, $rec, $displayoptions, $count = 1, $sort);
    $content = '';
    $content = $renderer->tagged_courses($tag->id, $exclusivemode, $ctx, $rec, $displayoptions, 0, $sort);
    $totalpages = ceil($totalcount / $perpage);
    if ($totalcount)
    return new local_tags\output\tagindex($tag, 'local_courses', 'courses', $content,
            $exclusivemode, $fromctx, $ctx, $rec, $page, $totalpages);
    else
    return '';
}
/**
* todo sql query departmentwise
* @param  $systemcontext object 
* @return array
**/

 
function local_opencourses_request_dependent_query($aliasname){
    $returnquery = " WHEN ({$aliasname}.compname LIKE 'elearning') THEN (SELECT fullname from {course} 
                     WHERE id = {$aliasname}.componentid) ";
    return $returnquery;
}
 
/**
 * Serve the new course form as a fragment.
 *
 * @param array $args List of named arguments for the fragment loader.
 * @return string
 */
function local_opencourses_output_fragment_custom_courseevidence_form($args){
    global $DB, $CFG, $PAGE;
    $args = (object) $args;
    $o = '';

    $params = array(
        'courseid' => $args->courseid,
        'userid' => $args->userid,
    );
    $serialiseddata = json_decode($args->jsonformdata);
    parse_str($serialiseddata, $formdata);
    $mform = new custom_courseevidence_form(null, $params, 'post', '', null, true, $formdata);
   
    ob_start();
    $mform->display();
    $o .= ob_get_contents();
    ob_end_clean();
    return $o;
}

function local_opencourses_render_navbar_output() {
    global $PAGE;

    $PAGE->requires->js_call_amd('local_opencourses/courseAjaxform', 'load');
}
function local_opencourses_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {
    // Check the contextlevel is as expected - if your plugin is a block, this becomes CONTEXT_BLOCK, etc.

    // Make sure the filearea is one of those used by the plugin.
    if ($filearea !== 'files_filemanager') {
        return false;
    }

    $itemid = array_shift($args);

    $filename = array_pop($args);
    if (!$args) {
        $filepath = '/';
    } else {
        $filepath = '/'.implode('/', $args).'/';
    }

    // Retrieve the file from the Files API.
    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'local_courses', $filearea, $itemid, $filepath, $filename);
    if (!$file) {
        return false;
    }
    send_file($file, $filename, null, 0, false, 0, $options);
}
/**
 * Serve the new course form as a fragment.
 *
 * @param array $args List of named arguments for the fragment loader.
 * @return string
 */
function local_opencourses_output_fragment_custom_selfcompletion_form($args) {
    global $DB, $CFG, $PAGE;
    $args = (object) $args;
    
    return get_string('selfcompletionconfirm', 'local_courses', $args->coursename);
}

function emailid_filter($mform,$query='',$searchanywhere=false, $page=0, $perpage=25){
  
 

     global $DB, $USER;
    $context = context_system::instance();
     $course_id = optional_param('id', '1', PARAM_INT);
    $course = $DB->get_record('course', array('id' => $course_id));
    $batchid = optional_param('batchid',0,PARAM_INT);
     $roleid = optional_param('roleid', '3', PARAM_INT);
     $costcenterid = optional_param('costcenterid', '0', PARAM_INT);
     $departmentid = optional_param('departmentid', '0', PARAM_INT);
     $subdepartmentid = optional_param('subdepartmentid', '0', PARAM_INT);

 
 
    $params['suspended'] = 0;
    $params['deleted'] = 0;

    if($roleid == 5){
         $open_type = 1;
    }

    if($roleid == 3){
         $open_type = 0;
    }
   
    if ($total == 0) {
         $sql = "SELECT u.id, concat(u.firstname,' ', u.lastname,' ','(',u.email,')') as fullname";
    } else {
        $sql = "SELECT count(u.id) as total";
    }
    if($roleid == 3){
        $sql .= " FROM {user} AS u WHERE u.roleid=3 AND u.id > 2 AND u.suspended = :suspended AND u.deleted = :deleted AND u.open_type = 0 ";
    }
    if($roleid == 5){


     //  $sql .= " FROM {user} AS u JOIN {cohort_members} AS cm ON u.id = cm.userid WHERE  u.id > 2 AND u.suspended = :suspended AND u.deleted = :deleted AND u.open_type = 1";

        $sql .= " FROM {user} AS u  WHERE  u.id > 2 AND u.suspended = :suspended AND u.deleted = :deleted AND u.open_type = 1";

    /* $sql .=   " FROM {user} AS u JOIN {role_assignments} AS ra ON ra.userid = u.id JOIN {context} ctx ON ctx.id = ra.contextid
             JOIN {role} r ON r.id = ra.roleid
              WHERE r.id=$role_id
            
            
            AND  u.id > 2 AND u.suspended = :suspended AND u.deleted = :deleted AND u.open_departmentid IS NOT NULL AND u.open_type=$open_type";*/


    }

    if ($lastitem != 0) {
       $sql .= " AND u.id > $lastitem";
    }
    if (!is_siteadmin()) {
        $user_detail = $DB->get_record('user', array('id'=>$USER->id));
        $sql .= " AND u.open_costcenterid = :costcenter";
        $params['costcenter'] = $course ->open_costcenterid;
        if (has_capability('local/costcenter:manage_owndepartments', 
                $context) && !has_capability('local/costcenter:manage_ownorganization',$context)) {
            $sql .= " AND u.open_departmentid = :department";
            $params['department'] = $user_detail->open_departmentid;
        }
    }
    $sql .= " AND u.id <> $USER->id";

 
    if (!empty($departmentid)) {
         $sql .=" AND u.open_departmentid IN ({$departmentid})";
    }
 
    if (!empty($subdepartmentid)) {
         $sql .=" AND u.open_subdepartment IN ({$subdepartmentid})";
    }
         
 
        if (!empty($costcenterid)) {
         $sql .=" AND u.open_costcenterid IN ({$costcenterid})";
    }
 

 



    // $order = " ORDER BY concat(u.firstname,' ',u.lastname) ASC ";
    if ($total==0) {
        $userslist = $DB->get_records_sql_menu($sql.$order, $params);
    } else {
        $userslist = $DB->count_records_sql($sql, $params);
    }
   // return $availableusers;


 
    $options = array(
      //  'ajax' => 'local_mooccourses/form-options-selector',
        'multiple' => true,
        'data-action' => 'emailid',
        'data-options' => json_encode(array('id' => 0)),
        'placeholder' => get_string('email')
    );

    $select = $mform->addElement('autocomplete', 'emailid', '',$userslist,$options);
    $mform->setType('emailid', PARAM_RAW);
}


function opencourses_filter($mform){
 global $DB, $USER;
    $systemcontext = context_system::instance();
    $sql = "SELECT id, fullname FROM {course} WHERE id > 1 AND open_identifiedas = 6";
    $sql2 = " AND open_costcenterid = ?";
    $sql3 = " AND open_departmentid = ?";
    if (is_siteadmin()) {
       $courseslist = $DB->get_records_sql_menu($sql);
    } else if (has_capability('local/costcenter:manage_ownorganization', $systemcontext)){
        $courseslist = $DB->get_records_sql_menu($sql.$sql2, [$USER->open_costcenterid]);
    } else if (has_capability('local/costcenter:manage_owndepartments', $systemcontext)){
        $courseslist = $DB->get_records_sql_menu($sql.$sql2.$sql3, [$USER->open_costcenterid, $USER->open_departmentid]);
    }
 
    $select = $mform->addElement('autocomplete', 'opencourses', '', $courseslist, array('placeholder' => get_string('course')));
    $mform->setType('opencourses', PARAM_RAW);
    $select->setMultiple(true);
}

// function emailid_filter($mform){
//     echo "ffff";
// }

function mooccourse_enrolled_users1($type = null, $course_id = 0, $params, $total=0, $offset1=-1, $perpage=-1, $lastitem=0){
    global $DB, $USER;
  
 

   $role_id = $params['roleid'];
 
 if($role_id == 5){
    $open_type = 1;
 }
if($role_id == 3)
{
    $open_type = 0;
}
 
    $organization = $params['organization'];
    $context = context_system::instance();
    $course = $DB->get_record('course', array('id' => $course_id));
 
    $params['suspended'] = 0;
    $params['deleted'] = 0;


    if($total==0){

         $sql = "SELECT u.id,concat(u.firstname,' ',u.lastname,' ','(',u.email,')') as fullname";
    }else{
        $sql = "SELECT count(u.id) as total";
    }

 /*$sql.=" FROM {user} AS u 
            JOIN {local_costcenter} cc ON cc.id = u.open_departmentid
            JOIN {course} as c ON c.open_departmentid = u.open_departmentid AND c.id=$course_id
            
           // JOIN {role} as r ON r.id =$role_id
            WHERE  u.id > 2 AND u.suspended = :suspended AND u.deleted = :deleted AND u.open_departmentid IS NOT NULL";*/


        $sql.=" FROM {user} AS u 
            JOIN {local_costcenter} cc ON cc.id = u.open_costcenterid";


if($course->open_departmentid != 0){
    $sql.=" JOIN {course} as c ON c.open_departmentid = u.open_departmentid";
}else{
    $sql.=" JOIN {course} as c ON c.open_costcenterid = u.open_costcenterid";
}

 
 // $sql.=" JOIN {course} as c ON c.open_departmentid = u.open_departmentid";
           
     
        $sql.= " JOIN {local_costcenter} cc ON cc.id = u.open_costcenterid

        JOIN {role_assignments} as ra ON ra.userid = u.id 
            JOIN {role} as r ON r.id = ra.roleid
            JOIN {context} ctx ON ctx.id = ra.contextid

              WHERE r.id=$role_id
            AND c.id=$course_id
            
            AND  u.id > 2 AND u.suspended = :suspended AND u.deleted = :deleted AND u.open_departmentid IS NOT NULL AND u.open_type=$open_type";
 
    /*
     JOIN {role} as r ON r.id = u.open_role */
 

    if($lastitem!=0){
       $sql.=" AND u.id > $lastitem";
    }
    if (!is_siteadmin()) {

        $user_detail = $DB->get_record('user', array('id'=>$USER->id));
        $sql .= " AND u.open_costcenterid = :costcenter";
        $params['costcenter'] = $course ->open_costcenterid;
        if (has_capability('local/costcenter:manage_owndepartments',$context) AND !has_capability('local/costcenter:manage_ownorganization',$context)) {
            $sql .=" AND u.open_departmentid = :department";
            $params['department'] = $user_detail->open_departmentid;
        }
    }

      $sql .=" AND u.id <> $USER->id";
 
    if (!empty($params['email'])) {
         $sql.=" AND u.id IN ({$params['email']})";
    }
    if (!empty($params['emailid'][0])) {
         $sql.=" AND u.id IN ({$params['emailid'][0]})";
    }
    if (!empty($params['uname'])) {
         $sql .=" AND u.id IN ({$params['uname']})";
    }
    if (!empty($params['department'])) {
         $sql .=" AND u.open_departmentid IN ({$params['department']})";
    }
    if (!empty($params['organization'])) {
        $organizationID = $params['organization'];
        $sql .=" AND u.open_costcenterid = $organizationID";
    }
        if (!empty($params['organizations'])) {
        $organizationIDs = $params['organizations'];
        $sql .=" AND u.open_costcenterid IN ({$organizationIDs})";
    }
    if (!empty($params['idnumber'])) {
         $sql .=" AND u.id IN ({$params['idnumber']})";
    }
    if (!empty($params['groups'])) {
         $sql .=" AND u.id IN (SELECT userid FROM {cohort_members} WHERE cohortid IN ({$params['groups']}))";
    }
    if ($type=='add') {
        $sql .= " AND u.id NOT IN (SELECT ue.userid
                             FROM {user_enrolments} AS ue 
                             JOIN {enrol} e ON (e.id = ue.enrolid and e.courseid=$course_id and (e.enrol='manual' OR e.enrol='self')))";
    }elseif ($type=='remove') {
        $sql .= " AND u.id IN (SELECT ue.userid
                             FROM {user_enrolments} AS ue 
                             JOIN {enrol} e ON (e.id = ue.enrolid and e.courseid=$course_id and (e.enrol='manual' OR e.enrol='self')))";
    }
    // if (!empty($params['roleid'])) {
    //     $roleid = $params['roleid'];
    //     $sql .= " AND u.open_role = $roleid";
    // }
    $order = ' ORDER BY u.id ASC ';
    if($perpage!=-1){
        $order.="LIMIT $perpage";
    }
  


    if($total==0){
        $availableusers = $DB->get_records_sql_menu($sql.$order,$params);
    }else{
        $availableusers = $DB->count_records_sql($sql,$params);
    }
    return $availableusers;
}


function course_enrolled_users2($type = null, $course_id = 0, $params, $total=0, $offset=-1, $perpage=-1, $lastitem=0){
 
    global $DB, $USER;


    $context = context_system::instance();
    $course = $DB->get_record('course', array('id' => $course_id));
    $batchid = optional_param('batchid',0,PARAM_INT);
 
    $params['suspended'] = 0;
    $params['deleted'] = 0;
    $open_type = 1;
 
    $role_id = $params['roleid'];
    if ($total == 0) {
         $sql = "SELECT u.id, concat(u.firstname,' ', u.lastname,' ','(',u.email,')') as fullname";
    } else {
        $sql = "SELECT count(u.id) as total";
    }
    if($params['roleid'] == 3){
        $sql .= " FROM {user} AS u WHERE u.roleid=3 AND u.id > 2 AND u.suspended = :suspended AND u.deleted = :deleted AND u.open_type = 0 ";
    }
    if($params['roleid'] == 5){


     //  $sql .= " FROM {user} AS u JOIN {cohort_members} AS cm ON u.id = cm.userid WHERE  u.id > 2 AND u.suspended = :suspended AND u.deleted = :deleted AND u.open_type = 1";

        $sql .= " FROM {user} AS u  WHERE u.id > 2 AND u.suspended = :suspended AND u.deleted = :deleted AND u.open_type = 1";

    /* $sql .=   " FROM {user} AS u JOIN {role_assignments} AS ra ON ra.userid = u.id JOIN {context} ctx ON ctx.id = ra.contextid
             JOIN {role} r ON r.id = ra.roleid
              WHERE r.id=$role_id
            
            
            AND  u.id > 2 AND u.suspended = :suspended AND u.deleted = :deleted AND u.open_departmentid IS NOT NULL AND u.open_type=$open_type";*/


    }

    if ($lastitem != 0) {
       $sql .= " AND u.id > $lastitem";
    }
    if (!is_siteadmin()) {
        $user_detail = $DB->get_record('user', array('id'=>$USER->id));
        $sql .= " AND u.open_costcenterid = :costcenter";
        $params['costcenter'] = $course ->open_costcenterid;
        if (has_capability('local/costcenter:manage_owndepartments', 
                $context) && !has_capability('local/costcenter:manage_ownorganization',$context)) {
            $sql .= " AND u.open_departmentid = :department";
            $params['department'] = $user_detail->open_departmentid;
        }
    }
    $sql .= " AND u.id <> $USER->id";

    if (!empty($params['email'])) {
         $sql.=" AND u.id IN ({$params['email']})";
    }
    if (!empty($params['uname'])) {
         $sql .=" AND u.id IN ({$params['uname']})";
    }
    // if (!empty($params['department'])) {
    //      $sql .=" AND u.open_departmentid IN ({$params['department']})";
    // }
        if (!empty($params['departments'])) {
         $sql .=" AND u.open_departmentid IN ({$params['departments']})";
    }
    // if (!empty($params['subdepartment'])) {
    //      $sql .=" AND u.open_subdepartment IN ({$params['subdepartment']})";
    // }
        if (!empty($params['subdepartments'])) {
         $sql .=" AND u.open_subdepartment IN ({$params['subdepartments']})";
    }
    // if (!empty($params['organization'])) {
    //      $sql .=" AND u.open_costcenterid IN ({$params['organization']})";
    // }
        if (!empty($params['organizations'])) {
         $sql .=" AND u.open_costcenterid IN ({$params['organizations']})";
    }
    if (!empty($params['idnumber'])) {
         $sql .=" AND u.id IN ({$params['idnumber']})";
    }

    if (!empty($params['location'])) {

        $locations = explode(',', $params['location']);
        list($locationsql, $locationparams) = $DB->get_in_or_equal($locations, SQL_PARAMS_NAMED, 'location');
        $params = array_merge($params, $locationparams);            
        $sql .= " AND u.open_location {$locationsql} ";
    }

    if (!empty($params['hrmsrole'])) {

        $hrmsroles = explode(',', $params['hrmsrole']);
        list($hrmsrolesql, $hrmsroleparams) = $DB->get_in_or_equal($hrmsroles, SQL_PARAMS_NAMED, 'hrmsrole');
        $params = array_merge($params, $hrmsroleparams);            
        $sql .= " AND u.open_hrmsrole {$hrmsrolesql} ";
    }
    if (!empty($params['groups'])) {
         $group_list = $DB->get_records_sql_menu("select cm.id, cm.userid from {cohort_members} cm, {user} u where u.id = cm.userid AND u.deleted = 0 AND u.suspended = 0 AND cm.cohortid IN ({$params['groups']})");
         
         $groups_members = implode(',', $group_list);
         if (!empty($groups_members))
         $sql .=" AND u.id IN ({$groups_members})";
         else
         $sql .=" AND u.id =0";
    }
    if($params['roleid'] == 3){
        if ($type == 'add') {
            $sql .= " AND u.id NOT IN (SELECT ue.userid
                             FROM {user_enrolments} AS ue 
                             JOIN {enrol} e ON (e.id = ue.enrolid and e.courseid = $course_id 
                             and (e.enrol='manual' OR e.enrol ='self')))";
        } else if ($type == 'remove') {
            $sql .= " AND u.id IN (SELECT ue.userid
                                 FROM {user_enrolments} AS ue 
                                 JOIN {enrol} e ON (e.id = ue.enrolid
                                 and e.courseid = $course_id and (e.enrol='manual' OR e.enrol = 'self')))";
        }
    }
    if($params['roleid'] == 5){
        if ($type == 'add') {
        $sql .= " AND u.id NOT IN (SELECT ue.userid
                             FROM {user_enrolments} AS ue 
                             JOIN {enrol} e ON (e.id = ue.enrolid and e.courseid = $course_id 
                             and (e.enrol='manual' OR e.enrol='self')))";

        } else if ($type == 'remove') {
            $sql .= " AND u.id IN (SELECT ue.userid
                             FROM {user_enrolments} AS ue 
                             JOIN {enrol} e ON (e.id = ue.enrolid 
                             and e.courseid = $course_id and (e.enrol='manual' OR e.enrol = 'self')))";
        }
    }



    // $order = " ORDER BY concat(u.firstname,' ',u.lastname) ASC ";
    if ($total==0) {
        $availableusers = $DB->get_records_sql_menu($sql.$order, $params, $offset, $perpage);
    } else {
        $availableusers = $DB->count_records_sql($sql, $params);
    }

 
    return $availableusers;
}

/**
    * function costcenterwise_opencourses_count
    * @todo count of courses under selected costcenter
    * @param int $costcenter costcenter
    * @param int $department department
    * @return  array courses count of each type
*/
function costcenterwise_opencourses_count($costcenter, $department = false, $subdepartment=false){
    global $USER, $DB, $CFG;
    $params = array();
    $params['costcenter'] = $costcenter;
    $countopencoursesql = "SELECT count(id) FROM {course} WHERE open_costcenterid = :costcenter AND open_identifiedas = 6";
    if ($department) {
        $countopencoursesql .= " AND open_departmentid = :department ";
        $params['department'] = $department;
    }
    if ($subdepartment) {
        $countopencoursesql .= " AND open_subdepartment = :subdepartment ";
        $params['subdepartment'] = $subdepartment;
    }
    // $activesql = " AND visible = 1 ";
    // $inactivesql = " AND visible = 0 ";

    $countopencourses = $DB->count_records_sql($countopencoursesql, $params);
    // $activeopencourses = $DB->count_records_sql($countopencoursesql.$activesql, $params);
    // $inactiveopencourses = $DB->count_records_sql($countopencoursesql.$inactivesql, $params);
    // if ($countopencourses >= 0) {
    //     if ($costcenter) {
    //         $viewcourselink_url = $CFG->wwwroot.'/local/courses/courses.php?costcenterid='.$costcenter; 
    //     }
    //     if ($department) {
    //         $viewcourselink_url = $CFG->wwwroot.'/local/courses/courses.php?departmentid='.$department; 
    //     } 
    //     if ($subdepartment) {
    //         $viewcourselink_url = $CFG->wwwroot.'/local/courses/courses.php?subdepartmentid='.$subdepartment; 
    //     }        
    // }

    // if ($activeopencourses >= 0) {
    //     if ($costcenter) {
    //         $count_courseactivelink_url = $CFG->wwwroot.'/local/courses/courses.php?status=active&costcenterid='.$costcenter; 
    //     }
    //     if ($department) {
    //         $count_courseactivelink_url = $CFG->wwwroot.'/local/courses/courses.php?status=active&departmentid='.$department; 
    //     }
    //     if ($subdepartment) {
    //         $count_courseactivelink_url = $CFG->wwwroot.'/local/courses/courses.php?status=active&subdepartmentid='.$subdepartment; 
    //     }
    // }
    // if ($inactiveopencourses >= 0) {
    //     if ($costcenter) {
    //         $count_courseinactivelink_url = $CFG->wwwroot.'/local/courses/courses.php?status=inactive&costcenterid='.$costcenter; 
    //     }
    //     if ($department) {
    //         $count_courseinactivelink_url = $CFG->wwwroot.'/local/courses/courses.php?status=inactive&departmentid='.$department; 
    //     }
    //     if ($subdepartment) {
    //         $count_courseinactivelink_url = $CFG->wwwroot.'/local/courses/courses.php?status=inactive&subdepartmentid='.$subdepartment; 
    //     }
    // }

    return array(
                'opencoursecount' => $countopencourses,
                // 'activecoursecount' => $activeopencourses,
                // 'inactivecoursecount' => $inactiveopencourses,
                // 'viewcourselink_url'=>$viewcourselink_url,
                // 'count_courseactivelink_url' =>$count_courseactivelink_url,
                // 'count_courseinactivelink_url' =>$count_courseinactivelink_url
            );
}
