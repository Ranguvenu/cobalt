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
defined('MOODLE_INTERNAL') || die;
require_once($CFG->dirroot.'/user/selector/lib.php');
require_once($CFG->libdir . '/formslib.php');
use \local_program\form\program_form as program_form;
use local_program\local\querylib;
use local_program\program;

function local_program_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {
    // Check the contextlevel is as expected - if your plugin is a block, this becomes CONTEXT_BLOCK, etc.

    // Make sure the filearea is one of those used by the plugin.
    if ($filearea !== 'programlogo') {
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
    $file = $fs->get_file($context->id, 'local_program', $filearea, $itemid, $filepath, $filename);
    if (!$file) {
        return false;
    }
    send_file($file, $filename, 0, $forcedownload, $options);
}

/**
 * Serve the new group form as a fragment.
 *
 * @param array $args List of named arguments for the fragment loader.
 * @return string
 */
function local_program_output_fragment_program_form($args) {
    global $CFG, $PAGE, $DB;
    $args = (object) $args;
    $context = $args->context;
    $return = '';
    $renderer = $PAGE->get_renderer('local_program');
    $formdata = [];
    if (!empty($args->jsonformdata)) {
        $serialiseddata = json_decode($args->jsonformdata);
        parse_str($serialiseddata, $formdata);
    }
    $formdata['id'] = $args->id;

    $mform = new program_form(null, array('id' => $args->id,
        'form_status' => $args->form_status), 'post', '', null, true, $formdata);
    $programdata = new stdClass();
    $programdata->id = $args->id;
    $programdata->form_status = $args->form_status;
    $mform->set_data($programdata);

    if (!empty((array) $serialiseddata)) {
        // If we were passed non-empty form data we want the mform to call validation functions and show errors.
        $mform->is_validated();
    }
    $formheaders = array_keys($mform->formstatus);
    $nextform = array_key_exists($args->form_status, $formheaders);
    if ($nextform === false) {
        return false;
    }
    ob_start();
    $formstatus = array();
    foreach (array_values($mform->formstatus) as $k => $mformstatus) {
        $activeclass = $k == $args->form_status ? 'active' : '';
        $formstatus[] = array('name' => $mformstatus, 'activeclass' => $activeclass);
    }
    $formstatusview = new \local_program\output\form_status($formstatus);
    $return .= $renderer->render($formstatusview);
    $mform->display();
    $return .= ob_get_contents();
    ob_end_clean();

    return $return;
}

/* DM-423-Amol-starts */
function local_program_output_fragment_program_curriculum_form($args){

    global $CFG, $PAGE, $DB;
    $args = (object) $args;
    $context = $args->context;
    $return = '';
    require_once($CFG->dirroot.'/local/curriculum/program.php');
    $curriculumid = $args->ccid;
    $programid = $args->bcid;
    $levelid = $args->levelid;
    require_login();
    $renderer = $PAGE->get_renderer('local_curriculum');
    $return .= $renderer->view_for_curriculumsemesteryear($curriculumid, $yearid, $programid);
    return $return;

}
/* DM-423-Amol-ends */
function local_program_output_fragment_course_form($args) {
    global $CFG, $PAGE, $DB;
    $args = (object) $args;
    $context = $args->context;
    $return = '';
    $renderer = $PAGE->get_renderer('local_program');
    $formdata = [];
    if (!empty($args->jsonformdata)) {
        $serialiseddata = json_decode($args->jsonformdata);
        parse_str($serialiseddata, $formdata);
    }
    $formdata['bcid'] = $args->id;
    $formdata['levelid'] = $args->levelid;
    $mform = new programcourse_form(null, array('bcid' => $args->bcid, 'levelid' => $args->levelid,
        'form_status' => $args->form_status), 'post', '', null, true, $formdata);
    $programdata = new stdClass();
    $programdata->id = $args->id;
    $programdata->form_status = $args->form_status;
    $mform->set_data($programdata);

    $coursesql = "SELECT DISTINCT lcsc.semesterid, lcs.semester FROM {local_cc_semester_courses} lcsc LEFT JOIN {local_curriculum} lc ON lc.id = lcsc.curriculumid LEFT JOIN {local_curriculum_semesters} lcs
            ON lcs.id = lcsc.semesterid
            JOIN {local_program} p ON p.curriculumid = lc.id WHERE p.id = :programid";
        $params = array();
        $params['programid'] = $args->bcid;
        $coursesnames = $DB->get_records_sql($coursesql,$params);
    if (!empty((array) $serialiseddata)) {
        // If we were passed non-empty form data we want the mform to call validation functions and show errors.
        $mform->is_validated();
    }
    $formheaders = array_keys($mform->formstatus);
    $nextform = array_key_exists($args->form_status, $formheaders);
    if ($nextform === false) {
        return false;
    }
    ob_start();
    $formstatus = new \local_program\output\form_status(array_values($mform->formstatus));
    $return .= $renderer->render($formstatus);
    if($coursesnames){
        $mform->display();
    }else{
       $return .= get_string('nocourseavailable', 'local_program');
    }
    $return .= ob_get_contents();
    ob_end_clean();

    return $return;
}
/**
 * This form lists the courses of the curriculum to which the current 
 * program is mapped.
 * 
 */
class programcourse_form extends moodleform {

    public function definition() {
        global $CFG, $DB, $USER;
        $querieslib = new querylib();
        $mform = &$this->_form;
        $bcid = $this->_customdata['bcid'];
        $levelid = $this->_customdata['levelid'];
        $context = context_system::instance();

        $mform->addElement('hidden', 'programid', $bcid);
        $mform->setType('programid', PARAM_INT);

        $mform->addElement('hidden', 'levelid', $levelid);
        $mform->setType('levelid', PARAM_INT);
        $courses = array();
        $course = $this->_ajaxformdata['course'];
        if (!empty($course)) {
            $course = implode(',', $course);
            $coursessql = "SELECT c.id, c.fullname
                FROM {course} AS c
                JOIN {enrol} AS en on en.courseid=c.id AND en.enrol='program' AND en.status=0
                WHERE c.id IN ($course) AND c.visible = 1
                AND concat(',', c.open_identifiedas, ',') LIKE '%,5,%'
                AND c.id <> " . SITEID;
            $courses = $DB->get_records_sql_menu($coursessql);
        } else if ($id > 0) {
            $coursessql = "SELECT c.id, c.fullname
                FROM {course} AS c
                JOIN {enrol} AS en on en.courseid=c.id and en.enrol='program' and en.status=0
                JOIN {local_program_level_courses} AS cc ON cc.courseid = c.id
                WHERE cc.programid = $bcid AND c.visible = 1
                AND concat(',', c.open_identifiedas, ',') LIKE '%,5,%' ";
            $courses = $DB->get_records_sql_menu($coursessql);
        }
        $params = array();
        $params['programid'] = $bcid;
        $coursecount=0;
        $levelposition = $DB->get_field('local_program_levels', 'position', ['id' => $levelid]);
        $getsemester = "SELECT DISTINCT lcsc.semesterid, lcs.semester 
            FROM {local_cc_semester_courses} lcsc 
            LEFT JOIN {local_curriculum} lc ON lc.id = lcsc.curriculumid 
            LEFT JOIN {local_curriculum_semesters} lcs ON lcs.id = lcsc.semesterid
            JOIN {local_program} p ON p.curriculumid = lc.id
            WHERE p.id = $bcid 
            -- AND lcs.position = $levelposition 
            ORDER BY lcsc.semesterid ASC ";
        $semesterid = $DB->get_records_sql($getsemester);
        $levelname = $DB->get_field('local_program_levels', 'level', ['id' => $levelid]);
        
        foreach($semesterid as $key => $semestername) {
            $mform->addElement('html','<div class="assign_courses_form mb-4">');
            $mform->addElement('html', "<b class='semname'>".$semestername->semester."</b>");
            $coursesql = "SELECT  DISTINCT(c.id),c.fullname, lcsc.coursetype mandatory
                FROM {local_curriculum} lc 
                JOIN {local_cc_semester_courses} lcsc ON lcsc.curriculumid=lc.id 
                JOIN {course} c ON c.id=lcsc.courseid 
                WHERE c.id NOT IN (SELECT lcs.parentid from {local_program_level_courses} lcs WHERE c.id=lcs.parentid and lcs.programid = $bcid)
                AND lcsc.semesterid = $semestername->semesterid";

            $coursesnames = $DB->get_records_sql($coursesql,$params);
            $coursearray=array();
            /**
             * ODL-866:Ikram Code Starts Here..
             * 
             * Differentiating the core courses and elective courses.
             **/
            $mform->addElement('html','<div class="sem_card p-3">');
            $mform->addElement('html', '<div class="card"><div class="card-body bold py-2 px-0">'.get_string('core_courses', 'local_program').'</div>
                    <div class="container" >');
            foreach($coursesnames as $courses_lists){
                $coursecount ++;
                if ($courses_lists->mandatory == 1) {
                    $courseids = 'course['.$courses_lists->id.']';
                    $c_ids[] = $courses_lists->id;
                    $coursearray['_mand'][] = $mform->createElement('advcheckbox', $courseids, $courses_lists->fullname, '', array('group' => 2, 'CourseType' => 'mandatory', 'class' => "mandatory"), array(0, $courses_lists->id));
                }else{
                    continue;
                }
            }
            $m_ids = implode(',', $c_ids);
            $mform->addElement('hidden', 'mandatory_courseids', $m_ids);
            $mform->addElement('hidden', 'levelposition', $levelposition);
            if(!empty($coursearray['_mand'])){  
                $mform->addGroup($coursearray['_mand']);
                $mform->addElement('html', '</div></div>');
            }else{
                $mform->addElement('html', html_writer::div(get_string('nocourse', 'local_program'), 'text-center bold').'</div></div>');
            }
            $mform->addElement('html', '<div class="card mt-1"><div class="card-body bold py-2 px-0">'.get_string('elective_courses', 'local_program').'</div>');
            $haselectiveval = $DB->get_field('local_program_levels', 'has_course_elective', array('programid' => $bcid, 'id' => $levelid));
            foreach($coursesnames as $courses_lists){
                $coursecount ++;
                if ($courses_lists->mandatory == 0) {
                    $courseids = 'course['.$courses_lists->id.']';
                    $el_ids[] = $courses_lists->id;
                    if ($haselectiveval > 0) {
                        $coursearray['_elec'][] = $mform->createElement('advcheckbox', $courseids, $courses_lists->fullname, '', array('group' => 2, 'CourseType' => 'elective'), array(0, $courses_lists->id));
                    } else {
                        $coursearray['_elec'][] = $mform->createElement('advcheckbox', $courseids, $courses_lists->fullname, '', array('group' => 2, 'CourseType' => 'elective', 'disabled' => 'disabled'), array(0, $courses_lists->id));
                    }
                } else {
                    continue;
                }
            }
            $e_ids = implode(',', $el_ids);
            $mform->addElement('hidden', 'elective_courseids', $e_ids, 'class = "elective_course"');
            if(!empty($coursearray['_elec'])){
                $mform->addGroup($coursearray['_elec']);
                $mform->addElement('html', '</div></div>');
            }else{
                $mform->addElement('html', html_writer::div(get_string('nocourse', 'local_program'), 'text-center bold').'</div></div>');
            }
            // if(empty($coursearray)) {
            //     $mform->addElement('static', '', '', get_string('no_cr_avl','local_groups'));
            // }
            $mform->addElement('html','</div>');
            $mform->addElement('html','</div>');
        }
        /** Ikram code ends here.. **/
        $variable=$DB->count_records_sql("SELECT count(sc.courseid) 
            FROM {local_cc_semester_courses} as sc 
            JOIN {local_program} as p ON p.curriculumid=sc.curriculumid 
            WHERE p.id=$bcid");
        $varcount=$DB->count_records_sql("SELECT count(plc.parentid) 
            FROM {local_program_level_courses} as plc 
            JOIN {local_program} as p ON p.id=plc.programid 
            WHERE p.id=$bcid");
        // if($variable != $varcount && $coursecount >1){
        //     $this->add_checkbox_controller(2);
        // }
    }
}


/**
 * User selector subclass for the list of potential users on the assign roles page,
 * when we are assigning in a context below the course level. (CONTEXT_MODULE and
 * some CONTEXT_BLOCK).
 *
 * This returns only enrolled users in this context.
 */
class local_program_potential_users extends user_selector_base {
    protected $programid;
    protected $context;
    protected $courseid;
    /**
     * @param string $name control name
     * @param array $options should have two elements with keys groupid and courseid.
     */
    public function __construct($name, $options) {
        global $CFG;
        if (isset($options['context'])) {
            $this->context = $options['context'];
        } else {
            $this->context = context::instance_by_id($options['contextid']);
        }
        $options['accesscontext'] = $this->context;
        parent::__construct($name, $options);
        $this->programid = $options['programid'];
        $this->organization = $options['organization'];
        $this->department = $options['department'];
        $this->email = $options['email'];
        $this->idnumber = $options['idnumber'];        $this->uname = $options['uname'];
        $this->searchanywhere = true;
        require_once($CFG->dirroot . '/group/lib.php');
    }

    protected function get_options() {
        global $CFG;
        $options = parent::get_options();
        $options['file'] = 'local/program/lib.php';
        $options['programid'] = $this->programid;
        $options['contextid'] = $this->context->id;
        return $options;
    }

    public function find_users($search) {
        global $DB;
        $params = array();
        $program = $DB->get_record('local_program', array('id' => $this->programid));
        if (empty($program)) {
            print_error('program not found!');
        }

        // Now we have to go to the database.
        list($wherecondition, $params) = $this->search_sql($search, 'u');

        if ($wherecondition) {
            $wherecondition = ' AND ' . $wherecondition;
        }

        $fields      = 'SELECT ' . $this->required_fields_sql('u');
        $countfields = 'SELECT COUNT(u.id)';
        $params['confirmed'] = 1;
        $params['suspended'] = 0;
        $params['deleted'] = 0;

        $sql   = " FROM {user} AS u
                  WHERE 1 = 1
                        {$wherecondition}
                    AND u.id > 2 AND u.confirmed = :confirmed AND u.suspended = :suspended
                    AND u.deleted = :deleted
                        ";
        if ($program->costcenter && (has_capability('local/program:manageprogram', context_system::instance())) && ( !is_siteadmin() && (!has_capability('local/program:manage_multiorganizations', context_system::instance()) && !has_capability('local/costcenter:manage_multiorganizations', context_system::instance())))) {
            $sql .= " AND u.open_costcenterid = :costcenter";
            $params['costcenter'] = $program->costcenter;

            if ($program->department && (has_capability('local/program:manage_owndepartments', context_system::instance()) || has_capability('local/costcenter:manage_owndepartments', context_system::instance()))) {
               $sql .= " AND u.open_departmentid = :department";
               $params['department'] = $program->department;
            }
        }

        if (!empty($this->email)) {
            $sql .= " AND u.id IN ({$this->email})";
        }
        if (!empty($this->uname)) {
            $sql .= " AND u.id IN ({$this->uname})";
        }
        if (!empty($this->department)) {
            $sql .= " AND u.open_departmentid IN ($this->department)";
        }
        if (!empty($this->idnumber)) {
            $sql .= " AND u.id IN ($this->idnumber)";
        }

        $options = array('contextid' => $this->context->id, 'programid' => $this->programid, 'email' => $this->email, 'uname' => $this->uname, 'department' => $this->department, 'idnumber' => $this->idnumber, 'organization' => $this->organization);
        $local_program_existing_users = new local_program_existing_users('removeselect', $options);
        $enrolleduerslist = $local_program_existing_users->find_users('', true);
        if (!empty($enrolleduerslist)) {
            $enrolleduers = implode(',', $enrolleduerslist);
            $sql .= " AND u.id NOT IN ($enrolleduers)";
        }

        list($sort, $sortparams) = users_order_by_sql('u', $search, $this->accesscontext);
        $order = ' ORDER BY ' . $sort;

        // Check to see if there are too many to show sensibly.
        if (!$this->is_validating()) {
            $potentialmemberscount = $DB->count_records_sql($countfields . $sql, $params);
            if ($potentialmemberscount > $this->maxusersperpage) {
                return $this->too_many_results($search, $potentialmemberscount);
            }
        }
        // If not, show them.
        $availableusers = $DB->get_records_sql($fields . $sql . $order, array_merge($params, $sortparams));

        if (empty($availableusers)) {
            return array();
        }

        if ($search) {
            $groupname = get_string('potusersmatching', 'local_program', $search);
        } else {
            $groupname = get_string('potusers', 'local_program');
        }

        return array($groupname => $availableusers);
    }
}

/**
 * User selector subclass for the list of users who already have the role in
 * question on the assign roles page.
 */
class local_program_existing_users extends user_selector_base {
    protected $programid;
    protected $context;
    // protected $courseid;
    /**
     * @param string $name control name
     * @param array $options should have two elements with keys groupid and courseid.
     */
    public function __construct($name, $options) {
        global $CFG;
        $this->searchanywhere = true;
        if (isset($options['context'])) {
            $this->context = $options['context'];
        } else {
            $this->context = context::instance_by_id($options['contextid']);
        }
        $options['accesscontext'] = $this->context;
        parent::__construct($name, $options);
        $this->programid = $options['programid'];
        $this->organization = $options['organization'];
        $this->department = $options['department'];
        $this->email = $options['email'];
        $this->idnumber = $options['idnumber'];
        $this->uname = $options['uname'];
        require_once($CFG->dirroot . '/group/lib.php');
    }

    protected function get_options() {
        global $CFG;
        $options = parent::get_options();
        $options['file'] = 'local/program/lib.php';
        $options['programid'] = $this->programid;
        $options['contextid'] = $this->context->id;
        return $options;
    }
    public function find_users($search, $idsreturn = false) {
        global $DB;

        list($wherecondition, $params) = $this->search_sql($search, 'u');

        $params['programid'] = $this->programid;
        $fields = "SELECT DISTINCT u.id, " . $this->required_fields_sql('u') ;
        $countfields = "SELECT COUNT(DISTINCT u.id) ";
        $params['confirmed'] = 1;
        $params['suspended'] = 0;
        $params['deleted'] = 0;
        $sql = " FROM {user} AS u
                JOIN {local_program_users} AS cu ON cu.userid = u.id
                 WHERE {$wherecondition}
                AND u.id > 2 AND u.confirmed = :confirmed AND u.suspended = :suspended
                    AND u.deleted = :deleted AND cu.programid = :programid";
        if (!empty($this->email)) {
            $sql.=" AND u.id IN ({$this->email})";
        }
       if (!empty($this->uname)) {
            $sql .=" AND u.id IN ({$this->uname})";
        }
        if (!empty($this->department)) {
            $sql .=" AND u.open_departmentid IN ($this->department)";
        }
        if (!empty($this->idnumber)) {
            $sql .=" AND u.id IN ($this->idnumber)";
        }
        if (!$this->is_validating()) {
            $existinguserscount = $DB->count_records_sql($countfields . $sql, $params);
            if ($existinguserscount > $this->maxusersperpage) {
                return $this->too_many_results($search, $existinguserscount);
            }
        }
        if ($idsreturn) {
            $contextusers = $DB->get_records_sql_menu('SELECT DISTINCT u.id, u.id as userid ' . $sql, $params);
            return $contextusers;
        } else {
            $order = " ORDER BY u.id DESC";
            $contextusers = $DB->get_records_sql($fields . $sql . $order, $params);
        }

        // No users at all.
        if (empty($contextusers)) {
            return array();
        }

        if ($search) {
            $groupname = get_string('enrolledusersmatching', 'enrol', $search);
        } else {
            $groupname = get_string('enrolledusers', 'enrol');
        }
        return array($groupname => $contextusers);
    }

    protected function this_con_group_name($search, $numusers) {
        if ($this->context->contextlevel == CONTEXT_SYSTEM) {
            // Special case in the System context.
            if ($search) {
                return get_string('extusersmatching', 'local_program', $search);
            } else {
                return get_string('extusers', 'local_program');
            }
        }
        $contexttype = context_helper::get_level_name($this->context->contextlevel);
        if ($search) {
            $a = new stdClass;
            $a->search = $search;
            $a->contexttype = $contexttype;
            if ($numusers) {
                return get_string('usersinthisxmatching', 'core_role', $a);
            } else {
                return get_string('noneinthisxmatching', 'core_role', $a);
            }
        } else {
            if ($numusers) {
                return get_string('usersinthisx', 'core_role', $contexttype);
            } else {
                return get_string('noneinthisx', 'core_role', $contexttype);
            }
        }
    }

    protected function parent_con_group_name($search, $contextid) {
        $context = context::instance_by_id($contextid);
        $contextname = $context->get_context_name(true, true);
        if ($search) {
            $a = new stdClass;
            $a->contextname = $contextname;
            $a->search = $search;
            return get_string('usersfrommatching', 'core_role', $a);
        } else {
            return get_string('usersfrom', 'core_role', $contextname);
        }
    }
}

function local_program_output_fragment_new_catform($args) {
    global $CFG, $DB;

    $args = (object) $args;
    $context = $args->context;
    $categoryid = $args->categoryid;
    $o = '';
    $formdata = [];
    if (!empty($args->jsonformdata)) {
        $serialiseddata = json_decode($args->jsonformdata);
        parse_str($serialiseddata, $formdata);
    }

    if ($args->categoryid > 0) {
        $heading = 'Update category';
        $collapse = false;
        $data = $DB->get_record('local_program_categories', array('id' => $categoryid));
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

    $mform = new local_program\form\catform(null, array('editoroptions' => $editoroptions), 'post', '', null, true, $formdata);

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
function program_filter($mform){
    global $DB,$USER;
    $stable = new stdClass();
    $stable->thead = false;
    $stable->start = 0;
    $stable->length = -1;
    $stable->search = '';
    $concatsql = '';
    $systemcontext = context_system::instance();
    if ($systemcontext) {
        $program_sql = "SELECT bc.id  FROM {local_program} AS bc ";
        if (has_capability('local/program:manageprogram', $systemcontext)
            && !is_siteadmin()
            && !has_capability('local/program:manage_multiorganizations', $systemcontext)
            && !has_capability('local/costcenter:manage_multiorganizations', $systemcontext)
            && has_capability('local/costcenter:manage_ownorganization', $systemcontext)) {
                $joinon = "cc.id = bc.costcenter";
                $concatsql = " AND bc.costcenter = {$USER->open_costcenterid} ";
        } else if (/*(has_capability('local/program:manage_owndepartments', $systemcontext) ||*/
            !is_siteadmin() && has_capability('local/costcenter:manage_owndepartments', $systemcontext)) {
                $joinon = "cc.id = bc.department OR cc.id = bc.costcenter";
                $concatsql = " AND bc.costcenter = {$USER->open_costcenterid} AND CONCAT(',',bc.department,',') LIKE '%,{$USER->open_departmentid},%' ";
        } else if (/*(has_capability('local/program:manage_owndepartments', $systemcontext) ||*/
            !is_siteadmin() && has_capability('local/costcenter:manage_ownsubdepartments', $systemcontext)) {
                $joinon = "cc.id = bc.department OR cc.id = bc.costcenter";
                $concatsql = " AND bc.costcenter = {$USER->open_costcenterid} AND CONCAT(',',bc.department,',') LIKE '%,{$USER->open_departmentid},%'AND CONCAT(',',bc.subdepartment,',') LIKE '%,{$USER->open_subdepartment},%' ";
        }
        else {
            $joinon = " cc.id = bc.costcenter ";
        }
        $program_sql .= " JOIN {local_costcenter} AS cc ON $joinon
                WHERE 1 = 1 ";
        $program_sql .= $concatsql;
        $programids = $DB->get_fieldset_sql($program_sql);
        $componentid = implode(',', $programids);
        if (!empty($componentid)) {
            $courseslist = $DB->get_records_sql_menu("SELECT id, name FROM {local_program}
                WHERE id IN ($componentid)");
        } else {
            $courseslist = $DB->get_records_sql_menu("SELECT id, name FROM {local_program} ");
        }
    }
    $select = $mform->addElement('autocomplete', 'program', '', $courseslist,
        array('placeholder' => get_string('program_name', 'local_program')));
    $mform->setType('program', PARAM_RAW);
    $select->setMultiple(true);
}
function get_user_program($userid) {
    global $DB;
    $sql = "SELECT lc.id, lc.name, lc.description
                FROM {local_program} AS lc
                JOIN {local_program_users} AS lcu ON lcu.programid = lc.id
                WHERE userid = :userid AND lc.status IN (1, 4)";
    $programs = $DB->get_records_sql($sql, array('userid' => $userid));
    return $programs;
}

class program_managelevel_form extends moodleform {

    public function definition() {
        global $CFG, $DB, $USER;
        $querieslib = new querylib();
        $mform = &$this->_form;
        $id = $this->_customdata['id'];
        $programid = $this->_customdata['programid'];
        $context = context_system::instance();

        $mform->addElement('hidden', 'id', $id);
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'programid', $programid);
        $mform->setType('programid', PARAM_INT);

        $position = 0;
        $position = $DB->get_field('local_program_levels', 'position', ['programid' => $programid]);
        if (!empty($position) && $position > 0) {
            $position = $position+1;
        }else{
            $position = 1;
        }
        $mform->addElement('hidden', 'position', $position);
        $mform->setType('position', PARAM_INT);

        $mform->addElement('text', 'level', get_string('level', 'local_program'));
        $mform->addRule('level', null, 'required', null, 'client');

        $mform->addElement('date_selector', 'startdate', get_string('startdate', 'local_program'));
        $mform->addRule('startdate', get_string('missingstartdate','local_program'), 'required', null);

        $mform->addElement('date_selector', 'enddate', get_string('enddate', 'local_program'));
        $mform->addRule('enddate', get_string('missingenddate','local_program'), 'required', null);

        /* DM-123 - Bhagyeshwar - added one text field semester credits */
        // $mform->addElement('text', 'semister_credits', get_string('semister_credits', 'local_program'));
        // $mform->addRule('semister_credits', null, null, 'client');

        // ODL-866: Adding elective option to semesters.
        $levelrecord = $DB->get_record('local_program_levels', array('id' => $id, 'programid' => $programid));
        $prglvlcourses = (new program)->program_level_courses($programid, $id, false);
        $electives = array();
        foreach ($prglvlcourses as $prglvlcourse) {
            if ($prglvlcourse->mandatory == 0) {
                $electives[] = $prglvlcourse;
            }
        }
        if (count($electives) > 0) {
            foreach ($electives as $elective) {
                $role = $DB->get_field('role', 'id', array('shortname' => 'student'));
                $contextcourse = get_context_instance(CONTEXT_COURSE, $elective->id);
                $students = get_role_users($role, $contextcourse);
            }
        }
        $has_electives=[];
        if ($levelrecord->has_course_elective > 0 && $levelrecord->course_elective > 0 && count($students) > 0) {
            $has_electives[] = $mform->createElement('advcheckbox', 'hascourseelective', get_string('has_course_elative', 'local_program'), '', 'disabled');
            $mform->addGroup($has_electives,  'advcheckbox', '', array(' '), false);
            $mform->addElement('static', 'cannoteditelective', '', get_string('editnotpossible', 'local_program'));
            $mform->addElement('text', 'courseelective', get_string('course_elective', 'local_program'), array('placeholder'=> get_string('give_number', 'local_program'), 'maxlength' => '1', 'disabled'), $levelrecord->course_elective);
            $mform->addHelpButton('courseelective', 'course_elective', 'local_program');
            // $mform->addRule('courseelective', get_string('numbersonly', 'local_program'), 'numeric', null, null, 'client');
            $mform->setDefault('hascourseelective', 1);
            $mform->addElement('hidden', 'has_course_elective', $levelrecord->has_course_elective);
            $mform->setDefault('courseelective', $levelrecord->course_elective);
            $mform->addElement('hidden', 'course_elective', $levelrecord->course_elective);
        } else {
            $has_electives[] = $mform->createElement('advcheckbox', 'has_course_elective', get_string('has_course_elative', 'local_program'));
            $mform->addGroup($has_electives,  'advcheckbox', '', array(' '), false);
            $mform->addElement('text', 'course_elective', get_string('course_elective', 'local_program'), array('placeholder'=> get_string('give_number', 'local_program'), 'maxlength' => '1'));
            $mform->addElement('static', 'cannoteditelective', '', get_string('editnotpossible', 'local_program'));
            $mform->addHelpButton('course_elective', 'course_elective', 'local_program');
            $mform->addRule('course_elective', get_string('numbersonly', 'local_program'), 'numeric', null, null, 'client');
            $mform->hideIf('course_elective', 'has_course_elective', 'neq', 1);
            $mform->hideIf('cannoteditelective', 'has_course_elective', 'neq', 1);
            // ODL-866: Code END
        }
        $courseexists = $DB->record_exists('local_program_level_courses', array('programid' => $programid, 'levelid' => $id));
        
        $course_elective_count = $DB->get_field('local_program_levels','course_elective',array('programid' => $programid, 'id' => $id));

        $levelcoursescount = count($prglvlcourses);
        $count = 0;
        foreach ($prglvlcourses as $value) {
            if($value->mandatory == 0){
                $elec_crs_count++;
            }
            $activitycompletioncriteria = $DB->record_exists('course_completion_criteria', array('course'=>$value->id));
            if($activitycompletioncriteria){
                $count++;
            }
        }

        if ($courseexists) {
            if($levelcoursescount <= $count && $course_elective_count <= $elec_crs_count){
                // if($levelcoursescount <= $count && ){
                $mform->addElement('advcheckbox', 'active', get_string('active', 'local_program'), get_string('tomakeactive', 'local_program'), array('group' => 1), array(0, 1));
                // $mform->addHelpButton('active', 'active', 'local_program');
            }
            else {
                $mform->addElement('advcheckbox', 'active', get_string('active', 'local_program'), get_string('activate', 'local_program'), 'disabled');
                // $mform->addHelpButton('active', 'actives', 'local_program');
            }
        } else {
            $mform->addElement('advcheckbox', 'active', get_string('active', 'local_program'), get_string('activate', 'local_program'), 'disabled');
            // $mform->addHelpButton('active', 'actives', 'local_program');
        }
        $mform->disabledIf('enrolmethod', 'active','eq',0);
        $studentenrolment = array();
        $studentenrolment[NULL] = get_string('selectenroltype','local_program');

        $studentenrolment[1] = get_string('enrolautomatically','local_program');
        $studentenrolment[2] = get_string('manualenrol','local_program');
        $mform->addElement('select','enrolmethod',get_string('studentenrolments','local_program'),$studentenrolment);

        $mform->addElement('editor', 'level_description', get_string('description', 'local_program'));
        $mform->setType('level_description', PARAM_RAW);

        $mform->disable_form_change_checker();
    }

    public function validation($data, $files) {
        global $DB, $CFG;
        $id = $this->_customdata['id'] > 0 ? $this->_customdata['id'] : 0;
        $errors = parent::validation($data, $files);
        $programstartdate = $DB->get_field('local_program', 'program_startdate', array('id' => $data['programid']));
        $programdata = $DB->get_record('local_program', array('id' => $data['programid']));
        
        if ($id == 0) {
            $alllevel = $DB->get_records('local_program_levels');
            foreach($alllevel as $alllevelvalues) {
                $lastlevelid = $alllevelvalues->id;
            }
            $id = $lastlevelid + 1;
        }
        
        // To get the previous sem end-date
        $previousssemid = "SELECT id, enddate, startdate
                            FROM {local_program_levels}
                           WHERE id = (SELECT max(id)
                                        FROM {local_program_levels}
                                        WHERE id < $id AND programid = $programdata->id
                                    )";
        $semestersids = $DB->get_record_sql($previousssemid);
        $lastsemenddate = date('d-m-Y', $semestersids->enddate);
        if ($id > $semestersids->id) {
            if ($data['startdate'] <= $semestersids->enddate) {
                $errors['startdate'] = get_string('next_semdate_error', 'local_program', $lastsemenddate);
            }
            if ($data['enddate'] <= $data['startdate']) {
                $errors['enddate'] = get_string('duration_sem_end_date_error', 'local_program');
            }
            if ($programdata->duration_format == 'M') {
                $format = $programdata->duration;
                $sdate = $programdata->program_startdate;
                if ($sdate) {
                    $enddate = date('d-m-Y', strtotime('+ '.$format.' months', $sdate));
                    if ($data['enddate'] >= $data['startdate']) {
                        if($data['enddate'] > strtotime($enddate)){
                            $errors['enddate'] = get_string('duration_end_date_error', 'local_program', $enddate);
                        }
                    }
                } else {
                    $enddate = 'N/A';
                }
            } else {
                $format = $programdata->duration;
                $sdate = $programdata->program_startdate;
                if ($sdate) {
                    $enddate = date('d-m-Y', strtotime('+ '.$format.' year', $sdate));
                    if ($data['enddate'] >= $data['startdate']) {
                        if($data['enddate'] > strtotime($enddate)){
                            $errors['enddate'] = get_string('duration_end_date_error', 'local_program', $enddate);
                        }
                    }
                } else {
                    $enddate = 'N/A';
                }
            }
        } else {
            if ($data['startdate'] <= $semestersids->enddate) {
                $errors['startdate'] = get_string('next_semdate_error', 'local_program', $lastsemenddate);
            }
            if ($data['enddate'] <= $data['startdate']) {
                $errors['enddate'] = get_string('duration_sem_end_date_error', 'local_program');
            }
            if ($programdata->duration_format == 'M') {
                $format = $programdata->duration;
                $sdate = $programdata->program_startdate;
                if ($sdate) {
                    $enddate = date('d-m-Y', strtotime('+ '.$format.' months', $sdate));
                    if ($data['enddate'] >= $data['startdate']) {
                        if($data['enddate'] > strtotime($enddate)){
                            $errors['enddate'] = get_string('duration_end_date_error', 'local_program', $enddate);
                        }
                    }
                } else {
                    $enddate = 'N/A';
                }
            } else {
                $format = $programdata->duration;
                $sdate = $programdata->program_startdate;
                if ($sdate) {
                    $enddate = date('d-m-Y', strtotime('+ '.$format.' year', $sdate));
                    if ($data['enddate'] >= $data['startdate']) {
                        if($data['enddate'] > strtotime($enddate)){
                            $errors['enddate'] = get_string('duration_end_date_error', 'local_program', $enddate);
                        }
                    }
                } else {
                    $enddate = 'N/A';
                }
            }
        }
        if ($id > 0) {
            if ($data['startdate'] != 0 && $data['startdate'] < $programstartdate) {
                $errors['startdate'] = get_string('startdterr', 'local_program', date('d-m-Y', $programstartdate));
            }
        } else {
            if ($data['startdate'] != 0 && $data['startdate'] < $programstartdate) {
                $errors['startdate'] = get_string('startdterr', 'local_program', date('d-m-Y', $programstartdate));
            }
        }
        if ($data['has_course_elective']) {
            if (!empty($data['course_elective'])) {
                if (!is_numeric($data['course_elective'])) {
                    $errors['course_elective'] = get_string('numbersonly', 'local_program');
                }
            }
            if ($data['course_elective'] == 0) {
                $errors['course_elective'] = get_string('zeronotallowed', 'local_program', $data['course_elective']);
            }
            if ($data['course_elective'] < 0 || $data['course_elective'] > 9) {
                $errors['course_elective'] = get_string('numbersbetween', 'local_program', $data['course_elective']);
            }
        }
            $params = array();
        if($data['active'] == 1) {
            if($data['enrolmethod'] == ''){
                $errors['enrolmethod'] = get_string('pleaseselectenroltype', 'local_program');
            }
            $params['levelid'] =  $data['id'];
            $params['programid'] = $data['programid'];
            $params['mandatory'] = 0;
    $electcountsql =  "SELECT COUNT(lplc.id) FROM {local_program_level_courses} lplc WHERE lplc.levelid = :levelid AND lplc.programid = :programid AND lplc.mandatory = :mandatory";
    $electcountdata = $DB->count_records_sql($electcountsql, $params);

            if($electcountdata < $data['course_elective']){
                $errors['course_elective'] = get_string('choosenelective', 'local_program');
            }
        }
        
        return $errors;
    }
}


function local_program_output_fragment_program_managelevel_form($args) {
    global $CFG, $PAGE, $DB;
    $args = (object) $args;
    $context = $args->context;
    $return = '';
    $renderer = $PAGE->get_renderer('local_program');
    $formdata = [];
    if (!empty($args->jsonformdata)) {
        $serialiseddata = json_decode($args->jsonformdata);
        parse_str($serialiseddata, $formdata);
    }
    $formdata['id'] = $args->id;
    $formdata['programid'] = $args->programid;

    $mform = new program_managelevel_form(null, array('id' => $args->id,
        'programid' => $args->programid, 'form_status' => $args->form_status), 'post', '', null,
        true, $formdata);
    $bclevel = new stdClass();
    $bclevel->programid = $args->programid;
    if ($args->id > 0) {
        $bclevel = $DB->get_record('local_program_levels', array('id' => $args->id));
    }

    $bclevel->form_status = $args->form_status;
    $bclevel->level_description['text'] = $bclevel->description;
    $mform->set_data($bclevel);

    if (!empty((array) $serialiseddata)) {
        // If we were passed non-empty form data we want the mform to call validation functions and show errors.
        $mform->is_validated();
    }
    $formheaders = array_keys($mform->formstatus);
    $nextform = array_key_exists($args->form_status, $formheaders);
    if ($nextform === false) {
        return false;
    }
    ob_start();
    $formstatus = array();
    foreach (array_values($mform->formstatus) as $k => $mformstatus) {
        $activeclass = $k == $args->form_status ? 'active' : '';
        $formstatus[] = array('name' => $mformstatus, 'activeclass' => $activeclass);
    }
    $formstatusview = new \local_program\output\form_status($formstatus);
    $return .= $renderer->render($formstatusview);
    $mform->display();
    $return .= ob_get_contents();
    ob_end_clean();

    return $return;
}
/*
* Author Rizwana
* Displays a node in left side menu
* @return  [type] string  link for the leftmenu
*/
function local_program_leftmenunode(){
    $systemcontext = context_system::instance();
    $programnode = '';
    if(((has_capability('local/program:manageprogram', context_system::instance())) &&
        (!has_capability('local/program:trainer_viewprogram', context_system::instance()))) ||
        (is_siteadmin())) {
        $programnode .= html_writer::start_tag('li', array('id'=> 'id_leftmenu_browseprograms', 'class'=>'pull-left user_nav_div browseprograms'));
            $programs_url = new moodle_url('/local/program/index.php');
            $program_icon = '<span class="mp_wht_structure_icon dypatil_cmn_icon icon"></span>';
            $programs = html_writer::link($programs_url, $program_icon.'<span class="user_navigation_link_text">'.get_string('browse_programs','local_program').'</span>',array('class'=>'user_navigation_link'));
            $programnode .= $programs;
        $programnode .= html_writer::end_tag('li');
    }

    return array('7' => $programnode);
}
function local_program_quicklink_node(){
    global $CFG, $PAGE, $OUTPUT;
    $systemcontext = context_system::instance();
    $stable = new stdClass();
    if(has_capability('local/program:manageprogram', $systemcontext) || is_siteadmin()){

        $PAGE->requires->js_call_amd('local_program/ajaxforms', 'load');

        $programs = array();
        $programs['node_header_string'] = get_string('manage_br_programs', 'local_program');
        $programs['pluginname'] = 'bootcamp';
        $programs['quicknav_pluginclass'] = 'quicknav_icon_programs';
        $programs['plugin_icon_class'] = FALSE;
        if(has_capability('local/program:createprogram', $systemcontext) || is_siteadmin()){
            $programs['create'] = TRUE;
            $programs['create_element'] = html_writer::link('javascript:void(0)', get_string('create'), array('class' => 'quick_nav_link goto_local_program', 'title' => get_string('create_program', 'local_program'), 'onclick' => '(function(e){ require("local_program/ajaxforms").init({contextid: '.$systemcontext->id.', component:"local_program", callback:"program_form", form_status:0, plugintype: "local", pluginname: "program", id:0, title: "createprogram" }) })(event)'));
        }
        $programs['viewlink_url'] = $CFG->wwwroot.'/local/program/index.php';
        $programs['view'] = TRUE;
        $programs['viewlink_title'] = get_string('view_programs', 'local_program');
        $programs['space_count'] = 'one';
        $content = $OUTPUT->render_from_template('block_quick_navigation/quicklink_node', $programs);
    }

    return array('8' => $content);
}

/**
 * process the bootcamp_mass_enroll
 * @param csv_import_reader $cir  an import reader created by caller
 * @param Object $bootcamp  a bootcamp record from table mdl_local_bootcamp
 * @param Object $context  course context instance
 * @param Object $data    data from a moodleform
 * @return string  log of operations
 */
function program_mass_enroll($cir, $program, $context, $data) {
    global $CFG,$DB, $USER;
    require_once ($CFG->dirroot . '/group/lib.php');

    $emaillogs = new \local_program\notification();
    // init csv import helper
    $useridfield = $data->firstcolumn;
    $cir->init();
    $enrollablecount = 0;
    while ($fields = $cir->next()) {
        $a = new stdClass();
        if (empty ($fields))
            continue;
        $fields[0]= str_replace('"', '', trim($fields[0]));
        /*First Condition To validate users*/
        $systemcontext = \context_system::instance();
        if(is_siteadmin() || has_capability('local/costcenter:manage_multiorganizations', $systemcontext)){
            $sql="SELECT u.* from {user} u where u.deleted=0 and u.suspended=0 and u.$useridfield LIKE '{$fields[0]}'";
        }else if(has_capability('local/costcenter:manage_ownorganization', $systemcontext)){
            $sql="SELECT u.* from {user} u where u.deleted=0 and u.suspended=0 and u.$useridfield LIKE '{$fields[0]}' AND u.open_costcenterid={$USER->open_costcenterid} ";
        }else{
            $sql="SELECT u.* from {user} u where u.deleted=0 and u.suspended=0 and u.$useridfield LIKE '{$fields[0]}' AND u.open_costcenterid={$USER->open_costcenterid} AND u.open_departmentid = {$USER->open_departmentid} ";
        }

        if (!$user = $DB->get_record_sql($sql)) {
            $result .= '<div class="alert alert-error">'.get_string('im:user_unknown', 'local_courses', $fields[0] ). '</div>';
            continue;
        } else {

            $allow = true;
            $type = 'program_enrol';
            $dataobj = $program->id;
            $fromuserid = $USER->id;
            if ($allow) {
                    if (true) {
                        $programuser = new stdClass();
                        $programuser->programid = $program->id;
                        $programuser->courseid = 0;
                        $programuser->userid = $user->id;
                        $programuser->supervisorid = 0;
                        $programuser->prefeedback = 0;
                        $programuser->postfeedback = 0;
                        $programuser->trainingfeedback = 0;
                        $programuser->confirmation = 0;
                        $programuser->attended_sessions = 0;
                        $programuser->hours = 0;
                        $programuser->completion_status = 0;
                        $programuser->completiondate = 0;
                        $programuser->usercreated = $USER->id;
                        $programuser->timecreated = time();
                        $programuser->usermodified = $USER->id;
                        $programuser->timemodified = time();
                        try {
                            $programuser->id = $DB->insert_record('local_program_users',
                            $programuser);
                            $local_program = $DB->get_record('local_program', array('id' => $program->id));

                            $params = array(
                                'context' => context_system::instance(),
                                'objectid' => $programuser->id,
                                'other' => array('programid' => $program->id)
                            );

                            $event = \local_program\event\program_users_enrol::create($params);
                            $event->add_record_snapshot('local_program_users', $programuser);
                            $event->trigger();

                            if ($local_program->status == 0) {
                                $touser = \core_user::get_user($programuser->userid);
                                $email_logs = $emaillogs->program_notification($type, $touser, $USER, $local_program);
                            }
                            $result .= '<div class="alert alert-success">'.get_string('im:enrolled_ok', 'local_courses', fullname($user)).'</div>';
                            $enrollablecount ++;
                        } catch (dml_exception $ex) {
                            print_error($ex);
                        }
                    } else {
                        break;
                    }
                // }
                $programid = $program->id;
                $program = new stdClass();
                $program->id = $programid;
                $program->totalusers = $DB->count_records('local_program_users',
                    array('programid' => $programid));
                $DB->update_record('local_program', $program);
            }
        }
    }
    $result .= '<br />';//exit;
    $result .= get_string('im:stats_i', 'local_program', $enrollablecount) . "";
    return $result;
}

/*
* Author Sarath
* return count of programs under selected costcenter
* @return  [type] int count of programs
*/
function costcenterwise_program_count($costcenter, $department = false, $subdepartment=false){
    global $USER, $DB,$CFG;
        $params = array();
        $params['costcenter'] = $costcenter;
        $countprogramql = "SELECT count(id) FROM {local_program} WHERE costcenter = :costcenter";
        if ($department) {
            $countprogramql .= " AND department = :department ";
            $params['department'] = $department;
        }
        if ($subdepartment) {
            $countprogramql .= " AND subdepartment = :subdepartment ";
            $params['subdepartment'] = $subdepartment;
        }
        $activesql = " AND visible = 1 ";
        $inactivesql = " AND visible = 0 ";

        $countprograms = $DB->count_records_sql($countprogramql, $params);
        $activeprograms = $DB->count_records_sql($countprogramql.$activesql, $params);
        $inactiveprograms = $DB->count_records_sql($countprogramql.$inactivesql, $params);
        if($countprograms >= 0){
            if($costcenter){
                $viewprogramlinkurl = $CFG->wwwroot.'/local/program/index.php?costcenterid='.$costcenter;
            }
            if ($department) {
                $viewprogramlinkurl = $CFG->wwwroot.'/local/program/index.php?departmentid='.$department; 
            } 
            if ($subdepartment) {
                $viewprogramlinkurl = $CFG->wwwroot.'/local/program/index.php?subdepartmentid='.$subdepartment; 
            }

        }
        if($activeprograms >= 0){
            if($costcenter){
                $countprogramactivelinkurl = $CFG->wwwroot.'/local/program/index.php?status=active&costcenterid='.$costcenter;
            }
            if ($department) {
                $countprogramactivelinkurl = $CFG->wwwroot.'/local/program/index.php?status=active&departmentid='.$department; 
            }
            if ($subdepartment) {
                $countprogramactivelinkurl = $CFG->wwwroot.'/local/program/index.php?status=active&subdepartmentid='.$subdepartment; 
            }
        }
        if($inactiveprograms >= 0){
            if($costcenter){
                $countprograminactivelinkurl = $CFG->wwwroot.'/local/program/index.php?status=inactive&costcenterid='.$costcenter;
            }
            if ($department) {
                $countprograminactivelinkurl = $CFG->wwwroot.'/local/program/index.php?status=inactive&departmentid='.$department; 
            }
            if ($subdepartment) {
                $countprograminactivelinkurl = $CFG->wwwroot.'/local/program/index.php?status=inactive&subdepartmentid='.$subdepartment; 
            }
        }
    return array(
        'program_plugin_exist' => true,
        'allprogramcount' => $countprograms,
        'activeprogramcount' => $activeprograms,
        'inactiveprogramcount' => $inactiveprograms,
        'viewprogramlink_url' => $viewprogramlinkurl,
        'count_programactivelink_url' => $countprogramactivelinkurl,
        'count_programinactivelink_url' => $countprograminactivelinkurl
    );
}
/** 866: Program Electives[Ikram Starts]  **/
/**
 * Un assign user from program course enrollments.
 * 
 */
function local_program_unassign_course($eventdata){
    global $DB;
    $data = $eventdata->get_data();
    $DB->delete_records('local_program_enrolments', ['userid' => $data['userid'], 'courseid' => $data['courseid']]);
}
/** 866: Program Electives[Ikram Ends]  **/

/*
* Author sarath
* @return true for reports under category
*/
function learnerscript_program_list(){
    return 'Program';
}

/**
 * Returns classrooms tagged with a specified tag.
 *
 * @param local_tags_tag $tag
 * @param bool $exclusivemode if set to true it means that no other entities tagged with this tag
 *             are displayed on the page and the per-page limit may be bigger
 * @param int $fromctx context id where the link was displayed, may be used by callbacks
 *            to display items in the same context first
 * @param int $ctx context id where to search for records
 * @param bool $rec search in subcontexts as well
 * @param int $page 0-based number of page being displayed
 * @return \local_tags\output\tagindex
 */
function local_program_get_tagged_programs($tag, $exclusivemode = false, $fromctx = 0, $ctx = 0, $rec = 1, $page = 0, $sort = '') {
    global $CFG, $PAGE;
    // prepare for display of tags related to evaluations
    $perpage = $exclusivemode ? 10 : 5;
    $displayoptions = array(
        'limit' => $perpage,
        'offset' => $page * $perpage,
        'viewmoreurl' => null,
    );
    $renderer = $PAGE->get_renderer('local_program');
    $totalcount = $renderer->tagged_programs($tag->id, $exclusivemode, $ctx, $rec, $displayoptions, $count = 1, $sort);
    $content = $renderer->tagged_programs($tag->id, $exclusivemode, $ctx, $rec, $displayoptions, 0, $sort);
    $totalpages = ceil($totalcount / $perpage);
    if ($totalcount)
    return new local_tags\output\tagindex($tag, 'local_program', 'program', $content,
            $exclusivemode, $fromctx, $ctx, $rec, $page, $totalpages);
    else
    return '';
}

/**
 * [available_enrolled_users description]
 * @param  string  $type       [description]
 * @param  integer $groupid [description]
 * @param  [type]  $params     [description]
 * @param  integer $total      [description]
 * @param  integer $offset1    [description]
 * @param  integer $perpage    [description]
 * @param  integer $lastitem   [description]
 * @return [type]              [description]
 */
function local_program_users($type = null, $groupid = 0, $params, $total=0, $offset1=-1, $perpage=-1, $lastitem=0) {

    global $DB, $USER;

    $context = context_system::instance();
    $group = $DB->get_record('cohort', array('id' => $groupid));
    // $batchdata = $DB->get_record('local_groups', array('cohortid' => $groupid));
    // // print_object($batchdata);exit;

    // $params['btchorganization'] = $batchdata->costcenterid;
    // $params['btchdepartment'] = $batchdata->departmentid;
    // $params['btchsubdepartment'] = $batchdata->subdepartmentid;
    $params['suspended'] = 0;
    $params['deleted'] = 0;
    $levelid = $params['levelid'];
    if ($total == 0) {
        $sql = "SELECT u.id,concat(u.firstname,' ',u.lastname,' ','(',u.email,')') as fullname";
    } else {
        $sql = "SELECT count(u.id) as total";
    }
    $sql .= " FROM {user} u JOIN {cohort_members} cm ON u.id = cm.userid";
    $sql .= " 
            WHERE  u.id > 2 AND u.suspended = :suspended AND u.deleted = :deleted AND (open_type=1)";
    if ($lastitem != 0) {
        $sql .= " AND u.id > $lastitem";
    }
    if (!empty($params['btchorganization'])) {
        $sql .= " AND u.open_costcenterid = :btchorganization";
    }

    if (!is_siteadmin()) {
        $userdetail = $DB->get_record('user', array('id' => $USER->id));
        $sql .= " AND u.open_costcenterid = :costcenter";
        $params['costcenter'] = $userdetail->open_costcenterid;
        if (has_capability('local/costcenter:manage_owndepartments', $context) &&
                !has_capability('local/costcenter:manage_ownorganization', $context)) {
            $sql .= " AND u.open_departmentid = :department";
            $params['department'] = $userdetail->open_departmentid;
        }
    }
    if (!empty($params['email'])) {
        $sql .= " AND u.id IN ({$params['email']})";
    }
    if (!empty($params['uname'])) {
        $sql .= " AND u.id IN ({$params['uname']})";
    }
    if (!empty($params['subdepartment'])) {
        $sql .= " AND u.open_subdepartment IN ({$params['subdepartment']})";
    }
    if (!empty($params['department'])) {
        $sql .= " AND u.open_departmentid IN ({$params['department']})";
    }
    if (!empty($params['organization'])) {
        $sql .= " AND u.open_costcenterid IN ({$params['organization']})";
    }
    if (!empty($params['idnumber'])) {
        $sql .= " AND u.id IN ({$params['idnumber']})";
    }
    if (!empty($params['groups'])) {
        $query = "SELECT usr.id
                FROM {user} usr
                JOIN {cohort_members} cm ON cm.userid=usr.id
                WHERE cm.cohortid IN ({$params['groups']}) ";

        $groupusers = $DB->get_records_sql_menu($query);

        if ($userslist) {
            $userslist = implode(',', $groupusers);
            $sql .= " AND u.id IN ($userslist)";
        }
    }

    if ($type == 'add') {
        $sql .= " AND cm.cohortid = {$groupid} AND u.id NOT IN (SELECT userid FROM {local_program_users} WHERE levelids = $levelid)";

    } else if ($type == 'remove') {
        $sql .= " AND u.id IN (SELECT userid FROM {local_program_users} WHERE levelids = $levelid)";
    }
    $order = ' ORDER BY u.id ASC ';

    if ($total == 0) {
        $availableusers = $DB->get_records_sql_menu($sql.$order, $params);
    } else {
        $availableusers = $DB->count_records_sql($sql, $params);
    }
    
    return $availableusers;
}

/**
 * Add groups member
 * @param  int $groupsid
 * @param  int $userid
 * @return void
 */
// function local_program_add_member($groupsid, $userid, $programid) {
//     global $DB;
//     if ($DB->record_exists('local_program_users', array('levelids' => $groupsid, 'userid' => $userid))) {
//         // No duplicates!
//         return;
//     }
//     $record = new stdClass();
//     $record->levelids  = $groupsid;
//     $record->programid  = $programid;
//     $record->supervisorid  = 0;
//     $record->hours  = 0;
//     $record->usercreated  = 2;
//     $record->timecreated  = time();
//     $record->userid    = $userid;
//     $record->timeadded = time();
//     $DB->insert_record('local_program_users', $record);
// }
/**
 * Remove groups member
 * @param  int $groupsid
 * @param  int $userid
 * @return void
 */
// function local_program_remove_member($groupsid, $userid, $programid) {
//     global $DB;
//     $id = $DB->get_field('local_program_users', 'id', array('userid' => $userid, 'levelids' => $groupsid));
//     print_r($id);exit;
//     $record = new stdClass();
//     $record->id  = $id;
//     $record->levelids  = $groupsid;
//     $record->programid  = $programid;
//     $record->supervisorid  = 0;
//     $record->hours  = 0;
//     $record->usercreated  = 2;
//     $record->timecreated  = time();
//     $record->userid    = $userid;
//     // $DB->update_record('local_program_users', array('levelids' => $groupsid, 'userid' => $userid, 'programid' => $programid));
//     $DB->update_record('local_program_users', $record);
// }
