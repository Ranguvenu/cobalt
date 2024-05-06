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
 * Competencies to review page.
 *
 * @package    block_assignments
 * @copyright  Moodle India
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
global $PAGE, $OUTPUT, $DB, $USER;
require_login();
$url = new moodle_url('/blocks/assignments/submissions.php');
$PAGE->set_url($url);
$PAGE->set_title(get_string('pluginname', 'block_assignments'));
$PAGE->set_heading(get_string('pluginname', 'block_assignments'));

require_once($CFG->dirroot . '/blocks/assignments/filters_form.php');
$teacher = $DB->get_records_sql("SELECT DISTINCT(r.id),r.shortname 
                            FROM {role} r
                            JOIN {role_assignments} ra ON ra.roleid = r.id
                            JOIN {user} u ON u.id = ra.userid
                            WHERE u.id = {$USER->id} 
                            AND r.shortname = 'editingteacher'");
echo $OUTPUT->header();
if ($teacher) {
    $renderer = $PAGE->get_renderer('block_assignments');
    $filterparams = $renderer->assignments_content(true);
    $mform = new filters_form(null, array('filterlist' => array('assignmentname', 'coursename', 'studentname'), 'filterparams' => $filterparams));
    if ($mform->is_cancelled()) {
        redirect($CFG->wwwroot . '/blocks/assignments/submissions.php');
    }
    echo '<a class="btn-link btn-sm d-flex align-items-center filter_btn" href="javascript:void(0);" data-toggle="collapse"
            data-target="#block_assignments-filter_collapse" aria-expanded="false"
            aria-controls="block_assignments-filter_collapse">
                    <span class="filter mr-2">Filters</span>
                <i class="m-0 fa fa-sliders fa-2x" aria-hidden="true"></i>
          </a>';
    $filterparams['submitid'] = 'form#filteringform';
    echo  '<div class="mt-2 mb-2 collapse '.$show.'" id="block_assignments-filter_collapse">
                <div id="filters_form" class="card card-body p-2">';
                    $mform->display();
    echo       '</div>
           </div>';
    echo $OUTPUT->render_from_template('block_assignments/global_filter', $filterparams);
    echo $renderer->assignments_content();
} else {
    throw new moodle_exception('pagecantaccess');
}
echo $OUTPUT->footer();
