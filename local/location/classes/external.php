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
class local_location_external extends external_api {

    /**
     * Describes the parameters for submit_create_group_form webservice.
     * @return external_function_parameters
     */
    public static function submit_instituteform_form_parameters() {
        return new external_function_parameters(
            array(
                'contextid' => new external_value(PARAM_INT, 'The context id for the evaluation'),
                'jsonformdata' => new external_value(PARAM_RAW, 'The data from the create group form, encoded as a json array'),
            )
        );
    }

    /**
     * form submission of institute name and returns instance of this object
     *
     * @param int $contextid
     * @param [string] $jsonformdata
     * @return institute form submits
     */
    public function submit_instituteform_form($contextid, $jsonformdata){
        global $PAGE, $CFG;
        require_once($CFG->dirroot . '/local/location/lib.php');

        // We always must pass webservice params through validate_parameters.
        $params = self::validate_parameters(
            self::submit_instituteform_form_parameters(),
            [
                'contextid' => $contextid,
                'jsonformdata' => $jsonformdata
            ]
        );

        $context = context_system::instance();
        // We always must call validate_context in a webservice.
        self::validate_context($context);
        $serialiseddata = json_decode($params['jsonformdata']);
        $data = array();

        parse_str($serialiseddata, $data);
        $warnings = array();
        $mform = new local_location\form\instituteform(null, array(), 'post', '', null, true, $data);
        $institutes  = new local_location\event\location();
        $valdata = $mform->get_data();

        if ($valdata) {
            if ($valdata->id > 0) {
                $institutes->institute_update_instance($valdata);
            } else {
                $institutes->institute_insert_instance($valdata);
            }
        } else {
            // Generate a warning.
            throw new moodle_exception('Error in creation');
        }
    }


    /**
     * Returns description of method result value.
     *
     * @return external_description
     * @since Moodle 3.0
     */
    public static function submit_instituteform_form_returns() {
        return new external_value(PARAM_INT, 'institute id');
    }

    public static function submit_roomform_form_parameters() {
        return new external_function_parameters(
            array(
                'contextid' => new external_value(PARAM_INT, 'The context id for the evaluation'),
                'jsonformdata' => new external_value(PARAM_RAW, 'The data from the create group form, encoded as a json array'),

            )
        );
    }

    /**
     * form submission of institute name and returns instance of this object
     *
     * @param int $contextid
     * @param [string] $jsonformdata
     * @return institute form submits
     */
    public function submit_roomform_form($contextid, $jsonformdata){
        global $PAGE, $CFG;

        require_once($CFG->dirroot . '/local/location/lib.php');
        // We always must pass webservice params through validate_parameters.
        $params = self::validate_parameters(self::submit_roomform_form_parameters(),
                                    ['contextid' => $contextid, 'jsonformdata' => $jsonformdata]);
        // $context = $params['contextid'];
        $context = context_system::instance();
        // We always must call validate_context in a webservice.
        self::validate_context($context);
        $serialiseddata = json_decode($params['jsonformdata']);
        // throw new moodle_exception('Error in creation');
        // die;
        $data = array();

        parse_str($serialiseddata, $data);
        $warnings = array();
         $mform = new local_location\form\roomform(null, array(), 'post', '', null, true, $data);
        $rooms  = new local_location\event\location();
        $valdata = $mform->get_data();

        if($valdata){
            if($valdata->id>0){
                $rooms->room_update_instance($valdata);
            } else{
                $rooms->room_insert_instance($valdata);
            }
        } else {
            // Generate a warning.
            throw new moodle_exception('Error in creation');
        }
    }

    /**
     * Returns description of method result value.
     *
     * @return external_description
     * @since Moodle 3.0
     */
    public static function submit_roomform_form_returns() {
        return new external_value(PARAM_INT, 'room id');
    }

