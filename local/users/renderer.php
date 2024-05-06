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
class local_users_renderer extends plugin_renderer_base {   
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
        $user_image = $OUTPUT->user_picture($userrecord, array('size' => 80, 'link' => false));
        /*user roles*/
        $userroles = get_user_roles($systemcontext, $id);
        if(!empty($userroles)){
                $rolename  = array();
                foreach($userroles as $roles) {
                    $rolename[] = ucfirst($roles->name);
                }
                $roleinfo = implode(", ",$rolename);
        } else {
            $roleinfo = "Employee";
        }
        $sql3 = "SELECT cc.fullname, u.open_employeeid,
                u.open_costcenterid,u.open_supervisorid,
                u.department, u.open_subdepartment ,
                u.open_departmentid      
                FROM {local_costcenter} cc, {user} u
                WHERE u.id=:id AND u.open_costcenterid=cc.id";
        $userOrg = $DB->get_record_sql($sql3, array('id' => $id));
        $usercostcenter = $DB->get_field('local_costcenter', 'fullname',  array('id' => $userOrg->open_costcenterid));
        if($userOrg->open_departmentid == 0){
            $userdepartment = 'All';
        } else{

        $userdepartment = $DB->get_field('local_costcenter', 'fullname',  array('id' => $userOrg->open_departmentid));
        }
        $usersubdepartment = $DB->get_field('local_costcenter', 'fullname',  array('id' => $userOrg->open_subdepartment));
        if(!empty($userrecord->phone1)){
                $contact = $userrecord->phone1;
        }else{
                $contact = 'N/A';
        }
        if(!empty($userrecord->city)){
                $usercity = $userrecord->city;
        }else{
                $usercity = 'N/A';
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
        // $usercontent = new stdClass();
        // $core_component = new core_component();
        // $local_pluginlist = $core_component::get_plugin_list('local');
        // $existingplugin = array();
        // $usercontent = array();
        // foreach($local_pluginlist AS $pluginname => $pluginurl){
        //     $userclass = '\local_'.$pluginname.'\local\user';
        //     if(class_exists($userclass)){
        //         $plugininfo = array();
        //         $pluginclass = new $userclass;
        //         if(method_exists($userclass, 'user_profile_content')){
        //             $plugindata = $pluginclass->user_profile_content($id,true);
        //             $usercontent[] = $plugindata;
        //             $plugininfo['userenrolledcount'] = $plugindata->count;
        //             $plugininfo['string'] = $plugindata->string;
        //         }
        //     }
        // }
        // ksort($existingplugin);
        // $existingplugin = array_values($existingplugin);
        if(is_siteadmin() || has_capability('local/users:edit',$systemcontext) || ($USER->id == $id)){
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
    //for tabs dispalying
        // $core_component = new core_component();
        // $plugins = $core_component::get_plugin_list('local');
        // $pluginarray = array();
        // foreach ($plugins as $key => $valuedata) {
        //     $userclass = '\local_'.$key.'\local\user';
        //     if(class_exists($userclass)){
        //         $pluginclass = new $userclass;
        //         if(method_exists($userclass, 'user_profile_content')){
        //             $pluginarray[$key] = true;
        //         }
        //     }
        // }
        // $pluginarray['skills'] = 1;
        $certificatecount = 0;
        $options = array('targetID' => 'display_modulesdata');
       
        $userattendedsql = "SELECT COUNT(stat.id) as statuscount
                              FROM {attendance_log} al
                              JOIN {attendance_sessions} ats ON al.sessionid = ats.id
                              JOIN {attendance} a ON ats.attendanceid = a.id
                              JOIN {attendance_statuses} stat ON al.statusid = stat.id
                              JOIN {local_program_level_courses} lplc ON a.course = lplc.courseid
                              JOIN {local_program_levels} pl ON lplc.programid = pl.programid
                              AND lplc.levelid = pl.id
                             WHERE al.studentid = {$USER->id} AND stat.acronym IN ('P','L')
                              AND pl.active = 1";

        $userattended = $DB->count_records_sql($userattendedsql);

        $role = $DB->get_record_sql("SELECT id, shortname FROM {role} WHERE shortname = 'student'");
        $programcourses = $DB->get_record_sql("SELECT distinct(lplc.programid),lplc.levelid
                                        FROM {local_program_level_courses} lplc
                                        JOIN {local_program_levels} pl ON lplc.programid = pl.programid
                                        AND lplc.levelid = pl.id
                                        JOIN {local_program_users} pu ON lplc.programid = pu.programid
                                       WHERE pl.active = 1 AND pu.userid = {$USER->id}");

        if ($programcourses == null) {
            $programcourses = new \stdClass;
            $programcourses->programid = 0;
            $programcourses->levelid = 0;
        }

        // To get the all sessions in active semester.
        $totalattdencesql = "SELECT COUNT(DISTINCT(ats.id))
                              FROM {attendance_sessions} ats
                              JOIN {attendance} a ON a.id = ats.attendanceid
                              JOIN {local_program_level_courses} lplc ON a.course = lplc.courseid
                              JOIN {local_program_levels} pl ON lplc.programid = pl.programid
                              AND lplc.levelid = pl.id
                              JOIN {role_assignments} rl ON rl.userid = {$USER->id}
                             WHERE pl.active = 1 AND pl.programid = {$programcourses->programid}
                              AND lplc.levelid = {$programcourses->levelid} AND rl.roleid = {$role->id}";

        $totalattdence = $DB->count_records_sql($totalattdencesql);

        if ($userattended && $totalattdence > 0) {
            $percentage = round(($userattended / $totalattdence) * 100);
        } else {
            $percentage = 0;
        }
        
        $usersviewContext = [
            "userid" => $userrecord->id,
            "username" => fullname($userrecord),
            "userimage" => $user_image,
            "rolename" => $roleinfo,
            "empid" => $userOrg->open_employeeid != NULL ? $userOrg->open_employeeid : 'N/A',
            "user_email" => $userrecord->email,
            "organisation" => $usercostcenter ? $usercostcenter : 'N/A',
            "department" => $userdepartment ? $userdepartment : 'ALL',
            "subdepartment" => $usersubdepartment ? $usersubdepartment : 'ALL',
            "timezone" => core_date::get_user_timezone($userrecord->timezone),
            "address" => $userrecord->address != NULL ? $userrecord->address : 'N/A',
            "state"=>!empty(trim($userrecord->open_state)) ? $userrecord->open_state:'N/A',
            "phnumber" => $contact,
            "location" => $usercity,
            "certimg" => $OUTPUT->image_url('certicon','local_users'),
            // "usercontent" => $usercontent, 
            // "existingplugin" => $existingplugin,
            "editprofile" => new moodle_url("/user/editadvanced.php", array('id' => $userrecord->id, 'returnto' => 'profile')),
            "prflbgimageurl" => $OUTPUT->image_url('prflbg', 'local_users'),
            "certificatescount" => $certificatecount,
            "supervisorname" => $reporting_username,
            "capabilityedit" => $capabilityedit,
            "loginasurl" => $loginasurl,
            "options" => $options,
            // "pluginslist" => $pluginarray,
            "percentage" => $percentage,
            "firstlabelstring" => $labelstring->firstlevel,
            "secondlabelstring" => $labelstring->secondlevel,
            "thirdlabelstring" => $labelstring->thirdlevel,
          
        ];
        $value = $this->render_from_template('local_users/profile', $usersviewContext);
        return $value;
    }
    /**
     * [user_page_top_action_buttons description]
     * @return [html] [top action buttons content]
     */
	public function user_page_top_action_buttons(){
		global $CFG;
		$systemcontext = context_system::instance();
        return $this->render_from_template('local_users/usertopactions', array('contextid' => $systemcontext->id));
	}
    /**
     * [render_form_status description]
     * @method render_form_status
     * @param  \local_users\output\form_status $page [description]
     * @return [type]                                    [description]
     */
    public function render_form_status(\local_users\output\form_status $page) {
        $data = $page->export_for_template($this);
        return parent::render_from_template('local_users/form_status', $data);
    }
    /**
     * [display_users description]
     * @method manageusers_content
     * @param  $filter default false
     * @author
     */
    public function manageusers_content($filter = false){
        global $USER;
        $status = optional_param('status', '', PARAM_RAW);
        $costcenterid = optional_param('costcenterid', '', PARAM_INT);
        $departmentid = optional_param('departmentid', '', PARAM_INT);
        $subdepartmentid = optional_param('subdepartmentid', '', PARAM_INT);
        $systemcontext = context_system::instance();
        $options = array('targetID' => 'manage_users1','perPage' => 12, 'cardClass' => 'col-md-6 col-lg-4 col-xl-3 col-12', 'viewType' => 'table');
        $options['methodName']='local_users_manageusers_view';
        $options['templateName']='local_users/users_view'; 
        $options = json_encode($options);
        
        $dataoptions = json_encode(
            array(
                'userid' => $USER->id,
                'contextid' => $systemcontext->id,
                'status' => $status,
                'costcenterid' => $costcenterid,
                'departmentid' => $departmentid,
                'subdepartmentid' => $subdepartmentid
            )
        );
        $filterdata = json_encode(
            array('status' => $status,
                'organizations' => $costcenterid,
                'departments' => $departmentid,
                'subdepartment' => $subdepartmentid
            )
        );
        $context = [
                'targetID' => 'manage_users1',
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
    public function get_enrolled_program($id) {
        global $CFG, $OUTPUT, $DB, $PAGE, $USER;
        $PAGE->set_url('/local/users/profile.php');
        $userid = $id;
        $programid = $DB->get_field('local_program_users', 'programid', array('userid' =>$id));
        $todaydate = time();
        $count = 0;
        $startdate = '';
        $enddate = '';
        $percentage = '';
        $params = array();
        $params['programid'] = $programid;
        $programsdetails = $DB->get_records('local_program_levels', $params);
        foreach ($programsdetails as $key => $value) {
            if($value->enddate <> 0){
                if($todaydate > $value->enddate)
                {
                    $count++;
                }
            } else {
                $percentage = 0;
            }
        }
        $countsem = count($programsdetails);
        if($count >= 1){
            $percentage = ($count/$countsem)*100;
            $percentage = floor($percentage);
        }
        $programname = new \stdClass();
        $currentsemester = new \stdClass();
        if($programid != null){

        $programname = $DB->get_record('local_program', array('id' =>$programid));
        $batchname = $DB->get_field('cohort', 'name', array('id' => $programname->batchid));
        if (strlen($batchname) > 6) {
                    $batchname = substr($batchname, 0, 6).'...';
                }

        $totalcourses = $DB->count_records('local_program_level_courses', array('programid' => $programid));
        
        $course = $DB->get_records('local_program_level_courses', array('programid' => $programid));

        $currentsemester = $DB->get_record('local_program_levels', array('programid' =>$programid, 'active' =>1));
         if($currentsemester){
                if($currentsemester->startdate == 0){
                    $startdate = 'N/A';
                } else {
                    $startdate = date('d-M-Y',$currentsemester->startdate);
                }
                if($currentsemester->enddate == 0){
                    $enddate = 'N/A';
                } else {
                    $enddate = date('d-M-Y',$currentsemester->enddate);
                }
            }


        $totalsems = $DB->count_records('local_program_levels', array('programid'=>$programid));
        } else {
            $programname->name = 'N/A';
            $batchname = 'N/A';
            $totalcourses = 0;
            $currentsemester->level = 'N/A';
            $totalsems = 0;
        }
            if($currentsemester->startdate == 0){
                $startdate = 'N/A';
            } else {
                $startdate = date('d-M-Y',$currentsemester->startdate);
            }
            if($currentsemester->enddate == 0){
                $enddate = 'N/A';
            } else {
                $enddate = date('d-M-Y',$currentsemester->enddate);
            }
            if($percentage == null){
                $percentage = 0;
            }
            if($percentage == 'NAN'){
                $percentage = 0;
            }
        $usersprgram = [
            'id'=> $userid,
            'roleid'=> $programid,
            'programname' => $programname->name,
            'currentsemester' =>$currentsemester->level,
            'startdate' =>$startdate,
            'enddate' =>$enddate,
            'totalsems' => $totalsems,
            'batchname' => $batchname,
            'totalcourses' => $totalcourses,
            'percentage' => $percentage,
        ];
        echo $OUTPUT->render_from_template('local_users/userprofile', $usersprgram);
    }
    public function get_coursedetails($id){
        global $CFG, $OUTPUT, $DB, $PAGE, $USER, $COURSE;
        $PAGE->set_url('/local/users/profile.php');
        $data = '';
        $programid = $DB->get_field('local_program_users', 'programid', array('userid' =>$id));
        $courses = array();
            $totalcourses = $DB->get_records('local_program_level_courses', array('programid' => $programid));
        if(!empty($programid) && !empty($totalcourses)){
            $line = array();
            foreach ($totalcourses as $key => $value) {
                
                      $courseids[$key] = $value->courseid;
            }
            $var = implode(',', $courseids);
            $courses = $DB->get_records_sql("SELECT * FROM {course} WHERE id IN ($var)");
            
            $params = array();
            $params['userid'] = $id; 
            foreach($courses as $key => $value){
                require_once($CFG->dirroot.'/local/includes.php');
                if (strlen($value->fullname) > 8) {
                    $value->fullname = substr($value->fullname, 0, 8).'...';
                }
                    $includes = new user_course_details();
                    $courseimage = $includes->course_summary_files($value);
                        $value->courseimage = $courseimage;
                $coursecontext = $DB->get_field('context','id', array('instanceid' => $value->id , 'contextlevel' =>50));
                $instructors = $DB->get_records_sql("SELECT u.* FROM {user} u JOIN {role_assignments} ra ON ra.userid = u.id WHERE ra.roleid = 3 AND ra.contextid = $coursecontext");
                
                $countoftopics = "SELECT COUNT(id) FROM {course_sections} WHERE course = $value->id AND section >= 1";
                    $nooftopics = $DB->count_records_sql($countoftopics, $params);
                    $value->topics = $nooftopics;
                if($value->open_departmentid != 0){
                    $value->open_departmentid = $DB->get_field_sql("SELECT fullname FROM {local_costcenter} WHERE id = $value->open_departmentid AND depth > 1");                
                } else if($value->open_departmentid == 0){
                    $value->open_departmentid = 'ALL';
                } else{
                    $value->open_departmentid = 'N/A';
                }
                $instructors = array_keys($instructors);
                $i=0;
                $j=0;
                    foreach($instructors as $instructor => $val){
                        $userrecord = $DB->get_record('user', array('id' => $val));
                        $line[$j]['username'] = $userrecord->username;
                        $user_image = $OUTPUT->user_picture($userrecord, array('size' => 15, 'link' => false));
                        $line[$i]['teacherimage'] = $user_image;
                        $value->teacherimg = $line;
                        $i++;
                        $j++;
                }
                 $params['id'] = $value->id;
            $totalmodules = "SELECT COUNT(*) FROM {course_modules} cm WHERE cm.course = :id";
            $totalmodulescount = $DB->count_records_sql($totalmodules, $params);
            
            // $completedmodules = "SELECT COUNT(cmc.id) FROM {course_modules_completion} cmc LEFT JOIN {course_modules} cm ON cmc.coursemoduleid = cm.id WHERE cm.course = :id";

            $completedmodules = "SELECT COUNT(cmc.id) FROM {user} u JOIN {course_modules_completion} cmc ON u.id = cmc.userid JOIN {course_modules} cm ON cmc.coursemoduleid = cm.id WHERE cm.course = :id AND u.id = :userid";
            $completedmodulescount = $DB->count_records_sql($completedmodules, $params);

            $courseprogress = $completedmodulescount/$totalmodulescount*100;
            $value->courseprogress = floor($courseprogress);     
            }
        }else{
            $data = get_string('nocoursesareavailable', 'local_users');
        }
        $userscourses = [
          'records' => array_values($courses),
          'data' => $data,
        ];
        echo $OUTPUT->render_from_template('local_users/userprofiletabs', $userscourses);
    }
}
