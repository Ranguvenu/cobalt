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
 * @package local_curriculum
 */

function xmldb_local_curriculum_upgrade($oldversion) {
    global $DB, $CFG;
    $dbman = $DB->get_manager();
    if ($oldversion < 20191100010) {
        $table = new xmldb_table('local_curriculum');
        $field = new xmldb_field('open_departmentid', XMLDB_TYPE_INTEGER, '10', null, null, null, 0, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 20191100010, 'local', 'curriculum');
    }

     if ($oldversion < 20191100011) {
        $table = new xmldb_table('local_curriculum');
        $field = new xmldb_field('open_subdepartment', XMLDB_TYPE_INTEGER, '10', null, null, null, 0, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 20191100011, 'local', 'curriculum');
    }

     if ($oldversion < 20191100012) {
        $table = new xmldb_table('local_cc_semester_courses');
        $field = new xmldb_field('open_departmentid', XMLDB_TYPE_INTEGER, '10', null, null, null, 0, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 20191100012, 'local', 'curriculum');
    }
 
    return true;
}
