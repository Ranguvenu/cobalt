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

class local_employees_renderer extends plugin_renderer_base {
    /**
     * Description: Employees profile view in profile.php
     * @param  [int] $id [user id whose profile is viewed]
     * @return [HTML]     [user profile page content]
     */
    public function employees_profile_view($id) {
        global $CFG, $OUTPUT, $DB, $PAGE, $USER;
        require_once($CFG->dirroot.'/course/renderer.php');
        require_once($CFG->libdir . '/badgeslib.php');

        $labelstring = get_config('local_costcenter');
        $corecomponent = new core_component();
        $systemcontext = context_system::instance();
        $userrecord = $DB->get_record('user', array('id' => $id));
        /*user image*/
        $user_image = $OUTPUT->user_picture($userrecord, array('size' => 100, 'link' => false));

        /*user roles*/
        // $userroles = get_user_roles($systemcontext, $id);
        // if(!empty($userroles)){
        //         $rolename  = array();
        //         foreach($userroles as $roles) {
        //             $rolename[] = ucfirst($roles->name);
        //         }
        //         $roleinfo = implode(", ",$rolename);
        // } else {
        //     $roleinfo = "Employee";
        // }
       
        $sql3 = "SELECT cc.fullname, u.open_employeeid,
                u.open_costcenterid,u.open_supervisorid,
                u.department, u.open_subdepartment ,
                u.open_departmentid
                FROM {local_costcenter} cc, {user} u
                WHERE u.id=:id AND u.open_costcenterid=cc.id";
        $userOrg = $DB->get_record_sql($sql3, array('id' => $id));
        $usercostcenter = $DB->get_field('local_costcenter', 'fullname',  array('id' => $userOrg->open_costcenterid));
        $userdepartment = $DB->get_field('local_costcenter', 'fullname',  array('id' => $userOrg->open_departmentid));
        $usersubdepartment = $DB->get_field('local_costcenter', 'fullname',  array('id' => $userOrg->open_subdepartment));
    
        if(!empty($userrecord->city)){
                $empcity = $userrecord->city;
        }else{
                $empcity = 'N/A';
        }
        $get_reporting_username ='';
        if(!empty($userOrg->open_supervisorid)){
            $get_reporting_username_sql = "SELECT u.id, u.firstname, u.lastname, u.open_employeeid FROM {user} as u WHERE  u.id= :open_supervisorid";
                $get_reporting_username = $DB->get_record_sql($get_reporting_username_sql , array('open_supervisorid' => $userOrg->open_supervisorid));
                $reporting_to_empid = $get_reporting_username->serviceid != NULL ? ' ('.$get_reporting_username->open_employeeid.')' : 'N/A';
                $reporting_username = $get_reporting_username->firstname.' '.$get_reporting_username->lastname;
        }else{
                $reporting_username = 'N/A';
        }
        $usercontent = new stdClass();
        $core_component = new core_component();
        $local_pluginlist = $core_component::get_plugin_list('local');
        $existingplugin = array();
        $usercontent = array();
        foreach($local_pluginlist AS $pluginname => $pluginurl){
            $userclass = '\local_'.$pluginname.'\local\employees';
            if(class_exists($userclass)){
                $plugininfo = array();
                $pluginclass = new $userclass;
                if(method_exists($userclass, 'employees_profile_content')){
                    $plugindata = $pluginclass->employees_profile_content($id,true);
                    $usercontent[] = $plugindata;
                    $plugininfo['userenrolledcount'] = $plugindata->count;
                    $plugininfo['string'] = $plugindata->string;
                }
            }
        }

        ksort($existingplugin);
        $existingplugin = array_values($existingplugin);
        if(is_siteadmin() || has_capability('local/employees:edit',$systemcontext) || ($USER->id == $id)){
            $capabilityedit = 1;
        }else{
            $capabilityedit = 0;
        }
        if(has_capability('moodle/user:loginas', $systemcontext)){
            $loginasurl = new moodle_url('/course/loginas.php', array('id'=> 1, 'user' => $userrecord->id, 'sesskey' => sesskey()));
        }else{
            $loginasurl = false;
        }
        if($get_reporting_username){
        $supervisorname = $get_reporting_username->firstname.' '.$get_reporting_username->lastname;
    }

        $usertype = $userrecord->open_type;             
        $rolename = $DB->get_field('role','shortname',array('id'=>$userrecord->roleid));
        $opencostid = $DB->get_field('user','open_costcenterid',array('id'=>$userrecord->id));
        $organization = $DB->get_field('local_costcenter','fullname',array('id'=>$opencostid));
        if($rolename == 'editingteacher'){
            $rolename = 'Teacher';
        }
        if($rolename == 'hod'){
            $rolename = 'HOD';
        }
            $list['open_type'] = $rolename;

            $formsql ='';
            
           


        $allcoursesql = "SELECT c.id,c.fullname,plc.programid as programid,
                        pl.id as levelid
                        FROM {user} u
                        JOIN {role_assignments} ra ON ra.userid = u.id
                        JOIN {role} r ON r.id = ra.roleid AND r.shortname = 'editingteacher'
                        JOIN {context} ctx ON ctx.id = ra.contextid
                        JOIN {course} c ON c.id = ctx.instanceid
                        JOIN {local_program_level_courses} plc ON plc.courseid=c.id
                        JOIN {local_program_levels} pl ON pl.id = plc.levelid 
                        AND pl.programid = plc.programid
                        WHERE u.id = $userrecord->id ";

                $formsql .= " order by c.id DESC limit 3";
        $allcourses = $DB->get_records_sql($allcoursesql.$formsql);
        $allcourse = $DB->get_records_sql($allcoursesql);
        $allenrolledcou =count($allcourse);

            foreach ($allcourses as $key => $vals) {
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
                $coursedetails->cid = $vals->id;
                $coursedetails->fullname = $vals->fullname;
                $coursedetails->programid = $vals->programid;
                $coursedetails->count = $stucount;
                $coursedetails->percentage = $percentage;
                    
                $coursesarray[]=$coursedetails;
            }
        $gradecount=[];

        foreach ($allcourse as $coursekey => $val) {
            $quiz['count']= 0;
            $filecount['count']=0;
            $questioncount['count']=0;
            $gradecount['count'] = 0;
            $quiz['count'] += $DB->count_records('quiz', array ('course' =>$val->id));
            $totalquiz['count'] +=$quiz['count'];
            $grades = "SELECT ag.id,a.course FROM {assign_grades} ag 
                        JOIN {assign} a ON a.id = ag.assignment 
                        WHERE a.course = $val->id and ag.grader >= 1";
            $graderecords = $DB->get_records_sql($grades);

            $gradecount['count'] += count($graderecords);
            $totalgrades['count'] +=$gradecount['count'];

            $files = "SELECT COUNT(f.id),cm.course FROM {files} f 
                        JOIN {context} c ON c.id = f.contextid 
                        JOIN {course_modules} cm ON cm.id = c.instanceid 
                        WHERE f.userid = :userid AND cm.course = :courseid 
                        AND f.filename != :filename";
            $filecount['count'] += $DB->count_records_sql($files, array('userid' =>$userrecord->id,'courseid' =>$val->id,'filename' =>'.'));
            $totalfiles['count'] +=$filecount['count'];

           $questions = "SELECT DISTINCT(qa.questionid),que.name,cm.course 
                        FROM {question} que 
                        JOIN {question_attempts} qa ON que.id = qa.questionid 
                        JOIN {question_usages} qu ON qu.id = qa.questionusageid 
                        JOIN {context} con ON con.id = qu.contextid 
                        JOIN {course_modules} cm ON con.instanceid = cm.id 
                        JOIN {quiz} q ON q.course = cm.course 
                        WHERE q.course = :id";

            $questionrecords = $DB->get_records_sql($questions,array('id' =>$val->id));
            $questioncount['count'] += count($questionrecords);
            $totalquestions['count'] +=$questioncount['count'];
        }
            $popularcoursesql = "SELECT p.id,p.name,count(c.id) as coursecounts
                      FROM {user} u
                      JOIN {role_assignments} ra ON ra.userid = u.id
                      JOIN {role} r ON r.id=ra.roleid AND r.shortname = 'editingteacher'
                      JOIN {context} ctx ON ctx.id = ra.contextid
                      JOIN {course} c ON c.id = ctx.instanceid
                      JOIN {local_program_level_courses} plc ON plc.courseid = ctx.instanceid JOIN {local_program} p ON p.id= plc.programid
                     WHERE u.id =  $userrecord->id GROUP BY p.id";

            $popularcourse=$DB->get_records_sql($popularcoursesql);
            $procount =count($popularcourse);

                $programdata = "SELECT  p.id as programid,p.name as programname,co.name as batchname,p.duration,count(c.id) as coursecount,c.id as courseid,u.id as count
                      FROM {user} u
                      JOIN {role_assignments} ra ON ra.userid = u.id
                      JOIN {role} r ON r.id=ra.roleid 
                      JOIN {context} AS ctx ON ctx.id = ra.contextid
                      JOIN {course} c ON c.id = ctx.instanceid
                      JOIN {local_program_level_courses} plc ON plc.courseid = ctx.instanceid 
                      JOIN {local_program} p ON p.id= plc.programid
                      JOIN {cohort} co ON co.id=p.batchid
                     WHERE u.id = $userrecord->id and p.id = plc.programid 
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
            $userratings = $DB->get_records_sql("SELECT avg(r.rating) as 
                                    avgrating 
                                    FROM {ratings} r
                                    JOIN {user} u ON u.id = r.instructorid 
                                    WHERE u.id = $userrecord->id");
                foreach($userratings as $userrating){
                    if ($userrating->avgrating) {
                        $avgrating = number_format($userrating->avgrating, 1, ".", ".");
                    }else {
                        $avgrating = 'NA';
                    }
                }
            if(empty($totalquiz)){
                $totalquiz['count'] = 0;
            }else{
                $totalquiz;
            }
            if(empty($totalgrades)){
                $totalgrades['count'] = 0;
            }else{
                $totalgrades;
            }
            if(empty($procount)){
                $procount = 0;
            }else{
                $procount;
            }
            if(empty($totalfiles)){
                $totalfiles['count'] = 0;
            }else{
                $totalfiles;
            }
            if(empty($totalquestions)){
                $totalquestions['count'] = 0;
            }else{
                $totalquestions;
            }
        $usersviewContext = [
            'avgrating' => $avgrating,
            'coursedetails' => $programarrays,
            "userid" => $userrecord->id,
            "id" => $userrecord->id,
            "username" => fullname($userrecord),
            "userimage" => $user_image,
            'allcoursecount' => $allenrolledcou,
            'open_type' => $rolename,
            'coursename' => $coursesarray,
            'filecount' => $totalfiles,
            'quiz' => $totalquiz,
            'questioncount' => $totalquestions,
            'procount' => $procount, 
            'gradecount' => $totalgrades,
            "empid" => $userOrg->open_employeeid != NULL ? $userOrg->open_employeeid : 'N/A',
            "user_email" => $userrecord->email,
            "organisation" => $usercostcenter ? $usercostcenter : 'N/A',
            "department" => $userdepartment ? $userdepartment : 'ALL',
            "subdepartment" => $usersubdepartment ? $usersubdepartment : 'ALL',
            "timezone" => core_date::get_user_timezone($userrecord->timezone),
            "address" => $userrecord->address != NULL ? $userrecord->address : 'N/A',
            "state"=>!empty(trim($userrecord->open_state)) ? $userrecord->open_state:'N/A',
            "phone" => $userrecord->phone1 ? $userrecord->phone1 : 'N/A',
            "location" => $empcity,
            "certimg" => $OUTPUT->image_url('certicon','local_employees'),
            "usercontent" => $usercontent,
            "existingplugin" => $existingplugin,
            "editprofile" => new moodle_url("/user/editadvanced.php", array('id' => $userrecord->id, 'returnto' => 'profile')),
            "supervisorname" => $reporting_username,
            "capabilityedit" => $capabilityedit,
            "loginasurl" => $loginasurl,
            "firstlabelstring" => $labelstring->firstlevel,
            "secondlabelstring" => $labelstring->secondlevel,
            "thirdlabelstring" => $labelstring->thirdlevel,

        ];
        $value = $this->render_from_template('local_employees/profile', $usersviewContext);
        return $value;
    }
    
    /**
     * [user_page_top_action_buttons description]
     * @return [html] [top action buttons content]
     */
	public function employees_page_top_action_buttons(){
		global $CFG;
		$systemcontext = context_system::instance();
        return $this->render_from_template('local_employees/employeestopaction', array('contextid' => $systemcontext->id));
	}
    /**
     * [render_form_status description]
     * @method render_form_status
     * @param  \local_employees\output\form_status $page [description]
     * @return [type]                                    [description]
     */
    public function render_form_status(\local_employees\output\form_status $page) {
        $data = $page->export_for_template($this);
        return parent::render_from_template('local_employees/form_status', $data);
    }

    /**
     * [display_users description]
     * @method manageusers_content
     * @param  $filter default false
     */
    public function manage_employees_content($filter = false){
        global $USER;

        $systemcontext = context_system::instance();

        $options = array('targetID' => 'manage_employees1','perPage' => 12, 'cardClass' => 'col-md-6 col-lg-4 col-xl-3 col-12', 'viewType' => 'table');
        $options['methodName']='local_employees_manage_employees_view';
        $options['templateName']='local_employees/employees_view';
        $options = json_encode($options);

        $dataoptions = json_encode(array('userid' =>$USER->id,'contextid' => $systemcontext->id));
        $filterdata = json_encode(array());

        $context = [
                'targetID' => 'manage_employees1',
                'options' => $options,
                'dataoptions' => $dataoptions,
                'filterdata' => $filterdata
        ];
        if($filter){
            return  $context;
        }else{
            return  $this->render_from_template('local_costcenter/cardPaginate', $context);
        }
    }
}
