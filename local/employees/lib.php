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
 * @subpackage local_employees
 */

use local_employees\output\team_status_lib;

defined('MOODLE_INTERNAL') || die;
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot.'/user/editlib.php');
/**
 * Description: To display the form in modal on modal trigger event.
 * @param  [array] $args [the parameters required for the form]
 * @return        [modal content]
 */
function local_employees_output_fragment_new_create_employees($args){
    global $CFG,$DB, $PAGE;
    $args = (object) $args;
    $context = $args->context;
    $o = '';
    $formdata = [];
    if (!empty($args->jsonformdata)) {
        $serialiseddata = json_decode($args->jsonformdata);
        parse_str($serialiseddata, $formdata);
    }
    $editoroptions = [
        'maxfiles' => EDITOR_UNLIMITED_FILES,
        'maxbytes' => $course->maxbytes,
        'trust' => false,
        'context' => $context,
        'noclean' => true,
        'subdirs' => false,'autosave'=>false
    ];
    $group = file_prepare_standard_editor($group, 'description', $editoroptions, $context, 'group', 'description', null);
    if ($args->id > 0) {
        $heading = 'Update User';
        $collapse = false;
        $data = $DB->get_record('user', array('id'=>$args->id));
        if($args->form_status==2){
            $data = $DB->get_record('user', array('id'=>$args->id));
            //$description= $data->description;
            //unset($data->description);
            //$data->description['text'] =$description;
        }
            $description = $data->description;
            $data->description = array();
            $data->description['text'] =$description;
        unset($data->password);
        useredit_load_preferences($data);
        $res = $DB->get_record('role_assignments',array('userid'=>$data->id));
           $rolename = $DB->get_record('role',array('id'=>$res->roleid));
           
        $mform = new local_employees\forms\create_employees(null, array('editoroptions' => $editoroptions,'form_status' => $args->form_status,'id' => $data->id,'open_costcenterid'=>$data->open_costcenterid,'subdept'=>$data->open_subdepartment,'roleid'=>$rolename->shortname,'deptid'=>$categoryvalue), 'post', '', null, true, $formdata);
        $mform->set_data($data);
    }
    else {
        $mform = new local_employees\forms\create_employees(null, array('editoroptions' => $editoroptions,'form_status' => $args->form_status), 'post', '', null, true, $formdata);

    }

    if (!empty($args->jsonformdata) && strlen($args->jsonformdata) >2) {
        // If we were passed non-empty form data we want the mform to call validation functions and show errors.
        $mform->is_validated();
    }
    $formheaders = array_keys($mform->formstatus);
    $nextform = array_key_exists($args->form_status, $formheaders);
    if ($nextform === false) {
        return false;
    }
    $renderer = $PAGE->get_renderer('local_employees');
    ob_start();
    $formstatus = array();
    foreach (array_values($mform->formstatus) as $k => $mformstatus) {
        $activeclass = $k == $args->form_status ? 'active' : '';
        $formstatus[] = array('name' => $mformstatus, 'activeclass' => $activeclass);
    }
    $formstatusview = new \local_employees\output\form_status($formstatus);
    $o .= $renderer->render($formstatusview);
    $mform->display();
    $o .= ob_get_contents();
    ob_end_clean();
    return $o;
}
/**
 * Description: User fullname filter code
 * @param  [mform object]  $mform[the form object where the form is initiated]
 * @param  string  $query          [text inserted in filter]
 * @param  boolean $searchanywhere [description]
 * @param  integer $page           [page value]
 * @param  integer $perpage        [entities per page]
 */
function employees_filter($mform,$query='',$searchanywhere=false, $page=0, $perpage=25){
    global $DB, $USER;

    $systemcontext = context_system::instance();
    $userslist=array();
    $data=data_submitted();
    $userslistparams = array('adminuserid' => 2, 'deleted' => 0, 'suspended' => 0, 'userid' => $USER->id);
    if(is_siteadmin() || has_capability('local/costcenter:manage_multiorganizations', $systemcontext)){
        $userslist_sql = "SELECT id, 
                concat(firstname,' ',lastname) as fullname 
                FROM {user} WHERE id > :adminuserid 
                AND deleted = :deleted AND suspended = :suspended 
                AND id <> :userid  ";
    } else if (has_capability('local/costcenter:manage_ownorganization', $systemcontext)) {
        $userslist_sql="SELECT id, concat(firstname,' ',lastname) as fullname FROM {user} WHERE id > :adminuserid AND open_costcenterid = :costcenterid AND deleted = :deleted AND suspended = :suspended AND id <> :userid  ";
        $userslistparams['costcenterid'] = $USER->open_costcenterid;
    } else if (has_capability('local/costcenter:manage_owndepartments', $systemcontext)) {
        $userslist_sql="SELECT id,
                concat(firstname,' ',lastname) as fullname
                FROM {user} WHERE id > :adminuserid
                AND open_costcenterid = :costcenterid
                AND open_departmentid = :departmentid
                AND deleted = :deleted
                AND suspended = :suspended
                AND id <> :userid ";
        $userslistparams['costcenterid'] = $USER->open_costcenterid;
        $userslistparams['departmentid'] = $USER->open_departmentid;
    }
    if (!empty($query)) {
        if ($searchanywhere) {
            $likesql = $DB->sql_like("CONCAT(firstname, ' ',lastname)", "'%$query%'", false);
            $userslist_sql .= " AND $likesql ";
        } else {
            $likesql = $DB->sql_like("CONCAT(firstname, ' ',lastname)", "'$query%'", false);
            $userslist_sql .= " AND $likesql ";
        }
    }
    if (isset($data->users)&&!empty(($data->users))) {

        list($usersql, $userparam) = $DB->get_in_or_equal($data->users, SQL_PARAMS_NAMED);
        $userslist_sql .= " AND id $usersql ";
        $userslistparams = $userparam + $userslistparams;
    }
    if (!empty($query)||empty($mform)) {
        $userslist = $DB->get_records_sql($userslist_sql, $userslistparams, $page, $perpage);
        return $userslist;
    }
    if ((isset($data->users)&&!empty($data->users))) {
         $userslist = $DB->get_records_sql_menu($userslist_sql, $userslistparams, $page, $perpage);
    }
    $options = array(
                    'ajax' => 'local_courses/form-options-selector',
                    'multiple' => true,
                    'data-action' => 'users',
                    'data-options' => json_encode(array('id' => 0)),
                    'placeholder' => get_string('users')
    );
    $select = $mform->addElement('autocomplete', 'users', '', $userslist, $options);
    $mform->setType('users', PARAM_RAW);
}
/**
 * Description: User email filter code
 * @param  [mform object]  $mform[the form object where the form is initiated]
 * @param  string  $query          [text inserted in filter]
 * @param  boolean $searchanywhere [description]
 * @param  integer $page           [page value]
 * @param  integer $perpage        [entities per page]
 */
function empemail_filters($mform, $query='', $searchanywhere=false, $page=0, $perpage=25) {
    global $DB, $USER;
    $systemcontext = context_system::instance();
    $userslist=array();
    $data=data_submitted();
    $userslistparams = array('adminuserid' => 2, 'deleted' => 0, 'userid' => $USER->id, 'open_type' => 0, 'suspended'=>0);
    if (is_siteadmin() || has_capability('local/costcenter:manage_multiorganizations', $systemcontext)) {
        $userslist_sql="SELECT id, email as fullname
                        FROM {user} WHERE id > :adminuserid
                        AND deleted = :deleted
                        AND id <> :userid
                        AND open_type = :open_type 
                        AND suspended = :suspended";
    } else if (!is_siteadmin() && has_capability('local/costcenter:manage_ownorganization', $systemcontext)) {

        $collegeadminsql = "SELECT u.id FROM {user} u 
                    JOIN {role_assignments} ra ON ra.userid = u.id 
                    JOIN {role} r ON r.id = ra.roleid 
                    WHERE r.shortname = 'orgadmin' AND u.suspended <> 1
                    AND u.open_costcenterid = :costcenter";
        $collegeadmins = $DB->get_fieldset_sql($collegeadminsql, ['costcenter' => $USER->open_costcenterid]);
        
        $userslist_sql="SELECT id, email as fullname
                        FROM {user} WHERE id > :adminuserid
                        AND open_costcenterid = :costcenterid
                        AND deleted = :deleted
                        AND open_type = :open_type
                        AND id <> :userid
                        AND suspended = :suspended";
        if (count($collegeadmins) > 0) {
            $collegeadminsids = implode(',', $collegeadmins);
            $userslist_sql .= " AND id NOT IN($collegeadminsids) ";
        }
        $userslistparams['costcenterid'] = $USER->open_costcenterid;
    } else if (!is_siteadmin() && has_capability('local/costcenter:manage_owndepartments', $systemcontext)) {
        $orgadminsql = "SELECT u.id FROM {user} u 
                    JOIN {role_assignments} ra ON ra.userid = u.id 
                    JOIN {role} r ON r.id = ra.roleid 
                    WHERE r.shortname = 'orgadmin' OR r.shortname = 'collegeadmin' AND u.suspended <> 1
                    AND u.open_costcenterid = :costcenter";
        $orgadmins = $DB->get_fieldset_sql($orgadminsql, ['costcenter' => $USER->open_costcenterid]);

        $userslist_sql="SELECT id, email as fullname
                            FROM {user} WHERE id > :adminuserid
                            AND open_costcenterid = :costcenterid
                            AND open_departmentid = :departmentid
                            AND deleted = :deleted
                            AND open_type = :open_type
                            AND id <> :userid
                            AND suspended = :suspended";

        if (count($orgadmins) > 0) {
            $orgadminsids = implode(',', $orgadmins);
            $userslist_sql .= " AND id NOT IN($orgadminsids) ";
        }
        $userslistparams['costcenterid'] = $USER->open_costcenterid;
        $userslistparams['departmentid'] = $USER->open_departmentid;
    } else if (!is_siteadmin() && has_capability('local/costcenter:manage_ownsubdepartments', $systemcontext)) {
        $orgadminsql = "SELECT u.id FROM {user} u 
                    JOIN {role_assignments} ra ON ra.userid = u.id 
                    JOIN {role} r ON r.id = ra.roleid 
                    WHERE r.shortname = 'orgadmin' OR r.shortname = 'collegeadmin' OR r.shortname = 'departmentadmin' AND u.suspended <> 1
                    AND u.open_costcenterid = :costcenter";
        $orgadmins = $DB->get_fieldset_sql($orgadminsql, ['costcenter' => $USER->open_costcenterid]);

        $userslist_sql="SELECT id, email as fullname
                            FROM {user} WHERE id > :adminuserid
                            AND open_costcenterid = :costcenterid
                            AND open_departmentid = :departmentid
                            AND open_subdepartment = :subdepartment
                            AND deleted = :deleted
                            AND open_type = :open_type
                            AND id <> :userid
                            AND suspended = :suspended";

        if (count($orgadmins) > 0) {
            $orgadminsids = implode(',', $orgadmins);
            $userslist_sql .= " AND id NOT IN($orgadminsids) ";
        }
        $userslistparams['costcenterid'] = $USER->open_costcenterid;
        $userslistparams['departmentid'] = $USER->open_departmentid;
        $userslistparams['subdepartment'] = $USER->open_subdepartment;
    }
    if(!empty($query)){
        if ($searchanywhere) {
            $likesql = $DB->sql_like('email', "'%$query%'", false);
            $userslist_sql .= " AND $likesql ";
        } else {
            $likesql = $DB->sql_like('email', "'$query%'", false);
            $userslist_sql .= " AND $likesql ";
        }
    }
    if (isset($data->email)&&!empty(($data->email))) {
        $implode=implode(',',$data->email);
        list($mailsql, $mailparam) = $DB->get_in_or_equal($data->email, SQL_PARAMS_NAMED);
        $userslist_sql .= " AND id $mailsql ";
        $userslistparams = $mailparam + $userslistparams;
    }
    if (!empty($query)||empty($mform)) {
        $userslist = $DB->get_records_sql($userslist_sql, $userslistparams, $page, $perpage);
        return $userslist;
    }
    if((isset($data->email)&&!empty($data->email))){
        $userslist = $DB->get_records_sql_menu($userslist_sql, $userslistparams, $page, $perpage);
    }
    $options = array(
        'ajax' => 'local_courses/form-options-selector',
        'multiple' => true,
        'data-action' => 'empemail',
        'data-options' => json_encode(array('id' => 0)),
        'placeholder' => get_string('email')
    );
    $select = $mform->addElement('autocomplete', 'email', '',$userslist,$options);
    $mform->setType('email', PARAM_RAW);
}

