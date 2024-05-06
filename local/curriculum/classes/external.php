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
 * External curriculums API
 *
 * @package    local_curriculum
 * @category   external
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();
global $PAGE, $OUTPUT;

require_once("$CFG->libdir/externallib.php");
require_once($CFG->dirroot . '/local/curriculum/program.php');
require_once($CFG->dirroot.'/local/curriculum/classes/form/curriculum_managesemester_form.php');

class local_curriculum_external extends external_api {

    public static function submit_curriculum_data_parameters() {
        return new external_function_parameters(
            array(
                'contextid' => new external_value(PARAM_INT, 'The context id', false),
                'jsonformdata' => new external_value(PARAM_RAW, 'Submitted Form Data', false),
            )
        );
    }

    public static function submit_curriculum_data($contextid, $jsonformdata) {
        global $PAGE, $DB, $CFG, $USER;
        require_once($CFG->dirroot . '/local/curriculum/lib.php');
        $params = self::validate_parameters(self::submit_curriculum_data_parameters(),
                                    ['contextid' => $contextid, 'jsonformdata' => $jsonformdata]);
        $context = context_system::instance();
        self::validate_context($context);
        $serialiseddata = json_decode($params['jsonformdata']);
        $data = array();
        parse_str($serialiseddata, $data);
        $warnings = array();
        $curriculum = new stdClass();

        // The last param is the ajax submitted data.
        $mform = new local_curriculum\form\curriculum_form(null, array(), 'post', '', null, true, $data);
        $validateddata = $mform->get_data();
        // print_r($validateddata);exit;
        if($validateddata->open_costcenterid){
            $validateddata->costcenter = $validateddata->open_costcenterid;
            unset($validateddata->open_costcenterid);
        }
            $validateddata->department = $validateddata->open_departmentid;

        $validateddata->programid = $data['programid'];

        if ($validateddata) {
            // Do  action.
            $curriculumid = (new program)->manage_curriculum($validateddata);
            if ($curriculumid > 0) {
                $form_status = -2;
                $error = false;
            } else {
                $form_status = -2;
                $error = true;
            }
        } else {
            // Generate a warning.
            throw new moodle_exception('missingcurriculum', 'local_curriculum');
        }
        $return = array(
            'id' => $curriculumid,
            'form_status' => $form_status);
        return true;

    }

    public static function submit_curriculum_data_returns() {
          return new external_value(PARAM_BOOL, 'return');
    }
     public static function delete_curriculum_instance_parameters() {
        return new external_function_parameters(
            array(
                'action' => new external_value(PARAM_ACTION, 'Action of the event', false),
                'id' => new external_value(PARAM_INT, 'ID of the record', 0),
                 'curriculumid' => new external_value(PARAM_INT, 'ID of the record', 0),
                 'curriculumname' => new external_value(PARAM_TEXT, 'curriculumname', false),
                'confirm' => new external_value(PARAM_BOOL, 'Confirm', false),
            )
        );
    }

    public static function delete_curriculum_instance($action, $id, $curriculumid,$confirm) {
        global $DB, $CFG;
         try {

            $currriculumexistssql = "SELECT lc.id FROM {local_program} lp
                                        JOIN {local_curriculum} lc ON lc.id = lp.curriculumid
                                        WHERE lp.curriculumid = :curriculumid";
            $curriculummapped = $DB->record_exists_sql($currriculumexistssql, array('curriculumid' => $id));
            if ($curriculummapped) {
                $return = false;
            } else {
                $deletesemcourses = (new program)->deletesemonlinecourses('', $id, '');
                $DB->delete_records('local_cc_semester_courses', array('curriculumid' => $id));
                $DB->delete_records('local_curriculum_semesters', array('curriculumid' => $id));

                // delete events in calendar
                $DB->delete_records('event', array('plugin_instance'=>$id, 'plugin'=>'local_curriculum')); // added by sreenivas
                $params = array(
                        'context' => context_system::instance(),
                        'objectid' =>$id
                );

                $DB->delete_records('local_curriculum', array('id' => $id));
                $return = true;
            }
        } catch (dml_exception $ex) {
            print_error('deleteerror', 'local_curriculum');
            $return = false;
        }
        return $return;
    }
    public static function delete_curriculum_instance_returns() {
        return new external_value(PARAM_BOOL, 'return');
    }
      public static function managecurriculumyears_parameters() {
        return new external_function_parameters(
            array(
                'contextid' => new external_value(PARAM_INT, 'The context id', true, 1),
                'form_status' => new external_value(PARAM_INT, 'Form position', false, 0),
                'jsonformdata' => new external_value(PARAM_RAW, 'Submitted Form Data', false),
            )
        );
    }

