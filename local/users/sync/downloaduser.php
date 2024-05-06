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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
* @package  local_users
* @copyright Anil
* @license  http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/
require_once(__DIR__ . '/../../../config.php');
require_login();
$PAGE->set_context(\context_system::instance());

define('AJAX_SCRIPT', true);
global $CFG,$PAGE,$OUTPUT,$USER,$DB;
$systemcontext = context_system::instance();
$classessql = "SELECT u.username, u.firstname,u.lastname,u.email,u.open_costcenterid ,u.city,u.phone1
   FROM {user} u 
   WHERE u.id > 2 AND u.deleted = 0 AND u.open_type = 1";
if (is_siteadmin() || has_capability('local/costcenter:manage_multiorganizations', $systemcontext)) {
   $classessql .= " AND u.suspended = 0 ";
} else if (!is_siteadmin() && has_capability('local/costcenter:manage_ownorganization', $systemcontext)) {
   $classessql .= " AND u.suspended = 0 AND u.open_costcenterid = $USER->open_costcenterid";
} else if (!is_siteadmin() && !has_capability('local/costcenter:manage_ownorganization', $systemcontext) && has_capability('local/costcenter:manage_owndepartments', $systemcontext)) {
   $classessql .= " AND u.suspended = 0 AND u.open_costcenterid = $USER->open_costcenterid AND u.open_departmentid = $USER->open_departmentid";
} else {
   $classessql .= " AND u.suspended = 0";
}
$labelstring = get_config('local_costcenter');
$classes = $DB->get_records_sql($classessql);
$table = new html_table();
$table->id = "user_head";
$table->head[] = 'firstname';
$table->head[] = 'lastname';
$table->head[] = 'email';
$table->head[] = 'city';
$table->head[] = strtolower($labelstring->firstlevel);
$table->head[] = 'phone';

$data = array();
foreach ($classes as $class) {
   $line = array();
   $line[] = $class->firstname;
   $line[] = $class->lastname;
   $line[] = $class->email;
   $line[] = $class->city;
   $line[] = $DB->get_field('local_costcenter','fullname',array('id' =>$class->open_costcenterid));
   $line[] = $class->phone1;
   $data[] = $line;
}

$table->id = "student_data";
$table->data = $data;
require_once($CFG->libdir.'/csvlib.class.php');
   $matrix = array();
   $filename = 'local_user_data';
   if (!empty($table->head)) {
       $countcols = count($table->head);
       $keys = array_keys($table->head);
       $lastkey = end($keys);
       foreach ($table->head as $key => $heading) {
          $matrix[0][$key] = str_replace("\n",' ', htmlspecialchars_decode(strip_tags(nl2br($heading))));
       }
   }
   if (!empty($table->data)) {
      foreach ($table->data as $rkey => $row) {
         foreach ($row as $key => $item) {
               $matrix[$rkey + 1][$key] = str_replace("\n",' ', htmlspecialchars_decode(strip_tags(nl2br($item))));
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

