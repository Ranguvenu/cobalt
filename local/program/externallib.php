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
 * @package
 * @author     eAbyas Info Solutions
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
defined('MOODLE_INTERNAL') || die();
global $PAGE, $OUTPUT;

require_once("$CFG->libdir/externallib.php");
require_once($CFG->dirroot . '/local/program/lib.php');
require_once($CFG->dirroot . '/lib/moodlelib.php');
require_once($CFG->dirroot . '/cohort/lib.php');
use \local_program\program as program;
use \local_program\form\program_form as program_form;
use local_program\local\userdashboard_content as DashboardProgram;

class local_program_external extends external_api {

    public static function program_instance_parameters() {
        return new external_function_parameters(
            array(
                'id' => new external_value(PARAM_INT, 'ID', 0),
                'contextid' => new external_value(PARAM_INT, 'The context id', false),
                'form_status' => new external_value(PARAM_INT, 'Form position', 0),
                'jsonformdata' => new external_value(PARAM_RAW, 'Submitted Form Data', false),
            )
        );
    }

    public static function program_instance($id, $contextid, $form_status, $jsonformdata) {
        global $PAGE, $DB, $CFG, $USER;
        $context = context::instance_by_id($contextid, MUST_EXIST);
        self::validate_context($context);
        $serialiseddata = json_decode($jsonformdata);
        $data = array();
        parse_str($serialiseddata, $data);

        $warnings = array();

        $program = new stdClass();

        // The last param is the ajax submitted data.
        $mform = new program_form(null, array('form_status' => $form_status, 'id' => $id), 'post', '', null, true, $data);
        $validateddata = $mform->get_data();
        if ($validateddata) {
            // Do the action.
            if($form_status == 0)
                $programid = (new program)->manage_program($validateddata);

            if(class_exists('\block_trending_modules\lib')){
                $trendingclass = new \block_trending_modules\lib();
                if(method_exists($trendingclass, 'trending_modules_crud')){
                    $trendingclass->trending_modules_crud($programid, 'local_program');
                }
            }

            $formheaders = array_keys($mform->formstatus);
            $next = $form_status + 1;
            $nextform = array_key_exists($next, $formheaders);
            if ($nextform !== false) {
                $form_status = $next;
                $error = false;
            } else {
                $form_status = -1;
                $error = true;
            }
        } else {
            // Generate a warning.
            throw new moodle_exception('missingprogram', 'local_program');
        }
        $return = array(
            'id' => $programid,
            'form_status' => $form_status);
        return $return;

    }

    public static function program_instance_returns() {
        return new external_single_structure(array(
            'id' => new external_value(PARAM_INT, 'Context id for the framework'),
            'form_status' => new external_value(PARAM_INT, 'form_status'),
        ));
    }

    public static function delete_program_instance_parameters() {
        return new external_function_parameters(
            array(
                'action' => new external_value(PARAM_ACTION, 'Action of the event', false),
                'id' => new external_value(PARAM_INT, 'ID of the record', 0),
                 'programid' => new external_value(PARAM_INT, 'ID of the record', 0),
                'confirm' => new external_value(PARAM_BOOL, 'Confirm', false),
                'programname' => new external_value(PARAM_RAW, 'Action of the event', false),
            )
        );
    }

    public static function delete_program_instance($action, $id, $confirm,$programname) {
        global $DB, $CFG;
        try {
            $usersexists = array();
            $lvlcrsesql = "SELECT lpc.courseid, lp.id, lp.batchid 
                            FROM {local_program} lp
                            JOIN {local_program_level_courses} lpc ON lpc.programid = lp.id 
                            JOIN {cohort} c ON lp.batchid = c.id
                            JOIN {cohort_members} cm ON lp.batchid = cm.cohortid
                            WHERE lp.id = :programid";
            $prog_level_courses = $DB->get_records_sql($lvlcrsesql, array('programid' => $id));
            foreach ($prog_level_courses as $plcourses) {
                $hasusers = enrol_get_course_users($plcourses->courseid);
                if (count($hasusers) > 0) {
                    $usersexists[] = true;
                }
            }
            if (in_array(1, $usersexists)) {
                $return = false;
            } else {
                foreach ($prog_level_courses as $pcourse) {
                    $courseparticipants = enrol_get_course_users($pcourse->courseid);
                    $coursecontext = context_course::instance($pcourse->courseid);
                    foreach ($courseparticipants as $courseparticipant) {
                        $instance = $DB->get_record('enrol', array('id'=>$courseparticipant->ueenrolid), '*', MUST_EXIST);
                        $plugin = enrol_get_plugin($instance->enrol);
                        $plugin->unenrol_user($instance, $courseparticipant->id);
                    }
                    $del_co = delete_course($pcourse->courseid);
                }
                $batchid = $DB->get_field('local_program', 'batchid', array('id' => $id));
                $systemcontext = context_system::instance();
                $cohort = new stdClass();
                $cohort->id = $batchid;
                $cohort->contextid = $systemcontext->id;
                // cohort_delete_cohort($cohort);
                $DB->delete_records('local_program_levels', array('programid' => $id));
                $DB->delete_records('local_program_level_courses', array('programid' => $id));
                $DB->delete_records('local_program_enrolments', array('programid' => $id));
                $DB->delete_records('local_program_users', array('programid' => $id));
                // delete events in calendar
                $DB->delete_records('event', array('plugin_instance'=>$id, 'plugin'=>'local_program'));
                $params = array(
                        'context' => context_system::instance(),
                        'objectid' =>$id
                );

                $event = \local_program\event\program_deleted::create($params);
                $event->add_record_snapshot('local_program', $id);
                $event->trigger();
                $DB->delete_records('local_program', array('id' => $id));
                if(class_exists('\block_trending_modules\lib')){
                    $trendingclass = new \block_trending_modules\lib();
                    if(method_exists($trendingclass, 'trending_modules_crud')){
                        $program_object = new stdClass();
                        $program_object->id = $id;
                        $program_object->module_type = 'local_program';
                        $program_object->delete_record = True;
                        $trendingclass->trending_modules_crud($program_object, 'local_program');
                    }
                }
                $return = true;
            }
        } catch (dml_exception $ex) {
            print_error('deleteerror', 'local_program');
            $return = false;
        }
        return $return;
    }
    public static function delete_program_instance_returns() {
        return new external_value(PARAM_BOOL, 'return');
    }