    public static function managecurriculumyears($contextid, $form_status, $jsonformdata) {
        global $PAGE, $DB, $CFG, $USER;
        require_once($CFG->dirroot.'/local/curriculum/lib.php');
        $context = context::instance_by_id($contextid, MUST_EXIST);
        self::validate_context($context);
        $serialiseddata = json_decode($jsonformdata);

        $data = array();
        parse_str($serialiseddata, $data);
        $warnings = array();
        $curriculum = new stdClass();

        // The last param is the ajax submitted data.
        $mform = new curriculum_manageyear_form(null, array('id' => $data['id'],
            'curriculumid' => $data['curriculumid'],
            'form_status' => $form_status), 'post', '', null, true, $data);
        $validateddata = $mform->get_data();

        if ($validateddata) {
            // Do the action.
            $program = $DB->get_field('local_curriculum', 'program',
                array('id' => $validateddata->curriculumid));
            $action = 'create';
            if ($validateddata->id > 0) {
                $action = 'update';
            }
            $sessionid = (new program)->manage_program_curriculum_years($validateddata);
            if ($sessionid > 0) {
                $form_status = -2;
                $error = false;
            } else {
                $error = true;
            }
        } else {
            // Generate a warning.
            throw new moodle_exception('missingcurriculum', 'local_curriculum');
        }
        $return = array(
            'id' => $sessionid,
            'form_status' => $form_status);
        return $return;
    }

    public static function managecurriculumyears_returns() {
        return new external_single_structure(array(
            'id' => new external_value(PARAM_INT, 'Context id for the framework'),
            'form_status' => new external_value(PARAM_INT, 'form_status'),
        ));
    }
    public static function managecurriculumsemesters_parameters() {
        return new external_function_parameters(
            array(
                'contextid' => new external_value(PARAM_INT, 'The context id', true, 1),
                'form_status' => new external_value(PARAM_INT, 'Form position', false, 0),
                'jsonformdata' => new external_value(PARAM_RAW, 'Submitted Form Data', false),
            )
        );
    }

    public static function managecurriculumsemesters($contextid, $form_status, $jsonformdata) {
        global $PAGE, $DB, $CFG, $USER;
        require_once($CFG->dirroot . '/local/curriculum/lib.php');
 
        $context = context::instance_by_id($contextid, MUST_EXIST);
        self::validate_context($context);
        $serialiseddata = json_decode($jsonformdata);
        $curriculumid = optional_param('ccid', '', PARAM_INT);
        $type = optional_param('type', '', PARAM_INT);
        $data = array();

        parse_str($serialiseddata, $data);

        $warnings = array();
        $curriculum = new stdClass();

        // The last param is the ajax submitted data.
        $mform = new local_curriculum\form\curriculum_managesemester_form(null, array('id' => $data['id'],
            'programid' => $data['programid'], 'curriculumid' => $curriculumid, 'yearid' => 1, 'type' => $type,
            'form_status' => $form_status), 'post', '', null, true, $data);
        $validateddata = $mform->get_data();

        if ($validateddata) {
            // Do the action.
            $program = $DB->get_field('local_curriculum', 'program',
                array('id' => $validateddata->curriculumid));
            $action = 'create';
            if ($validateddata->id > 0) {
                $action = 'update';
            }
            $sessionid = (new program)->manage_curriculum_program_semesters($validateddata);
            if ($sessionid > 0) {
                $form_status = -2;
                $error = false;
            } else {
                $error = true;
            }
        } else {
            // Generate a warning.
            throw new moodle_exception('missingcurriculum', 'local_curriculum');
        }
        $return = array(
            'id' => $sessionid,
            'form_status' => $form_status);
        return $return;
    }

    public static function managecurriculumsemesters_returns() {
        return new external_single_structure(array(
            'id' => new external_value(PARAM_INT, 'Context id for the framework'),
            'form_status' => new external_value(PARAM_INT, 'form_status'),
        ));
    }

    public static function curriculum_course_instance_parameters() {
        return new external_function_parameters(
            array(
                'id' => new external_value(PARAM_INT, 'ID', 0),
                'contextid' => new external_value(PARAM_INT, 'The context id', false),
                'form_status' => new external_value(PARAM_INT, 'Form position', 0),
                'jsonformdata' => new external_value(PARAM_RAW, 'Submitted Form Data', false),
            )
        );
    }

