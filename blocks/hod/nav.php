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
 * @package    block_hod
 * @copyright  moodle
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/blocks/hod/classes/form/form.php');
global $DB;
require_login();

$PAGE->set_title('Submissions');
$PAGE->set_heading('Proposal Details');
$PAGE->set_url(new moodle_url('/blocks/hod/details.php?formid=1'));
$context = context_system::instance();
$PAGE->requires->css('/blocks/hod/css/navtab.css');
$PAGE->requires->js('/blocks/hod/js/navtab.css');

echo $OUTPUT->header();


$templatecontext = (object)[
    'tab' => 'tab one',
];

echo $OUTPUT->render_from_template('block_hod/example' , $templatecontext);
$mform->display();
echo $OUTPUT->footer();
