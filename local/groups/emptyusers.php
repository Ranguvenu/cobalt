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
 * Version information
 *
 * @package    local_groups
 * @copyright  2022 eAbyas Info Solutions Pvt. Ltd.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
global $CFG, $DB, $PAGE, $OUTPUT;
require_once($CFG->dirroot.'/local/program/lib.php');
require_login();
use local_program\program;
$cohortid = required_param('cid', PARAM_INT);
$search = optional_param_array('search', '', PARAM_RAW);
$context = context_system::instance();
$PAGE->set_context($context);
$title = get_string('cohorts', 'local_groups');

$cid = $DB->get_record('cohort', array('id' => $cohortid));
if (empty($cid)) {
    throw new moodle_exception(get_string('programsnotfound', 'local_groups'));
}

$url = new moodle_url($CFG->wwwroot . '/local/groups/emptyusers.php', array('cid' => $cohortid));
$PAGE->requires->js_call_amd('local_program/program', 'GroupsDatatables',
                    array(array('cohortid' => $cohortid)));
$renderer = $PAGE->get_renderer('local_program');

$PAGE->set_url($url);
echo $OUTPUT->header();

$stable = new stdClass();
$stable->thead = true;
$stable->start = 0;
$stable->length = -1;
$stable->search = '';
$stable->cohortid = $cohortid;
echo $renderer->viewgroupusers($stable, true);
echo $OUTPUT->footer();
