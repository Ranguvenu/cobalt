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
 * @package BizLMS
 * @subpackage local_location
 */


defined('MOODLE_INTERNAL') or die;
/*
 *  @method institute output fragment
 *  @param $args
 */
function local_location_output_fragment_new_instituteform($args) {
	global $CFG, $DB;

	$args = (object) $args;
	$context = $args->context;
	$instituteid = $args->instituteid;
	$o = '';
	$formdata = [];
	if (!empty($args->jsonformdata)) {
		$serialiseddata = json_decode($args->jsonformdata);
		parse_str($serialiseddata, $formdata);
	}

	if ($args->instituteid > 0) {
		$heading = get_string('update_institute','local_location');
		$collapse = false;
		$data = $DB->get_record('local_location_institutes', array('id' => $instituteid));
        $data->open_costcenterid = $data->costcenter;
	}
	$editoroptions = [
		'maxfiles' => EDITOR_UNLIMITED_FILES,
		'maxbytes' => $course->maxbytes,
		'trust' => false,
		'context' => $context,
		'noclean' => true,
		'subdirs' => false,
	];
	$group = file_prepare_standard_editor($group, 'description', $editoroptions, $context, 'group', 'description', null);

	$mform = new local_location\form\instituteform(null, array('editoroptions' => $editoroptions, 'id' => $instituteid), 'post', '', null, true, $formdata);

	$mform->set_data($data);

	if (!empty($formdata)) {
		// If we were passed non-empty form data we want the mform to call validation functions and show errors.
		$mform->is_validated();
	}

	ob_start();
	$mform->display();
	$o .= ob_get_contents();
	ob_end_clean();
	return $o;
}
/*
 *  @method room output fragment
 *  @param $args
 */
function local_location_output_fragment_new_roomform($args) {
	global $CFG, $DB;

	$args = (object) $args;
	$context = $args->context;
	$roomid = $args->roomid;
	$o = '';
	$formdata = [];
	if (!empty($args->jsonformdata)) {
		$serialiseddata = json_decode($args->jsonformdata);
		parse_str($serialiseddata, $formdata);
	}

	if ($args->roomid > 0) {
		$heading = get_string('update_room','local_location');
		$collapse = false;
		$data = $DB->get_record('local_location_room', array('id' => $roomid));
	}
	$editoroptions = [
		'maxfiles' => EDITOR_UNLIMITED_FILES,
		'maxbytes' => $course->maxbytes,
		'trust' => false,
		'context' => $context,
		'noclean' => true,
		'subdirs' => false,
	];
	$group = file_prepare_standard_editor($group, 'description', $editoroptions, $context, 'group', 'description', null);

	$mform = new local_location\form\roomform(null, array('editoroptions' => $editoroptions), 'post', '', null, true, $formdata);

	$mform->set_data($data);

	if (!empty($formdata)) {
		// If we were passed non-empty form data we want the mform to call validation functions and show errors.
		$mform->is_validated();
	}

	ob_start();
	$mform->display();
	$o .= ob_get_contents();
	ob_end_clean();
	return $o;
}