    public static function curriculum_course_instance($id, $contextid, $form_status, $jsonformdata) {

 
        global $PAGE, $DB, $CFG, $USER;
        require_once($CFG->dirroot.'/local/curriculum/classes/form/programcourse_form.php');
        $context = context::instance_by_id($contextid, MUST_EXIST);
        self::validate_context($context);
        $serialiseddata = json_decode($jsonformdata);
        $data = array();
        parse_str($serialiseddata, $data);

        // The last param is the ajax submitted data.
        $mform = new programcourses_form(null, array('programid' => $programid, 'curriculumid' => $data['curriculumid'],
            'semesterid' => $data['semesterid'], 'yearid' => $data['yearid'], 'form_status' => $form_status),
            'post', '', null, true, $data);
        $validateddata = $mform->get_data();

        if ($validateddata) {
            // Do the action.
            $sessionid = (new program)->manage_curriculum_courses($validateddata);
            if ($sessionid > 0) {
                $form_status = -2;
                $error = false;
            } else {
                $error = true;
            }
        } else {
            // Generate a warning.
            throw new moodle_exception('missingcurriculum', 'local_curriculum');
        }
        $return = array(
            'id' => $sessionid,
            'form_status' => $form_status);
        return $return;
    }

    public static function curriculum_course_instance_returns() {
        return new external_single_structure(array(
            'id' => new external_value(PARAM_INT, 'Context id for the framework'),
            'form_status' => new external_value(PARAM_INT, 'form_status'),
        ));
    }
     public static function curriculum_unassign_course_parameters(){
        return new external_function_parameters(
            array(
                'curriculumid' => new external_value(PARAM_INT, 'ID of the curriculum'),
                'yearid' => new external_value(PARAM_INT, 'ID of the curriculum'),
                'semesterid' => new external_value(PARAM_INT, 'ID of the curriculum semester'),
                'courseid' => new external_value(PARAM_INT, 'ID of the curriculum semester course to be unassigned')
            )
        );
    }
    public static function curriculum_unassign_course($curriculumid, $yearid, $semesterid, $courseid){
        global $CFG;
        if ($curriculumid > 0 && $yearid > 0 && $semesterid > 0 && $courseid > 0) {
            $program = new program();

            $program->unassign_courses_from_semester($curriculumid, $yearid, $semesterid, $courseid);
            return true;
        } else {
            throw new moodle_exception('Error in unassigning of course');
            return false;
        }
    }
    public static function curriculum_unassign_course_returns(){
        return new external_value(PARAM_BOOL, 'return');
    }
    public static function delete_semester_data_parameters() {
        return new external_function_parameters(
            array(
                'action' => new external_value(PARAM_ACTION, 'Action of the event', false),
                'id' => new external_value(PARAM_INT, 'ID of the record', 0),
                'curriculumid' => new external_value(PARAM_INT, 'ID of the record', 0),
                'yearid' => new external_value(PARAM_INT, 'ID of the record', 0),
                'confirm' => new external_value(PARAM_BOOL, 'Confirm', false),
            )
        );
    }

    public static function delete_semester_data($action, $id, $curriculumid, $yearid, $confirm) {
        global $DB,$USER;
        try {
            $hascoursessql = "SELECT courseid FROM {local_cc_semester_courses} WHERE semesterid = :semesterid AND curriculumid = :curriculumid";
            $hascourses = $DB->get_fieldset_sql($hascoursessql, array('semesterid' => $id, 'curriculumid' => $curriculumid));
            if (count($hascourses) > 0) {
                $return = false;
            } else {
                $deletesemcourses = (new program)->deletesemonlinecourses($id, $curriculumid, $yearid);
                $DB->delete_records('local_cc_semester_courses', array('semesterid' => $id));
                $params = array(
                        'context' => context_system::instance(),
                        'objectid' => $id
                );

                $DB->delete_records('local_curriculum_semesters', array('id' => $id));
                $return = true;
            }
        } catch (dml_exception $ex) {
            print_error('deleteerror', 'local_curriculum');
            $return = false;
        }
        return $return;
    }

