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
use local_program\program;
function export_report($programid, $stable, $type) {
    global $DB, $CFG;
    require_once($CFG->dirroot . '/lib/excellib.class.php');
    $data = array();
    $matrix = array();
    $thead =array();
    if($type == 'programwise') {
        $filename = 'program Users.xls';
        $sql = "SELECT u.*, cu.attended_sessions, cu.hours, cu.completion_status, c.totalsessions,
            c.activesessions FROM {user} AS u
                     JOIN {local_program_users} AS cu ON cu.userid = u.id
                     JOIN {local_program} AS c ON c.id = cu.programid
                    WHERE c.id = {$programid} AND u.confirmed = 1 AND u.suspended = 0 AND u.deleted = 0 AND u.id > 2 ORDER BY id ASC ";
        $programusers = $DB->get_records_sql($sql);
        $table = new html_table();
        if (!empty($programusers)) {
            foreach ($programusers as $sdata) {
               $programname = $DB->get_field('local_program', 'name', array('id'=>$programid));
                $line = array();
                $line[] = fullname($sdata);
                $line[] = $programname;
                $line[] = $sdata->email;
                $total_levels = $DB->count_records('local_program_levels',  array('programid' => $programid));

                // $currdate = time();
                // $completed_levels = $DB->count_records_sql("SELECT COUNT(level) FROM {local_program_levels} WHERE programid = $programid AND enddate <= $currdate AND enddate <> 0");
                $total_levels_data = $DB->get_records('local_program_levels',  array('programid' => $programid));
                $completedlevelscount = 0;
                foreach ($total_levels_data as $value) {
                    $completed_levels = $DB->record_exists('local_semesters_completions', array('levelid' => $value->id, 'userid' => $sdata->id));
                    if($completed_levels){
                        $completedlevelscount++;
                    }
                }
                $levelscompletedcount = $completedlevelscount;
                $line[] = $levelscompletedcount.'/'.$total_levels;

                 $compltstatus = $DB->get_record('local_programcompletions', array('programid' => $programid, 'userid' => $sdata->id));
                    if ($compltstatus->completionstatus == 1) {
                        $line[] = get_string('completed', 'local_program');
                    }else{
                        $line[] = get_string('levelinprogress', 'local_program');

                    }

                $data[] = $line;
            }
            $table->data = $data;
        }
        $table->head = array(get_string('employee', 'local_program'), get_string('program_name', 'local_program'), get_string('email'),get_string('nooflevels', 'local_program'), get_string('status'));
    } else if($type == 'coursewise') {
        $filename = 'User-wise course-enrolled report.xls';
        $sql = "SELECT u.* FROM {user} AS u
                     JOIN {local_program_users} AS cu ON cu.userid = u.id
                     JOIN {local_program} AS c ON c.id = cu.programid
                    WHERE c.id = {$programid} AND u.confirmed = 1 AND u.suspended = 0 AND u.deleted = 0 AND u.id > 2 ORDER BY id ASC";
        $programusers = $DB->get_records_sql($sql);
        $table = new html_table();
        if (!empty($programusers)) {
            foreach ($programusers as $sdata) {
                $params = [];
                $sql = "SELECT c.id, concat(u.firstname, u.lastname) as fullname,
                            u.email, p.name as programname, c.fullname as course
                         FROM {local_program_users} as lpu
                         JOIN {user} as u ON u.id = lpu.userid
                         JOIN {local_program} as p ON p.id = lpu.programid
                         JOIN {local_program_level_courses} as lplc
                              ON lplc.programid = lpu.programid
                         JOIN {course} as c ON c.id = lplc.courseid
                        WHERE p.id = :programid AND u.id = :userid";
                $params['programid'] = $programid;
                $params['userid'] = $sdata->id;
                $sessionenrolledusers = $DB->get_records_sql($sql, $params);
                if(!empty($sessionenrolledusers)){
                    foreach ($sessionenrolledusers as $sessionenrolleduser) {
                        $programname = $sessionenrolleduser->programname;

                        $coursename = $sessionenrolleduser->course;
                        $sessionname = $sessionenrolleduser->sessionname;
                        $line = array();
                        $line[] = fullname($sdata);
                        $line[] = $sdata->email;
                        $line[] = $programname;
                        $line[] = $coursename;
                        $data[] = $line;
                    }
                } else {
                    $programname = $DB->get_field('local_program', 'name', array('id'=>$programid));
                    $line1 = array();
                    $line1[] = fullname($sdata);
                    $line1[] = $sdata->email;
                    $line1[] = $programname;
                    $line1[] = '--';
                    $data[] = $line1;
                }
            }
            $table->data = $data;
        }
        $table->head = array(get_string('employee', 'local_program'), get_string('email'), get_string('program_name', 'local_program'),get_string('course', 'local_program'));
    }
     // semesterwise starts 
    else if($type == 'semesterwise') {
        $filename = 'User-wise semesters-enrolled report.xls';
        $table = new html_table();
        $programid = optional_param('bcid', '', PARAM_INT);
        $programname = $DB->get_field('local_program', 'name', ['id' => $programid]);
        $leveldata_sql = "SELECT lpl.* FROM  {local_program_levels} lpl WHERE lpl.programid = $programid";
        $leveldata = $DB->get_records_sql($leveldata_sql);
        $userdata_sql = "SELECT u.* FROM {user} u JOIN 
        {local_program_users} lpu ON u.id = lpu.userid WHERE lpu.programid = $programid";
        $userdata = $DB->get_records_sql($userdata_sql);

        // Add role name headers.
        foreach ($leveldata as $targetdata) {
            $name = 's_' .$targetdata->id;
            foreach($userdata as $data){
                $countofenroledusers += $DB->count_records_sql("SELECT count(id) FROM {local_program_level_users} WHERE userid = $data->id and levelid = $targetdata->id ");
            }
            $semcompletionid = $DB->get_field('local_semesters_completions','id', ['userid' => $fromdata->id, 'levelid' => $targetdata->id]);
            $table->head[0] = '<label>'.get_string('employee', 'local_program').'</label>';
            $table->head[2] = '<label>'.get_string('email', 'local_program').'</label>';
            $table->head[1] = '<label>'.get_string('program_name', 'local_program').'</label>';
            $table->head[]  = '<label>'.$targetdata->level.'</label>';
        }
        // Now the rest of the table.
        foreach ($userdata as $fromdata) {
            $row = array($fromdata->firstname.$fromdata->lastname);
            foreach ($leveldata as $targetdata) {
                $semcompletionid = $DB->get_field('local_semesters_completions','id', ['userid' => $fromdata->id, 'levelid' => $targetdata->id]);

                $semesterstatusid = $DB->get_field('local_program_levels','active', ['id' => $targetdata->id]);
                if($semcompletionid && $semesterstatusid == 1){
                    $semesterstatus = 'Completed';
                } else if(empty($semcompletionid) && $semesterstatusid == 0){
                    $semesterstatus = 'Not yet started';
                } else if(empty($semcompletionid) && $semesterstatusid == 1){
                    $semesterstatus = 'Inprogress';
                } else if($semcompletionid && $semesterstatusid == 0){
                    $semesterstatus = 'Completed';
                }
                $row[2] = $fromdata->email;
                $row[1] = $programname;
                $row[] = '<label>'.$semesterstatus.'</label>';
            }

            $table->data[] = $row;
        }

    }
     // semesterwise ends 

    if (!empty($table->head)) {
        foreach ($table->head as $key => $heading) {
            $matrix[0][$key] = str_replace("\n", ' ', htmlspecialchars_decode(\local_costcenter\lib::strip_tags_custom(nl2br($heading))));
        }
    }

    if (!empty($table->data)) {
        foreach ($table->data as $rkey => $row) {
            foreach ($row as $key => $item) {
                $matrix[$rkey + 1][$key] = str_replace("\n", ' ', htmlspecialchars_decode(\local_costcenter\lib::strip_tags_custom(nl2br($item))));
            }
        }
    }
    $downloadfilename = clean_filename($filename);
    /// Creating a workbook
    $workbook = new MoodleExcelWorkbook("-");
    /// Sending HTTP headers
    $workbook->send($downloadfilename);
    /// Adding the worksheet
    $myxls = $workbook->add_worksheet($filename);
    foreach ($matrix as $ri => $col) {
        foreach ($col as $ci => $cv) {
            //Formatting by sowmya
            $format = array('border'=>1);
            if($ri == 0){
                $format['bold'] = 1;
                $format['bg_color'] = '#f0a654';
                $format['color'] = '#FFFFFF';
            }

            if(is_numeric($cv)){
                $format['align'] = 'center';
                $myxls->write_number($ri, $ci, $cv, $format);
            } else {
                $myxls->write_string($ri, $ci, $cv, $format);
            }
        }
    }
    $workbook->close();
    exit;
}
