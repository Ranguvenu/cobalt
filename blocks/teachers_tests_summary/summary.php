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
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
$url = new moodle_url('/blocks/teachers_tests_summary/summary.php');
$PAGE->set_url($url);
$PAGE->set_title(get_string('pluginname', 'block_teachers_tests_summary'));
$PAGE->set_heading(get_string('pluginname', 'block_teachers_tests_summary'));
$PAGE->navbar->add(get_string('home', 'block_teachers_tests_summary'), new moodle_url('/my'));
$PAGE->navbar->add(get_string('pluginname', 'block_teachers_tests_summary'));
$PAGE->requires->jquery();
// $PAGE->requires->css('/blocks/teachers_tests_summary/style.css');
$PAGE->requires->js_call_amd('block_teachers_tests_summary/quiz', 'Datatable', array());
$PAGE->requires->js_call_amd('block_teachers_tests_summary/quiz', 'init',
                                    array(
                                        array(
                                            'contextid' => $systemcontext->id,
                                            'selector' => '.quizpopup'
                                        )
                                    )
                                );
$PAGE->requires->js_call_amd('block_teachers_tests_summary/inprogressquiz', 'init',
                                    array(
                                        array(
                                            'contextid' => $systemcontext->id,
                                            'selector' => '.inprogressquizpopup'
                                        )
                                    )
                                );
$PAGE->requires->js_call_amd('block_teachers_tests_summary/notattemptedquiz', 'init',
                                    array(
                                        array(
                                            'contextid' => $systemcontext->id,
                                            'selector' => '.notattemptedquizpopup'
                                        )
                                    )
                                );

require_once($CFG->dirroot . '/blocks/teachers_tests_summary/filters_form.php');

$teacher = $DB->get_records_sql("SELECT DISTINCT(r.id),r.shortname 
                            FROM {role} r
                            JOIN {role_assignments} ra ON ra.roleid = r.id
                            JOIN {user} u ON u.id = ra.userid
                            WHERE u.id = {$USER->id} 
                            AND r.shortname = 'editingteacher'");

echo $OUTPUT->header();
if ($teacher) {
    $renderer = $PAGE->get_renderer('block_teachers_tests_summary');
    $filterparams = $renderer->quizzes_content(true);
    $mform = new filters_form(null, array('filterlist' => array('quizname', 'course'), 'filterparams' => $filterparams));

    if ($mform->is_cancelled()) {
        redirect($CFG->wwwroot . '/blocks/teachers_tests_summary/summary.php');
    }

    echo '<a class="btn-link btn-sm d-flex align-items-center filter_btn" href="javascript:void(0);" data-toggle="collapse"
            data-target="#block_teachers_tests_summary-filter_collapse" aria-expanded="false"
            aria-controls="block_teachers_tests_summary-filter_collapse">
                    <span class="filter mr-2">Filters</span>
                <i class="m-0 fa fa-sliders fa-2x" aria-hidden="true"></i>
          </a>';
    $filterparams['submitid'] = 'form#filteringform';
    echo  '<div class="mt-2 mb-2 collapse '.$show.'" id="block_teachers_tests_summary-filter_collapse">
                <div id="filters_form" class="card card-body p-2">';
                    $mform->display();
    echo       '</div>
           </div>';

    echo $OUTPUT->render_from_template('block_teachers_tests_summary/global_filter', $filterparams);
    echo $renderer->quizzes_content();
} else {
    throw new moodle_exception('pagecantaccess');
}
echo $OUTPUT->footer();