/**
 * Description: User designation filter code
 * @param  [mform object]  $mform [the form object where the form is initiated]
 */
function role_filters($mform) {
    global $DB, $USER;
    $systemcontext = context_system::instance();
    if(is_siteadmin() || has_capability('local/costcenter:manage_multiorganizations', $systemcontext)){
        $userslist_sql = "SELECT id,name FROM {role} WHERE shortname = 'editingteacher' OR shortname = 'orgadmin' OR shortname = 'collegeadmin' OR shortname = 'departmentadmin'";
    } else if (has_capability('local/costcenter:manage_ownorganization', $systemcontext)) {
        $userslist_sql = "SELECT id,name FROM {role} WHERE shortname = 'editingteacher' /*OR shortname = 'orgadmin'*/ OR shortname = 'collegeadmin' OR shortname = 'departmentadmin'";
    }else if(has_capability('local/costcenter:manage_owndepartments', $systemcontext)){
        $userslist_sql = "SELECT id,name FROM {role} WHERE shortname = 'editingteacher' OR shortname = 'departmentadmin'";
    }else if(has_capability('local/costcenter:manage_ownsubdepartments', $systemcontext)){
        $userslist_sql = "SELECT id,name FROM {role} WHERE shortname = 'editingteacher'";
    }
    $userslist = $DB->get_records_sql_menu($userslist_sql/*, $userslistparams*/);
    $select = $mform->addElement('autocomplete', 'role', '', $userslist, array('placeholder' => get_string('employeerole','local_employees')));
    $mform->setType('idnumber', PARAM_RAW);
    $select->setMultiple(true);
}

/**
 * Description: User employeeid filter code
 * @param  [mform object]  $mform          [the form object where the form is initiated]
 * @param  string  $query          [text inserted in filter]
 * @param  boolean $searchanywhere [description]
 * @param  integer $page           [page value]
 * @param  integer $perpage        [entities per page]
 */
function employeesid_filter($mform,$query='',$searchanywhere=false, $page=0, $perpage=25) {
    global $DB, $USER;
    $systemcontext = context_system::instance();
    $userslist=array();
    $data=data_submitted();
    $userslistparams = array('adminuserid' => 2, 'deleted' => 0, 'suspended' => 0, 'userid' => $USER->id);
    if (is_siteadmin() || has_capability('local/costcenter:manage_multiorganizations', $systemcontext)) {
        $userslist_sql="SELECT id, open_employeeid as fullname
                    FROM {user} WHERE id > :adminuserid
                    AND deleted = :deleted
                    AND suspended = :suspended
                    AND id <> :userid";
    } else if (has_capability('local/costcenter:manage_ownorganization', $systemcontext)) {
        $userslist_sql="SELECT id, open_employeeid as fullname
                        FROM {user} WHERE id > :adminuserid
                        AND open_costcenterid = :costcenterid
                        AND deleted = :deleted
                        AND suspended = :suspended
                        AND id <> :userid";
        $userslistparams['costcenterid'] = $USER->open_costcenterid;
    } else if (has_capability('local/costcenter:manage_owndepartments', $systemcontext)) {
        $userslist_sql="SELECT id, open_employeeid as fullname
                        FROM {user} WHERE id > :adminuserid
                        AND open_costcenterid = :costcenterid
                        AND open_departmentid = :departmentid
                        AND deleted = :deleted
                        AND suspended = :suspended
                        AND id <> :userid";
        $userslistparams['costcenterid'] = $USER->open_costcenterid;
        $userslistparams['departmentid'] = $USER->open_departmentid;
    }
    if(!empty($query)){
        if ($searchanywhere) {
            $likesql = $DB->sql_like('open_employeeid', "'%$query%'", false);
            $userslist_sql.=" AND $likesql ";
        } else {
            $likesql = $DB->sql_like('open_employeeid', "'$query%'", false);
            $userslist_sql.=" AND $likesql ";
        }
    }
    if (isset($data->idnumber)&&!empty(($data->idnumber))) {
        list($idsql, $idparam) = $DB->get_in_or_equal($data->idnumber, SQL_PARAMS_NAMED);
        $userslist_sql .= " AND id $idsql ";
        $userslistparams = $idparam + $userslistparams;
    }
    if (!empty($query)||empty($mform)) {
        $userslist = $DB->get_records_sql($userslist_sql, $userslistparams, $page, $perpage);
        return $userslist;
    }
    if ((isset($data->idnumber)&&!empty($data->idnumber))) {
        $userslist = $DB->get_records_sql_menu($userslist_sql,$userslistparams);
    }
    $options = array(
        'ajax' => 'local_courses/form-options-selector',
        'multiple' => true,
        'data-action' => 'employeeid',
        'data-options' => json_encode(array('id' => 0)),
        'placeholder' => get_string('idnumber','local_employees')
    );
    $select = $mform->addElement('autocomplete', 'idnumber', '',$userslist,$options);
    $mform->setType('idnumber', PARAM_RAW);
}

/**
 * Description: User designation filter code
 * @param  [mform object]  $mform [the form object where the form is initiated]
 */
function designations_filter($mform) {
    global $DB, $USER;
    $systemcontext = context_system::instance();
    $userslistparams = array('adminuserid' => 2, 'deleted' => 0, 'suspended' => 0, 'userid' => $USER->id);
    if(is_siteadmin() || has_capability('local/costcenter:manage_multiorganizations', $systemcontext)){
        $userslist_sql = "SELECT id FROM {user}
                        WHERE id > :adminuserid
                        AND deleted = :deleted
                        AND suspended = :suspended
                        AND id <> :userid";
    } else if (has_capability('local/costcenter:manage_ownorganization', $systemcontext)) {
        $userslist_sql = "SELECT id FROM {user}
                        WHERE id > :adminuserid
                        AND open_costcenterid = :costcenterid
                        AND deleted = :deleted
                        AND suspended = :suspended
                        AND id <> :userid";
        $userslistparams['costcenterid'] = $USER->open_costcenterid;
    }else if(has_capability('local/costcenter:manage_owndepartments', $systemcontext)){
        $userslist_sql = "SELECT id FROM {user} WHERE id > :adminuserid AND open_costcenterid = :costcenterid AND open_departmentid = :departmentid AND deleted = deleted AND suspended = :suspended AND id <> :userid";
        $userslistparams['costcenterid'] = $USER->open_costcenterid;
        $userslistparams['departmentid'] = $USER->open_departmentid;
    }
    $userslist = $DB->get_records_sql_menu($userslist_sql, $userslistparams);
    $select = $mform->addElement('autocomplete', 'designation', '', $userslist, array('placeholder' => get_string('designation','local_employees')));
    $mform->setType('idnumber', PARAM_RAW);
    $select->setMultiple(true);
}

/**
 * Description: User location filter code
 * @param  [mform object]  $mform [the form object where the form is initiated]
 */
function locations_filter($mform){
    global $DB, $USER;
    $systemcontext = context_system::instance();
    $userslistparams = array('adminuserid' => 2, 'deleted' => 0, 'suspended' => 0, 'userid' => $USER->id);
    if (is_siteadmin() || has_capability('local/costcenter:manage_multiorganizations', $systemcontext)) {
        $userslist_sql = "SELECT u.city, u.city AS name
                        FROM {user} AS u WHERE u.id > :adminuserid
                        AND u.deleted = :deleted
                        AND u.suspended = :suspended
                        AND u.id <> :userid ";
    } else if (has_capability('local/costcenter:manage_ownorganization', $systemcontext)) {
        $userslist_sql = "SELECT u.city, u.city AS name
                        FROM {user} AS u WHERE u.id > :adminuserid
                        AND u.open_costcenterid = :costcenterid
                        AND u.deleted = :deleted
                        AND u.suspended = :suspended
                        AND u.id <> :userid ";
        $userslistparams['costcenterid'] =  $USER->open_costcenterid;
    } else if (has_capability('local/costcenter:manage_owndepartments', $systemcontext)) {
        $userslist_sql = "SELECT u.city, u.city AS name
                        FROM {user} AS u WHERE u.id > :adminuserid
                        AND u.open_costcenterid = :costcenterid
                        AND u.open_departmentid = :departmentid
                        AND u.deleted = :deleted
                        AND u.suspended = :suspended
                        AND u.id <> :userid";
        $userslistparams['costcenterid'] =  $USER->open_costcenterid;
        $userslistparams['departmentid'] =  $USER->open_departmentid;
    } else if (has_capability('local/costcenter:manage_ownsubdepartments', $systemcontext)) {
        $userslist_sql = "SELECT u.city, u.city AS name
                        FROM {user} AS u WHERE u.id > :adminuserid
                        AND u.open_costcenterid = :costcenterid
                        AND u.open_departmentid = :departmentid
                        AND u.open_subdepartment = :open_subdepartment
                        AND u.deleted = :deleted
                        AND u.suspended = :suspended
                        AND u.id <> :userid";
        $userslistparams['costcenterid'] =  $USER->open_costcenterid;
        $userslistparams['departmentid'] =  $USER->open_departmentid;
        $userslistparams['subdepartment'] =  $USER->open_subdepartment;
    }
    $userslist_sql .= " AND u.city != '' AND u.city IS NOT NULL GROUP BY u.city ";
    $userslist = $DB->get_records_sql_menu($userslist_sql, $userslistparams);
    $select = $mform->addElement('autocomplete', 'location', '', $userslist, array('placeholder' => get_string('location', 'local_employees')));
    $mform->setType('idnumber', PARAM_RAW);
    $select->setMultiple(true);
}

/**
 * Description: User hrmsrole filter code
 * @param  [mform object]  $mform [the form object where the form is initiated]
 */
function hrmsroles_filter($mform) {
    global $DB, $USER;
    $systemcontext = context_system::instance();
    $userslistparams = array('visible' => 0);
        $userslist_sql = "SELECT id,name FROM {local_hrmsroles} 
                        WHERE visible = :visible ";
    $userslist = $DB->get_records_sql_menu($userslist_sql, $userslistparams);
    $select = $mform->addElement('autocomplete', 'hrmsrole', '', $userslist, array('placeholder' => get_string('open_hrmsrole', 'local_employees')));
    $mform->setType('hrmsrole', PARAM_RAW);
    $select->setMultiple(true);
}

/**
 * Description: User band filter code
 * @param  [mform object]  $mform [the form object where the form is initiated]
 */
function bands_filter($mform) {
    global $DB, $USER;
    $systemcontext = context_system::instance();
    $userslistparams = array('adminuserid' => 2, 'deleted' => 0, 'suspended' => 0, 'userid' => $USER->id);
    if (is_siteadmin() || has_capability('local/costcenter:manage_multiorganizations', $systemcontext)) {
        $userslist_sql = "SELECT id, open_band FROM {user}
                        WHERE id > :adminuserid
                        AND deleted = :deleted
                        AND suspended = :suspended
                        AND id <> :userid";
    } else if (has_capability('local/costcenter:manage_ownorganization', $systemcontext)) {
        $userslist_sql = "SELECT id, open_band FROM {user}
                        WHERE id > :adminuserid
                        AND open_costcenterid = :costcenterid
                        AND deleted = :deleted
                        AND suspended = :suspended
                        AND id <> :userid";
        $userslistparams['costcenterid'] = $USER->open_costcenterid;
    } else if (has_capability('local/costcenter:manage_owndepartments', $systemcontext)) {
        $userslist_sql = "SELECT id, open_band FROM {user}
                        WHERE id > :adminuserid
                        AND open_costcenterid = :costcenterid
                        AND open_departmentid = :departmentid
                        AND deleted = :deleted
                        AND suspended = :suspended
                        AND id <> :userid ";
        $userslistparams['costcenterid'] = $USER->open_costcenterid;
        $userslistparams['departmentid'] = $USER->open_departmentid;
    }
    $userslist = $DB->get_records_sql_menu($userslist_sql, $userslistparams);
    $select = $mform->addElement('autocomplete', 'band', '', $userslist, array('placeholder' => get_string('band', 'local_employees')));
    $mform->setType('idnumber', PARAM_RAW);
    $select->setMultiple(true);
}
/**
 * Description: User name filter code
 * @param  [mform object]  $mform [the form object where the form is initiated]
 */
