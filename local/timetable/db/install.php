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
 * post installation hook for adding data.
 *
 * @package    local_timetable
 * @copyright  2023 Dipanshu Kasera
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Post installation procedure
 */
function xmldb_local_timetable_install() {
    global $DB, $USER;

    $result = true;
    $arr = $DB->get_records('local_costcenter', ['depth' => 1]);
    foreach ($arr as $k => $v) {
        $rec = new stdClass;
        $rec->session_type = get_string('exam', 'local_timetable');
        $rec->organization = $v->id;
        $rec->usercreated = $USER->id;
        $rec->timecreated = time();
        $rec->timemodified = null;
        if (!$DB->record_exists('local_session_type', array('organization' => $v->id, 'session_type' => 'Examination'))) {
            $result = $result && $DB->insert_record('local_session_type', $rec);
        }
    }

    $dbman = $DB->get_manager(); // loads ddl manager and xmldb classes
    $table = new xmldb_table('attendance_sessions');
    if ($dbman->table_exists($table)) {
        $field = new xmldb_field('batch_group', XMLDB_TYPE_INTEGER, '10', null, null, null, 0, 'courseid');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }

    return $result;
}
