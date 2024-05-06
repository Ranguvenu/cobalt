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
 * @package    local_groups
 * @copyright  2022 eAbyas Info Solutions Pvt. Ltd.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;
require_once("$CFG->libdir/externallib.php");
class local_groups_external extends external_api {

    /**
     * Describes the parameters for submit_create_group_form webservice.
     * @return external_function_parameters
     */
    public static function submit_groupsform_form_parameters() {
        return new external_function_parameters(
            array(
                'id' => new external_value(PARAM_INT, 'groupsid', 0),
                'contextid' => new external_value(PARAM_INT, 'The context id for the groups'),
                'jsonformdata' => new external_value(PARAM_RAW, 'The data from the create group form, encoded as a json array'),

            )
        );
    }


    /**
     * form submission of groups name and returns instance of this object
     *
     * @param int $contextid
     * @param [string] $jsonformdata
     * @return groups form submits
     */
    public function submit_groupsform_form($id, $contextid, $jsonformdata) {
        global $PAGE, $CFG;
        require_once($CFG->dirroot . '/local/groups/lib.php');
        $context = context::instance_by_id($contextid, MUST_EXIST);
        self::validate_context($context);
        $serialiseddata = json_decode($jsonformdata);

        $data = array();

        parse_str($serialiseddata, $data);
        $warnings = array();
        $mform = new \local_groups\form\edit_form(null,
                                    array(
                                        'id' => $data->id,
                                        'org' => $data['open_costcenterid'],
                                        'departmentid' => $data['open_departmentid'],
                                        'subdepartmentid' => $data['open_subdepartment']
                                    ), 'post', '', null, true, $data);
        $valdata = $mform->get_data();
        if ($valdata) {
            if ($valdata->id > 0) {
                $groupsupdate = local_groups_update_groups($valdata);
            } else {
                $groupsinsert = local_groups_add_groups($valdata);
            }
        } else {
            throw new moodle_exception('Error in creation');
        }
    }


    /**
     * Returns description of method result value.
     *
     * @return external_description
     * @since Moodle 3.0
     */
    public static function submit_groupsform_form_returns() {
        return new external_value(PARAM_INT, 'groups id');
    }
    /**
     * [groups_status_confirm_parameters description]
     * @return [external function param] [parameters for the groups status update]
     */
    public static function groups_status_confirm_parameters() {
        return new external_function_parameters(
            array(
                'action' => new external_value(PARAM_ACTION, 'Action of the event', false),
                'id' => new external_value(PARAM_INT, 'ID of the record', 0),
                'confirm' => new external_value(PARAM_INT, 'confirm', true),
                'actionstatus' => new external_value(PARAM_RAW, 'actionstatus', false),
                'actionstatusmsg' => new external_value(PARAM_RAW, 'actionstatusmsg', false),
            )
        );
    }
    /**
     * [groups_status_confirm description]
     * @param  [type] $action  [description]
     * @param  [int] $id      [id of the groups]
     * @param  [int] $confirm [confirmation key]
     * @return [boolean]          [true if success]
     */
    public static function groups_status_confirm($action, $id, $confirm) {
        global $DB;
        if ($id) {
            $visible = $DB->get_field('local_groups', 'visible', array('id' => $id));
            if ($visible == 1) {
                $visible = 0;
            } else {
                $visible = 1;
            }
            $sql = "UPDATE {local_groups}
                     SET visible = $visible
                    WHERE id = $id";
            $DB->execute($sql);
            $return = true;
        } else {
            $return = false;
        }
        return $return;
    }
    /**
     * [groups_status_confirm_returns description]
     * @return [external value] [boolean]
     */
    public static function groups_status_confirm_returns() {
        return new external_value(PARAM_BOOL, 'return');
    }
    /**
     * [groups_delete_groups_parameters description]
     * @return [external value] [params for deleting groups]
     */
    public static function groups_delete_groups_parameters() {
        return new external_function_parameters(
            array(
                'id' => new external_value(PARAM_INT, 'userid', 0)
                   )
        );
    }
    /**
     * [groups_delete_groups description]
     * @param  [int] $id id of groups to be deleted
     * @return [boolean]     [true for success]
     */
    public static function groups_delete_groups($id) {
        global $DB;
        if ($id) {
            $groupsdelete = $DB->delete_records('local_groups', array('id' => $id));
            $groupsdelete .= $DB->delete_records('local_groups_permissions', array('groupid' => $id));
            return true;
        } else {
            throw new moodle_exception('Error in deleting');
            return false;
        }
    }

