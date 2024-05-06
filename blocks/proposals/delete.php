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
 * Version details.
 *
 * @package    block_proposals
 * @copyright  moodle
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(__DIR__ . '/../../config.php');
require_login();
global $DB;
$id = optional_param('formid', 0 , PARAM_INT);
$PAGE->set_url(new moodle_url('/blocks/proposals/delete.php'));
$PAGE->set_context(\context_system::instance());
$PAGE->set_title('Delete Submission');
if ($id) {
    $DB->delete_records('submissions', array('id' => $id));
}
redirect($CFG->wwwroot.'/blocks/proposals/view.php');