    public static function delete_semester_data_returns() {
        return new external_value(PARAM_BOOL, 'return');
    }
    public static function delete_semesteryear_data_parameters() {
        return new external_function_parameters(
            array(
                'action' => new external_value(PARAM_ACTION, 'Action of the event', false),
                'id' => new external_value(PARAM_INT, 'ID of the record', 0),
                'curriculumid' => new external_value(PARAM_INT, 'ID of the record', 0),
                'confirm' => new external_value(PARAM_BOOL, 'Confirm', false),
            )
        );
    }
    public static function delete_semesteryear_data($action, $id, $curriculumid,  $confirm) {
        global $DB,$USER;
        try {
            $deletesemcourses = (new program)->deletesemonlinecourses('', $curriculumid, $id);
            $DB->delete_records('local_cc_semester_courses', array('yearid' => $id));
            $DB->delete_records('local_curriculum_semesters', array('yearid' => $id));
            $DB->delete_records('local_cc_session_signups', array('yearid' => $id));
            $params = array(
                    'context' => context_system::instance(),
                    'objectid' => $id
            );

            $event = \local_program\event\year_deleted::create($params);
            $event->add_record_snapshot('local_program_cc_years', $id);
            $event->trigger();

            $yearrecord = $DB->get_record('local_program_cc_years', array('id' => $id));
            $DB->delete_records('local_program_cc_years', array('id' => $id));
            $years = $DB->get_records('local_program_cc_years',array('curriculumid' => $yearrecord->curriculumid));
            $sequence = 1;
            foreach ($years as $key => $year) {
                $yearobject = '';
                $yearobject->id = $year->id;
                $yearobject->sequence = $sequence;
                $DB->update_record('local_program_cc_years',$yearobject);
                $sequence++;
            }
            $yearscount = $DB->count_records_sql('SELECT count(id) as id FROM {local_program_cc_years} WHERE curriculumid = '.$curriculumid);

            $DB->execute('UPDATE {local_curriculum} SET duration ='. $yearscount.' WHERE id = '.$curriculumid);
            $return = true;
        } catch (dml_exception $ex) {
            print_error('deleteerror', 'local_curriculum');
            $return = false;
        }
        return $return;
    }
    public static function delete_semesteryear_data_returns() {
        return new external_value(PARAM_BOOL, 'return');
    }
    /**
     * Lists all the curriculums available on the site
     * 
     */
    public static function list_curriculums_parameters() {
        return new external_function_parameters(
            array(
                'options' => new external_value(PARAM_RAW, 'The paging data for the service', VALUE_OPTIONAL),
                'dataoptions' => new external_value(PARAM_RAW, 'The data for the service', VALUE_OPTIONAL),
                'offset' => new external_value(PARAM_INT, 'Number of items to skip from the begging of the result set',
                    VALUE_DEFAULT, 0),
                'limit' => new external_value(PARAM_INT, 'Maximum number of results to return',
                    VALUE_DEFAULT, 5),
                'contextid' => new external_value(PARAM_INT, 'contextid', VALUE_OPTIONAL),
                'filterdata' => new external_value(PARAM_RAW, 'filters applied', VALUE_OPTIONAL),
            )
        );
    }
    public static function list_curriculums($options, $dataoptions, $offset, $limit, $contextid, $filterdata) {
        global $CFG,$PAGE;
        require_once($CFG->dirroot.'/local/curriculum/lib.php');
        $params = self::validate_parameters(
            self::list_curriculums_parameters(),
            [
                'options' => $options,
                'dataoptions' => $dataoptions,
                'offset' => $offset,
                'limit' => $limit,
                'contextid' => $contextid,
                'filterdata' => $filterdata,
            ]
        );
        $context = context_system::instance();
        $PAGE->set_context($context);
        $offset = $params['offset'];
        $limit = $params['limit'];
        $filtervalues = json_decode($params['filterdata']);
        $stable = new \stdClass();
        $stable->thead = false;
        $stable->start = $offset;
        $stable->length = $limit;
        $data = get_curriculums($stable, $filtervalues);
        $totalcount = $data['totalcount'];
        $labelstring = get_config('local_costcenter');
        $college = get_string('college', 'local_curriculum', $labelstring);
        if(is_siteadmin() || has_capability('local/costcenter:manage_multiorganizations', $context) 
            || has_capability('local/costcenter:manage_owndepartments', $context) 
            || has_capability('local/costcenter:manage_ownorganization', $context)){
            $canshowactions = true;
        } else{
            $canshowactions = false;
        }
        $return = [
            'totalcount'    => $totalcount,
            'length'        => $totalcount,
            'filterdata'    => $filterdata,
            'records'       => array_values($data['curriculum']),
            'options'       => $options,
            'dataoptions'   => $dataoptions,
            'url'           => $CFG->wwwroot,
            'college'       => $college,
            'canshowactions'=> $canshowactions,
        ];
        return $return;

    }
    public static function list_curriculums_returns() {
        return new external_single_structure([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'totalcount' => new external_value(PARAM_INT, 'total number of challenges in result set'),
            'records' => new external_multiple_structure(
                new external_single_structure(
                    array(
                        'id'             => new external_value(PARAM_INT, 'Curriculum ID'),
                        'curriculumname' => new external_value(PARAM_RAW, 'Name of the curriculum'),
                        'university'     => new external_value(PARAM_RAW, 'Name of the college/university'),
                        'action'         => new external_value(PARAM_RAW, 'Action of the records'),
                    )
                )
            ),
            'filterdata' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
            'length' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
            'url' => new external_value(PARAM_RAW, 'Base URL'),
            'college' => new external_value(PARAM_TEXT, 'String for the university/colloge'),
            'canshowactions' => new external_value(PARAM_BOOL, 'Capability for the action buttons'),
        ]);
    }
}