    /**
     * Returns description of method result value.
     *
     * @return external_description
     * @since Moodle 3.0
     */
    public static function location_view_instance_parameters() {
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

    public static function location_view_instance ($options, $dataoptions, $offset = 0, $limit = 0, $contextid, $filterdata) {
        global $OUTPUT, $CFG, $DB, $USER, $PAGE;
        require_once($CFG->dirroot . '/local/lib.php');
        require_once($CFG->dirroot . '/local/location/lib.php');
        require_login();
        $PAGE->set_url('/local/location/index.php', array());
        $PAGE->set_context($contextid);

        $params = self::validate_parameters(
            self::location_view_instance_parameters(),
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
        $data = listof_locations($stable, $filtervalues);
        $totalcount = $data['count'];

        $labelstring = get_config('local_costcenter');

        return [
            'length' => $totalcount,
            'filterdata' => $filterdata,
            'records' => $data,
            'totalcount' => $totalcount,
            'options' => $options,
            'dataoptions' => $dataoptions,
            'firstlevel' => $labelstring->firstlevel,
            'siteadmin' => is_siteadmin(),
        ];
    }

    /**
     * Returns description of method result value.
     *
     * @return external_description
     * @since Moodle 3.0
     */
    public static function location_view_instance_returns() {
        return new external_single_structure([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'totalcount' => new external_value(PARAM_INT, 'total number of challenges in result set'),
            'filterdata' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
            'length' => new external_value(PARAM_INT, 'total number of challenges in result set'),
            'firstlevel' => new external_value(PARAM_RAW, 'firstlevel', VALUE_OPTIONAL),
            'siteadmin' => new external_value(PARAM_BOOL, 'siteadmin', VALUE_OPTIONAL),
            'records' => new external_single_structure(
                array(
                    'length' => new external_value(PARAM_INT, 'length', VALUE_OPTIONAL),
                    'haslocations' => new external_multiple_structure(
                        new external_single_structure(
                            array(
                                'id' => new external_value(PARAM_INT, 'id'),
                                'fullname' => new external_value(PARAM_RAW, 'fullname', VALUE_OPTIONAL),
                                'costcenter' => new external_value(PARAM_RAW, 'costcenter', VALUE_OPTIONAL),
                                'institute_type' => new external_value(PARAM_RAW, 'institute_type'),
                                'address' => new external_value(PARAM_RAW, 'address'),
                                'recordexist' => new external_value(PARAM_BOOL, 'recordexist'),
                                'siteadmin' => new external_value(PARAM_BOOL, 'siteadmin'),
                            )
                        )
                    ),
                )
            )
        ]);
    }

    // delete_location
    public function delete_location_parameters(){
        return new external_function_parameters(
            array(
                'id' => new external_value(PARAM_INT, 'id', 0)
            )
        );
    }

    public function delete_location($id){
        global $DB;        
        if ($id > 0) {
            $sessiontype = $DB->delete_records('local_location_institutes', array('id' => $id));
            return true;
        } else {
            throw new moodle_exception('Error in deleting');
            return false;
        }
            
    }

    public function delete_location_returns(){
        return new external_value(PARAM_BOOL, 'return');
    }

    /**
     * Returns description of method result value.
     *
     * @return external_description
     * @since Moodle 3.0
     */
    public static function room_view_instance_parameters() {
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

    public static function room_view_instance ($options, $dataoptions, $offset = 0, $limit = 0, $contextid, $filterdata) {
        global $OUTPUT, $CFG, $DB, $USER, $PAGE;
        require_once($CFG->dirroot . '/local/lib.php');
        require_once($CFG->dirroot . '/local/location/lib.php');
        require_login();
        $PAGE->set_url('/local/location/index.php', array());
        $PAGE->set_context($contextid);

        $params = self::validate_parameters(
            self::room_view_instance_parameters(),
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
        $data = listof_rooms($stable, $filtervalues);
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
    public static function room_view_instance_returns() {
        return new external_single_structure([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'totalcount' => new external_value(PARAM_INT, 'total number of challenges in result set'),
            'filterdata' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
            'length' => new external_value(PARAM_INT, 'total number of challenges in result set'),
            'records' => new external_single_structure(
                array(
                    'length' => new external_value(PARAM_INT, 'length', VALUE_OPTIONAL),
                    'hasrooms' => new external_multiple_structure(
                        new external_single_structure(
                            array(
                                'id' => new external_value(PARAM_INT, 'id'),
                                'location' => new external_value(PARAM_RAW, 'location', VALUE_OPTIONAL),
                                'building' => new external_value(PARAM_RAW, 'building'),
                                'room' => new external_value(PARAM_RAW, 'room'),
                                'room' => new external_value(PARAM_RAW, 'room'),
                                'capacity' => new external_value(PARAM_RAW, 'capacity'),
                                'recordexist' => new external_value(PARAM_BOOL, 'recordexist'),
                            )
                        )
                    ),
                )
            )
        ]);
    }

    // delete_room
    public function delete_room_parameters(){
        return new external_function_parameters(
            array(
                'id' => new external_value(PARAM_INT, 'id', 0)
            )
        );
    }

    public function delete_room($id){
        global $DB;        
        if ($id > 0) {
            $sessiontype = $DB->delete_records('local_location_room', array('id' => $id));
            return true;
        } else {
            throw new moodle_exception('Error in deleting');
            return false;
        }
            
    }

    public function delete_room_returns(){
        return new external_value(PARAM_BOOL, 'return');
    }
}
