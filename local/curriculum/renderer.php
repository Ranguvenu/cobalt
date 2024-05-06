<?php

class local_curriculum_renderer extends plugin_renderer_base {

    function top_action_buttons() {
        global $CFG;
        $systemcontext = context_system::instance();
        $output = "";
        $output .= "<ul class='course_extended_menu_list'>";
        if (is_siteadmin() || has_capability('local/costcenter:manage_multiorganizations', $systemcontext) 
            || has_capability('local/costcenter:manage_ownorganization', $systemcontext)
            || has_capability('local/costcenter:manage_owndepartments', $systemcontext)) {
            $output .= '<li>
                            <div class="courseedit course_extended_menu_itemcontainer">
                            <a id="extended_menu_createcourses" class="pull-right course_extended_menu_itemlink"
                                title = "'.get_string('create_curriculum','local_curriculum').'"
                                data-action="createcoursemodal"
                                onclick="(function(e){
                                        require(\'local_curriculum/ajaxforms\')
                                        .init({contextid:'.$systemcontext->id.',
                                           component:\'local_curriculum\',
                                           callback:\'curriculum_form\',
                                           form_status:0, id :0, plugintype: \'local\',
                                           pluginname: \'curriculum\'}) })(event)">
                                <span class="createicon">
                                <span class="curriculum_icon icon"></span>
                                    <i class="fa fa-plus createiconchild" aria-hidden="true">
                                    </i>
                                </span>
                            </a>
                            </div>
                        </li>';
        }
        $output .= "</ul>";
        echo $output;
    }
    public function curriculum_view() {
        global $OUTPUT, $CFG;
        $systemcontext = context_system::instance();
        $filterparams = $this->get_catelog_curriculums(true);
        $filterparams['submitid'] = 'form#filteringform';
        $filterparams['global_filter'] = $OUTPUT->render_from_template('local_costcenter/global_filter', $filterparams);
        $filterparams['curriculums'] = $this->get_catelog_curriculums();
        return $this->render_from_template('local_curriculum/manage_curriculums', $filterparams);
    }
    public function get_catelog_curriculums($filter = null){
        $systemcontext = context_system::instance();
        $options = array('targetID' => 'viewcurriculum_details', 'perPage' => 10, 'cardClass' => 'col-md-4 col-12','viewType' => 'card');
        $options['methodName']='local_curriculum_list_curriculums';
        $options['templateName']= 'local_curriculum/list_curriculums';
        $options = json_encode($options);
        $filterdata = json_encode(array('status' => $status, 'organizations'=>$costcenterid, 
                                        'departments' => $departmentid, 'subdepartment' => $subdepartmentid));
        $dataoptions = json_encode(array('contextid' => $systemcontext->id));
        $templatecontext = [
                'targetID' => 'viewcurriculum_details',
                'options' => $options,
                'dataoptions' => $dataoptions,
                'filterdata' => $filterdata
        ];
        if($filter){
            return  $templatecontext;
        }else{
            return  $this->render_from_template('local_costcenter/cardPaginate', $templatecontext);
        }
    }
    public function viewcurriculum($curriculumid) {

        global $OUTPUT, $CFG, $DB, $USER, $PAGE;
        require_once($CFG->dirroot.'/local/curriculum/program.php');
        $systemcontext = context_system::instance();
        $stable = new stdClass();
        $stable->curriculumid = $curriculumid;
        $stable->thead = false;
        $stable->start = 0;
        $stable->length = 1;
        $curriculum = new program();
        $curriculum_result = $curriculum->curriculums($stable);
        if (empty($curriculum)) {
            print_error("curriculum Not Found!");
        }
        $programtemplatestatus = '';
        if(!is_siteadmin()){
            $sql = '';
            $params = array();
            $params['curriculum_resultid'] = $curriculum_result->id; 
            $params['userid'] = $USER->id;
            $sql = "SELECT lp.id
                      FROM {local_program_users} lpu
                      JOIN {local_program} lp ON lp.id = lpu.programid
                      JOIN {local_curriculum} lc
                           ON lp.curriculumid = lc.id
                     WHERE lp.curriculumid = :curriculum_resultid
                           AND lpu.userid = :userid
                                      ";
        $programid = $DB->get_record_sql($sql, $params);
        $programtemplatestatus = $curriculum->programtemplatestatus($programid->id);
        $checkcopyprogram = $curriculum->checkcopyprogram($programid->id);
        }
        else{
            $sql = '';
            $sql = "SELECT lp.id
                          FROM {local_program} lp
                          JOIN {local_curriculum} lc
                          ON lp.curriculumid = lc.id;";
            $programid = $DB->get_records_sql($sql);
            foreach($programid as $key => $value){
                $programtemplatestatus = $curriculum->programtemplatestatus($value->id);
                $checkcopyprogram = $curriculum->checkcopyprogram($value->id);

            }
        }

 
        $managemyprogram = false;
        foreach ($curriculum_result as $key => $value) {
            if ($value->costcenter == $USER->open_costcenterid) {
                $managemyprogram = true;
            }
        $includesfile = false;
        if (file_exists($CFG->dirroot . '/local/includes.php')) {
            $includesfile = true;
            require_once($CFG->dirroot . '/local/includes.php');
            $includes = new user_course_details();
        }

        $return = "";

        $affiliatecolleges = $DB->count_records_sql('SELECT COUNT(id) FROM {local_program} WHERE id = :id AND costcenter <> :costcenter',  array('id' => $value->id, 'costcenter' => $value->costcenter));

        $copiedprograms = $DB->count_records_sql("SELECT COUNT(id) FROM {local_program} WHERE id = :id AND costcenter = :costcenter",  array('id' => $value->id, 'costcenter' => $value->costcenter));
        
        $curriculumcompletion = $action = $edit = $delete = $assignusers = $assignusersurl = false;
        if ((has_capability('local/program:manageprogram', context_system::instance()) || is_siteadmin())) {
            $action = true;
        }
        if ((has_capability('local/program:programcompletion', context_system::instance()) || is_siteadmin())) {
            $curriculumcompletion = false;
        }
        if (((has_capability('local/program:editprogram', context_system::instance()) || is_siteadmin())) && $programtemplatestatus) {
            $edit = true;
        }
    }
        $cannotdelete = true;
        $delete = false;
        if ((has_capability('local/program:deleteprogram', context_system::instance()) || is_siteadmin())) {
            if (!$programtemplatestatus) {
                $cannotdelete = true;
                $delete  = false;
            } else {
                $cannotdelete = false;
                $delete = true;
            }
        }
        $bulkenrollusers = false;
        $bulkenrollusersurl = false;

        $selfenrolmenttabcap = true;
        if (!has_capability('local/program:manageprogram', context_system::instance())) {
            $selfenrolmenttabcap = false;
        }
        if (!empty($curriculum->description)) {
            $description = strip_tags(html_entity_decode($curriculum->description));
        } else {
            $description = "";
        }
        $isdescription = '';
        $userview = '';
        $yearid = '';
        if ($userview) {
            $mycompletedsemesters = (new program)->mycompletedsemesteryears($curriculumid, $USER->id);
            $notcmptlsemesters = (new program)->mynextsemesteryears($curriculumid);

            if (!empty($notcmptlsemesters)) {
                $yearid = $notcmptlsemesters[0];
            }
          }
          $completionstatus = '';
        if ($completionstatus == 1) {
            $curriculumcompletionstatus = true;
        } else {
            $curriculumcompletionstatus = false;
        }
        $description = $DB->get_field('local_curriculum','description',array('id' => $curriculumid));
        $isdescription = strip_tags($description);
        $decsriptionstring = '';
        if($isdescription){
            $decsriptionstring = $isdescription;
        }
        $curriculummappedprogramid = $DB->get_field('local_program','id',array('curriculumid' => $curriculumid,'id' => 0));
        if(!empty($curriculummappedprogramid)){
           $checkingafiliate = $DB->get_records_sql('SELECT id FROM {local_program} WHERE id = '.$curriculummappedprogramid);
             if(!empty($checkingafiliate)){
             $programtemplatestatus = false;
           }
        }
        $duration_format = $DB->get_field('local_curriculum','duration_format',array('id' => $curriculumid));
        $curriculumcontext = [
            'curriculum' => $curriculum,
            'curriculumid' => $curriculumid,
            'action' => $action,
            'edit' => $edit,
            'curriculumcompletion' => $curriculumcompletion,
            'cannotdelete' => $cannotdelete,
            'delete' => $delete,
            'assignusers' => $assignusers,
            'assignusersurl' => $assignusersurl,
            'bulkenrollusers' => $bulkenrollusers,
            'bulkenrollusersurl' => $bulkenrollusersurl,
            'description' => $description,
            'descriptionstring' => $decsriptionstring,
            'isdescription' => $isdescription,
            'cfg' => $CFG,
            'curriculumcompletionstatus' => $curriculumcompletionstatus,
            'cancreatesemesteryear' => (has_capability('local/curriculum:createsemesteryear', $systemcontext) && ($programtemplatestatus)),
            'seats_image' => $OUTPUT->image_url('GraySeatNew', 'local_program'),
            'yearid' => $yearid,
            'curriculumsemesteryears' => $this->viewcurriculumsemesteryears($curriculumid, $yearid),

        ];
        if($duration_format == 'M'){
         $curriculumcontext['cancreatesemesteryear'] = 0;
        }
        return $this->render_from_template('local_curriculum/curriculumContent', $curriculumcontext);
    }


    /* DM-423-Amol-starts */
    public function view_for_curriculumsemesteryear($curriculumid, $yearid, $programid) {
        global $OUTPUT, $CFG, $DB, $USER;

        $systemcontext = context_system::instance();
        $programsemesters = array();
        $curriculumsemesters = (new program)->curriculumsemesteryear($curriculumid);
        $prgmsemesterscourses = (new program)->program_semesters_courses($programid);
        $semesters = false;
        if(count($curriculumsemesters) > 1){
            $semesters = true;
        }
        $isactivesql = "SELECT active FROM {local_program_levels} WHERE programid = :programid";
        $isactive = $DB->get_fieldset_sql($isactivesql, array('programid' => $programid));
        if (in_array(1, $isactive) || $prgmsemesterscourses) {
            foreach ($curriculumsemesters as $curriculumsemester) {
                $mandatorycourses = $DB->get_records_sql('SELECT lplc.courseid as courseid,
                                                c.fullname as course
                                                 FROM {course} c
                                                 JOIN {local_cc_semester_courses} ccsc ON ccsc.courseid = c.id
                                                 JOIN {local_program_level_courses} lplc ON lplc.parentid = ccsc.courseid
                                                 WHERE ccsc.semesterid = :semesterid AND lplc.programid = :programid AND ccsc.coursetype = 1 AND lplc.mandatory = 1', 
                                                 array('semesterid' => $curriculumsemester->semesterid, 'programid' => $programid));

                $curriculumsemester->mandatorycourses = array_values($mandatorycourses);

                $electivecourses = $DB->get_records_sql('SELECT lplc.courseid as courseid,
                                                c.fullname as course
                                                 FROM {course} c
                                                 JOIN {local_cc_semester_courses} ccsc ON ccsc.courseid = c.id
                                                 JOIN {local_program_level_courses} lplc ON lplc.parentid = ccsc.courseid
                                                 WHERE ccsc.semesterid = :semesterid AND lplc.programid = :programid AND lplc.mandatory = 0 AND ccsc.coursetype = 0', 
                                                 array('semesterid' => $curriculumsemester->semesterid, 'programid' => $programid));
                foreach ($electivecourses as $electivecourse) {
                    $coursecontext = context_course::instance($electivecourse->courseid);
                    if (is_siteadmin()) {
                        $isenrolled = null;
                    } else {
                        $isenrolled = is_enrolled($coursecontext, $USER->id);
                        $electivecourse->isenrolled = $isenrolled;
                    }
                }
                $curriculumsemester->electivecourses = array_values($electivecourses);
            }
            foreach ($curriculumsemesters as $ckey => $val) {
                $semid[] = $curriculumsemesters[$ckey]->semesterid;
            }
            $semids = $semid[0];
            $programsemesters['curriculumsemesters'] = array_values($curriculumsemesters);
            $programsemesters['semesters'] = $semesters;
            $programsemesters['isactive'] = 1;
            $programsemesters['semids'] = $semids;

            $return = $this->render_from_template('local_curriculum/curriculumsin_view',
                $programsemesters);
            return $return;
        } else {
            foreach ($curriculumsemesters as $currsem) {
                $mandatcourses = $DB->get_records_sql("
                                        SELECT lcsc.courseid as courseid, c.fullname as course
                                        FROM {local_cc_semester_courses} lcsc 
                                        JOIN {course} c ON c.id = lcsc.courseid
                                        WHERE lcsc.semesterid = :semesterid
                                         AND curriculumid = :curriculumid
                                         AND coursetype = :coursetype",
                                         array('semesterid' => $currsem->semesterid,
                                            'curriculumid' => $currsem->curriculumid,
                                            'coursetype' => 1));
                $currsem->mandatcourses = array_values($mandatcourses);
                $electcourses = $DB->get_records_sql("
                                        SELECT lcsc.courseid as courseid, c.fullname as course
                                        FROM {local_cc_semester_courses} lcsc 
                                        JOIN {course} c ON c.id = lcsc.courseid
                                        WHERE lcsc.semesterid = :semesterid
                                         AND curriculumid = :curriculumid
                                         AND coursetype = :coursetype",
                                         array('semesterid' => $currsem->semesterid,
                                            'curriculumid' => $currsem->curriculumid,
                                            'coursetype' => 0));
                $currsem->electcourses = array_values($electcourses);
            }
            foreach ($curriculumsemesters as $ckey => $val) {
                $semid[] = $curriculumsemesters[$ckey]->semesterid;
            }
            $semids = $semid[0];
            $programsemesters['curriculumsemesters'] = array_values($curriculumsemesters);
            $programsemesters['semesters'] = $semesters;
            $programsemesters['semids'] = $semids;

            $return = $this->render_from_template('local_curriculum/curriculumsin_view',
                $programsemesters);
            return $return;
        }
    }
    /* DM-423-Amol-ends */
    
     public function viewcurriculumsemesteryears($curriculumid, $yearid) {
        global $OUTPUT, $CFG, $DB, $USER;
        $systemcontext = context_system::instance();
        $assign_courses = '';
        $ccuser = '';
        $userview = $ccuser && !is_siteadmin() && !has_capability('local/curriculum:createprogram', $systemcontext) ? true : false;

        $stable = new stdClass();
        $stable->curriculumid = $curriculumid;
        $stable->thead = false;
        $stable->start = 0;
        $stable->length = 1;
        $curriculum = (new program)->curriculums($stable);
        foreach ($curriculum as $key => $value) {
        $curriculumsemesteryears = (new program)->curriculum_semesteryears($curriculumid);
        $programtemplatestatus = (new program)->programtemplatestatus($value->program);
        $affiliatecolleges = $DB->count_records_sql('SELECT COUNT(id) FROM {local_program} WHERE id = :id AND costcenter <> :costcenter',  array('id' => $value->id, 'costcenter' => $value->costcenter));
        $copiedprograms = $DB->count_records_sql("SELECT COUNT(id) FROM {local_program} WHERE id = :id AND costcenter = :costcenter",  array('id' => $value->id, 'costcenter' => $value->costcenter));

        $managemyprogram = false;

        if ($value->costcenter == $USER->open_costcenterid) {
            $managemyprogram = true;
        }
    }
        if ($userview) {
            $mycompletedsemesters = (new program)->mycompletedsemesters($curriculumid, $USER->id);
            $notcmptlsemesters = (new program)->mynextsemesters($curriculumid);
            if (!empty($notcmptlsemesters)) {
                $nextsemester = $notcmptlsemesters[0];
            } else {
                $nextsemester = 0;
            }
        }
        if (!empty($curriculumsemesteryears)) {
            foreach ($curriculumsemesteryears as $k => $curriculumsemesteryear) {
                $activeclass = '';
                $disabled = '';
                $yearname = strlen($curriculumsemesteryear->year) > 11 ? substr($curriculumsemesteryear->year, 0, 11) ."<span class='semesterrestrict'>..</span>": $curriculumsemesteryear->year;

                $curriculumsemesteryear->year = "<span title='".$curriculumsemesteryear->year."'>".$yearname."</span>";
                if ($curriculumsemesteryear->id == $yearid) {
                    $activeclass = 'active';
                }

                $canmanagesemesteryear = false;
                if ($userview && !is_siteadmin() && !has_capability('local/curriculum:createprogram', $systemcontext)) {
                    if (!$yearrecordexists) {
                        $disabled = 'disabled';
                    }

                    $curriculumsemesteryear->mycompletionstatus = '';
                    if ($userview && $completion_status == 1) {
                        $curriculumsemesteryear->mycompletionstatus = 'Completed';
                    }

                } else {
                    if (has_capability('local/curriculum:managesemesteryear', $systemcontext) || is_siteadmin()) {

                        if (($checkstudents || !$programtemplatestatus)) {
                            $canmanagesemesteryear = false;
                        } else {

                 $curriculummappedprogramid = $DB->get_field('local_program','id',array('curriculumid' => $curriculumid,'id' => 0));
                if(!empty($curriculummappedprogramid)){
                   $checkingafiliate = $DB->get_records_sql('SELECT id FROM {local_program} WHERE id = '.$curriculummappedprogramid);
                }

                if(!empty($checkingafiliate)){
                     $canmanagesemesteryear = false;
                }
                else{
                     $canmanagesemesteryear = true;
                }
                        }
                    }
                }

                $curriculumsemesteryear->myinprogressstatus = '';
                if ($userview && $completion_status == 0) {
                    $curriculumsemesteryear->myinprogressstatus = 'Inprogress';
                }
                $curriculumsemesteryear->active = $activeclass;
                $curriculumsemesteryear->disabled = $disabled;

                $candeletesemesteryear = false;
                if ($semestercount_records > 0 && has_capability('local/curriculum:deletesemesteryear',
                    $systemcontext)) {
                    $candeletesemesteryear = false;
                } else if (has_capability('local/curriculum:deletesemesteryear', $systemcontext)) {
                    $candeletesemesteryear = true;
                }
                $curriculumsemesteryear->candeletesemesteryear = $candeletesemesteryear;
                $curriculumsemesteryear->canmanagesemesteryear = $canmanagesemesteryear;
                $curriculumsemesteryears[$k] = $curriculumsemesteryear;
            }
        }
         $duration_format = $DB->get_field('local_curriculum','duration_format',array('id' => $curriculumid));

        $curriculumsemesterscontext = [
            'contextid' => $systemcontext->id,
            'curriculumid' => $curriculumid,
            'cancreatesemesteryear' => has_capability('local/curriculum:createsemesteryear', $systemcontext),
            'canviewsemesteryear' => has_capability('local/curriculum:viewsemesteryear', $systemcontext),
            'canaddsemesteryear' => has_capability('local/curriculum:createsemesteryear', $systemcontext) || is_siteadmin(),
            'caneditsemesteryear' => has_capability('local/curriculum:editsemesteryear', $systemcontext) || is_siteadmin(),
            'cancreatesemester' => has_capability('local/curriculum:createsemester', $systemcontext) || is_siteadmin(),
            // 'canenrolcourse' => has_capability('local/curriculum:enrolcourse', $systemcontext) && !is_siteadmin(),
            'cfg' => $CFG,
            'yearid' => $yearid,
            'cantakeattendance' => has_capability('local/curriculum:takesessionattendance',
                $systemcontext) && !is_siteadmin(),

            'cansetcost' => has_capability('local/curriculum:cansetcost',
                $systemcontext) || is_siteadmin(),
            'userview' => $userview,
            'curriculumsemesteryear' => $this->viewcurriculumsemesteryear($curriculumid, $yearid)
        ];

           if($duration_format == 'M'){
            $curriculumsemesterscontext['duration_diff'] = '1';
           }
           else{
             $curriculumsemesterscontext['duration_diff'] = '0';
           }

        $return = $this->render_from_template('local_curriculum/yearstab_content',
            $curriculumsemesterscontext);
        return $return;
    }
    public function viewcurriculumsemesteryear($curriculumid, $yearid) {
        global $OUTPUT, $CFG, $DB, $USER;
        require_once($CFG->dirroot.'/local/curriculum/program.php');

        $systemcontext = context_system::instance();

        $stable = new stdClass();
        $stable->curriculumid = $curriculumid;
        $stable->thead = false;
        $stable->start = 0;
        $stable->length = 1;
        $curriculum = (new program)->curriculums($stable);
        foreach ($curriculum as $key => $value) {
            $programtemplatestatus = (new program)->programtemplatestatus($value->program);
            $checkcopyprogram = (new program)->checkcopyprogram($value->program);
            $ccuser = '';
            $userview = $ccuser && !is_siteadmin() && !has_capability('local/curriculum:createprogram', $systemcontext) ? true : false;

            $semestercount_records = $DB->count_records('local_curriculum_semesters',
                    array('curriculumid' => $curriculumid, 'yearid' => 1));

            $curriculumsemesteryears = (new program)->curriculum_semesters($curriculumid);

            $affiliatecolleges = $DB->count_records_sql('SELECT COUNT(id) FROM {local_program} WHERE id = :id AND costcenter <> :costcenter',  array('id' => $value->id, 'costcenter' => $value->costcenter));

            $copiedprograms = $DB->count_records_sql("SELECT COUNT(id) FROM {local_program} WHERE id = :id AND costcenter = :costcenter",  array('id' => $value->id, 'costcenter' => $value->costcenter));

            $managemyprogram = false;

            if (($value->costcenter == $USER->open_costcenterid) || is_siteadmin()) {
                $managemyprogram = true;
            }
        }
        if ($userview) {
            $mycompletedsemesteryears = (new program)->mycompletedsemesteryears($curriculumid, $USER->id);
            $notcmptlsemesteryears = (new program)->mynextsemesteryears($curriculumid);
            if (!empty($notcmptlsemesteryears)) {
                $nextsemester = $notcmptlsemesteryears[0];
            } else {
                $nextsemester = 0;
            }
        }
        if(is_siteadmin() || has_capability('local/costcenter:manage_multiorganizations
            ', $systemcontext) || has_capability('local/costcenter:manage_owndepartments', $systemcontext) || has_capability('local/costcenter:manage_ownorganization', $systemcontext)){
            $caneditsemester = ((has_capability('local/curriculum:editsemester', $systemcontext) || is_siteadmin()) || $programtemplatestatus);
        }else{
            $caneditsemester = false;
        }

        if (!empty($curriculumsemesteryears)) {
            $semestername = '';
            $activeclass = '';
            $disabled = '';
            foreach ($curriculumsemesteryears as $k => $curriculumsemesteryear) {
                $curriculumsemesteryear->semester = "<span title='".$curriculumsemesteryear->semester."'>".$semestername."</span>";

                $curriculumsemesteryear->mycompletionstatus = '';
                if ($userview && $completion_status == 1) {
                    $curriculumsemesteryear->mycompletionstatus = 'Completed';
                }

                $curriculumsemesteryear->myinprogressstatus = '';
                if ($userview && $completion_status == 0) {
                    $curriculumsemesteryear->myinprogressstatus = 'Inprogress';
                }

                $curriculumsemesteryear->active = $activeclass;
                $curriculumsemesteryear->disabled = $disabled;

                $candeletesemester = false;
                if ($semestercount_records > 0 && has_capability('local/curriculum:deletesemester',
                    $systemcontext)) {
                    $candeletesemester = false;
                } else if (has_capability('local/curriculum:deletesemester', $systemcontext)) {
                    $candeletesemester = true;
                }
                $curriculumsemesteryear->candeletesemester = $candeletesemester;
                $curriculumsemesteryears[$k] = $curriculumsemesteryear;
            }
        }
        $signupscount = '';
        $cancreatesemester = false;
        $curriculummappedprogramid = $DB->get_field('local_program','id',array('curriculumid' => $curriculumid,'id' => 0));
        if(!empty($curriculummappedprogramid)){
           $checkafiliate = $DB->get_records_sql('SELECT id FROM {local_program} WHERE id = '.$curriculummappedprogramid);
         }
        if(!empty($checkafiliate)){
             $cancreatesemester = false;
        }
        else{
             if (($signupscount > 0) && (has_capability('local/curriculum:createsemester', $systemcontext) || is_siteadmin()) && $programtemplatestatus) {
                $cancreatesemester = false;
             } else if ((has_capability('local/curriculum:createsemester', $systemcontext) || is_siteadmin()) && $programtemplatestatus) {
                  $cancreatesemester = true;
             }
        }

        $candoactions = false;
        if ($signupscount > 0 || !has_capability('local/costcenter:manage_owndepartments', $systemcontext)) {
            $candoactions = false;
        } else {
            $candoactions = true;
        }
        $canaddcourse = false;
        $curriculummappedprogramid = $DB->get_field('local_program','id',array('curriculumid' => $curriculumid,'id' => 0));
        if(!empty($curriculummappedprogramid)){
           $chckingafiliate = $DB->get_records_sql('SELECT id FROM {local_program} WHERE id = '.$curriculummappedprogramid);
        }
        if(!empty($chckingafiliate)){
             $canaddcourse = false;
           }
           else{
        if (($signupscount > 0) && (has_capability('local/curriculum:addcourse', $systemcontext) || is_siteadmin())) {
            $canaddcourse = false;
        } else if ((has_capability('local/curriculum:addcourse', $systemcontext) || is_siteadmin()) || $programtemplatestatus) {
            $canaddcourse = true;
        }
        }

        $caneditcourse = false;
        if (($signupscount > 0) && (has_capability('local/curriculum:editcourse', $systemcontext) || is_siteadmin())) {
            $caneditcourse = false;
        } else {
            $caneditcourse = true;
        }

        $canmanagecourse = false;
        if (($signupscount > 0) && (has_capability('local/curriculum:managecourse', $systemcontext) || is_siteadmin())) {
            $canmanagecourse = false;
        } else {
            $canmanagecourse = true;
        }

        $canremovecourse = false;
        $cannotremovecourse = false;
        $signups = '';
        if ((has_capability('local/curriculum:removecourse', $systemcontext) || $programtemplatestatus) && (is_siteadmin() || has_capability('local/costcenter:manage_multiorganizations', $systemcontext) || has_capability('local/costcenter:manage_ownorganization', $systemcontext) || has_capability('local/costcenter:manage_owndepartments', $systemcontext))) {
            $canremovecourse = true;
            $cannotremovecourse = false;
        } else if ((($signups > 0 && has_capability('local/curriculum:removecourse',
                $systemcontext)) || $affiliatecolleges > 0)) {
            $canremovecourse = false;
            $cannotremovecourse = true;
        }
        $curriculummappedprogramid = $DB->get_field('local_program','id',array('curriculumid' => $curriculumid,'id' => 0));
          if(!empty($curriculummappedprogramid)){
           $checkingafiliate = $DB->get_records_sql('SELECT id FROM {local_program} WHERE id = '.$curriculummappedprogramid);
            if(!empty($checkingafiliate)){
            $canremovecourse = false;
          }
        }
        $yearsemestercontentcontext = array();
        $yearsemestercontentcontext['contextid'] = $systemcontext->id;
        $yearsemestercontentcontext['candoactions'] = $candoactions;
        $yearsemestercontentcontext['curriculumid'] = $curriculumid;
        $yearsemestercontentcontext['cancreatesemester'] = $cancreatesemester;
        $yearsemestercontentcontext['canviewsemester'] = has_capability('local/curriculum:viewsemester', $systemcontext);
        $yearsemestercontentcontext['caneditsemester'] = $caneditsemester;
        $yearsemestercontentcontext['canaddcourse'] = $canaddcourse;
        $yearsemestercontentcontext['caneditcourse'] = $caneditcourse;
        $yearsemestercontentcontext['canmanagecourse'] = $canmanagecourse;

        $yearsemestercontentcontext['canremovecourse'] = $canremovecourse;
        $yearsemestercontentcontext['cannotremovecourse'] = $cannotremovecourse;

        // $canaddfaculty = false;
        // if (((has_capability('local/curriculum:canaddfaculty', $systemcontext)) || is_siteadmin()) && !$checkcopyprogram) {
        //     $canaddfaculty = true;
        // }

        // $yearsemestercontentcontext['canaddfaculty'] = $canaddfaculty;

        // $canmanagefaculty = false;
        // if (((has_capability('local/curriculum:canmanagefaculty', $systemcontext) || is_siteadmin())) && !$checkcopyprogram) {
        //     $canmanagefaculty = true;
        // }

        // $yearsemestercontentcontext['canmanagefaculty'] = $canmanagefaculty;

        $yearsemestercontentcontext['canenrolcourse'] = ((has_capability('local/curriculum:enrolcourse', $systemcontext) || is_siteadmin()) && !$checkcopyprogram);

        $yearsemestercontentcontext['cfg'] = $CFG;
        $yearsemestercontentcontext['yearid'] = $yearid;
        $yearsemestercontentcontext['cantakeattendance'] = has_capability('local/curriculum:takesessionattendance',
                $systemcontext) && !is_siteadmin();
        $yearsemestercontentcontext['userview'] = $userview;
        $curriculumsemesters = (new program)->curriculumsemesteryear($curriculumid);

        $semesters = false;
        if(count($curriculumsemesters) > 1){
            $semesters = true;
        }
        if ($ccuser && has_capability('local/curriculum:viewprogram', $systemcontext) && !is_siteadmin() && !has_capability('local/curriculum:trainer_viewprogram', $systemcontext) && !has_capability('local/curriculum:viewusers', $systemcontext)) {
            foreach ($curriculumsemesters as $curriculumsemester) {
                $courses = $DB->get_records_sql('SELECT c.id as courseid, c.fullname as course, ccsc.coursetype
                                                   FROM {course} c
                                                   JOIN {local_cc_semester_courses} ccsc ON ccsc.courseid = c.id
                                                   JOIN {local_curriculum_semesters} lcs ON lcs.id = ccsc.semesterid
                                                   WHERE ccsc.semesterid = 1 AND ccss.userid = :userid ', array('semesterid' => 1, 'userid' => $USER->id));
                $curriculumsemester->courses = array_values($courses);
            }
        } else if (has_capability('local/curriculum:trainer_viewprogram', $systemcontext) && !is_siteadmin()) {
            foreach ($curriculumsemesters as $curriculumsemester) {
                $courses = $DB->get_records_sql('SELECT c.id as courseid, c.fullname as course, ccsc.importstatus, ccsc.coursetype
                                                   FROM {course} c
                                                   JOIN {local_cc_semester_courses} ccsc ON ccsc.courseid = c.id
                                                   JOIN {local_curriculum_semesters} lcs ON lcs.id = ccsc.semesterid
                                                   JOIN {local_cc_session_trainers} ccst ON ccst.courseid = ccsc.courseid
                                                   WHERE ccsc.semesterid = :semesterid AND ccst.trainerid = :trainerid', array('semesterid' => $curriculumsemester->semesterid, 'trainerid' => $USER->id));
                $curriculumsemester->courses = array_values($courses);
            }
                $coursetypes = array();
                foreach($courses as $course){
                    $coursescheck = array();
                    if($course->coursetype == 1){
                        $courses[$course->courseid]->coursetype = true;
                    }else{
                        $courses[$course->courseid]->coursetype = false;
                    }
                }
            $curriculumsemester->caneditcurrentsemester = false;
            $curriculumsemester->candeletecurrentsemester = false;
        } else {
            foreach ($curriculumsemesters as $curriculumsemester) {
                $courses = $DB->get_records_sql('SELECT c.id as courseid, c.fullname as course, ccsc.importstatus, ccsc.coursetype
                                                   FROM {course} c
                                                   JOIN {local_cc_semester_courses} ccsc ON ccsc.courseid = c.id
                                                   WHERE ccsc.semesterid = :semesterid', array('semesterid' => $curriculumsemester->semesterid));
                if (count($courses) > 0) {
                    $havingcourses = true;
                } else {
                    $havingcourses = false;
                }
                $curriculumsemester->courses = array_values($courses);
                $curriculumsemester->havingcourses = $havingcourses;
                $coursetypes = array();
                foreach($courses as $course){
                    $coursescheck = array();
                    if($course->coursetype == 1){
                        $courses[$course->courseid]->coursetype = true;
                        $exists = $DB->record_exists('course_completion_criteria',array('course' => $course->courseid));
                        if($exists){
                            $courses[$course->courseid]->completioncriteria = true;
                        }
                    }else{
                        $courses[$course->courseid]->coursetype = false;
                        $courses[$course->courseid]->completioncriteria = false;
                    }
                }

                $curriculummappedprogramid = $DB->get_field('local_program','id',array('curriculumid' => $curriculumsemester->curriculumid,'id' => 0));
                if(!empty($curriculummappedprogramid)){
                   $checkingaffiliates = $DB->get_records_sql('SELECT id FROM {local_program} WHERE id = '.$curriculummappedprogramid);

                }

                if(!empty($checkingaffiliates)){
                    $curriculumsemester->caneditcurrentsemester = false;
                    $curriculumsemester->candeletecurrentsemester = false;
                }
                else{
                    $curriculumsemester->caneditcurrentsemester = true;
                    $curriculumsemester->candeletecurrentsemester = true;
                }

                $semesteruserscount = '';
                if ($semesteruserscount > 0) {
                    $curriculumsemester->caneditcurrentsemester = false;
                    $curriculumsemester->candeletecurrentsemester = false;
                }
            }
        }
        $coursesadded = $DB->record_exists('local_cc_semester_courses', array('curriculumid' => $curriculumid, 'semesterid' => 1));

        $yearsemestercontentcontext['coursesadded'] = $coursesadded;
        $yearsemestercontentcontext['curriculumsemesters'] = array_values($curriculumsemesters);
        $yearsemestercontentcontext['curriculumsemesteryears'] = array_values($curriculumsemesteryears);
        $yearsemestercontentcontext['semesters'] = $semesters;
        $yearsemestercontentcontext['canimportcoursecontent'] = (has_capability('local/curriculum:importcoursecontent', $systemcontext) || is_siteadmin());
            $yearsemestercontentcontext['admin'] =  !is_siteadmin();
    
        $return = $this->render_from_template('local_curriculum/yearsemestercontent',
            $yearsemestercontentcontext);
        return $return;
    }
}
