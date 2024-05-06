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
 * curriculum Capabilities
 *
 * curriculum - A Moodle plugin for managing ILT's
 *
 * @package     local_curriculum
 * @author:     Arun Kumar Mukka <arun@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
defined('MOODLE_INTERNAL') || die;
$capabilities = array(
    'local/curriculum:createprogram' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'coursecreator' => CAP_PREVENT,
            'teacher'        => CAP_PREVENT,
            'editingteacher' => CAP_PREVENT,
            'manager'          => CAP_ALLOW,
            'user'        => CAP_PREVENT,
            'student'      => CAP_PREVENT,
            'guest' => CAP_PREVENT
        ),
    ),
    'local/curriculum:manageprogram' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'coursecreator' => CAP_PREVENT,
            'teacher'        => CAP_ALLOW,
            'editingteacher' => CAP_PREVENT,
            'manager'          => CAP_ALLOW,
            'user'        => CAP_PREVENT,
            'student'      => CAP_PREVENT,
            'guest' => CAP_PREVENT
        ),
    ),
    'local/curriculum:viewprogram' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'coursecreator' => CAP_PREVENT,
            'teacher'        => CAP_ALLOW,
            'editingteacher' => CAP_PREVENT,
            'manager'          => CAP_ALLOW,
            'user'        => CAP_PREVENT,
            'student'      => CAP_PREVENT,
            'guest' => CAP_PREVENT
        ),
    ),
    'local/curriculum:viewusers' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
           'coursecreator' => CAP_PREVENT,
           'teacher'        => CAP_ALLOW,
           'editingteacher' => CAP_PREVENT,
           'manager'          => CAP_ALLOW,
           'user'        => CAP_PREVENT,
           'student'      => CAP_PREVENT,
           'guest' => CAP_PREVENT
        ),
    ),
    'local/curriculum:takesessionattendance' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
           'coursecreator' => CAP_PREVENT,
           'teacher'        => CAP_ALLOW,
           'editingteacher' => CAP_PREVENT,
           'manager'          => CAP_ALLOW,
           'user'        => CAP_PREVENT,
           'student'      => CAP_PREVENT,
           'guest' => CAP_PREVENT
        ),
    ),
    'local/curriculum:trainer_viewprogram' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'coursecreator' => CAP_PREVENT,
            'teacher'        => CAP_ALLOW,
            'editingteacher' => CAP_PREVENT,
            'manager'          => CAP_PREVENT,
            'user'        => CAP_PREVENT,
            'student'      => CAP_PREVENT,
            'guest' => CAP_PREVENT
        ),
    ),
    'local/curriculum:addcourse' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
           'coursecreator' => CAP_PREVENT,
           'teacher'        => CAP_PREVENT,
           'editingteacher' => CAP_PREVENT,
           'manager'          => CAP_ALLOW,
           'user'        => CAP_PREVENT,
           'student'      => CAP_PREVENT,
           'guest' => CAP_PREVENT
        ),
    ),
    'local/curriculum:editcourse' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
           'coursecreator' => CAP_PREVENT,
           'teacher'        => CAP_PREVENT,
           'editingteacher' => CAP_PREVENT,
           'manager'          => CAP_ALLOW,
           'user'        => CAP_PREVENT,
           'student'      => CAP_PREVENT,
           'guest' => CAP_PREVENT
        ),
    ),
    'local/curriculum:removecourse' => array(
        'captype' => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
           'coursecreator' => CAP_PREVENT,
           'teacher'        => CAP_PREVENT,
           'editingteacher' => CAP_PREVENT,
           'manager'          => CAP_ALLOW,
           'user'        => CAP_PREVENT,
           'student'      => CAP_PREVENT,
           'guest' => CAP_PREVENT
        ),
    ),
    'local/curriculum:managecourse' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
           'coursecreator' => CAP_PREVENT,
           'teacher'        => CAP_PREVENT,
           'editingteacher' => CAP_PREVENT,
           'manager'          => CAP_ALLOW,
           'user'        => CAP_PREVENT,
           'student'      => CAP_PREVENT,
           'guest' => CAP_PREVENT
        ),
    ),
    'local/curriculum:manage_owndepartments' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'coursecreator' => CAP_PREVENT,
            'teacher'        => CAP_PREVENT,
            'editingteacher' => CAP_PREVENT,
            'manager'          => CAP_PREVENT,
            'user'        => CAP_PREVENT,
            'student'      => CAP_PREVENT,
            'guest' => CAP_PREVENT
        ),
    ),
    'local/curriculum:manage_ownorganization' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'coursecreator' => CAP_PREVENT,
            'teacher'        => CAP_PREVENT,
            'editingteacher' => CAP_PREVENT,
            'manager'          => CAP_PREVENT,
            'user'        => CAP_PREVENT,
            'student'      => CAP_PREVENT,
            'guest' => CAP_PREVENT
        ),
    ),
    'local/curriculum:manage_multiorganizations' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'coursecreator' => CAP_PREVENT,
            'teacher'        => CAP_PREVENT,
            'editingteacher' => CAP_PREVENT,
            'manager'          => CAP_PREVENT,
            'user'        => CAP_PREVENT,
            'student'      => CAP_PREVENT,
            'guest' => CAP_PREVENT
        ),
    ),
    'local/curriculum:createsemester' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
           'coursecreator' => CAP_PREVENT,
           'teacher'        => CAP_PREVENT,
           'editingteacher' => CAP_PREVENT,
           'manager'          => CAP_ALLOW,
           'user'        => CAP_PREVENT,
           'student'      => CAP_PREVENT,
           'guest' => CAP_PREVENT
        ),
    ),
    'local/curriculum:viewsemester' => array(
        'captype' => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
           'coursecreator' => CAP_PREVENT,
           'teacher'        => CAP_PREVENT,
           'editingteacher' => CAP_PREVENT,
           'manager'          => CAP_ALLOW,
           'user'        => CAP_PREVENT,
           'student'      => CAP_PREVENT,
           'guest' => CAP_PREVENT
        ),
    ),
    'local/curriculum:editsemester' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
           'coursecreator' => CAP_PREVENT,
           'teacher'        => CAP_PREVENT,
           'editingteacher' => CAP_PREVENT,
           'manager'          => CAP_ALLOW,
           'user'        => CAP_PREVENT,
           'student'      => CAP_PREVENT,
           'guest' => CAP_PREVENT
        ),
    ),
    'local/curriculum:deletesemester' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
           'coursecreator' => CAP_PREVENT,
           'teacher'        => CAP_PREVENT,
           'editingteacher' => CAP_PREVENT,
           'manager'          => CAP_ALLOW,
           'user'        => CAP_PREVENT,
           'student'      => CAP_PREVENT,
           'guest' => CAP_PREVENT
        ),
    ),
    'local/curriculum:managesemester' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
           'coursecreator' => CAP_PREVENT,
           'teacher'        => CAP_PREVENT,
           'editingteacher' => CAP_PREVENT,
           'manager'          => CAP_ALLOW,
           'user'        => CAP_PREVENT,
           'student'      => CAP_PREVENT,
           'guest' => CAP_PREVENT
        ),
    ),
    'local/curriculum:enrolcourse' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
           'student'      => CAP_ALLOW,
        ),
    ),
    'local/curriculum:createsemesteryear' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
           'coursecreator' => CAP_PREVENT,
           'teacher'        => CAP_PREVENT,
           'editingteacher' => CAP_PREVENT,
           'manager'          => CAP_ALLOW,
           'user'        => CAP_PREVENT,
           'student'      => CAP_PREVENT,
           'guest' => CAP_PREVENT
        ),
    ),
    'local/curriculum:viewsemesteryear' => array(
        'captype' => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
           'coursecreator' => CAP_PREVENT,
           'teacher'        => CAP_PREVENT,
           'editingteacher' => CAP_PREVENT,
           'manager'          => CAP_ALLOW,
           'user'        => CAP_PREVENT,
           'student'      => CAP_PREVENT,
           'guest' => CAP_PREVENT
        ),
    ),
    'local/curriculum:editsemesteryear' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
           'coursecreator' => CAP_PREVENT,
           'teacher'        => CAP_PREVENT,
           'editingteacher' => CAP_PREVENT,
           'manager'          => CAP_ALLOW,
           'user'        => CAP_PREVENT,
           'student'      => CAP_PREVENT,
           'guest' => CAP_PREVENT
        ),
    ),
    'local/curriculum:deletesemesteryear' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
           'coursecreator' => CAP_PREVENT,
           'teacher'        => CAP_PREVENT,
           'editingteacher' => CAP_PREVENT,
           'manager'          => CAP_ALLOW,
           'user'        => CAP_PREVENT,
           'student'      => CAP_PREVENT,
           'guest' => CAP_PREVENT
        ),
    ),
    'local/curriculum:managesemesteryear' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
           'coursecreator' => CAP_PREVENT,
           'teacher'        => CAP_PREVENT,
           'editingteacher' => CAP_PREVENT,
           'manager'          => CAP_ALLOW,
           'user'        => CAP_PREVENT,
           'student'      => CAP_PREVENT,
           'guest' => CAP_PREVENT
        ),
    ),
    'local/curriculum:canaddfaculty' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
           'manager'          => CAP_ALLOW
        ),
    ),
    'local/curriculum:cansetcost' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
           'manager'          => CAP_ALLOW
        ),
    ),
    'local/curriculum:canmanagefaculty' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
           'manager'          => CAP_ALLOW
        ),
    ),
    'local/curriculum:importcoursecontent' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'coursecreator' => CAP_PREVENT,
            'teacher'        => CAP_PREVENT,
            'manager'          => CAP_ALLOW
        ),
    ),
);
