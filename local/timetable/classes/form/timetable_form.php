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
 * @package    local_groups
 * @copyright  2022 eAbyas Info Solution Pvt Ltd.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
use moodleform;
use context_instance;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/lib/formslib.php');
require_once($CFG->dirroot . '/local/costcenter/lib.php');
use local_costcenter\functions\userlibfunctions as costcenterlib;
class timetable_form extends moodleform {

    /**
     * Define the group edit form
     */
    public function definition() {
        global $DB, $USER;
        $context = \context_system::instance();
        $mform = $this->_form;
        $id = $this->_customdata['id'];
        if ($id > 0) {
            $semid = $this->_customdata['semesterid'];
            if (empty($semid)) {
                $semesterid = $DB->get_field('local_timeintervals', 'semesterid', ['id' => $id]);
            } else {
                $semesterid = $semid;
            }
            $programid = $DB->get_field('local_program_levels', 'programid', ['id' => $semesterid]);
        } else {
            $programid = $_REQUEST['program'];
            $semesterid = $_REQUEST['semester'];
        }

        $class = 'timetableclass';

        $getdepartmentelements = costcenterlib::org_hierarchy($mform, $id, $context, $mformajax, 'timeintervals', $class);

        $programselect = [null => get_string('selectprogram', 'local_timetable')];
        if ($id || $this->_ajaxformdata['program'] || $programid) {
            $openprogram = (int) $this->_ajaxformdata['program']
                                ? (int)$this->_ajaxformdata['program']
                                : $programid;
            $programs = $programselect + $DB->get_records_menu('local_program', array('id' => $openprogram), '', $fields = 'id, name');
        } else {
            $openprogram = 0;
            $programs = $programselect;
        }
        $programoptions = array(
            'ajax' => 'local_costcenter/form-options-selector',
            'data-selectstring' => get_string('select_program', 'local_timetable'),
            'data-contextid' => $context->id,
            'data-action' => 'costcenter_program_selector',
            'data-options' => json_encode(array('id' => $openprogram)),
            'class' => 'programnameselect',
            'data-parentclass' => 'organisationselect',
            'data-class' => 'programselect',
            'multiple' => false,
            'data-pluginclass' => $class,
            'onchange' => '(function(e){ require("local_costcenter/newcostcenter").changeElement(event) })(event)',
        );

        $mform->addElement('autocomplete', 'program', get_string('program', 'local_timetable'), $programs, $programoptions);
        $mform->setType('program', PARAM_INT);
        $mform->addRule('program', get_string('missingprogram', 'local_timetable'), 'required', null, 'client');

        if ($_REQUEST['semester'] > 0) {
            $semesterid = $_REQUEST['semester'];
        } else {
            $semesterid = $semesterid;
        }

        $levelselect = [null => get_string('selectsemester', 'local_timetable')];
        if ($id || $this->_ajaxformdata['semester'] || $semesterid) {
            $openlevel = (int) $this->_ajaxformdata['semester']
                                ? (int)$this->_ajaxformdata['semester']
                                : $semesterid;
            $programlevels = $levelselect + $DB->get_records_menu('local_program_levels', array('id' => $openlevel), '', $fields = 'id, level');
        } else {
            $openlevel = 0;
            $programlevels = $levelselect;
        }
        $programleveloptions = array(
            'ajax' => 'local_costcenter/form-options-selector',
            'data-selectstring' => get_string('select_level', 'local_timetable'),
            'data-contextid' => $context->id,
            'data-action' => 'program_level_selector',
            'data-options' => json_encode(array('id' => $openlevel)),
            'class' => 'programlevelselect',
            'data-parentclass' => 'programselect',
            'data-class' => 'levelselect',
            'multiple' => false,
            'data-pluginclass' => $class,
            // 'onchange' => '(function(e){ require("local_costcenter/newcostcenter").changeElement(event) })(event)',
        );

        $mform->addElement('autocomplete', 'semester', get_string('semester', 'local_timetable'), $programlevels, $programleveloptions);
        // $mform->addHelpButton('semester', 'program_levels', 'local_timetable');
        $mform->setType('semester', PARAM_INT);
        $mform->addRule('semester', get_string('missingsemester', 'local_timetable'), 'required', null, 'client');

        include('settimeintervals_form.php');

        /*$mform->addElement('checkbox', 'visible', get_string('publish', 'local_timetable'), '', array('checked' => 'checked', 'name' => 'my-checkbox', 'data-size' => 'small', 'data-on-color' => 'info', 'data-off-color' => 'warning', 'data-on-text' => 'Yes', 'data-switch-set' => 'size', 'data-off-text' => 'No'));
        $mform->addHelpButton('visible', 'publish', 'local_timetable');*/

        $mform->addElement('hidden', 'visible');
        $mform->setType('visible', PARAM_INT);
        $mform->setDefault('visible', true);

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->setConstant('id', $id);

        $submitlable = ($id > 0) ? get_string('save') : get_string('save');

        $this->add_action_buttons($cancel = true, $submitlable);

        if ($id > 0) {
            $intervaldata = $DB->get_record('local_timeintervals', ['id' => $id]);
            $data = array(
                'open_costcenterid' => $intervaldata->schoolid,
                'open_departmentid' => $intervaldata->open_departmentid,
                'open_subdepartment' => $intervaldata->open_subdepartment,
                'program' => $intervaldata->programid,
                'semester' => $intervaldata->semesterid
            );
            $mform->setDefaults($data);
        }
    }

    public function validation($data, $files) {
        global $DB;
        $errors = parent::validation($data, $files);
        $data = (object)$data;
        for($i = 0; $i < count($data->starthours); $i++){
            
            $starttime = mktime($data->starthours[$i], $data->startminutes[$i]);
            $endtime = mktime($data->endhours[$i], $data->endminutes[$i]);

            $previousendtime = mktime($data->endhours[$i-1], $data->endminutes[$i-1]);

            if ($endtime <= $starttime) {
                $errors['section_array['.$i.']'] = get_string('st_et_error', 'local_timetable');
            } else if ($starttime <= $previousendtime) {
                $errors['section_array['.$i.']'] = $errors['section_array['.$i.']'] = get_string('pre_et_error', 'local_timetable');
            }
        }

        if ($data->id == 0) {
            $existssql = "SELECT *
                            FROM {local_timeintervals} 
                            WHERE programid = $data->program
                            AND semesterid = $data->semester
                            AND id != $data->id";
            $exists = $DB->get_record_sql($existssql);
            if ($exists) {
                $errors['semester'] = get_string('semalreadyexists', 'local_timetable');
            }
        }

        return $errors;
    }
}
