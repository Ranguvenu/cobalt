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
 * @package local_program
 */
defined('MOODLE_INTERNAL') || die();

function xmldb_local_program_upgrade($oldversion) {
    global $DB, $CFG;
    $dbman = $DB->get_manager();
    if ($oldversion < 2022051902.17) {
        $table = new xmldb_table('local_program');
        $field = new xmldb_field('department', XMLDB_TYPE_INTEGER, '10', null, null, null, 0, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('subdepartment', XMLDB_TYPE_INTEGER, '10', null, null, null, 0, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2022051902.17, 'local', 'program');
    }

    
    if ($oldversion < 2022051902.18) {
        $table = new xmldb_table('local_program_levels');
        $field = new xmldb_field('semister_credits', XMLDB_TYPE_CHAR, '200', null, null, null, null);

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2022051902.18, 'local', 'program');
    }

    if ($oldversion < 2022051902.20) {
        $table = new xmldb_table('local_program');
        $field = new xmldb_field('has_course_elective', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('course_elective', XMLDB_TYPE_CHAR, '200', null, null, null, null);
    
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2022051902.20, 'local', 'program');
    }
    /**
     * ODL-866: Ikram Code Starts..
     * Adding [coursetype] coloumn in local_program_level_courses to check the how many 
     * courses added in the programs are electives and mandatory.
     **/
    if ($oldversion < 2022051902.22) {
        $table = new xmldb_table('local_program_level_courses');
        $field = new xmldb_field('mandatory', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'courseid');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2022051902.22, 'local', 'program');
    }
    // Adding local program user enrolled courses.
    if ($oldversion < 2022051902.31) {
        $table = new xmldb_table('local_program_enrolments');
        
        // Adding fields to table norsu_strands.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('programid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('levelid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('mandatory', XMLDB_TYPE_INTEGER, '1', null, null, null, 0);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, null, null, 0);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, 0);

        // Adding keys to table.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        upgrade_plugin_savepoint(true, 2022051902.31, 'local', 'program');
    }
    if ($oldversion < 2022051902.32) {
        // added events to the plugin so updating..
        upgrade_plugin_savepoint(true, 2022051902.32, 'local', 'program');
    }
    // Ikram Code Ends Here..


    if ($oldversion < 2022051902.34) {
        $table = new xmldb_table('local_program');
        $field = new xmldb_field('duration', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'subdepartment');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('prerequisite', XMLDB_TYPE_CHAR, '200', null, null, null, null);
    
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2022051902.34, 'local', 'program');
    }
    
    if ($oldversion < 2022051902.36) {
        $table = new xmldb_table('local_program_levels');
        $field = new xmldb_field('has_course_elective', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('course_elective', XMLDB_TYPE_CHAR, '200', null, null, null, null);
    
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2022051902.36, 'local', 'program');
    }

    if ($oldversion < 2022051902.40) {
        $table = new xmldb_table('local_program');
        $field = new xmldb_field('program_startdate', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2022051902.40, 'local', 'program');
    }
    if ($oldversion < 2022051902.44) {
        $table = new xmldb_table('local_semesters_completions');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('programid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('levelid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timecompleted', XMLDB_TYPE_INTEGER, '10', null, null, null, 0);

        // Adding keys to table.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        upgrade_plugin_savepoint(true, 2022051902.44, 'local', 'program');
    }
    if ($oldversion < 2022051902.51) {
        $table = new xmldb_table('local_program');
        $field = new xmldb_field('prerequisite', XMLDB_TYPE_CHAR, '225', null, null, null, null);
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
            $field = new xmldb_field('prerequisite', XMLDB_TYPE_TEXT, 'big', null, null, null, null);
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2022051902.51, 'local', 'program');
    }
    if ($oldversion < 2022051902.52) {
        $table = new xmldb_table('local_program');
        $field = new xmldb_field('hasadmissions', XMLDB_TYPE_INTEGER, '1', null, null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2022051902.52, 'local', 'program');
    }
    if ($oldversion < 2022051902.53) {
        $table = new xmldb_table('local_program_levels');
        $field = new xmldb_field('enrolmethod', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2022051902.53, 'local', 'program');
    }
    if ($oldversion < 2022051902.55) {
        $table = new xmldb_table('local_program_levels');
        $field = new xmldb_field('enrolmethod', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2022051902.55, 'local', 'program');
    }

    // Program Course Enrollment
    if ($oldversion < 2022051902.56) {
        $time = time();
        // $notification_typeinfo = $DB->get_record('local_notification_type', array('shortname' => 'program_level_completion'));
        $parent_module = $DB->get_record('local_notification_type', array('shortname' => 'program', 'parent_module' => 0));
        if($parent_module){
            $notificationtypedata = array('name' => 'Program Course Enrollment', 'shortname' => 'program_course_enrollment', 'parent_module' => $parent_module->id, 'usercreated' => '2', 'timecreated' => $time, 'usermodified' => 2, 'timemodified' => NULL, 'pluginname' => 'program');
            $DB->insert_record('local_notification_type', $notificationtypedata );
        }
        upgrade_plugin_savepoint(true, 2022051902.56, 'local', 'program');
    }

    // Program Course Completion.
    if ($oldversion < 2022051902.56) {
        $time = time();
        // $notification_typeinfo = $DB->get_record('local_notification_type', array('shortname' => 'program_level_completion'));
        $parent_module = $DB->get_record('local_notification_type', array('shortname' => 'program', 'parent_module' => 0));
        if($parent_module){
            $notificationtypedata = array('name' => 'Program Course Completion', 'shortname' => 'program_course_completion', 'parent_module' => $parent_module->id, 'usercreated' => '2', 'timecreated' => $time, 'usermodified' => 2, 'timemodified' => NULL, 'pluginname' => 'program');
            $DB->insert_record('local_notification_type', $notificationtypedata );
        }
        upgrade_plugin_savepoint(true, 2022051902.56, 'local', 'program');
    }

    if ($oldversion < 2022051902.59) {
        $table = new xmldb_table('local_program_level_users');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('programid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('levelid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('checked', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        
        // Adding keys to table.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        upgrade_plugin_savepoint(true, 2022051902.59, 'local', 'program');
    }

    return true;
}
