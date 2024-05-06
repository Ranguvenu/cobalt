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
 * @package    local_costcenter
 * @copyright  2022 eAbyas Info Solutions Pvt. Ltd.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;
define('ACTIVE', 0);
define('IN_ACTIVE', 1);
define('TOTAL', 2);
define('HIERARCHY_LEVELS', 3);
require_once($CFG->dirroot . '/user/selector/lib.php');
require_once($CFG->dirroot . '/enrol/locallib.php');
require_once($CFG->dirroot . '/message/lib.php');

class costcenter {
    /*
     * @method get_next_child_sortthread Get costcenter child list
     * @param  int $parentid which is id of a parent costcenter
     * @param  [string] $table is a table name
     * @return list of costcenter children
     * */
    public function get_next_child_sortthread($parentid, $table) {
        global $DB, $CFG;
        $maxthread = $DB->get_record_sql("SELECT MAX(sortorder) AS sortorder
                                           FROM {$CFG->prefix}{$table}
                                          WHERE parentid = :parentid", array('parentid' => $parentid)
                                        );
        if (!$maxthread || strlen($maxthread->sortorder) == 0) {
            if ($parentid == 0) {
                // First top level item.
                return $this->inttovancode(1);
            } else {
                // Parent has no children yet.
                return $DB->get_field('local_costcenter', 'sortorder', array('id' => $parentid)) . '.' . $this->inttovancode(1);
            }
        }
        return $this->increment_sortorder($maxthread->sortorder);
    }

    /**
     * Convert an integer to a vancode
     * @param int $int integer to convert.
     * @return vancode The vancode representation of the specified integer
     */
    public function inttovancode($int = 0) {
        $num = base_convert((int) $int, 10, 36);
        $length = strlen($num);
        return chr($length + ord('0') - 1) . $num;
    }

    /**
     * Convert a vancode to an integer
     * @param string $char Vancode to convert. Must be <= '9zzzzzzzzzz'
     * @return integer The integer representation of the specified vancode
     */
    public function vancodetoint($char = '00') {
        return base_convert(substr($char, 1), 36, 10);
    }

    /**
     * Increment a vancode by N (or decrement if negative)
     *
     */
    public function increment_vancode($char, $inc = 1) {
        return $this->inttovancode($this->vancodetoint($char) + (int) $inc);
    }
    /**
     * Increment a sortorder by N (or decrement if negative)
     *
     */
    public function increment_sortorder($sortorder, $inc = 1) {
        if (!$lastdot = strrpos($sortorder, '.')) {
            // Root level, just increment the whole thing.
            return $this->increment_vancode($sortorder, $inc);
        }
        $start = substr($sortorder, 0, $lastdot + 1);
        $last = substr($sortorder, $lastdot + 1);
        // Increment the last vancode in the sequence.
        return $start . $this->increment_vancode($last, $inc);
    }