    public static function program_course_selector_parameters() {
        $query = new external_value(
            PARAM_RAW,
            'Query string'
        );
        $includes = new external_value(
            PARAM_ALPHA,
            'What other contexts to fetch the frameworks from. (all, parents, self)',
            VALUE_DEFAULT,
            'parents'
        );
        return new external_function_parameters(array(
            'query' => $query,
            'context' => self::get_context_parameters(),
            'includes' => $includes
        ));
    }

    public static function program_course_selector($query, $context, $includes = 'parents') {
        global $CFG, $DB, $USER;
        $params = self::validate_parameters(self::program_course_selector_parameters(), array(
            'query' => $query,
            'context' => $context,
            'includes' => $includes
        ));
        $query = $params['query'];
        $includes = $params['includes'];
        $context = self::get_context_from_params($params['context']);

        self::validate_context($context);
        $courses = array();
        if ($query) {
            $queryparams = array();
            $concatsql = '';
            if ((has_capability('local/program:manageprogram', context_system::instance())) && ( !is_siteadmin() && (!has_capability('local/program:manage_multiorganizations', context_system::instance()) && !has_capability('local/costcenter:manage_multiorganizations', context_system::instance())))) {
                $concatsql .= " AND open_costcenterid = :costcenterid";
                $queryparams['costcenterid'] = $USER->open_costcenterid;
                if ((has_capability('local/program:manage_owndepartments', context_system::instance())|| has_capability('local/costcenter:manage_owndepartments', context_system::instance()))) {
                     $concatsql .= " AND open_departmentid = :department";
                     $queryparams['department'] = $USER->open_departmentid;
                 }
           }
            $cousresql = "SELECT c.id, c.fullname
                           FROM {course} AS c
                           JOIN {enrol} AS en on en.courseid = c.id AND en.enrol = 'program' and en.status = 0
                          WHERE c.visible = 1 AND concat(',',c.open_identifiedas,',') LIKE '%,5,%' AND c.fullname LIKE :query AND c.id <> " . SITEID . " {$concatsql}";
            $queryparams['query'] = "%$query%";
            $courses = $DB->get_records_sql($cousresql, $queryparams);
        }

        return array('courses' => $courses);
    }
    public static function program_course_selector_returns() {
        return new external_single_structure(array(
            'courses' => new external_multiple_structure(
                new external_single_structure(array(
                    'id' => new external_value(PARAM_INT, 'ID of the course'),
                    'fullname' => new external_value(PARAM_RAW, 'course fullname'),
                ))
            ),
        ));
    }
    public static function program_form_option_selector_parameters() {
        $query = new external_value(
            PARAM_RAW,
            'Query string'
        );
        $action = new external_value(
            PARAM_RAW,
            'Action for the program form selector'
        );
        $options = new external_value(
            PARAM_RAW,
            'Action for the program form selector'
        );

        return new external_function_parameters(array(
            'query' => $query,
            'context' => self::get_context_parameters(),
            'action' => $action,
            'options' => $options
        ));
    }

    public static function program_form_option_selector($query, $context, $action, $options) {
        global $CFG, $DB, $USER;
        $params = self::validate_parameters(self::program_form_option_selector_parameters(), array(
            'query' => $query,
            'context' => $context,
            'action' => $action,
            'options' => $options
        ));
        $query = trim($params['query']);
        $action = $params['action'];
        $context = self::get_context_from_params($params['context']);
        $options = $params['options'];
        if (!empty($options)) {
            $formoptions = json_decode($options);
        }
        self::validate_context($context);
        if ($query && $action) {
            $querieslib = new \local_program\local\querylib();
            $return = array();

            switch($action) {
                case 'program_costcenter_selector':
                    if ($formoptions->id > 0 && !isset($formoptions->parnetid)) {
                        $parentid = $DB->get_field('local_program', 'costcenter', array('id' => $formoptions->id));
                    } else{
                         $parentid = $formoptions->parnetid;
                    }
                    $depth = $formoptions->depth;
                    $params = array();
                    $costcntersql = "SELECT id, fullname
                                        FROM {local_costcenter}
                                        WHERE visible = 1 ";
                    if ($parentid > 0) {
                        $costcntersql .= is_array($parentid) ? " AND parentid IN :parentid " : " AND parentid = :parentid ";
                        $params['parentid'] = is_array($parentid) ? implode(',', $parentid) : $parentid;

                    } else {
                        $costcntersql .= " AND parentid = 0 ";
                    }
                    if ($depth > 0) {
                        $costcntersql .= " AND depth = :depth ";
                        $params['depth'] = $depth;

                        if ($depth == 3) {
                            if (!(is_siteadmin() || has_any_capability(['local/program:manage_multiorganizations', 'local/costcenter:manage_multiorganizations', 'local/costcenter:manage_ownorganization'], $context))){
                                $costcntersql .= " AND parentid = :userdepartmentid ";
                                $params['userdepartmentid'] = $USER->open_departmentid;
                            } else {
                                $programcostcenter = $DB->get_field('local_program', "costcenter", array('id' => $formoptions->id));
                                if ($programcostcenter) {
                                    $costcntersql .= " AND parentid IN (SELECT id FROM {local_costcenter} AS llc WHERE llc.parentid = :programcostcenter) ";
                                    $params['programcostcenter'] = $programcostcenter;
                                }

                            }
                        }
                    }
                    if (!empty($query)) {
                        $costcntersql .= " AND fullname LIKE :query ";
                        $params['query'] = '%' . $query . '%';
                    }
                    if ($depth > 1) {
                        $return = array(-1 => array('id' => -1,'fullname' => 'All')) + $DB->get_records_sql($costcntersql, $params);
                    } else {
                        $return = $DB->get_records_sql($costcntersql, $params);
                    }

                break;
                case 'program_completions_courses_selector':
                    $courses_sql = "SELECT c.id, c.fullname FROM {course} as c JOIN {local_program_level_courses} as lcc on lcc.courseid=c.id where lcc.programid = {$formoptions->programid}";
                    $return = $DB->get_records_sql($courses_sql);
                break;
            }
            return json_encode($return);
        }
    }
    public static function program_form_option_selector_returns() {
        return new external_value(PARAM_RAW, 'data');
    }
    public static function program_course_instance_parameters() {
        return new external_function_parameters(
            array(
                'id' => new external_value(PARAM_INT, 'ID', 0),
                'contextid' => new external_value(PARAM_INT, 'The context id', false),
                'form_status' => new external_value(PARAM_INT, 'Form position', 0),
                'jsonformdata' => new external_value(PARAM_RAW, 'Submitted Form Data', false),
            )
        );
    }

