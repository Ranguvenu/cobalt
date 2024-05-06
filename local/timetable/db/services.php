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
 * Web service for local_timetable
 * @package    local_timetable
 * @subpackage db
 * @since      Moodle 2.4
 * @copyright  2023 Dipanshu Kasera <kasera.dipanshu@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;
$functions = array(
    'local_timetable_submit_instance' => array(
        'classname' => 'local_timetable_external',
        'methodname' => 'submit_instance',
        'classpath' => 'local/timetable/externallib.php',
        'description' => 'Session insert forms event handling',
        'ajax' => true,
        'type' => 'write'
    ),
    'local_timetable_update_instance' => array(
        'classname' => 'local_timetable_external',
        'methodname' => 'update_instance',
        'classpath' => 'local/timetable/externallib.php',
        'description' => 'Session update forms event handling',
        'ajax' => true,
        'type' => 'write'
    ),
    'local_timetable_delete_instance' => array(
        'classname' => 'local_timetable_external',
        'methodname' => 'delete_instance',
        'classpath' => 'local/timetable/externallib.php',
        'description' => 'Session and schedulesessions detetion event handling',
        'ajax' => true,
        'type' => 'write'
    ),
    'local_timetable_semester_slots_delete_instance' => array(
        'classname' => 'local_timetable_external',
        'methodname' => 'semester_slots_delete_instance',
        'classpath' => 'local/timetable/externallib.php',
        'description' => 'All session slots event handling',
        'ajax' => true,
        'type' => 'write'
    ),
    'local_timetable_view_instance' => array(
        'classname' => 'local_timetable_external',
        'methodname' => 'timetable_view_instance',
        'classpath' => 'local/timetable/externallib.php',
        'description' => 'Semester timetable view event handling',
        'ajax' => true,
        'type' => 'write'
    ),
    'local_timetable_individual_session_instance' => array(
        'classname' => 'local_timetable_external',
        'methodname' => 'timetable_individual_session_instance',
        'classpath' => 'local/timetable/externallib.php',
        'description' => 'Semester individual sessions view event handling',
        'ajax' => true,
        'type' => 'write'
    ),
    'local_timetable_delete_session' => array(
        'classname' => 'local_timetable_external',
        'methodname' => 'delete_session',
        'classpath' => 'local/timetable/externallib.php',
        'description' => 'Semester individual sessions view event handling',
        'ajax' => true,
        'type' => 'write'
    ),
    'local_timetable_delete_session_type' => array(
        'classname' => 'local_timetable_external',
        'methodname' => 'delete_session_type',
        'classpath' => 'local/timetable/externallib.php',
        'description' => 'Semester individual sessions view event handling',
        'ajax' => true,
        'type' => 'write'
    ),
);
