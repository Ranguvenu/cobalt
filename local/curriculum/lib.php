<?php
require_once($CFG->libdir . '/formslib.php');
use \local_curriculum\form\curriculum_form as curriculum_form;
use \local_curriculum\form\curriculum_managesemester_form as curriculum_managesemester_form;

function local_curriculum_leftmenunode(){
    global $USER, $DB;
    $systemcontext = context_system::instance();
    $usersnode = '';

    $managecurriculum_string =  get_string('manage_curriculum','local_curriculum');

    if(has_capability('local/costcenter:manage',$systemcontext) || is_siteadmin()) {
        $usersnode .= html_writer::start_tag('li', array('id'=> 'id_leftmenu_curriculum', 'class'=>'pull-left user_nav_div users'));
        $users_url = new moodle_url('/local/curriculum/index.php');
        $users = html_writer::link($users_url, '<span class="cls_wht_structure_icon dypatil_cmn_icon icon"></span><span class="user_navigation_link_text">'.$managecurriculum_string.'</span>',array('class'=>'user_navigation_link'));
        $usersnode .= $users;
        $usersnode .= html_writer::end_tag('li');
    }
    return array('6' => $usersnode);
}
function local_curriculum_output_fragment_curriculum_form($args){

    global $CFG, $PAGE, $DB;
    $args = (object) $args;
    $context = $args->context;
    $return = '';
    $renderer = $PAGE->get_renderer('local_curriculum');
    $formdata = [];
    if (!empty($args->jsonformdata)) {
        $serialiseddata = json_decode($args->jsonformdata);
        parse_str($serialiseddata, $formdata);
    }
    if ($args->id > 0) {
        $curriculumdata = $DB->get_record('local_curriculum', array('id' => $args->id));
    }
    if($curriculumdata->costcenter){
        $curriculumdata->open_costcenterid = $curriculumdata->costcenter;
    }
    // print_r($curriculumdata);exit;
    $formdata['id'] = $args->id;
    $curriculumyear = $DB->get_field('local_curriculum','duration',array('id' => $args->id));
    $mform = new curriculum_form(null, array('id' => $args->id, 'programid' => $args->programid, 'open_costcenterid' => $curriculumdata->open_costcenterid, 'form_status' => $args->form_status), 'post', '', null, true, $formdata);

    $mform->set_data($curriculumdata);

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
function local_curriculum_output_fragment_curriculum_manageyear_form($args) {
    global $CFG, $PAGE, $DB;
    $args = (object) $args;
    $context = $args->context;
    $return = '';
    $renderer = $PAGE->get_renderer('local_curriculum');
    $formdata = [];
    if (!empty($args->jsonformdata)) {
        $serialiseddata = json_decode($args->jsonformdata);
        parse_str($serialiseddata, $formdata);
    }
    $formdata['id'] = $args->id;
    $formdata['curriculumid'] = $args->curriculumid;
    $formdata['programid'] = $args->programid;

    $mform = new curriculum_manageyear_form(null, array('id' => $args->id,
        'curriculumid' => $args->curriculumid,'cost' => $catalogargs->cost, 'programid' => $args->programid, 'form_status' => $args->form_status), 'post', '', null,
        true, $formdata);
    $bcsemester = new stdClass();
    $bcsemester->curriculumid = $args->curriculumid;
    $bcsemester->programid = $args->programid;
    if ($args->id > 0) {
        $bcsemester = $DB->get_record('local_program_cc_years', array('id' => $args->id));
    }

    $bcsemester->form_status = $args->form_status;
    $mform->set_data($bcsemester);

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
function local_curriculum_output_fragment_curriculum_managesemester_form($args) {
    global $CFG, $PAGE, $DB;
    $args = (object) $args;
    $context = $args->context;
    $return = '';
    $renderer = $PAGE->get_renderer('local_curriculum');
    $formdata = [];
    if (!empty($args->jsonformdata)) {
        $serialiseddata = json_decode($args->jsonformdata);
        parse_str($serialiseddata, $formdata);
    }
    $formdata['id'] = $args->id;
    $formdata['curriculumid'] = $args->curriculumid;
    $formdata['programid'] = $args->programid;

    $mform = new curriculum_managesemester_form(null, array('id' => $args->id,
        'curriculumid' => $args->curriculumid, 'programid' => $args->programid, 'yearid' => 1, 'form_status' => $args->form_status), 'post', '', null,
        true, $formdata);
    $bcsemester = new stdClass();
    $bcsemester->curriculumid = $args->curriculumid;
    $bcsemester->programid = $args->programid;
    if ($args->id > 0) {
        $bcsemester = $DB->get_record('local_curriculum_semesters', array('id' => $args->id));
    }

    $bcsemester->form_status = $args->form_status;
    $bcsemester->semester_description['text'] = $bcsemester->description;
    $mform->set_data($bcsemester);

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
function local_curriculum_output_fragment_course_form($args) {
    global $CFG, $PAGE, $DB;

    require_once($CFG->dirroot.'/local/curriculum/classes/form/programcourse_form.php');
    $args = (object) $args;
    $context = $args->context;
    $return = '';
    $renderer = $PAGE->get_renderer('local_curriculum');
    $formdata = [];
    if (!empty($args->jsonformdata)) {
        $serialiseddata = json_decode($args->jsonformdata);
        parse_str($serialiseddata, $formdata);
    }
    $formdata['programid'] = $args->programid;
    $formdata['curriculumid'] = $args->curriculumid;
    $formdata['yearid'] = 1;
    $formdata['semesterid'] = $args->semesterid;


    $mform = new programcourses_form(null, array('programid' => $args->programid,'curriculumid' => $args->curriculumid, 'yearid' => 1, 'semesterid' => $args->semesterid,
        'form_status' => $args->form_status), 'post', '', null, true, $formdata);
    $coursedata = new stdClass();
    $coursedata->id = $args->id;
    $coursedata->form_status = $args->form_status;
    $mform->set_data($coursedata);

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
    $mform->display();
    $return = ob_get_contents();
    ob_end_clean();

    return $return;
}
function local_curriculum_output_fragment_curriculum_managefaculty_form($args) {
    global $CFG, $PAGE, $DB;
    require_once($CFG->dirroot."/local/curriculum/classes/form/managefaculty_form.php");
    $args = (object) $args;
    $context = $args->context;
    $return = '';
    $renderer = $PAGE->get_renderer('local_curriculum');
    $formdata = [];
    if (!empty($args->jsonformdata)) {
        $serialiseddata = json_decode($args->jsonformdata);
        parse_str($serialiseddata, $formdata);
    }
    $formdata['yearid'] = $args->yearid;
    $formdata['programid'] = $args->programid;
    $formdata['curriculumid'] = $args->curriculumid;
    $formdata['semesterid'] = $args->semesterid;
    $formdata['courseid'] = $args->courseid;


    $mform = new managefaculties_form(null, array('programid' => $args->programid, 'curriculumid' => $args->curriculumid,'yearid' => $args->yearid, 'semesterid' => $args->semesterid, 'courseid' => $args->courseid,
        'form_status' => $args->form_status), 'post', '', null, true, $formdata);
    $curriculumdata = new stdClass();
    $curriculumdata->id = $args->id;
    $curriculumdata->programid = $args->programid;
    $curriculumdata->curriculumid = $args->curriculumid;
    $curriculumdata->form_status = $args->form_status;
    $mform->set_data($curriculumdata);

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
function local_curriculum_output_fragment_curriculum_managestudent_form($args) {
    global $CFG, $PAGE, $DB;
    $args = (object) $args;

    $context = $args->context;
    $return = '';
    $renderer = $PAGE->get_renderer('local_curriculum');
    $formdata = [];
    if (!empty($args->jsonformdata)) {
        $serialiseddata = json_decode($args->jsonformdata);
        parse_str($serialiseddata, $formdata);
    }
    $formdata['yearid'] = $args->yearid;
    $formdata['curriculumid'] = $args->curriculumid;
    $formdata['programid'] = $args->programid;

    $mform = new managestudent_form(null, array('curriculumid' => $args->curriculumid,'yearid' => $args->yearid, 'programid' => $args->programid,
        'form_status' => $args->form_status), 'post', '', null, true, $formdata);
    $curriculumdata = new stdClass();
    $curriculumdata->id = $args->id;
    $curriculumdata->curriculumid = $args->curriculumid;
    $curriculumdata->form_status = $args->form_status;
    $mform->set_data($curriculumdata);

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
function costcenter_filter($mform){

      global $DB,$USER;
    $systemcontext = context_system::instance();
    $sql = "SELECT id, fullname FROM {local_costcenter} WHERE parentid = 0";
    $sql .= " ORDER BY fullname";
    $costcenterslist = $DB->get_records_sql_menu($sql);
    $select = $mform->addElement('autocomplete', 'costcenter', '', $costcenterslist, array('placeholder' => get_string('costcenters','local_curriculum')));
    $mform->setType('costcenter', PARAM_RAW);
    $select->setMultiple(true);
}
 function find_departments($costcenter){
        global $DB;
        if($costcenter) {
            $univdep_sql = "select id, name from {local_program} where costcenter = $costcenter";
            $univ_dep = $DB->get_records_sql($univdep_sql);

            return $costcenter =  $univ_dep;
        }else {
            return $costcenter;
        }
    }


    /*
* return count of users under selected costcenter
* @return  [type] int count of users
*/
function costcenterwise_curriculum_count($costcenter, $department = false, $subdepartment = false){
    global $USER, $DB, $CFG;
    $params = array();
    $params['costcenter'] = $costcenter;
    $countusersql = "SELECT count(id) FROM {local_curriculum} WHERE costcenter = :costcenter";
    
    if($department){
        $countusersql .= " AND open_departmentid = :department ";
        $params['department'] = $department;
    }

    if($subdepartment){
        $countusersql .= " AND open_subdepartment = :subdepartment ";
        $params['subdepartment'] = $subdepartment;
    }

    $countcurriculums = $DB->count_records_sql($countusersql, $params);
    return array(
        'totalcurriculums' => $countcurriculums
    );
}


function find_courses($semesterid,$curriculumid)
{
        global $DB;
            $costcenterid = $DB->get_field('local_curriculum', 'costcenter', array('id'=>$curriculumid));
             $open_departmentid = $DB->get_field('local_curriculum', 'open_departmentid', array('id'=>$curriculumid));
             $open_subdepartment = $DB->get_field('local_curriculum', 'open_subdepartment', array('id'=>$curriculumid));

            $existedcourses = array();
    
             $cousresql ="SELECT c.id,c.fullname 
            FROM {course} c 
            JOIN {local_costcenter} lcost ON c.open_costcenterid = lcost.id
            JOIN {local_curriculum} lc ON lcost.id = lc.costcenter 

           WHERE c.id NOT IN (
                SELECT cc.courseid 
                FROM {local_cc_semester_courses} cc
               WHERE cc.curriculumid = $curriculumid) AND c.open_identifiedas = 3 AND lc.costcenter = $costcenterid  AND c.open_departmentid = $open_departmentid AND c.open_subdepartment = $open_subdepartment AND lc.id = $curriculumid AND c.visible = 1";   

            $displayedcourses = $DB->get_records_sql($cousresql);
 
            $cids = array();
            $parentcourseids = array();
            foreach($displayedcourses as $key => $value){

              $courseids = $key;
              $childids = $DB->get_record_sql('SELECT cc.courseid FROM {local_cc_semester_courses} cc JOIN {course} c ON c.id = cc.courseid WHERE curriculumid = '.$curriculumid.' AND yearid = 1');
              if($childids){

                $parentcourseids[] = $courseids;
              }
              $coursenames = $value;
              $cids[] = $courseids;

            }
            if(!empty($cids)){

               foreach($cids as $key => $courseid){
                   $cid = $courseid;
               }
            }
            
            $course_sql =  " ORDER BY id DESC";
           if($parentcourseids){
            $pcids = implode(',', $parentcourseids);
            $courses = $DB->get_records_sql($cousresql.$course_sql);
            }
           else{
            $courses = $DB->get_records_sql($cousresql.$course_sql);
            }

            return $courses;

    }

    class curriculum_manageyear_form extends moodleform {

    public function definition() {
        global $CFG, $DB, $USER;
        $mform = &$this->_form;
        $id = $this->_customdata['id'];
        $programid = $this->_customdata['programid'];
        $curriculumid = $this->_customdata['curriculumid'];
        $cost = $this->_customdata['cost'];
        $context = context_system::instance();

        $mform->addElement('hidden', 'id', $id);
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'curriculumid', $curriculumid);
        $mform->setType('curriculumid', PARAM_INT);

        $mform->addElement('hidden', 'programid', $programid);
        $mform->setType('programid', PARAM_INT);
        $mform->addElement('hidden', 'yeardiff', $cost);
        $mform->setType('yeardiff', PARAM_INT);

        if($cost == 0){
        $mform->addElement('text', 'year', get_string('acedemicyear', 'local_curriculum'));
        }

        $mform->addElement('text', 'cost', get_string('cost', 'local_curriculum'));
        $mform->addRule('cost', null, 'numeric', null, 'client');
        $mform->addRule('cost', null, 'nonzero', null, 'client');
        $mform->setType('cost', PARAM_FLOAT);

        $mform->disable_form_change_checker();
    }

    public function validation($data, $files) {
        $errors = array();
        global $DB, $CFG;
        $errors = parent::validation($data, $files);

        $cost = $data['cost'];
        if($data['yeardiff'] == 0){
        if($data['year'] == null){
             $errors['year'] = get_string('missingyear', 'local_curriculum');
        }
         }
        if (!empty($data['year']) && strlen($data['year']) > 200) {
                $errors['year'] = get_string('lengthofyear', 'local_curriculum');
        }

        return $errors;
    }
}
/**
 * Get curriculums
 * 
 */
function get_curriculums($stable, $filtervalues = null){
    global $DB, $CFG,$USER,$OUTPUT;
    $systemcontext = context_system::instance();
    $selectsql = "SELECT c.id, c.name, c.shortname, c.costcenter FROM {local_curriculum} c ";
    $countsql  = "SELECT count(c.id) FROM {local_curriculum} c  ";
    $formsql .= " JOIN {local_costcenter} lc ON lc.id = c.costcenter ";
    $formsql .= " WHERE 1=1 AND c.program = 0 ";
    if (!is_siteadmin() && has_capability('local/costcenter:manage_ownorganization', $systemcontext)) {
        $formsql .= " AND c.costcenter = {$USER->open_costcenterid}";
    } else if (!is_siteadmin() && has_capability('local/costcenter:manage_owndepartments', $systemcontext)) {
        $formsql .= " AND c.open_departmentid = {$USER->open_departmentid}";
    } else if (!is_siteadmin() && has_capability('local/costcenter:manage_ownsubdepartments', $systemcontext)) {
        $formsql .= " AND c.open_subdepartment = {$USER->open_subdepartment}";
    }
    if ( $filtervalues->search_query != "" ) {
        $formsql .= " AND ((c.name LIKE '%".$filtervalues->search_query."%')
                      OR (c.shortname LIKE '%".$filtervalues->search_query."%')
                      OR (lc.fullname LIKE '%".$filtervalues->search_query."%')
                            
                        )";
    }
    $labelstring = get_config('local_costcenter');
    $firstlevel = $labelstring->firstlevel;
    $secondlevel = $labelstring->secondlevel;
    $thirdlevel = $labelstring->thirdlevel;
    if (!empty($filtervalues->$firstlevel)) {
        $organizations = explode(',', $filtervalues->$firstlevel);
        if (count($organizations) > 0) {
            for ($i=0; $i < count($organizations); $i++) { 
                if (is_numeric($organizations[$i])) {
                    $orgids[] = $organizations[$i];
                }
            }
        }
        $group = implode(',', $orgids);
        $deptquery = array();
        if($group) {
            $deptquery[] = "  c.costcenter IN($group) ";
            $groupqueeryparams = implode('OR', $deptquery);
            $formsql .= ' AND ('.$groupqueeryparams.')';
        }
    }
    if (!empty($filtervalues->$secondlevel)) {
        $departments = explode(',', $filtervalues->$secondlevel);
        if (count($departments) > 0) {
            for ($i=0; $i < count($departments); $i++) { 
                if (is_numeric($departments[$i])) {
                    $deptids[] = $departments[$i];
                }
            }
        }
        $group = implode(',', $deptids);
        $deptquery = array();
        if($group) {
            $deptquery[] = "  c.open_departmentid IN($group) ";
            $groupqueeryparams = implode('OR', $deptquery);
            $formsql .= ' AND ('.$groupqueeryparams.')';
        }
    }
    if (!empty($filtervalues->$thirdlevel)) {
        $subdepartment = explode(',', $filtervalues->$thirdlevel);
        if (count($subdepartment) > 0) {
            for ($i=0; $i < count($subdepartment); $i++) { 
                if (is_numeric($subdepartment[$i])) {
                    $subdeptids[] = $subdepartment[$i];
                }
            }
        }
        $group = implode(',', $subdeptids);
        $deptquery = array();
        if($group) {
            $deptquery[] = "  c.open_subdepartment IN($group) ";
            $groupqueeryparams = implode('OR', $deptquery);
            $formsql .= ' AND ('.$groupqueeryparams.')';
        }
    }
    if ($filtervalues->curriculum) {
        $curriculum = explode(',', $filtervalues->curriculum);
        if (count($curriculum) > 0) {
            for ($i=0; $i < count($curriculum); $i++) { 
                if (is_numeric($curriculum[$i])) {
                    $curriculumids[] = $curriculum[$i];
                }
            }
        }
        $group = implode(',', $curriculumids);
        $deptquery = array();
        if($group) {
            $deptquery[] = "  c.id IN($group) ";
            $groupqueeryparams = implode('OR', $deptquery);
            $formsql .= ' AND ('.$groupqueeryparams.')';
        }
    }
    $totalcourses = $DB->count_records_sql($countsql.$formsql);
    $formsql .= " ORDER BY c.id DESC";
    $curriculums = $DB->get_records_sql($selectsql.$formsql, [], $stable->start,$stable->length);
    $data = array();
    foreach ($curriculums as $curriculum) {
        $row = array();
        $costcentername = $DB->get_field('local_costcenter', 'fullname', array('id' => $curriculum->costcenter));
        if (strlen($curriculum->name) >= 20) {
            $curriculumname = substr($curriculum->name, 0, 17).'...';
        } else {
            $curriculumname = $curriculum->name;
        }
        $row['id'] = $curriculum->id;
        $row['curriculumname'] = $curriculum->name;
        $row['university'] = $costcentername;
        $programscount = $DB->count_records("local_program", array('curriculumid' => $curriculum->id));
        $action = html_writer::link('javascript:void(0)',
                                    $OUTPUT->pix_icon('t/edit', get_string('edit'), 'moodle',
                                        array('')),
                                    array('title' => get_string('edit'),
                                            'alt' => get_string('edit'),
                                            'data-value' => $advisor->id,
                                            'onclick' => '(function(e){
                                                require("local_curriculum/ajaxforms")
                                                .init({contextid:1, component:"local_curriculum",
                                                callback:"curriculum_form", form_status:0,
                                                plugintype: "local", pluginname: "curriculum",
                                                id: '.$curriculum->id.'})
                                            })(event)'
                                        )
                                );
        if ($programscount > 0) {
            $action .= html_writer::link('javascript:void(0)',
                                            $OUTPUT->pix_icon('t/delete', get_string('delete'),
                                                                'moodle', array('')
                                                    ),
                                            array('title' => get_string('delete'),
                                                    'id' => $curriculum->id,
                                                    'onclick' => '(function(e){
                                                        require(\'local_curriculum/ajaxforms\')
                                                        .deleteConfirm({
                                                            action:\'cannotdeletecurriculum\',
                                                            curriculumname: \''.$curriculum->name.'\',
                                                            id: '.$curriculum->id.'
                                                        })
                                                    })(event)'
                                                )
                                        );
        } else {
            $action .= html_writer::link('javascript:void(0)',
                                            $OUTPUT->pix_icon('t/delete', get_string('delete'),
                                                'moodle', array('')
                                            ), array('title' => get_string('delete'),
                                                        'id' => $curriculum->id,
                                                        'onclick' => '(function(e){
                                                            require(\'local_curriculum/ajaxforms\')
                                                            .deleteConfirm({
                                                                action:\'deletecurriculum\',
                                                                curriculumname: \''.$curriculum->name.'\',
                                                                id: '.$curriculum->id.'
                                                            })
                                                        })(event)'
                                                    )
                                        );
        }
        if(is_siteadmin() || has_capability('local/costcenter:manage_multiorganizations', $systemcontext) 
            || has_capability('local/costcenter:manage_ownorganization', $systemcontext) 
            || has_capability('local/costcenter:manage_owndepartments', $systemcontext)){
            $actioncaps= html_writer::div($action,'action_btns_container d-flex align-items-center');
        } else{
            $actioncaps= html_writer::div('','action_btns_container d-flex align-items-center');
        }
        $row['action'] = $actioncaps;
        $data[] = $row;
    }
    $return = ['curriculum' => $data, 'totalcount' => $totalcourses];
    return $return;

}
/**
 * Filter curriculum
 */
