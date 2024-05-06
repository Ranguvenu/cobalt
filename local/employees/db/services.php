<?php
/**
 * This file is part of eAbyas
 *
 * Copyright eAbyas Info Solutons Pvt Ltd, India
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * @package   local
 * @subpackage  employees
 * @author eabyas  <info@eabyas.in>
**/

defined('MOODLE_INTERNAL') || die;
$functions = array(
    'local_employees_submit_creates_employees_form' => array(
        'classname'   => 'local_employees_external',
        'methodname'  => 'submit_creates_employees_form',
        'classpath'   => 'local/employees/classes/external.php',
        'description' => 'Submit form',
        'type'        => 'write',
        'ajax' => true,
    ),
    'local_employees_trash_employees' => array(
        'classname'   => 'local_employees_external',
        'methodname'  => 'trash_employees',
        'classpath'   => 'local/employees/classes/external.php',
        'description' => 'deleting of employees',
        'type'        => 'write',
        'ajax' => true,
    ),
    'local_employees_suspend_employees' => array(
        'classname'   => 'local_employees_external',
        'methodname'  => 'suspend_employees',
        'classpath'   => 'local/employees/classes/external.php',
        'description' => 'suspending of employees',
        'type'        => 'write',
        'ajax' => true,
    ),
    'local_employees_get_departments_list' => array(
        'classname'   => 'local_employees_external',
        'methodname'  => 'get_departments_list',
        'classpath'   => 'local/employees/classes/external.php',
        'description' => 'Get Departments List',
        'type'        => 'read',
        'ajax' => true,
    ),
    'local_employees_get_supervisors_list' => array(
        'classname'   => 'local_employees_external',
        'methodname'  => 'get_supervisors_list',
        'classpath'   => 'local/employees/classes/external.php',
        'description' => 'Get Supervisors List',
        'type'        => 'read',
        'ajax' => true,
    ),
    'local_employees_manage_employees_view' => array(
        'classname'   => 'local_employees_external',
        'methodname'  => 'manage_employees_view',
        'classpath'   => 'local/employees/classes/external.php',
        'description' => 'Display the employees Page',
        'type'        => 'write',
        'ajax' => true
    ),
    'local_employees_profile_moduledata' => array(
        'classname'   => 'local_employees_external',
        'methodname'  => 'profilemoduledata',
        'classpath'   => 'local/employees/classes/external.php',
        'description' => 'display module data in profile',
        'type'        => 'write',
        'ajax' => true,
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    ),
    'local_employees_profile_data' => array(
        'classname'   => 'local_employees_external',
        'methodname'  => 'profiledata',
        'classpath'   => 'local/employees/classes/external.php',
        'description' => 'display the profile data',
        'type'        => 'write',
        'ajax' => true,
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    ),
    'local_employees_dashboard_stats' => array(
        'classname'   => 'local_employees_external',
        'methodname'  => 'dashboard_stats',
        'classpath'   => 'local/employees/classes/external.php',
        'description' => 'Dashboard stats for mobile',
        'type'        => 'read',
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    ),
    'local_employees_pending_activities' => array(
        'classname' => 'local_employees_external',
        'methodname' => 'pending_activities',
        'description' => 'Get pending_activities',
        'classpath' => 'local/employees/classes/external.php',
        'type' => 'read',
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    ),
    'local_employees_get_grade_items' => array(
        'classname' => 'local_employees_external',
        'methodname' => 'get_grade_items',
        'classpath' => 'local/employees/classes/externallib.php',
        'description' => 'Returns the complete list of grade items for employees in a course',
        'type' => 'read',
        'capabilities' => 'gradereport/employees:view',
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    ),
    'local_employees_get_course_grades' => array(
        'classname' => 'local_employees_external',
        'methodname' => 'get_course_grades',
        'classpath' => 'local/employees/classes/externallib.php',
        'description' => 'Get the given employees courses final grades',
        'type' => 'read',
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    ),
    'local_employees_submit_profile_update_form' => array(
        'classname'   => 'local_employees_external',
        'methodname'  => 'submit_profile_update_form',
        'classpath'   => 'local/employees/classes/external.php',
        'description' => 'Submit form',
        'type'        => 'write',
        'ajax' => true,
    ),
);
