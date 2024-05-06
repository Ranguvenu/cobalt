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
 * @subpackage local_program
 */

namespace local_program\form;
use local_costcenter\functions\userlibfunctions as costcenterlib;
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/completionlib.php');
use context_system;
use local_program\local\querylib;
use moodleform;
use core_component;

class program_form extends moodleform {
    public $formstatus;

    public function __construct($action = null, $customdata = null, $method = 'post', $target = '', $attributes = null, $editable = true, $formdata = null) {

        $this->formstatus = array(
            'generaldetails' => get_string('generaldetails', 'local_program'),
            );
        parent::__construct($action, $customdata, $method, $target, $attributes, $editable, $formdata);
    }

    public function definition() {
        global $CFG, $USER, $PAGE, $DB;
        $querieslib = new querylib();
        $mform = &$this->_form;
        $renderer = $PAGE->get_renderer('local_program');
        $context = context_system::instance();
        $form_status = $this->_customdata['form_status'];
        $mformajax    =& $this->_ajaxformdata;
        $id = $this->_customdata['id'] > 0 ? $this->_customdata['id'] : 0;
        $formheaders = array_keys($this->formstatus);
        $formheader = $formheaders[$formstatus];
        $labelstring = get_config('local_costcenter');
        $programrec = $DB->get_record('local_program', array('id' => $id));
        // print_r($programrec);exit;
        $dept_or_col = $DB->get_field('local_costcenter','dept_or_col',['id' => $programrec->department]);

        $mform->addElement('hidden', 'id', $id);
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'form_status', $form_status);
        $mform->setType('form_status', PARAM_INT);
        $class = 'programclass';
        $core_component = new core_component();