function usernames_filter($mform) {
    global $DB, $USER;
    $systemcontext = context_system::instance();
    $userslistparams = array('adminuserid' => 2, 'deleted' => 0, 'suspended' => 0, 'userid' => $USER->id);
    if (is_siteadmin() || has_capability('local/costcenter:manage_multiorganizations', $systemcontext)) {
        $userslist_sql = "SELECT id, username FROM {user}
                        WHERE id > :adminuserid
                        AND deleted = :deleted
                        AND suspended = :suspended
                        AND id <> :userid";
    } else if (has_capability('local/costcenter:manage_ownorganization', $systemcontext)) {
        $userslist_sql = "SELECT id, username FROM {user}
                        WHERE id > :adminuserid
                        AND open_costcenterid = :costcenterid
                        AND deleted = :deleted
                        AND suspended = :suspended
                        AND id <> :userid";
        $userslistparams['costcenterid'] = $USER->open_costcenterid;
    }else if(has_capability('local/costcenter:manage_owndepartments', $systemcontext)){
        $userslist_sql = "SELECT id, username FROM {user}
                        WHERE id > :adminuserid
                        AND open_costcenterid = :costcenterid
                        AND open_departmentid = :departmentid
                        AND deleted = :deleted
                        AND suspended = :suspended
                        AND id <> :userid ";
        $userslistparams['costcenterid'] = $USER->open_costcenterid;
        $userslistparams['departmentid'] = $USER->open_departmentid;
    }
    $userslist = $DB->get_records_sql_menu($userslist_sql, $userslistparams);
    $select = $mform->addElement('autocomplete', 'username', '', $userslist, array('placeholder' => get_string('username')));
    $mform->setType('username', PARAM_RAW);
    $select->setMultiple(true);
}

/**
 * Description: User custom filter code
 * @param  [mform object]  $mform [the form object where the form is initiated]
 */