function listof_locations($stable, $filterdata) {
	global $DB, $USER, $CFG;
	$systemcontext = context_system::instance();

	$params = array();
	$countsql = "SELECT count(*)";
	$sql = 'SELECT * ';
	$formsql = 'FROM {local_location_institutes} ';
	$formsql .= 'WHERE 1=1 ';

    if (is_siteadmin() || has_capability('local/costcenter:manage_multiorganizations', $systemcontext) ) {
        $params['visible'] = 1;
        $formsql .= ' AND visible = :visible ';
    } else if (has_capability('local/costcenter:manage_ownorganization', $systemcontext)) {
        $formsql .= " AND costcenter IN ($USER->open_costcenterid)";
    } else if (has_capability('local/costcenter:manage_owndepartments', $systemcontext)) {
        $formsql .= " AND open_departmentid IN ($USER->open_departmentid)";
    } else if (has_capability('local/costcenter:manage_ownsubdepartments', $systemcontext)) {
        $formsql .= " AND open_subdepartment IN ($USER->open_subdepartment)";
    }

    // Global search filter.
    if(isset($filterdata->search_query) && trim($filterdata->search_query) != ''){
        $formsql .= " AND (fullname LIKE :search1 OR institute_type LIKE :search2 OR address LIKE :search3 )";
        $params['search1'] = '%'.trim($filterdata->search_query).'%';
        $params['search2'] = '%'.trim($filterdata->search_query).'%';
        $params['search3'] = '%'.trim($filterdata->search_query).'%';
    }

    // Organization filter.
    $labelstring = get_config('local_costcenter');
    $firstlevel = $labelstring->firstlevel;
    $secondlevel = $labelstring->secondlevel;
    $thirdlevel = $labelstring->thirdlevel;

    if(!empty($filterdata->$firstlevel)){
        $organizations = explode(',',$filterdata->$firstlevel);
        $organizations = array_filter($organizations, function($value){
            if($value != '_qf__force_multiselect_submission'){
                return $value;
            }
        });
        if($organizations != NULL) {
            list($relatedeorganizationssql, $relatedorganizationsparams) = $DB->get_in_or_equal($organizations, SQL_PARAMS_NAMED, 'organizations');
            $params = array_merge($params, $relatedorganizationsparams);
            $formsql .= " AND costcenter {$relatedeorganizationssql}";
        }
    }

    // Department filter.
    if(!empty($filterdata->$secondlevel)){
        $departments = explode(',',$filterdata->$secondlevel);
        $departments = array_filter($departments, function($value){
            if($value != '_qf__force_multiselect_submission'){
                return $value;
            }
        });
        if($departments != NULL) {
            list($relatededepartmentssql, $relateddepartmentsparams) = $DB->get_in_or_equal($departments, SQL_PARAMS_NAMED, 'departments');
            $params = array_merge($params, $relateddepartmentsparams);
            $formsql .= " AND open_departmentid {$relatededepartmentssql}";
        }
    }

    // Subdepartment filter.
    if(!empty($filterdata->$thirdlevel)){
        $subdepartment = explode(',',$filterdata->$thirdlevel);
        $subdepartment = array_filter($subdepartment, function($value){
            if($value != '_qf__force_multiselect_submission'){
                return $value;
            }
        });
        if($subdepartment != NULL) {
            list($relatedesubdepartmentsql, $relatedsubdepartmentparams) = $DB->get_in_or_equal($subdepartment, SQL_PARAMS_NAMED, 'subdepartment');
            $params = array_merge($params, $relatedsubdepartmentparams);
            $formsql .= " AND open_subdepartment {$relatedesubdepartmentsql}";
        }
    }

    // Location filter.
    if (!empty($filterdata->location_name)) {
        $locations = explode(',', $filterdata->location_name);
        $locations = array_filter($locations, function($value){
            if($value != '_qf__force_multiselect_submission'){
                return $value;
            }
        });
        if($locations != NULL) {
            list($locationsql, $locationparams) = $DB->get_in_or_equal($locations, SQL_PARAMS_NAMED, 'location');
            $params = array_merge($params, $locationparams);            
            $formsql .= " AND id {$locationsql} ";
        }
    }

    // Location_type filter.
    if (!empty($filterdata->location_type)) {
        $locationtype = explode(',', $filterdata->location_type);
        $locationtype = array_filter($locationtype, function($value){
            if($value != '_qf__force_multiselect_submission'){
                return $value;
            }
        });
        if($locationtype != NULL) {
            list($locationtypeql, $locationtypeparams) = $DB->get_in_or_equal($locationtype, SQL_PARAMS_NAMED, 'location');
            $params = array_merge($params, $locationtypeparams);            
            $formsql .= " AND institute_type {$locationtypeql} ";
        }
    }

    $order .= ' ORDER BY id DESC ';
	$institutes = $DB->get_records_sql($sql.$formsql.$order, $params, $stable->start, $stable->length);
	$count = $DB->count_records_sql($countsql.$formsql, $params);

	$line = array();
	foreach($institutes as $key => $value) {
		$data = array();
        $costcenter = $DB->get_field('local_costcenter', 'fullname', ['id' => $value->costcenter]);
		$data['id'] = $value->id;
		$data['fullname'] = $value->fullname;
        $data['costcenter'] = $costcenter;
		$data['address'] = $value->address;
		if ($value->institute_type == 1) {
			$type = 'Internal';
		} else if ($value->institute_type == 2) {
			$type = 'External';
		}
		$locationexists = $DB->get_records('attendance_sessions', ['building' => $value->id]);
        if ($locationexists) {
            $recordexist = true;
        } else {
            $recordexist = false;
        } 
		$data['institute_type'] = $type;
		$data['recordexist'] = $recordexist;
        $data['siteadmin'] = is_siteadmin();

		$line[] = $data;
	}

	$records = [
		'haslocations' => $line,
		'count' => $count,
		'length' => $count
	];

	return $records;
}

