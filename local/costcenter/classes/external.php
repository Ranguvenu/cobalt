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
 * local local_costcenter
 *
 * @package    local_costcenter
 * @copyright  2022 eAbyas <eAbyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;
require_once("$CFG->libdir/externallib.php");
class local_costcenter_external extends external_api {

    /**
     * Describes the parameters for submit_create_group_form webservice.
     * @return external_function_parameters
     */
    public static function submit_costcenterform_form_parameters() {
        return new external_function_parameters(
            array(
                'contextid' => new external_value(PARAM_INT, 'The context id for the evaluation'),
                'jsonformdata' => new external_value(PARAM_RAW, 'The data from the create group form, encoded as a json array'),
            )
        );
    }

    /**
     * form submission of costcenter name and returns instance of this object
     *
     * @param int $contextid
     * @param [string] $jsonformdata
     * @return costcenter form submits
     */
    public function submit_costcenterform_form($contextid, $jsonformdata) {
        global $PAGE, $CFG;

        require_once($CFG->dirroot . '/local/costcenter/lib.php');
        // We always must pass webservice params through validate_parameters.
        $params = self::validate_parameters(self::submit_costcenterform_form_parameters(),
                                    ['contextid' => $contextid, 'jsonformdata' => $jsonformdata]);
        $context = context_system::instance();
        // We always must call validate_context in a webservice.
        self::validate_context($context);
        $serialiseddata = json_decode($params['jsonformdata']);
        $data = array();
        parse_str($serialiseddata, $data);
        $warnings = array();
        $mform = new local_costcenter\form\organization_form(null,
                                                            array('formtype' => $data['formtype']),
                                                            'post', '', null, true, $data
                                                        );
        $valdata = $mform->get_data();

        if ($valdata) {
            if ($valdata->id > 0) {
                $costcenterupdate = costcenter_edit_instance($valdata->id, $valdata);
            } else {
                $costcenterinsert = costcenter_insert_instance($valdata);
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
    public static function submit_costcenterform_form_returns() {
        return new external_value(PARAM_INT, 'costcenter id');
    }
    /**
     * [costcenter_status_confirm_parameters description]
     * @return [external function param] [parameters for the costcenter status update]
     */
    public static function costcenter_status_confirm_parameters() {
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
     * [costcenter_status_confirm description]
     * @param  [type] $action  [description]
     * @param  [int] $id      [id of the costcenter]
     * @param  [int] $confirm [confirmation key]
     * @return [boolean]          [true if success]
     */
    public static function costcenter_status_confirm($action, $id, $confirm) {
        global $DB;
        if ($id) {
            $visible = $DB->get_field('local_costcenter', 'visible', array('id' => $id));
            if ($visible == 1) {
                $visible = 0;
            } else {
                $visible = 1;
            }
            $sql = "UPDATE {local_costcenter}
                     SET visible = $visible
                    WHERE id=$id";

            $DB->execute($sql);
            $return = true;
        } else {
            $return = false;
        }

        return $return;
    }
    /**
     * [costcenter_status_confirm_returns description]
     * @return [external value] [boolean]
     */
    public static function costcenter_status_confirm_returns() {
        return new external_value(PARAM_BOOL, 'return');
    }
    /**
     * [costcenter_delete_costcenter_parameters description]
     * @return [external value] [params for deleting costcenter]
     */
    public static function costcenter_delete_costcenter_parameters() {
        return new external_function_parameters(
            array(
                'id' => new external_value(PARAM_INT, 'userid', 0)
                   )
        );
    }
    /**
     * [costcenter_delete_costcenter description]
     * @param  [int] $id id of costcenter to be deleted
     * @return [boolean]     [true for success]
     */
    public static function costcenter_delete_costcenter($id) {
        global $DB;
        if ($id) {
            // $costcentercategory = $DB->get_field_sql("SELECT lc.category
            //                                            FROM {local_costcenter} lc
            //                                           JOIN {course_categories} cc ON cc.id = lc.category
            //                                            WHERE lc.id = {$id} "
            //                                         );
            // if ($costcentercategory) {
            //     $dataobject = new stdClass();
            //     $dataobject->id = $costcentercategory;
            //     $dataobject->idnumber = uniqid();
            //     $DB->update_record('course_categories', $dataobject);
            // }
            // $costcenterdelete = $DB->delete_records('local_costcenter', array('id' => $id));
            // $costcenterdelete .= $DB->delete_records('local_costcenter_permissions', array('costcenterid' => $id));

            $parentdata = $DB->get_record('local_costcenter', ['id' => $id]);
            if ($parentdata->depth == 1) {
                    
                    $deptparentid = $DB->get_records('local_costcenter', ['parentid' => $id]);
                    
                    if (!empty($deptparentid)) {
                        foreach ($deptparentid as $key => $value) {

                            $thirdlevel = $DB->get_record('local_costcenter', ['parentid' => $value->id]);
                            
                            $catdelete = $DB->delete_records('course_categories', array('id' => $thirdlevel->category));
                            
                            $catdelete .= $DB->delete_records('course_categories', array('id' => $value->category));
                            
                            /* Second level*/
                            $costcenterdelete .= $DB->delete_records('local_costcenter', array('parentid' => $parentdata->id));
                            
                            /* Third level*/
                            $costcenterdelete .= $DB->delete_records('local_costcenter', array('parentid' => $value->id));

                        }
                    }
                $catdelete = $DB->delete_records('course_categories', array('id' => $parentdata->category));
                $costcenterdelete .= $DB->delete_records('local_costcenter', array('id' => $id));
            } else if ($parentdata->depth == 2) {
                $secondlevel = $DB->get_record('local_costcenter', ['id' => $id]);

                $catdelete .= $DB->delete_records('course_categories', array('parent' => $secondlevel->category));
                
                $catdelete .= $DB->delete_records('course_categories', array('id' => $secondlevel->category));  
                
                $costcenterdelete .= $DB->delete_records('local_costcenter', array('parentid' => $secondlevel->id));

                $costcenterdelete .= $DB->delete_records('local_costcenter', array('id' => $secondlevel->id));
            } else if ($parentdata->depth == 3) {
                $thirdlevel = $DB->get_record('local_costcenter', ['id' => $id]);

                $catdelete .= $DB->delete_records('course_categories', array('id' => $thirdlevel->category));
                        
                $costcenterdelete .= $DB->delete_records('local_costcenter', array('id' => $thirdlevel->id));
            }
            return true;
        } else {
            throw new moodle_exception('Error in deleting');
            return false;
        }
    }
    /**
     * [costcenter_delete_costcenter_returns description]
     * @return [external value] [boolean]
     */
    public static function costcenter_delete_costcenter_returns() {
        return new external_value(PARAM_BOOL, 'return');
    }

    /**
     * Describes the parameters for departmentlist webservice.
     * @return external_function_parameters
     */
    public static function departmentlist_parameters() {
        return new external_function_parameters(
            array(
                'orgid' => new external_value(PARAM_INT, 'The id for the costcenter / organization')
            )
        );
    }

    /**
     * departments list
     *
     * @param int $orgid id for the organization
     * @return array
     */
    public static function departmentlist($orgid) {
        global $DB, $CFG, $USER;
        $orglib = new local_costcenter\functions\userlibfunctions();
        $departmentlist = $orglib->find_departments_list($orgid);
        $return = array(
            'departments' => json_encode($departmentlist)
            );
        return $return;
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     */
    public static function departmentlist_returns() {
        return new external_function_parameters(
            array(
                'departments' => new external_value(PARAM_RAW, 'Departmentlist ')
            )
        );
    }


    /**
     *
     * @return external_function_parameters
     */
    public static function departmentview_parameters() {
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
    public static function departmentview($jsonformdata) {
        global $PAGE;

        $params = self::validate_parameters(self::departmentview_parameters(),
                                            ['jsonformdata' => $jsonformdata]);

        $serialiseddata = json_decode($params['jsonformdata']);
        $data = array();
        parse_str($serialiseddata, $data);
        $PAGE->set_context(\context_system::instance());
        $mform = new \local_costcenter\functions\costcenter(null, array(), 'post', '', null, true, $data);
        $validateddata = $mform->get_data();
        $formdata = data_submitted();
        if ($validateddata) {
            set_config('serialkey', $validateddata->fullname, 'local_costcenter');
            $licencekeyhash = md5($validateddata->fullname);
            set_config('lms_serialkey', $licencekeyhash, 'local_costcenter');

            $return = array(
                'status' => 'success',
                'fullname' => $validateddata->fullname
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
    public static function departmentview_returns() {
        return new external_function_parameters(
            array(
                'status' => new external_value(PARAM_RAW, 'success/fail'),
                'fullname' => new external_value(PARAM_RAW, ' fullname ')
            )
        );
    }
    public static function subdepartmentlist_parameters() {
        return new external_function_parameters(
            array(
                'parentid' => new external_value(PARAM_INT, 'The id for the parent'),
                'parenttype' => new external_value(PARAM_TEXT, 'The type of the parent')
            )
        );
    }
    public static function subdepartmentlist($parentid, $parenttype) {
        global $DB, $CFG, $USER;
        $params = self::validate_parameters(self::subdepartmentlist_parameters(),
                                            ['parentid' => $parentid, 'parenttype' => $parenttype]);
        if (is_array($parentid)) {
            $parentid = implode(',', $parentid);
        }
        if ($parenttype == 'organization') {
            $subdeptsql = "SELECT lc.id, lc.fullname
                            FROM {local_costcenter} lc
                            JOIN {local_costcenter} llc ON llc.id=lc.parentid
                           WHERE llc.id IN (:id) ";
        } else if ($parenttype == 'department') {
            $subdeptsql = "SELECT lc.id, lc.fullname
                            FROM {local_costcenter} lc
                           WHERE lc.parentid IN (:id) ";
        }
        $params = array('id' => $parentid);
        $subdepartmentlist = $DB->get_records_sql_menu($subdeptsql, $params);
        $return = array(
            'subdepartments' => json_encode($subdepartmentlist)
            );
        return $return;
    }
    public static function subdepartmentlist_returns() {
        return new external_function_parameters(
            array(
                'subdepartments' => new external_value(PARAM_RAW, 'Departmentlist')
            )
        );
    }
    public static function form_option_selector_parameters() {
        $query = new external_value(PARAM_RAW, 'Query string');
        $action = new external_value(PARAM_RAW, 'Action for the costcenter form selector');
        $options = new external_value(PARAM_RAW, 'Action for the kpichallenge form selector');
        $searchanywhere = new external_value(PARAM_BOOL, 'find a match anywhere, or only at the beginning');
        $page = new external_value(PARAM_INT, 'Page number');
        $perpage = new external_value(PARAM_INT, 'Number per page');
        $pluginclass = new external_value(PARAM_RAW, 'Action for the costcenter form selector');
        return new external_function_parameters(array(
            'query' => $query,
            'context' => self::get_context_parameters(),
            'action' => $action,
            'options' => $options,
            'searchanywhere' => $searchanywhere,
            'page' => $page,
            'perpage' => $perpage,
            'pluginclass' => $pluginclass,
        ));
    }
    public static function form_option_selector($query, $context, $action, $options, $searchanywhere, $page, $perpage, $pluginclass) {
        global $CFG, $DB, $USER;
        $context['contextid'] = 1;
        $systemcontext =  context_system::instance();
        $params = self::validate_parameters(self::form_option_selector_parameters(), array(
            'query' => $query,
            'context' => $context,
            'action' => $action,
            'options' => $options,
            'searchanywhere' => $searchanywhere,
            'page' => $page,
            'perpage' => $perpage,
            'pluginclass' => $pluginclass,
        ));
        $query = $params['query'];
        $action = $params['action'];
        $context = self::get_context_from_params($params['context']);
        $options = $params['options'];

        $searchanywhere = $params['searchanywhere'];
        $page = $params['page'];
        $perpage = $params['perpage'];

        if (!empty($options)) {
            $formoptions = json_decode($options);
        }
        $roleshortname = $DB->get_field('role', 'shortname', ['id' => $formoptions->roleid]);
        self::validate_context($context);
        $allobject = new \stdClass();
        $allobject->id = 0;
        $allobject->fullname = 'All';
        $allobjectarr = array(0 => $allobject);
        if ($action) {

            $return = array();

            switch($action) {
                case 'costcenter_element_selector':
                    $fields = array("fullname");
                    // var_dump($formoptions->depth < $USER->useraccess['currentroleinfo']['depth']);exit;
                    if ($formoptions->depth < $USER->useraccess['currentroleinfo']['depth']) {
                        $elements = $USER->useraccess['currentroleinfo']['contextinfo'];
                        if ($USER->useraccess['currentroleinfo']['depth'] - 1 > $formoptions->depth) {
                            $elements = [$elements[0]];
                        }

                        $accounts = array_map(function ($data) use ($formoptions, $DB) {
                            $identifiers = explode('/', $data['costcenterpath']);
                            $identifier = $identifiers[$formoptions->depth];
                            $fullname = $DB->get_field('local_costcenter', 'fullname', ['id' => $identifier]);
                            return array('id' => $identifier, 'fullname' => $fullname);
                        }, $elements);
                    } else {
                        $parentids = [];
                        if (is_array($formoptions->parentid)) {
                            $parentids = $formoptions->parentid;
                            if (empty($parentids) && $formoptions->prefix != 'filter') {
                                array_push($parentids, 0);
                            }
                        } else {
                            $parentid = $formoptions->parentid ? $formoptions->parentid : 0;
                            if(!is_siteadmin() && has_capability('local/costcenter:manage_ownorganization', $systemcontext)){
                                $parentid = $formoptions->parentid ? $formoptions->parentid : $USER->open_costcenterid;

                            }
                            if(!is_siteadmin() && !has_capability('local/costcenter:manage_ownorganization', $systemcontext) && has_capability('local/costcenter:manage_owndepartments', $systemcontext)){
                                $parentid = $formoptions->parentid ? $formoptions->parentid : $USER->open_departmentid;

                            }
                            array_push($parentids, $parentid);
                        }
                        if (!empty($parentids)) {
                            list($parentsql, $parentparams) = $DB->get_in_or_equal($parentids, SQL_PARAMS_NAMED, 'organisationid');
                        } else {
                            $parentsql = '';
                            $parentparams = [];
                        }
                        $sqlparams['parentid'] = $formoptions->parentid ? $formoptions->parentid : 0;
                        $sqlparams['depth'] = $formoptions->depth;
                        $likesql = array();
                        $i = 0;
                        if (!empty($query)) {
                            foreach ($fields as $field) {
                                $i++;
                                $likesql[] = $DB->sql_like($field, ":queryparam$i", false);
                                $sqlparams["queryparam$i"] = "%$query%";
                            }
                            $sqlfields = implode(" OR ", $likesql);
                            $concatsql = " AND ($sqlfields) ";
                        }
                        $costcenterpathconcatsql = (new \local_costcenter\lib\accesslib())::get_costcenter_path_field_concatsql($columnname='path');
                        $fields      = 'SELECT id, fullname';
                        $accountssql = " FROM {local_costcenter}
                                         WHERE 1=1 $concatsql AND parentid {$parentsql} $costcenterpathconcatsql AND depth = :depth ";
                        if ($formoptions->id == 0) {
                            $accountssql .= ' AND visible = 1';
                        }
                        if($formoptions->depth > 1){
                            // print_r($parentparams);exit;
                            if($parentsql){
                                $accounts = $DB->get_records_sql($fields . $accountssql, array_merge($parentparams, $sqlparams), ($page * $perpage) - 0, $perpage + 1);
                            // } else {
                            //     $emptyobject = (object) $emptydata;
                            //     $emptyarray = array();
                            //     $emptyarray[] = $emptyobject;
                            //     $accounts = $emptyarray; 
                            }
                        } else {
                            $accounts = $DB->get_records_sql($fields . $accountssql, array_merge($parentparams, $sqlparams), ($page * $perpage) - 0, $perpage + 1);
                        }
                        if ($formoptions->enableallfield) {
                            $accounts = $allobjectarr + $accounts;
                        }
                        if ($accounts) {
                            $totalaccounts = count($accounts);
                            $moreaccounts = $totalaccounts > $perpage;

                            if ($moreaccounts) {
                                // We need to discard the last record.
                                array_pop($accounts);
                            }
                        }
                    }
                    $return = array_values(json_decode(json_encode(($accounts)), true));
                    break;
                case 'costcenter_room_selector':
                    if ((int)$formoptions->id || (int)$formoptions->parentid) {
                        $fields = array("llr.name");
                        $sqlparams['instituteid'] = $formoptions->parentid;
                        $likesql = array();
                        $i = 0;
                        foreach ($fields as $field) {
                            $i++;
                            $likesql[] = $DB->sql_like($field, ":queryparam$i", false);
                            $sqlparams["queryparam$i"] = "%$query%";
                        }
                        $sqlfields = implode(" OR ", $likesql);
                        $concatsql .= " AND ($sqlfields) ";
                        $fields    = "SELECT llr.id, llr.name as fullname ";
                        $roomsql = " FROM {local_location_room} llr
                                        WHERE 1 = 1 $concatsql
                                        AND llr.instituteid = :instituteid ";
                        $rooms = $DB->get_records_sql($fields.$roomsql, $sqlparams, ($page * $perpage) - 0, $perpage + 1);
                        if ($rooms) {
                            $totalaccounts = count($rooms);
                            $moreaccounts = $totalaccounts > $perpage;

                            if ($moreaccounts) {
                                // We need to discard the last record.
                                array_pop($rooms);
                            }
                        }
                    }
                    if (!empty($rooms)) {
                        $return = array_values(json_decode(json_encode(($rooms)), true));
                    }
                break;
                case 'costcenter_building_selector':
                    if ((int)$formoptions->id || (int)$formoptions->parentid || (int)$formoptions->costcenterid) {
                        $fields = array("fullname");
                        $sqlparams['costcenter'] = $formoptions->costcenterid;
                        $likesql = array();
                        $i = 0;
                        foreach ($fields as $field) {
                            $i++;
                            $likesql[] = $DB->sql_like($field, ":queryparam$i", false);
                            $sqlparams["queryparam$i"] = "%$query%";
                        }
                        $sqlfields = implode(" OR ", $likesql);
                        $concatsql .= " AND ($sqlfields) ";
                        $fields    = "SELECT lli.id, lli.fullname ";
                        $locationsql = " FROM {local_location_institutes} lli
                                        WHERE 1 = 1 $concatsql
                                        AND lli.costcenter = :costcenter ";
                        $locations = $DB->get_records_sql($fields.$locationsql, $sqlparams, ($page * $perpage) - 0, $perpage + 1);
                        if ($locations) {
                            $totalaccounts = count($locations);
                            $moreaccounts = $totalaccounts > $perpage;

                            if ($moreaccounts) {
                                // We need to discard the last record.
                                array_pop($locations);
                            }
                        }
                    }
                    if (!empty($locations)) {
                        $return = array_values(json_decode(json_encode(($locations)), true));
                    }
                break;
                case 'costcenter_program_selector':
                    if ((int)$formoptions->organisationid  ||
                        (int)$formoptions->departmentid ||
                        (int)$formoptions->subdepartment ) {
                            
                        $fields = array("name");
                        $sqlparams['organisationid'] = $formoptions->organisationid;
                        $sqlparams['departmentid'] = $formoptions->departmentid;
                        $sqlparams['subdepartment'] = $formoptions->subdepartment;
                        $likesql = array();
                        $i = 0;
                        foreach ($fields as $field) {
                            $i++;
                            $likesql[] = $DB->sql_like($field, ":queryparam$i", false);
                            $sqlparams["queryparam$i"] = "%$query%";
                        }
                        $sqlfields = implode(" OR ", $likesql);
                        $concatsql .= " AND ($sqlfields) ";
                        $fields    = "SELECT p.id, p.name as fullname ";
                        $groupssql = " FROM {local_program} p
                                        WHERE 1 = 1 $concatsql
                                        AND p.costcenter IN (:organisationid) ";

                        if ($formoptions->departmentid) {
                            $groupssql .= " AND p.department IN (:departmentid)";
                        }
                        if ($formoptions->subdepartment) {
                            $groupssql .= " AND p.subdepartment IN (:subdepartment)";
                        }
                        $programname = $DB->get_records_sql($fields.$groupssql, $sqlparams, ($page * $perpage) - 0, $perpage + 1);
                        if ($programname) {
                            $totalaccounts = count($programname);
                            $moreaccounts = $totalaccounts > $perpage;

                            if ($moreaccounts) {
                                // We need to discard the last record.
                                array_pop($programname);
                            }
                        }
                    }
                    if (!empty($programname)) {
                        $return = array_values(json_decode(json_encode(($programname)), true));
                    }
                break;
                case 'program_level_selector':
                    if ((int)$formoptions->parentid) {
                        $fields = array("level");
                        $sqlparams['levelid'] = $formoptions->parentid;
                        $likesql = array();
                        $i = 0;
                        foreach ($fields as $field) {
                            $i++;
                            $likesql[] = $DB->sql_like($field, ":queryparam$i", false);
                            $sqlparams["queryparam$i"] = "%$query%";
                        }
                        $sqlfields = implode(" OR ", $likesql);
                        $concatsql .= " AND ($sqlfields) ";
                        $fields    = "SELECT lpl.id, lpl.level as fullname ";
                        $groupssql = " FROM {local_program_levels} lpl
                                        WHERE 1 = 1 $concatsql
                                        AND lpl.programid = :levelid ";
                        $levelnames = $DB->get_records_sql($fields.$groupssql, $sqlparams, ($page * $perpage) - 0, $perpage + 1);
                        if ($levelnames) {
                            $totalaccounts = count($levelnames);
                            $moreaccounts = $totalaccounts > $perpage;

                            if ($moreaccounts) {
                                // We need to discard the last record.
                                array_pop($levelnames);
                            }
                        }
                    }
                    if (!empty($levelnames)) {
                        $return = array_values(json_decode(json_encode(($levelnames)), true));
                    }
                break;
                case 'costcenter_courseid_selector':
                    /*if ($formoptions->id) {
                        $sql = "SELECT c.id, c.fullname
                                 FROM {course} c
                                 JOIN {local_program_level_courses} lplc ON c.id = lplc.courseid
                                 WHERE lplc.levelid = {$formoptions->id}";
                        $coursenames = $DB->get_records_sql($sql);
                    }*/
                    if ((int)$formoptions->id) {
                        $fields = array("fullname");
                        $sqlparams['parentid'] = $formoptions->id;
                        $likesql = array();
                        $i = 0;
                        foreach ($fields as $field) {
                            $i++;
                            $likesql[] = $DB->sql_like($field, ":queryparam$i", false);
                            $sqlparams["queryparam$i"] = "%$query%";
                        }
                        $sqlfields = implode(" OR ", $likesql);
                        $concatsql .= " AND ($sqlfields) ";
                        $fields = "SELECT c.id, c.fullname";
                        $coursesql = " FROM {course} c
                                       JOIN {local_program_level_courses} lplc ON c.id = lplc.courseid
                                       WHERE 1=1 $concatsql AND lplc.levelid = :parentid ";
                        $courses = $DB->get_records_sql($fields.$coursesql, $sqlparams, ($page * $perpage) - 0, $perpage + 1);
                        if ($courses) {
                            $totalcourses = count($courses);
                            $morecourses = $totalcourses > $perpage;

                            if ($morecourses) {
                                // We need to discard the last record.
                                array_pop($courses);
                            }
                        }
                    }
                    if (!empty($courses)) {
                        $return = array_values(json_decode(json_encode(($courses)), true));
                    }
                break;
                case 'costcenter_teacherid_selector':
                    if ((int)$formoptions->parentid || (int)$formoptions->courseid) {
                        $fields = array("u.firstname", "u.lastname");
                        if (is_siteadmin()
                            || has_capability('local/costcenter:manage_multiorganizations', $systemcontext)
                            || has_capability('local/costcenter:manage_ownorganization', $systemcontext)
                            || has_capability('local/costcenter:manage_owndepartments', $systemcontext)
                            || has_capability('local/costcenter:manage_ownsubdepartments', $systemcontext)) {
                            $sqlparams['parentid'] = $formoptions->parentid;
                        } else {
                            $sqlparams['parentid'] = $formoptions->courseid;
                        }
                        $likesql = array();
                        $i = 0;
                        foreach ($fields as $field) {
                            $i++;
                            $likesql[] = $DB->sql_like($field, ":queryparam$i", false);
                            $sqlparams["queryparam$i"] = "%$query%";
                        }
                        $sqlfields = implode(" OR ", $likesql);
                        $concatsql .= " AND ($sqlfields) ";
                        $fields = "SELECT u.id, CONCAT(u.firstname, ' ', u.lastname) as fullname";
                        $teachersql = " FROM {user} u
                                        JOIN {user_enrolments} ue ON ue.userid = u.id 
                                        JOIN {enrol} e ON e.id = ue.enrolid
                                        WHERE 1=1 $concatsql AND (u.open_type = 0 AND u.deleted = 0)  AND e.courseid = :parentid ";
                        $teachers = $DB->get_records_sql($fields.$teachersql, $sqlparams, ($page * $perpage) - 0, $perpage + 1);
                        if ($teachers) {
                            $totalaccounts = count($teachers);
                            $moreteachers = $totalaccounts > $perpage;

                            if ($moreteachers) {
                                // We need to discard the last record.
                                array_pop($teachers);
                            }
                        }
                    }
                    if (!empty($teachers)) {
                        $return = array_values(json_decode(json_encode(($teachers)), true));
                    }
                break;
                case 'costcenter_departments_selector':
                    if ((int)$formoptions->id) {
                        $fields = array("fullname"/*, "shortname"*/);
                        $sqlparams['parentid'] = $formoptions->id;
                        $likesql = array();
                        $i = 0;
                        foreach ($fields as $field) {
                            $i++;
                            $likesql[] = $DB->sql_like($field, ":queryparam$i", false);
                            $sqlparams["queryparam$i"] = "%$query%";
                        }
                        $sqlfields = implode(" OR ", $likesql);
                        $concatsql .= " AND ($sqlfields) ";
                        $fields      = 'SELECT id, fullname';
                        $accountssql = " FROM {local_costcenter}
                                         WHERE 1=1 $concatsql AND parentid = :parentid ";
                        if ($formoptions->id == 0) {
                            $accountssql .= ' AND visible = 1';
                        }
                        $dfallarray = array(); 
                        $defaultall = new stdClass();
                        $defaultall->id = 0;
                        $defaultall->fullname = get_string('all');
                        $dfallarray[0] = $defaultall;
                        $accounts = $dfallarray + $DB->get_records_sql($fields.$accountssql, $sqlparams, ($page * $perpage) - 0, $perpage + 1);
                        if ($accounts) {
                            $totalaccounts = count($accounts);
                            $moreaccounts = $totalaccounts > $perpage;

                            if ($moreaccounts) {
                                // We need to discard the last record.
                                array_pop($accounts);
                            }
                        }
                    }
                    if (!empty($accounts)) {
                        $return = array_values(json_decode(json_encode(($accounts)), true));
                    }
                break;
                // role code Starts
                case 'costcenter_role_selector':
                    $fields = array("name"/*, "shortname"*/);
                    $sqlparams['editingteacher'] = 'editingteacher';
                    $sqlparams['collegeadmin'] = 'collegeadmin';
                    $sqlparams['orgadmin'] = 'orgadmin';
                    $likesql = array();
                    $i = 0;
                    foreach ($fields as $field) {
                        $i++;
                        $likesql[] = $DB->sql_like($field, ":queryparam$i", false);
                        $sqlparams["queryparam$i"] = "%$query%";
                    }
                    $sqlfields = implode(" OR ", $likesql);
                    $concatsql .= " AND ($sqlfields) ";
                    $fields      = 'SELECT id, name as fullname, shortname';

                    $systemcontext = \context_system::instance();
                    if (has_capability('local/costcenter:manage_multiorganizations', $systemcontext)) {
                        $accountssql = " FROM {role}
                                     WHERE 1=1 $concatsql AND (shortname = 'editingteacher' OR shortname = 'collegeadmin' OR shortname = 'orgadmin' OR shortname = 'departmentadmin') ORDER by id ASC";
                    } else if(has_capability('local/costcenter:manage_ownorganization', $systemcontext)) {
                        $accountssql = " FROM {role}
                                     WHERE 1=1 $concatsql AND (shortname = 'editingteacher' OR shortname = 'collegeadmin' OR shortname = 'departmentadmin') ORDER by id ASC";
                    } else if(has_capability('local/costcenter:manage_owndepartments', $systemcontext)){
                        $accountssql = " FROM {role}
                                     WHERE 1=1 $concatsql AND (shortname = 'editingteacher' OR shortname = 'departmentadmin') ORDER by id ASC";
                    } else if(has_capability('local/costcenter:manage_ownsubdepartments', $systemcontext)){
                        $accountssql = " FROM {role}
                                     WHERE 1=1 $concatsql AND (shortname = 'editingteacher') ORDER by id ASC";
                    }

                    // $accountssql = " FROM {role}
                    //                  WHERE 1=1 $concatsql AND (shortname = 'editingteacher' OR shortname = 'collegeadmin' OR shortname = 'orgadmin') ORDER by id ASC";
                    $accounts = $DB->get_records_sql($fields.$accountssql, $sqlparams, ($page * $perpage) - 0, $perpage + 1);
                    foreach ($accounts as $key => $value) {
                        $value->fullname = ucfirst($value->fullname);
                    }
                    if ($accounts) {
                        $totalaccounts = count($accounts);
                        $moreaccounts = $totalaccounts > $perpage;

                        if ($moreaccounts) {
                            // We need to discard the last record.
                            array_pop($accounts);
                        }
                    }
                    $return = array_values(json_decode(json_encode(($accounts)), true));
                break;
                // role code Ends

                case 'costcenter_organisation_selector':
                    $fields = array("fullname"/*, "shortname"*/);
                    $sqlparams['parentid'] = 0;
                    $likesql = array();
                    $i = 0;
                    foreach ($fields as $field) {
                        $i++;
                        $likesql[] = $DB->sql_like($field, ":queryparam$i", false);
                        $sqlparams["queryparam$i"] = "%$query%";
                    }
                    $sqlfields = implode(" OR ", $likesql);
                    $concatsql .= " AND ($sqlfields) ";
                    $fields      = 'SELECT id, fullname, dept_or_col';
                    $accountssql = " FROM {local_costcenter}
                                     WHERE 1=1 $concatsql AND parentid = :parentid ";
                    // if ($formoptions->id == 0) {
                        $accountssql .= ' AND visible = 1';
                    // }
                    $accounts = $DB->get_records_sql($fields.$accountssql, $sqlparams, ($page * $perpage) - 0, $perpage + 1);
                    if ($accounts) {
                        $totalaccounts = count($accounts);
                        $moreaccounts = $totalaccounts > $perpage;

                        if ($moreaccounts) {
                            // We need to discard the last record.
                            array_pop($accounts);
                        }
                    }
                    $return = array_values(json_decode(json_encode(($accounts)), true));
                break;
                case 'costcenter_department_selector':
                    if ((is_array($formoptions->parentid) && !empty($formoptions->parentid)) ||
                        (!is_array($formoptions->parentid) && $formoptions->parentid > 0) ) {
                        $fields = array("fullname"/*, "shortname"*/);

                        $likesql = array();
                        $i = 0;
                        foreach ($fields as $field) {
                            $i++;
                            $likesql[] = $DB->sql_like($field, ":queryparam$i", false);
                            $sqlparams["queryparam$i"] = "%$query%";
                        }
                        $sqlfields = implode(" OR ", $likesql);
                        $concatsql .= " AND ($sqlfields) ";

                        list($organisationidssql, $organisationparams) =
                            $DB->get_in_or_equal($formoptions->parentid, SQL_PARAMS_NAMED, 'organisationid');

                        $fields      = 'SELECT id, fullname';
                        $lobssql = " FROM {local_costcenter}
                                         WHERE 1=1 $concatsql AND parentid $organisationidssql ";
                        // if ($formoptions->id == 0) {
                            $lobssql .= ' AND visible = 1';
                        // }
                        $sqlparams = array_merge($sqlparams, $organisationparams);
                        if($pluginclass === 'employeeclass'){
                            if($roleshortname === 'orgadmin'){
                                $departments = $allobjectarr;
                            } else {
                            $departments = $DB->get_records_sql($fields.$lobssql, $sqlparams,
                                                               ($page * $perpage) - 0, $perpage + 1);
                            }
                        } else{
                        if($pluginclass === 'curriculumclass' || $pluginclass === 'courseclass'){
                                $departments = $allobjectarr+$DB->get_records_sql($fields.$lobssql, $sqlparams, ($page * $perpage) - 0, $perpage + 1);
                            }else{


                            $departments = $DB->get_records_sql($fields.$lobssql, $sqlparams,
                                                               ($page * $perpage) - 0, $perpage + 1
                                                            );
                            }
                        }
                            
                        $return = array_values($departments);
                    } else {
                        $return = $allobjectarr;
                    }
                break;
                case 'costcenter_subdepartment_selector':
                    if ((is_array($formoptions->parentid) && !empty($formoptions->parentid)) ||
                        (!is_array($formoptions->parentid) && $formoptions->parentid > 0) ) {
                        $fields = array("fullname"/*, "shortname"*/);

                        $likesql = array();
                        $i = 0;
                        foreach ($fields as $field) {
                            $i++;
                            $likesql[] = $DB->sql_like($field, ":queryparam$i", false);
                            $sqlparams["queryparam$i"] = "%$query%";
                        }
                        $sqlfields = implode(" OR ", $likesql);
                        $concatsql .= " AND ($sqlfields) ";

                        list($parentidsql, $parentparams) =
                                $DB->get_in_or_equal($formoptions->parentid, SQL_PARAMS_NAMED, 'organisationid');

                        $fields      = 'SELECT id, fullname';
                        $subdepartmentsql = " FROM {local_costcenter}
                                         WHERE 1=1 $concatsql AND parentid $parentidsql ";
                        // if ($formoptions->id == 0) {
                            $subdepartmentsql .= ' AND visible = 1';
                        // }
                        $sqlparams = array_merge($sqlparams, $parentparams);
                        if($pluginclass === 'employeeclass'){
                            if($roleshortname === 'orgadmin'){
                                $subdepartments = $allobjectarr;
                            } else if($roleshortname === 'collegeadmin'){
                                $subdepartments = $allobjectarr;
                            } else if($roleshortname === 'editingteacher'){
                                $subdepartments = $DB->get_records_sql($fields.$subdepartmentsql,
                                                                $sqlparams, ($page * $perpage) - 0,
                                                                $perpage + 1
                                                            );
                            } 
                            else {
                                $subdepartments = $DB->get_records_sql($fields.$subdepartmentsql,
                                                                $sqlparams, ($page * $perpage) - 0,
                                                                $perpage + 1
                                                            );
                            }
                        } else{


                        // if($pluginclass === 'curriculumclass' || $pluginclass === 'courseclass'){
                                $subdepartments = $allobjectarr+$DB->get_records_sql($fields.$subdepartmentsql,
                                                            $sqlparams, ($page * $perpage) - 0,
                                                            $perpage + 1
                                                        );
                            // }
                            // else{
                            //     $subdepartments = $DB->get_records_sql($fields.$subdepartmentsql,
                            //                                     $sqlparams, ($page * $perpage) - 0,
                            //                                     $perpage + 1
                            //                                 );
                            // }
                        }

                        if ($departments) {
                            $totalsubdepartments = count($subdepartments);
                            $moresubdepartments = $totalsubdepartments > $perpage;

                            if ($moresubdepartments) {
                                // We need to discard the last record.
                                array_pop($subdepartments);
                            }
                        }
                        $return = array_values(json_decode(json_encode(($subdepartments)), true));
                    } else {
                        $return = array_values(json_decode(json_encode(($allobjectarr)), true));
                    }
                break;
                case 'costcenter_category_selector':
                    if ((int)$formoptions->organisationid  ||
                        (int)$formoptions->departmentid ||
                        (int)$formoptions->subdepartment ) {
                        $parentidarray = array();
                        if (!empty($formoptions->subdepartment)) {
                            if ((int)$formoptions->subdepartment > 0) {
                                $parentid = $formoptions->subdepartment;
                            }
                        }
                        if (!empty($formoptions->departmentid) && empty($parentid)) {
                            if ((int)$formoptions->departmentid > 0) {
                                $parentid = $formoptions->departmentid;
                            }
                        }
                        if (!empty($formoptions->organisationid) && empty($parentid)) {
                            if ((int)$formoptions->organisationid > 0) {
                                $parentid = $formoptions->organisationid;
                            }
                        }
                        if ($formoptions->id > 1) {
                            $parentcategory = $DB->get_field('local_costcenter', 'category', array('category' => $formoptions->id));
                        } else {
                            $parentcategory = $DB->get_field('local_costcenter', 'category', array('id' => $parentid));
                        }
                        $fields = array("name");

                        $likesql = array();
                        $i = 0;
                        if ($query != '') {
                            foreach ($fields as $field) {
                                $i++;
                                $likesql[] = $DB->sql_like($field, ":queryparam$i", false);
                                $sqlparams["queryparam$i"] = "%$query%";
                            }
                            $sqlfields = implode(" OR ", $likesql);
                            $concatsql = " AND ($sqlfields) ";
                        } else {
                            $sqlparams = [];
                            $concatsql = " ";
                        }

                        $fields      = 'SELECT id, path AS fullname';
                        $categoriessql = " FROM {course_categories}
                                          WHERE 1=1 $concatsql
                                           AND (path like '%/{$parentcategory}/%' OR id = $parentcategory) ";
                        if ($formoptions->id == 0) {
                            $categoriessql .= ' AND visible = 1';
                        } 
                        else {
                            $categoriessql .= ' AND visible = 1';
                        }

                        $categories = $DB->get_records_sql_menu($fields.$categoriessql,
                                           $sqlparams, ($page * $perpage) - 0, $perpage + 1);
                        if ($categories) {
                            $totalcategories = count($categories);
                            $morecategories = $totalcategories > $perpage;

                            if ($morecategories) {
                                // We need to discard the last record.
                                array_pop($categories);
                            }
                        }
                        foreach ($categories as $key => $categorywise) {
                            $explodepaths = explode('/', $categorywise);

                            $countcat = count($explodepaths);
                            if ($countcat > 0) {
                                $catpathnames = array();
                                for ($i = 0; $i < $countcat; $i++) {
                                    if ($i != 0) {
                                        $catpathnames[$i] = $DB->get_field('course_categories',
                                                            'name', array('id' => $explodepaths[$i]));
                                    }
                                }
                                if (count($catpathnames) > 1) {
                                    $return[] = array('id' => $key, 'fullname' => implode(' / ', $catpathnames));
                                } else {
                                    $return[] = array('id' => $key, 'fullname' => $catpathnames[1]);;
                                }
                            }
                        }
                    }
                break;
                case 'costcenter_course_selector' :
                    $classname = '\\local_courses\\local\\general_lib';
                    $class = class_exists($classname) ? new $classname() : null;
                    if (!is_null($class)) {
                        $methodname = 'get_courses_having_completion_criteria';
                        if (isset($formoptions->courseid) && $formoptions->courseid > 1 && method_exists($class, $methodname)) {
                            $courses = $class->$methodname($formoptions->courseid, $query, ($page * $perpage) - 0, $perpage + 1);
                            if ($courses) {
                                $totalcourses = count($courses);
                                $morecourses = $totalcourses > $perpage;

                                if ($morecourses) {
                                    // We need to discard the last record.
                                    array_pop($courses);
                                }
                            }
                            $return = array_values(json_decode(json_encode(($courses)), true));
                        }
                    }
                break;
                case 'costcenter_batch_selector' :
                    $fields = array("name");
                    $sqlparams['organisationid'] = $formoptions->organisationid;
                    $sqlparams['departmentid'] = $formoptions->departmentid;
                    $sqlparams['subdepartment'] = $formoptions->subdepartment;
                    $likesql = array();
                    $i = 0;
                    foreach ($fields as $field) {
                        $i++;
                        $likesql[] = $DB->sql_like($field, ":queryparam$i", false);
                        $sqlparams["queryparam$i"] = "%$query%";
                    }
                    $sqlfields = implode(" OR ", $likesql);
                    $concatsql .= " AND ($sqlfields) ";
                    $fields    = "SELECT c.id,c.name as fullname ";
                    $groupssql = " FROM {cohort} c 
                                    JOIN {local_groups} lg ON lg.cohortid = c.id
                                    WHERE c.visible = 1 $concatsql
                                    AND lg.costcenterid IN (:organisationid) ";

                    $groupssql .= " AND c.id NOT IN (SELECT p.batchid FROM {local_program} p)";

                    // if ($formoptions->departmentid) {
                    //     $groupssql .= " AND lg.departmentid IN (:departmentid)";
                    // }
                    // if ($formoptions->subdepartment) {
                    //     $groupssql .= " AND lg.subdepartmentid IN (:subdepartment)";
                    // }
                    if(!$formoptions->departmentid){
                        $groupssql .= " AND lg.departmentid = 0";
                    }
                    if(!$formoptions->subdepartment){
                        $groupssql .= " AND lg.subdepartmentid = 0";
                    }

                    if ($formoptions->departmentid) {
                        $deptarray[0] = 0;
                        $deptarray[1] = $formoptions->departmentid;
                        $deptarray = implode(',', $deptarray);
                        $groupssql .= " AND lg.departmentid IN ($deptarray)";
                    }
                    if ($formoptions->subdepartment) {
                        $subdeptarray[0] = 0;
                        $subdeptarray[1] = $formoptions->subdepartment;
                        $subdeptarray = implode(',', $subdeptarray);
                        $groupssql .= " AND lg.subdepartmentid IN ($subdeptarray)";
                    }
                    $groups = $DB->get_records_sql($fields.$groupssql, $sqlparams, ($page * $perpage) - 0, $perpage + 1);
                    if ($groups) {
                        $totalaccounts = count($groups);
                        $moreaccounts = $totalaccounts > $perpage;

                        if ($moreaccounts) {
                            // We need to discard the last record.
                            array_pop($groups);
                        }
                    }
                    $return = array_values(json_decode(json_encode(($groups)), true));
                break;
                case 'costcenter_curriculum_selector' :
                    $fields = array("name");
                    $sqlparams['organisationid'] = $formoptions->organisationid;
                    $likesql = array();
                    $i = 0;
                    foreach ($fields as $field) {
                        $i++;
                        $likesql[] = $DB->sql_like($field, ":queryparam$i", false);
                        $sqlparams["queryparam$i"] = "%$query%";
                    }
                    $sqlfields = implode(" OR ", $likesql);
                    $concatsql .= " AND ($sqlfields) ";
                    $fields      = "SELECT lc.id,lc.name as fullname";
                    $curriculumssql = " FROM {local_curriculum} lc
                                        WHERE lc.visible = 1 $concatsql
                                        AND lc.costcenter IN (:organisationid)";
                    if(!$formoptions->departmentid){
                        $curriculumssql .= " AND lc.open_departmentid = 0";
                    }
                    if(!$formoptions->subdepartment){
                        $curriculumssql .= " AND lc.open_subdepartment = 0";
                    }
                    if ($formoptions->departmentid) {
                        $deptarray[0] = 0;
                        $deptarray[1] = $formoptions->departmentid;
                        $deptarray = implode(',', $deptarray);
                        $curriculumssql .= " AND lc.open_departmentid IN ($deptarray)";
                    }
                    if ($formoptions->subdepartment) {
                        $subdeptarray[0] = 0;
                        $subdeptarray[1] = $formoptions->subdepartment;
                        $subdeptarray = implode(',', $subdeptarray);
                        $curriculumssql .= " AND lc.open_subdepartment IN ($subdeptarray)";
                    }
                    $curriculums = $DB->get_records_sql($fields.$curriculumssql, $sqlparams, ($page * $perpage) - 0, $perpage + 1);
                    if ($curriculums) {
                        $totalaccounts = count($curriculums);
                        $moreaccounts = $totalaccounts > $perpage;

                        if ($moreaccounts) {
                            // We need to discard the last record.
                            array_pop($curriculums);
                        }
                    }
                    $return = array_values(json_decode(json_encode(($curriculums)), true));
                break;
                case 'costcenter_courses_selector' :
                    $fields = array("c.fullname"/*, "shortname"*/);
                    $sqlparams['departmentid'] = $formoptions->departmentid;
                    $sqlparams['subdepartment'] = $formoptions->subdepartment;
                    $sqlparams['curriculumid'] = $formoptions->id;
                    $sqlparams['currid'] = $formoptions->id;
                    $sqlparams['open_identifiedas'] = 3;
                    $sqlparams['visible'] = 1;
                    $likesql = array();
                    $i = 0;
                    foreach ($fields as $field) {
                        $i++;
                        $likesql[] = $DB->sql_like($field, ":queryparam$i", false);
                        $sqlparams["queryparam$i"] = "%$query%";
                    }
                    $sqlfields = implode(" OR ", $likesql);
                    $concatsql .= " AND ($sqlfields) ";
                    $fields      = "SELECT c.id, c.fullname";
                    $coursessql = " FROM {course} c ";
                    $coursessql .= " JOIN {local_costcenter} lcost ON c.open_costcenterid = lcost.id
                                        JOIN {local_curriculum} lc ON lcost.id = lc.costcenter ";
                    if ($formoptions->id) {
                        $coursessql .= " WHERE c.id NOT IN (
                                            SELECT cc.courseid 
                                                FROM {local_cc_semester_courses} cc WHERE cc.curriculumid IN (:currid)) $concatsql";
                    }
                    if ($formoptions->departmentid) {
                        $coursessql .= " AND c.open_departmentid IN (:departmentid)";
                    }
                    if ($formoptions->subdepartment) {
                        $coursessql .= " AND c.open_subdepartment IN (:subdepartment)";
                    }
                    if ($formoptions->id) {
                        $coursessql .= " AND lc.id IN (:curriculumid) AND c.open_identifiedas IN (:open_identifiedas) AND c.visible = :visible";
                    }
                    $accounts = $DB->get_records_sql($fields.$coursessql, $sqlparams, ($page * $perpage) - 0, $perpage + 1);
                    if ($accounts) {
                        $totalaccounts = count($accounts);
                        $moreaccounts = $totalaccounts > $perpage;

                        if ($moreaccounts) {
                            // We need to discard the last record.
                            array_pop($accounts);
                        }
                    }
                    $return = array_values(json_decode(json_encode(($accounts)), true));
                break;
            }
        }
        return json_encode($return);
    }
    public static function form_option_selector_returns() {
        return new external_value(PARAM_RAW, 'data');
    }
}