    public static function program_course_instance($id, $contextid, $form_status, $jsonformdata) {
        global $PAGE, $DB, $CFG, $USER;
        $context = context::instance_by_id($contextid, MUST_EXIST);
        self::validate_context($context);
        $serialiseddata = json_decode($jsonformdata);
        $data = array();
        parse_str($serialiseddata, $data);

        $warnings = array();
        $program = new stdClass();
        // The last param is the ajax submitted data.
        $mform = new programcourse_form(null, array('bcid' => $data['programid'],
            'levelid' => $data['levelid'], 'form_status' => $form_status),
            'post', '', null, true, $data);
        $validateddata = $mform->get_data();
        if ($validateddata) {
            // Do the action.
            $sessionid = (new program)->manage_program_courses($validateddata);
            if ($sessionid > 0) {
                $form_status = -2;
                $error = false;
            } else {
                $error = true;
            }
        } else {
            // Generate a warning.
            throw new moodle_exception('missingprogram', 'local_program');
        }
        $return = array(
            'id' => $sessionid,
            'form_status' => $form_status,
            'formdata' => $validateddata
        );
        return $return;
    }

    public static function program_course_instance_returns() {
        return new external_single_structure(array(
            'id' => new external_value(PARAM_INT, 'Context id for the framework'),
            'form_status' => new external_value(PARAM_INT, 'form_status'),
        ));
    }
    public static function delete_programcourse_instance_parameters() {
        return new external_function_parameters(
            array(
                'action' => new external_value(PARAM_ACTION, 'Action of the event', false),
                'id' => new external_value(PARAM_INT, 'ID of the record', 0),
                'programid' => new external_value(PARAM_INT, 'program ID', 0),
                'confirm' => new external_value(PARAM_BOOL, 'Confirm', false),
            )
        );
    }

