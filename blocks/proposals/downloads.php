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
 * Version details.
 *
 * @package    block_proposals
 * @copyright  moodle
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('AJAX_SCRIPT', true);
require_once(__DIR__ . '/../../config.php');
global $CFG , $USER , $PAGE , $OUTPUT , $DB;
$systemcontext = context_system::instance();
require_login();
$id = optional_param('id' , null , PARAM_INT);

// $id = $USER->id;
$classes = $DB->get_records_sql("SELECT * FROM {submissions} WHERE id = $id");
 
$table = new html_table();
$table->id = "custom_table_class";
$table->head[] = 'S.No';
$table->head[] = 'PI Name';
$table->head[] = 'Project Title';
$table->head[] = 'Status';
$table->head[] = 'Department Name';
foreach ($classes as $class) { 
    if($class->status == 3){
        $table->head[] = 'Date Of Revision';
    }else if($class->status == 0 && $class->draft == 'n1' || $class->draft == 'f1'){
        $table->head[] = 'Date Of Submission';
    }else if($class->status == 0 && $class->draft == 'n0' || $class->draft == 'f0'){
        $table->head[] = 'Date Of Saved in draft';
    }

}
$table->head[] = 'Application Type';
$table->head[] = 'Project Code';
$table->head[] = 'Comment';
$data = array();
$i = 1;
// print_object($classes);exit;
foreach ($classes as $class) {
    $username = $DB->get_record('user' , [ 'id' => $class->userid]);

    if ($class->status == 0 && $class->draft == 'n0' || $class->draft == 'f0') {
        $status = "Saved In Draft";
    }
    if ($class->status == 0 && $class->draft == 'n1' || $class->draft == 'f1') {
        $status = "Waiting For Approval";
    }

    if ($class->status == 1) {
        $status = "Approved";
    }
    if ($class->status == 2) {
        $status = "Rejected";
    }
    if ($class->status == 3) {
        $status = "Under revision";
    }

    if($class->comment == null){
        $class->comment = 'N/A'; 
    }
    $type = $DB->get_record('applicationtable',array('id'=>$class->applicationtype));
    $line = array();
    $line[] = $i++;
    $line[] = $username->firstname;
    $line[] = ucfirst($class->title);
    $line[] = $status;
    $line[] = $class->departmentname;
    $line[] = $class->time;
    $line[] = $type->type;
    $line[] = $type->code;
    $line[] = $class->comment;
    $data[] = $line;
}
$table->id = "custom_table_class";
$table->data = $data;
 require_once($CFG->libdir . '/csvlib.class.php');
    $matrix = array();
    $filename = 'submission_report';
if (!empty($table->head)) {
        $countcols = count($table->head);
        $keys = array_keys($table->head);
        $lastkey = end($keys);
    foreach ($table->head as $key => $heading) {
            $matrix[0][$key] = str_replace("\n", ' ', htmlspecialchars_decode(strip_tags(nl2br($heading))));
    }
}
if (!empty($table->data)) {
    foreach ($table->data as $rkey => $row) {
        foreach ($row as $key => $item) {
                $matrix[$rkey + 1][$key] = str_replace("\n", ' ', htmlspecialchars_decode(strip_tags(nl2br($item))));
        }
    }
}
    $csvexport = new csv_export_writer();
    $csvexport->set_filename($filename);
foreach ($matrix as $ri => $col) {
        $csvexport->add_data($col);
}
    $csvexport->download_file();
    exit; 