    /**
     * [groups_delete_groups_returns description]
     * @return [external value] [boolean]
     */
    public static function groups_delete_groups_returns() {
        return new external_value(PARAM_BOOL, 'return');
    }

    public static function hide_groups_parameters() {
        return new external_function_parameters(
            array(
                'id'  => new external_value(PARAM_INT, 'id', 0),
            )
        );
    }

    public static function hide_groups($id) {
        global $DB, $USER;
        $params = self::validate_parameters(
            self::hide_groups_parameters(), array('id' => $id)
        );
        $context = context_system::instance();
        self::validate_context($context);
        if ($id) {
            $res = $DB->execute('UPDATE {local_groups} SET visible = 1 WHERE id = ?', [$id]);
        } else {
            throw new moodle_exception('Error');
        }
    }
    public static function hide_groups_returns() {
        return $res;
    }

    public static function unhide_groups_parameters() {
        return new external_function_parameters(
            array(
               'id' => new external_value(PARAM_INT, 'id', 0),
            )
        );
    }

    public static function unhide_groups($id) {
        global $DB, $USER;
        $params = self::validate_parameters (
            self::unhide_groups_parameters(), array('id' => $id)
        );
        $context = context_system::instance();
        self::validate_context($context);
        if ($id) {
            $res = $DB->execute('UPDATE {local_groups} SET visible = 0 WHERE id = ?', [$id]);
        } else {
            throw new moodle_exception('Error');
        }
    }

    public static function unhide_groups_returns() {
        return $res;
    }

    /**
     *
     * @return external_function_parameters
     */
    public static function submit_licenceform_parameters() {
        return new external_function_parameters(
            array(
                'jsonformdata' => new external_value(PARAM_RAW, 'The data of licence settings form, encoded as a json array')
            )
        );
    }

    /**
     *
     *
     * @param int $orgid id for the organization
     * @return array
     */
    public static function submit_licenceform($jsonformdata) {
        global $PAGE;

        $params = self::validate_parameters(self::submit_licenceform_parameters(),
                                            ['jsonformdata' => $jsonformdata]);

        $serialiseddata = json_decode($params['jsonformdata']);
        $data = array();
        parse_str($serialiseddata, $data);
        $PAGE->set_context(\context_system::instance());
        $mform = new \local_groups\form\licence_form(null, array(), 'post', '', null, true, $data);
        $validateddata = $mform->get_data();
        $formdata = data_submitted();
        if ($validateddata) {
            set_config('serialkey', $validateddata->licencekey, 'local_groups');
            $licencekeyhash = md5($validateddata->licencekey);
            set_config('lms_serialkey', $licencekeyhash, 'local_groups');

            $return = array(
                'status' => 'success',
                'licencekey' => $validateddata->licencekey
                );
            return $return;
        } else {
            throw new moodle_exception('Error in creation');
        }
    }
    /**
     * Returns description of method result value
     *
     * @return external_description
     */
    public static function submit_licenceform_returns() {
        return new external_function_parameters(
            array(
                'status' => new external_value(PARAM_RAW, 'success/fail'),
                'licencekey' => new external_value(PARAM_RAW, ' Licence key ')
            )
        );
    }