    public static function delete_programcourse_instance($action, $id, $programid, $confirm) {
        global $DB;
        try {
            if ($confirm) {
                $course = $DB->get_field('local_program_level_courses', 'courseid', array('programid' => $programid, 'id' => $id));

                $program_completiondata = $DB->get_record_sql("SELECT id,courseids
                                        FROM {local_program_completion}
                                        WHERE programid = $programid");

                if ($program_completiondata->courseids != null) {
                    $program_courseids = explode(',', $program_completiondata->courseids);
                    $array_diff = array_diff($program_courseids, array($course));
                    if (!empty($array_diff)) {
                        $program_completiondata->courseids = implode(',', $array_diff);
                    } else {
                        $program_completiondata->courseids = "NULL";
                    }
                    $DB->update_record('local_program_completion', $program_completiondata);
                    $params = array(
                        'context' => context_system::instance(),
                        'objectid' => $program_completiondata->id
                    );

                    $event = \local_program\event\program_completions_settings_updated::create($params);
                    $event->add_record_snapshot('local_program', $programid);
                    $event->trigger();
                }

                $programtrainers = $DB->get_records_menu('local_program_trainers',
                    array('programid' => $programid), 'trainerid', 'id, trainerid');
                if (!empty($programtrainers)) {
                    foreach ($programtrainers as $programtrainer) {
                        $unenrolprogramtrainer = (new program)->manage_program_course_enrolments($course, $programtrainer,
                            'editingteacher', 'unenrol');
                    }
                }
                $programusers = $DB->get_records_menu('local_program_users',
                    array('programid' => $programid), 'userid', 'id, userid');
                if (!empty($programusers)) {
                    foreach ($programusers as $programuser) {
                        $unenrolprogramuser = (new program)->manage_program_course_enrolments($course, $programuser,
                            'employee', 'unenrol');
                    }
                }
                $params = array(
                    'context' => context_system::instance(),
                    'objectid' =>$id
                );

                $event = \local_program\event\program_courses_deleted::create($params);
                $event->add_record_snapshot('local_program_level_courses', $id);
                $event->trigger();
                $DB->delete_records('local_program_level_courses', array('id' => $id));
                $return = true;
            } else {
                $return = false;
            }
        } catch (dml_exception $ex) {
            print_error('deleteerror', 'local_program');
            $return = false;
        }
        return $return;
    }

    public static function delete_programcourse_instance_returns() {
        return new external_value(PARAM_BOOL, 'return');
    }

    /**
     * Assigin courses to Semesters of a program
     * @param int $contextid
     * @param int $form_status
     * @param json $jsonformdata
     */
    public static function manageprogramlevels_parameters() {
        return new external_function_parameters(
            array(
                'contextid' => new external_value(PARAM_INT, 'The context id', true, 1),
                'form_status' => new external_value(PARAM_INT, 'Form position', false, 0),
                'jsonformdata' => new external_value(PARAM_RAW, 'Submitted Form Data', false),
            )
        );
    }
    public static function manageprogramlevels($contextid, $form_status, $jsonformdata) {
        global $PAGE, $DB, $CFG, $USER;
        $context = context::instance_by_id($contextid, MUST_EXIST);
        self::validate_context($context);
        $serialiseddata = json_decode($jsonformdata);
        $data = array();
        parse_str($serialiseddata, $data);

        $warnings = array();
        $program = new stdClass();

        // The last param is the ajax submitted data.
        $mform = new program_managelevel_form(null, array('id' => $data['id'],
            'programid' => $data['programid'],
            'form_status' => $form_status), 'post', '', null, true, $data);
        $validateddata = $mform->get_data();
        if($validateddata->active == 0){
            $validateddata->enrolmethod = NULL;
        }
        if ($validateddata) {
            // Do the action.

            $action = 'create';
            if ($validateddata->id > 0) {
                $action = 'update';
            }
            $sessionid = (new program)->manage_program_stream_levels($validateddata);
            if ($sessionid > 0) {
                $form_status = -2;
                $error = false;
            } else {
                $error = true;
            }
        } else {
            // Generate a warning.
            throw new moodle_exception('missingprogram', 'local_program');
        }
        $return = array(
            'id' => $sessionid,
            'form_status' => $form_status);
        return $return;
    }

    public static function manageprogramlevels_returns() {
        return new external_single_structure(array(
            'id' => new external_value(PARAM_INT, 'Context id for the framework'),
            'form_status' => new external_value(PARAM_INT, 'form_status'),
        ));
    }
    public static function bclevel_unassign_course_parameters(){
        return new external_function_parameters(
            array(
                'programid' => new external_value(PARAM_INT, 'ID of the program'),
                'levelid' => new external_value(PARAM_INT, 'ID of the program level/semester'),
                'bclcid' => new external_value(PARAM_INT, 'ID of the program level course to be unassigned')
            )
        );
    }
    public static function bclevel_unassign_course($programid, $levelid, $bclcid){
        global $DB;
        if ($programid > 0 && $bclcid > 0 && $levelid > 0) {
            $courseid = $DB->get_field('local_program_level_courses', 'courseid', array('levelid' => $levelid, 'programid' => $programid, 'id' => $bclcid));
            $hasusers = enrol_get_course_users($courseid);
            if (count($hasusers) > 0) {
                return false;
            } else {
                $program = new program();
                $program->unassign_courses_to_bclevel($programid, $levelid, $bclcid);
                return true;
            }
        } else {
            throw new moodle_exception('Error in unassigning of course');
            return false;
        }
    }
    public static function bclevel_unassign_course_returns(){
        return new external_value(PARAM_BOOL, 'return');
    }

    public static function delete_level_instance_parameters() {
        return new external_function_parameters(
            array(
                'action' => new external_value(PARAM_ACTION, 'Action of the event', false),
                'id' => new external_value(PARAM_INT, 'ID of the record', 0),
                'programid' => new external_value(PARAM_INT, 'ID of the record', 0),
                'confirm' => new external_value(PARAM_BOOL, 'Confirm', false),
            )
        );
    }

    public static function delete_level_instance($action, $id, $programid, $confirm) {
        global $DB,$USER;
        try {
            $userexistsarray = array();
            $prgrmlevelcourses = (new program)->program_level_courses($programid, $id, false);
            foreach ($prgrmlevelcourses as $prgrmlevelcourse) {
                $hasusers = enrol_get_course_users($prgrmlevelcourse->id);
                if (count($hasusers) > 0) {
                    $userexistsarray[] = true;
                }
            }
            if (in_array(1, $userexistsarray)) {
                $return = false;
            } else {
                $DB->delete_records('local_program_level_courses', array('levelid' => $id));

                $params = array(
                        'context' => context_system::instance(),
                        'objectid' =>$id
                );

                $event = \local_program\event\level_deleted::create($params);
                $event->add_record_snapshot('local_program_levels', $id);
                $event->trigger();

                $DB->delete_records('local_program_levels', array('id' => $id));
                

                $levelsdata = $DB->get_records('local_program_levels', ['programid' => $programid]);
                $levelscountdata = COUNT($levelsdata);
                $programusersdata = $DB->get_records('local_program_users', ['programid' => $programid]);
                $insertrec = new \stdClass();
                foreach ($programusersdata as $key => $value) {
                    $completedsemdata = $DB->get_records('local_semesters_completions', ['programid' => $programid, 'userid' => $value->userid]);
                    $completedsemcountdata = COUNT($completedsemdata);
                    if($levelscountdata === $completedsemcountdata) {
                        $insertrec->programid = $programid;
                        $insertrec->userid = $value->userid;
                        $insertrec->usercreated = $USER->id;
                        $insertrec->timecreated = time();
                        $insertrec->completionstatus = time();
                        $insertdata->id = $DB->insert_record('local_programcompletions', $insertrec);

                        // Certificate code
                        if($insertdata->id){
                            $templateid = $DB->get_field('local_program', 'certificateid', array('id'=> $programid));
                            $programname = $DB->get_field('local_program', 'shortname', array('id'=> $programid));
                            $cretificateexists = $DB->record_exists('tool_certificate_issues', array('userid' => $value->userid, 'moduleid' => $programid));
                            if (!$cretificateexists) {
                                $certificatedata = new \stdClass();
                                $certificatedata->userid = $value->userid;
                                $certificatedata->templateid = $templateid;
                                $certificatedata->moduleid = $programid;
                                $certificatedata->code = $programname.$value->userid;
                                $certificatedata->moduletype = 'program';
                                $certificatedata->timecreated = time();
                                $certificatedata->emailed = 1;
                                $object->id = $DB->insert_record('tool_certificate_issues', $certificatedata);

                            } else {
                                return null;
                            }
                        }
                    }
                }
                $return = true;
            }
        } catch (dml_exception $ex) {
            print_error('deleteerror', 'local_program');
            $return = false;
        }
        return $return;
    }

    public static function delete_level_instance_returns() {
        return new external_value(PARAM_BOOL, 'return');
    }

    // public static function active_program_instance($action, $id, $confirm,$programname) {
    //     global $DB;
    //     try {
    //         $program=$DB->get_record('local_program',array('id'=>$id));
    //         $program->visible=1;
    //         $DB->update_record('local_program', $program);
    //         if(class_exists('\block_trending_modules\lib')){
    //             $dataobject = new stdClass();
    //             $dataobject->update_status = True;
    //             $dataobject->id = $id;
    //             $dataobject->module_type = 'local_program';
    //             $dataobject->module_visible = 1;
    //             $class = (new \block_trending_modules\lib())->trending_modules_crud($dataobject, 'local_program');
    //         }
    //         $params = array(
    //                 'context' => context_system::instance(),
    //                 'objectid' =>$id
    //         );
    //         $event = \local_program\event\program_activated::create($params);
    //         $event->add_record_snapshot('local_program', $id);
    //         $event->trigger();
    //         $return = true;
    //     } catch (dml_exception $ex) {
    //         print_error('inactiveerror', 'local_program');
    //         $return = false;
    //     }
    //     return $return;
    // }
    // public static function active_program_instance_returns() {
    //     return new external_value(PARAM_BOOL, 'return');
    // }
    public static function organization_streams_parameters() {
        return new external_function_parameters(
            array(
                'orgid' => new external_value(PARAM_INT, 'The id for the costcenter / organization'),
            )
        );
    }
    public static function organization_streams($orgid) {
        global $DB;
     $data = $DB->get_records_menu('local_program_stream',array('costcenterid' => $orgid),'stream','id,stream');
          return json_encode($data);
    }
    public static function organization_streams_returns() {
        return new external_value(PARAM_RAW, 'data');
    }
    public static function data_for_programs_parameters(){
        $filter = new external_value(PARAM_TEXT, 'Filter text');
        $filter_text = new external_value(PARAM_TEXT, 'Filter name',VALUE_OPTIONAL);
        $filter_offset = new external_value(PARAM_INT, 'Offset value',VALUE_OPTIONAL);
        $filter_limit = new external_value(PARAM_INT, 'Limit value',VALUE_OPTIONAL);
        $params = array(
            'filter' => $filter,
            'filter_text' => $filter_text,
            'filter_offset' => $filter_offset,
            'filter_limit' => $filter_limit
        );
        return new external_function_parameters($params);
    }
    public function data_for_programs($filter, $filter_text='', $filter_offset = 0, $filter_limit = 0){
        global $PAGE;

        $params = self::validate_parameters(self::data_for_programs_parameters(), array(
            'filter' => $filter,
            'filter_text' => $filter_text,
            'filter_offset' => $filter_offset,
            'filter_limit' => $filter_limit
        ));

        $PAGE->set_context(context_system::instance());
        $renderable = new local_program\output\program_courses($params['filter'],$params['filter_text'], $params['filter_offset'], $params['filter_limit']);
        $output = $PAGE->get_renderer('block_userdashboard');

        $data= $renderable->export_for_template($output);

        return $data;
    }
    public function data_for_programs_returns(){
        return new external_single_structure(array (
            'total' => new external_value(PARAM_INT, 'Number of enrolled courses.', VALUE_OPTIONAL),
            'inprogresscount'=>  new external_value(PARAM_INT, 'Number of inprogress course count.'),
            'completedcount'=>  new external_value(PARAM_INT, 'Number of complete course count.'),
            'program_view_count'=>  new external_value(PARAM_INT, 'Number of courses count.'),
            'enableslider'=>  new external_value(PARAM_INT, 'Flag for enable the slider.'),
            'inprogress_elearning_available'=>  new external_value(PARAM_INT, 'Flag to check enrolled course available or not.'),
            'course_count_view'=>  new external_value(PARAM_TEXT, 'to add course count class'),
            'functionname' => new external_value(PARAM_TEXT, 'Function name'),
            'subtab' => new external_value(PARAM_TEXT, 'Sub tab name'),
            'programtemplate' => new external_value(PARAM_INT, 'template name',VALUE_OPTIONAL),
            'menu_heading' => new external_value(PARAM_TEXT, 'heading string of the dashboard'),
            'enableflow' => new external_value(PARAM_BOOL, "flag for flow enabling", VALUE_DEFAULT, true),
            'moduledetails' => new external_multiple_structure(
                new external_single_structure(
                    array(
                        'ProgramDescription' => new external_value(PARAM_RAW, 'Description of Program'),
                        'ProgramFullname' => new external_value(PARAM_RAW, 'Fullname of Program'),
                        'DisplayProgramFullname' => new external_value(PARAM_RAW, 'Displayed Program Fullname'),
                        'ProgramUrl' => new external_value(PARAM_RAW, 'Url for the Program'),
                        'ProgramIcon' => new external_value(PARAM_RAW, 'Icon for the program'),
                        'rating_element' => new external_value(PARAM_RAW, 'Rating Element for Program'),
                        'index' => new external_value(PARAM_INT, 'Index of Card'),
                    )
                )
            ),
            'menu_heading' => new external_value(PARAM_TEXT, 'heading string of the dashboard'),
            'nodata_string' => new external_value(PARAM_TEXT, 'no data message'),
            'index' => new external_value(PARAM_INT, 'number of courses count'),
            'filter' => new external_value(PARAM_TEXT, 'filter for display data'),
            'filter_text' => new external_value(PARAM_TEXT, 'filtertext content',VALUE_OPTIONAL),
            'view_more_url' => new external_value(PARAM_URL, 'view_more_url for tab'),
            'viewMoreCard' => new external_value(PARAM_BOOL, 'More info card to display'),
            'enrolled_url' => new external_value(PARAM_URL, 'enrolled_url for tab'),
            'inprogress_url' => new external_value(PARAM_URL, 'inprogress_url for tab'),
            'completed_url' => new external_value(PARAM_URL, 'completed_url for tab'),
        ));
    }
    public static function data_for_programs_paginated_parameters(){
        return new external_function_parameters([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'offset' => new external_value(PARAM_INT, 'Number of items to skip from the begging of the result set',
                VALUE_DEFAULT, 0),
            'limit' => new external_value(PARAM_INT, 'Maximum number of results to return',
                VALUE_DEFAULT, 0),
            'contextid' => new external_value(PARAM_INT, 'contextid'),
            'filterdata' => new external_value(PARAM_RAW, 'filters applied'),
        ]);
    }
    public static function data_for_programs_paginated($options, $dataoptions, $offset = 0, $limit = 0, $contextid, $filterdata){
        global $DB, $PAGE;
        require_login();
        $PAGE->set_context($contextid);

        $decodedoptions = (array)json_decode($options);
        $decodedfilter = (array)json_decode($filterdata);
        $PAGE->set_url('/local/program/userdashboard.php', array('tab' => $filter));
        $filter = $decodedoptions['filter'];
        $filter_text = isset($decodedfilter['search_query']) ? $decodedfilter['search_query'] : '';
        $filter_offset = $offset;
        $filter_limit = $limit;

        $PAGE->set_context(context_system::instance());
        $renderable = new local_program\output\program_courses($filter, $filter_text, $filter_offset, $filter_limit);
        $output = $PAGE->get_renderer('local_program');

        $data = $renderable->export_for_template($output);
        $totalcount = $renderable->coursesViewCount;
        return [
            'totalcount' => $totalcount,
            'length' => $totalcount,
            'filterdata' => $filterdata,
            'records' => array($data),
            'options' => $options,
            'dataoptions' => $dataoptions,
        ];
    }
    public static function data_for_programs_paginated_returns(){
        return new external_single_structure([
        'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
        'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
        'totalcount' => new external_value(PARAM_INT, 'total number of challenges in result set'),
        'filterdata' => new external_value(PARAM_RAW, 'The data for the service'),
        'records' => new external_multiple_structure(
                new external_single_structure(array (
                    'total' => new external_value(PARAM_INT, 'Number of enrolled courses.', VALUE_OPTIONAL),
                    'inprogresscount'=>  new external_value(PARAM_INT, 'Number of inprogress course count.'),
                    'completedcount'=>  new external_value(PARAM_INT, 'Number of complete course count.'),
                    'program_view_count'=>  new external_value(PARAM_INT, 'Number of courses count.'),
                    'inprogress_elearning_available'=>  new external_value(PARAM_INT, 'Flag to check enrolled course available or not.'),
                    'course_count_view'=>  new external_value(PARAM_TEXT, 'to add course count class'),
                    'functionname' => new external_value(PARAM_TEXT, 'Function name'),
                    'subtab' => new external_value(PARAM_TEXT, 'Sub tab name'),
                    'programtemplate' => new external_value(PARAM_INT, 'template name',VALUE_OPTIONAL),
                    'menu_heading' => new external_value(PARAM_TEXT, 'heading string of the dashboard'),
                    'enableflow' => new external_value(PARAM_BOOL, "flag for flow enabling", VALUE_DEFAULT, false),
                    'moduledetails' => new external_multiple_structure(
                        new external_single_structure(
                            array(
                                'ProgramDescription' => new external_value(PARAM_RAW, 'Description of Program'),
                                'ProgramFullname' => new external_value(PARAM_RAW, 'Fullname of Program'),
                                'DisplayProgramFullname' => new external_value(PARAM_RAW, 'Displayed Program Fullname'),
                                'ProgramUrl' => new external_value(PARAM_RAW, 'Url for the Program'),
                                'ProgramIcon' => new external_value(PARAM_RAW, 'Icon for the program'),
                                'rating_element' => new external_value(PARAM_RAW, 'Rating Element for Program')
                            )
                        )
                    ),
                    'menu_heading' => new external_value(PARAM_TEXT, 'heading string of the dashboard'),
                    'nodata_string' => new external_value(PARAM_TEXT, 'no data message'),
                    'index' => new external_value(PARAM_INT, 'number of courses count'),
                    'filter' => new external_value(PARAM_TEXT, 'filter for display data'),
                    'filter_text' => new external_value(PARAM_TEXT, 'filtertext content',VALUE_OPTIONAL),
                )
            )
        )
    ]);
    }
    public static function unenrol_user_parameters(){
        return new external_function_parameters([
            'contextid' => new external_value(PARAM_INT, 'Context for the service'),
            'programid' => new external_value(PARAM_INT, 'Program id for the service'),
            'userid' => new external_value(PARAM_INT, 'Userid For the service')
        ]);
    }
    public static function unenrol_user($contextid, $programid, $userid){
        $params = self::validate_parameters(self::unenrol_user_parameters(), array(
            'contextid' => $contextid,
            'programid' => $programid,
            'userid' => $userid
        ));
        $programclass = new \local_program\program();
        $programclass->program_remove_assignusers($programid, [$userid]);
        return true;
    }
    public static function unenrol_user_returns(){
        return new external_value(PARAM_BOOL, 'return');
    }

    /**
    * [data_for_program_courses_parameters description]
     * @return parameters for data_for_program_courses
     */
    public static function userprograms_parameters() {
        return new external_function_parameters(
            array(
                'status' => new external_value(PARAM_TEXT, 'status'),
                'search' =>  new external_value(PARAM_TEXT, 'search', VALUE_OPTIONAL, ''),
                'page' =>  new external_value(PARAM_INT, 'page', VALUE_OPTIONAL, 0),
                'perpage' =>  new external_value(PARAM_INT, 'perpage', VALUE_OPTIONAL, 15)
            )
        );
    }

    public static function userprograms($status, $search = '', $page = 0, $perpage = 15) {
        global $PAGE;

        $params = self::validate_parameters(self::userprograms_parameters(), array(
            'status' => $status, 'search' => $search, 'page' => $page, 'perpage' => $perpage
        ));

        $PAGE->set_context(context_system::instance());
        $renderable = new \block_userdashboard\output\program_courses($status, $search, $page * $perpage, $perpage);
        $output = $PAGE->get_renderer('block_userdashboard');

        $data = $renderable->export_for_template($output);
        $programs = json_decode($data->inprogress_elearning);
        return array('programs' => $programs, 'total' => $data->total);
    }

    public static function userprograms_returns() {
        return new external_single_structure(array (
                'programs' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'inprogress_coursename' => new external_single_structure(
                                    array(
                                        'id' =>  new external_value(PARAM_INT, 'Program Id'),
                                        'fullname' =>  new external_value(PARAM_RAW, 'Program Name'),
                                        'stream' =>  new external_value(PARAM_INT, 'Stream'),
                                        'description' =>  new external_value(PARAM_RAW, 'Description'),
                                    )
                                ),

                            'lastaccessstime' => new external_value(PARAM_RAW, 'Last Access Time'),
                            'lastaccessdate' => new external_value(PARAM_RAW, 'Last Access Date'),
                            'course_image_url' => new external_value(PARAM_URL, 'Course URL'),
                            'coursesummary' => new external_value(PARAM_RAW, 'Course Summary'),
                            'course_fullname' => new external_value(PARAM_RAW, 'Course Fullname'),
                            'inprogress_coursename_fullname' => new external_value(PARAM_RAW, 'Course Fullname'),
                            'program_url' => new external_value(PARAM_URL, 'Program URL'),
                            'programprogress' => new external_value(PARAM_INT, 'Program Progress')
                        )
                    ), VALUE_DEFAULT, array()
                )
            )
        );
    }
    /**
    * [data for program levels}
     * @return parameters for programlevels
     */
    public static function programlevels_parameters() {
        return new external_function_parameters(
            array('programid' => new external_value(PARAM_INT, 'programid')
            )
        );
    }

    public static function programlevels($programid) {
        global $PAGE, $CFG;

        $params = self::validate_parameters(self::programlevels_parameters(), array(
            'programid' => $programid
        ));

        $PAGE->set_context(context_system::instance());

        $program_levels = (new program)->programlevels($programid);
        return array('levels' => $program_levels);
    }

    public static function programlevels_returns() {
        return new external_single_structure(array (
                'levels' => new external_multiple_structure(
                     new external_single_structure(
                        array(
                            'id' => new external_value(PARAM_INT, 'id'),
                            'programid' => new external_value(PARAM_INT, 'programid'),
                            'level' => new external_value(PARAM_RAW, 'name'),
                            'description' => new external_value(PARAM_RAW, 'description'),
                            'status' => new external_value(PARAM_INT, 'status'),
                            'totalcourses' => new external_value(PARAM_INT, 'totalcourses'),
                            'position' => new external_value(PARAM_INT, 'position'),
                            'totalusers' => new external_value(PARAM_INT, 'totalusers'),
                            'activeusers' => new external_value(PARAM_INT, 'activeusers'),
                            'totalhours' => new external_value(PARAM_INT, 'totalhours'),
                            'totalsessions' => new external_value(PARAM_INT, 'totalsessions'),
                            'activesessions' => new external_value(PARAM_INT, 'activesessions'),
                            'usercreated' => new external_value(PARAM_RAW, 'usercreated'),
                            'timecreated' => new external_value(PARAM_RAW, 'timecreated'),
                            'usermodified' => new external_value(PARAM_RAW, 'usermodified'),
                            'timemodified' => new external_value(PARAM_RAW, 'timemodified'),
                        )
                    ), VALUE_DEFAULT, array()
                 )
            )
        );
    }
    /**
    * [data for program courses}
     * @return parameters for programlevels
     */
    public static function levelcourses_parameters() {
        return new external_function_parameters(
            array(
                'programid' => new external_value(PARAM_INT, 'programid'),
                'levelid' => new external_value(PARAM_INT, 'levelid', VALUE_OPTIONAL, 0)
            )
        );
    }

    public static function levelcourses($programid, $levelid = 0) {
        global $PAGE, $CFG;

        $params = self::validate_parameters(self::levelcourses_parameters(), array(
            'programid' => $programid, 'levelid' => $levelid
        ));

        $programlevelcourses = (new program)->levelcourses($programid, $levelid);

        return array('courses' => $programlevelcourses);
    }

    public static function levelcourses_returns() {
        return new external_single_structure(array (
                'courses' => new external_multiple_structure(
                     new external_single_structure(
                        array(
                            'bclevelcourseid' => new external_value(PARAM_INT, 'program level course id'),
                            'id' => new external_value(PARAM_INT, 'course id'),
                            'shortname' => new external_value(PARAM_TEXT, 'course short name'),
                            'category' => new external_value(PARAM_INT, 'category id'),
                            'fullname' => new external_value(PARAM_TEXT, 'full name'),
                            'idnumber' => new external_value(PARAM_RAW, 'id number', VALUE_OPTIONAL),
                            'summary' => new external_value(PARAM_RAW, 'summary'),
                            'summaryformat' => new external_format_value('summary'),
                            'format' => new external_value(PARAM_PLUGIN,
                                    'course format: weeks, topics, social, site,..'),
                            'showgrades' => new external_value(PARAM_INT,
                                    '1 if grades are shown, otherwise 0', VALUE_OPTIONAL),
                            'newsitems' => new external_value(PARAM_INT,
                                    'number of recent items appearing on the course page', VALUE_OPTIONAL),
                            'startdate' => new external_value(PARAM_INT,
                                    'timestamp when the course start'),
                            'enddate' => new external_value(PARAM_INT,
                                    'timestamp when the course end'),
                            'maxbytes' => new external_value(PARAM_INT,
                                    'largest size of file that can be uploaded into the course',
                                    VALUE_OPTIONAL),
                            'showreports' => new external_value(PARAM_INT,
                                    'are activity report shown (yes = 1, no =0)', VALUE_OPTIONAL),
                            'visible' => new external_value(PARAM_INT,
                                    '1: available to student, 0:not available', VALUE_OPTIONAL),
                            'groupmode' => new external_value(PARAM_INT, 'no group, separate, visible',
                                    VALUE_OPTIONAL),
                            'groupmodeforce' => new external_value(PARAM_INT, '1: yes, 0: no',
                                    VALUE_OPTIONAL),
                            'defaultgroupingid' => new external_value(PARAM_INT, 'default grouping id',
                                    VALUE_OPTIONAL),
                            'timecreated' => new external_value(PARAM_INT,
                                    'timestamp when the course have been created', VALUE_OPTIONAL),
                            'timemodified' => new external_value(PARAM_INT,
                                    'timestamp when the course have been modified', VALUE_OPTIONAL),
                            'enablecompletion' => new external_value(PARAM_INT,
                                    'Enabled, control via completion and activity settings. Disbaled,
                                        not shown in activity settings.',
                                    VALUE_OPTIONAL),
                            'completionnotify' => new external_value(PARAM_INT,
                                    '1: yes 0: no', VALUE_OPTIONAL),
                            'lang' => new external_value(PARAM_SAFEDIR,
                                    'forced course language', VALUE_OPTIONAL),
                            'levelid' => new external_value(PARAM_INT,
                                    'levelid')
                        )
                    ), VALUE_DEFAULT, array()
                 )
            )
        );
    }
    /**
    * [data_for_program_courses_parameters description]
     * @return parameters for data_for_program_courses
     */
    public static function myprograms_parameters() {
        return new external_function_parameters(
            array(
                'status' => new external_value(PARAM_TEXT, 'status'),
                'search' =>  new external_value(PARAM_TEXT, 'search', VALUE_OPTIONAL, ''),
                'page' =>  new external_value(PARAM_INT, 'page', VALUE_OPTIONAL, 0),
                'perpage' =>  new external_value(PARAM_INT, 'perpage', VALUE_OPTIONAL, 15)
            )
        );
    }

    public static function myprograms($status, $search = '', $page = 0, $perpage = 15) {
        global $PAGE, $DB, $CFG;
        require_once($CFG->dirroot . '/local/ratings/lib.php');
        $params = self::validate_parameters(self::myprograms_parameters(), array(
            'status' => $status, 'search' => $search, 'page' => $page, 'perpage' => $perpage
        ));
        if ($status == 'inprogress') {
            $programs = DashboardProgram::inprogress_programs($search, $page, $perpage);
        } else if ($status == 'completed') {
            $programs = DashboardProgram::completed_programs($search, $page, $perpage);
        } else {
            $programs = DashboardProgram::enrolled_programs($search, $page, $perpage);
        }
        foreach($programs as $program) {
            $modulerating = $DB->get_field('local_ratings_likes', 'module_rating', array('module_id' => $program->id, 'module_area' => 'local_program'));
            if(!$modulerating){
                 $modulerating = 0;
            }
            $program->rating = $modulerating;
            $likes = $DB->count_records('local_like', array('likearea' => 'local_program', 'itemid' => $program->id, 'likestatus' => '1'));
            $dislikes = $DB->count_records('local_like', array('likearea' => 'local_program', 'itemid' => $program->id, 'likestatus' => '2'));
            $avgratings = get_rating($program->id, 'local_program');
            $avgrating = $avgratings->avg;
            $ratingusers = $avgratings->count;
            $program->likes = $likes;
            $program->dislikes = $dislikes;
            $program->avgrating = $avgrating;
            $program->ratingusers = $ratingusers;
        }
        return array('programs' => $programs);
    }
    public static function myprograms_returns() {
        return new external_single_structure(array(
                'programs' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'id' => new external_value(PARAM_INT, 'id'),
                            'fullname' => new external_value(PARAM_RAW, 'fullname'),
                            'shortname' => new external_value(PARAM_RAW, 'shortname'),
                            'description' => new external_value(PARAM_RAW, 'description'),
                            'rating' => new external_value(PARAM_INT, 'program Rating'),
                            'likes' => new external_value(PARAM_INT, 'program Likes'),
                            'dislikes' => new external_value(PARAM_INT, 'program Dislikes'),
                            'avgrating' => new external_value(PARAM_FLOAT, 'program avgrating'),
                            'ratingusers' => new external_value(PARAM_FLOAT, 'program users rating')
                        )
                    ), VALUE_DEFAULT, array()
                )
            )
        );
    }
    /** ODL 866: Ikram Starts Here.. **/
    /**
     * Enrol users to courses
     * 
     */
    public static function enrol_user_parameters(){
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'Course id of the course'),
            'programid' => new external_value(PARAM_INT, 'Program id for the service'),
            'userid' => new external_value(PARAM_INT, 'Userid For the service'),
            'levelid' => new external_value(PARAM_INT, 'Level id For the service')
        ]);
    }
    public static function enrol_user($courseid, $programid, $userid, $levelid){
        $params = self::validate_parameters(self::enrol_user_parameters(), array(
            'programid' => $programid,
            'userid' => $userid,
            'courseid' => $courseid,
            'levelid' => $levelid
        ));
        
        $programclass = new \local_program\program();
        $programclass->enrol_user_to_program_courses($programid, $userid, $courseid, $levelid);
        return true;
    }
    public static function enrol_user_returns(){
        return new external_value(PARAM_BOOL, 'return');
    }
    /** ODL 866: Ikram ENDS Here.. **/
}
