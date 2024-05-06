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
 * Upgrade code for install
 *
 * @package   mod_assign
 * @copyright 2012 NetSpot {@link http://www.netspot.com.au}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * upgrade this assignment instance - this function could be skipped but it will be needed later
 * @param int $oldversion The old version of the assign module
 * @return bool
 */
function xmldb_stream_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2016052307) {
        $table = new xmldb_table('stream');
        $field = new xmldb_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, null, null, null);      

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, null, null, null);      

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_mod_savepoint(true, 2016052307, 'stream');
    }

    if ($oldversion < 2021031618) {
        $table = new xmldb_table('stream');
        $field = new xmldb_field('duration', XMLDB_TYPE_CHAR, '255', null, null, null, null);      

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_mod_savepoint(true, 2021031618, 'stream');
    }
    if ($oldversion < 2016052311) {
        $table = new xmldb_table('uploaded_videos');
        if (!$dbman->table_exists($table)) {
            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table->add_field('organization', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null);
            $table->add_field('videoid', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('title', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('description', XMLDB_TYPE_TEXT, null, null, null, null, null);
            $table->add_field('tags', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('filename', XMLDB_TYPE_CHAR, '255', null, null, null, '0');
            $table->add_field('filepath', XMLDB_TYPE_CHAR, '255', null, null, null, '0');
            $table->add_field('thumbnail', XMLDB_TYPE_CHAR, '255', null, null, null, '0');
            $table->add_field('organisationname', XMLDB_TYPE_CHAR, '255', null, null, null, '0');
            $table->add_field('tagsname', XMLDB_TYPE_CHAR, '255', null, null, null, '0');
            $table->add_field('status', XMLDB_TYPE_INTEGER, '1', null, null, null, '0');
            $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
            $table->add_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
            $result = $dbman->create_table($table);
        }
        upgrade_mod_savepoint(true, 2016052311, 'stream');
    }
    if ($oldversion < 2016052312) {
        $table = new xmldb_table('uploaded_videos');
        $field = new xmldb_field('uploaded_on', XMLDB_TYPE_INTEGER, '10', null, null, null, null);      

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, null);      

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_mod_savepoint(true, 2016052312, 'stream');
    }
    if ($oldversion < 2016052313) {
        $table = new xmldb_table('uploaded_videos');
        $field = new xmldb_field('streamurl', XMLDB_TYPE_CHAR, '255', null, null, null, '0');      

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_mod_savepoint(true, 2016052313, 'stream');
    }

    if ($oldversion < 2021031619) {
        $table = new xmldb_table('stream_recordings');
        if (!$dbman->table_exists($table)) {
            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table->add_field('type', XMLDB_TYPE_CHAR, '55', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('activityid', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
            $table->add_field('videoid', XMLDB_TYPE_CHAR, '155', null, null, null, '0');
            $table->add_field('recordon', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
            $table->add_field('filepath', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('filename', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('status', XMLDB_TYPE_INTEGER, '1', null, null, null, '0');
            $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
            $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
            $result = $dbman->create_table($table);
        }
        upgrade_mod_savepoint(true, 2021031619, 'stream');
    }
    return true;
}
