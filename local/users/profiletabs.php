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
 * List the tool provided in a course
 *
 * @package    local
 * @subpackage  users
 * @copyright  2016 manikanta <manikantam@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../config.php');
require_login();
global $DB, $CFG, $USER, $PAGE, $OUTPUT;
require_once($CFG->dirroot . '/local/courses/lib.php');
$PAGE->requires->jquery();
$id = optional_param('id', $USER->id, PARAM_INT);
$action = required_param('action', PARAM_TEXT);
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
$userrenderer = $PAGE->get_renderer('local_users');
switch ($action) {
    case 'program';
          $userrenderer->get_enrolled_program($id);
    exit;
    break;
    case 'courses':
          echo $userrenderer->get_coursedetails($id);
    exit;
    break;
}
