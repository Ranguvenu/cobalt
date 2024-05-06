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
 * @author eabyas <info@eabyas.in>
 * @package
 * @subpackage local_program
 */
namespace local_program\output;
require_once($CFG->dirroot . '/local/program/lib.php');
require_once($CFG->dirroot . '/user/lib.php');
defined('MOODLE_INTERNAL') || die;

if (file_exists($CFG->dirroot . '/local/includes.php')) {
    require_once($CFG->dirroot . '/local/includes.php');
}

use context_system;
use html_table;
use html_writer;
use local_program\program;
use plugin_renderer_base;
use user_course_details;
use moodle_url;
use stdClass;
use single_button;
use tabobject;
use core_completion\progress;

class renderer extends plugin_renderer_base {
    /**
     * [render_program description]
     * @method render_program
     * @param  \local_program\output\program $page [description]
     * @return [type]                                  [description]
     */
    public function render_program(\local_program\output\program $page) {
        $data = $page->export_for_template($this);
        return parent::render_from_template('local_program/program', $data);
    }
    /**
     * Display the program tabs
     * @return string The text to render
     */
    public function get_program_tabs($selected_subdepts = null, $selectedcostcenterid= null, $selecteddepartmentid = null,$selectedprogram = null,$selectedstatus = null) {
        global $CFG, $OUTPUT,$DB;
        $stable = new stdClass();
        $stable->thead = true;
        $stable->start = 0;
        $stable->length = -1;
        $stable->search = '';
        $programscontent = $this->viewprograms($stable,$selected_subdepts, $selectedcostcenterid, $selecteddepartmentid,$selectedprogram,$selectedstatus);
        $context = context_system::instance();

        $programtabslist = [
            'programslist' => $programscontent,
            'contextid' => $context->id,
            'plugintype' => 'local',
            'plugin_name' =>'program',
            'is_siteadmin' => ((has_capability('local/program:manageprogram',
            context_system::instance())) || is_siteadmin()) ? true : false,
            'creataprogram' => ((has_capability('local/program:manageprogram',
            context_system::instance()) && has_capability('local/program:createprogram',
            context_system::instance())) || is_siteadmin()) ? true : false,
        ];
        return $this->render_from_template('local_program/programtabs', $programtabslist);
    }
    /**
     * [viewprograms description]
     * @method viewprograms
     * @param  [type]         $stable [description]
     * @return [type]                 [description]
     */
    public function viewprograms($stable,$subdepts = null, $costcenterid= null, $departmentid = null,$program = null,$status = null,$groups=null) {
        $labelstring = get_config('local_costcenter');

        global $OUTPUT, $CFG, $DB;
        $systemcontext = context_system::instance();
        if (file_exists($CFG->dirroot . '/local/includes.php')) {
            require_once($CFG->dirroot . '/local/includes.php');
            $includes = new user_course_details();
        }
        if ($stable->thead) {

            $programs = (new program)->programs($stable,false,$subdepts, $costcenterid, $departmentid,$program,$status,$groups);
            if ($programs['programscount'] > 0) {
                $table = new html_table();
                $table->head = array('', '', '');
                $table->id = 'viewprograms';
                $return = html_writer::table($table);
            } else {
                $return = "<div class='alert alert-info text-center'>" .
                        get_string('noprograms', 'local_program') . "</div>";
            }
        } else {
            $programs = (new program)->programs($stable,false,$subdepts, $costcenterid, $departmentid,$program,$status,$groups);
            $data = array();
            $programchunks = array_chunk($programs['programs'], 3);
            foreach ($programchunks as $bc_data) {
                $row = [];
                foreach ($bc_data as $sdata) {
                    $line = array();
                    $program = $sdata->name;
                    if (!empty($sdata->programlogo)) {
                        $itemid = $sdata->programlogo;
                    }
                    $url = $CFG->wwwroot.'/local/program/pix/default.jpg';
                    $fs = get_file_storage();

                    if ($files = $fs->get_area_files($systemcontext->id, 'local_program', 'programlogo', $itemid, 'sortorder', false)) {
                        foreach ($files as $file) {
                                $fileurl = moodle_url::make_pluginfile_url($file->get_contextid(),
                                $file->get_component(),
                                $file->get_filearea(),
                                $file->get_itemid(),
                                $file->get_filepath(),
                                $file->get_filename()
                            );
                            $url = $fileurl;
                        }
                    }

                    $programname = $program;
                    $description = \local_costcenter\lib::strip_tags_custom(strip_tags($sdata->description));
                    $isdescription = '';
                    if (empty($description)) {
                        $isdescription = false;
                    } else {
                        $isdescription = true;
                        if (strlen($description) > 130) {
                            $decsriptionCut = substr($description, 0, 130);
                            $decsriptionstring = \local_costcenter\lib::strip_tags_custom(strip_tags($decsriptionCut));
                        } else{
                            $decsriptionstring = "";
                        }
                    }

                    if ($sdata->batchid) {
                        $stream =  $DB->get_field('cohort', 'name',
                            array('id' => $sdata->batchid));
                    }
                    if($sdata->hasadmissions == 1) {
                        $currdate = time();
                        if ($sdata->enddate > $currdate) {
                            $addmissions = get_string('openedforadmsns', 'local_program');
                            $isactivesql = "SELECT active FROM {local_program_levels} WHERE programid = :programid";
                            $isactive = $DB->get_fieldset_sql($isactivesql, array('programid' => $sdata->id));
                            if (in_array(1, $isactive)) {
                                $makesemactive = get_string('openedforadmsns', 'local_program');
                                $class = 'text-success';
                                $addmissions = '';
                            } else {
                                $makesemactive = get_string('openedforadmsns','local_program');
                                $addmissions = '';
                                $class = 'text-success';
                            }
                        } else {
                            $addmissions = get_string('admissionsclosed','local_program');
                            $makesemactive = '';
                            $class = 'text-primary';
                          }
                    }

                     else {
                        $addmissions = '';
                    }
                    // $streamname = strlen($stream) > 15 ? substr($stream, 0, 15) . ".." : $stream;
                    $level = $DB->count_records('local_program_levels',
                            array('programid' =>$sdata->id));
                    $curriculumname = $DB->get_field('local_curriculum', 'name', array('id' => $sdata->curriculumid));
                    if (!$curriculumname) {
                         $curriculumname = "N/A";
                    }
                     $count_eusers = new program();
                    $counteu = $count_eusers->count_enrolled_users($sdata->id);
                    $line['program'] = $program;
                    
                    $costcentername = $DB->get_field('local_costcenter', 'fullname', ['id' => $sdata->costcenter]);
                    $departmentname = $DB->get_field('local_costcenter', 'fullname', ['id' => $sdata->department]);
                    $subdepartmentname = $DB->get_field('local_costcenter','fullname', ['id' => $sdata->subdepartment]);

                    $line['costcentername'] = $costcentername;
                    if(strlen($departmentname) > 0){
                        $line['departmentname'] = $departmentname;
                    }else{
                        $line['departmentname'] = 'All';
                    }
                    if(strlen($subdepartmentname) > 0){
                        $line['subdepartmentname'] = $subdepartmentname;
                    }else{
                        $line['subdepartmentname'] = 'All';
                    }
                    if(is_siteadmin() || has_capability('local/costcenter:manage_multiorganizations', $systemcontext)){
                        $superadmin = true;
                    } else if(!is_siteadmin() && !has_capability('local/costcenter:manage_multiorganizations', $systemcontext) && has_capability('local/costcenter:manage_ownorganization', $systemcontext)){
                        $firstlevelhead = true;
                    } else if(!is_siteadmin() && !has_capability('local/costcenter:manage_multiorganizations', $systemcontext) && !has_capability('local/costcenter:manage_ownorganization', $systemcontext) && has_capability('local/costcenter:manage_owndepartments', $systemcontext)){
                        $secondlevelhead = true;
                    } else if(!is_siteadmin() && !has_capability('local/costcenter:manage_multiorganizations', $systemcontext) && !has_capability('local/costcenter:manage_ownorganization', $systemcontext) && !has_capability('local/costcenter:manage_owndepartments', $systemcontext) && has_capability('local/costcenter:manage_ownsubdepartments', $systemcontext)){
                        $thirdlevelhead = true;

                    }
                    
                    

                    $line['superadmin'] = $superadmin;
                    $line['firstlevelhead'] = $firstlevelhead;
                    $line['secondlevelhead'] = $secondlevelhead;
                    $line['thirdlevelhead'] = $thirdlevelhead;
                    
                    $line['firstlevel'] = $labelstring->firstlevel;
                    $line['secondlevel'] = $labelstring->secondlevel;
                    $line['thirdlevel'] = $labelstring->thirdlevel;

                    $line['programname'] = $programname;
                    $line['stream'] = $stream;
                    $line['class'] = $class;
                    $line['makesemactive'] = $makesemactive;
                    $line['hasadmissions'] = $sdata->hasadmissions;
                    $line['addmissions'] = $addmissions;
                    $line['streamname'] = $stream;
                    $line['curriculumname'] = $curriculumname;
                    $line['totallevels'] = $level;
                    $line['programicon'] = $OUTPUT->image_url('program_icon', 'local_program');
                    $line['description'] =  \local_costcenter\lib::strip_tags_custom(html_entity_decode($sdata->description));
                    $line['descriptionstring'] = $decsriptionstring;
                    $line['isdescription'] = $isdescription;
                    $line['enrolled_users'] = $counteu;
                    $line['completed_users'] = $sdata->completed_users;
                    $line['programid'] = $sdata->id;
                    $line['editicon'] = $OUTPUT->image_url('t/edit');
                    $line['deleteicon'] = $OUTPUT->image_url('t/delete');
                    $line['assignusersicon'] = $OUTPUT->image_url('t/assignroles');
                    $line['programcompletion'] = false;
                    if ($sdata->program_startdate) {
                        $prgmstartdate = date('d M Y', $sdata->program_startdate);
                    } else {
                        $prgmstartdate = 'N/A';
                    }
                    if($sdata->duration_format == 'M') {
                        $strtdate = $sdata->program_startdate;
                        if($sdata->duration > 1) {
                            $sdata->duration_format = 'Months';
                        } else {
                            $sdata->duration_format = 'Month';
                        }
                        if ($sdata->program_startdate) {
                            $relativenddate = strtotime('+ '.$sdata->duration.' months', $strtdate);
                            $prgmenddate =  date('d M Y', $relativenddate);                            
                        } else {
                            $prgmenddate = 'N/A';
                        }
                    } else {
                        $strtdate = $sdata->program_startdate;
                        if($sdata->duration > 1) {
                            $sdata->duration_format = 'Years';
                        } else {
                            $sdata->duration_format = 'Year';
                        }
                        if ($sdata->program_startdate) {
                            $relativenddate = strtotime('+ '.$sdata->duration.' year', $strtdate);
                            $prgmenddate =  date('d M Y', $relativenddate);
                        } else {
                            $prgmenddate = 'N/A';
                        }
                    }
                    if ($sdata->enddate) {
                        $admsnenddate = date('d-M-Y', $sdata->enddate);
                    } else {
                        $admsnenddate = 'N/A';
                    }
                    $line['programstartdate'] = $prgmstartdate;
                    $line['programenddate'] = $prgmenddate;
                    $line['admissionenddate'] = $admsnenddate;
                    $line['imageurl'] = $url;
                    $mouseovericon = false;
                    if ((has_capability('local/program:manageprogram', $systemcontext) || is_siteadmin())) {
                        $line['action'] = true;
                    }

                    if ((has_capability('local/program:editprogram', $systemcontext) || is_siteadmin())) {
                        $line['edit'] = true;
                        $mouseovericon = true;
                    }

                    if ((has_capability('local/program:deleteprogram', $systemcontext) || is_siteadmin())) {
                             $countrecords = $DB->get_records('local_program_levels', array('programid' => $sdata->id, 'active' => 1));
                        if(count($countrecords) > 0) {
                            $line['cannotdelete'] = true;
                            $mouseovericon = true;
                        } else {
                            $line['delete'] = true;
                            $mouseovericon = true;
                        }
                    }
                    if (is_siteadmin() || (has_capability('local/program:inactiveprogram', $systemcontext) || (has_capability('local/program:activeprogram', $systemcontext)))) {
                         $line['hide_show'] = true;
                         $mouseovericon = true;
                    }
                    if ((has_capability('local/program:manageusers', $systemcontext) || is_siteadmin())) {
                        $line['assignusers'] = true;
                        $mouseovericon = true;
                    }
                    $completionstatus = $DB->get_field('local_program_users', 'completion_status', array('programid'=>$sdata->id, 'userid'=>$USER->id));
                    if($completionstatus == 1){
                        $line['programcompletionstatus'] = true;
                    } else {
                        $line['programcompletionstatus'] = false;

                    }
                    $line['programcompletion_id'] = $programcompletion_id;
                    if($sdata->visible==1&&has_capability('local/program:inactiveprogram', $systemcontext)){
                        $line['hide'] = true;
                    }elseif(has_capability('local/program:activeprogram', $systemcontext)){
                        $line['show'] = true;
                    }

                    $line['mouse_overicon'] = $mouseovericon;
                    $row[] = $this->render_from_template('local_program/browseprogram', $line);
                }
                if (!isset($row[1])) {
                    $row[1] = '';
                    $row[2] = '';
                } else if (!isset($row[2])) {
                    $row[2] = '';
                }
                $data[] = $row;
            }

            $return = array(
                "recordsTotal" => $programs['programscount'],
                "recordsFiltered" => $programs['programscount'],
                "data" => $data
            );
        }
        return $return;
    }
    public function viewprogramlevels($programid, $levelid) {
        global $OUTPUT, $CFG, $DB, $USER;
        $systemcontext = context_system::instance();
        $assign_courses = '';
        $bcuser = $DB->record_exists('local_program_users',
            array('programid' => $programid, 'userid' => $USER->id));
        $userview = $bcuser && !is_siteadmin() && !has_capability('local/program:createprogram', $systemcontext) ? true : false;
        // Fetching Program Semesters.
        $programlevels = (new program)->program_levels($programid);

         if ($userview) {
            $mycompletedlevels = (new program)->myprogramlist($programid, $USER->id);
            $notcmptllevels = (new program)->mynextlevels($programid);
            if (!empty($notcmptllevels)) {
                $nextlevel = $notcmptllevels[0];
            } else {
                $nextlevel = 0;
            }
        }
        if (!empty($programlevels)) {
            $can_delete_levels = (new program)->levels_completion_status($programid);
            foreach ($programlevels as $k => $programlevel) {
                $activeclass = '';
                $disabled = '';
                $levelname = strlen($programlevel->level) > 15 ? substr($programlevel->level, 0, 15) ."<span class='levelrestrict'>..</span>": $programlevel->level;

                $programlevel->level = "<span title='$programlevel->level'>".$levelname."</span>";
                if ($programlevel->id == $levelid) {
                    $activeclass = 'active';
                }
                if ($userview && !is_siteadmin() && !has_capability('local/program:createprogram', $systemcontext) && array_search($programlevel->id, $mycompletedlevels) === false
                    && $nextlevel != $programlevel->id) {
                    $disabled = 'disabled';
                }
                $programlevel->mycompletionstatus = '';
                if ($userview && array_search($programlevel->id, $mycompletedlevels) !== false) {
                    $programlevel->mycompletionstatus = 'Completed';
                }

                $session = $DB->get_record('local_bc_session_signups', array('programid'=>$programid, 'levelid'=>$programlevel->id, 'completion_status'=>0));
                $programlevel->myinprogressstatus = '';
                if ($userview && array_search($programlevel->id, $mycompletedlevels) === false && !empty($session)) {
                    $programlevel->myinprogressstatus = 'Inprogress';
                }

                $programlevel->active = $activeclass;
                $programlevel->disabled = $disabled;
                $levelcount_records = $DB->get_records('local_bc_session_signups',
                array('programid' => $programid, 'levelid' => $programlevel->id));
                $candeletelevel = false;

                if($can_delete_levels){
                    if (count($levelcount_records) > 0 && has_capability('local/program:deletelevel',
                        $systemcontext)) {
                        $candeletelevel = false;
                    } else if (has_capability('local/program:deletelevel', $systemcontext)) {
                        $candeletelevel = true;
                    }
                }
                $programlevel->candeletelevel = $candeletelevel;
                $programlevels[$k] = $programlevel;
            }
        }

        $programlevelscontext = [
            'contextid' => $systemcontext->id,
            'programid' => $programid,
            'cancreatelevel' => has_capability('local/program:createlevel', $systemcontext),
            'canviewlevel' => has_capability('local/program:viewlevel', $systemcontext),
            'caneditlevel' => has_capability('local/program:editlevel', $systemcontext),
            'canaddcourse' => has_capability('local/program:addcourse', $systemcontext),
            'caneditcourse' => has_capability('local/program:editcourse', $systemcontext),
            'canmanagecourse' => has_capability('local/program:managecourse', $systemcontext),
            'candeletelevel' => $candeletelevel,
            'cfg' => $CFG,
            'levelid' => $levelid,
            'programlevel' => $programlevel,
            'userview' => $userview,
            'programlevels' => array_values($programlevels),
            'levelcourses' => $this->viewprogramcourses($programid, $levelid)
        ];
        $return = $this->render_from_template('local_program/levelstab_content',
            $programlevelscontext);
        return $return;
    }
    /**
     * [viewprogramcourses description]
     * @method viewprogramcourses
     * @param  [type]               $programid [description]
     * @return [type]                            [description]
     */
    public function viewprogramcourses($programid, $levelid) {
        global $OUTPUT, $CFG, $DB, $USER;
        $systemcontext = context_system::instance();
        $assign_courses = '';
        $view_mand = true;
        $bcuser = $DB->record_exists('local_program_users',
            array('programid' => $programid, 'userid' => $USER->id));
        $userview = $bcuser && !is_siteadmin() && !has_capability('local/program:createprogram', $systemcontext) ? true : false;
        $bclevel = new stdClass();
        $bclevel->programid = $programid;
        $bclevel->levelid = $levelid;

        if ($userview) {
            $mycompletedlevels = (new program)->mycompletedlevels($programid, $USER->id);
            $notcmptllevels = (new program)->mynextlevels($programid);
            if (!empty($notcmptllevels)) {
                $nextlevel = $notcmptllevels[0];
            } else {
                $nextlevel = 0;
            }
        }

        $programlevelcourses = (new program)->program_level_courses($programid, $levelid, $userview);
        $count = 0;
        foreach ($programlevelcourses as $programlevelcourse) {
            $contexcourse = \context_course::instance($programlevelcourse->id);
            if (is_enrolled($contexcourse, $USER)) {
                $count++;
            }
        }
        if ($count > 0) {
            $semcompletionstatus = true;
        } else {
            $semcompletionstatus = false;
        }
        if ($userview) {
            $enrolledcoursessqlquery = "SELECT c.id, c.fullname FROM {course} AS c
                                    JOIN {context} AS ctx ON c.id = ctx.instanceid
                                    JOIN {role_assignments} AS ra ON ra.contextid = ctx.id
                                    JOIN {user} AS u ON u.id = ra.userid WHERE u.id = :userid";
            $csenrolled = $DB->get_records_sql($enrolledcoursessqlquery, array('userid' => $USER->id));
            $enrolledcount = count($csenrolled);
            $completedcount = 0;
            foreach ($csenrolled as $cid => $cenrolled) {
                $completedsql = "SELECT * FROM {course_completions} WHERE userid = :userid AND course = :cid";
                $cscompleted = $DB->record_exists_sql($completedsql, array('userid' => $USER->id, 'cid' => $cenrolled->id));
                if ($cscompleted) {
                    $completedcount++;
                }
            }
            $level_completion_exists = $DB->record_exists('local_semesters_completions', ['programid' => $programid, 'userid' => $USER->id, 'levelid' => $levelid]);
            if($level_completion_exists){
                $semcompletionstat = true;
            } else{
                $semcompletionstat = false;
            }
            // if ($completedcount == $enrolledcount) {
            //     $semcompletionstat = true;
            // } else {
            //     $semcompletionstat = false;
            // }
        }
        $cannotdeletesem = false;
        $programlevel = $DB->get_record('local_program_levels', array('programid' => $programid, 'id' => $levelid));
        $semcompletionexists = $DB->record_exists('local_semesters_completions', array('programid' => $programid, 'levelid' => $levelid));
        if($semcompletionexists){
            $caneditsemester = true;
        } else {
            $caneditsemester = false;
        }

        $countsarray = array();
        foreach ($programlevelcourses as $programlevelcourse) {
            $totalcourseenrolledusers = enrol_get_course_users($programlevelcourse->id);
            if (count($totalcourseenrolledusers) > 0) {
                $countsarray[] = true;
            }
        }
        if($programlevel->enrolmethod == 2 && $programlevel->active == 1){
            $enableenrolicon = true;
        }else{
            $enableenrolicon = false;
        }
        if (in_array(1, $countsarray)) {
            $hasusers = true;
        }
        if ($programlevel->enddate > 0 && $programlevel->enddate != null) {
            if ($semcompletionexists) {
                $cannotdeletesem = true;
            } else {
                $cannotdeletesem = false;
            }
        }
        if ($cannotdeletesem || $programlevel->active || $hasusers) {
            $semesterdelete = true;
        } else {
            $semesterdelete = false;
        }
        if(!empty($programlevel->description)){
                $description = substr(($programlevel->description), 0, 600).'...';
                $programlevel->wholedescription = \local_costcenter\lib::strip_tags_custom(($programlevel->description));
                // $programlevel->description = \local_costcenter\lib::strip_tags_custom(strip_tags($description));
            } else {
                $programlevel->wholedescription = '';
                $programlevel->description = '';
            }
            if (($programlevel->startdate > 0) && ($programlevel->enddate > 0)) {
                if($programlevel->startdate > time()){
                    $disablecourse = true;
                } else {
                    $disablecourse = false;
                }
                if($programlevel->startdate < time() && $programlevel->enddate < time()){
                     $disablecourse = false;   
                }
            }
        if (($programlevel->startdate > 0) && ($programlevel->enddate > 0)) {
            $programlevel->startdate = date('d-M-Y',$programlevel->startdate);
            $programlevel->enddate = date('d-M-Y',$programlevel->enddate);
        } else {
            $programlevel->startdate = "N/A";
            $programlevel->enddate = "N/A";
        }
        $programlevel->mycompletionstatus = '';
        if ($userview && array_search($programlevel->id, $mycompletedlevels) !== false) {
            $programlevel->mycompletionstatus = 'Completed';
        }
        $session = $DB->get_record('local_bc_session_signups', array('programid'=>$programid, 'levelid'=>$programlevel->id, 'completion_status'=>0));
        $programlevel->myinprogressstatus = '';
        if ($userview && array_search($programlevel->id, $mycompletedlevels) === false && !empty($session)) {
            $programlevel->myinprogressstatus = 'Inprogress';
        }
        
        $attendenceid = '';
        $access = '';
        $elec_crs_count = 0;
        $enrolme = '';
        $has_ele = $DB->get_field('local_program_levels', 'course_elective', ['id' => $levelid]);
        $elec_crs_count = $DB->get_field_sql("SELECT count(mandatory) FROM {local_program_enrolments} WHERE programid ='$programid' AND levelid ='$levelid' AND userid ='$USER->id' AND mandatory = 0 "
            
        );
        foreach ($programlevelcourses as $i => $bclevelcourse) {
            $coursecontext = \context_course::instance($bclevelcourse->id);
            $bclevelcourse->coursefullname = $bclevelcourse->course;
            $courselink = strlen($bclevelcourse->course) > 25 ? substr($bclevelcourse->course, 0, 25) . "..." : $bclevelcourse->course;
            $moduleid = $DB->get_field('modules', 'id', array('name' => 'attendance'));
            $attendenceid = $DB->get_field('course_modules', 'id', array('course' => $bclevelcourse->id, 'module' => $moduleid));    

            $url = new \moodle_url('/course/view.php', array('id' => $bclevelcourse->id));
            $hidecourse = false;
            if ($userview) {
                if ($bclevelcourse->mandatory == 0) { // Elective courses
                    if (is_enrolled($coursecontext, $USER)) {
                        if ($elec_crs_count < $has_ele ) {
                            $enrolme = true;
                            $hidecourse = false;
                            $view_mand = false;
                        }
                        $courseurl = html_writer::link($url, $courselink,['target'=>'__blank']);
                        $is_enrolled = true;
                    }else{

                        if ($elec_crs_count >= $has_ele) {
                            $hidecourse = true;
                            // $elec_crs_count++;
                            $enrolme = false;
                            $view_mand = true;
                        }else{
                            $view_mand = false;
                            $courseurl = html_writer::link($url, $courselink, ['style' => "pointer-events: none;opacity: 0.4;", 'target'=>'__blank']);
                            $enrolme = true;
                            $is_enrolled = false;
                        }
                    }
                }else{ // Mandatory courses
                    if (!$view_mand) {
                        $courseurl = html_writer::link($url, $courselink, ['style' => "pointer-events: none;opacity: 0.4;", 'target'=>'__blank']);
                    }else{
                        $courseurl = html_writer::link($url, $courselink,['target'=>'__blank']);
                        if ($elec_crs_count >= $has_ele) {
                            $view_mand = true;
                        }else{
                            $view_mand = false;
                        }
                    }
                }
            }else{ 
                $courseurl = html_writer::link($url, $courselink,['target'=>'__blank']);
            }
            if ($view_mand && $disablecourse == 0 && $programlevel->active == 1) {
                $view_mand = 'style = "pointer-events: all;opacity: 1;"';
            } else if ($view_mand && $disablecourse == 0 && $programlevel->active == 0) {
                $view_mand = 'style = "pointer-events: all;opacity: 1;"';
            }
            else{
                $view_mand = 'style = "pointer-events: none;opacity: 0.4;"';
            }
            $bclevelcourse->course = $courseurl;
            $bclevelcourse->is_enrolled = $is_enrolled;
            $bclevelcourse->is_level_active = $programlevel->active;
            $bclevelcourse->enrolme = $enrolme;
            $bclevelcourse->view_mand = $view_mand;
            $bclevelcourse->disablecourse = $disablecourse;
            unset($enrolme); // we don't need it now.
            $countrecords = $DB->count_records('local_program_level_courses',
                array('programid' => $programid, 'levelid' => $levelid,
                    'courseid' => $bclevelcourse->bclevelcourseid));
            $countenrolledusers = count_enrolled_users($coursecontext);
            $canremovecourse = false;
            $cannotremovecourse = false;
            if ($countrecords > 0 && has_capability('local/program:removecourse',
                $systemcontext) || $countenrolledusers > 0 && !$userview) {
                $canremovecourse = false;
                $cannotremovecourse = true;
            } else if (has_capability('local/program:removecourse', $systemcontext)) {
                $canremovecourse = true;
                $cannotremovecourse = false;
            }
            $bclevelcourse->canremovecourse = $canremovecourse;
            $bclevelcourse->cannotremovecourse = $cannotremovecourse;
            $bclevelcourse->atten = $attendenceid;
            $bclevelcourse->hidecourse = $hidecourse;
            // $course_completions_exists = $DB->record_exists('course_completions', array('course'=>$bclevelcourse->id, 'user'));
            $course_completion_exists = $DB->record_exists_sql("SELECT id FROM {course_completions} cc WHERE cc.course = $bclevelcourse->id AND cc.userid = $USER->id AND cc.timecompleted > 0");

            if($course_completion_exists){
                $courseprogress = 'Completed';
                $courseview = 'text-success float-right';
            } else {
                $courseprogress = 'Inprogress';
                $courseview = 'text-primary float-right';
            }
            $bclevelcourse->courseprogress = $courseprogress;
            $bclevelcourse->courseview = $courseview;
            $bclevelcourse->enrolid = $DB->get_field('enrol','id', array('courseid'=>$bclevelcourse->id, 'enrol'=>'manual'));
            $bclevelcourse->facultyroleid = $DB->get_field('role', 'id', array('shortname' => 'editingteacher'));
            $bclevelcourse->studentroleid = $DB->get_field('role', 'id', array('shortname' => 'student'));
            
            $costid = $DB->get_field('local_program', 'costcenter', array('id' => $bclevelcourse->programid));
            $deptid = $DB->get_field('local_program', 'department', array('id' => $bclevelcourse->programid));
            $subdptid = $DB->get_field('local_program', 'subdepartment', array('id' => $bclevelcourse->programid));
            $bclevelcourse->enrol_url = $CFG->wwwroot . '/local/courses/courseenrol.php?enrolid='.$bclevelcourse->enrolid.'&roleid='.$bclevelcourse->facultyroleid.'&id='.$bclevelcourse->id.'&programid='.$bclevelcourse->programid.'&costcenterid='.$costid.'&departmentid='.$deptid.'&subdepartmentid='.$subdptid;
            
            $batchid = $DB->get_field('local_program', 'batchid', array('id' => $bclevelcourse->programid));

            $bclevelcourse->enrol_student_url = $CFG->wwwroot . '/local/courses/courseenrol.php?enrolid='.$bclevelcourse->enrolid.'&roleid='.$bclevelcourse->studentroleid.'&id='.$bclevelcourse->id.'&programid='.$bclevelcourse->programid.'&costcenterid='.$costid.'&departmentid='.$deptid.'&subdepartmentid='.$subdptid.'&batchid='.$batchid;
            // Course image.
            if (file_exists($CFG->dirroot.'/local/includes.php')) {
                require_once($CFG->dirroot.'/local/includes.php');
                $includes = new user_course_details();

                $parentcourseid = $DB->get_field('local_program_level_courses','parentid',array('courseid'=> $bclevelcourse->id));
                
                $parentcourseexists = $DB->record_exists('course', array('id' => $parentcourseid));
                if($parentcourseexists){
                    $image_url = $DB->get_record('course',array('id'=> $parentcourseid));
                    if($image_url) {
                        $courseimage = $includes->course_summary_files($image_url);

                        if (is_object($courseimage)) {
                            $bclevelcourse->courseimage = $courseimage->out();                    
                        } else {
                            $bclevelcourse->courseimage = $courseimage;
                        }
                    }            
                } else {
                    $programcourseimageurl = $DB->get_record('course',array('id'=> $bclevelcourse->id));
                    if($programcourseimageurl){
                        $programcourseimage = $includes->course_summary_files($programcourseimageurl);
                        if (is_object($programcourseimage)) {
                            $bclevelcourse->courseimage = $programcourseimage->out();                    
                        }else{
                            $bclevelcourse->courseimage = $programcourseimage;
                        }
                    }
                  }  

            }
            $programlevelcourses[$i] = $bclevelcourse;
            if (empty($bclevelcourse->atten)) {
                $access = false;
            }else{
                $access = true;
            }
            if(!empty($bclevelcourse->summary)){
                $summary = substr(strip_tags($bclevelcourse->summary), 0, 100).'...';
                $bclevelcourse->wholesummary = \local_costcenter\lib::strip_tags_custom(strip_tags($bclevelcourse->summary));
                $bclevelcourse->summary = \local_costcenter\lib::strip_tags_custom(strip_tags($summary));
            } else {
                $bclevelcourse->wholesummary = '';
                $bclevelcourse->summary = '';
            }
        }
        $batchid = $DB->get_field('local_program', 'batchid', array('id' => $programid));
        $enrolstudenturl = $CFG->wwwroot . '/local/program/assign.php?levelid='.$bclevelcourse->levelid.'&batchid='.$batchid.'&costcenterid='.$costid;
        $programname = $DB->get_field('local_program', 'name', array('id' => $programid));
        $params = [];
        $params['levelid'] = $levelid;
        $params['programid'] = $programid;
        $programCompleted = $DB->get_field_sql("SELECT programid FROM {local_program_levels}  WHERE id = :levelid AND programid = :programid AND (DATE(FROM_UNIXTIME(startdate)) <= CURDATE() OR DATE(FROM_UNIXTIME(startdate)) >= CURDATE()) AND active = 0 ", $params);

        $createNewProgram = $DB->get_field_sql("SELECT programid FROM {local_program_levels} WHERE id = :levelid AND startdate = 0 AND enddate = 0", $params);
        $prevsemidsql = "SELECT id FROM {local_program_levels}
                           WHERE id = (SELECT max(id)
                                        FROM {local_program_levels}
                                        WHERE id < :levelid AND programid = :programid
                                    )";
        $prevsemid = $DB->get_field_sql($prevsemidsql, array('levelid' => $levelid, 'programid' => $programid));
        if ($prevsemid > 0) {
            $prevlvlid = $prevsemid;
        } else {
            $prevlvlid = 0;
        }
        $systemcontext = context_system::instance();
        $programcoursescontext = [
            'contextid' => $systemcontext->id,
            'cancreatelevel' => has_capability('local/program:createlevel', $systemcontext),
            'programid' => $programid,
            'name' => $programname,
            'has_ele' => $has_ele,
            'has_ele_info' => get_string('electiveinfouser', 'local_program', $has_ele),
            'is_elec_enrolled' => $elec_crs_count >= $has_ele ? true : false,
            'userid' => $USER->id,
            'programCompleted' => $programCompleted,
            'enableenrolicon' => $enableenrolicon,
            'enrolstudenturl' => $enrolstudenturl,
            'cannoteditsem' => $cannoteditsem,
            'canaddcourse' => has_capability('local/program:addcourse', $systemcontext),
            'caneditlevel' => has_capability('local/program:editlevel', $systemcontext),
            'caneditcourse' => has_capability('local/program:editcourse', $systemcontext),
            'canmanagecourse' => has_capability('local/program:managecourse', $systemcontext),
            'candeletelevel' => has_capability('local/program:deletelevel', $systemcontext),
            'createNewProgram' => $createNewProgram,
            'cfg' => $CFG,
            'levelposition' => $programlevel->position, // for semester position whether it is 
                                                        //sem-1 or sem-2 or other.
            'levelid' => $levelid,
            'is_level_active' => $programlevel->active,
            'candeletesem' => $semesterdelete,
            'atten' => array_values($programlevelcourses),
            'cantakeattendance' => has_capability('local/program:takesessionattendance',
                $systemcontext) && !is_siteadmin(),
            'programlevel' => $programlevel,
            'userview' => $userview,
            'semcompletionstatus' => $semcompletionstatus,
            'semcompletionstat' => $semcompletionstat,
            'access' => $access,
            'prevlvlid' => $prevlvlid,
            'programlevelcourses' => array_values($programlevelcourses)
        ];
        $return = $this->render_from_template('local_program/levelcoursescontent', $programcoursescontext);
        return $return;
    }
    /**
     * Display the program view
     * @return string The text to render
     */
    public function viewprogram($programid) {
        global $OUTPUT, $CFG, $DB, $USER, $PAGE;
        $systemcontext = context_system::instance();
        $stable = new stdClass();
        $stable->programid = $programid;
        $stable->thead = false;
        $stable->start = 0;
        $stable->length = 1;
        $program = (new program)->programs($stable);
        if (empty($program)) {
            print_error("noprograms", 'local_program');
        }
        $includesfile = false;
        if (file_exists($CFG->dirroot . '/local/includes.php')) {
            $includesfile = true;
            require_once($CFG->dirroot . '/local/includes.php');
            $includes = new user_course_details();
        }

        if ($program->programlogo > 0) {
            $program->programlogoimg = (new program)->program_logo($program->programlogo);
            if ($program->programlogoimg == false) {
                if($includesfile){
                    $program->programlogoimg = $includes->get_classes_summary_files($program);
                }
            }
        } else {
            if($includesfile){
                $program->programlogoimg = $includes->get_classes_summary_files($program);
            }
        }
        $program->department = $program->department == -1 ? get_string('all') : $DB->get_field('local_costcenter', 'fullname', array ('id' => $program->department));
        $program->subdepartment = $program->subdepartment == -1 ? get_string('all') : $DB->get_field('local_costcenter', 'fullname', array ('id' => $program->subdepartment));

        $groups_sql = "SELECT mc.name FROM {cohort} AS mc
            JOIN {local_groups} AS lg ON lg.cohortid = mc.id
            WHERE ',{$program->open_group},' LIKE concat('%,',lg.id,',%') ";
        $program->open_group = $program->open_group ? implode(', ', $DB->get_fieldset_sql($groups_sql)): get_string('all');
        $program->open_group_str = strlen($program->open_group) > 15 ? substr($program->open_group, 0, 15).'...': $program->open_group;

        $programcompletion = $user_tab = $course_tab = $session_tab = $action = $edit = $delete = false;
            $session_tab = false;
            $course_tab = true;
        if (has_capability('local/program:viewusers', context_system::instance())) {
            $user_tab = true;
        }
        if ((has_capability('local/program:manageprogram', context_system::instance()) || is_siteadmin())) {
            $action = true;
        }
        if ((has_capability('local/program:programcompletion', context_system::instance()) || is_siteadmin())) {
            $programcompletion = false;
        }
        if ((has_capability('local/program:editprogram', context_system::instance()) || is_siteadmin())) {
            $edit = true;
        }
        $unenrolbutton = $this->get_self_unenrollment_button($programid, $program->name);
        if(!is_null($unenrolbutton)){
            $action = true;
        }
        $assignusers = false;
        if ((has_capability('local/program:manageusers', context_system::instance()) || is_siteadmin())) {
            $assignusers = true;

        }
        $selfenrolmenttabcap = true;
        if (!has_capability('local/program:manageprogram', context_system::instance())) {
            $selfenrolmenttabcap = false;
        }
        if (!empty($program->description)) {
            $description = substr(($program->description), 0, 300).'...';
            // $description = \local_costcenter\lib::strip_tags_custom(strip_tags($description));
            $description = $description;
            $wholedescription = \local_costcenter\lib::strip_tags_custom(($program->description));
            unset($program->description);

        } else {
            $description = "";
        }
        $isdescription = '';
        $decsriptionstring = '';
        if (empty($description)) {
            $isdescription = false;
            $decsriptionstring = "";
        } else {
            $isdescription = true;
         }
         if (!empty($program->programlogo)) {
            $itemid = $program->programlogo;
        }
        $url =$CFG->wwwroot.'/local/program/pix/default.jpg';
        $fs = get_file_storage();

        if ($files = $fs->get_area_files((\context_system::instance())->id, 'local_program', 'programlogo', $itemid, 'sortorder', false)) {
            foreach ($files as $file) {
                    $fileurl = moodle_url::make_pluginfile_url($file->get_contextid(),
                    $file->get_component(),
                    $file->get_filearea(),
                    $file->get_itemid(),
                    $file->get_filepath(),
                    $file->get_filename()
                );
                // $download_url = $fileurl->out();
                $url =$fileurl;
            }
        }
        $bcuser = $DB->record_exists('local_program_users',
            array('programid' => $programid, 'userid' => $USER->id));
        $userview = $bcuser && !is_siteadmin() && !has_capability('local/program:createprogram', $systemcontext) ? true : false;

        if ($userview) {
            $mycompletedlevels = (new program)->mycompletedlevels($programid, $USER->id);
            $notcmptllevels = (new program)->mynextlevels($programid);
            if (!empty($notcmptllevels)) {
                $levelid = $notcmptllevels[0];
            }else {
                $levelid_sql = "SELECT id FROM {local_program_levels} WHERE programid = :programid ORDER BY id ASC ";
                $levelid = $DB->get_field_sql($levelid_sql, array('programid' => $programid));
            }
        } else {
            $levelid = $DB->get_field_select('local_program_levels', 'id',
            'programid = :programid ORDER BY id ASC ',
            array('programid' => $programid));// LIMIT 0, 1
        }
        $completionstatus = $DB->get_field('local_program_users', 'completion_status', array('programid'=>$programid, 'userid'=>$USER->id));
        if($completionstatus == 1){
            $programcompletionstatus = true;
        } else {
            $programcompletionstatus = false;
        }
        // Get batch of the current program.
        $batch=$DB->get_field_sql("SELECT c.name 
            FROM {cohort} c
            JOIN {local_groups} g on g.cohortid = c.id 
            JOIN {local_program} p on p.batchid = g.cohortid 
            WHERE p.id=$programid"
        );
        $curriculum=$DB->get_field_sql("SELECT c.name 
            FROM {local_curriculum} c
            JOIN {local_program} p on p.curriculumid = c.id 
            WHERE p.id=$programid"
        );
        $curriculumid=$DB->get_field_sql("SELECT c.id 
            FROM {local_curriculum} c
            JOIN {local_program} p on p.curriculumid = c.id 
            WHERE p.id=$programid"
        );

//download certificate code starts
        $core_component = new \core_component();
        $certificate_plugin_exist = $core_component::get_plugin_directory('tool', 'certificate');
        if ($certificate_plugin_exist) {
            $certid = $DB->get_field('local_program', 'certificateid', array('id'=>$programid));
        } else {
            $certid = false;
        }
        // $downloadurl = '';
        $sql = "SELECT id, programid, userid, completionstatus
                        FROM {local_programcompletions}
                        WHERE programid = :programid AND userid = :userid
                        AND completionstatus != 0 ";

        $completed = $DB->record_exists_sql($sql, array('programid'=>$programid, 'userid'=>$USER->id));
        // $program_exists = SELECT id
        //                 FROM {local_programcompletions}
        //                 WHERE programid = $programid
        $program_exists = $DB->record_exists('local_programcompletions', ['programid' => $programid]);
        if($program_exists || !has_capability('local/costcenter:manage_owndepartments', $systemcontext) && has_capability('local/costcenter:manage_ownsubdepartments', $systemcontext)){
            $cancreatelevel = false;
        } else{
            $cancreatelevel = true;
        }
        // $cancreatelevel = 
        if($certid) {
                $icon = '<i class="icon fa fa-download" aria-hidden="true"></i>';
                    if($completed) {
                       $certcode = $DB->get_field('tool_certificate_issues', 'code', array('moduleid'=>$programid, 'userid'=> $USER->id,'moduletype'=>'program'));

                       $array = array('preview'=>0, 'templateid'=>$certid,'code'=> $certcode);

                        $urlimage = new moodle_url('../../admin/tool/certificate/view.php?', $array);

                        $downloadurl = html_writer::link($urlimage, $icon, array('title'=>get_string('download_certificate','tool_certificate'),'target' => "_blank") );
                    } else {
                        
                        $downloadurl = html_writer::link($urlimage, $icon, array('title'=>get_string('certificatedownload','local_program')));
                    }
                }
                else {
                    $downloadurl = get_string('nodata', 'local_program');
                }   
                $certificateid = $DB->get_field('local_program', 'certificateid', ['id'=>$programid]);
                if($certificateid){
                    $certificatename = $DB->get_field('tool_certificate_templates', 'name',['id'=>$certificateid]); 
                } else{
                    $certificatename = '';
                }

                $p_startdate = date('d-m-Y', $program->program_startdate);
                if($program->duration_format == 'M') {
                    $sdate = $program->program_startdate;
                    if($program->duration > 1) {
                        $program->duration_format = 'Months';
                    } else {
                        $program->duration_format = 'Month';
                    }
                    if ($sdate) {
                        $newdate = strtotime('+ '.$program->duration.' months', $sdate);
                        $program_enddate =  date('d-m-Y', $newdate);
                        
                    } else {
                        $program_enddate = 'N/A';
                    }
                } else {
                    $sdate = $program->program_startdate;
                    if($program->duration > 1) {
                        $program->duration_format = 'Years';
                    } else {
                        $program->duration_format = 'Year';
                    }
                    if ($sdate) {
                        $newdate = strtotime('+ '.$program->duration.' year', $sdate);
                        $program_enddate =  date('d-m-Y', $newdate);
                    } else {
                        $program_enddate = 'N/A';
                    }
                }

        $programcontext = [
            'program' => $program,
            'programid' => $programid,
            'action' => $action,
            'edit' => $edit,
            'duration' =>$program->duration,
            'p_startdate' =>$p_startdate,
            'program_enddate' =>$program_enddate,
            'wholedescription' => $wholedescription,
            'duration_format' =>$program->duration_format,
            'programcompletion' => $programcompletion,
            'delete' => $delete,
            'assignusers' => $assignusers,
            'description' => $description,
            'descriptionstring' => $decsriptionstring,
            'isdescription' => $isdescription,
            'user_tab' => $user_tab,
            'course_tab' => $course_tab,
            'session_tab' => $session_tab,
            'programname' => $program->name,
            'cfg' => $CFG,
            'programcompletionstatus' => $programcompletionstatus,
            // 'cancreatelevel' => has_capability('local/program:createlevel', $systemcontext),
            'cancreatelevel' => $cancreatelevel,
            'seats_image' => $OUTPUT->image_url('GraySeatNew', 'local_program'),
            'levelid' => $levelid,
            // Get Program Semesters
            'programlevels' => $this->viewprogramlevels($programid, $levelid),
            'unenrolbutton' => $unenrolbutton,
            'batchname' => $batch,
            'curriculumname' => $curriculum,
            'curriculumid' => $curriculumid,
            'imageurl' => $url,
            'userview' => $userview,
            'downloadurl' => $downloadurl,
            'certificatename' => $certificatename,
        ];
        return $this->render_from_template('local_program/programContent', $programcontext);
    }
    public function get_self_unenrollment_button($programid, $programname){
        global $DB, $USER;
        $selfenrolled = $DB->record_exists('local_program_users', array('programid' => $programid, 'userid' => $USER->id, 'usercreated' => $USER->id));
        if(!$selfenrolled){
            return null;
        }
        $systemcontext = \context_system::instance();
        $object = html_writer::link('javascript:void(0)', '<i class="icon fa fa-user-times" aria-hidden="true" aria-label="" title ="'.get_string('unenrol').'"></i>', array('class' => 'course_extended_menu_itemlink unenrolself_module', 'onclick' => '(function(e){ require(\'local_program/program\').unEnrolUser({programid: '.$programid.', userid:'.$USER->id.', programname:\''.$programname.'\', contextid:'.$systemcontext->id.'}) })(event)'));
        $container = html_writer::div($object, '', array('class' => 'course_extended_menu_itemcontainer text-xs-center'));
        $liTag = html_writer::tag('li', $container);
        return html_writer::tag('ul', $liTag, array('class' => 'course_extended_menu_list'));
    }
    /**
     * [viewprogramusers description]
     * @method viewprogramusers
     * @param  [type]             $programid   [description]
     * @param  [type]             $stable      [description]
     * @return [type]                          [description]
     */
    public function viewprogramusers($stable, $grppage = false) {
        global $OUTPUT, $CFG, $DB, $PAGE;
        $search = '';
        $PAGE->requires->js_call_amd('local_program/programcompletion','load');
        $batchid = $stable->batchid;
        $programid = $stable->programid;
         $batch=$DB->get_record_sql("SELECT c.id,c.name, p.name as pname FROM {cohort} c 
                            JOIN {local_program} p ON c.id=p.batchid WHERE p.id=$programid");
        if (has_capability('local/program:manageusers', context_system::instance()) && has_capability('local/program:manageprogram', context_system::instance()) && ($grppage)) {
            $url = new moodle_url('/local/program/enrollusers.php', array('bcid' => $programid));
            $assign_users ='<ul class="course_extended_menu_list">
                                <li>
                                    <div class="createicon course_extended_menu_itemlink"><a href="' . $CFG->wwwroot . '/local/groups/assign.php?id='.$batch->id.'"><i class="icon fa fa-users" aria-hidden="true" title="'.get_string('assigncohorts', 'local_groups').'"></i></a>
                                    </div>
                                </li>
                            </ul>';
        } else if (has_capability('local/program:manageusers', context_system::instance()) && has_capability('local/program:manageprogram', context_system::instance())) {
            $url = new moodle_url('/local/program/enrollusers.php', array('bcid' => $programid));
            $assign_users ='<ul class="course_extended_menu_list">
                                <li>
                                    <div class="createicon course_extended_menu_itemlink"><a href="' . $CFG->wwwroot . '/local/program/users.php?download=1&amp;format=xls&amp;type=programwise&amp;bcid='.$programid.'&amp;search='.$search.'"><i class="icon fa fa-download" aria-hidden="true" title="'.get_string('programdownloadreport', 'local_program').'"></i></a>
                                    </div>
                                </li>
                                <li>
                                    <div class="createicon course_extended_menu_itemlink"><a href="' . $CFG->wwwroot . '/local/program/users.php?download=1&amp;format=xls&amp;type=coursewise&amp;bcid='.$programid.'&amp;search='.$search.'"><i class="icon fa fa-download" aria-hidden="true" title="'.get_string('coursedownloadreport', 'local_program').'"></i></a>
                                    </div>
                                </li>
                            </ul>';
        } else {
            $assign_users = "";
        }
        $core_component = new \core_component();
        $certificate_plugin_exist = $core_component::get_plugin_directory('tool', 'certificate');
        if ($certificate_plugin_exist) {
            $certid = $DB->get_field('local_program', 'certificateid', array('id'=>$programid));
        } else {
            $certid = false;
        }
        if ($stable->thead) {
            $programusers = (new program)->programusers($programid, $stable);
            if ($programusers['programuserscount'] > 0) {
                $table = new html_table();
                if ($grppage) {
                $head = array(get_string('employee', 'local_program'), get_string('email'), get_string('program', 'local_program'));
            } else {
                $head = array(get_string('employee', 'local_program'), get_string('email', 'local_program'), get_string('nooflevels', 'local_program'));
            }
                $head[] = get_string('certificate','local_program');
                $head[] = get_string('completionstatus','local_program');
                $table->head = $head;
            if ($grppage) {
                $table->id = 'viewgroupusers';

            } else {
                $table->id = 'viewprogramusers';
            }
                $table->attributes['data-programid'] = $programid;
                $table->align = array('center', 'center', 'center', 'center');
                if($certid){
                    $table->align[] = 'center';
                }

                $return = $assign_users.html_writer::table($table);
            } else {
                if ($batchid) {
                    $return = $assign_users."<div class='mt-15 text-center alert alert-info w-full pull-left'>" . get_string('nobatchusers', 'local_groups') . "</div>";
                } else if ($stable->programid) {
                    $return = $assign_users."<div class='mt-15 text-center alert alert-info w-full pull-left'>" . get_string('noprogramusers', 'local_program') . "</div>";
                }
            }
        } else {
            $programusers = (new program)->programusers($programid, $stable);
            $data = array();
            foreach ($programusers['programusers'] as $sdata) {
                $line = array();
                $line[] = '<div>
                                <span>' . $OUTPUT->user_picture($sdata) . ' ' . fullname($sdata) . '</span>
                            </div>';
                $line[] = '<span> <label for="email">' . $sdata->email . '</lable></span>';
                $currdate = time();
                $total_levels = $DB->count_records('local_program_levels',  array('programid' => $programid));
                $total_levels_data = $DB->get_records('local_program_levels',  array('programid' => $programid));
                $completedlevelscount = 0;
                foreach ($total_levels_data as $value) {
                    $completed_levels = $DB->record_exists('local_semesters_completions', array('levelid' => $value->id, 'userid' => $sdata->id));
                    if($completed_levels){
                        $completedlevelscount++;
                    } 
                    
                }
                /* Naveen Yada made few changes*/
                // $completed_levels = $DB->count_records_sql("SELECT COUNT(level) FROM {local_program_levels} WHERE programid = $programid AND enddate <= $currdate AND enddate <> 0");
                if ($grppage) {
                    $line[] = '<span> <label for="nooflevelscount">'.$batch->pname.'</lable></span>';
                } else {
                    $line[] = '<span><a href="'.$CFG->wwwroot.'/local/program/semesterwiseconfigure.php?bcid='.$programid.'"><label for="nooflevelscount">'.$completedlevelscount.'/'.$total_levels.'</lable></a></span>';
                }

                $sql = "SELECT id, programid, userid, completionstatus
                        FROM {local_programcompletions}
                        WHERE programid = :programid AND userid = :userid
                        AND completionstatus != 0 ";

                $completed = $DB->record_exists_sql($sql, array('programid'=>$programid, 'userid'=>$sdata->id));
                if($certid) {
                $icon = '<i class="icon fa fa-download" aria-hidden="true"></i>';
                    if($completed && $completedlevelscount == $total_levels) {
                       $certcode = $DB->get_field('tool_certificate_issues', 'code', array('moduleid'=>$programid,'userid'=>$sdata->id,'moduletype'=>'program'));

                       $array = array('preview'=>0, 'templateid'=>$certid,'code'=> $certcode);

                        $url = new moodle_url('../../admin/tool/certificate/view.php?', $array);

                        $downloadlink = html_writer::link($url, $icon, array('title'=>get_string('download_certificate','tool_certificate'),'target' => "_blank"));
                    } else {
                        $url = 'javascript: void(0)';
                        $downloadlink = html_writer::link($url, $icon, array('title'=>get_string('certificatedownload','local_program')));
                    }
                } else {
                        $downloadlink = get_string('nodata', 'local_program');
                }
                    $line[] =  $downloadlink;
                if ($completedlevelscount == $total_levels) {
                    if ($completed) {
                        $line[] = html_writer::span(get_string('completed', 'local_program'));
                    } else {
                        $link = get_string('nodata', 'local_program');
                        $line[] = html_writer::div($link, '', null);
                    }
                } else {
                    $line[] = html_writer::span(get_string('levelinprogress', 'local_program'));
                }
                $data[] = $line;

            }
            $return = array(
                "recordsTotal" => $programusers['programuserscount'],
                "recordsFiltered" => $programusers['programuserscount'],
                "data" => $data,
            );
        } 
        return $return;
    }
    public function viewprogramlastchildpopup($programid){
        global $OUTPUT, $CFG, $DB, $USER, $PAGE;
        $systemcontext = \context_system::instance();
        $stable = new stdClass();
        $stable->programid = $programid;
        $stable->thead = false;
        $stable->start = 0;
        $stable->length = 1;
        $program = (new program)->programs($stable);
        $context = context_system::instance();
        $program_status = $DB->get_field('local_program', 'status', array('id' => $programid));
        if (!has_capability('local/program:view_newprogramtab', context_system::instance()) && $program_status== 0) {
            print_error("You don't have permissions to view this page.");
        } else if (!has_capability('local/program:view_holdprogramtab', context_system::instance()) &&
            $program_status == 2) {
            print_error("You don't have permissions to view this page.");
        }
        if (empty($program)) {
            print_error("program Not Found!");
        }
        $includesfile = false;
        if (file_exists($CFG->dirroot . '/local/includes.php')) {
            $includesfile = true;
            require_once($CFG->dirroot . '/local/includes.php');
            $includes = new user_course_details();
        }

        if ($program->programlogo > 0) {
            $program->programlogoimg = (new program)->program_logo($program->programlogo);
            if ($program->programlogoimg == false) {
                if($includesfile){
                    $program->programlogoimg = $includes->get_classes_summary_files($sdata);
                }
            }
        } else {
            if($includesfile){
                $program->programlogoimg = $includes->get_classes_summary_files($program);
            }
        }

        $return = "";
        $program->userenrolmentcap = (has_capability('local/program:manageusers', context_system::instance())
            && has_capability('local/program:manageprogram', context_system::instance())
            && $program->status == 0) ? true : false;

        $stable = new stdClass();
        $stable->thead = true;
        $stable->start = 0;
        $stable->length = -1;
        $stable->search = '';


        $allocatedseats = $DB->count_records('local_program_users',
            array('programid' => $programid)) ;
        $coursesummary = \local_costcenter\lib::strip_tags_custom($course->summary,
                    array('overflowdiv' => false, 'noclean' => false, 'para' => false));
        // $description = \local_costcenter\lib::strip_tags_custom(html_entity_decode($program->description));
        $wholeprogramdescription = $description;


        $description = substr(($program->description), 0, 400).'...';
        $wholeprogramdescription = \local_costcenter\lib::strip_tags_custom(($program->description));
        unset($program->description);

        $isdescription = '';
        if (empty($description)) {
           $isdescription = false;
        } else {
            $isdescription = true;
            // if (strlen($description) > 250) {
            //     $decsriptionCut = substr($description, 0, 250);
            //     $decsriptionstring = \local_costcenter\lib::strip_tags_custom(html_entity_decode($decsriptionCut));
            // } else {
            //     $decsriptionstring = "";
            // }
        }

        $program_exists = $DB->record_exists('local_programcompletions', ['programid' => $programid]);
        if($program_exists || !has_capability('local/costcenter:manage_owndepartments', $systemcontext) && has_capability('local/costcenter:manage_ownsubdepartments', $systemcontext)){
            $cancreatelevel = false;
        } else{
            $cancreatelevel = true;
        }

        $programcontext = [
            'program' => $program,
            'programid' => $programid,
            'allocatedseats' => $allocatedseats,
            'description' => $description,
            'wholeprogramdescription' => $wholeprogramdescription,
            'cancreatelevel' => $cancreatelevel,
            // 'descriptionstring' => $decsriptionstring,
            'isdescription' => $isdescription,
            'contextid' => $context->id,
            'cfg' => $CFG,
            'linkpath' => "$CFG->wwwroot/local/program/view.php?bcid=$programid"
        ];
        return $this->render_from_template('local_program/programview', $programcontext);
    }
    
      public function programview_check($programid) {
        global $OUTPUT, $CFG, $DB, $USER, $PAGE;
        $stable = new stdClass();
        $stable->programid = $programid;
        $stable->thead = false;
        $stable->start = 0;
        $stable->length = 1;
        $program = (new program)->programs($stable);
        $context = context_system::instance();
        $program_status = $DB->get_field('local_program', 'status', array('id' => $programid));
        if (empty($program)) {
            print_error("program Not Found!");
        }

        return $program;
    }
    public function get_userdashboard_program($tab, $filter = false) {
        $systemcontext = context_system::instance();

        $options = array('targetID' => 'dashboard_program', 'perPage' => 6, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'card');
        $options['methodName'] = 'local_program_userdashboard_content_paginated';
        $options['templateName'] = 'local_program/userdashboard_paginated';
        $options['filter'] = $tab;
        $options = json_encode($options);
        $filterdata = json_encode(array());
        $dataoptions = json_encode(array('contextid' => $systemcontext->id));
        $context = [
            'targetID' => 'dashboard_program',
            'options' => $options,
            'dataoptions' => $dataoptions,
            'filterdata' => $filterdata
        ];
        if ($filter) {
            return  $context;
        } else {
            return  $this->render_from_template('local_costcenter/cardPaginate', $context);
        }
    }

      /**
     * [viewprogramusers description]
     * @method viewgroupusers
     * @param  [type]             $cohortid   [description]
     * @param  [type]             $stable      [description]
     * @return [type]                          [description]
     */
    public function viewgroupusers($stable, $grppage = false) {
        global $OUTPUT, $CFG, $DB, $PAGE;
        $search = '';
        // $PAGE->requires->js_call_amd('local_program/programcompletion','load');
        $batchid = $stable->cohortid;

        $batch = $DB->get_record_sql("SELECT DISTINCT(c.id), c.name
                               FROM {cohort} c
                               JOIN {cohort_members} cm ON cm.cohortid = c.id
                              WHERE cm.cohortid={$batchid}");
        if (has_capability('local/program:manageusers', context_system::instance()) && has_capability('local/program:manageprogram', context_system::instance()) && ($grppage)) {
            $assign_users ='<ul class="course_extended_menu_list">
                                <li>
                                    <div class="createicon course_extended_menu_itemlink"><a href="' . $CFG->wwwroot . '/local/groups/assign.php?id='.$batchid.'"><i class="icon fa fa-users" aria-hidden="true" title="'.get_string('assigncohorts', 'local_groups').'"></i></a>
                                    </div>
                                </li>
                            </ul>';
        } else {
            $assign_users = "";
        }

        if ($stable->thead) {
            $groupusers = (new program)->groupusers($batchid, $stable);

            if ($groupusers['groupuserscount'] > 0) {
                $table = new html_table();
                if ($grppage) {
                $head = array(get_string('employee', 'local_program'), get_string('email'), get_string('program', 'local_program'));
            }
                $table->head = $head;
            if ($grppage) {
                $table->id = 'viewgroupusersdata';
            }
                $table->attributes['data-groupid'] = $batchid;
                $table->align = array('left', 'left', 'left', 'left');
                $return = $assign_users.html_writer::table($table);
              
            } else {
                if (!$batch) {
                    $return = $assign_users."<div class='mt-15 text-center alert alert-info w-full pull-left'>" . get_string('nobatchusers', 'local_groups') . "</div>";
                }
            }
        } else {
            $groupusers = (new program)->groupusers($batchid, $stable);

            $data = array();
            foreach ($groupusers['groupusers'] as $sdata) {
                $line = array();
                $line[] = '<div>
                                <span>' . $OUTPUT->user_picture($sdata) . ' ' . fullname($sdata) . '</span>
                            </div>';
                $line[] = '<span> <label for="email">' . $sdata->email . '</lable></span>';
                $line[] = get_string('nodata', 'local_program');
                $data[] = $line;
            }
            $return = array(
                "recordsTotal" => $groupusers['groupuserscount'],
                "recordsFiltered" => $groupusers['groupuserscount'],
                "data" => $data,
            );
        }
        return $return;
    }



       /**
     * [get_sem_wise_program_table description]
     * @method viewgroupusers
     * @param  [type]             $cohortid   [description]
     * @param  [type]             $stable      [description]
     * @return [type]                          [description]
     */
     // get_sem_wise_program_table
    public function get_sem_wise_program_table($programid) {
        global $OUTPUT, $CFG, $DB, $PAGE;
        $table = new html_table();
        $table->align = array('left');
        $table->head = array('&#xa0;');
        $table->colclasses = array('');

        $programid = optional_param('bcid', '', PARAM_INT);
        $sql = "SELECT lpl.* FROM  {local_program_levels} lpl WHERE lpl.programid = $programid";
        $leveldata = $DB->get_records_sql($sql);
        $sql1 = "SELECT u.* FROM {user} u JOIN 
        {local_program_users} lpu ON u.id = lpu.userid WHERE lpu.programid = $programid";
        $userdata = $DB->get_records_sql($sql1);

        // Add role name headers.
        foreach ($leveldata as $targetdata) {
            $name = 's_' .$targetdata->id;
            $table->id = 'semwiseuserenrol';
            $countofenroledusers = 0;
            foreach($userdata as $data){
                $countofenroledusers += $DB->count_records_sql("SELECT count(id) FROM {local_program_level_users} WHERE userid = $data->id and levelid = $targetdata->id ");
            }
            if(count($userdata) === $countofenroledusers){
                 $checked = "checked";
            } else{
                 $checked = " ";
            }
            $semcompletionid = $DB->get_field('local_semesters_completions','id', [/*'userid' => $fromdata->id,*/ 'levelid' => $targetdata->id]);
            // $currdate = time();
            // $futuredate = strtotime("+7 day");

            // if($semcompletionid || ($targetdata->startdate > $futuredate) || ($targetdata->enddate < $currdate)){
                 $disabled = 'disabled';
            // } else{
            //     $disabled = '';
            // }
            if($semcompletionid){
                $color = 'style = "accent-color: #0E9700;"';
            } else {
                $color = '';
            }
            $table->head[0] = '<label>'.get_string('employee', 'local_program').'</label>';
            $table->head[] = /*'<input type = "checkbox"  '.$color.' class="'.$name.'" name="' . $name . '" value = "'.$name.'" '.$checked.' '.$disabled.' title="selectall"
            onclick="saveusersToDB('.$targetdata->id.','.$programid.')"/>'.*/$targetdata->level/*.'<input type = "checkbox"  '.$color.' class="'.$name.'" name="' . $name . '" value = "'.$name.'" '.$checked.' '.$disabled.' title="disableall"
            onclick="saveusersToDB('.$targetdata->id.','.$programid.')"/>'*/;
        }

        // Now the rest of the table.
        foreach ($userdata as $fromdata) {
            $row = array($fromdata->firstname.$fromdata->lastname);
            foreach ($leveldata as $targetdata) {
                // $checkboxid = $DB->get_field('local_program_level_users','checked', ['userid' => $fromdata->id, 'levelid' => $targetdata->id]);
                $semcompletionid = $DB->get_field('local_semesters_completions','id', ['userid' => $fromdata->id, 'levelid' => $targetdata->id]);
                $currdate = time();
                $futuredate = strtotime("+7 day"); 
                // if($semcompletionid || ($targetdata->startdate > $futuredate) || ($targetdata->enddate < $currdate)){
                     $disabled = 'disabled';
                // } else{
                //     $disabled = '';
                // }
                // if($checkboxid == 1){
                //     $checked = "checked";
                // } else{
                //     $checked = " ";
                // }
                if($semcompletionid){
                    $checked = "checked";
                    $color = 'style = "accent-color: #0E9700;"';
                } else {
                    $checked = "";
                    $color = '';
                }
                $name = 's_' . $fromdata->id . '_' . $targetdata->id;
                $tooltip = '';
                $row[] = '<input type="checkbox"  '.$color.' name="' . $name . '" id="' . $name .
                    '" value=" ' . $name . '" '.$checked.' '.$disabled.' class="'.$name.'"/>' .
                    '<label for="' . $name . '" class="accesshide">' . $tooltip . '</label>';

                // $row[] = '<input type="checkbox"  '.$color.' name="' . $name . '" id="' . $name .
                // '" value=" ' . $name . '" '.$checked.' '.$disabled.' class="'.$name.'" onclick="saveToDB('.$fromdata->id.','.$targetdata->id.', '.$programid.')"/>' .
                // '<label for="' . $name . '" class="accesshide">' . $tooltip . '</label>';

            }
            $table->data[] = $row;
        }
        return $table;
    }
}


