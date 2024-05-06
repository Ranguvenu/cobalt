<?php

defined('MOODLE_INTERNAL') || die;
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/completionlib.php');
require_once($CFG->dirroot . '/local/lib.php');

$hierarchy = new hierarchy();

class local_timetablelayout_hierarchyform extends moodleform {

    function definition() {
        global $USER, $CFG, $DB, $PAGE, $OUTPUT;
        global $hierarchy;
        $mform = $this->_form;
        $id = $this->_customdata['id'];
        $batchid = $this->_customdata['batchid'];
        $scid = null;
        if (isset($this->_customdata['scid'])) {
            $scid = $this->_customdata['scid'];
        }
        $PAGE->requires->yui_module('moodle-local_timetable-timetablelayout', 'M.local_timetable.init_timetablelayout', array(array('formid' => $mform->getAttribute('id'))));
        if ($batchid > 0) {            
            $batchmapinfo=$DB->get_record('local_groups',array('cohortid'=>$batchid));         
        }
        $hierarchy = new hierarchy();
        $school = $hierarchy->get_assignedschools();
        if (is_siteadmin()) {
            $school = $hierarchy->get_school_items();
        }
        $parents = $hierarchy->get_school_parent($school);
        $count = count($school);

        if ($count == 1) {
            /* ---registrar is assigned to only one school, display as static--- */
            foreach ($school as $scl) {
                $key = $scl->id;
                $value = $scl->fullname;
            }
            $mform->addElement('static', 'schools', get_string('organisations', 'local_timetable'), $value);
            $mform->addElement('hidden', 'schoolid', $key);
        
        } else {
            $mform->addElement('select', 'schoolid', get_string('organisations', 'local_timetable'), $parents);
            $mform->addRule('schoolid', get_string('missingorganisation', 'local_timetable'), 'required', null, 'client');
        }
          $mform->setType('schoolid', PARAM_INT);
          if(isset($batchmapinfo->schoolid) && ($batchmapinfo->schoolid > 0))
          $mform->setDefault('schoolid',$batchmapinfo->schoolid);  

        $mform->addElement('hidden', 'addprogram');
        $mform->setType('addprogram', PARAM_RAW);
        
        $mform->addElement('hidden', 'addsemesterhere');
        $mform->setType('addsemesterhere', PARAM_RAW);
        
        $mform->registerNoSubmitButton('updatecourseformat');
        $mform->addElement('submit', 'updatecourseformat', get_string('updatedata', 'local_timetable'));
        
        include('settimeintervals_form.php');

        $mform->addElement('checkbox', 'visible', get_string('publish', 'local_timetable'), '', array('checked' => 'checked', 'name' => 'my-checkbox', 'data-size' => 'small', 'data-on-color' => 'info', 'data-off-color' => 'warning', 'data-on-text' => 'Yes', 'data-switch-set' => 'size', 'data-off-text' => 'No'));
        $mform->addHelpButton('visible', 'publish', 'local_timetable');
        $mform->setDefault('visible', true);

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $submitlable = ($id > 0) ? get_string('save') : get_string('save');

        $this->add_action_buttons($cancel = true, $submitlable);
    }

    function definition_after_data() {
        global $DB;
        global $hierarchy, $mybatch, $batches, $cplan;
        $mform = $this->_form;
        $batchid = $this->_customdata['batchid'];
        // ----getting timelayout id , it wil be used in  editing time layout---
        $id = $this->_customdata['id'];
        if($id>0){
            $timelayoutinfo=$DB->get_record('local_batch_timetablelayout',array('id'=>$id));            
        }
    
        if ($batchid > 0) {            
            $batchmapinfo=$DB->get_record('local_groups',array('cohortid'=>$batchid));              
        }
        $schoolvalue = $mform->getElementValue('schoolid');
        if(is_array($schoolvalue)) {
            $schoolid =$schoolvalue[0];
        } else {
            $schoolid = $schoolvalue;
        }
         
        $program = array();
        if (isset($schoolvalue) && $schoolid > 0) {
            $program = $hierarchy->get_records_cobaltselect_menu('local_program', "costcenter=$schoolid AND visible=1", null, '', 'id,name', '--Select--');
            $myselect = $mform->createElement('select', 'programid', get_string('program', 'local_timetable'), $program);
            $mform->insertElementBefore($myselect, 'addprogram');
            $mform->addRule('programid', get_string('missingprogram', 'local_timetable'), 'required', null, 'client');
             if(isset($batchmapinfo->programid) && ($batchmapinfo->programid>0))
             $mform->setDefault('programid',$batchmapinfo->programid);           
            $programvalue = $mform->getElementValue('programid');
        }

        //---------------offering period drop down-----------------------
        if ($schoolid > 0 && $programvalue[0] > 0) {
            $semesters = $hierarchy->get_active_semesters($programvalue[0]);
            $sem = $mform->createElement('select', 'semesterid', get_string('selectsemester', 'local_timetable'), $semesters);
            $mform->insertElementBefore($sem, 'addsemesterhere');
            $mform->addRule('semesterid', get_string('missingsemester', 'local_timetable'), 'required', null, 'client');
            $mform->setType('semesterid', PARAM_INT);
            $semestervalue = $mform->getElementValue('semesterid');
        }// end of definition after data function
    }
    
    public function validation($data, $files) {
        global $DB, $CFG;        
        $errors = parent::validation($data, $files);
        $data =(object)$data;
        for($i = 0; $i < count($data->starthours); $i++){
            if(strtolower($data->start_td[$i]) == 'pm'){
                if ($data->starthours[$i] != 12) {
                    $data->starthours[$i] = $data->starthours[$i]+12; 
                }
            }
            if(strtolower($data->end_td[$i]) == 'pm'){
                if ($data->endhours[$i] != 12) {
                    $data->endhours[$i] = $data->endhours[$i]+12;
                }
            }

            $starttime = mktime($data->starthours[$i], $data->startminutes[$i]);
            $endtime = mktime($data->endhours[$i], $data->endminutes[$i]);
                    
            if ($data->starthours[$i] == 11 && $data->startminutes[$i] <= 55 && $data->start_td[$i] == 'am') {
                if($data->endhours[$i] == 12 && $data->endminutes[$i] <= 55 && $data->end_td[$i] == 'am'){
                    $errors['section_array['.$i.']'] = get_string('st_et_error', 'local_timetable');
                } else if($starttime >= $endtime){
                    $errors['section_array['.$i.']'] = get_string('st_et_error', 'local_timetable');
                }
            } else if ($data->starthours[$i] <= $data->endhours[$i-1]){
                if ($i > 0 && $data->startminutes[$i] < $data->endminutes[$i-1]) {
                    $errors['section_array['.$i.']'] = get_string('pre_et_error', 'local_timetable');
                } else if($i > 0 && $data->start_td[$i] != $data->end_td[$i-1]){
                    $errors['section_array['.$i.']'] = get_string('pre_et_error', 'local_timetable');
                } else if($starttime >= $endtime){
                    $errors['section_array['.$i.']'] = get_string('st_et_error', 'local_timetable');
                }
            } else if($starttime >= $endtime){
                $errors['section_array['.$i.']'] = get_string('st_et_error', 'local_timetable');
            }
        }

        $existssql = "SELECT *
                        FROM {local_timeintervals} 
                        WHERE programid = $data->programid
                        AND semesterid = $data->semesterid
                        AND id != $data->id";
        $exists = $DB->get_record_sql($existssql);
        if ($exists) {
            $errors['semesterid'] = get_string('semalreadyexists', 'local_timetable');
        }
        return $errors;
    }
}
