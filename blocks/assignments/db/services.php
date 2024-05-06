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
 * block assignments gradings
 *
 * @package    block_assignments
 * @copyright  2023 Moodle India
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// We defined the web service functions to install.

defined("MOODLE_INTERNAL") || die;
$functions = array(
    'block_assignments_view' => array(
        'classname'   => 'block_assignments_external',
        'methodname'  => 'assignments_grades_view',
        'classpath'   => 'blocks/assignments/classes/external.php',
        'description' => 'Assignments for grade view',
        'type'        => 'write',
        'ajax' => true,
    )
);