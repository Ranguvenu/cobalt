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
 * @package
 * @author     eAbyas Info Solutions
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
defined('MOODLE_INTERNAL') || die;
$capabilities = array(
    'local/program:createprogram' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'coursecreator' => CAP_INHERIT,
            'teacher'        => CAP_INHERIT,
            'editingteacher' => CAP_INHERIT,
            'manager'          => CAP_ALLOW,
            'user'        => CAP_INHERIT,
            'student'      => CAP_INHERIT,
            'guest' => CAP_INHERIT
        ),
    ),
    'local/program:inactiveprogram' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'coursecreator' => CAP_INHERIT,
            'teacher'        => CAP_INHERIT,
            'editingteacher' => CAP_INHERIT,
            'manager'          => CAP_ALLOW,
            'user'        => CAP_INHERIT,
            'student'      => CAP_INHERIT,
            'guest' => CAP_INHERIT
        ),
    ),
    'local/program:activeprogram' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'coursecreator' => CAP_INHERIT,
            'teacher'        => CAP_INHERIT,
            'editingteacher' => CAP_INHERIT,
            'manager'          => CAP_ALLOW,
            'user'        => CAP_INHERIT,
            'student'      => CAP_INHERIT,
            'guest' => CAP_INHERIT
        ),
    ),
    'local/program:editprogram' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'coursecreator' => CAP_INHERIT,
            'teacher' => CAP_INHERIT,
            'editingteacher' => CAP_INHERIT,
            'manager' => CAP_ALLOW,
            'user' => CAP_INHERIT,
            'student' => CAP_INHERIT,
            'guest' => CAP_INHERIT
        ),
    ),
    'local/program:deleteprogram' => array(
        'captype' => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'coursecreator' => CAP_INHERIT,
            'teacher'        => CAP_INHERIT,
            'editingteacher' => CAP_INHERIT,
            'manager'          => CAP_ALLOW,
            'user'        => CAP_INHERIT,
            'student'      => CAP_INHERIT,
            'guest' => CAP_INHERIT
        ),
    ),
    'local/program:manageprogram' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'coursecreator' => CAP_INHERIT,
            'teacher'        => CAP_ALLOW,
            'editingteacher' => CAP_INHERIT,
            'manager'          => CAP_ALLOW,
            'user'        => CAP_INHERIT,
            'student'      => CAP_INHERIT,
            'guest' => CAP_INHERIT
        ),
    ),
    'local/program:createsession' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'coursecreator' => CAP_INHERIT,
            'teacher'        => CAP_INHERIT,
            'editingteacher' => CAP_INHERIT,
            'manager'          => CAP_ALLOW,
            'user'        => CAP_INHERIT,
            'student'      => CAP_INHERIT,
            'guest' => CAP_INHERIT
        ),
    ),
    
    'local/program:manageusers' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
           'coursecreator' => CAP_INHERIT,
           'teacher'        => CAP_INHERIT,
           'editingteacher' => CAP_INHERIT,
           'manager'          => CAP_ALLOW,
           'user'        => CAP_INHERIT,
           'student'      => CAP_INHERIT,
           'guest' => CAP_INHERIT
        ),
    ),
    'local/program:viewusers' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
           'coursecreator' => CAP_INHERIT,
           'teacher'        => CAP_ALLOW,
           'editingteacher' => CAP_INHERIT,
           'manager'          => CAP_ALLOW,
           'user'        => CAP_INHERIT,
           'student'      => CAP_INHERIT,
           'guest' => CAP_INHERIT
        ),
    ),
    'local/program:takesessionattendance' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
           'coursecreator' => CAP_INHERIT,
           'teacher'        => CAP_ALLOW,
           'editingteacher' => CAP_INHERIT,
           'manager'          => CAP_ALLOW,
           'user'        => CAP_INHERIT,
           'student'      => CAP_INHERIT,
           'guest' => CAP_INHERIT
        ),
    ),
    'local/program:trainer_viewprogram' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'coursecreator' => CAP_INHERIT,
            'teacher'        => CAP_ALLOW,
            'editingteacher' => CAP_INHERIT,
            'manager'          => CAP_INHERIT,
            'user'        => CAP_INHERIT,
            'student'      => CAP_INHERIT,
            'guest' => CAP_INHERIT
        ),
    ),
    'local/program:view_newprogramtab' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
           'coursecreator' => CAP_INHERIT,
           'teacher'        => CAP_INHERIT,
           'editingteacher' => CAP_INHERIT,
           'manager'          => CAP_ALLOW,
           'user'        => CAP_INHERIT,
           'student'      => CAP_INHERIT,
           'guest' => CAP_INHERIT
        ),
    ),
    'local/program:view_holdprogramtab' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
           'coursecreator' => CAP_INHERIT,
           'teacher'        => CAP_INHERIT,
           'editingteacher' => CAP_INHERIT,
           'manager'          => CAP_ALLOW,
           'user'        => CAP_INHERIT,
           'student'      => CAP_INHERIT,
           'guest' => CAP_INHERIT
        ),
    ),
    'local/program:addcourse' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
           'coursecreator' => CAP_INHERIT,
           'teacher'        => CAP_INHERIT,
           'editingteacher' => CAP_INHERIT,
           'manager'          => CAP_ALLOW,
           'user'        => CAP_INHERIT,
           'student'      => CAP_INHERIT,
           'guest' => CAP_INHERIT
        ),
    ),
    'local/program:editcourse' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
           'coursecreator' => CAP_INHERIT,
           'teacher'        => CAP_INHERIT,
           'editingteacher' => CAP_INHERIT,
           'manager'          => CAP_ALLOW,
           'user'        => CAP_INHERIT,
           'student'      => CAP_INHERIT,
           'guest' => CAP_INHERIT
        ),
    ),
    'local/program:managecourse' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
           'coursecreator' => CAP_INHERIT,
           'teacher'        => CAP_INHERIT,
           'editingteacher' => CAP_INHERIT,
           'manager'          => CAP_ALLOW,
           'user'        => CAP_INHERIT,
           'student'      => CAP_INHERIT,
           'guest' => CAP_INHERIT
        ),
    ),
    'local/program:manage_owndepartments' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'coursecreator' => CAP_INHERIT,
            'teacher'        => CAP_INHERIT,
            'editingteacher' => CAP_INHERIT,
            'manager'          => CAP_INHERIT,
            'user'        => CAP_INHERIT,
            'student'      => CAP_INHERIT,
            'guest' => CAP_INHERIT
        ),
    ),
    'local/program:manage_multiorganizations' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'coursecreator' => CAP_INHERIT,
            'teacher'        => CAP_INHERIT,
            'editingteacher' => CAP_INHERIT,
            'manager'          => CAP_INHERIT,
            'user'        => CAP_INHERIT,
            'student'      => CAP_INHERIT,
            'guest' => CAP_INHERIT
        ),
    ),
    'local/program:programcompletion' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
           'coursecreator' => CAP_INHERIT,
           'teacher'        => CAP_INHERIT,
           'editingteacher' => CAP_INHERIT,
           'manager'          => CAP_ALLOW,
           'user'        => CAP_INHERIT,
           'student'      => CAP_INHERIT,
           'guest' => CAP_INHERIT
        ),
    ),
    'local/program:createlevel' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
           'coursecreator' => CAP_INHERIT,
           'teacher'        => CAP_INHERIT,
           'editingteacher' => CAP_INHERIT,
           'manager'          => CAP_ALLOW,
           'user'        => CAP_INHERIT,
           'student'      => CAP_INHERIT,
           'guest' => CAP_INHERIT
        ),
    ),
    'local/program:viewlevel' => array(
        'captype' => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
           'coursecreator' => CAP_INHERIT,
           'teacher'        => CAP_INHERIT,
           'editingteacher' => CAP_INHERIT,
           'manager'          => CAP_ALLOW,
           'user'        => CAP_INHERIT,
           'student'      => CAP_INHERIT,
           'guest' => CAP_INHERIT
        ),
    ),
    'local/program:editlevel' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
           'coursecreator' => CAP_INHERIT,
           'teacher'        => CAP_INHERIT,
           'editingteacher' => CAP_INHERIT,
           'manager'          => CAP_ALLOW,
           'user'        => CAP_INHERIT,
           'student'      => CAP_INHERIT,
           'guest' => CAP_INHERIT
        ),
    ),
    'local/program:deletelevel' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
           'coursecreator' => CAP_INHERIT,
           'teacher'        => CAP_INHERIT,
           'editingteacher' => CAP_INHERIT,
           'manager'          => CAP_ALLOW,
           'user'        => CAP_INHERIT,
           'student'      => CAP_INHERIT,
           'guest' => CAP_INHERIT
        ),
    ),
    'local/program:enrolsession' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
           'student'      => CAP_ALLOW,
        ),
    ),
    /* DM-469-Amol-starts */
    'local/program:removecourse' => array(
        'captype' => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
           'coursecreator' => CAP_INHERIT,
           'teacher'        => CAP_INHERIT,
           'editingteacher' => CAP_INHERIT,
           'manager'          => CAP_ALLOW,
           'user'        => CAP_INHERIT,
           'student'      => CAP_INHERIT,
           'guest' => CAP_INHERIT
        ),
    )
    /* DM-469-Amol-ends */
);