    public static function managegroupsview_parameters() {
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


    public static function managegroupsview ($options, $dataoptions, $offset = 0, $limit = 0, $contextid, $filterdata) {
        global $OUTPUT, $CFG, $DB, $USER, $PAGE;
        require_once($CFG->dirroot . '/local/groups/lib.php');
        require_login();
        $PAGE->set_url('/local/groups/index.php', array());
        $PAGE->set_context($contextid);
        $params = self::validate_parameters(
            self::managegroupsview_parameters(),
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
        $stable->thead = true;
        $stable->start = $offset;
        $stable->length = $limit;
        $stable->contextid = $decodedata->contextid;

        $data = manage_groups_data($stable, $filtervalues);
        $totalcount = $data['count'];
        unset($data['count']);
        
        return [
            'filterdata' => $filterdata,
            'records' => $data,
            'totalcount' => $totalcount,
            'options' => $options,
            'dataoptions' => $dataoptions,
        ];
    }

    /**
     * Returns description of method result value.
     */
    public static function  managegroupsview_returns() {
        return new external_single_structure([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'totalcount' => new external_value(PARAM_INT, 'total number of users in result set'),
            'filterdata' => new external_value(PARAM_RAW, 'The data for the service'),
            'records' => new external_multiple_structure(
                            new external_single_structure(
                                array(
                                    'actions' => new external_value(PARAM_RAW, 'user  actions', VALUE_OPTIONAL),
                                    'userid' => new external_value(PARAM_RAW, 'userid', VALUE_OPTIONAL),
                                    'batchid' => new external_value(PARAM_RAW, 'batchid', VALUE_OPTIONAL),
                                    'groupid' => new external_value(PARAM_RAW, 'groupid', VALUE_OPTIONAL),
                                    'roleid' => new external_value(PARAM_RAW, 'roleid', VALUE_OPTIONAL),
                                    'startdate' => new external_value(PARAM_RAW, 'startdate', VALUE_OPTIONAL),
                                    'enddate' => new external_value(PARAM_RAW, 'enddate', VALUE_OPTIONAL),
                                    'costfullname' => new external_value(PARAM_RAW, 'costfullname', VALUE_OPTIONAL),
                                     'deptname' => new external_value(PARAM_RAW, 'deptname', VALUE_OPTIONAL),
                                     'role_count' => new external_value(PARAM_RAW, 'role_count', VALUE_OPTIONAL),
                                    'groupname' => new external_value(PARAM_RAW, 'groupname', VALUE_OPTIONAL),
                                    'programname' => new external_value(PARAM_RAW, 'programname', VALUE_OPTIONAL),
                                    'user_url' => new external_value(PARAM_RAW, 'user_url', VALUE_OPTIONAL),
                                    'location_url' => new external_value(PARAM_RAW, 'location_url', VALUE_OPTIONAL),
                                    'groupcount' => new external_value(PARAM_RAW, 'total count of users', VALUE_OPTIONAL),
                                    'visible' => new external_value(PARAM_RAW, 'total count of users', VALUE_OPTIONAL),
                                    'pname' => new external_value(PARAM_RAW, 'Program fullname', VALUE_OPTIONAL),
                                    'departmentname' => new external_value(PARAM_RAW, 'department fullname', VALUE_OPTIONAL),
                                    'subdepartmentname' => new external_value(PARAM_RAW, 'subdepartment fullname', VALUE_OPTIONAL),
                                    'collegefullname' => new external_value(PARAM_RAW, 'college fullname', VALUE_OPTIONAL),
                                    'batch' => new external_value(PARAM_RAW, 'batch', VALUE_OPTIONAL),
                                    'firstlevel' => new external_value(PARAM_RAW, 'firstlevel', VALUE_OPTIONAL),
                                    'secondlevel' => new external_value(PARAM_RAW, 'secondlevel', VALUE_OPTIONAL),
                                    'thirdlevel' => new external_value(PARAM_RAW, 'thirdlevel', VALUE_OPTIONAL),
                                    'superadmin' => new external_value(PARAM_RAW, 'superadmin', VALUE_OPTIONAL),
                                    'firstlevelhead' => new external_value(PARAM_RAW, 'firstlevelhead', VALUE_OPTIONAL),
                                    'secondlevelhead' => new external_value(PARAM_RAW, 'secondlevelhead', VALUE_OPTIONAL),
                                    'thirdlevelhead' => new external_value(PARAM_RAW, 'thirdlevelhead', VALUE_OPTIONAL),
                                )
                            )
                        )
        ]);
    }

    /**
     * [delete_sub_groups_parameters description]
     * @return [external value] [params for deleting groups]
     */
    public static function delete_sub_groups_parameters() {
        return new external_function_parameters(
            array(
                'id' => new external_value(PARAM_INT, 'id', 0)
            )
        );
    }
    /**
     * [delete_sub_groups description]
     * @param  [int] $id id of groups to be deleted
     * @return [boolean]     [true for success]
     */
    public static function delete_sub_groups($id) {
        global $DB;
        if ($id) {
            $data = $DB->get_record('local_sub_groups', ['id' => $id]);
            $DB->delete_records('cohort', array('id' => $data->groupid));
            $DB->delete_records('local_sub_groups', array('id' => $id));
            return true;
        } else {
            throw new moodle_exception('Error in deleting');
            return false;
        }
    }

    /**
     * [delete_sub_groups_returns description]
     * @return [external value] [boolean]
     */
    public static function delete_sub_groups_returns() {
        return new external_value(PARAM_BOOL, 'return');
    }
}
