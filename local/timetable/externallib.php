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
 * @package    local_timetable
 * @copyright  2023 Dipanshu Kasera
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;
require_once("$CFG->libdir/externallib.php");
class local_timetable_external extends external_api {

    /**
     * Describes the parameters for submit_create_session_form webservice.
     * @return external_function_parameters
     */
    public static function submit_instance_parameters() {
        return new external_function_parameters(
            array(
                'id' => new external_value(PARAM_INT, 'groupsid', 0),
                'contextid' => new external_value(PARAM_INT, 'The context id for the groups'),
                'jsonformdata' => new external_value(PARAM_RAW, 'The data from the create group form, encoded as a json array'),
                'form_status' => new external_value(PARAM_INT, 'Form position', 0),
                'dayname' => new external_value(PARAM_RAW, 'dayname', 0),
                'semesterid' => new external_value(PARAM_INT, 'semesterid', 0),
                'slotid' => new external_value(PARAM_INT, 'slotid', 0)

            )
        );
    }

    /**
     * form submission of session name and returns instance of this object
     *
     * @param int $contextid
     * @param [string] $jsonformdata
     * @return groups form submits
     */
    public function submit_instance($id, $contextid, $jsonformdata, $form_status, $dayname, $semesterid, $slotid) {
        global $PAGE, $CFG;
        require_once($CFG->dirroot . '/local/timetable/lib.php');
        $context = context::instance_by_id($contextid, MUST_EXIST);
        self::validate_context($context);
        $serialiseddata = json_decode($jsonformdata);

        $data = array();
        parse_str($serialiseddata, $data);

        $warnings = array();
        $mform = new \local_timetable\form\session_form(null,
                                    array('form_status' => $form_status, 'id' => $id), 'post', '', null, true, $data);
        $valdata = $mform->get_data();
        if ($valdata) {
            $valdata->dayname = $dayname;
            $valdata->semesterid = $semesterid;
            $valdata->slotid = $slotid;
            $valdata->session_type = $data['session_type'];
            $valdata->batch_group = $data['batch_group'];
            $valdata->period = $data['period'];
            if ($valdata->id > 0) {
                $sessionupdate = local_timetable_update_session($valdata);
            } else {
                $sessioninsert = local_timetable_add_session($valdata);
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
            throw new moodle_exception('Error in creation');
        }
        $return = array(
            'id' => $sessioninsert,
            'form_status' => $form_status
        );
        return $return;
    }

    /**
     * Returns description of method result value.
     *
     * @return external_description
     * @since Moodle 3.0
     */
    public static function submit_instance_returns() {
        return new external_single_structure(array(
            'id' => new external_value(PARAM_INT, 'id'),
            'form_status' => new external_value(PARAM_INT, 'form_status'),
        ));
    }

    /**
     * Describes the parameters for submit_update_session_form webservice.
     * @return external_function_parameters
     */
    public static function update_instance_parameters() {
        return new external_function_parameters(
            array(
                'id' => new external_value(PARAM_INT, 'id', 0),
                'contextid' => new external_value(PARAM_INT, 'The context id for the groups'),
                'jsonformdata' => new external_value(PARAM_RAW, 'The data from the create group form, encoded as a json array'),
                'form_status' => new external_value(PARAM_INT, 'Form position', 0),
                'dayname' => new external_value(PARAM_RAW, 'dayname', 0),
                'semesterid' => new external_value(PARAM_INT, 'semesterid', 0),
                'slotid' => new external_value(PARAM_INT, 'slotid', 0)

            )
        );
    }

    /**
     * form submission of session name and returns instance of this object
     *
     * @param int $contextid
     * @param [string] $jsonformdata
     * @return groups form submits
     */
    public function update_instance($id, $contextid, $jsonformdata, $form_status, $dayname, $semesterid, $slotid) {
        global $PAGE, $CFG;
        require_once($CFG->dirroot . '/local/timetable/lib.php');
        $context = context::instance_by_id($contextid, MUST_EXIST);
        self::validate_context($context);
        $serialiseddata = json_decode($jsonformdata);

        $data = array();
        parse_str($serialiseddata, $data);

        $warnings = array();
        $mform = new \local_timetable\form\update_session(null,
                                    array('id' => $id, 'course' => $data['coursesid']), 'post', '', null, true, $data);
        $valdata = $mform->get_data();
        if (!empty($valdata)) {
            $valdata->dayname = $dayname;
            $valdata->semesterid = $semesterid;
            $valdata->slotid = $slotid;
            $valdata->session_type = $data['session_type'];
        }
        if ($valdata) {
            if ($valdata->id > 0) {
                $sessionupdate = local_timetable_update_session($valdata);
            }
        } else {
            throw new moodle_exception('Error in updation');
        }
        $return = array(
            'id' => $sessionupdate,
        );

        return $return;
    }

    /**
     * Returns description of method result value.
     *
     * @return external_description
     * @since Moodle 3.0
     */
    public static function update_instance_returns() {
        return new external_single_structure(array(
            'id' => new external_value(PARAM_INT, 'id', ''),
            'form_status' => new external_value(PARAM_INT, 'form_status', ''),
        ));
    }

    /**
     * [delete_instance_parameters description]
     * @return [external value] [params for deleting groups]
     */
    public static function delete_instance_parameters() {
        return new external_function_parameters(array(
                'id' => new external_value(PARAM_INT, 'id', 0)
            )
        );
    }
    /**
     * [delete_instance description]
     * @param  [int] $id id of session to be deleted
     * @return [boolean]     [true for success]
     */
    public static function delete_instance($id) {
        global $DB;
        if ($id > 0) {
            $crrdate = time();
            $sessids = $DB->get_field('local_schedulesessions', 'sessionid', ['id' => $id]);
            $sessid = explode(', ', $sessids);
            $sqlid = array();
            foreach ($sessid as $ids) {
                $sqlid[$ids] = $DB->get_field_sql("SELECT id FROM {attendance_sessions} WHERE id IN ($ids) AND sessdate > $crrdate ");
            }
            $fsessionids = array_flip($sqlid);
            $allsessioncount = count($sessid);
            $fsessioncount = count($fsessionids);
            $psessionids = array_diff($sessid, $fsessionids);
            $finalfliparray = array_flip($psessionids);
            $sessionids = implode(',', array_keys($finalfliparray));
            if($allsessioncount == $fsessioncount){
                $DB->delete_records('local_schedulesessions', array('id' => $id));
            } else {
                $std = new stdClass();
                $std->id = $id;
                $std->sessionid = $sessionids;
                $DB->update_record('local_schedulesessions',$std);
            }
            if($fsessionids){
                foreach ($fsessionids as $key => $value) {
                    $DB->delete_records('attendance_sessions', array('id' => $value));

                }
            }
            return true;
        } else {
            throw new moodle_exception('Error in deleting');
            return false;
        }
    }

    /**
     * [delete_instance_returns description]
     * @return [external value] [boolean]
     */
    public static function delete_instance_returns() {
        return new external_value(PARAM_BOOL, 'return');
    }

    /**
     * [semester_slots_delete_instance_parameters description]
     * @return [external value] [params for deleting groups]
     */
    public static function semester_slots_delete_instance_parameters() {
        return new external_function_parameters(array(
                'id' => new external_value(PARAM_INT, 'id', 0)
            )
        );
    }
    /**
     * [semester_slots_delete_instance description]
     * @param  [int] $id id of semester slots to be deleted
     * @return [boolean]     [true for success]
     */
    public static function semester_slots_delete_instance($id) {
        global $DB;
        $intervalid = $DB->get_field('local_timeintervals', 'id', ['semesterid' => $id]);
        if ($id > 0) {
            $semsterslots = $DB->delete_records('local_timeintervals', array('id' => $intervalid));
            $semsterslots .= $DB->delete_records('local_timeintervals_slots', array('timeintervalid' => $intervalid));
            return true;
        } else {
            throw new moodle_exception('Error in deleting');
            return false;
        }
    }

    /**
     * [delete_instance_returns description]
     * @return [external value] [boolean]
     */
    public static function semester_slots_delete_instance_returns() {
        return new external_value(PARAM_BOOL, 'return');
    }


    public static function timetable_view_instance_parameters() {
        return new external_function_parameters([
                'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
                'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
                'offset' => new external_value(PARAM_INT, 'Number of items to skip from the begging of the result set',
                    VALUE_DEFAULT, 0),
                'limit' => new external_value(PARAM_INT, 'Maximum number of results to return',
                    VALUE_DEFAULT, 0),
                'contextid' => new external_value(PARAM_INT, 'contextid'),
                'filterdata' => new external_value(PARAM_RAW, 'The data for the service'),
            ]);
    }


    public static function timetable_view_instance ($options, $dataoptions, $offset = 0, $limit = 0, $contextid, $filterdata) {
        global $OUTPUT, $CFG, $DB, $USER, $PAGE;
        require_once($CFG->dirroot . '/local/lib.php');
        require_once($CFG->dirroot . '/local/timetable/lib.php');
        require_login();
        $PAGE->set_url('/local/timetable/timelayoutview.php', array());
        $PAGE->set_context($contextid);

        $params = self::validate_parameters(
            self::timetable_view_instance_parameters(),
            [
                'options' => $options,
                'dataoptions' => $dataoptions,
                'offset' => $offset,
                'limit' => $limit,
                'contextid' => $contextid,
                'filterdata' => $filterdata
            ]
        );
        $offset = $params['offset'];
        $limit = $params['limit'];
        $decodedata = json_decode($params['dataoptions']);
        $filtervalues = json_decode($filterdata);



        $stable = new \stdClass();
        $stable->thead = false;
        $stable->start = $offset;
        $stable->length = $limit;
        $stable->userid = $decodedata->userid;
        $data = listof_semester_timetable($stable, $filtervalues);
        $totalcount = $data['count'];

        return [
            'length' => $totalcount,
            'filterdata' => $filterdata,
            'records' => $data,
            'totalcount' => $totalcount,
            'options' => $options,
            'dataoptions' => $dataoptions,
        ];
    }

    /**
     * Returns description of method result value.
     *
     * @return external_description
     * @since Moodle 3.0
     */
    public static function timetable_view_instance_returns() {
        return new external_single_structure([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'totalcount' => new external_value(PARAM_INT, 'total number of challenges in result set'),
            'filterdata' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
            'length' => new external_value(PARAM_INT, 'total number of challenges in result set'),
            'records' => new external_single_structure(
                array(
                    'length' => new external_value(PARAM_INT, 'length', VALUE_OPTIONAL),
                    'hastimetable' => new external_multiple_structure(
                        new external_single_structure(
                            array(
                                'id' => new external_value(PARAM_INT, 'id'),
                                'organisations' => new external_value(PARAM_RAW, 'organisations', VALUE_OPTIONAL),
                                'programname' => new external_value(PARAM_RAW, 'programname'),
                                'batchname' => new external_value(PARAM_RAW, 'batchname'),
                                'timetable' => new external_value(PARAM_RAW, 'timetable', VALUE_OPTIONAL),
                                'class' => new external_value(PARAM_RAW, 'class', VALUE_OPTIONAL),
                                'displaynone' => new external_value(PARAM_RAW, 'displaynone', VALUE_OPTIONAL),
                                'dyclass' => new external_value(PARAM_RAW, 'dyclass', VALUE_OPTIONAL),
                                'configwwwroot' => new external_value(PARAM_RAW, 'configwwwroot', VALUE_OPTIONAL),
                                'firstlevel' => new external_value(PARAM_RAW, 'firstlevel', VALUE_OPTIONAL),
                                'programid' => new external_value(PARAM_INT, 'programid', VALUE_OPTIONAL),
                                'emptydata' => new external_value(PARAM_INT, 'emptydata', VALUE_OPTIONAL),
                                'data' => new external_multiple_structure(
                                    new external_single_structure(
                                        array(
                                            'id' => new external_value(PARAM_INT, 'id', VALUE_OPTIONAL),
                                            'contextid' => new external_value(PARAM_INT, 'contextid', VALUE_OPTIONAL),
                                            'semesterid' => new external_value(PARAM_INT, 'semesterid', VALUE_OPTIONAL),
                                            'startdaterange' => new external_value(PARAM_RAW, 'startdaterange', VALUE_OPTIONAL),
                                            'enddaterange' => new external_value(PARAM_RAW, 'enddaterange', VALUE_OPTIONAL),
                                            'curriculumname' => new external_value(PARAM_RAW, 'curriculumname', VALUE_OPTIONAL),
                                            'levelname' => new external_value(PARAM_RAW, 'levelname', VALUE_OPTIONAL),
                                            'sessionexists' => new external_value(PARAM_RAW, 'sessionexists', VALUE_OPTIONAL),
                                            'active' => new external_value(PARAM_BOOL, 'active', VALUE_OPTIONAL),
                                        )
                                    )
                                ),
                            )
                        )
                    ),
                )
            )
        ]);
    }

    /**
     * Individual sessions parameter value.
     */
    public static function timetable_individual_session_instance_parameters() {
        return new external_function_parameters([
                'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
                'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
                'offset' => new external_value(PARAM_INT, 'Number of items to skip from the begging of the result set',
                    VALUE_DEFAULT, 0),
                'limit' => new external_value(PARAM_INT, 'Maximum number of results to return',
                    VALUE_DEFAULT, 0),
                'contextid' => new external_value(PARAM_INT, 'contextid'),
                'filterdata' => new external_value(PARAM_RAW, 'The data for the service'),
            ]);
    }

    /**
     * Individual sessions method.
     */
    public static function timetable_individual_session_instance ($options, $dataoptions, $offset = 0, $limit = 0, $contextid, $filterdata) {
        global $OUTPUT, $CFG, $DB, $USER, $PAGE;
        require_once($CFG->dirroot . '/local/lib.php');
        require_once($CFG->dirroot . '/local/timetable/lib.php');
        require_login();
        $PAGE->set_url('/local/timetable/timelayoutview.php', array());
        $PAGE->set_context($contextid);

        $params = self::validate_parameters(
            self::timetable_individual_session_instance_parameters(),
            [
                'options' => $options,
                'dataoptions' => $dataoptions,
                'offset' => $offset,
                'limit' => $limit,
                'contextid' => $contextid,
                'filterdata' => $filterdata
            ]
        );
        $offset = $params['offset'];
        $limit = $params['limit'];
        $decodedata = json_decode($params['dataoptions']);
        $filtervalues = json_decode($filterdata);

        $stable = new \stdClass();
        $stable->thead = false;
        $stable->start = $offset;
        $stable->length = $limit;
        $stable->userid = $decodedata->userid;
        $stable->semid = $decodedata->semid;
        $data = listof_individual_semester_session_timetable($stable, $filtervalues);
        $totalcount = $data['count'];

        return [
            'length' => $totalcount,
            'filterdata' => $filterdata,
            'records' => $data,
            'totalcount' => $totalcount,
            'options' => $options,
            'dataoptions' => $dataoptions,
        ];
    }

    /**
     * Returns description of method result value.
     *
     * @return external_description
     * @since Moodle 3.0
     */
    public static function timetable_individual_session_instance_returns() {
        return new external_single_structure([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'totalcount' => new external_value(PARAM_INT, 'total number of challenges in result set'),
            'filterdata' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
            'length' => new external_value(PARAM_INT, 'total number of challenges in result set'),
            'records' => new external_single_structure(
                array(
                    'length' => new external_value(PARAM_INT, 'length', VALUE_OPTIONAL),
                    'hassession' => new external_multiple_structure(
                        new external_single_structure(
                            array(
                                'sr' => new external_value(PARAM_INT, 'sr'),
                                'semid' => new external_value(PARAM_INT, 'semid'),
                                'courseid' => new external_value(PARAM_INT, 'courseid'),
                                'cmid' => new external_value(PARAM_INT, 'cmid'),
                                'sessionid' => new external_value(PARAM_INT, 'sessionid'),
                                'grouptype' => new external_value(PARAM_INT, 'grouptype'),
                                'viewmode' => new external_value(PARAM_INT, 'viewmode'),
                                'sessioname' => new external_value(PARAM_RAW, 'sessioname', VALUE_OPTIONAL),
                                'date' => new external_value(PARAM_RAW, 'date'),
                                'time' => new external_value(PARAM_RAW, 'time'),
                                'teacher' => new external_value(PARAM_RAW, 'teacher', VALUE_OPTIONAL),
                                'type' => new external_value(PARAM_RAW, 'type', VALUE_OPTIONAL),
                                'description' => new external_value(PARAM_RAW, 'description', VALUE_OPTIONAL),
                                'overallrating' => new external_value(PARAM_RAW, 'overallrating', VALUE_OPTIONAL),
                                'configwwwroot' => new external_value(PARAM_RAW, 'configwwwroot', VALUE_OPTIONAL),
                                'taken' => new external_value(PARAM_RAW, 'taken', VALUE_OPTIONAL),
                                'lasttakenby' => new external_value(PARAM_INT, 'lasttakenby', VALUE_OPTIONAL),
                                'programname' => new external_value(PARAM_RAW, 'programname', VALUE_OPTIONAL),
                                'building' => new external_value(PARAM_RAW, 'building', VALUE_OPTIONAL),
                                'room' => new external_value(PARAM_RAW, 'room', VALUE_OPTIONAL),
                                'batch_group' => new external_value(PARAM_RAW, 'batch_group', VALUE_OPTIONAL),
                                'group_name' => new external_value(PARAM_RAW, 'group_name', VALUE_OPTIONAL),
                                'active' => new external_value(PARAM_RAW, 'active', VALUE_OPTIONAL),
                            )
                        )
                    ),
                )
            )
        ]);
    }
    // delete_individual_session
    public function delete_session_parameters(){
        return new external_function_parameters(
            array(
                'id' => new external_value(PARAM_INT, 'userid', 0),
                'contextid' => new external_value(PARAM_INT, 'The context id for the evaluation', false)
            )
        );
    }
    public function delete_session($id, $contextid){
        global $DB;
            $totalsessions = $DB->get_records_sql("SELECT * FROM {local_schedulesessions}");
            if($totalsessions){
                $i = 0;
                foreach ($totalsessions as $key => $val) {
                    $singlesessions = explode(',', $val->sessionid);
                    $singlesessions_count = COUNT($singlesessions);
                    if($singlesessions_count > 1){
                        if (($keys = array_search($id, $singlesessions)) !== false) {
                            unset($singlesessions[$keys]);
                        }
                        $singlesession = implode(',', $singlesessions);
                        $newclass = new \stdClass();
                        $newclass->id = $val->id;
                        $newclass->sessionid = $singlesession;
                        $DB->update_record('local_schedulesessions',$newclass);
                    }
                    if($singlesessions_count == 1 && $singlesessions[$i] == $id) {
                        $DB->delete_records('local_schedulesessions', array('sessionid'=> $singlesessions[$i]));
                    }
                }
            }
        $session_delete = $DB->delete_records('attendance_sessions',['id' => $id]);
        return $return;
    }
    public function delete_session_returns(){
        return new external_value(PARAM_BOOL, 'return');
    }

    // delete_session_type
    public function delete_session_type_parameters(){
        return new external_function_parameters(
            array(
                'id' => new external_value(PARAM_INT, 'userid', 0)
            )
        );
    }

    public function delete_session_type($id){
        global $DB;
        if ($id > 0) {
            $sessiontype = $DB->delete_records('local_session_type', array('id' => $id));
            return true;
        } else {
            throw new moodle_exception('Error in deleting');
            return false;
        }
            
    }

    public function delete_session_type_returns(){
        return new external_value(PARAM_BOOL, 'return');
    }
}
