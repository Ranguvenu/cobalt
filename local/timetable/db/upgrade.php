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
 * @package local_timetable
 */

function xmldb_local_timetable_upgrade($oldversion) {
    global $DB, $CFG, $USER;
    $dbman = $DB->get_manager();
    if ($oldversion < 2020032602.02) {
        $table = new xmldb_table('local_session_type');

        // Adding fields to table norsu_strands.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('session_type', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, null, null, 0);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, null, null, 0);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, 0);

        // Adding keys to table.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        upgrade_plugin_savepoint(true, 2020032602.02, 'local', 'timetable');
    }
    if ($oldversion < 2020032602.03) {
        $table = new xmldb_table('local_session_type');
        $field = new xmldb_field('organization', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'session_type');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2020032602.03, 'local', 'timetable');
    }

    if ($oldversion < 2020032602.05) {
        $table = new xmldb_table('local_timeintervals');
        $field1 = new xmldb_field('open_departmentid', XMLDB_TYPE_INTEGER, '20', null, null, null, null,null);
        $field2 = new xmldb_field('open_subdepartment', XMLDB_TYPE_INTEGER, '20', null, null, null, null,null);

        if (!$dbman->field_exists($table, $field1)) {
            $dbman->add_field($table, $field1);
        }
        if (!$dbman->field_exists($table, $field2)) {
            $dbman->add_field($table, $field2);
        }
        upgrade_plugin_savepoint(true, 2020032602.05, 'local', 'timetable');
    }

    if ($oldversion < 2020032602.06) {
        $arr = $DB->get_records('local_costcenter', ['depth' => 1]);
        foreach ($arr as $k => $v) {
            $rec = new stdClass;
            $rec->session_type = get_string('exam', 'local_timetable');
            $rec->organization = $v->id;
            $rec->usercreated = $USER->id;
            $rec->timecreated = time();
            $rec->timemodified = null;
            if (!$DB->record_exists('local_session_type', array('organization' => $v->id, 'session_type' => 'Examination'))) {
                $DB->insert_record('local_session_type', $rec);
            }
        }
        upgrade_plugin_savepoint(true, 2020032602.06, 'local', 'timetable');
    }

    return true;
}
