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

namespace local_courses\form;
use local_users\functions\userlibfunctions as userlib;
use local_costcenter\functions\userlibfunctions as costcenterlib;
use core;
use moodleform;
use context_system;
use context_course;
use context_coursecat;
use core_component;
defined('MOODLE_INTERNAL') || die;
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->libdir.'/completionlib.php');

class custom_course_form extends moodleform {
    protected $course;
    protected $context;
    public $formstatus;
    public function __construct($action = null, $customdata = null, $method = 'post', $target = '', 
                                $attributes = null, $editable = true, $formdata = null) {
        $this->formstatus = array(
            'manage_course' => get_string('manage_course', 'local_courses'),

            // For future refrence (do not remove).
            
            // 'other_details' => get_string('courseother_details', 'local_courses')
        );
        parent::__construct($action, $customdata, $method, $target, $attributes, $editable, $formdata);
    }
    /**
     * Form definition.
     */
    function definition() {
        global $DB,$OUTPUT,$CFG, $PAGE, $USER;

        $mform = $this->_form;
        $course = $this->_customdata['course']; // This contains the data of this form.
        $course_id = $this->_customdata['courseid']; // This contains the data of this form.
        $category = $this->_customdata['category'];
        $formstatus = $this->_customdata['form_status'];
		$get_coursedetails = $this->_customdata['get_coursedetails'];
        $editoroptions = $this->_customdata['editoroptions'];
        $returnto = $this->_customdata['returnto'];
        $returnurl = $this->_customdata['returnurl'];
        $costcenterid = $this->_customdata['costcenterid'];
        $systemcontext   = context_system::instance();
        $mformajax    =& $this->_ajaxformdata;

        $formheaders = array_keys($this->formstatus);
        $formheader = $formheaders[$formstatus];

        if(empty($category)){
          $category = $CFG->defaultrequestcategory;
        }

        if (!empty($course->id)) {
          $coursecontext = context_course::instance($course->id);
          $context = $coursecontext;
          $categorycontext = context_coursecat::instance($category->id);
        } else {
          $coursecontext = null;
          $categorycontext = context_coursecat::instance($category);
          $context = $categorycontext;
        }

        $courseconfig = get_config('moodlecourse');

        $this->course  = $course;
        $this->context = $context;

        // Form definition with new course defaults.
        $mform->addElement('hidden', 'returnto', null);
        $mform->setType('returnto', PARAM_ALPHANUM);
        $mform->setConstant('returnto', $returnto);

        $mform->addElement('hidden', 'form_status', $formstatus);
        $mform->setType('form_status', PARAM_ALPHANUM);

        $mform->addElement('hidden', 'returnurl', null);
        $mform->setType('returnurl', PARAM_LOCALURL);
        $mform->setConstant('returnurl', $returnurl);

        $mform->addElement('hidden', 'getselectedclients');
        $mform->setType('getselectedclients', PARAM_BOOL);

        $defaultformat = $courseconfig->format;

        if(empty($course->id)){
          $courseid = 0;
        }else{
          $courseid = $id = $course->id;
        }

        // For Announcements activity.
        $mform->addElement('hidden', 'newsitems',$courseconfig->newsitems);

        $mform->addElement('hidden', 'id', $courseid);
        $mform->setType('id', PARAM_INT);
		// $systemcontext = context_system::instance();
        $core_component = new core_component();
        if($formstatus == 0){
            $getdepartmentelements = costcenterlib::org_hierarchy($mform, $course->id, $systemcontext, $mformajax, 'course', 'courseclass');
// Sandeep Category For future refrence (do not remove commented code). - Starts //
            // if((int)$this->_ajaxformdata['open_subdepartment']){

            //     $parentid = (int)$this->_ajaxformdata['open_subdepartment'];
            // }else if((int)$this->_ajaxformdata['open_departmentid']){
            //     $parentid = (int)$this->_ajaxformdata['open_departmentid'];
            // }else if((int)$this->_ajaxformdata['open_costcenterid']){
            //     $parentid = (int)$this->_ajaxformdata['open_costcenterid'];
            // }
            // if($parentid){
            //   $parentcategory = $DB->get_field('local_costcenter', 'category', array('id' => $parentid));
            //   $categorysql = "SELECT cc.id, cc.path FROM {course_categories} AS cc 
            //       WHERE (cc.path LIKE '%/{$parentcategory}/%' OR cc.id = {$parentcategory}) ";
            //   $displaylist = $DB->get_records_sql_menu($categorysql);

            // }
            // $selectcatlist = array(null => get_string('selectcat', 'local_courses'));
            // if( isset($displaylist) && !empty($displaylist) ){
            //   $findisplaylist = array();
            //   foreach ($displaylist as $key => $categorywise) {
            //     $explodepaths = explode('/', $categorywise);
            //     $countcat = count($explodepaths);
            //     if($countcat > 0){
            //         $catpathnames = array();
            //         for ($i=0; $i < $countcat; $i++) { 
            //             if($i != 0){
            //                 $catpathnames[$i] = $DB->get_field('course_categories', 'name', array('id' => $explodepaths[$i]));
            //             }
            //         }
            //         if(count($catpathnames) > 1){
            //             $findisplaylist[$key] = implode(' / ', $catpathnames);
            //         }else{
            //             $findisplaylist[$key] = $catpathnames[1];
            //         }
                    
            //     }
            //   }
            //   $categories = $selectcatlist+$findisplaylist;
            // }else {
            //   $categories = $selectcatlist;
            // }
            
            // $categoryoptions = array(
            //   'ajax' => 'local_costcenter/form-options-selector',
            //   'data-contextid' => $systemcontext->id,
            //   'data-action' => 'costcenter_category_selector',
            //   'data-options' => json_encode(array('id' => $parentcategory)),
            //   'class' => 'categoryselect',
            //   'data-parentclass' => 'organisationselect',
            //   'data-class' => 'categoryselect',
            //   'multiple' => false,
            // );
            // $mform->addElement('autocomplete', 'category', get_string('coursecategory','local_courses'), 
            //                     $categories, $categoryoptions);
            // $mform->addHelpButton('category', 'coursecategory');
            // $mform->addRule('category', get_string('pleaseselectcategory', 'local_courses'), 'required', null, 'client');
            // $mform->setType('category', PARAM_INT);
            // Sandeep Category For future refrence (do not remove commented code). - Ends //

            $mform->addElement('text','fullname', get_string('course_name', 'local_courses'), 'maxlength="254" size="50"');
            $mform->addHelpButton('fullname', 'course_name', 'local_courses');
            $mform->addRule('fullname', get_string('missingfullname','local_courses'), 'required', null, 'client');
            $mform->setType('fullname', PARAM_TEXT);
            if (!empty($course->id) && !has_capability('moodle/course:changefullname', $coursecontext)) {
                $mform->hardFreeze('fullname');
                $mform->setConstant('fullname', $course->fullname);
            }

            $mform->addElement('text', 'shortname', get_string('coursecode', 'local_courses'), 'maxlength="100" size="20"');
            $mform->addHelpButton('shortname', 'coursecode', 'local_courses');
            $mform->addRule('shortname', get_string('missingshortname', 'local_courses'), 'required', null, 'client');
            $mform->setType('shortname', PARAM_TEXT);
            if (!empty($course->id) && !has_capability('moodle/course:changeshortname', $coursecontext)) {
                $mform->hardFreeze('shortname');
                $mform->setConstant('shortname', $course->shortname);
            }

            $identify = array();
            $classroom_plugin_exist = $core_component::get_plugin_directory('local', 'classroom');
            $learningplan_plugin_exist = $core_component::get_plugin_directory('local', 'learningplan');
            $certification_plugin_exist = $core_component::get_plugin_directory('local', 'certification');
            
            if(!empty($classroom_plugin_exist)){
                $identify['2'] = get_string('classroom', 'local_courses');
            }
            $identify['3'] = get_string('elearning', 'local_courses');
            if(!empty($learningplan_plugin_exist)){
              $identify['4'] = get_string('learningplan', 'local_courses');
            }

            if(!empty($certification_plugin_exist)){
              $identify['6'] = get_string('certification', 'local_courses');
            }
            /* Naveen Yada hidden the form field */
            foreach ($identify as $k => $v){
            
            $mform->addElement('hidden', 'open_identifiedas', get_string('type', 'local_courses'));
            $mform->setType('open_identifiedas', PARAM_INT);
            $mform->setConstant('open_identifiedas', $k);

            }

            // For course format.
            $courseformats = get_sorted_course_formats(true);
            $formcourseformats = array();
            foreach ($courseformats as $courseformat) {
              $formcourseformats[$courseformat] = get_string('pluginname', "format_$courseformat");
            }

            if (isset($course->format)) {
              $course->format = course_get_format($course)->get_format(); // Replace with default if not found.
              if (!in_array($course->format, $courseformats)) {
                  // This format is disabled. Still display it in the dropdown.
                  $formcourseformats[$course->format] = get_string('withdisablednote', 'moodle',
                          get_string('pluginname', 'format_'.$course->format));
              }
            }

            $mform->addElement('select', 'format', get_string('format'), $formcourseformats);
            $mform->addHelpButton('format', 'format');
            $mform->setDefault('format', $defaultformat);

            $mform->addElement('editor', 'summary_editor', get_string('coursesummary','local_courses'), null, $editoroptions);
            $mform->addHelpButton('summary_editor', 'coursesummary');
            $mform->setType('summary_editor', PARAM_RAW);
            $summaryfields = 'summary_editor';

            if ($overviewfilesoptions = course_overviewfiles_options($course)) {
              $mform->addElement('filemanager', 'overviewfiles_filemanager', 
                                get_string('courseoverviewfiles', 'local_courses'), null, $overviewfilesoptions);
              $mform->addHelpButton('overviewfiles_filemanager', 'courseoverviewfiles');
              $summaryfields .= ',overviewfiles_filemanager';
            }

        } 

        // For future refrence (do not remove commented code).

        // else if ($formstatus == 1){

        //     $mform->addElement('date_time_selector', 'startdate', get_string('startdate', 'local_courses'),
        //      array());
        //     $mform->addHelpButton('startdate', 'startdate');
		
		// 	$mform->addElement('date_time_selector', 'enddate', get_string('enddate', 'local_courses'), array('optional' => false));
        //     $mform->addHelpButton('enddate', 'enddate');

        //     $users_plugin_exist = $core_component::get_plugin_directory('local','users');
        //     if ($users_plugin_exist) {
        //         require_once($CFG->dirroot . '/local/users/lib.php');
        //         $functionname ='globaltargetaudience_elementlist';
        //          if(function_exists($functionname)) {
        //             $modulecostcenter = $DB->get_field('course', 'open_costcenterid', array('id' => $courseid));

        //             $mform->modulecostcenter = $modulecostcenter;
        //             $functionname($mform, array('hrmsrole', 'location'));
        //         }
        //     }
        // }
        $mform->closeHeaderBefore('buttonar');
		$mform->disable_form_change_checker();
        // Finally set the current form data.
        if(empty($course)&&$course_id>0){
             $course = get_course($course_id);
        }
        if(!empty($course->open_certificateid)){
            $course->map_certificate = 1;
        }
        $this->set_data($course);
		 $mform->disable_form_change_checker();
    }
     /**
     * Validation.
     *
     * @param array $data
     * @param array $files
     * @return array the errors that were found
     */
    function validation($data, $files) {
        global $DB;

        $errors = parent::validation($data, $files);
		$form_data = data_submitted();
        $labelstring = get_config('local_costcenter');
		
        // Add field validation check for duplicate shortname.
        if ($course = $DB->get_record('course', array('shortname' => $data['shortname']), '*', IGNORE_MULTIPLE)) {
            if (empty($data['id']) || $course->id != $data['id']) {
                $errors['shortname'] = get_string('shortnametaken', '', $course->fullname);
            }
        }  
		if (!empty($data['shortname']) && strrpos($data['shortname'], ' ') == true) {
            $errors['shortname'] = get_string('spacesnotallowed', 'local_courses');
        }
        // For future refrence (do not remove commented code).

        // if (isset($data['startdate']) && $data['startdate']
        //         && isset($data['enddate']) && $data['enddate']) {
        //     if ($data['enddate'] < $data['startdate']) {
        //         $errors['enddate'] = get_string('nosameenddate', 'local_courses');
        //     }
        // }
    // Sandeep Category For future refrence (do not remove commented code). - Starts //
        // if (isset($data['category']) && $data['form_status'] == 0){
        //     if(empty($data['category'])){
        //         $errors['category'] = get_string('err_category', 'local_courses');
        //     }
        // }

    // Sandeep Category For future refrence (do not remove commented code). - Ends //
        if ($data['map_certificate'] == 1 && empty($data['open_certificateid'])){
            $errors['open_certificateid'] = get_string('err_certificate', 'local_courses');
        }
        
        if ($data['open_enablepoints'] == 1 && empty($data['open_points'])){
            $errors['pointsArr'] = get_string('err_points', 'local_courses');
        }
        if (isset($data['open_costcenterid']) && $data['form_status'] == 0){
            if($data['open_costcenterid'] == 0){
                $errors['open_costcenterid'] = get_string('errororganization', 'local_users', $labelstring->firstlevel);
            }
        }
        if (isset($data['open_identifiedas']) && $data['form_status'] == 0){
            if($data['open_identifiedas'] != 3 && empty('open_identifiedas')){
                $errors['open_identifiedas'] = get_string('missingtype', 'local_courses');
            }
        }
        // if ($DB->record_exists('course', array('fullname' => trim($data['fullname'])), '*', IGNORE_MULTIPLE)) {
        //     $courseExists = $DB->get_record('course', array('fullname' => trim($data['fullname'])), '*', IGNORE_MULTIPLE);
        //     if (empty($data['id']) || $courseExists->id != $data['id']) {
        //         $errors['fullname'] = get_string('courseexists', 'local_courses', $courseExists->fullname);
        //     }
        // }
        $errors = array_merge($errors, enrol_course_edit_validation($data, $this->context));

        return $errors;
    }
}