function listof_rooms($stable, $filterdata) {
    global $DB, $CFG, $OUTPUT,$USER, $PAGE;
    $params = array();
    $systemcontext = context_system::instance();
    $countsql = "SELECT count(lcr.id), lci.fullname "; 
    $sql = "SELECT lcr.*, lci.fullname ";
    $formsql = "FROM {local_location_room} lcr
                JOIN {local_location_institutes} lci ON lci.id = lcr.instituteid ";
    $formsql .= "WHERE 1=1 ";

    if (is_siteadmin() || has_capability('local/costcenter:manage_multiorganizations', $systemcontext) ) {
        $params['visible'] = 1;
        $formsql .= " AND lci.visible = :visible ";
    } else if (has_capability('local/costcenter:manage_ownorganization', $systemcontext)) {
        $formsql .= " AND lci.costcenter IN ($USER->open_costcenterid)";
    } else if (has_capability('local/costcenter:manage_owndepartments', $systemcontext)) {
        $formsql .= " AND lci.open_departmentid IN ($USER->open_departmentid)";
    } else if (has_capability('local/costcenter:manage_ownsubdepartments', $systemcontext)) {
        $formsql .= " AND lci.open_subdepartment IN ($USER->open_subdepartment)";
    }

    // Global search filter.
    if(isset($filterdata->search_query) && trim($filterdata->search_query) != ''){
        $formsql .= " AND (lcr.name LIKE :search1 OR lcr.building LIKE :search2 OR lcr.address LIKE :search3 OR lcr.capacity LIKE :search4 OR lci.fullname LIKE :search5 )";
        $params['search1'] = '%'.trim($filterdata->search_query).'%';
        $params['search2'] = '%'.trim($filterdata->search_query).'%';
        $params['search3'] = '%'.trim($filterdata->search_query).'%';
        $params['search4'] = '%'.trim($filterdata->search_query).'%';
        $params['search5'] = '%'.trim($filterdata->search_query).'%';
    }

    $orderby = " ORDER BY lcr.id DESC ";
    $rooms = $DB->get_records_sql($sql.$formsql.$orderby, $params, $stable->start, $stable->length);
    $count = $DB->count_records_sql($countsql.$formsql, $params);

    $list = array();
    foreach ($rooms as $key => $room) {
        $data = array();

        $location = $DB->get_field('local_location_institutes', 'fullname', ['id' => $room->instituteid]);
        if ($room->capacity > 0) {
            $capacity = $room->capacity;
        } else {
            $capacity = get_string('na', 'local_location');
        }
        
        $data['id'] = $room->id;
        $data['building'] = $room->building;
        $data['room'] = $room->name;
        $data['capacity'] = $capacity;
        $data['location'] = $location;

        $roomexists = $DB->get_records('attendance_sessions', ['room' => $room->id]);
        if ($roomexists) {
            $recordexist = true;
        } else {
            $recordexist = false;
        }
        $data['recordexist'] = $recordexist;

        $list[] = $data;
    }

    $records = [
        'hasrooms' => $list,
        'count' => $count,
        'length' => $count
    ];

    return $records;
}

/**
 * Description: User email filter code
 * @param  [mform object]  $mform[the form object where the form is initiated]
 * @param  string  $query          [text inserted in filter]
 * @param  boolean $searchanywhere [description]
 * @param  integer $page           [page value]
 * @param  integer $perpage        [entities per page]
 */
function location_name_filter($mform, $query='', $searchanywhere=false, $page=0, $perpage=25) {
    global $DB, $USER;
    $systemcontext = context_system::instance();
    $userslist = array();
    $data = data_submitted();
    $userslistparams = array('visible' => 1);
    $location_sql = "SELECT l.id, l.fullname
                      FROM {local_location_institutes} l";
    $concatsql = " WHERE 1=1 ";

    if (is_siteadmin() || has_capability('local/costcenter:manage_multiorganizations', $systemcontext) ) {
        $concatsql .= " AND l.visible = :visible ";
    } else if (has_capability('local/costcenter:manage_ownorganization', $systemcontext)) {
        $concatsql .= " AND l.costcenter IN ($USER->open_costcenterid)";
    } else if (has_capability('local/costcenter:manage_owndepartments', $systemcontext)) {
        $concatsql .= " AND l.open_departmentid IN ($USER->open_departmentid)";
    } else if (has_capability('local/costcenter:manage_ownsubdepartments', $systemcontext)) {
        $concatsql .= " AND l.open_subdepartment IN ($USER->open_subdepartment)";
    }

    if(!empty($query)){
        if ($searchanywhere) {
            $likesql = $DB->sql_like('l.fullname', "'$query%'", false);
            $concatsql .= " AND ($likesql) ";
        } else {
            $likesql = $DB->sql_like('l.fullname', "'$query%'", false);
            $concatsql .= " AND ($likesql) ";
        }
    }

    if (!empty($query) || empty($mform)) {
        $userslist = $DB->get_records_sql($location_sql.$concatsql, $userslistparams, $page, $perpage);
        return $userslist;
    }
    $options = array(
        'ajax' => 'local_courses/form-options-selector',
        'multiple' => true,
        'data-action' => 'location_name',
        'data-options' => json_encode(array('id' => 0)),
        'placeholder' => get_string('location_name', 'local_location')
    );
    $mform->addElement('autocomplete', 'location_name', '', $userslist, $options);
    $mform->setType('location_name', PARAM_RAW);
}

/**
 * Description: User email filter code
 * @param  [mform object]  $mform[the form object where the form is initiated]
 * @param  string  $query          [text inserted in filter]
 * @param  boolean $searchanywhere [description]
 * @param  integer $page           [page value]
 * @param  integer $perpage        [entities per page]
 */
function location_type_filter($mform, $query='', $searchanywhere=false, $page=0, $perpage=25) {
    global $DB, $USER;
    $type = array(null => '', 1 => get_string('internal', 'local_location'), 2 => get_string('external', 'local_location'));

    $options = array(
        'placeholder' => get_string('location_type', 'local_location')
    );
    
    $mform->addElement('autocomplete', 'location_type', '', $type, $options);
    $mform->setType('location_type', PARAM_RAW);
}
