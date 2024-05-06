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
 *
 * @package    local_admissions
 * @copyright  2022 eAbyas Info Solutions
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_admissions\output;
defined('MOODLE_INTERNAL') || die();
use plugin_renderer_base;
use context_system;
use moodle_url;
class renderer extends plugin_renderer_base {

	public function programsdata(array $programids, array $levelids) {
        global $DB, $PAGE, $CFG;
        $systemcontext = context_system::instance();
        if(!count($programids) > 0){
            return false;
        } else {
            if (count($levelids) > 0) {
                $progarrayvals = implode(',', $programids);
                $levarrayvals = implode(',', $levelids);
                $programssql = "SELECT * FROM {local_program} WHERE id IN ($progarrayvals) ORDER BY id DESC";
                $programsdataobj = $DB->get_records_sql($programssql);

                $datares = array();
                
                foreach ($programsdataobj as $program) {
                    $data = array();
                    $data['context'] = $systemcontext->id;
                    $data['id'] = $program->id;
                    $data['programname'] = $program->name;
                    $data['fulldescription'] = \local_costcenter\lib::strip_tags_custom(html_entity_decode($program->description));
                    $description = \local_costcenter\lib::strip_tags_custom(html_entity_decode($program->description));
                    if (strlen($description) >= 700) {
                        $description = substr($description, 0, 700).'...';
                    }
                    $prerequisite = \local_costcenter\lib::strip_tags_custom(html_entity_decode($program->prerequisite));
                    if ($prerequisite) {
                        $data['prerequisite'] = $prerequisite;
                    }
                    $data['description'] = $description;
                    $progstartdtsql = "SELECT MIN(startdate) as startdate FROM {local_program_levels} WHERE programid = {$program->id} AND startdate <> 0";
                    $progstartdate = $DB->get_field_sql($progstartdtsql);
                    if ($progstartdate == '1970-01-01' || $progstartdate == 0) {
                        $progstartdate = 'NA';
                    } else {
                        $progstartdate = date('d-M-Y', $progstartdate);
                    }
                    $data['startdate'] = $progstartdate;
                    $progenddtsql = "SELECT MAX(enddate) as enddate FROM {local_program_levels} WHERE programid = {$program->id}";
                    $progenddate = $DB->get_field_sql($progenddtsql);
                    if ($progenddate == '1970-01-01' || $progenddate == 0) {
                        $progenddate = 'NA';
                    } else {
                        $progenddate = date('d-M-Y', $progenddate);
                    }
                    $data['enddate'] = $progenddate;
                    $levelscount = $DB->count_records('local_program_levels', array('programid' => $program->id));
                    if ($levelscount > 0) {
                        $semcount = $levelscount;
                    } else {
                        $semcount = 0;
                    }
                    $data['nsems'] = $semcount;
                    $data['ncourses'] = $program->totalcourses;
                    $filesql = "SELECT * FROM {files} WHERE contextid = :contextid AND itemid = :itemid AND filesize > 0";
                    $filesdata = $DB->get_record_sql($filesql, ['contextid' => $systemcontext->id, 'itemid' => $program->programlogo]);
                    $url = $CFG->wwwroot.'/local/admissions/pix/default.jpg';
                    $fs = get_file_storage();
                    if ($files = $fs->get_area_files($systemcontext->id, $filesdata->component, $filesdata->filearea, $filesdata->itemid, $filesdata->sortorder, false)) {
                        foreach ($files as $file) {
                            $fileurl = moodle_url::make_pluginfile_url($file->get_contextid(),
                            $file->get_component(),
                            $file->get_filearea(),
                            $file->get_itemid(),
                            $file->get_filepath(),
                            $file->get_filename());
                            // $downloadurl = $fileurl->get_port() ? $fileurl->get_scheme() . '://' . $fileurl->get_host() . $fileurl->get_path() . ':' . $fileurl->get_port() : $fileurl->get_scheme() . '://' . $fileurl->get_host() . $fileurl->get_path();
                            $url =$fileurl;
                        }
                    }
                    $data['imageurl'] = $url;

                    $certificate = $DB->get_field('tool_certificate_templates', 'name', array('id' => $program->certificateid));
                    if ($certificate) {
                        $data['certificate'] = $certificate;
                    } else {
                        $data['certificate'] = 'NA';
                    }
                    $levelnamessql = "SELECT bcl.id as levelid, bcl.semister_credits as semcredits,
                                        bc.id as programid, bcl.level, bcl.position
                                        FROM {local_program_levels} bcl
                                        JOIN {local_program} bc ON bc.id = bcl.programid
                                        WHERE bc.id = :programid";
                    $levelnames = $DB->get_records_sql($levelnamessql, ['programid' => $program->id]);
                    $data['levels'] = array_values($levelnames);
                    $admsnenddate = $program->enddate;
                    if ($admsnenddate == '1970-01-01' || $admsnenddate == 0) {
                        $admsnenddate = 'NA';
                    } else {
                        $admsnenddate = date('d-M-Y', $admsnenddate);
                    }
                    $data['admissionenddate'] = $admsnenddate;
                    $coursessql = "SELECT bclc.id AS bclevelcourseid, lpl.level,
                                    bclc.programid, bclc.levelid, c.fullname AS course, c.*
                                    FROM {local_program_level_courses} bclc
                                    JOIN {local_program_levels} lpl ON lpl.id = bclc.levelid
                                    JOIN {course} c ON c.id = bclc.courseid
                                    WHERE bclc.programid = :programid
                                    AND bclc.levelid IN($levarrayvals)";
                    $courses = $DB->get_records_sql($coursessql, ['programid' => $program->id]);
                    $teachers = array();
                    if ($courses) {
                        foreach ($courses as $course) {
                            $coursecontext = $DB->get_field('context', 'id', array('instanceid' => $course->id, 'contextlevel' => 50));
                            $teachers[] = $DB->get_records_sql("SELECT u.*, concat(u.firstname, u.lastname) as fullname, u.username as username, plc.programid as programid,
                                pl.id as levelid
                                FROM {user} u
                                JOIN {role_assignments} ra ON ra.userid = u.id
                                JOIN {role} r ON r.id = ra.roleid AND r.shortname = 'editingteacher'
                                JOIN {context} ctx ON ctx.id = ra.contextid
                                JOIN {course} c ON c.id = ctx.instanceid
                                JOIN {local_program_level_courses} plc ON plc.courseid=c.id
                                JOIN {local_program_levels} pl ON pl.id = plc.levelid 
                                AND pl.programid = plc.programid
                                WHERE plc.programid = :programid", ['programid' => $program->id]);
                            break;
                        }
                        if ($teachers) {
                            $singledimteachers = call_user_func_array('array_merge', $teachers);
                            foreach ($singledimteachers as $teacher) {
                                $userpicture = new \user_picture($teacher, array('size' => 60, 'class' => 'userpic', 'link' => false));
                                $userpicture = $userpicture->get_url($PAGE);
                                $teacher->teacherurl = $userpicture;
                            }
                            $data['faculty'] = array_values($singledimteachers);
                        } else {
                            $data['faculty'] = false;
                        }
                    }

                    $datares[] = $data;
                }
                return $datares;
            } else {
                return false;
            }
        }
    }

    public function admission_programs() {
        global $DB, $CFG;
        $systemcontext = context_system::instance();
        $currtime = date('d-M-Y', time());
        $currenttime = strtotime($currtime);
        $programssql = "SELECT lp.* FROM {local_program_levels} as lpl
                    JOIN {local_program} as lp ON lp.id = lpl.programid
                    WHERE lp.enddate >= {$currenttime} AND lp.hasadmissions = 1";
        $programs = $DB->get_records_sql($programssql);
        if (count($programs) > 0) {
            foreach ($programs as $key => $value) {
                $certificate = $DB->get_field('tool_certificate_templates', 'name', array('id' => $value->certificateid));
                if ($certificate) {
                    $value->certificatename = $certificate;
                }
                if (strlen($certificate) > 7) {
                    $certificate = substr($certificate, 0, 7).'...';
                }
                if ($certificate) {
                    $value->certificate = $certificate;
                } else {
                    $value->certificate = 'N/A';
                }
                $filesql = "SELECT * FROM {files} WHERE contextid = :contextid AND itemid = :itemid AND filesize > 0";
                $filesdata = $DB->get_record_sql($filesql, ['contextid' => $systemcontext->id, 'itemid' => $value->programlogo]);
                $url = $CFG->wwwroot.'/local/admissions/pix/default.jpg';
                $fs = get_file_storage();
                if ($files = $fs->get_area_files($systemcontext->id, $filesdata->component, $filesdata->filearea, $filesdata->itemid, $filesdata->sortorder, false)) {
                    foreach ($files as $file) {
                        $fileurl = moodle_url::make_pluginfile_url($file->get_contextid(),
                        $file->get_component(),
                        $file->get_filearea(),
                        $file->get_itemid(),
                        $file->get_filepath(),
                        $file->get_filename());
                        $url = $fileurl;
                    }
                }
                $value->imageurl = $url;
                $startdtsql = "SELECT MIN(startdate) as startdate FROM {local_program_levels} WHERE programid = {$value->id} AND startdate <> 0";
                $startdt = $DB->get_field_sql($startdtsql);
                if ($startdt == '1970-01-01' || $startdt == 0) {
                    $startdt = 'N/A';
                } else {
                    $startdt = date('d-M-Y', $startdt);
                }
                $value->progstartdate = $startdt;

                $enddtsql = "SELECT MAX(enddate) as enddate FROM {local_program_levels} WHERE programid = {$value->id}";
                $enddt = $DB->get_field_sql($enddtsql);
                if ($enddt == '1970-01-01' || $enddt == 0) {
                    $enddt = 'N/A';
                } else {
                    $enddt = date('d-M-Y', $enddt);
                }
                $value->progenddate = $enddt;
                $levelscount = $DB->count_records('local_program_levels', array('programid' => $value->id));
                if ($levelscount > 0) {
                    $value->levelscount = $levelscount;
                } else {
                    $value->levelscount = 0;
                }
                $admsnenddate = date('d-M-Y', $value->enddate);
                if (empty($admsnenddate)) {
                    $admissionenddate = "N/A";
                } else {
                    $admissionenddate = $admsnenddate;
                }
                $value->enddate = $admissionenddate;
            }
            return $programs;
        } else {
            return false;
        }
    }

    public function admission_prog_tabsdata(int $programid) {
        global $DB, $PAGE, $OUTPUT;
        $programsems = $DB->get_records('local_program_levels', array('programid' => $programid));
        return $programsems;
    }

    public function program_faculty(int $programid) {
        global $DB;
        $teacherssql = "SELECT u.*, concat(u.firstname,' ', u.lastname) as fullname, u.username as username, plc.programid as programid, pl.id as levelid
                        FROM {user} u
                        JOIN {role_assignments} ra ON ra.userid = u.id
                        JOIN {role} r ON r.id = ra.roleid AND r.shortname = 'editingteacher'
                        JOIN {context} ctx ON ctx.id = ra.contextid
                        JOIN {course} c ON c.id = ctx.instanceid
                        JOIN {local_program_level_courses} plc ON plc.courseid=c.id
                        JOIN {local_program_levels} pl ON pl.id = plc.levelid 
                        AND pl.programid = plc.programid
                        WHERE plc.programid = :programid";
        $teachers = $DB->get_records_sql($teacherssql, ['programid' => $programid]);

        return $teachers;
    }

    public function adminprogramsdata(array $programids, array $levelids) {
        global $DB, $PAGE, $CFG;
        $systemcontext = context_system::instance();
        if(!count($programids) > 0){
            return false;
        } else {
            if (count($levelids) > 0) {
                $progarrayvals = implode(',', $programids);
                $levarrayvals = implode(',', $levelids);
                $programssql = "SELECT * FROM {local_program} WHERE id IN ($progarrayvals) ORDER BY id DESC";
                $programsdataobj = $DB->get_records_sql($programssql);
                $countprograminfo = $DB->get_record_sql("SELECT count(id) as programcount FROM {local_program} WHERE id IN ($progarrayvals)");

                $datares = array();
                
                foreach ($programsdataobj as $program) {
                    $data = array();
                    $data['context'] = $systemcontext->id;
                    $data['id'] = $program->id;
                    $data['programname'] = $program->name;
                    $data['description'] = $program->description;
                    $data['cohortname'] = $DB->get_field('cohort', 'name', array('id'=>$program->batchid));
                    $progstartdtsql = "SELECT MIN(startdate) as startdate FROM {local_program_levels} WHERE programid = {$program->id} AND startdate <> 0";

                    $progsemactivesql = "SELECT id FROM {local_program_levels} WHERE programid = {$program->id} AND active = 1 ";
                    $progsemactivesql = $DB->get_field_sql($progsemactivesql);
                    
                    $progstartdate = $DB->get_field_sql($progstartdtsql);
                    if ($progstartdate == '1970-01-01' || $progstartdate == 0) {
                        $progstartdate = 'NA';
                    } else {
                        $progstartdate = date('d-M-Y', $progstartdate);
                    }
                    $data['startdate'] = $progstartdate;
                    $data['activesem'] = $progsemactivesql;
                    $progenddtsql = "SELECT MAX(enddate) as enddate FROM {local_program_levels} WHERE programid = {$program->id}";
                    $progenddate = $DB->get_field_sql($progenddtsql);
                    if ($progenddate == '1970-01-01' || $progenddate == 0) {
                        $progenddate = 'NA';
                    } else {
                        $progenddate = date('d-M-Y', $progenddate);
                    }
                    $data['enddate'] = $progenddate;
                    $levelscount = $DB->count_records('local_program_levels', array('programid' => $program->id));
                    if ($levelscount > 0) {
                        $semcount = $levelscount;
                    } else {
                        $semcount = 0;
                    }
                    $data['nsems'] = $semcount;
                    $data['ncourses'] = $program->totalcourses;
                    $filesql = "SELECT * FROM {files} WHERE contextid = :contextid AND itemid = :itemid AND filesize > 0";
                    $filesdata = $DB->get_record_sql($filesql, ['contextid' => $systemcontext->id, 'itemid' => $program->programlogo]);
                    $url = '<img src="'.$CFG->wwwroot.'/local/admissions/pix/default.jpg'.'" height="150" width="200" alt="default.jpg">';
                    $fs = get_file_storage();
                    if ($files = $fs->get_area_files($systemcontext->id, $filesdata->component, $filesdata->filearea, $filesdata->itemid, $filesdata->sortorder, false)) {
                        foreach ($files as $file) {
                            $fileurl = moodle_url::make_pluginfile_url($file->get_contextid(),
                            $file->get_component(),
                            $file->get_filearea(),
                            $file->get_itemid(),
                            $file->get_filepath(),
                            $file->get_filename());
                            // $downloadurl = $fileurl->get_port() ? $fileurl->get_scheme() . '://' . $fileurl->get_host() . $fileurl->get_path() . ':' . $fileurl->get_port() : $fileurl->get_scheme() . '://' . $fileurl->get_host() . $fileurl->get_path();
                            $url = '<img src="' . $fileurl . '" height="150" width="200" alt="'.$file->get_filename().'">';
                        }
                    }
                    $data['imageurl'] = $url;

                    $certificate = $DB->get_field('tool_certificate_templates', 'name', array('id' => $program->certificateid));
                    if ($certificate) {
                        $data['certificate'] = $certificate;
                    } else {
                        $data['certificate'] = 'NA';
                    }
                    $levelnamessql = "SELECT bcl.id as levelid, bcl.semister_credits as semcredits,
                                        bc.id as programid, bcl.level, bcl.position
                                        FROM {local_program_levels} bcl
                                        JOIN {local_program} bc ON bc.id = bcl.programid
                                        WHERE bc.id = :programid";
                    $levelnames = $DB->get_records_sql($levelnamessql, ['programid' => $program->id]);
                    $data['levels'] = array_values($levelnames);
                    $admsnenddate = $program->enddate;
                    if ($admsnenddate == '1970-01-01' || $admsnenddate == 0) {
                        $admsnenddate = 'NA';
                    } else {
                        $admsnenddate = date('d-M-Y', $admsnenddate);
                    }
                    $data['admissionenddate'] = $admsnenddate;
                    $coursessql = "SELECT bclc.id AS bclevelcourseid, lpl.level,
                                    bclc.programid, bclc.levelid, c.fullname AS course, c.*
                                    FROM {local_program_level_courses} bclc
                                    JOIN {local_program_levels} lpl ON lpl.id = bclc.levelid
                                    JOIN {course} c ON c.id = bclc.courseid
                                    WHERE bclc.programid = :programid
                                    AND bclc.levelid IN($levarrayvals)";
                    $courses = $DB->get_records_sql($coursessql, ['programid' => $program->id]);
                    $teachers = array();
                    if ($courses) {
                        foreach ($courses as $course) {
                            $coursecontext = $DB->get_field('context', 'id', array('instanceid' => $course->id, 'contextlevel' => 50));
                            $teachers[] = $DB->get_records_sql("SELECT u.*, concat(u.firstname, u.lastname) as fullname, u.username as username, plc.programid as programid,
                                pl.id as levelid
                                FROM {user} u
                                JOIN {role_assignments} ra ON ra.userid = u.id
                                JOIN {role} r ON r.id = ra.roleid AND r.shortname = 'editingteacher'
                                JOIN {context} ctx ON ctx.id = ra.contextid
                                JOIN {course} c ON c.id = ctx.instanceid
                                JOIN {local_program_level_courses} plc ON plc.courseid=c.id
                                JOIN {local_program_levels} pl ON pl.id = plc.levelid 
                                AND pl.programid = plc.programid
                                WHERE plc.programid = :programid", ['programid' => $program->id]);
                            break;
                        }
                        if ($teachers) {
                            $singledimteachers = call_user_func_array('array_merge', $teachers);
                            foreach ($singledimteachers as $teacher) {
                                $userpicture = new \user_picture($teacher, array('size' => 60, 'class' => 'userpic', 'link' => false));
                                $userpicture = $userpicture->get_url($PAGE);
                                $teacher->teacherurl = $userpicture;
                            }
                            $data['faculty'] = array_values($singledimteachers);
                        } else {
                            $data['faculty'] = false;
                        }
                    } 
                    $data['countprograminfo'] = $countprograminfo;
                    $isactivesql = "SELECT active FROM {local_program_levels} WHERE programid = :programid";
                    // $isactive = $DB->get_fieldset_sql($isactivesql, array('programid' => $program->id));
                    // if (in_array(1, $isactive)) {
                    //     $allow = true;
                    // } else {
                    //     $allow = false;
                    // }
                    $data['admintoapply'] = true;
                    $datares[] = $data;
                }
                return $datares;
            } else {
                return false;
            }
        }
    }
}
