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
 * Class block_semester_progress
 *
 * @package    block_semester_progress
 * @copyright  2023 Dipanshu Kasera
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// use \block_semester_progress\semester_progress_lib as semester_progress_lib;
define('AJAX_SCRIPT', true);
require_once(dirname(__FILE__) . '/../../config.php');
global $CFG, $DB, $OUTPUT, $PAGE;
require_login();
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
$semid = optional_param('semid', 0, PARAM_INT);
$pid = $DB->get_field('local_program_levels', 'programid', ['id' => $semid]);

$data = new \block_semester_progress\semester_progress_lib();
$completedsem = $data->cm_semester($pid, $semid);

$usercourses = [
    'completedsem' => $completedsem
];

// echo $OUTPUT->render_from_template('block_semester_progress/completedsem', $usercourses);
echo json_encode($usercourses);