function customs_filter($mform) {
    global $DB, $USER;
    $systemcontext = context_system::instance();
    $filterv=$DB->get_field('local_filters', 'filters', array('plugins' => 'users'));
    $filterv=explode(',',$filterv);
    foreach($filterv as $fieldvalue){
        $userslistparams = array('adminuserid' => 2, 'deleted' => 0, 'suspended' => 0, 'userid' => $USER->id);
        if (is_siteadmin() || has_capability('local/costcenter:manage_multiorganizations', $systemcontext)) {
            $userslist_sql = "SELECT id, $fieldvalue FROM {user}
                            WHERE id > :adminuserid
                            AND deleted = :deleted
                            AND suspended = :suspended
                            AND id <> :userid ";
        } else if (has_capability('local/costcenter:manage_ownorganization', $systemcontext)) {
            $userslist_sql = "SELECT id, $fieldvalue FROM {user}
                            WHERE id > :adminuserid
                            AND open_costcenterid = :costcenterid
                            AND deleted = :deleted
                            AND suspended = :suspended
                            AND id <> :userid ";
            $userslistparams['costcenterid'] = $USER->open_costcenterid;
        } else if (has_capability('local/costcenter:manage_owndepartments', $systemcontext)) {
            $userslist = $DB->get_records_sql_menu("SELECT id,
                        $fieldvalue FROM {user}
                        WHERE id > :adminuserid
                        AND open_costcenterid = :costcenterid
                        AND open_departmentid = :departmentid
                        AND deleted = :deleted
                        AND suspended = :suspended
                        AND id <> :userid ");
            $userslistparams['costcenterid'] = $USER->open_costcenterid;
            $userslistparams['departmentid'] = $USER->open_departmentid;
        }
        $userslist = $DB->get_records_sql_menu($userslist_sql, $userslistparams);
        $select = $mform->addElement('autocomplete', $fieldvalue, '', $userslist, array('placeholder' => get_string($fieldvalue, 'local_employees')));
        $mform->setType($fieldvalue, PARAM_RAW);
        $select->setMultiple(true);
    }
}

/**
 * [globaltargetaudience_elementlist description]
 * @param  [type] $mform       [description]
 * @param  [type] $elementlist [description]
 * @return [type]              [description]
 */
function globaltargetaudiences_elementlist($mform,$elementlist){
    global $CFG, $DB, $USER;

    $context = context_system::instance();
    $params = array();
    $params['deleted'] = 0;
    $params['suspended'] = 0;
    if ($mform->modulecostcenter == 0 && (is_siteadmin()||has_capability('local/costcenter:manage_multiorganizations',$context))) {
        $main_sql="";
    } else if (has_capability('local/costcenter:manage_ownorganization',$context)) {
         $costcenterid = $mform->modulecostcenter ? $mform->modulecostcenter : $USER->open_costcenterid;
        $main_sql = " AND u.suspended = :suspended AND u.deleted =:deleted  AND u.open_costcenterid = :costcenterid ";
        $params['costcenterid'] = $costcenterid;
    } else if (has_capability('local/costcenter:manage_owndepartments',$context)) {
        $main_sql = " AND u.suspended = :suspended AND u.deleted = :deleted  AND u.open_costcenterid = :costcenterid AND u.open_departmentid = :departmentid ";
        $params['costcenterid'] = $USER->open_costcenterid;
        $params['departmentid'] = $USER->open_departmentid;
    }
    $dbman = $DB->get_manager();
    if (in_array('group', $elementlist)) {
        $groupslist[null]=get_string('all');
        if(is_siteadmin() || has_capability('local/costcenter:manage_multiorganizations',$context) ){
            if($dbman->table_exists('local_groups')){
                $groupslist += $DB->get_records_sql_menu("SELECT c.id, c.name FROM {local_groups} g, {cohort} c  WHERE c.visible = :visible AND c.id = g.cohortid ",array('visible' => 1));
            }
        } else if (has_capability('local/costcenter:manage_ownorganization', $context)) {
            $groupslist+= $DB->get_records_sql_menu("SELECT c.id,
                        c.name FROM {local_groups} g, {cohort} c
                        WHERE c.visible = :visible
                        AND c.id = g.cohortid
                        AND g.costcenterid = :costcenterid ",
                        array('costcenterid' => $USER->open_costcenterid,'visible' => 1));
        } else if (has_capability('local/costcenter:manage_owndepartments', $context)) {
            $groupslist+= $DB->get_records_sql_menu("SELECT c.id,
                        c.name FROM {local_groups} g, {cohort} c
                        WHERE c.visible = 1 AND c.id = g.cohortid
                        AND g.costcenterid = :costcenterid
                        AND  g.departmentid = :departmentid ",
                        array('costcenterid' => $USER->open_costcenterid,'departmentid' => $USER->open_departmentid,'visible' => 1));
        }
        $selectgroup = $mform->addElement('autocomplete',  'open_group',  get_string('open_group', 'local_employees'),$groupslist);
        $mform->setType('open_group', PARAM_RAW);
        $mform->addHelpButton('open_group','groups','local_employees');
        $selectgroup->setMultiple(true);
    }
    if (in_array('branch', $elementlist)) {
        $branch_details[null]=get_string('all');
        $branch_sql = "SELECT u.open_branch,
                    u.open_branch AS branchvalue
                    FROM {user} AS u WHERE u.id > 2 $main_sql
                    AND u.open_branch IS NOT NULL
                    GROUP BY u.open_branch";
        $branch_details+= $DB->get_records_sql_menu($branch_sql,$params);
        $selectbranch = $mform->addElement('autocomplete',  'open_branch',  get_string('open_branch', 'local_employees'), $branch_details);
        $mform->setType('open_branch', PARAM_RAW);
        $selectbranch->setMultiple(true);
    }
}

/*
* Displays a node in left side menu
* @return  [type] string  link for the leftmenu
*/
function local_employees_leftmenunode(){
    global $USER, $DB;
    $systemcontext = context_system::instance();
    $key=array();
    $usersnode = '';
     if (has_capability('local/employees:manage',$systemcontext) || has_capability('local/employees:view',$systemcontext) || is_siteadmin()) {
        $usersnode .= html_writer::start_tag('li', array('id'=> 'id_leftmenu_users', 'class'=>'pull-left user_nav_div users'));
            $users_url = new moodle_url('/local/employees/index.php');
            $users = html_writer::link($users_url, '<span class="teacher_structure_icon dypatil_cmn_icon icon"></span>
            <span class="user_navigation_link_text">'.get_string('manage_employees','local_employees').'</span>',
            array('class'=>'user_navigation_link'));
            $usersnode .= $users;
        $usersnode .= html_writer::end_tag('li');
        $key=array('2' => $usersnode);
    }
    return $key;
}

function local_employees_quicklink_node(){
    global $DB, $PAGE, $USER, $CFG,$OUTPUT;
    $systemcontext = context_system::instance();
    if(is_siteadmin() || has_capability('local/employees:view',$systemcontext)){
        $sql = "SELECT count(u.id) FROM {user} u 
                JOIN {local_costcenter} lc ON lc.id = u.open_costcenterid 
                WHERE u.id > 2  AND u.deleted = :deleted";

        $suspendsql = " AND u.suspended = :suspended ";
        $params = array();
        $params['deleted'] =  0;

        $activeparams = array();
        $activeparams['suspended'] = 0;
        $activeparams['deleted'] = 0;

        $inactiveparams = array();
        $inactiveparams['suspended'] = 1;
        $inactiveparams['deleted'] = 0;

        if (is_siteadmin() || has_capability('local/costcenter:manage_multiorganizations', $systemcontext)) {
            $sql .= "";
        } else if(has_capability('local/costcenter:manage_ownorganization', $systemcontext)){
            //costcenterid concating
            $sql .= " AND u.open_costcenterid = :costcenterid ";
            $params['costcenterid'] =  $USER->open_costcenterid;
            $activeparams['costcenterid'] =  $USER->open_costcenterid;
            $inactiveparams['costcenterid'] =  $USER->open_costcenterid;
        } else if (has_capability('local/costcenter:manage_owndepartments',$systemcontext)) {
            //costcenterid concating
            $sql .= " AND u.open_costcenterid = :costcenterid ";
            $params['costcenterid'] =  $USER->open_costcenterid;
            $activeparams['costcenterid'] =  $USER->open_costcenterid;
            $inactiveparams['costcenterid'] =  $USER->open_costcenterid;

            //departmentid concating
            $sql .= " AND u.open_departmentid = :departmentid ";
            $params['departmentid'] =  $USER->open_departmentid;
            $activeparams['departmentid'] =  $USER->open_departmentid;
            $inactiveparams['departmentid'] =  $USER->open_departmentid;
        }
        $count_activeusers = $DB->count_records_sql($sql.$suspendsql, $activeparams);
        $count_inactiveusers = $DB->count_records_sql($sql.$suspendsql, $inactiveparams);
        $count_users = $DB->count_records_sql($sql, $params);
        $percent = round(($count_activeusers/$count_users)*100);
        $percent = (int)$percent;

        //local users count content
        $local_employees = $PAGE->requires->js_call_amd('local_employees/newemployees', 'load', array());

        $countinformation = array();
        $displayline = false;
        $hascapablity = false;

        if(has_capability('local/employees:create',$systemcontext) || is_siteadmin()){
            $displayline = true;
            $hascapablity = true;
            $countinformation['create_element'] = html_writer::link('javascript:void(0)', get_string('create'), array('class'=>'quick_nav_link goto_local_employees course_extended_menu_itemlink', 'data-action' => 'createusersmodal', 'title' => get_string('createemployee', 'local_employees'), 'data-action' => 'createusersmodal',  'onclick' => '(function(e){ require("local_employees/newemployees").init({selector:"createusersmodal", context:1, userid:'.$USER->id.', form_status:0}) })(event)'));
        }
        $countinformation['node_header_string'] = get_string('manage_br_employees', 'local_employees');
        $countinformation['pluginname'] = 'employees';
        $countinformation['plugin_icon_class'] = 'fa fa-user-plus';
        $countinformation['quicknav_icon_class'] = 'quicknav_icon_user';
        $countinformation['contextid'] = $systemcontext->id;
        $countinformation['userid'] = $USER->id;
        $countinformation['create'] = $hascapablity;
        $countinformation['viewlink_url'] = $CFG->wwwroot.'/local/employees/index.php';
        $countinformation['view'] = TRUE;
        $countinformation['displaystats'] = TRUE;

        $countinformation['percentage'] = $percent;
        $countinformation['count_total'] = $count_users;
        $countinformation['count_inactive'] = $count_inactiveusers;
        $countinformation['inactive_string'] = get_string('inactive_string', 'block_quick_navigation');
        $countinformation['count_active'] = $count_activeusers;
        $countinformation['space_count'] = 'two';
        $local_employees .= $OUTPUT->render_from_template('block_quick_navigation/quicklink_node', $countinformation);
        }
    return array('1' => $local_employees);
}

/*
* return count of users under selected costcenter
* @return  [type] int count of users
*/
function manage_employees_count($stable,$filterdata) {
    global $DB, $PAGE,$USER,$CFG,$OUTPUT;
    $systemcontext = context_system::instance();
    $countsql = "SELECT  count(u.id) "; 
    $selectsql = "SELECT  u.* ,lc.fullname AS costcentername ,(SELECT fullname FROM {local_costcenter} WHERE id=u.open_departmentid) AS departmentname ";

$formsql   =" FROM {user} AS u 
        JOIN {local_costcenter} AS lc ON lc.id=u.open_costcenterid 
        WHERE u.deleted=0 and (u.id > 2 AND u.deleted = 0) AND (u.open_type=0) ";

    $params = array();
    if(is_siteadmin() || has_capability('local/costcenter:manage_multiorganizations', $systemcontext)){
        $formsql .= "";
    }else if(!is_siteadmin() && has_capability('local/costcenter:manage_ownorganization', $systemcontext)){
        // $collegeadminsql = "SELECT u.id FROM {user} u 
        //             JOIN {role_assignments} ra ON ra.userid = u.id 
        //             JOIN {role} r ON r.id = ra.roleid 
        //             WHERE r.shortname = 'collegeadmin' AND u.suspended <> 1
        //             AND u.open_costcenterid = :costcenter";
        // $collegeadmins = $DB->get_fieldset_sql($collegeadminsql, ['costcenter' => $USER->open_costcenterid]);
        // if (count($collegeadmins) > 0) {
        //     $collegeadminsids = implode(',', $collegeadmins);
        //     $formsql .= " AND open_costcenterid = :costcenterid AND u.id <> :userid AND u.id NOT IN($collegeadminsids) ";
        // } else {
            $formsql .= " AND open_costcenterid = :costcenterid AND u.id <> :userid AND u.roleid <> :roleid ";
        // }
        $params['costcenterid'] = $USER->open_costcenterid;
        $params['userid'] = $USER->id;
        $params['roleid'] = $USER->roleid;
    }else if(!is_siteadmin() && has_capability('local/costcenter:manage_owndepartments', $systemcontext)){
        $orgadminsql = "SELECT u.id FROM {user} u 
                    JOIN {role_assignments} ra ON ra.userid = u.id 
                    JOIN {role} r ON r.id = ra.roleid 
                    WHERE r.shortname = 'orgadmin' AND u.suspended <> 1
                    AND u.open_costcenterid = :costcenter";
        $orgadmins = $DB->get_fieldset_sql($orgadminsql, ['costcenter' => $USER->open_costcenterid]);
        if (count($orgadmins) > 0) {
            $orgadminsids = implode(',', $orgadmins);
            $formsql .= " AND open_costcenterid = :costcenterid AND open_departmentid = :departmentid AND u.id <> :userid AND u.roleid <> :roleid AND u.id NOT IN($orgadminsids) ";
        } else {
            $formsql .= " AND open_costcenterid = :costcenterid AND open_departmentid = :departmentid AND u.id <> :userid AND u.roleid <> :roleid";
        }
        $params['costcenterid'] = $USER->open_costcenterid;
        $params['departmentid'] = $USER->open_departmentid;
        $params['userid'] = $USER->id;
        $params['roleid'] = $USER->roleid;
    }
    else if(!is_siteadmin() && has_capability('local/costcenter:manage_ownsubdepartments', $systemcontext)){
        $collegeadminsql = "SELECT u.id FROM {user} u 
                    JOIN {role_assignments} ra ON ra.userid = u.id 
                    JOIN {role} r ON r.id = ra.roleid 
                    WHERE r.shortname = 'collegeadmin' OR r.shortname = 'departmentadmin' AND u.suspended <> 1
                    AND u.open_costcenterid = :costcenter
                    AND u.open_departmentid = :departmentid
                    AND u.open_subdepartment = :subdepartmentid";
        $collegeadmins = $DB->get_fieldset_sql($collegeadminsql, ['costcenter' => $USER->open_costcenterid, 'departmentid' => $USER->open_departmentid, 'subdepartmentid' =>$USER->open_subdepartment]);
        if (count($collegeadmins) > 0) {
            $collegeadminsids = implode(',', $collegeadmins);
            $formsql .= " AND open_costcenterid = :costcenterid AND open_departmentid = :departmentid AND open_subdepartment = :subdepartment AND u.id <> :userid AND u.roleid <> :roleid AND u.id NOT IN($collegeadminsids) ";
        } else {
            $formsql .= " AND open_costcenterid = :costcenterid AND open_departmentid = :departmentid AND open_subdepartment = :subdepartment AND u.id <> :userid AND u.roleid = :roleid ";
        }
        $params['costcenterid'] = $USER->open_costcenterid;
        $params['departmentid'] = $USER->open_departmentid;
        $params['subdepartment'] = $USER->open_subdepartment;
        $params['userid'] = $USER->id;
        $params['roleid'] = $USER->roleid;
    }
    if(isset($filterdata->search_query) && trim($filterdata->search_query) != ''){
        $formsql .= " AND (u.username LIKE :search1 OR concat(u.firstname,' ',u.lastname) LIKE :search2 OR u.email LIKE :search3 OR u.open_employeeid LIKE :search4 )";
        $params['search1'] = '%'.trim($filterdata->search_query).'%';
        $params['search2'] = '%'.trim($filterdata->search_query).'%';
        $params['search3'] = '%'.trim($filterdata->search_query).'%';
        $params['search4'] = '%'.trim($filterdata->search_query).'%';
    }
    if(!empty($filterdata->idnumber)){
        $idnumber =explode(',',$filterdata->idnumber);
        $idnumbers = array_filter($idnumbers, function($value){
            if($value != '_qf__force_multiselect_submission'){
                return $value;
            }
        });
        if($idnumbers != NULL) {
            list($relatedidnumbersql, $relatedidnumberparams) = $DB->get_in_or_equal($idnumbers, SQL_PARAMS_NAMED, 'idnumber');
            $params = array_merge($params,$relatedidnumberparams);
            $formsql .= " AND u.id $relatedidnumbersql";
        }   
    }
    if (!empty($filterdata->email)) {
          $emails =explode(',',$filterdata->email);
            $emails = array_filter($emails, function($value){
                if($value != '_qf__force_multiselect_submission'){
                    return $value;
                }
            });
        if($emails != NULL) {
            $emailarray = array();
            foreach($emails as $key => $value){
                $emailarray[] = "u.id = $value";
            }
            $implodeemail = implode(' OR ', $emailarray);
            $formsql .= " AND ($implodeemail)";
        }
    } 
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
            $params = array_merge($params,$relatedorganizationsparams);
            $formsql .= " AND u.open_costcenterid $relatedeorganizationssql";
        }
    }
    if(!empty($filterdata->$secondlevel)){
        $departments = explode(',',$filterdata->$secondlevel);
        $departments = array_filter($departments, function($value){
                if($value != '_qf__force_multiselect_submission'){
                    return $value;
                }
        });
        if($departments != NULL) {
            list($relatededepartmentssql, $relateddepartmentsparams) = $DB->get_in_or_equal($departments, SQL_PARAMS_NAMED, 'departments');
            $params = array_merge($params,$relateddepartmentsparams);
            $formsql .= " AND u.open_departmentid $relatededepartmentssql";
        }
    }
    if(!empty($filterdata->$thirdlevel)){
        $subdepartment = explode(',',$filterdata->$thirdlevel);
        $subdepartment = array_filter($subdepartment, function($value){
                if($value != '_qf__force_multiselect_submission'){
                    return $value;
                }
        });
        if($subdepartment != NULL) {
            list($relatedesubdepartmentsql, $relatedsubdepartmentparams) = $DB->get_in_or_equal($subdepartment, SQL_PARAMS_NAMED, 'subdepartment');
            $params = array_merge($params,$relatedsubdepartmentparams);
            $formsql .= " AND u.open_subdepartment $relatedesubdepartmentsql";
        }
    }
    if (!empty($filterdata->location)) {

        $locations = explode(',',$filterdata->location);
        if($locations != NULL) {
            list($locationsql, $locationparams) = $DB->get_in_or_equal($locations, SQL_PARAMS_NAMED, 'location');
            $params = array_merge($params,$locationparams);            
            $formsql .= " AND u.open_location {$locationsql} ";
        }
    }
    if(!empty($filterdata->role)){
        $role = explode(',',$filterdata->role);
        $role = array_filter($role, function($value){
                if($value != '_qf__force_multiselect_submission'){
                    return $value;
                }
        });
        if($role != NULL) {
            list($relatederolesql, $relatedroleparams) = $DB->get_in_or_equal($role, SQL_PARAMS_NAMED, 'role');
            $params = array_merge($params,$relatedroleparams);
            $formsql .= " AND u.roleid $relatederolesql";
        }
    }
    if (!empty($filterdata->hrmsrole)) {

        $hrmsroles = explode(',',$filterdata->hrmsrole);
        if($hrmsroles != NULL) {
            list($hrmsrolesql, $hrmsroleparams) = $DB->get_in_or_equal($hrmsroles, SQL_PARAMS_NAMED, 'hrmsrole');
            $params = array_merge($params,$hrmsroleparams);            
            $formsql .= " AND u.open_hrmsrole {$hrmsrolesql} ";
        }
    }
    if(!empty($filterdata->status)){
        $status = explode(',',$filterdata->status);
        $status = array_filter($status, function($value){
            if($value != '_qf__force_multiselect_submission'){
                return $value;
            }
        });
        //0 is inactive and 1 is active.
        if(!(in_array('active',$status) && in_array('inactive',$status))){
            if(in_array('active' ,$status)){
                $formsql .= " AND u.suspended = 0";           
            }else if(in_array('inactive' ,$status)){
                $formsql .= " AND u.suspended = 1";
            }
        }
    }
     
    $ordersql = " ORDER BY u.id DESC";
    $totalusers = $DB->count_records_sql($countsql.$formsql,$params);

    $activesql =  " AND u.suspended = :suspended ";
    $params['suspended'] = 0;
    $activeusers = 0;
    $activeusers = $DB->count_records_sql($countsql.$formsql.$activesql, $params);

    $params['suspended'] = 1;
    $inactiveusers = 0;
    $inactiveusers =  $DB->count_records_sql($countsql.$formsql.$activesql,$params);
    $users = $DB->get_records_sql($selectsql.$formsql.$ordersql,$params,$stable->start,$stable->length);
        return array('totalusers' => $totalusers,'activeusercount' => $activeusers,'inactiveusercount' => $inactiveusers,'users' => $users);
} 