        if($form_status == 0){
            if($id <=0){
                $getdepartmentelements = costcenterlib::org_hierarchy($mform, $id, $context, $mformajax, 'program', 'programclass');
            }
            if ($id > 0) {
                $costcentersql = "SELECT lc.fullname FROM {local_costcenter} lc 
                                    JOIN {local_program} lp ON lp.costcenter = lc.id
                                    WHERE lp.id = :programid";
                $costcenter = $DB->get_field_sql($costcentersql, array('programid' => $id));
                $mform->addElement('static', 'costcentername', get_string('organization', 'local_costcenter', $labelstring->firstlevel), $costcenter);
                $mform->addElement('hidden', 'open_costcenterid', $programrec->costcenter);
                $mform->setDefault('open_costcenterid', $programrec->costcenter);
            } 

            if ($id > 0) {
                // if($dept_or_col == 0){
                    $deptname = $DB->get_field('local_costcenter', 'fullname', array('id' => $programrec->department, 'depth' => 2));
                    if ($deptname) {
                        $mform->addElement('static', 'departmentname', get_string('department', 'local_program', $labelstring->secondlevel), $deptname);
                    } else {
                        $mform->addElement('static', 'departmentname', get_string('department', 'local_program', $labelstring->secondlevel), get_string('allclasses', 'local_program'));
                    }
                    $mform->addElement('hidden', 'open_departmentid', $programrec->department);
                    $mform->setDefault('open_departmentid', $programrec->department);
                // } else{
                //     $deptname = $DB->get_field('local_costcenter', 'fullname', array('id' => $programrec->department, 'depth' => 2));
                //     if ($deptname) {
                //         $mform->addElement('static', 'collegename', get_string('collegelabel', 'local_costcenter'), $deptname);
                //     } else {
                //         $mform->addElement('static', 'collegename', get_string('collegelabel', 'local_costcenter'), get_string('allclasses', 'local_program'));
                //     }
                //     $mform->addElement('hidden', 'open_departmentid', $programrec->department);
                //     $mform->setDefault('open_departmentid', $programrec->department);
                // }

            // }

            // if ($id > 0) {
                $subdeptname = $DB->get_field('local_costcenter', 'fullname', array('id' => $programrec->subdepartment, 'depth' => 3));
                if ($subdeptname) {
                    $mform->addElement('static', 'subdepartmentname', get_string('subdepartment', 'local_program', $labelstring->thirdlevel), $subdeptname);
                } else {
                    $mform->addElement('static', 'subdepartmentname', get_string('subdepartment', 'local_program', $labelstring->thirdlevel), get_string('allclasses', 'local_program'));
                }
                $mform->addElement('hidden', 'open_subdepartment', $programrec->subdepartment);
                $mform->setDefault('open_subdepartment', $programrec->subdepartment);
            } 

        if (is_siteadmin($USER->id)
            || has_capability('local/costcenter:manage_multiorganizations', $context)
            || has_capability('local/costcenter:manage_ownorganization', $context)
            || has_capability('local/costcenter:manage_owndepartments', $context)) {
            $batchselect = [0 => get_string('select_batch', 'local_program')];
            if ($id || $this->_ajaxformdata['batchid']) {
                $openbatch = (int) $this->_ajaxformdata['batchid']
                                    ? (int)$this->_ajaxformdata['batchid']
                                    : $programrec->batchid;
                $batches = $batchselect + $DB->get_records_menu('cohort', array('id' => $openbatch), '',
                                        $fields = 'id, name');
            } else {
                $openbatch = 0;
                $batches = $batchselect;
            }
            
            $batchoptions = array(
                'ajax' => 'local_costcenter/form-options-selector',
                'data-contextid' => $context->id,
                'data-action' => 'costcenter_batch_selector',
                'data-options' => json_encode(array('id' => $openbatch)),
                'class' => 'batchselect',
                'data-parentclass' => 'organisationselect',
                'data-class' => 'batchselect',
                'data-pluginclass' => $class,
                'multiple' => false,
            );
            if ($id > 0) {
                $batchname = $DB->get_field('cohort', 'name', array('id' => $programrec->batchid));
                $mform->addElement('static', 'batchname', get_string('batch', 'local_program'), $batchname);
                $mform->addElement('hidden', 'batchid', $batches);
                $mform->setDefault('batchid', $batches);
            } else {
                $mform->addElement('autocomplete', 'batchid', get_string('batch', 'local_program'), $batches, $batchoptions);
                // $mform->addRule('batchid', null, 'required', null, 'client');
                $mform->addRule('batchid', get_string('pleaseselectbatch', 'local_program'), 'required', null, 'client');
                $mform->setType('batchid', PARAM_INT);
                $mform->addElement('html', '<div id="id_create_batch" class="mb-3"><b>'.get_string('note', 'local_program').'</b> '.get_string('create_batch', 'local_program').' </div>');
            }
        }

        if (is_siteadmin($USER->id)
            || has_capability('local/costcenter:manage_multiorganizations', $context)
            || has_capability('local/costcenter:manage_ownorganization', $context)
            || has_capability('local/costcenter:manage_owndepartments', $context)) {
            $curriculumselect = [0 => get_string('select_curriculum_list', 'local_program')];
            if ($id || $this->_ajaxformdata['curriculumid']) {
                $opencurriculum = (int) $this->_ajaxformdata['curriculumid']
                                    ? (int)$this->_ajaxformdata['curriculumid']
                                    : $programrec->curriculumid;
                $curriculums = $curriculumselect + $DB->get_records_menu('local_curriculum', array('id' => $opencurriculum), '', $fields = 'id, name');
            } else {
                $opencurriculum = 0;
                $curriculums = $curriculumselect;
            }
            
            $curriculumoptions = array(
                'ajax' => 'local_costcenter/form-options-selector',
                'data-contextid' => $context->id,
                'data-action' => 'costcenter_curriculum_selector',
                'data-options' => json_encode(array('id' => $opencurriculum)),
                'class' => 'curriculumselect',
                'data-parentclass' => 'organisationselect',
                'data-class' => 'curriculumselect',
                'multiple' => false,
                'data-pluginclass' => $class,
            );
            if ($id > 0) {
                $curriculumname = $DB->get_field('local_curriculum', 'name', array('id' => $programrec->curriculumid));
                $mform->addElement('static', 'curriculumname', get_string('curriculumid', 'local_program'), $curriculumname);
                $mform->addElement('hidden', 'curriculumid', $curriculums);
                $mform->setDefault('curriculumid', $curriculums);
            } else {
                $mform->addElement('autocomplete', 'curriculumid', get_string('curriculumid', 'local_program'), $curriculums, $curriculumoptions);
                $mform->addRule('curriculumid', get_string('missingcurriculumid','local_program'), 'required', null);
                $mform->setType('curriculumid', PARAM_INT);
            }

        }
            $mform->addElement('text', 'name', get_string('pg_name', 'local_program'), array());

            if (!empty($CFG->formatstringstriptags)) {
                $mform->setType('name', PARAM_TEXT);
            } else {
                $mform->setType('name', PARAM_CLEANHTML);
            }

            $mform->addRule('name', null, 'required', null, 'client');


            $mform->addElement('advcheckbox', 'hasadmissions', '', get_string('hasadmissions', 'local_program'), array('group' => 1), array(0, 1));
            $mform->addHelpButton('hasadmissions', 'hasadmissions', 'local_program');
            
            $mform->addElement('textarea', 'prerequisite', get_string('prerequisite', 'local_program'), array('placeholder'=>'Program prerequisite'));
            $mform->setType('prerequisite', PARAM_RAW);
            $mform->hideIf('prerequisite', 'hasadmissions', 'neq', 1);

            $mform->addElement('date_selector', 'startdate', get_string('astartdate', 'local_program'));
            $mform->setType('startdate', PARAM_RAW);
            $mform->hideIf('startdate', 'hasadmissions', 'neq', 1);

            $mform->addElement('date_selector', 'enddate', get_string('aenddate', 'local_program'));
            $mform->setType('enddate', PARAM_RAW);
            $mform->hideIf('enddate', 'hasadmissions', 'neq', 1);

            if($id > 0){
                $activesemsql = "SELECT lpl.id, lpl.level
                                  FROM {local_program_levels} lpl
                                 WHERE lpl.programid = $id AND lpl.active = 1";
                $activesem = $DB->record_exists_sql($activesemsql);
                $programStartdate = $DB->get_field('local_program', 'program_startdate', array('id' => $id));
                if ($activesem) {
                    $mform->disabledIf('program_startdate_disabled', 'id', 'neq', 0);
                    $mform->addElement('date_selector', 'program_startdate_disabled', get_string('program_startdate', 'local_program'));
                    $mform->setType('program_startdate_disabled', PARAM_RAW);
                    $mform->setConstant('program_startdate_disabled', $programStartdate);
                    $mform->addElement('hidden', 'program_startdate', $programStartdate);

                    $mform->addElement('html', '<div id="id_cannot_edit"><b>'.get_string('note', 'local_program').'</b> '.get_string('can_not_edit', 'local_program').' </div>'); 
                } else {
                    $mform->addElement('date_selector', 'program_startdate', get_string('program_startdate', 'local_program'));
                    $mform->setType('program_startdate', PARAM_RAW);
                }
            } else {
                $mform->addElement('date_selector', 'program_startdate', get_string('program_startdate', 'local_program'));
                $mform->setType('program_startdate', PARAM_RAW);
            }

            if($id > 0){
                $programdata = $DB->get_record('local_program', array('id' => $id));
                if ($programdata->duration_format == 'M') {
                    $format = 'Months';
                } else {
                    $format = 'Years';
                }
                $activesemsql = "SELECT lpl.id, lpl.level
                                  FROM {local_program_levels} lpl
                                 WHERE lpl.programid = $id AND lpl.active = 1";
                $activesem = $DB->record_exists_sql($activesemsql);
                $semstdatesql = "SELECT MIN(startdate) as startdate
                                   FROM {local_program_levels}
                                  WHERE programid = {$id} AND startdate <> 0";
                $semstdate = $DB->get_field_sql($semstdatesql);

                if (isset($activesem)) {
                    if (isset($semstdate)) {
                        $duration = array();
                        $duration[] = & $mform->createElement('text', 'duration_info');
                        $durationformat = array('Y' => 'Years', 'M' => 'Months');
                        $duration[] = & $mform->createElement('select', 'duration_format_info', get_string('forumtype', 'forum'), $durationformat);
                        $mform->disabledIf('durationfield_id', 'id', 'neq', 0);
                        $myduration = $mform->addElement('group', 'durationfield_id', get_string('duration', 'local_program'), $duration, '  ', false);
                        $mform->setDefault('durationfield_id', $programdata->duration, $format);

                        $mform->addElement('hidden', 'duration', $programdata->duration);
                        $mform->addElement('hidden', 'duration_format', $format);

                        $mform->addElement('html', '<div id="id_cannot_edit"><b>'.get_string('note', 'local_program').'</b> '.get_string('can_not_edit_duration', 'local_program').' </div>'); 
                    } else {
                        $duration = array();
                        $duration[] = & $mform->createElement('text', 'duration');
                        $durationformat = array('Y' => 'Years', 'M' => 'Months');
                        $duration[] = & $mform->createElement('select', 'duration_format', get_string('forumtype', 'forum'), $durationformat);
                        $myduration = $mform->addElement('group', 'durationfield', get_string('duration', 'local_program'), $duration, '  ', false);
                        $mform->addRule('durationfield', null, 'required', null, 'client');
                        $mform->setDefault('durationfield', $programdata->duration, $format);
                    }
                } else {
                    $duration = array();
                    $duration[] = & $mform->createElement('text', 'duration');
                    $durationformat = array('Y' => 'Years', 'M' => 'Months');
                    $duration[] = & $mform->createElement('select', 'duration_format', get_string('forumtype', 'forum'), $durationformat);
                    $myduration = $mform->addElement('group', 'durationfield', get_string('duration', 'local_program'), $duration, '  ', false);
                    $mform->addRule('durationfield', null, 'required', null, 'client');
                    $mform->setDefault('durationfield', $programdata->duration, $format);
                }
            } else {
                $duration = array();
                $duration[] = & $mform->createElement('text', 'duration');
                $durationformat = array('Y' => 'Years', 'M' => 'Months');
                $duration[] = & $mform->createElement('select', 'duration_format', get_string('forumtype', 'forum'), $durationformat);
                $myduration = $mform->addElement('group', 'durationfield', get_string('duration', 'local_program'), $duration, '  ', false);
                $mform->addRule('durationfield', null, 'required', null, 'client');
                $mform->setType('duration', PARAM_RAW);
            }

            $mform->addElement('filemanager', 'programlogo',
                    get_string('programlogo', 'local_program'), null,
                    array('maxfiles' => 1, 'maxbytes' => 2048000, 'accepted_types' => '.jpg,png,jpeg'));
            $mform->addHelpButton('programlogo','image','local_program');
            $editoroptions = array(
                'noclean' => false,
                'autosave' => false,
                'maxfiles' => EDITOR_UNLIMITED_FILES,
                'maxbytes' => $CFG->maxbytes,
                'trusttext' => false,
                'forcehttps' => false,
                'context' => $context
            );
            $mform->addElement('editor', 'cr_description_editor',
                    get_string('description', 'local_program'), null, $editoroptions);
            $mform->setType('cr_description_editor', PARAM_RAW);
            $mform->addHelpButton('cr_description_editor', 'description', 'local_program');

            //certificate
            $certificate_plugin_exist = $core_component::get_plugin_directory('tool', 'certificate');
            if($certificate_plugin_exist){
                if($id>0){
                    $programCompleted = $DB->get_field('local_programcompletions', 'id', ['programid' => $id]);
                    if(!empty($programCompleted)){
                        $select = array(null => get_string('select_certificate','local_program'));
                        $certiid = $DB->get_field('local_program', 'certificateid', ['id' => $id]);
                        $cert_templates = $DB->get_field('tool_certificate_templates','name', ['id' => $certiid]);
                        $mform->addElement('static',  'certificateids', get_string('certificate_template','local_program'), $cert_templates);

                        $mform->addElement('hidden', 'map_certificate', 1);
                        $mform->setType('id', PARAM_INT);

                        $mform->addElement('hidden', 'certificateid', $certiid);
                        $mform->setType('id', PARAM_INT);

                        $mform->addHelpButton('certificateids', 'certificate_template', 'local_program');
                        $mform->setType('certificateids', PARAM_INT);
                    } else{
                        $checkboxes = array();
                        $checkboxes[] = $mform->createElement('advcheckbox', 'map_certificate', null, '', array(),array(0,1));
                        $mform->addGroup($checkboxes, 'map_certificate', get_string('add_certificate', 'local_program'), array(' '), false);
                        $mform->addHelpButton('map_certificate', 'add_certificate', 'local_program');

                        $select = array(null => get_string('select_certificate','local_program'));

                        if(is_siteadmin() || has_capability('local/costcenter:manage_multiorganizations', $context)){
                            $cert_templates = $DB->get_records_menu('tool_certificate_templates',array(),'name', 'id,name');
                        }else{
                            $cert_templates = $DB->get_records_menu('tool_certificate_templates',array('costcenter'=>$USER->open_costcenterid),'name', 'id,name');
                        }
                        $certificateslist = $select + $cert_templates;

                        $mform->addElement('select',  'certificateid', get_string('certificate_template','local_program'), $certificateslist);
                        $mform->addHelpButton('certificateid', 'certificate_template', 'local_program');
                        $mform->setType('certificateid', PARAM_INT);
                        $mform->hideIf('certificateid', 'map_certificate', 'neq', 1);
                    }
                } else{
                    $checkboxes = array();
                        $checkboxes[] = $mform->createElement('advcheckbox', 'map_certificate', null, '', array(),array(0,1));
                        $mform->addGroup($checkboxes, 'map_certificate', get_string('add_certificate', 'local_program'), array(' '), false);
                        $mform->addHelpButton('map_certificate', 'add_certificate', 'local_program');


                        $select = array(null => get_string('select_certificate','local_program'));

                        if(is_siteadmin() || has_capability('local/costcenter:manage_multiorganizations', $context)){
                            $cert_templates = $DB->get_records_menu('tool_certificate_templates',array(),'name', 'id,name');
                        }else{
                            $cert_templates = $DB->get_records_menu('tool_certificate_templates',array('costcenter'=>$USER->open_costcenterid),'name', 'id,name');
                        }
                        $certificateslist = $select + $cert_templates;

                        $mform->addElement('select',  'certificateid', get_string('certificate_template','local_program'), $certificateslist);
                        $mform->addHelpButton('certificateid', 'certificate_template', 'local_program');
                        $mform->setType('certificateid', PARAM_INT);
                        $mform->hideIf('certificateid', 'map_certificate', 'neq', 1);
                }
            }

        }

