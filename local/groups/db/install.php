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
function xmldb_local_groups_install() {
    global $DB, $USER;

    $dbman = $DB->get_manager(); // loads ddl manager and xmldb classes
    $table = new xmldb_table('cohort_members');
    if ($dbman->table_exists($table)) {
        $field = new xmldb_field('parentid', XMLDB_TYPE_INTEGER, '10', null, null, null, 0, 'cohortid');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }
    return $result;
}