/*
* return count of users under selected costcenter
* @return  [type] int count of users
*/
function manage_employees_content($stable, $users/*,$filterdata*/){
    global $DB, $PAGE,$USER,$CFG,$OUTPUT;
    $systemcontext = context_system::instance();
    $userslist = $users['users'];
    $data = array();

    foreach ($userslist as $user) {
        $list = array();
        $line = array();
        $user_picture = new user_picture($user, array('size' => 60, 'class' => 'userpic', 'link'=>false));
        $user_picture->size = 101; // to get the high resolution image (f3).
        $user_picture = $user_picture->get_url($PAGE);
        $userpic = $user_picture->out();
        $list['userpic'] = $userpic;
        $username = $user->firstname.' '.$user->lastname;
        if(strlen($username) > 24){
            $username = substr($username, 0, 24).'...';
        }
        $list['username'] = html_writer::tag('a', $username, array('href' =>$CFG->wwwroot. '/local/employees/profile.php?id='.$user->id));
        $useremail = $user->email;
        if(strlen($useremail) > 24){
            $useremail = substr($useremail, 0, 24).'...';
        }
        $list['email'] = !empty($useremail) ? $useremail : 'N/A';
        $list['fullemail'] = $user->email;


    $usertype = $user->open_type;             
        $rolename = $DB->get_field('role','shortname',array('id'=>$user->roleid));
        $opencostid = $DB->get_field('user','open_costcenterid',array('id'=>$user->id));
        $organization = $DB->get_field('local_costcenter','fullname',array('id'=>$opencostid));
        if($rolename == 'editingteacher'){
            $rolename = 'Teacher';
        }
        if($rolename == 'hod'){
            $rolename = 'HOD';
        }
            $list['open_type'] = $rolename;

        $program=$DB->get_record_sql("SELECT p.name FROM {local_program} p
                JOIN {local_program_users} u
                ON p.id=u.programid
                WHERE u.userid=$user->id");
        if(!empty($program)){
            $list['programstring'] = $program->name;
        }else{
            $list['programstring'] = 'N/A';
        }
        $batch=$DB->get_record_sql("SELECT c.name FROM {cohort} c
                JOIN {local_program} p
                ON c.id=p.batchid
                JOIN {local_program_users} pu
                ON p.id = pu.programid
                WHERE pu.userid=$user->id ");

    $allcoursesql = "SELECT u.id,c.fullname,plc.programid as programid,
                        pl.id as levelid
                        FROM {user} u
                        JOIN {role_assignments} ra ON ra.userid = u.id
                        JOIN {role} r ON r.id = ra.roleid AND r.shortname = 'editingteacher'
                        JOIN {context} ctx ON ctx.id = ra.contextid
                        JOIN {course} c ON c.id = ctx.instanceid
                        JOIN {local_program_level_courses} plc ON plc.courseid=c.id
                        JOIN {local_program_levels} pl ON pl.id = plc.levelid 
                        AND pl.programid = plc.programid
                        WHERE u.id = $user->id ";

        $allcourse = $DB->get_records_sql($allcoursesql);
            foreach($allcourse as $pro){
                $list['id'] =$pro->id;
            }

        $popularcoursesql = "SELECT p.id,p.name,count(c.id) as coursecounts
                      FROM {user} u
                      JOIN {role_assignments} ra ON ra.userid = u.id
                      JOIN {role} r ON r.id=ra.roleid AND r.shortname = 'editingteacher'
                      JOIN {context} ctx ON ctx.id = ra.contextid
                      JOIN {course} c ON c.id = ctx.instanceid
                      JOIN {local_program_level_courses} plc ON plc.courseid = ctx.instanceid JOIN {local_program} p ON p.id= plc.programid
                     WHERE u.id =  $user->id GROUP BY p.id";

            $popularcourse=$DB->get_records_sql($popularcoursesql);
            $procount =count($popularcourse);
            $list['programcount'] = $procount;
        $allcoursesqlss = "SELECT c.id,c.fullname,plc.programid as programid,
                        pl.id as levelid
                        FROM {user} u
                        JOIN {role_assignments} ra ON ra.userid = u.id
                        JOIN {role} r ON r.id = ra.roleid AND r.shortname = 'editingteacher'
                        JOIN {context} ctx ON ctx.id = ra.contextid
                        JOIN {course} c ON c.id = ctx.instanceid
                        JOIN {local_program_level_courses} plc ON plc.courseid=c.id
                        JOIN {local_program_levels} pl ON pl.id = plc.levelid 
                        AND pl.programid = plc.programid
                        WHERE u.id = $user->id ";

        $allcours = $DB->get_records_sql($allcoursesqlss);
        $allenrolledcoun =count($allcours);

        $list['coursecount'] = $allenrolledcoun;
        
            foreach ($allcours as $key => $vals) {
                $students = "SELECT u.id as sid,u.firstname,u.lastname
                      FROM {user} u
                      JOIN {role_assignments} ra ON ra.userid = u.id
                      JOIN {role} r ON r.id=ra.roleid AND r.shortname = 'student'
                      JOIN {context} ctx ON ctx.id = ra.contextid
                      JOIN {course} c ON c.id = ctx.instanceid
                      JOIN {local_program_level_courses} plc ON plc.courseid=c.id
                      JOIN {local_program} p ON p.id = plc.programid
                     WHERE c.id = $vals->id ";

                $sturecords = $DB->get_records_sql($students);
                $stucount = count($sturecords);

                $userattendedsql = "SELECT COUNT(stat.id) as statuscount
                            FROM {attendance_log} al
                            JOIN {attendance_sessions} ats ON al.sessionid = ats.id
                            JOIN {attendance} a ON ats.attendanceid = a.id
                            JOIN {attendance_statuses} stat ON al.statusid = stat.id
                            JOIN {local_program_level_courses} lplc ON a.course = lplc.courseid
                            JOIN {local_program_levels} pl ON lplc.programid = pl.programid
                            AND lplc.levelid = pl.id
                            WHERE lplc.courseid = $vals->id 
                            AND stat.acronym IN ('P','L')
                            AND lplc.programid = $vals->programid";

                $userattended = $DB->count_records_sql($userattendedsql);

                $totalattdencesql = "SELECT COUNT(DISTINCT(alog.id)) 
                            FROM {attendance_log} alog 
                            JOIN {attendance_sessions} ases ON ases.id = alog.sessionid 
                            JOIN {attendance} att ON att.id = ases.attendanceid 
                            WHERE att.course = $vals->id";
                $totalattdence = $DB->count_records_sql($totalattdencesql);

                    if ($userattended && $totalattdence > 0) {
                        $percentage = round(($userattended / $totalattdence) * 100);
                    } else {
                        $percentage = 0;
                    }

                $list['cid'] = $vals->id;
                $list['fullnames'] = $vals->fullname;
                $list['programid'] = $vals->programid;
                $list['studentcount'] = $stucount;
                $list['percentage'] = $percentage;
                    
            }



        if(!empty($batch)){
            $list['batchinfo'] = $batch->name;
        }else {
            $list['batchinfo'] = 'N/A';
        }

        $labelstring = get_config('local_costcenter');
        $list['org'] = $organization;
        $list['orgstring'] = $orgstring;
        $list['firstlabelstring'] = $labelstring->firstlevel;
        // $orgstring = strlen($organization) > 24 ? substr($organization, 0, 24)."..." : $organization;
        $list['org'] = $organization;
        $list['orgstring'] = $organization;

        $deptstring = strlen($dept) > 24 ? substr($dept, 0, 24)."..." : $dept;
        $designation = $user->open_designation;
        $designationstring = strlen($user->open_designation) > 14 ? substr($user->open_designation, 0, 14)."..." : $user->open_designation;

        $list['deptstring'] = $deptstring;
        $list['dept'] = $dept;
        $list['group'] = $user->open_group ? $user->open_group : 'N/A';
        $list['level'] = $user->open_level ? $user->open_level : 'N/A';
        $list['phno'] = ($user->phone1) ? $user->phone1 : '--' ;
        $list['designation'] = $designation;
        $list['designationstring'] = ($designationstring) ? $designationstring : '--' ;
        if(!empty($user->open_supervisorid)){
            $supervisior=$DB->get_field_sql("SELECT CONCAT(firstname,' ',lastname) AS fullname
                                             FROM {user} WHERE id = :supervisiorid",array('supervisiorid' => $user->open_supervisorid));
            $supervisiorstring = strlen($supervisior) > 12 ? substr($supervisior, 0, 12)."..." : $supervisior;
            $list['supervisor'] = $supervisior;
            $list['supervisiorstring'] = $supervisiorstring;
        } else{
            $list['supervisiorstring'] = '--' ;
        }
        $list['lastaccess'] = ($user->lastaccess) ? format_time(time() - $user->lastaccess) : get_string('never');
        $list['userid'] = $user->id;
        $list['fullname'] = fullname($user);
        if (has_capability('local/employees:manage', context_system::instance()) || is_siteadmin())
            $list['visible'] = $user->suspended;
            if(is_siteadmin() || has_capability('local/employees:edit', context_system::instance())){
                $list['editcap'] = 1;
            }else{
                $list['editcap'] = 0;
            }
            if(is_siteadmin() || has_capability('local/employees:trash', context_system::instance())){
                $list['delcap'] = 1;
            }else{
                $list['delcap'] = 0;
            }
            $enrolteacher=$DB->record_exists('user_enrolments', array('userid' => $user->id));
            if($enrolteacher){
                $list['delteacher'] = 1;
            }else{
                $list['delteacher'] = 0;
            }
            $data[] = $list;
    }
    return $data;
}

/*
* return filterform
*/
function employees_filters_form($filterparams){
    global $CFG;

    require_once($CFG->dirroot . '/local/courses/filters_form.php');

    $systemcontext = context_system::instance();
    
    if(is_siteadmin() || has_capability('local/costcenter:manage_multiorganizations', $systemcontext)){
        $mform = new filters_form(null, array('filterlist'=>array('dependent_fields',  'empemail', 'status','role'), 'courseid' => 1, 'enrolid' => 0,'plugins'=>array('employees','costcenter'),'filterparams' => $filterparams));
    }else if(has_capability('local/costcenter:manage_ownorganization',$systemcontext)){
        $mform = new filters_form(null, array('filterlist'=>array('dependent_fields', 'empemail', 'status','role'), 'courseid' => 1, 'enrolid' => 0,'plugins'=>array('employees','costcenter'),'filterparams' => $filterparams));
    }else if(has_capability('local/costcenter:manage_owndepartments', $systemcontext)){
        $mform = new filters_form(null, array('filterlist'=>array('dependent_fields', 'empemail', 'status','role'), 'courseid' => 1, 'enrolid' => 0,'plugins'=>array('employees','costcenter'),'filterparams' => $filterparams));
    }else if(has_capability('local/costcenter:manage_ownsubdepartments', $systemcontext)){
        $mform = new filters_form(null, array('filterlist'=>array('empemail', 'status'), 'courseid' => 1, 'enrolid' => 0,'plugins'=>array('employees','costcenter'),'filterparams' => $filterparams));
    }/*else {
        $mform = new filters_form(null, array('filterlist'=>array('empemail','status', 'location', 'hrmsrole'), 'courseid' => 1, 'enrolid' => 0,'plugins'=>array('employees','costcenter'),'filterparams' => $filterparams));
    }*/
    return $mform;
}

/*
* @return true for reports under category
*/
function learnerscript_employees_list(){
    return get_string('users', 'local_employees');
}

function send_logins_employees($user) {
    // removal of code if triggered by any chance. should never happen..
}

function local_employees_output_fragment_update_user_profile($args){
    global $CFG,$DB, $PAGE, $SESSION;
    $args = (object) $args;
    $context = $args->context;
    $SESSION->profileupdate = false;
    $o = '';
    $formdata = [];
    if (!empty($args->jsonformdata)) {
        $serialiseddata = json_decode($args->jsonformdata);
        parse_str($serialiseddata, $formdata);
    }
    $editoroptions = [
        'maxfiles' => EDITOR_UNLIMITED_FILES,
        'maxbytes' => $course->maxbytes,
        'trust' => false,
        'context' => $context,
        'noclean' => true,
        'subdirs' => false
    ];
    $group = file_prepare_standard_editor($group, 'description', $editoroptions, $context, 'group', 'description', null);
    if ($args->id > 0) {
        $heading = 'Update User';
        $collapse = false;
        $data = $DB->get_record('user', array('id'=>$args->id));
            $description= $data->description;
            unset($data->description);
            $data->description['text'] =$description;

        unset($data->password);
        useredit_load_preferences($data);
        $mform = new local_employees\forms\profile_form(null, array('editoroptions' => $editoroptions,'form_status' => $args->form_status,'id' => $data->id,'org'=>$data->open_costcenterid,'dept'=>$data->open_departmentid,'subdept'=>$data->open_subdepartment), 'post', '', null, true, $formdata);
        $mform->set_data($data);
    }

    if (!empty($args->jsonformdata) && strlen($args->jsonformdata) >2) {
        // If we were passed non-empty form data we want the mform to call validation functions and show errors.
        $mform->is_validated();
    }
    $formheaders = array_keys($mform->formstatus);
    $nextform = array_key_exists($args->form_status, $formheaders);
    if ($nextform === false) {
        return false;
    }
    $renderer = $PAGE->get_renderer('local_employees');
    ob_start();
    $formstatus = array();
    foreach (array_values($mform->formstatus) as $k => $mformstatus) {
        $activeclass = $k == $args->form_status ? 'active' : '';
        $formstatus[] = array('name' => $mformstatus, 'activeclass' => $activeclass);
    }
    $formstatusview = new \local_employees\output\form_status($formstatus);
    $o .= $renderer->render($formstatusview);
    $mform->display();
    $o .= ob_get_contents();
    ob_end_clean();
    return $o;
}

function contactsus_log($formdata, $sentstatus){
        global $DB, $USER;

        $touser = $DB->get_record('user', array('id'=>$formdata->mailto), 'id,email');
        $dataobject = new stdClass();
        $dataobject->notification_infoid = 0;
        $dataobject->from_userid = $USER->id;
        $dataobject->from_emailid = $USER->email;
        $dataobject->to_userid = $touser->id;
        $dataobject->to_emailid = $touser->email;
        $dataobject->moduletype = 'contactus';
        $dataobject->moduleid = 0;
        $dataobject->subject = $formdata->subject;
        $dataobject->emailbody = $formdata->emailbody['text'];
        if($sentstatus){
            $dataobject->status = 1;
        }else{
            $dataobject->status = 0;
        }
        $dataobject->ccto=0;
        $dataobject->time_created = time();
        $dataobject->user_created = 2;
        $insert = $DB->insert_record('local_emaillogs', $dataobject);

        return $insert;
}

function local_employees_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {
        // Check the contextlevel is as expected - if your plugin is a block, this becomes CONTEXT_BLOCK, etc.

        // Make sure the filearea is one of those used by the plugin.
        if ($filearea !== 'sme_support') {
            return false;
        }
        $itemid = array_shift($args);
        $filename = array_pop($args);
        if (!$args) {
            $filepath = '/';
        } else {
            $filepath = '/'.implode('/', $args).'/';
        }
        // Retrieve the file from the Files API.
        $fs = get_file_storage();
        $file = $fs->get_file($context->id, 'local_employees', $filearea, $itemid, $filepath, $filename);
        if (!$file) {
            return false;
        }
        send_file($file, $filename, 0, $forcedownload, $options);
    }
function local_employees_output_fragment_users_display($args) {
    global $CFG, $OUTPUT, $DB, $PAGE, $USER;
        $id =$args['id'];
        // $PAGE->set_url('/local/employees/index.php');

        $coursesql = "SELECT DISTINCT(c.id),c.fullname
                      FROM {user} u
                      JOIN {role_assignments} ra ON ra.userid = u.id
                      JOIN {role} r ON r.id = ra.roleid AND r.shortname = 'editingteacher'
                      JOIN {context} AS ctx ON ctx.id = ra.contextid
                      JOIN {course} c ON c.id = ctx.instanceid
                      JOIN {local_program_level_courses} plc ON plc.courseid=c.id
                      JOIN {local_program} p ON p.id = plc.programid
                      JOIN {cohort} co ON co.id = p.batchid
                      
                     WHERE u.id = $id";

        $courses = $DB->get_records_sql($coursesql);
        $enrolledcou =count($courses);

            $coursesarray = array();
            foreach ($courses as $coursekey => $value) {

                $students = "SELECT u.id as studentid,pl.startdate,pl.enddate,
                            c.fullname as fullname,plc.programid 
                        FROM {user} u 
                        JOIN {role_assignments} ra ON ra.userid = u.id 
                        JOIN {role} r ON r.id = ra.roleid AND r.shortname = 'student'
                        JOIN {context} ctx ON ctx.id = ra.contextid                 
                        JOIN {course} c ON c.id = ctx.instanceid 
                        JOIN {local_program_level_courses} plc ON plc.courseid = c.id 
                        JOIN {local_program_levels} pl ON pl.id = plc.levelid
                        WHERE ctx.instanceid = $value->id ";

                $sturecords = $DB->get_records_sql($students);
                $stucount = count($sturecords);
                foreach ($sturecords as $sturecordskey) {

                    $line =array();
                    $line['id'] = $sturecordskey->id;
                    $line['fullname'] = $sturecordskey->fullname;
                    $line['studentcount'] = $stucount;
                    $line['startdate'] = date('d-M-Y',$sturecordskey->startdate);
                    $line['enddate'] = date('d-M-Y',$sturecordskey->enddate);
                    $line['programid'] = $sturecordskey->programid;
                }
                $coursesarray[]=$line;
        }

        $usersviewContext = [
            'coursecount' => $enrolledcou,
            'coursename' => $coursesarray,
            ];

        $output = $OUTPUT->render_from_template('local_employees/courseview', $usersviewContext);
    return $output;
        
}
function local_employees_output_fragment_students_display($args) {
    global $CFG, $OUTPUT, $DB, $PAGE, $USER;
        $PAGE->set_url('/local/employees/profile.php');
        $id =$args['id'];
        $teacherid =$args['teacherid'];
          $open_identifiedas =$args['open_identifiedas'];

/*   old code   ---------------- */
      /*  $students = "SELECT u.id,u.firstname,u.lastname,u.email,p.id as pid,p.name
                      FROM {user} u
                      JOIN {role_assignments} ra ON ra.userid = u.id
                      JOIN {role} r ON r.id = ra.roleid AND r.shortname = 'student'
                      JOIN {context} AS ctx ON ctx.id = ra.contextid
                      JOIN {course} c ON c.id = ctx.instanceid
                      JOIN {local_program_level_courses} plc ON plc.courseid=c.id
                      JOIN {local_program} p ON p.id = plc.programid
                      JOIN {cohort} co ON co.id = p.batchid
                     WHERE c.id = $id";
            $sturecords = $DB->get_records_sql($students);

            foreach ($sturecords as $key ) {
                $userattendedsql = "SELECT  COUNT(stat.id) as statuscount
                              FROM {attendance_log} al
                              JOIN {attendance_sessions} ats ON al.sessionid = ats.id
                              JOIN {attendance} a ON ats.attendanceid = a.id
                              JOIN {attendance_statuses} stat ON al.statusid = stat.id
                              JOIN {local_program_level_courses} lplc ON a.course = lplc.courseid
                              JOIN {local_program_levels} pl ON lplc.programid = pl.programid
                              AND lplc.levelid = pl.id
                             WHERE lplc.courseid = $id 
                             AND ats.teacherid=$teacherid  AND ats.lasttakenby = $teacherid  AND stat.acronym IN ('P','L') 
                              AND lplc.programid = $key->pid AND al.studentid = $key->id AND DATE(FROM_UNIXTIME(ats.sessdate, '%Y-%m-%d')) < DATE(NOW())";
            
                $userattended = $DB->count_records_sql($userattendedsql);


                $totalattdencesql = "SELECT COUNT(DISTINCT(alog.id)) 
                            FROM {attendance_log} alog 
                            JOIN {attendance_sessions} ases ON ases.id = alog.sessionid 
                            JOIN {attendance} att ON att.id = ases.attendanceid 
                            WHERE att.course = $id AND ases.teacherid=$teacherid
                             AND ases.lasttakenby = $teacherid  AND alog.studentid = $key->id AND DATE(FROM_UNIXTIME(ases.sessdate, '%Y-%m-%d')) < DATE(NOW())";
                $totalattdence = $DB->count_records_sql($totalattdencesql);

                    if ($userattended && $totalattdence > 0) {
                        $percentage['aa'] = (($userattended / $totalattdence) * 100);
                    } else {
                        $percentage['aa'] = 0;
                        }
                    if ($percentage['aa'] == 0 || $percentage['aa'] == 100) {
                        $participantsaverages['aa'] = intval($percentage['aa'] );
                    } else {
                        $participantsaverages['aa'] = number_format($percentage['aa'], 2, '.', '.');
                    }


                $totalmodules = "SELECT COUNT(cm.id) FROM {course_modules} cm 
                                WHERE cm.course = $id AND cm.completion = 1";
                $totalmodulescount = $DB->count_records_sql($totalmodules);
                if($totalmodulescount > 1){
                    $completedmodules = "SELECT COUNT(cmc.id) 
                                FROM {course_modules_completion} cmc 
                                JOIN {course_modules} cm ON cm.id = cmc.coursemoduleid 
                                WHERE cm.course = $id AND cmc.userid = $key->id AND cmc.completionstate = 1";
                    $completedmodulescount = $DB->count_records_sql($completedmodules);

                    $courseprogress = ($completedmodulescount/$totalmodulescount*100);
                    // $courseprogress = number_format($courseprogress, 2, '.', '.');

                    if ($courseprogress == 0 || $courseprogress == 100) {
                        $courseprogress = intval($courseprogress );
                    } else {
                        $courseprogress = number_format($courseprogress, 2, '.', '.');
                    }

                    $course_completion_exists = $DB->record_exists_sql("SELECT id FROM {course_completions} cc WHERE cc.course = $id AND cc.userid = $key->id AND cc.timecompleted > 0");
                    if($course_completion_exists)
                    {
                        $courseprogress = 100;
                        $coursecompleted = get_string('completed','local_employees');
                    } else {
                        // $courseprogress = 'Inprogress';
                        $coursecompleted = get_string('tobecompleted','local_employees');
                        $coursecompleted_criteria = get_string('tobecompletedbasedoncompletioncriteria','local_employees');
                    }
                } else {
                    $criteria = true;
                    $course_completion_exists = $DB->record_exists_sql("SELECT id FROM {course_completions} cc WHERE cc.course = $id AND cc.userid = $key->id AND cc.timecompleted > 0");
                    if($course_completion_exists) {
                        $courseprogress = get_string('completed','local_employees');
                        $coursecompleted = get_string('completed','local_employees');
                    } else {
                        $courseprogress = get_string('inprogress_courses','local_employees');
                        $coursecompleted = get_string('tobecompleted','local_employees');
                        $coursecompleted_criteria = get_string('tobecompletedbasedoncompletioncriteria','local_employees');
                    }
                }
                $line = new stdClass();
                $line->statuscount=$userattended;
                $line->firstname = $key->firstname;
                $line->lastname = $key->lastname;
                $line->email = $key->email;
                $line->aa = $participantsaverages['aa'];
                $line->progress = $courseprogress;
                $line->criteria = $criteria;
                $line->coursecompleted = $coursecompleted;
                $line->coursecompleted_criteria = $coursecompleted_criteria;


                $coursesarray[]= $line;
            }
        $usersviewcontext = [
            'coursename' => $coursesarray,
            'attendance' => array_values($coursesarray),
            ];

        $output = $OUTPUT->render_from_template('local_employees/studentview', $usersviewcontext);
    return $output;*/

   if($open_identifiedas == 5 || $open_identifiedas == 3){     
        $students = "SELECT u.id,u.firstname,u.lastname,u.email,p.id as pid,p.name
                      FROM {user} u
                      JOIN {role_assignments} ra ON ra.userid = u.id
                      JOIN {role} r ON r.id = ra.roleid AND r.shortname = 'student'
                      JOIN {context} AS ctx ON ctx.id = ra.contextid
                      JOIN {course} c ON c.id = ctx.instanceid
                      JOIN {local_program_level_courses} plc ON plc.courseid=c.id
                      JOIN {local_program} p ON p.id = plc.programid
                      JOIN {cohort} co ON co.id = p.batchid
                     WHERE c.id = $id";
            $sturecords = $DB->get_records_sql($students);

            foreach ($sturecords as $key ) {
                $userattendedsql = "SELECT  COUNT(stat.id) as statuscount
                              FROM {attendance_log} al
                              JOIN {attendance_sessions} ats ON al.sessionid = ats.id
                              JOIN {attendance} a ON ats.attendanceid = a.id
                              JOIN {attendance_statuses} stat ON al.statusid = stat.id
                              JOIN {local_program_level_courses} lplc ON a.course = lplc.courseid
                              JOIN {local_program_levels} pl ON lplc.programid = pl.programid
                              AND lplc.levelid = pl.id
                             WHERE lplc.courseid = $id 
                             AND ats.teacherid=$teacherid /*AND ats.lasttakenby = $teacherid*/ AND stat.acronym IN ('P','L') 
                              AND lplc.programid = $key->pid AND al.studentid = $key->id AND DATE(FROM_UNIXTIME(ats.sessdate, '%Y-%m-%d')) < DATE(NOW())";
            
                $userattended = $DB->count_records_sql($userattendedsql);


                $totalattdencesql = "SELECT COUNT(DISTINCT(alog.id)) 
                            FROM {attendance_log} alog 
                            JOIN {attendance_sessions} ases ON ases.id = alog.sessionid 
                            JOIN {attendance} att ON att.id = ases.attendanceid 
                            WHERE att.course = $id AND ases.teacherid=$teacherid
                            /*AND ases.lasttakenby = $teacherid*/ AND alog.studentid = $key->id AND DATE(FROM_UNIXTIME(ases.sessdate, '%Y-%m-%d')) < DATE(NOW())";
                $totalattdence = $DB->count_records_sql($totalattdencesql);

                    if ($userattended && $totalattdence > 0) {
                        $percentage['aa'] = (($userattended / $totalattdence) * 100);
                    } else {
                        $percentage['aa'] = 0;
                        }
                    if ($percentage['aa'] == 0 || $percentage['aa'] == 100) {
                        $participantsaverages['aa'] = intval($percentage['aa'] );
                    } else {
                        $participantsaverages['aa'] = number_format($percentage['aa'], 2, '.', '.');
                    }


                $totalmodules = "SELECT COUNT(cm.id) FROM {course_modules} cm 
                                WHERE cm.course = $id AND cm.completion = 1";
                $totalmodulescount = $DB->count_records_sql($totalmodules);
                if($totalmodulescount > 1){
                    $completedmodules = "SELECT COUNT(cmc.id) 
                                FROM {course_modules_completion} cmc 
                                JOIN {course_modules} cm ON cm.id = cmc.coursemoduleid 
                                WHERE cm.course = $id AND cmc.userid = $key->id AND cmc.completionstate = 1";
                    $completedmodulescount = $DB->count_records_sql($completedmodules);

                    $courseprogress = ($completedmodulescount/$totalmodulescount*100);
                    // $courseprogress = number_format($courseprogress, 2, '.', '.');

                    if ($courseprogress == 0 || $courseprogress == 100) {
                        $courseprogress = intval($courseprogress );
                    } else {
                        $courseprogress = number_format($courseprogress, 2, '.', '.');
                    }

                    $course_completion_exists = $DB->record_exists_sql("SELECT id FROM {course_completions} cc WHERE cc.course = $id AND cc.userid = $key->id AND cc.timecompleted > 0");
                    if($course_completion_exists)
                    {
                        $courseprogress = 100;
                        $coursecompleted = get_string('completed','local_employees');
                    } else {
                        // $courseprogress = 'Inprogress';
                        $coursecompleted = get_string('tobecompleted','local_employees');
                        $coursecompleted_criteria = get_string('tobecompletedbasedoncompletioncriteria','local_employees');
                    }
                } else {
                    $criteria = true;
                    $course_completion_exists = $DB->record_exists_sql("SELECT id FROM {course_completions} cc WHERE cc.course = $id AND cc.userid = $key->id AND cc.timecompleted > 0");
                    if($course_completion_exists) {
                        $courseprogress = get_string('completed','local_employees');
                        $coursecompleted = get_string('completed','local_employees');
                    } else {
                        $courseprogress = get_string('inprogress_courses','local_employees');
                        $coursecompleted = get_string('tobecompleted','local_employees');
                        $coursecompleted_criteria = get_string('tobecompletedbasedoncompletioncriteria','local_employees');
                    }
                }
                $line = new stdClass();
                $line->statuscount=$userattended;
                $line->firstname = $key->firstname;
                $line->lastname = $key->lastname;
                $line->email = $key->email;
                $line->aa = $participantsaverages['aa'];
                $line->progress = $courseprogress;
                $line->criteria = $criteria;
                $line->coursecompleted = $coursecompleted;
                $line->coursecompleted_criteria = $coursecompleted_criteria;


                $coursesarray[]= $line;
            }
    }

    if($open_identifiedas == 6){

    $openstudents = "SELECT u.id,u.firstname,u.lastname,u.email 
                      FROM {user} u
                      JOIN {role_assignments} ra ON ra.userid = u.id
                      JOIN {role} r ON r.id = ra.roleid AND r.shortname = 'student'
                      JOIN {context} AS ctx ON ctx.id = ra.contextid
                      JOIN {course} c ON c.id = ctx.instanceid
                      
                     WHERE c.id = $id";
            $sturecords = $DB->get_records_sql($openstudents);

             foreach ($sturecords as $key ) {


  $userattendedsql = "SELECT  COUNT(stat.id) as statuscount
                              FROM {attendance_log} al
                              JOIN {attendance_sessions} ats ON al.sessionid = ats.id
                              JOIN {attendance} a ON ats.attendanceid = a.id
                              JOIN {attendance_statuses} stat ON al.statusid = stat.id
                              
                             AND ats.teacherid=$teacherid /*AND ats.lasttakenby = $teacherid*/ AND stat.acronym IN ('P','L') 
                                      AND al.studentid = $key->id AND DATE(FROM_UNIXTIME(ats.sessdate, '%Y-%m-%d')) < DATE(NOW())";
            
                $userattended = $DB->count_records_sql($userattendedsql);


                $totalattdencesql = "SELECT COUNT(DISTINCT(alog.id)) 
                            FROM {attendance_log} alog 
                            JOIN {attendance_sessions} ases ON ases.id = alog.sessionid 
                            JOIN {attendance} att ON att.id = ases.attendanceid 
                            WHERE att.course = $id AND ases.teacherid=$teacherid
                            /*AND ases.lasttakenby = $teacherid*/ AND alog.studentid = $key->id AND DATE(FROM_UNIXTIME(ases.sessdate, '%Y-%m-%d')) < DATE(NOW())";
                $totalattdence = $DB->count_records_sql($totalattdencesql);

                    if ($userattended && $totalattdence > 0) {
                        $percentage['aa'] = (($userattended / $totalattdence) * 100);
                    } else {
                        $percentage['aa'] = 0;
                        }
                    if ($percentage['aa'] == 0 || $percentage['aa'] == 100) {
                        $participantsaverages['aa'] = intval($percentage['aa'] );
                    } else {
                        $participantsaverages['aa'] = number_format($percentage['aa'], 2, '.', '.');
                    }


                $totalmodules = "SELECT COUNT(cm.id) FROM {course_modules} cm 
                                WHERE cm.course = $id AND cm.completion = 1";
                $totalmodulescount = $DB->count_records_sql($totalmodules);
                if($totalmodulescount > 1){
                    $completedmodules = "SELECT COUNT(cmc.id) 
                                FROM {course_modules_completion} cmc 
                                JOIN {course_modules} cm ON cm.id = cmc.coursemoduleid 
                                WHERE cm.course = $id AND cmc.userid = $key->id AND cmc.completionstate = 1";
                    $completedmodulescount = $DB->count_records_sql($completedmodules);

                    $courseprogress = ($completedmodulescount/$totalmodulescount*100);
                    // $courseprogress = number_format($courseprogress, 2, '.', '.');

                    if ($courseprogress == 0 || $courseprogress == 100) {
                        $courseprogress = intval($courseprogress );
                    } else {
                        $courseprogress = number_format($courseprogress, 2, '.', '.');
                    }

                    $course_completion_exists = $DB->record_exists_sql("SELECT id FROM {course_completions} cc WHERE cc.course = $id AND cc.userid = $key->id AND cc.timecompleted > 0");
                    if($course_completion_exists)
                    {
                        $courseprogress = 100;
                        $coursecompleted = get_string('completed','local_employees');
                    } else {
                        // $courseprogress = 'Inprogress';
                        $coursecompleted = get_string('tobecompleted','local_employees');
                        $coursecompleted_criteria = get_string('tobecompletedbasedoncompletioncriteria','local_employees');
                    }
                } else {
                    $criteria = true;
                    $course_completion_exists = $DB->record_exists_sql("SELECT id FROM {course_completions} cc WHERE cc.course = $id AND cc.userid = $key->id AND cc.timecompleted > 0");
                    if($course_completion_exists) {
                        $courseprogress = get_string('completed','local_employees');
                        $coursecompleted = get_string('completed','local_employees');
                    } else {
                        $courseprogress = get_string('inprogress_courses','local_employees');
                        $coursecompleted = get_string('tobecompleted','local_employees');
                        $coursecompleted_criteria = get_string('tobecompletedbasedoncompletioncriteria','local_employees');
                    }
                }
                 $line = new stdClass();
                $line->statuscount=$userattended;
                $line->firstname = $key->firstname;
                $line->lastname = $key->lastname;
                $line->email = $key->email;
                $line->aa = $participantsaverages['aa'];
                $line->progress = $courseprogress;
                $line->criteria = $criteria;
                $line->coursecompleted = $coursecompleted;
                $line->coursecompleted_criteria = $coursecompleted_criteria;
                $coursesarray[]= $line;
             }
    }
        $usersviewcontext = [
            'coursename' => $coursesarray,
            'attendance' => array_values($coursesarray),
            ];

 
        $output = $OUTPUT->render_from_template('local_employees/studentview', $usersviewcontext);
    return $output;
}
function local_employees_output_fragment_course_display($args) {
    global $CFG, $OUTPUT, $DB, $PAGE, $USER;
        $PAGE->set_url('/local/employees/profile.php');
        $id =$args['id'];
        $userid =$args['count'];

        $coursesql = "SELECT c.id,c.fullname,c.open_identifiedas,count(pu.userid) as countstudent,
                    p.id as programid
                      FROM {user} u
                      JOIN {role_assignments} ra ON ra.userid = u.id
                      JOIN {role} r ON r.id=ra.roleid AND r.shortname = 'editingteacher'
                      JOIN {context} AS ctx ON ctx.id = ra.contextid
                      JOIN {course} c ON c.id = ctx.instanceid
                      JOIN {local_program_level_courses} plc ON plc.courseid=c.id
                      JOIN {local_program_users} pu ON pu.programid = plc.programid
                      JOIN {local_program} p ON p.id = pu.programid
                      JOIN {cohort} co ON co.id=p.batchid
                     WHERE (p.id = $id ) AND u.id =$userid GROUP BY c.id";
        $courses = $DB->get_records_sql($coursesql);
        $enrolledcou =count($courses);

            $coursesarray = array();
            foreach ($courses as $coursekey ) {

                $userattendedsql = "SELECT COUNT(stat.id) as statuscount
                            FROM {attendance_log} al
                            JOIN {attendance_sessions} ats ON al.sessionid = ats.id
                            JOIN {attendance} a ON ats.attendanceid = a.id
                            JOIN {attendance_statuses} stat ON al.statusid = stat.id
                            JOIN {local_program_level_courses} lplc ON a.course = lplc.courseid
                            JOIN {local_program_levels} pl ON lplc.programid = pl.programid
                            AND lplc.levelid = pl.id
                            WHERE lplc.courseid = $coursekey->id 
                            AND stat.acronym IN ('P','L')
                            AND lplc.programid = $coursekey->programid";

                $userattended = $DB->count_records_sql($userattendedsql);

                $totalattdencesql = "SELECT COUNT(DISTINCT(alog.id)) 
                            FROM {attendance_log} alog 
                            JOIN {attendance_sessions} ases ON ases.id = alog.sessionid 
                            JOIN {attendance} att ON att.id = ases.attendanceid 
                            WHERE att.course = $coursekey->id";
                $totalattdence = $DB->count_records_sql($totalattdencesql);

                if ($userattended && $totalattdence > 0) {
                    $percentage = round(($userattended / $totalattdence) * 100);
                } else {
                    $percentage = 0;
                }

                $totalmodules = "SELECT COUNT(cm.id) FROM {course_modules} cm 
                                    WHERE cm.course = $coursekey->id";

                $completedmodules = "SELECT COUNT(cmc.id) FROM {user} u  
                            JOIN {course_modules_completion} cmc 
                            ON u.id = cmc.userid
                            JOIN {course_modules} cm 
                            ON cm.id = cmc.coursemoduleid 
                            WHERE cm.course = $coursekey->id 
                            AND u.id=$userid";
                $totalmodulescount = $DB->count_records_sql($totalmodules);
                $completedmodulescount = $DB->count_records_sql($completedmodules);

                $courseprogress = $completedmodulescount/$totalmodulescount*100;

                $line =array();
                $line['id'] = $coursekey->id;
                $line['fullname'] = $coursekey->fullname;
                $line['open_identifiedas'] = $coursekey->open_identifiedas;
                $line['count'] = $coursekey->countstudent;
                $line['aa'] = floor($percentage);
                $line['progress'] = floor($courseprogress);
                $coursesarray[]=$line;
            }

        $usersviewcontext = [
            // 'coursecount' => $enrolledcou,
            'coursename' => $coursesarray,
            ];

        $output = $OUTPUT->render_from_template('local_employees/coursecount', $usersviewcontext);
    return $output;
        
}

function local_employees_output_fragment_courseview_display($args) {
    global $CFG, $OUTPUT, $DB, $PAGE, $USER;
        // $PAGE->set_url('/local/employees/profile.php');
        $id =$args['id'];

    $allcoursesql = "SELECT c.id,c.fullname,c.open_identifiedas,plc.programid as programid,
                        pl.id as levelid
                        FROM {user} u
                        JOIN {role_assignments} ra ON ra.userid = u.id
                        JOIN {role} r ON r.id = ra.roleid AND r.shortname = 'editingteacher'
                        JOIN {context} ctx ON ctx.id = ra.contextid
                        JOIN {course} c ON c.id = ctx.instanceid
                        JOIN {local_program_level_courses} plc ON plc.courseid=c.id
                        JOIN {local_program_levels} pl ON pl.id = plc.levelid 
                        AND pl.programid = plc.programid
                        WHERE u.id = $id ";

                // $formsql .= " order by c.id DESC limit 10";
        // $allcourses = $DB->get_records_sql($allcoursesql.$formsql);
        $allcourse = $DB->get_records_sql($allcoursesql);
        $allenrolledcou =count($allcourse);

            foreach ($allcourse as $key => $vals) {
                $students = "SELECT u.id as sid,u.firstname,u.lastname
                      FROM {user} u
                      JOIN {role_assignments} ra ON ra.userid = u.id
                      JOIN {role} r ON r.id=ra.roleid AND r.shortname = 'student'
                      JOIN {context} ctx ON ctx.id = ra.contextid
                      JOIN {course} c ON c.id = ctx.instanceid
                      JOIN {local_program_level_courses} plc ON plc.courseid=c.id
                      JOIN {local_program} p ON p.id = plc.programid
                     WHERE c.id = $vals->id ";

                $sturecords = $DB->get_records_sql($students);
                $stucount = count($sturecords);

                $userattendedsql = "SELECT COUNT(stat.id) as statuscount
                            FROM {attendance_log} al
                            JOIN {attendance_sessions} ats ON al.sessionid = ats.id
                            JOIN {attendance} a ON ats.attendanceid = a.id
                            JOIN {attendance_statuses} stat ON al.statusid = stat.id
                            JOIN {local_program_level_courses} lplc ON a.course = lplc.courseid
                            JOIN {local_program_levels} pl ON lplc.programid = pl.programid
                            AND lplc.levelid = pl.id
                            WHERE lplc.courseid = $vals->id 
                            AND stat.acronym IN ('P','L')
                            AND lplc.programid = $vals->programid";

                $userattended = $DB->count_records_sql($userattendedsql);

                $totalattdencesql = "SELECT COUNT(DISTINCT(alog.id)) 
                            FROM {attendance_log} alog 
                            JOIN {attendance_sessions} ases ON ases.id = alog.sessionid 
                            JOIN {attendance} att ON att.id = ases.attendanceid 
                            WHERE att.course = $vals->id";
                $totalattdence = $DB->count_records_sql($totalattdencesql);

                    if ($userattended && $totalattdence > 0) {
                        $percentage = round(($userattended / $totalattdence) * 100);
                    } else {
                        $percentage = 0;
                    }


                $coursedetails = new stdClass();
                $url = $CFG->wwwroot.'/course/view.php?id='.$vals->id;

                $coursedetails->cid = $vals->id;
                $coursedetails->fullname = $vals->fullname;
                $coursedetails->open_identifiedas = $vals->open_identifiedas;
                $coursedetails->programid = $vals->programid;
                $coursedetails->studentcount = $stucount;
                $coursedetails->percentage = $percentage;
                $coursedetails->teacherid = $id;
                $coursedetails->url = $url;
                    
                $coursesarray[]=$coursedetails;
            }
            // print_r($coursesarray);die;
        $usersviewcontext = [
            // 'coursecount' => $enrolledcou,
            'records' => $coursesarray,
            ];

        $output = $OUTPUT->render_from_template('local_employees/profile_courseview', $usersviewcontext);
    return $output;
        
}
function local_employees_output_fragment_programview_display($args) {
    global $CFG, $OUTPUT, $DB, $PAGE, $USER;
        // $PAGE->set_url('/local/employees/profile.php');
        $id =$args['id'];

                $programdata = "SELECT  p.id as programid,p.name as programname,co.name as batchname,p.duration,count(c.id) as coursecount,c.id as courseid,u.id as count
                      FROM {user} u
                      JOIN {role_assignments} ra ON ra.userid = u.id
                      JOIN {role} r ON r.id=ra.roleid 
                      JOIN {context} AS ctx ON ctx.id = ra.contextid
                      JOIN {course} c ON c.id = ctx.instanceid
                      JOIN {local_program_level_courses} plc ON plc.courseid = ctx.instanceid 
                      JOIN {local_program} p ON p.id= plc.programid
                      JOIN {cohort} co ON co.id=p.batchid
                     WHERE u.id = $id and p.id = plc.programid 
                    GROUP BY p.id
                    ORDER by p.id ";

        $programdatasql=$DB->get_records_sql($programdata);
        $procounts =count($programdatasql);
        
            foreach ($programdatasql as $keys) {

                    $programdetails =new stdClass();
                    $programdetails->duration = $keys->duration;
                    $programdetails->batchname = $keys->batchname;
                    $programdetails->programname = $keys->programname;
                    $programdetails->coursecount = $keys->coursecount;
                    $programdetails->programid = $keys->programid;
                    $programdetails->courseid = $keys->courseid;
                    $programdetails->count = $keys->count;
                $programarrays[]=$programdetails;
            }

        $usersviewcontext = [
            // 'coursecount' => $enrolledcou,
            'programdetailss' => $programarrays,
            ];

        $output = $OUTPUT->render_from_template('local_employees/programview', $usersviewcontext);
    return $output;
        
}
/*
* return count of users under selected costcenter
* @return  [type] int count of users
*/
function costcenterwise_employees_count($costcenter,$department = false,$subdepartment=false){
    global $USER, $DB, $CFG;
        $params = array();
        $params['costcenter'] = $costcenter;
        $countemployeesql = "SELECT count(id) FROM {user} WHERE open_costcenterid = :costcenter AND deleted = 0 AND open_type = 0";
        if($department){
            $countemployeesql .= " AND open_departmentid = :department ";
            $params['department'] = $department;
        }
        if($subdepartment){
            $countemployeesql .= " AND open_subdepartment = :subdepartment ";
            $params['subdepartment'] = $subdepartment;
        }
        // $activesql = " AND suspended = 0 ";
        // $inactivesql = " AND suspended = 1 ";

        $countemployees = $DB->count_records_sql($countemployeesql, $params);
        // $activeusers = $DB->count_records_sql($countusersql.$activesql, $params);
        // $inactiveusers = $DB->count_records_sql($countusersql.$inactivesql, $params);
        // if ($countusers >= 0) {
        //     if ($costcenter) {
        //         $viewuserlinkurl = $CFG->wwwroot.'/local/employees/index.php?costcenterid='.$costcenter;
        //     }
        //     if ($department) {
        //         $viewuserlinkurl = $CFG->wwwroot.'/local/employees/index.php?departmentid='.$department; 
        //     } 
        //     if ($subdepartment) {
        //         $viewuserlinkurl = $CFG->wwwroot.'/local/employees/index.php?subdepartmentid='.$subdepartment; 
        //     } 
        // }
        // if ($activeusers >= 0) {
        //     if ($costcenter) {
        //         $countuseractivelinkurl = $CFG->wwwroot.'/local/employees/index.php?status=active&costcenterid='.$costcenter;
        //     }
        //     if ($department) {
        //         $countuseractivelinkurl = $CFG->wwwroot.'/local/employees/index.php?status=active&departmentid='.$department; 
        //     }
        //     if ($subdepartment) {
        //         $countuseractivelinkurl = $CFG->wwwroot.'/local/employees/index.php?status=active&subdepartmentid='.$subdepartment; 
        //     }
        // }
        // if ($inactiveusers >= 0) {
        //     if ($costcenter) {
        //         $countuserinactivelinkurl = $CFG->wwwroot.'/local/employees/index.php?status=inactive&costcenterid='.$costcenter;
        //     }
        //     if ($department) {
        //         $countuserinactivelinkurl = $CFG->wwwroot.'/local/employees/index.php?status=inactive&departmentid='.$department; 
        //     }
        //     if ($subdepartment) {
        //         $countuserinactivelinkurl = $CFG->wwwroot.'/local/employees/index.php?status=inactive&subdepartmentid='.$subdepartment; 
        //     }
        // }
    return array(
        'totalemployees' => $countemployees
        // 'activeusercount' => $activeusers,
        // 'inactiveusercount' => $inactiveusers,
        // 'viewuserlink_url' => $viewuserlinkurl,
        // 'count_useractivelink_url' => $countuseractivelinkurl,
        // 'count_userinactivelink_url' => $countuserinactivelinkurl
    );
}
