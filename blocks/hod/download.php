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
$id = $USER->id;
$res = $DB->get_record('user',array('id'=>$id));

if($res->levelofapprove == 0){
$classes = $DB->get_records_sql("SELECT * FROM {submissions} WHERE status = 0 AND (draft = 'f1' OR draft ='n1' OR draft ='nres1')");
$table = new html_table();
$table->id = "custom_table_class";
$table->head[] = get_string('SNo','block_hod');
$table->head[] = get_string('PIName','block_hod');
$table->head[] = get_string('ProjectTitle','block_hod');
$table->head[] = get_string('Status','block_hod');
$table->head[] = get_string('DepartmentName','block_hod');
$table->head[] = get_string('ApplicationType','block_hod');
$table->head[] = get_string('DateOfSubmission','block_hod');

$data = array();
$i = 1;
foreach ($classes as $class) {
        $username = $DB->get_record('user' , [ 'id' => $class->userid]);
    $type = $DB->get_record('applicationtable',array('id'=>$class->applicationtype));
    $departname=$DB->get_field('department','departmentname',array('id'=>$class->departmentname));
    if(!$class->comment){
        $class->comment = 'NA';
    }
    $line = array();
    $line[] = $i++;
    $line[] = $username->firstname;
    $line[] = ucfirst($class->title);
    $line[] = 'Waiting For Approval';
    $line[] = $departname;
    $line[] = $type->type;
    $line[] = $class->time;
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
}
else if($res->levelofapprove == 1){
    $classes = $DB->get_records_sql("SELECT * FROM {submissions} WHERE status = 1 and approveronestatus = 0");
$table = new html_table();
$table->id = "custom_table_class";
$table->head[] = get_string('SNo','block_hod');
$table->head[] = get_string('PIName','block_hod');
$table->head[] = get_string('ProjectTitle','block_hod');
$table->head[] = get_string('Status','block_hod');
$table->head[] = get_string('DepartmentName','block_hod');
$table->head[] = get_string('ApplicationType','block_hod');
$table->head[] = get_string('DateOfSubmission','block_hod');

$data = array();
$i = 1;
foreach ($classes as $class) {
        $username = $DB->get_record('user' , [ 'id' => $class->userid]);
    $type = $DB->get_record('applicationtable',array('id'=>$class->applicationtype));
    $departname=$DB->get_field('department','departmentname',array('id'=>$class->departmentname));
    if(!$class->comment){
        $class->comment = 'NA';
    }
    $line = array();
    $line[] = $i++;
    $line[] = $username->firstname;
    $line[] = ucfirst($class->title);
    $line[] = 'Waiting For Approval';
    $line[] = $departname;
    $line[] = $type->type;
    $line[] = $class->time;
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
}

else if($res->levelofapprove == 2){
    $classes = $DB->get_records_sql("SELECT * FROM {submissions} WHERE status = 1 and approveronestatus = 1 and approvertwostatus = 0");
$table = new html_table();
$table->id = "custom_table_class";
$table->head[] = get_string('SNo','block_hod');
$table->head[] = get_string('PIName','block_hod');
$table->head[] = get_string('ProjectTitle','block_hod');
$table->head[] = get_string('Status','block_hod');
$table->head[] = get_string('DepartmentName','block_hod');
$table->head[] = get_string('ApplicationType','block_hod');
$table->head[] = get_string('DateOfSubmission','block_hod');

$data = array();
$i = 1;
foreach ($classes as $class) {
        $username = $DB->get_record('user' , [ 'id' => $class->userid]);
    $type = $DB->get_record('applicationtable',array('id'=>$class->applicationtype));
    $departname=$DB->get_field('department','departmentname',array('id'=>$class->departmentname));
    if(!$class->comment){
        $class->comment = 'NA';
    }
    $line = array();
    $line[] = $i++;
    $line[] = $username->firstname;
    $line[] = ucfirst($class->title);
    $line[] = 'Waiting For Approval';
    $line[] = $departname;
    $line[] = $type->type;
    $line[] = $class->time;
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
}