        $mform->disable_form_change_checker();
    }
    public function validation($data, $files) {
        global $CFG, $DB, $USER;
        $batchid = $data['batchid'];
        $labelstring = get_config('local_costcenter');
        $id = $this->_customdata['id'] > 0 ? $this->_customdata['id'] : 0;
        $errors = parent::validation($data, $files);
        $form_status = $data['form_status'];
        if($form_status == 0){
            if (isset($data['name']) && empty(trim($data['name']))) {
                $errors['name'] = get_string('valnamerequired', 'local_program');
            }

            if ($data['map_certificate'] == 1 && empty($data['certificateid'])){
                $errors['certificateid'] = get_string('err_certificate', 'local_courses');
            }

            if (isset($data['open_costcenterid']) && $data['form_status'] == 0){
                if($data['open_costcenterid'] == 0){
                    $errors['open_costcenterid'] = get_string('errororganization', 'local_users', $labelstring->firstlevel);
                }
            }
            if (isset($data['batchid']) && $data['form_status'] == 0){
                if($data['batchid'] == 0){
                    $errors['batchid'] = get_string('pleaseselectbatch', 'local_program');
                }
            }
            if (isset($data['curriculumid']) && $data['form_status'] == 0){
                if($data['curriculumid'] == 0){
                    $errors['curriculumid'] = get_string('missingcurriculumid', 'local_program');
                }
            }

            //  batchid validation
            if($data['id'] <= 0){
                if(!empty($batchid) && $data['form_status'] == 0){
                    $batchidexists = $DB->record_exists('local_program',array('batchid' => $batchid));
                    if($batchidexists){
                        $errors['batchid'] = get_string('batchidexists', 'local_program');
                    }
                }
            }

            // Admission end-date validation.
            if ($id > 0) {
                if ($data['enddate'] != 0 && $data['enddate'] <= $data['startdate']) {
                    if ($data['enddate'] <= $data['program_startdate']) {
                        if ($data['hasadmissions'] != 0) {
                            $errors['enddate'] = get_string('admenddateerror', 'local_program');
                        }
                    }
                }
            } else {
                if ($data['enddate'] != 0 && $data['enddate'] <= $data['startdate']) {
                    if ($data['enddate'] <= $data['program_startdate']) {
                        if ($data['hasadmissions'] != 0) {
                            $errors['enddate'] = get_string('admenddateerror', 'local_program');
                        }
                    }
                }
            }
            if($data['hasadmissions'] == 1){
                if(empty($data['prerequisite'])){
                    $errors['prerequisite'] = get_string('prerequisitecannotempty', 'local_program');
                }
            }

            // Program start-date validation.
            if ($id > 0) {
                if ($data['program_startdate'] != 0 && $data['program_startdate'] <= $data['enddate']) {
                    if ($data['hasadmissions'] != 0) {
                        $errors['program_startdate'] = get_string('program_date_error', 'local_program');
                    }
                }
            } else {
                 if ($data['program_startdate'] != 0 && $data['program_startdate'] <= $data['enddate']) {
                    if ($data['hasadmissions'] != 0) {
                        $errors['program_startdate'] = get_string('program_date_error', 'local_program');
                    }
                }
            }

            // Duration validation.
            if ($id > 0) {
                if (empty($data['duration'])) {
                    $errors['durationfield'] = get_string('blankfielderror', 'local_program');
                }

                if(preg_match("/([%\$#\*\,\.]+)/", $data['duration'])) {
                    $errors['durationfield'] = get_string('decimalvalueerror', 'local_program');
                }
                
                if(preg_match("/^[a-zA-Z\s]+$/", $data['duration'])) {
                    $errors['durationfield'] = get_string('noletterserror', 'local_program');
                }
            } else {
                if (empty($data['duration'])) {
                    $errors['durationfield'] = get_string('blankfielderror', 'local_program');
                }

                if(preg_match("/([%\$#\*\,\.]+)/", $data['duration'])) {
                    $errors['durationfield'] = get_string('decimalvalueerror', 'local_program');
                }
                
                if(preg_match("/^[a-zA-Z\s]+$/", $data['duration'])) {
                    $errors['durationfield'] = get_string('noletterserror', 'local_program');
                }
            }
            //  if(empty($data['open_departmentid'])){
            //     $errors['open_departmentid'] = get_string('subdeptnameshouldselect', 'local_users', $labelstring);
            // }
        }
        return $errors;
    }

    public function set_data($components) {
        global $DB;
        $context = context_system::instance();
        $data = $DB->get_record('local_program', array('id' => $components->id));
        //populate tags
        $data->cr_description_editor = array();
        $data->cr_description_editor['text'] = $data->description;
        $draftitemid = file_get_submitted_draft_itemid('programlogo');
        file_prepare_draft_area($draftitemid, $context->id, 'local_program', 'programlogo',
            $data->programlogo, null);
        $data->programlogo = $draftitemid;
        $data->program_startdate_disabled = $data->program_startdate;
        $data->duration_info = $data->duration;
        $data->duration_format_info = $data->duration_format;
        $data->open_group =(!empty($data->open_group)) ? array_diff(explode(',',$data->open_group), array('')) :array(NULL=>NULL);
        $data->open_hrmsrole =(!empty($data->open_hrmsrole)) ? array_diff(explode(',',$data->open_hrmsrole), array('')) :array(NULL=>NULL);
        $data->open_designation =(!empty($data->open_designation)) ? array_diff(explode(',',$data->open_designation), array('')) :array(NULL=>NULL);
        $data->open_location =(!empty($data->open_location)) ? array_diff(explode(',',$data->open_location), array('')) :array(NULL=>NULL);
        if(!empty($data->certificateid)){
            $data->map_certificate = 1;
        }
        parent::set_data($data);
    }
}
