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
 * @package local_groups
 */

function xmldb_local_groups_upgrade($oldversion) {
    global $DB, $CFG;
    $dbman = $DB->get_manager();
    if ($oldversion < 2022111700) {
        $table = new xmldb_table('local_groups');
        $field = new xmldb_field('subdepartmentid', XMLDB_TYPE_INTEGER, '10', null, null, null, 0, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2022111700, 'local', 'groups');
    }
    if ($oldversion < 2022111700.05) {
        $table = new xmldb_table('local_groups');
        $field = new xmldb_field('categoryid');
           // Conditionally launch drop field companyid.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2022111700.05, 'local', 'groups');
    }
    if ($oldversion < 2022111700.10) {
        $table = new xmldb_table('local_sub_groups');

        // Adding fields to table norsu_strands.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null);
        $table->add_field('groupid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, null);
        $table->add_field('parentid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, null);
        $table->add_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, null, null, null, null);
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, null, null, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, null, null, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, null, null);

        // Adding keys to table.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        upgrade_plugin_savepoint(true, 2022111700.10, 'local', 'groups');
    }
    if ($oldversion < 2022111700.13) {
        $table = new xmldb_table('cohort_members');
        $field = new xmldb_field('parentid', XMLDB_TYPE_INTEGER, '10', null, null, null, 0, 'cohortid');
           // Conditionally launch drop field companyid.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2022111700.13, 'local', 'groups');
    }
    if ($oldversion < 2022111700.14) {
        $table = new xmldb_table('cohort_members');
        $field = new xmldb_field('parentid', XMLDB_TYPE_INTEGER, '10', null, null, null, 0, 'cohortid');
           // Conditionally launch drop field companyid.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2022111700.14, 'local', 'groups');
    }
    return true;
}