    /*Get uploaded course summary uploaded file
     * @param $course is an obj Moodle course
     * @return course summary file(img) src url if exists else return default course img url
     * */
    public function get_course_summary_file($course) {
        global $DB, $CFG, $OUTPUT;
        if ($course instanceof stdClass) {
            $course = new core_course_list_element($course);
        }
        // Set default course image.
        $url = $OUTPUT->image_url('/course_images/courseimg', 'local_costcenter');
        foreach ($course->get_course_overviewfiles() as $file) {
            $isimage = $file->is_valid_image();
            if ($isimage) {
                $url = file_encode_url("$CFG->wwwroot/pluginfile.php", '/' .
                    $file->get_contextid() . '/' . $file->get_component() . '/' .
                $file->get_filearea() . $file->get_filepath() . $file->get_filename(), !$isimage);
            }
        }
        return $url;
    }
    public function get_costcenter_icons() {
        global $USER, $DB;

        if (!empty($costcentershell = $DB->get_field('local_costcenter', 'shell',
                array('id' => $USER->open_costcenterid, 'visible' => 1)))) {
            return $costcentershell;
        } else {
            return false;
        }
    }
    public function get_costcenter_theme() {
        global $USER, $DB;

        if (!empty($costcentertheme = $DB->get_field('local_costcenter', 'theme',
                array('id' => $USER->open_costcenterid, 'visible' => 1)))) {
            return $costcentertheme;
        } else {
            return false;
        }
    }
    public static function get_costcenter_path_field_concatsql($matchcolumnname,$costcenterpath=null,$datatype=null){

        global $DB;

        if($datatype == null){

            $datatype=self::ALL_MODULE_CONTENT;
        }

        $concatsql="";

        if(is_siteadmin() && $costcenterpath == null){
            return $concatsql;

        }else{

            if($costcenterpath === null || $costcenterpath === 0){

                $concatsql =self::get_user_roleswitch_costcenterpath_concatsql($matchcolumnname,$datatype);

            }else{

                 $first_character = substr($costcenterpath, 0, 1);

                 if($first_character !== '/'){

                    $costcenterpath = "/".$costcenterpath."";

                 }

                $concatsql=self::costcenterpath_match_sql($costcenterpath,$matchcolumnname,$datatype);
                $concatsql="AND (".$concatsql.")";

            }

            return $concatsql;

        }
    }

}
/**
 * Description: local_costcenter_pluginfile for fetching images in costcenter plugin
 * @param  [INT] $course        [course id]
 * @param  [INT] $cm            [course module id]
 * @param  [context] $context       [context of the file]
 * @param  [string] $filearea      [description]
 * @param  [array] $args          [array of ]
 * @param  [boolean] $forcedownload [to download or only view]
 * @param  array  $options       [description]
 * @return [file]                [description]
 */
