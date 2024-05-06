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
 * @subpackage local_users
 */

use local_users\output\team_status_lib;

defined('MOODLE_INTERNAL') || die;
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot.'/user/editlib.php');
/**
 * Description: To display the form in modal on modal trigger event.
 * @param  [array] $args [the parameters required for the form]
 * @return        [modal content]
 */
function local_users_output_fragment_new_create_user($args){
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
            // $description= $data->description;
            // unset($data->description);
            // $data->description['text'] =$description;
           
        }
        $description = $data->description;
            $data->description = array();
            $data->description['text'] =$description;
        unset($data->password);
        useredit_load_preferences($data);
        $mform = new local_users\forms\create_user(null, array('editoroptions' => $editoroptions,'form_status' => $args->form_status,'id' => $data->id,'open_costcenterid'=>$data->open_costcenterid,'deptid'=>$data->open_departmentid,'subdept'=>$data->open_subdepartment), 'post', '', null, true, $formdata);
        $mform->set_data($data);
    }
    else{
        $mform = new local_users\forms\create_user(null, array('editoroptions' => $editoroptions,'form_status' => $args->form_status), 'post', '', null, true, $formdata);    
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
    $renderer = $PAGE->get_renderer('local_users');
    ob_start();
    $formstatus = array();
    foreach (array_values($mform->formstatus) as $k => $mformstatus) {
        $activeclass = $k == $args->form_status ? 'active' : '';
        $formstatus[] = array('name' => $mformstatus, 'activeclass' => $activeclass);
    }
    $formstatusview = new \local_users\output\form_status($formstatus);
    $o .= $renderer->render($formstatusview);
    $mform->display();
    $o .= ob_get_contents();
    ob_end_clean();
    return $o;
}
/**
 * Description: User fullname filter code
 * @param  [mform object]  $mform          [the form object where the form is initiated]
 * @param  string  $query          [text inserted in filter]
 * @param  boolean $searchanywhere [description]
 * @param  integer $page           [page value]
 * @param  integer $perpage        [entities per page]
 */
function users_filter($mform,$query='',$searchanywhere=false, $page=0, $perpage=25){
    global $DB, $USER;

    $systemcontext = context_system::instance();
    $userslist=array();
    $data=data_submitted();
    $userslistparams = array('adminuserid' => 2, 'deleted' => 0, 'suspended' => 0, 'userid' => $USER->id);
    if(is_siteadmin() || has_capability('local/costcenter:manage_multiorganizations', $systemcontext)){
        $userslist_sql="SELECT id, concat(firstname,' ',lastname) as fullname FROM {user} WHERE id > :adminuserid AND deleted = :deleted AND suspended = :suspended AND id <> :userid  ";

    }else if(has_capability('local/costcenter:manage_ownorganization', $systemcontext)){
        $userslist_sql="SELECT id, concat(firstname,' ',lastname) as fullname FROM {user} WHERE id > :adminuserid AND open_costcenterid = :costcenterid AND deleted = :deleted AND suspended = :suspended AND id <> :userid  ";
        $userslistparams['costcenterid'] = $USER->open_costcenterid;
    }else if(has_capability('local/costcenter:manage_owndepartments', $systemcontext)){
        $userslist_sql="SELECT id, concat(firstname,' ',lastname) as fullname FROM {user} WHERE id > :adminuserid AND open_costcenterid = :costcenterid AND open_departmentid = :departmentid AND deleted = :deleted AND suspended = :suspended AND id <> :userid ";
        $userslistparams['costcenterid'] = $USER->open_costcenterid;
        $userslistparams['departmentid'] = $USER->open_departmentid;
    }
    if(!empty($query)){ 
        if ($searchanywhere) {
            $likesql = $DB->sql_like("CONCAT(firstname, ' ',lastname)", "'%$query%'", false);
            $userslist_sql .= " AND $likesql ";
        } else {
            $likesql = $DB->sql_like("CONCAT(firstname, ' ',lastname)", "'$query%'", false);
            $userslist_sql .= " AND $likesql ";
        }
    }
    if(isset($data->users)&&!empty(($data->users))){
        list($usersql, $userparam) = $DB->get_in_or_equal($data->users, SQL_PARAMS_NAMED);
        $userslist_sql .= " AND id $usersql ";
        $userslistparams = $userparam + $userslistparams;
    }
    if(!empty($query)||empty($mform)){ 
        $userslist = $DB->get_records_sql($userslist_sql, $userslistparams, $page, $perpage);
        return $userslist;
    }
    if((isset($data->users)&&!empty($data->users))){ 
         $userslist = $DB->get_records_sql_menu($userslist_sql, $userslistparams, $page, $perpage);
    }
    $options = array(
                    'ajax' => 'local_courses/form-options-selector',
                    'multiple' => true,
                    'data-action' => 'users',
                    'data-options' => json_encode(array('id' => 0)),
                    'placeholder' => get_string('users')
    );
    $select = $mform->addElement('autocomplete', 'users', '',$userslist,$options);
    $mform->setType('users', PARAM_RAW);
}
/**
 * Description: User email filter code
 * @param  [mform object]  $mform          [the form object where the form is initiated]
 * @param  string  $query          [text inserted in filter]
 * @param  boolean $searchanywhere [description]
 * @param  integer $page           [page value]
 * @param  integer $perpage        [entities per page]
 */