function curriculum_filter($mform){
    global $DB, $USER;
    $systemcontext = context_system::instance();
    $selectsql = "SELECT DISTINCT id, name FROM {local_curriculum} c WHERE 1=1 AND c.program = 0 ";
    if (!is_siteadmin() && has_capability('local/costcenter:manage_ownorganization', $systemcontext)) {
        $selectsql .= " AND c.costcenter = {$USER->open_costcenterid}";
       
    } else if (!is_siteadmin() && has_capability('local/costcenter:manage_owndepartments', $systemcontext)) {
        $selectsql .= " AND c.open_departmentid = {$USER->open_departmentid}";
        
    } else if (!is_siteadmin() && has_capability('local/costcenter:manage_ownsubdepartments', $systemcontext)) {
        $selectsql .= " AND c.open_subdepartment = {$USER->open_subdepartment}";
        
    }
    if ( $filtervalues->search_query != "" ) {
        $formsql .= " and ((c.name LIKE '%".$filtervalues->search_query."%')
                            or (c.shortname LIKE '%".$filtervalues->search_query."%')
                            
                        )";
    }
    $formsql .= " ORDER BY c.id DESC";
    $curriculums = $DB->get_records_sql_menu($selectsql.$formsql);
    $options = [
        'multiple' => true,
        'class' => 'el_curriculums',
        'placeholder' => get_string('curriculums', 'local_curriculum'),
    ];
    $mform->addElement('autocomplete', 'curriculum','', $curriculums, $options);
    $mform->setType('curriculum', PARAM_RAW);
}