function local_costcenter_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {
    // Check the contextlevel is as expected - if your plugin is a block, this becomes CONTEXT_BLOCK, etc.

    // Make sure the filearea is one of those used by the plugin.
    if ($filearea !== 'costcenter_logo') {
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
    $file = $fs->get_file($context->id, 'local_costcenter', $filearea, $itemid, $filepath, $filename);
    if (!$file) {
        return false;
    }
    send_file($file, $filename, 0, $forcedownload, $options);
}
/**
 * Description: get the logo specified to the organization.
 * @param  [INT] $costcenter_logo [item id of the logo]
 * @return [URL]                  [path of the logo]
 */
function costcenter_logo($costcenterlogo) {
    global $DB;
    $costcenterlogourl = false;

    $sql = "SELECT * FROM {files} WHERE itemid = :logo  AND filename != '.' ORDER BY id DESC";
    $costcenterlogorecord = $DB->get_record_sql($sql, array('logo' => $costcenterlogo), 1);

    if (!empty($costcenterlogorecord)) {
        if ($costcenterlogorecord->filearea == "costcenter_logo") {
            $costcenterlogourl = moodle_url::make_pluginfile_url(
                                                    $costcenterlogorecord->contextid,
                                                    $costcenterlogorecord->component,
                                                    $costcenterlogorecord->filearea,
                                                    $costcenterlogorecord->itemid,
                                                    $costcenterlogorecord->filepath,
                                                    $costcenterlogorecord->filename
                                                );
        }
    }
    return $costcenterlogourl;
}
/**
 * @method local_costcenter_output_fragment_new_costcenterform
 * @param  $args is an array
 */
function local_costcenter_output_fragment_new_costcenterform($args) {
    global $CFG, $DB;
    $args = (object) $args;
    $context = $args->context;
    $o = '';
    $formdata = [];
    if (!empty($args->jsonformdata)) {
        $serialiseddata = json_decode($args->jsonformdata);
        parse_str($serialiseddata, $formdata);
    }
    if ($args->id) {
        $data = $DB->get_record('local_costcenter', array('id' => $args->id));
    }
    $mform = new local_costcenter\form\organization_form(null, array('id' => $args->id, 'formtype' => $args->formtype), 'post', '', null, true, $formdata);

    $draftitemid = file_get_submitted_draft_itemid('costcenter_logo');
    file_prepare_draft_area($draftitemid, $context->id, 'local_costcenter', 'costcenter_logo', $data->costcenter_logo, null);
    $data->costcenter_logo = $draftitemid;
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

/**
 * Description: [organizations_filter code]
 * @param  [mform]  $mform          [form where the filetr is initiated]
 * @param  string  $query          [description]
 * @param  boolean $searchanywhere [description]
 * @param  integer $page           [description]
 * @param  integer $perpage        [description]
 * @return [type]                  [description]
 */
function organizations_filter($mform, $query = '', $searchanywhere = false, $page = 0, $perpage = 25) {
    global $DB, $USER;
    $labelstring =  get_config('local_costcenter');
    $systemcontext = context_system::instance();
    $organizationlist = array();
    $data = data_submitted();
    $costcenterid = optional_param('costcenterid', '', PARAM_INT);
    $userparam = array();
    $organizationparam = array();
    $params = array();
    if (is_siteadmin() || has_capability('local/costcenter:manage_multiorganizations', $systemcontext)) {
        $organizationlistsql = "SELECT id, fullname FROM {local_costcenter} WHERE depth =1";
    } else {
        $organizationlistsql = "SELECT id, fullname FROM {local_costcenter} WHERE depth =1 AND id = :usercostcenter ";
        $userparam['usercostcenter'] = $USER->open_costcenterid;
    }
    if (!empty($query)) {
        if ($searchanywhere) {
            $organizationlistsql .= " AND fullname LIKE '%$query%' ";
        } else {
            $organizationlistsql .= " AND fullname LIKE '$query%' ";
        }
    }
    if (isset($data->organizations) && !empty(($data->organizations))) {
        list($organizationparamsql, $organizationparam) =
                $DB->get_in_or_equal($data->organizations, SQL_PARAMS_NAMED, 'param', true, false);
        $organizationlistsql .= " AND id $organizationparamsql";
    }
    $organizationlistsql .= " AND visible = 1";
    $params = array_merge($userparam, $organizationparam);

    if (!empty($query) || empty($mform)) {
        $organizationlist = $DB->get_records_sql($organizationlistsql, $params, $page, $perpage);
        return $organizationlist;
    }
    if ((isset($data->organizations) && !empty($data->organizations)) || !empty($costcenterid)) {
        $organizationlist = $DB->get_records_sql_menu($organizationlistsql, $params, $page, $perpage);
    }
    $options = array(
        'ajax' => 'local_courses/form-options-selector',
        'multiple' => true,
        'data-action' => 'organizations',
        'data-options' => json_encode(array('id' => 0)),
        'placeholder' => $labelstring->firstlevel,
    );
    $select = $mform->addElement('autocomplete', 'organizations', '', $organizationlist, $options);
    $mform->setType('organizations', PARAM_RAW);
}
/**
  * Description: [departments_filter code]
 * @param  [mform]  $mform          [form where the filetr is initiated]
 * @param  string  $query          [description]
 * @param  boolean $searchanywhere [description]
 * @param  integer $page           [description]
 * @param  integer $perpage        [description]
 * @return [type]                  [description]
 */
function departments_filter($mform,$query='',$searchanywhere=false, $page=0, $perpage=25){
    global $DB,$USER;
    $labelstring =  get_config('local_costcenter');
    $systemcontext = context_system::instance();
    $departmentslist=array();
    $data=data_submitted();
    $departmentid = optional_param('departmentid', '', PARAM_INT);
    $userparam = array();
    $organizationparam = array();
    $params = array();
    
    if(is_siteadmin() || has_capability('local/costcenter:manage_multiorganizations', $systemcontext)){
        $departmentslist_sql="SELECT id, fullname FROM {local_costcenter} WHERE depth = 2";
    }else{
        $departmentslist_sql="SELECT id, fullname FROM {local_costcenter} WHERE depth = 2 AND parentid = :usercostcenter ";
        $userparam['usercostcenter'] = $USER->open_costcenterid;
    }
    if(!empty($query)){ 
        if ($searchanywhere) {
            $departmentslist_sql.=" AND fullname LIKE '%$query%' ";
        } else {
            $departmentslist_sql.=" AND fullname LIKE '$query%' ";
        }
    }
    if(isset($data->departments)&&!empty(($data->departments))){
        list($organizationparamsql, $organizationparam) = $DB->get_in_or_equal($data->departments, SQL_PARAMS_NAMED, 'param', true, false);
        if($organizationparamsql){
            $departmentslist_sql.=" AND id {$organizationparamsql} ";
        }
    }
    $departmentslist_sql .= " AND visible = 1";
    $params = array_merge($userparam, $organizationparam);

    if(!empty($query)||empty($mform)){ 
        $departmentslist = $DB->get_records_sql($departmentslist_sql, $params, $page, $perpage);
        return $departmentslist;
    }
    if((isset($data->departments)&&!empty($data->departments))  || !empty($departmentid)){ 
        $departmentslist = $DB->get_records_sql_menu($departmentslist_sql, $params, $page, $perpage);
    }
    
    $options = array(
            'ajax' => 'local_courses/form-options-selector',
            'multiple' => true,
            'data-action' => 'departments',
            'data-options' => json_encode(array('id' => 0)),
            'placeholder' => $labelstring->secondlevel
    );
        
    $select = $mform->addElement('autocomplete', 'departments', '', $departmentslist,$options);
    $mform->setType('departments', PARAM_RAW);
}

/**
  * Description: [subdepartment_filter code]
 * @param  [mform]  $mform          [form where the filetr is initiated]
 * @param  string  $query          [description]
 * @param  boolean $searchanywhere [description]
 * @param  integer $page           [description]
 * @param  integer $perpage        [description]
 * @return [type]                  [description]
 */
function subdepartment_filter($mform,$query='',$searchanywhere=false, $page=0, $perpage=25){
    global $DB,$USER;
    $labelstring =  get_config('local_costcenter');
    $systemcontext = context_system::instance();
    $subdepartmentslist=array();
    $data=data_submitted();
    $subdepartmentid = optional_param('subdepartmentid', '', PARAM_INT);
    $userparam = array();
    $departmentparam = array();
    $params = array();
    if(is_siteadmin() || has_capability('local/costcenter:manage_multiorganizations', $systemcontext)){
        $subdepartmentslist_sql = "SELECT id, fullname FROM {local_costcenter} WHERE depth = 3 ";
    }else if(has_capability('local/costcenter:manage_ownorganization', $systemcontext)){
        $subdepartmentslist_sql = "SELECT id, fullname FROM {local_costcenter} WHERE depth = 3 AND parentid IN (SELECT id FROM {local_costcenter} WHERE parentid = :usercostcenter) ";
        $userparam['usercostcenter'] = $USER->open_costcenterid;
    }else{
        $subdepartmentslist_sql = "SELECT id, fullname FROM {local_costcenter} WHERE depth = 3 AND parentid = :userdepartment ";
        $userparam['userdepartment'] = $USER->open_departmentid;
    }
    if(!empty($query)){ 
        if ($searchanywhere) {
            $subdepartmentslist_sql .= " AND fullname LIKE '%$query%' ";
        } else {
            $subdepartmentslist_sql .= " AND fullname LIKE '$query%' ";
        }
    }
    if(isset($data->subdepartment)&&!empty(($data->subdepartment))){
        list($departmentparamsql, $departmentparam) = $DB->get_in_or_equal($data->subdepartment, SQL_PARAMS_NAMED, 'param', true, false);        
        $subdepartmentslist_sql.=" AND id $departmentparamsql";
    }
    $subdepartmentslist_sql .= " AND visible = 1";
    $params = array_merge($userparam, $departmentparam);

    if(!empty($query)||empty($mform)){ 
        $subdepartmentslist = $DB->get_records_sql($subdepartmentslist_sql, $params, $page, $perpage);
        return $subdepartmentslist;
    }
    if((isset($data->subdepartment)&&!empty($data->subdepartment)) || !empty($subdepartmentid)){ 
        $subdepartmentslist = $DB->get_records_sql_menu($subdepartmentslist_sql, $params, $page, $perpage);
    }
    
    $options = array(
            'ajax' => 'local_courses/form-options-selector',
            'multiple' => true,
            'data-action' => 'subdepartment',
            'data-options' => json_encode(array('id' => 0)),
            'placeholder' => $labelstring->thirdlevel,
            'id' => 'subdepartment_filter_element'
    );
        
    $select = $mform->addElement('autocomplete', 'subdepartment', '', $subdepartmentslist, $options);
    $mform->setType('subdepartment', PARAM_RAW);
}
/**
 * Description: [insert costcenter instance ]
 * @param  [OBJECT] $costcenter [costcenter object]
 * @return [INT]             [created costcenter id]
 */
function costcenter_insert_instance($costcenter) {
    global $DB, $CFG, $USER;
    $systemcontext = context_system::instance();
    if ($costcenter->parentid == 0) {
        $costcenter->depth = 1;
        $costcenter->path = '';
    } else {
        // Parent item must exist.
        $parent = $DB->get_record('local_costcenter', array('id' => $costcenter->parentid));
        $costcenter->depth = $parent->depth + 1;
        $costcenter->path = $parent->path;
    }
    // Get next child item that need to provide.
    $custom = new costcenter();
    if (!$sortorder = $custom->get_next_child_sortthread($costcenter->parentid, 'local_costcenter')) {
        return false;
    }

    $costcenter->sortorder = $sortorder;
    $parentid = $costcenter->parentid ? $costcenter->parentid : 0;
    $costcenter->costcenter_logo = $costcenter->costcenter_logo;
    $costcenter->shell = $costcenter->shell;

    file_save_draft_area_files($costcenter->costcenter_logo, $systemcontext->id,
        'local_costcenter', 'costcenter_logo', $costcenter->costcenter_logo);
    $costcenter->id = $DB->insert_record('local_costcenter', $costcenter);

    if ($costcenter->id) {
        $parentpath = $DB->get_field('local_costcenter', 'path', array('id' => $parentid));
        $path = $parentpath.'/'.$costcenter->id;
        $datarecord = new stdClass();
        $datarecord->id = $costcenter->id;
        $datarecord->path = $path;
        $DB->update_record('local_costcenter',  $datarecord);

        $record = new stdClass();
        $record->name = $costcenter->fullname;
        $record->parent = $DB->get_field('local_costcenter', 'category', array('id' => $parentid));
        $record->idnumber = $costcenter->shortname;
        $category = core_course_category::create($record);

        if ($category) {
            $DB->execute("UPDATE {local_costcenter} SET multipleorg = ? WHERE id = ?", [$costcenter->id, $costcenter->id]);
            $DB->execute("UPDATE {local_costcenter} SET category= ? WHERE id = ? ", [$category->id, $costcenter->id]);
        }
    }
    return $costcenter->id;
}
/**
 * Description: [edit costcenter instance ]
 * @param  [INT] $costcenterid  [id of the costcenter]
 * @param  [object] $newcostcenter [update content]
 * @return [BOOLEAN]                [true if updated ]
 */
function costcenter_edit_instance($costcenterid, $newcostcenter) {
    global $DB, $CFG;
    $systemcontext = context_system::instance();
    $oldcostcenter = $DB->get_record('local_costcenter', array('id' => $costcenterid));
    $category = $DB->get_field('local_costcenter', 'category', array('id' => $newcostcenter->id));
    // Check if the parentid is the same as that of new parentid.
    if ($newcostcenter->parentid != $oldcostcenter->parentid) {
        $newparentid = $newcostcenter->parentid;
        $newcostcenter->parentid = $oldcostcenter->parentid;
    }
    $today = strtotime(date('d/m/Y', time()));
    $newcostcenter->timemodified = $today;
    $newcostcenter->costcenter_logo = $newcostcenter->costcenter_logo;
    file_save_draft_area_files($newcostcenter->costcenter_logo, $systemcontext->id,
        'local_costcenter', 'costcenter_logo', $newcostcenter->costcenter_logo);

    $costercenter = $DB->update_record('local_costcenter', $newcostcenter);
    $coursecategories = $DB->record_exists('course_categories', array('id' => $category));
    if ($costercenter && $coursecategories) {
        $record = new stdClass();
        $record->id = $category;
        $record->name = $newcostcenter->fullname;
        $record->idnumber = $newcostcenter->shortname;
        $DB->update_record('course_categories', $record);
    }
    return true;

}
/**
 * [costcenter_items description]
 * @return [type] [description]
 */
function costcenter_items() {
    global $DB, $USER;
    $assignedcostcenters = '';
    $systemcontext = context_system::instance();
    if (is_siteadmin() || has_capability('local/costcenter:manage_multiorganizations', $systemcontext)) {
        $sql = "SELECT * from {local_costcenter} where visible=1 AND depth <3 ORDER by sortorder,fullname ";
        $assignedcostcenters = $DB->get_records_sql($sql);
    } else {
        $sql = "SELECT * from {local_costcenter} where visible = 1 and (id = ? or parentid = ?) ORDER by sortorder,fullname";
        $assignedcostcenters = $DB->get_records_sql($sql, [$USER->open_costcenterid, $USER->open_costcenterid]);
    }
    return $assignedcostcenters;
}
/*
* Author Rizwana
* Displays a node in left side menu
* @return  [type] string  link for the leftmenu
*/
function local_costcenter_leftmenunode() {
    global $USER;
    $systemcontext = context_system::instance();
    $costcenternode = '';
    if (has_capability('local/costcenter:view', $systemcontext) || is_siteadmin()) {
        $costcenternode .= html_writer::start_tag('li',
            array('id' => 'id_leftmenu_departments',
                  'class' => 'pull-left user_nav_div departments'
            )
        );
        $labelstring = get_config('local_costcenter');
        if (is_siteadmin() || has_capability('local/costcenter:manage_multiorganizations', $systemcontext)) {
            $organizationurl = new moodle_url('/local/costcenter/index.php');
            $organizationstring = get_string('orgStructure', 'local_costcenter', $labelstring);
        } else if (has_capability('local/costcenter:manage_ownorganization', $systemcontext)) {
            $organizationurl = new moodle_url('/local/costcenter/costcenterview.php', array('id' => $USER->open_costcenterid));
            $organizationstring = get_string('orgStructure', 'local_costcenter', $labelstring);
        } else if (has_capability('local/costcenter:manage_owndepartments', $systemcontext)) {
            $organizationurl = new moodle_url('/local/costcenter/costcenterview.php', array('id' => $USER->open_costcenterid));
            $organizationstring = get_string('clgStructure', 'local_costcenter', $labelstring);
        } 
        else {
            $organizationurl = new moodle_url('/local/costcenter/costcenterview.php', array('id' => $USER->open_departmentid));
            $organizationstring = get_string('orgStructure', 'local_costcenter', $labelstring);
        }
        $department = html_writer::link($organizationurl,
                                        '<span class="org_wht_structure_icon dypatil_cmn_icon icon">
                                        </span><span class="user_navigation_link_text">'
                                        .$organizationstring.
                                        '</span>', array('class' => 'user_navigation_link')
                                    );
        $costcenternode .= $department;
        $costcenternode .= html_writer::end_tag('li');
    }
    return array('2' => $costcenternode);
}

/*
* @return  plugins count with all modules
*/
function local_costcenter_plugins_count ($costcenterid, $departmentid=false, $subdepartmentid=false) {
    global $CFG;
    $corecomponent = new core_component();
    $localpluginlist = $corecomponent::get_plugin_list('local');
    $deparray = array();
    foreach ($localpluginlist as $key => $localpluginname) {
        if (file_exists($CFG->dirroot.'/local/'.$key.'/lib.php')) {
            require_once($CFG->dirroot.'/local/'.$key.'/lib.php');
            $functionname = 'costcenterwise_'.$key.'_count';
            if (function_exists($functionname)) {
                $data = $functionname($costcenterid, $departmentid, $subdepartmentid);
                foreach ($data as $key => $val) {
                    $deparray[$key] = $val;
                }
            }
        }
    }
    return $deparray;
}

/*
* @return true for reports under category
*/
function learnerscript_costcenter_list() {
    return 'Costcenter';
}


function local_costcenter_output_fragment_departmentview($args) {
    global $CFG, $DB;
    $args = (object) $args;
    $o = '';
    $formdata = [];
    if (!empty($args->jsonformdata)) {
        $serialiseddata = json_decode($args->jsonformdata);
        parse_str($serialiseddata, $formdata);
    }
    $mform = new local_costcenter\functions\costcenter(null, array(), 'post', '', null, true, $formdata);
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

// dependent_filters
function local_costcenter_get_dependent_fields($mform, $ajaxformdata, $customdata, $elements = null,$allenable = false, $pluginname='local_costcenter',$context= CONTEXT_SYSTEM, $multiple = false){
    global $DB, $USER;
    $context = \context_system::instance();
    $depth = (isset($USER->useraccess)) ? $USER->useraccess['currentroleinfo']['depth'] : 0;
    $contextinfo = (isset($USER->useraccess)) ? $USER->useraccess['currentroleinfo']['contextinfo'] : array() ;
    $count = $contextinfo ? count($contextinfo) : 0;
    if($count > 1){
        $depth--;
    }
    if(is_siteadmin()){
        $depth = 0;
    }
    $total_fields = 3;
    $fields = local_costcenter_get_fields();
    $prev_element = '';
    if(empty($elements) || !is_array($elements)){
        $elements = range(1, $total_fields);
    }
    $firstelement = false;
    $labelstring = get_config('local_costcenter');
    $firstlevel = $labelstring->firstlevel;
    $secondlevel = $labelstring->secondlevel;
    $thirdlevel = $labelstring->thirdlevel;
    foreach($elements as $level){
        if($level === 2){
            $selectlevel = get_string('select_secondlevel', 'local_costcenter', $secondlevel);
        }
        if($level === 3){
            $selectlevel =  get_string('select_thirdlevel', 'local_costcenter', $thirdlevel);
        }
        $levelelementoptions = array(
            'class' => $fields[$level].'_select custom_form_field',
            'id' => 'id_'.$fields[$level].'_select',
            'data-parentclass' => $prev_element,
            'data-selectstring' => $selectlevel,
            'placeholder' => $fields[$level],
            'data-depth' => $level,
            'data-pluginclass' => '',
            'data-class' => $fields[$level].'_select',
            'onchange' => '(function(e){ require("local_costcenter/newcostcenter").changeElement(event) })(event)',
        );
        $prev_element = $fields[$level].'_select';
        $fieldvalue = (!empty($ajaxformdata) && $ajaxformdata[$fields[$level]]) ? $ajaxformdata[$fields[$level]]  ?? null : $customdata[$fields[$level]]  ?? null;
        if($depth > $level){
            $mform->addElement('hidden', $fields[$level], null, $levelelementoptions);
            $mform->setConstant($fields[$level], $fieldvalue);
        }else{
            $enableallfield = (is_siteadmin() && $level == 1) || (!is_siteadmin() && ($USER->useraccess['currentroleinfo']['depth'] > $level))  ? false : $allenable;
            $levelelementoptions['multiple'] = ($firstelement ) ? false : $multiple;
            $levelelementoptions['ajax'] = 'local_costcenter/form-options-selector';
            $levelelementoptions['data-contextid'] = $context->id;
            $levelelementoptions['data-action'] = 'costcenter_element_selector';
            $prevfield = $fields[$level-1];
            $parentid = (!empty($ajaxformdata) && $ajaxformdata[$prevfield]) ? $ajaxformdata[$prevfield] ?? null : $customdata[$prevfield]  ?? null;
            $levelelementoptions['data-options'] = json_encode(array('depth' => $level, 'parentid' => $parentid, 'enableallfield' => $enableallfield));
            if($enableallfield){
                $levelelements = [0 => get_string('all')];
            }else{
                $levelelements = [];
            }
            if($fieldvalue){
                $levelelementids = is_array($fieldvalue) ? $fieldvalue : explode(',', $fieldvalue);
                $levelelementids = array_filter($levelelementids);
                $levelelements = [];
                if($levelelementids){
                    list($idsql, $idparams) = $DB->get_in_or_equal($levelelementids, SQL_PARAMS_NAMED, 'levelelements');
                    $levelsql = "SELECT id, fullname FROM {local_costcenter} WHERE id {$idsql} ";
                    $levelelements += $DB->get_records_sql_menu($levelsql, $idparams);
                }
            }
            $mform->addElement('autocomplete', $fields[$level], '', $levelelements, $levelelementoptions);
            $mform->addHelpButton($fields[$level], $fields[$level].$pluginname, $pluginname);

            $firstelement = false;
        }
        $mform->setType($fields[$level], PARAM_RAW);
    }
}


function local_costcenter_set_costcenter_path(&$data){
    global $USER;
    $fields = local_costcenter_get_fields();
    $contextinfo = (isset($USER->useraccess)) ? $USER->useraccess['currentroleinfo']['contextinfo'] : array();
    $pathnottracked = true;
    if($contextinfo){
        foreach($contextinfo AS $contextdata){
            if(isset($data['open_path']) && (strpos($data['open_path'], $contextdata['costcenterpath']) === 0)){
                $pathnottracked = false;
                $recordedpathids = explode('/', $data['open_path']);
                foreach($fields as $levelid => $field){
                    if(isset($recordedpathids[$levelid]) && $recordedpathids[$levelid] > 0){
                        $data[$field] = $recordedpathids[$levelid];
                    }
                }
                break;
            }
        }
    }else if(isset($data['open_path'])){
        $pathnottracked = false;
        $recordedpathids = explode('/', $data['open_path']);
        foreach($fields as $levelid => $field){
            if(isset($recordedpathids[$levelid]) && $recordedpathids[$levelid] > 0){
                $data[$field] = $recordedpathids[$levelid];
            }
        }
    }
    if($pathnottracked && $contextinfo){
        $rolecontext = \local_costcenter\lib\accesslib::get_costcenterpath_context($contextinfo[0]['context']);
        $rolecontextids = explode('/',$rolecontext);
        if(count($contextinfo) > 1){
            $depth = $USER->useraccess['currentroleinfo']['depth'];
        }else{
            $depth = $USER->useraccess['currentroleinfo']['depth'] - 1;
        }
        for($i = 1; $i <= $depth; $i++){
            $data[$fields[$i]] = $rolecontextids[$i];
        }
    }
}
function local_costcenter_get_fields(){
    $level = HIERARCHY_LEVELS;
    $labelstring = get_config('local_costcenter');
    $firstlevel = $labelstring->firstlevel;
    $secondlevel = $labelstring->secondlevel;
    $thirdlevel = $labelstring->thirdlevel;
    $fields = [ 1 => "$firstlevel", 2 => "$secondlevel", 3 => "$thirdlevel"];
    for($i=1; $i<= $level; $i++){
        if(isset($fields[$i])){
            $return[$i] = $fields[$i];
        }else{
            $return[$i] = 'open_level'.$i.'department';
        }
    }
    return $return;
}