function email_filter($mform,$query='',$searchanywhere=false, $page=0, $perpage=25){
    global $DB, $USER;
    $systemcontext = context_system::instance();
    $userslist=array();
    $data=data_submitted();
    $userslistparams = array('adminuserid' => 2, 'deleted' => 0, 'suspended' => 0,'userid' => $USER->id, 'open_type' => 1);
    if(is_siteadmin() || has_capability('local/costcenter:manage_multiorganizations', $systemcontext)){
        $userslist_sql="SELECT id, email as fullname 
                    FROM {user} WHERE id > :adminuserid 
                    AND deleted = :deleted 
                    AND id <> :userid 
                    AND open_type = :open_type
                    AND suspended = :suspended ";
    }else if(!is_siteadmin() && has_capability('local/costcenter:manage_ownorganization', $systemcontext)){
        $userslist_sql="SELECT id, email as fullname 
                    FROM {user} WHERE id > :adminuserid 
                    AND open_costcenterid = :costcenterid 
                    AND deleted = :deleted 
                    AND open_type = :open_type 
                    AND suspended = :suspended";
        $userslistparams['costcenterid'] = $USER->open_costcenterid;
    }else if(!is_siteadmin() && has_capability('local/costcenter:manage_owndepartments', $systemcontext)){
        $userslist_sql="SELECT id, email as fullname 
                    FROM {user} WHERE id > :adminuserid 
                    AND open_costcenterid = :costcenterid 
                    AND open_departmentid = :departmentid 
                    AND deleted = :deleted
                    AND open_type = :open_type
                    AND suspended = :suspended";
        $userslistparams['costcenterid'] = $USER->open_costcenterid;
        $userslistparams['departmentid'] = $USER->open_departmentid;
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
    if(isset($data->email)&&!empty(($data->email))){
        $implode=implode(',',$data->email);
        list($mailsql, $mailparam) = $DB->get_in_or_equal($data->email, SQL_PARAMS_NAMED);
        $userslist_sql .= " AND id $mailsql ";
        $userslistparams = $mailparam + $userslistparams;
    }
    if(!empty($query)||empty($mform)){ 
        $userslist = $DB->get_records_sql($userslist_sql, $userslistparams, $page, $perpage);
        return $userslist;
    }
    if((isset($data->email)&&!empty($data->email))){ 
        $userslist = $DB->get_records_sql_menu($userslist_sql, $userslistparams, $page, $perpage);
    }
    $options = array(
        'ajax' => 'local_courses/form-options-selector',
        'multiple' => true,
        'data-action' => 'email',
        'data-options' => json_encode(array('id' => 0)),
        'placeholder' => get_string('email')
    );
    $select = $mform->addElement('autocomplete', 'email', '',$userslist,$options);
    $mform->setType('email', PARAM_RAW);
}
/**
 * Description: User employeeid filter code
 * @param  [mform object]  $mform          [the form object where the form is initiated]
 * @param  string  $query          [text inserted in filter]
 * @param  boolean $searchanywhere [description]
 * @param  integer $page           [page value]
 * @param  integer $perpage        [entities per page]
 */
function employeeid_filter($mform,$query='',$searchanywhere=false, $page=0, $perpage=25){
    global $DB, $USER;
    $systemcontext = context_system::instance();
    $userslist=array();
    $data=data_submitted();
    $userslistparams = array('adminuserid' => 2, 'deleted' => 0, 'suspended' => 0, 'userid' => $USER->id);
    if(is_siteadmin() || has_capability('local/costcenter:manage_multiorganizations', $systemcontext)){
        $userslist_sql="SELECT id, open_employeeid as fullname 
                    FROM {user} WHERE id > :adminuserid 
                    AND deleted = :deleted AND suspended = :suspended 
                    AND id <> :userid";
    }else if(has_capability('local/costcenter:manage_ownorganization', $systemcontext)){
        $userslist_sql="SELECT id, open_employeeid as fullname 
                    FROM {user} WHERE id > :adminuserid 
                    AND open_costcenterid = :costcenterid 
                    AND deleted = :deleted AND suspended = :suspended
                    AND id <> :userid";
        $userslistparams['costcenterid'] = $USER->open_costcenterid;
    }else if(has_capability('local/costcenter:manage_owndepartments', $systemcontext)){
        $userslist_sql="SELECT id, open_employeeid as fullname 
                    FROM {user} WHERE id > :adminuserid 
                    AND open_costcenterid = :costcenterid 
                    AND open_departmentid = :departmentid 
                    AND deleted = :deleted AND suspended = :suspended 
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
    if(isset($data->idnumber)&&!empty(($data->idnumber))){
        list($idsql, $idparam) = $DB->get_in_or_equal($data->idnumber, SQL_PARAMS_NAMED);
        $userslist_sql .= " AND id $idsql ";
        $userslistparams = $idparam + $userslistparams;
    }
    if(!empty($query)||empty($mform)){ 
        $userslist = $DB->get_records_sql($userslist_sql, $userslistparams, $page, $perpage);
        return $userslist;
    }
    if((isset($data->idnumber)&&!empty($data->idnumber))){ 
        $userslist = $DB->get_records_sql_menu($userslist_sql,$userslistparams);
    }
    $options = array(
        'ajax' => 'local_courses/form-options-selector',
        'multiple' => true,
        'data-action' => 'employeeid',
        'data-options' => json_encode(array('id' => 0)),
        'placeholder' => get_string('idnumber','local_users')
    );
}
/**
 * Description: User designation filter code
 * @param  [mform object]  $mform          [the form object where the form is initiated]
 */
function designation_filter($mform){
    global $DB, $USER;
    $systemcontext = context_system::instance();
    $userslistparams = array('adminuserid' => 2, 'deleted' => 0, 'suspended' => 0, 'userid' => $USER->id);
    if(is_siteadmin() || has_capability('local/costcenter:manage_multiorganizations', $systemcontext)){
        $userslist_sql = "SELECT id FROM {user} 
                        WHERE id > :adminuserid AND deleted = :deleted 
                        AND suspended = :suspended AND id <> :userid";
    }else if(has_capability('local/costcenter:manage_ownorganization', $systemcontext)){
        $userslist_sql = "SELECT id FROM {user} 
                        WHERE id > :adminuserid AND open_costcenterid = :costcenterid AND deleted = :deleted 
                        AND suspended = :suspended AND id <> :userid";
        $userslistparams['costcenterid'] = $USER->open_costcenterid;
    }else if(has_capability('local/costcenter:manage_owndepartments', $systemcontext)){
        $userslist_sql = "SELECT id FROM {user} 
                        WHERE id > :adminuserid AND open_costcenterid = :costcenterid AND open_departmentid = :departmentid AND deleted = deleted 
                        AND suspended = :suspended AND id <> :userid";
        $userslistparams['costcenterid'] = $USER->open_costcenterid;
        $userslistparams['departmentid'] = $USER->open_departmentid;
    }
    $userslist = $DB->get_records_sql_menu($userslist_sql, $userslistparams);
    $select = $mform->addElement('autocomplete', 'designation', '', $userslist, array('placeholder' => get_string('designation','local_users')));
    $mform->setType('idnumber', PARAM_RAW);
    $select->setMultiple(true);
}
/**
 * Description: User location filter code
 * @param  [mform object]  $mform          [the form object where the form is initiated]
 */
function location_filter($mform){
    global $DB, $USER;
    $systemcontext = context_system::instance();
    $userslistparams = array('adminuserid' => 2, 'deleted' => 0, 'suspended' => 0, 'userid' => $USER->id);
    if(is_siteadmin() || has_capability('local/costcenter:manage_multiorganizations', $systemcontext)){
        $userslist_sql = "SELECT u.city, u.city AS name 
                        FROM {user} AS u WHERE u.id > :adminuserid 
                        AND u.deleted = :deleted 
                        AND u.suspended = :suspended AND u.id <> :userid ";
    }else if(has_capability('local/costcenter:manage_ownorganization', $systemcontext)){
        $userslist_sql = "SELECT u.city, u.city AS name 
                        FROM {user} AS u WHERE u.id > :adminuserid 
                        AND u.open_costcenterid = :costcenterid 
                        AND u.deleted = :deleted 
                        AND u.suspended = :suspended AND u.id <> :userid ";
        $userslistparams['costcenterid'] =  $USER->open_costcenterid;
    }else if(has_capability('local/costcenter:manage_owndepartments', $systemcontext)){
        $userslist_sql = "SELECT u.city, u.city AS name 
                        FROM {user} AS u WHERE u.id > :adminuserid 
                        AND u.open_costcenterid = :costcenterid 
                        AND u.open_departmentid = :departmentid 
                        AND u.deleted = :deleted 
                        AND u.suspended = :suspended AND u.id <> :userid";
        $userslistparams['costcenterid'] =  $USER->open_costcenterid;
        $userslistparams['departmentid'] =  $USER->open_departmentid;
    }
    $userslist_sql .= " AND u.city != '' AND u.city IS NOT NULL GROUP BY u.city ";
    $userslist = $DB->get_records_sql_menu($userslist_sql, $userslistparams);
    $select = $mform->addElement('autocomplete', 'location', '', $userslist, array('placeholder' => get_string('location','local_users')));
    $mform->setType('idnumber', PARAM_RAW);
    $select->setMultiple(true);
}

/**
 * Description: User hrmsrole filter code
 * @param  [mform object]  $mform[the form object where the form is initiated]
 */
function hrmsrole_filter($mform){
    global $DB, $USER;
    $systemcontext = context_system::instance();
    $userslistparams = array('visible' => 0);
        $userslist_sql = "SELECT id,name FROM {local_hrmsroles} WHERE visible = :visible ";
    $userslist = $DB->get_records_sql_menu($userslist_sql, $userslistparams);
    $select = $mform->addElement('autocomplete', 'hrmsrole', '', $userslist, array('placeholder' => get_string('open_hrmsrole','local_users')));
    $mform->setType('hrmsrole', PARAM_RAW);
    $select->setMultiple(true);
}

/**
 * Description: User band filter code
 * @param  [mform object]  $mform[the form object where the form is initiated]
 */
function band_filter($mform){
    global $DB, $USER;
    $systemcontext = context_system::instance();
    $userslistparams = array('adminuserid' => 2, 'deleted' => 0, 'suspended' => 0, 'userid' => $USER->id);
    if(is_siteadmin() || has_capability('local/costcenter:manage_multiorganizations', $systemcontext)){
        $userslist_sql = "SELECT id, open_band 
                        FROM {user} WHERE id > :adminuserid 
                        AND deleted = :deleted 
                        AND suspended = :suspended 
                        AND id <> :userid";
    }else if(has_capability('local/costcenter:manage_ownorganization', $systemcontext)){
        $userslist_sql = "SELECT id, open_band 
                        FROM {user} WHERE id > :adminuserid 
                        AND open_costcenterid = :costcenterid 
                        AND deleted = :deleted 
                        AND suspended = :suspended 
                        AND id <> :userid";
        $userslistparams['costcenterid'] = $USER->open_costcenterid;
    }else if(has_capability('local/costcenter:manage_owndepartments', $systemcontext)){
        $userslist_sql = "SELECT id, open_band 
                        FROM {user} WHERE id > :adminuserid 
                        AND open_costcenterid = :costcenterid 
                        AND open_departmentid = :departmentid 
                        AND deleted = :deleted 
                        AND suspended = :suspended 
                        AND id <> :userid ";
        $userslistparams['costcenterid'] = $USER->open_costcenterid;
        $userslistparams['departmentid'] = $USER->open_departmentid;
    }
    $userslist = $DB->get_records_sql_menu($userslist_sql, $userslistparams);
    $select = $mform->addElement('autocomplete', 'band', '', $userslist, array('placeholder' => get_string('band','local_users')));
    $mform->setType('idnumber', PARAM_RAW);
    $select->setMultiple(true);
}
/**
 * Description: User name filter code
 * @param  [mform object]  $mform[the form object where the form is initiated]
 */
function username_filter($mform){
    global $DB, $USER;
    $systemcontext = context_system::instance();
    $userslistparams = array('adminuserid' => 2, 'deleted' => 0, 'suspended' => 0, 'userid' => $USER->id);
    if(is_siteadmin() || has_capability('local/costcenter:manage_multiorganizations', $systemcontext)){
        $userslist_sql = "SELECT id, username FROM {user} WHERE id > :adminuserid AND deleted = :deleted AND suspended = :suspended AND id <> :userid";
    }else if(has_capability('local/costcenter:manage_ownorganization', $systemcontext)){
        $userslist_sql = "SELECT id, username 
                        FROM {user} WHERE id > :adminuserid 
                        AND open_costcenterid = :costcenterid 
                        AND deleted = :deleted 
                        AND suspended = :suspended 
                        AND id <> :userid";
        $userslistparams['costcenterid'] = $USER->open_costcenterid;
    }else if(has_capability('local/costcenter:manage_owndepartments', $systemcontext)){
        $userslist_sql = "SELECT id, username 
                        FROM {user} WHERE id > :adminuserid 
                        AND open_costcenterid = :costcenterid 
                        AND open_departmentid = :departmentid 
                        AND deleted = :deleted 
                        AND suspended = :suspended 
                        AND id <> :userid ";
        $userslistparams['costcenterid'] = $USER->open_costcenterid;
        $userslistparams['departmentid'] = $USER->open_departmentid;
    }
    $userslist = $DB->get_records_sql_menu($userslist_sql, $userslistparams);
    $select = $mform->addElement('autocomplete', 'username', '',$userslist, array('placeholder' => get_string('username')));
    $mform->setType('username', PARAM_RAW);
    $select->setMultiple(true);
}
/**
 * Description: User custom filter code
 * @param  [mform object]  $mform[the form object where the form is initiated]
 */
function custom_filter($mform){
    global $DB, $USER;
    $systemcontext = context_system::instance();
    $filterv=$DB->get_field('local_filters','filters',array('plugins'=>'users'));
    $filterv=explode(',',$filterv);
    foreach($filterv as $fieldvalue){
        $userslistparams = array('adminuserid' => 2, 'deleted' => 0, 'suspended' => 0, 'userid' => $USER->id);
        if(is_siteadmin() || has_capability('local/costcenter:manage_multiorganizations', $systemcontext)){
            $userslist_sql = "SELECT id, $fieldvalue 
                            FROM {user} WHERE id > :adminuserid 
                            AND deleted = :deleted 
                            AND suspended = :suspended 
                            AND id <> :userid ";
        }else if(has_capability('local/costcenter:manage_ownorganization', $systemcontext)){
            $userslist_sql = "SELECT id, $fieldvalue 
                            FROM {user} WHERE id > :adminuserid 
                            AND open_costcenterid = :costcenterid 
                            AND deleted = :deleted 
                            AND suspended = :suspended 
                            AND id <> :userid ";
            $userslistparams['costcenterid'] = $USER->open_costcenterid;
        }else if(has_capability('local/costcenter:manage_owndepartments', $systemcontext)){
            $userslist = $DB->get_records_sql_menu("SELECT id, $fieldvalue 
                                    FROM {user} WHERE id > :adminuserid 
                                    AND open_costcenterid = :costcenterid 
                                    AND open_departmentid = :departmentid 
                                    AND deleted = :deleted 
                                    AND suspended = :suspended 
                                    AND id <> :userid ");
            $userslistparams['costcenterid'] = $USER->open_costcenterid;
            $userslistparams['departmentid'] = $USER->open_departmentid;
        }
        $userslist = $DB->get_records_sql_menu($userslist_sql, $userslistparams);
        $select = $mform->addElement('autocomplete', $fieldvalue, '', $userslist, array('placeholder' => get_string($fieldvalue,'local_users')));
        $mform->setType($fieldvalue, PARAM_RAW);
        $select->setMultiple(true);
    }
}
// Add Target Audience to Classrooms//
/**
 * [globaltargetaudience_elementlist description]
 * @param  [type] $mform       [description]
 * @param  [type] $elementlist [description]
 * @return [type]              [description]
 */
function globaltargetaudience_elementlist($mform,$elementlist){
    global $CFG, $DB, $USER;

    $context = context_system::instance();
    $params = array();
    $params['deleted'] = 0;
    $params['suspended'] = 0;
    if($mform->modulecostcenter == 0 && (is_siteadmin()||has_capability('local/costcenter:manage_multiorganizations',$context))){  
        $main_sql="";       
    }elseif(has_capability('local/costcenter:manage_ownorganization',$context)){
         $costcenterid = $mform->modulecostcenter ? $mform->modulecostcenter : $USER->open_costcenterid;  
        $main_sql=" AND u.suspended = :suspended AND u.deleted =:deleted  AND u.open_costcenterid = :costcenterid ";
        $params['costcenterid'] = $costcenterid;
    }else if(has_capability('local/costcenter:manage_owndepartments',$context)){
        $main_sql=" AND u.suspended = :suspended AND u.deleted = :deleted  AND u.open_costcenterid = :costcenterid AND u.open_departmentid = :departmentid ";
        $params['costcenterid'] = $USER->open_costcenterid;
        $params['departmentid'] = $USER->open_departmentid;
    }
    $dbman = $DB->get_manager();
    if (in_array('group', $elementlist)){
        $groupslist[null]=get_string('all');
        if(is_siteadmin() || has_capability('local/costcenter:manage_multiorganizations',$context) ){  
            if($dbman->table_exists('local_groups')){   
                $groupslist += $DB->get_records_sql_menu("SELECT c.id, c.name FROM {local_groups} g, {cohort} c  WHERE c.visible = :visible AND c.id = g.cohortid ",array('visible' => 1));
            }
        }else if(has_capability('local/costcenter:manage_ownorganization', $context)){
            $groupslist+= $DB->get_records_sql_menu("SELECT c.id, c.name FROM {local_groups} g, {cohort} c  WHERE c.visible = :visible AND c.id = g.cohortid AND g.costcenterid = :costcenterid ",array('costcenterid' => $USER->open_costcenterid,'visible' => 1));
        }else if(has_capability('local/costcenter:manage_owndepartments', $context)){
            $groupslist+= $DB->get_records_sql_menu("SELECT c.id, c.name FROM {local_groups} g, {cohort} c  WHERE c.visible = 1 AND c.id = g.cohortid AND g.costcenterid = :costcenterid AND  g.departmentid = :departmentid ",array('costcenterid' => $USER->open_costcenterid,'departmentid' => $USER->open_departmentid,'visible' => 1));
        }
        $selectgroup = $mform->addElement('autocomplete',  'open_group',  get_string('open_group', 'local_users'),$groupslist);
        $mform->setType('open_group', PARAM_RAW);
        $mform->addHelpButton('open_group','groups','local_users');
        $selectgroup->setMultiple(true); 
    }            
    if (in_array('branch', $elementlist)){
        $branch_details[null]=get_string('all');
        $branch_sql = "SELECT u.open_branch,u.open_branch AS branchvalue FROM {user} AS u WHERE u.id > 2 $main_sql AND u.open_branch IS NOT NULL GROUP BY u.open_branch";
        $branch_details+= $DB->get_records_sql_menu($branch_sql,$params);
        $selectbranch = $mform->addElement('autocomplete',  'open_branch',  get_string('open_branch', 'local_users'), $branch_details);
        $mform->setType('open_branch', PARAM_RAW);
        $selectbranch->setMultiple(true);
    }   
}

/*
* Author Rizwana
* Displays a node in left side menu
* @return  [type] string  link for the leftmenu
*/
function local_users_leftmenunode(){
    global $USER, $DB;
    $systemcontext = context_system::instance();
    $key=array();
    $usersnode = '';
     if(has_capability('local/users:manage',$systemcontext) || has_capability('local/users:view',$systemcontext) || is_siteadmin()) {
        $usersnode .= html_writer::start_tag('li', array('id'=> 'id_leftmenu_users', 'class'=>'pull-left user_nav_div users'));
            $users_url = new moodle_url('/local/users/index.php');
            $users = html_writer::link($users_url, '<span class="user_wht_structure_icon dypatil_cmn_icon icon"></span><span class="user_navigation_link_text">'.get_string('manage_users','local_users').'</span>',array('class'=>'user_navigation_link'));
            $usersnode .= $users;
        $usersnode .= html_writer::end_tag('li');
        $key=array('2' => $usersnode);
    }
    return $key;
}

function local_users_quicklink_node(){
    global $DB, $PAGE, $USER, $CFG,$OUTPUT;
    $systemcontext = context_system::instance();
    if(is_siteadmin() || has_capability('local/users:view',$systemcontext)){
        $sql = "SELECT count(u.id) FROM {user} u 
            JOIN {local_costcenter} lc ON lc.id=u.open_costcenterid 
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

        if(is_siteadmin() || has_capability('local/costcenter:manage_multiorganizations',$systemcontext)){
            $sql .= "";
        }else if(has_capability('local/costcenter:manage_ownorganization',$systemcontext)){
            //costcenterid concating
            $sql .= " AND u.open_costcenterid = :costcenterid ";
            $params['costcenterid'] =  $USER->open_costcenterid;
            $activeparams['costcenterid'] =  $USER->open_costcenterid;
            $inactiveparams['costcenterid'] =  $USER->open_costcenterid;
        }else if(has_capability('local/costcenter:manage_owndepartments',$systemcontext)){
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
        $local_users = $PAGE->requires->js_call_amd('local_users/newuser', 'load', array());

        $countinformation = array();
        $displayline = false;
        $hascapablity = false;

        if(has_capability('local/users:create',$systemcontext) || is_siteadmin()){
            $displayline = true;
            $hascapablity = true;
            $countinformation['create_element'] = html_writer::link('javascript:void(0)', get_string('create'), array('class'=>'quick_nav_link goto_local_users course_extended_menu_itemlink', 'data-action' => 'createusermodal', 'title' => get_string('createuser', 'local_users'), 'data-action' => 'createusermodal',  'onclick' => '(function(e){ require("local_users/newuser").init({selector:"createusermodal", context:1, userid:'.$USER->id.', form_status:0}) })(event)'));
        }
        $countinformation['node_header_string'] = get_string('manage_br_users', 'local_users');
        $countinformation['pluginname'] = 'users';
        $countinformation['plugin_icon_class'] = 'fa fa-user-plus';
        $countinformation['quicknav_icon_class'] = 'quicknav_icon_user';
        $countinformation['contextid'] = $systemcontext->id;
        $countinformation['userid'] = $USER->id;
        $countinformation['create'] = $hascapablity;
        $countinformation['viewlink_url'] = $CFG->wwwroot.'/local/users/index.php'; 
        $countinformation['view'] = TRUE; 
        $countinformation['displaystats'] = TRUE;
        $countinformation['percentage'] = $percent;
        $countinformation['count_total'] = $count_users;
        $countinformation['count_inactive'] = $count_inactiveusers;
        $countinformation['inactive_string'] = get_string('inactive_string', 'block_quick_navigation');
        $countinformation['count_active'] = $count_activeusers;
        $countinformation['space_count'] = 'two';
        $local_users .= $OUTPUT->render_from_template('block_quick_navigation/quicklink_node', $countinformation);
        }
    return array('1' => $local_users);
}

/*
* return count of users under selected costcenter
* @return  [type] int count of users
*/
function costcenterwise_users_count($costcenter,$department = false,$subdepartment=false){
    global $USER, $DB, $CFG;
        $params = array();
        $params['costcenter'] = $costcenter;
        $countusersql = "SELECT count(id) FROM {user} WHERE open_costcenterid = :costcenter AND deleted = 0 AND open_type = 1";
        if($department){
            $countusersql .= " AND open_departmentid = :department ";
            $params['department'] = $department;
        }
        if($subdepartment){
            $countusersql .= " AND open_subdepartment = :subdepartment ";
            $params['subdepartment'] = $subdepartment;
        }
        $activesql = " AND suspended = 0 ";
        $inactivesql = " AND suspended = 1 ";

        $countusers = $DB->count_records_sql($countusersql, $params);
        $activeusers = $DB->count_records_sql($countusersql.$activesql, $params);
        $inactiveusers = $DB->count_records_sql($countusersql.$inactivesql, $params);
        if ($countusers >= 0) {
            if ($costcenter) {
                $viewuserlinkurl = $CFG->wwwroot.'/local/users/index.php?costcenterid='.$costcenter;
            }
            if ($department) {
                $viewuserlinkurl = $CFG->wwwroot.'/local/users/index.php?departmentid='.$department; 
            } 
            if ($subdepartment) {
                $viewuserlinkurl = $CFG->wwwroot.'/local/users/index.php?subdepartmentid='.$subdepartment; 
            } 
        }
        if ($activeusers >= 0) {
            if ($costcenter) {
                $countuseractivelinkurl = $CFG->wwwroot.'/local/users/index.php?status=active&costcenterid='.$costcenter;
            }
            if ($department) {
                $countuseractivelinkurl = $CFG->wwwroot.'/local/users/index.php?status=active&departmentid='.$department; 
            }
            if ($subdepartment) {
                $countuseractivelinkurl = $CFG->wwwroot.'/local/users/index.php?status=active&subdepartmentid='.$subdepartment; 
            }
        }
        if ($inactiveusers >= 0) {
            if ($costcenter) {
                $countuserinactivelinkurl = $CFG->wwwroot.'/local/users/index.php?status=inactive&costcenterid='.$costcenter;
            }
            if ($department) {
                $countuserinactivelinkurl = $CFG->wwwroot.'/local/users/index.php?status=inactive&departmentid='.$department; 
            }
            if ($subdepartment) {
                $countuserinactivelinkurl = $CFG->wwwroot.'/local/users/index.php?status=inactive&subdepartmentid='.$subdepartment; 
            }
        }
    return array(
        'totalusers' => $countusers,
        'activeusercount' => $activeusers,
        'inactiveusercount' => $inactiveusers,
        'viewuserlink_url' => $viewuserlinkurl,
        'count_useractivelink_url' => $countuseractivelinkurl,
        'count_userinactivelink_url' => $countuserinactivelinkurl
    );
}

/*
* return count of users under selected costcenter
* @return  [type] int count of users
*/
function manage_users_count($stable,$filterdata) {
    global $DB, $PAGE,$USER,$CFG,$OUTPUT;
    $systemcontext = context_system::instance();
    $countsql = "SELECT  count(u.id) "; 
    $selectsql = "SELECT  u.* ,lc.fullname AS costcentername ,(SELECT fullname FROM {local_costcenter} WHERE id=u.open_departmentid) AS departmentname ";
    if(isset($filterdata->program)){
        $filterdata->program = str_replace('_qf__force_multiselect_submission', '', $filterdata->program); 
    }
    if(isset($filterdata->groups)){
        $filterdata->groups = str_replace('_qf__force_multiselect_submission', '', $filterdata->groups); 
    }
    if(!empty($filterdata->program)){
    $formsql   =" FROM {user} AS u 
        JOIN {local_costcenter} AS lc ON lc.id=u.open_costcenterid 
        JOIN {local_program_users} as pu  ON u.id=pu.userid
        JOIN {local_program} AS p on p.id=pu.programid
        JOIN {cohort_members} AS cm ON cm.userid=u.id 
        WHERE (u.id > 2 AND u.deleted = 0) AND (u.open_type=1)";
    }else if(!empty($filterdata->groups)){
        $formsql   =" FROM {user} AS u 
        JOIN {local_costcenter} AS lc ON lc.id=u.open_costcenterid 
        JOIN {cohort_members} AS cm ON cm.userid=u.id 
        WHERE (u.id > 2 AND u.deleted = 0) AND (u.open_type=1)";
    }else{
        $formsql   =" FROM {user} AS u 
        JOIN {local_costcenter} AS lc ON lc.id=u.open_costcenterid 
        WHERE (u.id > 2 AND u.deleted = 0) AND (u.open_type=1)";
    }
    $params = array();
    if(is_siteadmin() || has_capability('local/costcenter:manage_multiorganizations', $systemcontext)){
        $formsql .= "";
    }else if(!is_siteadmin() && has_capability('local/costcenter:manage_ownorganization', $systemcontext)){
        $formsql .= " AND open_costcenterid = :costcenterid ";
        $params['costcenterid'] = $USER->open_costcenterid;
    }else if(!is_siteadmin() && has_capability('local/costcenter:manage_owndepartments', $systemcontext)){
        $formsql .= " AND open_costcenterid = :costcenterid AND open_departmentid = :departmentid ";
        $params['costcenterid'] = $USER->open_costcenterid;
        $params['departmentid'] = $USER->open_departmentid;
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
    // Program filter
    if (!empty($filterdata->program)) {
        $filteredprograms = array_filter(explode(',', $filterdata->program), 'is_numeric');
        if(!empty($filteredprograms)) {
            $programarray = array();
            foreach($filteredprograms as $key => $value) {
                $programarray[] = "p.id = $value"; 
            }
            $programimplode = implode(' OR ', $programarray);
            $formsql .= " AND ($programimplode) ";
        }
    }
    // Organization filter
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
    // Department filter
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
    // Batch filter
    if(!empty($filterdata->groups)){
        $groups = explode(',',$filterdata->groups);
        $groups = array_filter($groups, function($value){
                if($value != '_qf__force_multiselect_submission'){
                    return $value;
                }
        });
        if($groups != NULL) {
            list($relatedgroupssql, $relatedgroupsparams) = $DB->get_in_or_equal($groups, SQL_PARAMS_NAMED, 'groups');
            $params = array_merge($params,$relatedgroupsparams);
            $formsql .= " AND cm.cohortid $relatedgroupssql";
        }
    }
    // Subdepartment filter
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
    if (!empty($filterdata->hrmsrole)) {

        $hrmsroles = explode(',',$filterdata->hrmsrole);
        if($hrmsroles != NULL) {
            list($hrmsrolesql, $hrmsroleparams) = $DB->get_in_or_equal($hrmsroles, SQL_PARAMS_NAMED, 'hrmsrole');
            $params = array_merge($params,$hrmsroleparams);            
            $formsql .= " AND u.open_hrmsrole {$hrmsrolesql} ";
        }
    }
    // Status filter
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
function manage_users_content($stable, $users/*,$filterdata*/){
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
        $list['username'] = html_writer::tag('a', $username, array('href' =>$CFG->wwwroot. '/local/users/profile.php?id='.$user->id));
        $list['empid'] = ($user->open_employeeid) ? $user->open_employeeid : '--' ;
        $useremail = $user->email;
        if(strlen($useremail) > 24){
            $useremail = substr($useremail, 0, 24).'...';
        }
        $list['fullemail'] = $user->email;
        $list['email'] = !empty($useremail) ? $useremail : 'N/A';
        $usertype = $user->open_type;
        if ($usertype == 1) {
            $userstype = 'student';
        }
        $list['open_type'] = $userstype;
        $organization = $user->costcentername;
        $dept = $user->departmentname;
        
        if(!$dept){
            $dept = 'N/A';
        }
        $program = $DB->get_record_sql("SELECT p.name,p.id FROM {local_program} p 
                JOIN {local_program_users} u
                ON p.id=u.programid 
                WHERE u.userid=$user->id");

        if(!empty($program)){
            $list['programstring'] = $program->name;
            $list['programid'] = $program->id;

        }else{
            $list['programstring'] = 'N/A';
             $list['programid'] = 0;
        }


        $batch=$DB->get_record_sql("SELECT c.name FROM {cohort} c 
                JOIN {cohort_members} pu
                ON pu.cohortid = c.id 
                WHERE pu.userid=$user->id ");

        if(!empty($batch)){
            $list['batchinfo'] = $batch->name;
        }else {
            $list['batchinfo'] = 'N/A';
        }

        $sql = "SELECT u.id as idnumber_value, u.open_department,
                c.fullname AS departmentname
                FROM {user} as u
                JOIN {local_costcenter} AS c 
                ON c.id = u.open_department";

        $labelstring = get_config('local_costcenter');
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
        if (has_capability('local/users:manage', context_system::instance()) || is_siteadmin())
            $list['visible'] = $user->suspended;
            if(is_siteadmin() || has_capability('local/users:edit', context_system::instance())){
                $list['editcap'] = 1;
            }else{
                $list['editcap'] = 0;
            }
            if(is_siteadmin() || has_capability('local/users:delete', context_system::instance())){
                $list['delcap'] = 1;
            }else{
                $list['delcap'] = 0;
            }
            $programuser=$DB->record_exists('local_program_users', array('userid' => $user->id));
            $batchuser=$DB->record_exists('cohort_members', array('userid' => $user->id));
            if($programuser && $batchuser){
                $list['deluser'] = 1;
            }else{
                $list['deluser'] = 0;
            }
            if($batchuser){
                $list['batchuser'] = 1;
            }else{
                $list['batchuser'] = 0;
            }

            $data[] = $list;
    }
    return $data;

}

/*
* return filterform
*/
function users_filters_form($filterparams){

    global $CFG;
    require_once($CFG->dirroot . '/local/courses/filters_form.php');
    require_once($CFG->dirroot . '/local/program/lib.php');
    $systemcontext = context_system::instance();
    
    if(is_siteadmin() || has_capability('local/costcenter:manage_multiorganizations', $systemcontext)){
        $mform = new filters_form(null, array('filterlist'=>array('dependent_fields', 'email', 'status','program','batch'), 'courseid' => 1, 'enrolid' => 0,'plugins'=>array('users','costcenter','program'),'filterparams' => $filterparams));
    }else if(has_capability('local/costcenter:manage_ownorganization',$systemcontext)){
        $mform = new filters_form(null, array('filterlist'=>array('dependent_fields','email', 'status', 'program','batch'), 'courseid' => 1, 'enrolid' => 0,'plugins'=>array('users','costcenter'),'filterparams' => $filterparams));
    }else if(has_capability('local/costcenter:manage_owndepartments', $systemcontext)){
        $mform = new filters_form(null, array('filterlist'=>array('dependent_fields','email','status', 'program','batch'), 'courseid' => 1, 'enrolid' => 0,'plugins'=>array('users','costcenter'),'filterparams' => $filterparams));
    }else{
        $mform = new filters_form(null, array('filterlist'=>array('email','status', 'location', 'hrmsrole'), 'courseid' => 1, 'enrolid' => 0,'plugins'=>array('users','costcenter'),'filterparams' => $filterparams));
    }
    return $mform;
}

/*
* @return true for reports under category
*/
function learnerscript_users_list(){
    return get_string('users', 'local_users');
}

function local_users_output_fragment_update_user_profile($args){
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
        $mform = new local_users\forms\profile_form(null, array('editoroptions' => $editoroptions,'form_status' => $args->form_status,'id' => $data->id,'org'=>$data->open_costcenterid,'dept'=>$data->open_departmentid,'subdept'=>$data->open_subdepartment), 'post', '', null, true, $formdata);
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
    $renderer = $PAGE->get_renderer('local_users');
    ob_start();
    $formstatus = array();
    foreach (array_values($mform->formstatus) as $k => $mformstatus) {
        $activeclass = $k == $args->form_status ? 'active' : '';
        $formstatus[] = array('name' => $mformstatus, 'activeclass' => $activeclass);
    }
    $formstatusview = new \local_users\output\form_status($formstatus);
    $o .= $renderer->render($formstatusview);
    $mform->display();
    $o .= ob_get_contents();
    ob_end_clean();
    return $o;
}

function contactus_log($formdata, $sentstatus){
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
function local_users_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {
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
        $file = $fs->get_file($context->id, 'local_users', $filearea, $itemid, $filepath, $filename);
        if (!$file) {
            return false;
        }
        send_file($file, $filename, 0, $forcedownload, $options);
    }
function local_users_before_http_headers(){
    global $PAGE, $CFG, $USER;
    require_once ($CFG->libdir.'/accesslib.php');
    if(!is_siteadmin()){
        $PAGE->add_body_class('usersclass');
    }
    if(isloggedin()) {
        if($USER->open_type == 1){
            $PAGE->add_body_class('studentclass');
        }
    }
}


function local_users_output_fragment_program_display($args) {

 
    
    global $CFG, $OUTPUT, $DB, $PAGE, $USER;
        $PAGE->set_url('/local/users/profile.php');
       

    $programid = $args['programid'];
    $userid = $args['userid'];
    if(!$programid){
        $programid = 0;
    }
        
       
   $query =  $DB->get_records_sql("SELECT p.name,p.shortname,p.duration,p.description,p.totalcourses,p.course_elective,p.prerequisite,COUNT(lpl.programid) AS semester,lc.name AS curr_name , lc.shortname AS curr_shortname,date(from_unixtime(p.startdate)) AS startdate,date(from_unixtime(p.startdate)) AS enddate FROM {local_program} AS p 
    JOIN {local_program_levels} lpl ON lpl.programid = p.id 
    JOIN {local_curriculum} lc ON lc.id = p.curriculumid 
    WHERE p.id = $programid GROUP BY lpl.programid");
  


  $result = $DB->get_records_sql("SELECT userid FROM {local_program_users} WHERE programid = $programid");


    foreach($result as $t){
        $userarray[] = $t->userid;
    }
    $userarrstring = implode(',',$userarray);

if(!$userarray){
    $userarrstring = '0';
 }
    $groupcohortid    = $DB->get_records_sql("SELECT cohortid FROM {cohort_members} WHERE userid IN ($userarrstring)");
   foreach($groupcohortid as $gs)
   {
      $cohortid = $gs->cohortid;
   }
   $core_component = new \core_component();
      $certificate_plugin_exist = $core_component::get_plugin_directory('tool', 'certificate');
        if ($certificate_plugin_exist) {
            $certid = $DB->get_field('local_program', 'certificateid', array('id'=>$programid));
        } else {
            $certid = false;
        }
            $sql = "SELECT id, programid, userid, completionstatus
                        FROM {local_programcompletions}
                        WHERE programid = :programid  
                        AND completionstatus != 0 ";
$completed = $DB->record_exists_sql($sql, array('programid'=>$programid));

          if($certid) {
                    $icon = '<i class="icon fa fa-download" aria-hidden="true"></i>';
                    if($completed) {
                       $certcode = $DB->get_field('tool_certificate_issues', 'code', array('moduleid'=>$programid,'userid'=>$sdata->id,'moduletype'=>'program'));
                       $array = array('preview'=>1, 'templateid'=>$certid,'code'=>'previewing');
                        $url = new moodle_url('../../admin/tool/certificate/view.php?', $array);
                        $downloadlink = html_writer::link($url, $icon, array('title'=>get_string('download_certificate','tool_certificate')));
                    } else {
                        $url = 'javascript: void(0)';
                        $downloadlink = html_writer::tag($url, get_string('nodata', 'local_program'));
                    }
                    $line[] =  $downloadlink;
                }
            $templatedata =  array();

 
    foreach($query as $a){
    $countuser = $DB->get_records_sql("SELECT count(userid) AS enroll_user FROM {local_program_users} WHERE programid = $programid");

 foreach($countuser as $cu){
    $enroll_user = $cu->enroll_user;
 }
   $stdate = $DB->get_records_sql("SELECT   date(from_unixtime(startdate)) AS startddate FROM {local_program_levels} WHERE programid = $programid  ORDER BY id ASC LIMIT 1");

         foreach($stdate as $c){
            $startdata = $c->startddate;
         }

if($startdata == '1970-01-01')
{
    $stdate = 'N/A';
}
else{
    $stdate = $startdata;
}

$du = $a->duration;
 $date = $stdate;
$date = strtotime($date);
$new_date = strtotime('+ '.$du.' year', $date);
$enddate =  date('Y-m-d', $new_date);
 

 if($enddate == '1970-01-01')
{
    $enddate = 'N/A';
}
else{
    $enddate = $enddate;
}

$leveldata = $DB->get_records_sql("SELECT  courseid ,parentid, mandatory FROM {local_program_level_courses} WHERE programid = $programid");

 

 foreach($leveldata as $c)
 {
    $arr[] = $c->courseid;
    $parentid = $c->parentid;
    
        if($c->mandatory == 1){
            $coursetypearr[] = $c->mandatory;
            $cparentarr[] = $c->parentid;
        }
        else{
            $coursetypearre[] = $c->mandatory;
            $eparentarr[] = $c->parentid;
        }
    
    $coursecontext = $DB->get_field('context','id', array('instanceid' => $c->courseid, 'contextlevel' =>50));


         $instructors = $DB->get_records_sql("SELECT u.username,u.id FROM {user} u JOIN {role_assignments} ra ON ra.userid = u.id WHERE ra.roleid = 3 AND ra.contextid = $coursecontext");
             
             $k = 0;
            foreach($instructors as $key){
            $userrecord = $DB->get_record('user', array('id' => $key->id));
            $user_image = $OUTPUT->user_picture($userrecord, array('size' => 40, 'link' => false));
             
          $imgearr[$k]['userimage'] =$user_image;//$instructors;
          $imgearr[$k]['username'] =$userrecord->username;
          $k++;
        }
        
 
 }

 
   $elactvalue = implode(',',$eparentarr);
   $corevalues = implode(',',$cparentarr);
   $noofcourses = count($arr);
   $core = count($coursetypearr);
   $elactive = count($coursetypearre);
 
  if(!empty($cparentarr)){
    $coredata = $DB->get_records_sql("SELECT  fullname FROM {course} WHERE id IN ($corevalues)");
    foreach($coredata as $co){
            $coursefullname[] = $co->fullname;
         }
$coursefullname = implode(',',$coursefullname);
}else{
    $coursefullname = "N/A";
}
if(!empty($eparentarr)){
     $elactivedata = $DB->get_records_sql("SELECT fullname FROM {course} WHERE id IN ($elactvalue)");
     foreach($elactivedata as $eo){
            $ecoursefullname[] = $eo->fullname;
         }
$ecoursefullname = implode(',',$ecoursefullname);
 }else{
    $ecoursefullname = 'N/A';
 }


  $total = ("SELECT COUNT(programid)
                    FROM {local_program_levels} c WHERE programid = '$programid'");

            $totalusers   = $DB->count_records_sql($total);
            
   $pldata = $DB->get_records_sql("SELECT enddate AS enddate ,level  FROM {local_program_levels} WHERE programid = '$programid' AND enddate != 0 ");

       $cdate = time();

    $i= 0;
   foreach($pldata as $t)
   {
        if($cdate >$t->enddate)
        { $i++;
            
        }
   }
   
     $semnumber = $i;
 
     $tt = $semnumber*100;
     $per = $tt/$totalusers;
           
            $rowdata['name'] = $a->name;
            $rowdata['shortname'] = $a->shortname;
            $rowdata['description']  = strip_tags($a->description);
            $rowdata['startdate'] = $stdate;
            $rowdata['enddate'] = $enddate;
            $rowdata['prerequisite'] = $a->prerequisite;
            $rowdata['course_elective'] = $a->course_elective;
            $rowdata['enroll_user'] = $enroll_user;
            $rowdata['semester'] = $a->semester;
            $rowdata['curr_name'] = $a->curr_name;
            $rowdata['noofcourses'] = $noofcourses;
            $rowdata['core'] = $core;
            $rowdata['elactive'] = $elactive;
            $rowdata['batchid'] = $cohortid;
            $rowdata['per'] = $per;
            $rowdata['coursefullname'] = $coursefullname;
            $rowdata['ecoursefullname'] = $ecoursefullname;
            $rowdata['downloadlink'] = $downloadlink;
            $rowdata['instructor'] = array_values($imgearr);
            $templatedata['rowdata'][] = $rowdata;   


             $selectsql = "SELECT c.fullname AS course, bclc.courseid AS programcourseid, bclc.programid, cc.coursetype as ctype, 
                                    bclc.levelid, lpl.level AS levelname,      
                        (
                            SELECT COUNT(*) FROM {course_modules} cm
                                WHERE cm.course = bclc.courseid
                        ) AS total_modules,
                                (
                                    SELECT COUNT(cmc.id) FROM {course_modules_completion} cmc
                                    LEFT JOIN {course_modules} cm ON cmc.coursemoduleid = cm.id
                                    WHERE cm.course = bclc.courseid and  cmc.userid = u.id
                        ) AS modules_completed,
                        (
                            ROUND(100 / (SELECT COUNT(*) FROM {course_modules} cm WHERE cm.course = bclc.courseid) ) *
                            (SELECT COUNT(cmc.id) FROM {course_modules_completion} cmc
                            LEFT JOIN {course_modules} cm ON cmc.coursemoduleid = cm.id
                            WHERE  cm.course = bclc.courseid and cmc.userid = u.id)
                        )AS course_progress
                        
                        FROM {local_program_level_courses} bclc
                        JOIN {user} u
                        JOIN {user_enrolments} ue ON ue.userid=u.id
                        JOIN {enrol} e ON e.id=ue.enrolid
                        JOIN {course} c ON c.id = e.courseid and c.id = bclc.courseid
                        JOIN {local_cc_semester_courses} cc ON cc.open_parentcourseid =  bclc.parentid
                       JOIN {local_program_levels} lpl ON lpl.id = bclc.levelid and lpl.programid = bclc.programid WHERE u.id =  $userid";
        
        $queryparam = array();
        if(is_siteadmin()){
       }
        else {
          print_error('You dont have permissions to view this page.');
              die();
        }


        $semester_info = $DB->get_records_sql($selectsql, $queryparam, $tablelimits->start, $tablelimits->length);
 $colour = array('danger','primary','success','warning','info','dark','secondary');
        $c = 0;
        
       $list=array();
        $data = array();

$products = array();


 
 foreach($semester_info as $current) {
    $levelid = $current->levelid;
    $semester = $current->levelname;
    $products[$levelid][] = $current; // use $dsid as common array key for now
     $arrt[] = $levelid;
     $semnamedata[] = $semester;

}


$aruniq = array_unique($arrt);
 $semnameunidata = array_unique($semnamedata);
 
//  foreach($semnameunidata as $cy){

//  $semdata[]  = $cy;

// }

  $templatedata['semester_num'] = array_values($semnameunidata);

        foreach($aruniq as $x){


         
        foreach ($semester_info as $sem_detail) {

                 if($sem_detail->levelid == $x){

             
                $list['id'] = $sem_detail->programcourseid;
                $list['fullname'] = $sem_detail->course;
                $list['semester'] = $sem_detail->levelname;
                $list['roleid'] = $programid;
                $semester = $list['semester'];
                $list['levelid'] = $sem_detail->levelid;

               $levelid = $list['levelid'];
                if($sem_detail->ctype == 0){
                    $sem_detail->ctype = 'Elective';
                }
                else {
                    $sem_detail->ctype = 'Core';
                }
               $list['ctype'] = $sem_detail->ctype;
               $list['per'] = $sem_detail->course_progress;
               $list['colour'] =   $colour[$c];
               $list['semtotal'] = $semtotal;
               $data['sem'][] = $list;
               $c++;


               }

            }
 
 
            $semnamedata['semnamedata'] = $data['sem'][0]['semester'];
 
            $templatedata['glass'][]  = $data;
             $templatedata['semnamedata1'][]  = $semnamedata;

            $data = array();
        }
 }
 
 
    $output = $OUTPUT->render_from_template('local_users/programview', $templatedata);
    return $output;
}


