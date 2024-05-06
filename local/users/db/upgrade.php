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
defined('MOODLE_INTERNAL') || die();
function xmldb_local_users_upgrade($oldversion) {
	global $DB, $CFG;
	$dbman = $DB->get_manager();
	
	if($oldversion < 2020032601.7){
		
    $time = time();
    $initcontent = array('name' => 'User','shortname' => 'user','parent_module' => '0','usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'users');
    $parentid = $DB->get_field('local_notification_type', 'id', array('shortname' => 'user'));
    if(!$parentid){
        $parentid = $DB->insert_record('local_notification_type', $initcontent);
    }
    $notification_type_data = array(
        array('name' => 'External user register','shortname' => 'external_user_register','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'users')
    );
    foreach($notification_type_data as $notification_type){
        unset($notification_type['timecreated']);
        if(!$DB->record_exists('local_notification_type',  $notification_type)){
            $notification_type['timecreated'] = $time;
            $DB->insert_record('local_notification_type', $notification_type);
        }
    }
    $strings = array(
        array('name' => '[user_name]','module' => 'user','usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL),
        array('name' => '[user_email]','module' => 'user','usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL),
    );
    foreach($strings as $string){
        unset($string['timecreated']);
        if(!$DB->record_exists('local_notification_strings', $string)){
            $string_obj = (object)$string;
            $string_obj->timecreated = $time;
            $DB->insert_record('local_notification_strings', $string_obj);
        }
    }
		upgrade_plugin_savepoint(true,2020032601.7, 'local', 'user');
	}

    if ($oldversion < 2020032602.08) {
    $table = new xmldb_table('user');
    $field = new xmldb_field('rollno', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
    if (!$dbman->field_exists($table, $field)) {
        $dbman->add_field($table, $field);
    }
    upgrade_plugin_savepoint(true, 2020032602.08, '', 'user');
}

	return true;
}
